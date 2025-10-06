<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EmployeeIdHelper
{
    public static function generateNextId(): string
    {
        // Try Supabase first for global uniqueness
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_ANON_KEY');
        $candidates = [];
        if ($supabaseUrl && $supabaseKey) {
            try {
                $resp = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/employees', [
                    'select' => 'employee_id,created_at',
                    'order' => 'created_at.desc',
                    'limit' => 200,
                ]);
                if ($resp->ok()) {
                    $arr = $resp->json();
                    if (is_array($arr)) {
                        foreach ($arr as $row) {
                            $v = isset($row['employee_id']) ? (string)$row['employee_id'] : '';
                            if ($v !== '') $candidates[] = $v;
                        }
                    }
                }
            } catch (\Throwable $e) { /* fall back to DB */ }
        }

        if (empty($candidates)) {
            try {
                $rows = DB::table('employees')->select('employee_id')->orderBy('created_at','desc')->limit(200)->pluck('employee_id')->all();
                $candidates = array_values(array_filter(array_map(fn($v)=> $v !== null ? (string)$v : '', $rows)));
            } catch (\Throwable $e) {
                $candidates = [];
            }
        }

        $max = 0;
        foreach ($candidates as $v) {
            if (!is_string($v) || $v === '') continue;
            if (preg_match('/(\d+)/', $v, $m)) {
                $n = intval(ltrim($m[1], '0') === '' ? '0' : ltrim($m[1], '0'));
                if ($n > $max) $max = $n;
            }
        }
        $next = max(1, $max + 1);
        // Pad to at least 2 digits, expand as needed (Emp01, Emp02, ... Emp10, Emp100)
        $pad = $next < 100 ? 2 : (strlen((string)$next));
        return 'Emp' . str_pad((string)$next, $pad, '0', STR_PAD_LEFT);
    }
}
