<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MetrcConfig;
use App\Services\MetrcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Encryption\DecryptException;

class MetrcSettingsController extends Controller
{
    public function index()
    {
        // Display the most-recent row per env
        $sandbox    = MetrcConfig::where('environment', 'sandbox')->latest('id')->first();
        $production = MetrcConfig::where('environment', 'production')->latest('id')->first();

        return view('settings.metrc', compact('sandbox', 'production'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'environment'        => ['required', Rule::in(['sandbox','production'])],
            'base_url'           => ['required','url'],
            'integrator_key'     => ['required','string'],
            'user_key'           => ['required','string'],
            'facility_license'   => ['nullable','string'],
            'enabled_live_sync'  => ['sometimes','boolean'],
            'is_active'          => ['sometimes','boolean'],
            'confirm_replace'    => ['accepted'],
        ]);

        DB::transaction(function () use ($data) {
            // Replace existing config for that environment
            MetrcConfig::where('environment', $data['environment'])->delete();

            $cfg = new MetrcConfig($data);
            $cfg->enabled_live_sync = (bool)($data['enabled_live_sync'] ?? false);

            // Ensure ONLY ONE config is active across ALL envs (your requirement)
            if (!empty($data['is_active'])) {
                MetrcConfig::query()->update(['is_active' => false]);
                $cfg->is_active = true;
            } else {
                $cfg->is_active = false;
            }

            $cfg->save();
        });

        return back()->with('status', 'Configuration saved' . (!empty($data['is_active']) ? ' and activated.' : '.'));
    }

    public function test(string $id)
    {
        $cfg = MetrcConfig::findOrFail($id);

        try {
            $svc   = MetrcService::fromConfig($cfg);
            $probe = $svc->testConnection();

            $ok = (bool) ($probe['success'] ?? false);
            $msg = $probe['message'] ?? ($ok ? 'Connectivity ok.' : 'Connectivity failed.');

            $cfg->forceFill([
                'last_tested_at'   => now(),
                'last_test_status' => $ok ? 'ok' : 'failed',
            ])->save();

            return back()->with($ok ? 'status' : 'error', $msg);

        } catch (DecryptException $e) {
            $cfg->forceFill([
                'last_tested_at'   => now(),
                'last_test_status' => 'failed',
            ])->save();

            return back()->with('error',
                'Connectivity errored: could not decrypt one or more secrets. Re-save this configuration or check APP_KEY.'
            );
        } catch (\Throwable $e) {
            $cfg->forceFill([
                'last_tested_at'   => now(),
                'last_test_status' => 'failed',
            ])->save();

            return back()->with('error', 'Connectivity errored: ' . $e->getMessage());
        }
    }

    public function activate(string $id)
    {
        DB::transaction(function () use ($id) {
            // ONE globally active config at any time
            MetrcConfig::query()->update(['is_active' => false]);
            MetrcConfig::whereKey($id)->update(['is_active' => true]);
        });

        return back()->with('status', 'Configuration activated.');
    }
}
