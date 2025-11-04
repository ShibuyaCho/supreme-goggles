@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
  <h1 class="text-xl font-semibold mb-4">Metrc Settings</h1>

  {{-- Success or Error Alerts --}}
  @if (session('status'))
    <div class="p-3 rounded bg-green-100 text-green-900 mb-4 border border-green-300">
      {{ session('status') }}
    </div>
  @elseif (session('error'))
    <div class="p-3 rounded bg-red-100 text-red-900 mb-4 border border-red-300">
      {{ session('error') }}
    </div>
  @endif

  {{-- Validation Errors --}}
  @if ($errors->any())
    <div class="p-3 rounded bg-red-100 text-red-900 mb-4 border border-red-300">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Save Config Form --}}
  <form method="POST" action="{{ route('settings.metrc.store') }}" class="space-y-4" id="metrc-save-form">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label>Environment</label>
        <select name="environment" class="input" required>
          <option value="sandbox">Sandbox</option>
          <option value="production">Production</option>
        </select>
      </div>

      <div>
        <label>Base URL</label>
        <input name="base_url" type="url" class="input" placeholder="https://api-xx.metrc.com" required>
      </div>

      <div>
        <label>Integrator (Vendor) Key</label>
        <input name="integrator_key" type="password" class="input" autocomplete="new-password" required>
      </div>

      <div>
        <label>User Key</label>
        <input name="user_key" type="password" class="input" autocomplete="new-password" required>
      </div>

      <div>
        <label>Facility License</label>
        <input name="facility_license" type="text" class="input" placeholder="e.g. 123-ABC">
      </div>

      <div class="flex items-center gap-2 mt-6">
        <input id="is_active" name="is_active" type="checkbox" value="1" checked>
        <label for="is_active">Set as active config for this environment</label>
      </div>

      <div class="flex items-center gap-2 mt-6">
        <input id="enabled_live_sync" name="enabled_live_sync" type="checkbox" value="1">
        <label for="enabled_live_sync">Enable live sync</label>
      </div>
    </div>

    <div class="p-3 rounded bg-amber-50 border border-amber-200">
      <p class="font-medium">Replacing configuration</p>
      <p class="text-sm">
        Saving will <strong>delete any previous configuration</strong> for the selected environment and replace it with this one.
      </p>
      <label class="flex items-center gap-2 mt-2">
        <input type="checkbox" name="confirm_replace" value="1" required>
        <span>I understand and want to replace the previous configuration.</span>
      </label>
    </div>

    <div class="flex gap-3">
      <button class="btn-primary" type="submit">Save (Replace existing)</button>
    </div>
  </form>

  {{-- Show current configs --}}
  <div class="mt-8 space-y-4">
    @foreach (['sandbox' => $sandbox, 'production' => $production] as $env => $cfg)
      @php $isActive = $cfg && $cfg->is_active; @endphp

      <div class="p-4 rounded border {{ $isActive ? 'bg-green-50 border-green-300' : 'bg-white border-gray-200' }}">
        <div class="flex justify-between items-center mb-2">
          <div class="font-semibold text-lg">{{ strtoupper($env) }}</div>
          @if ($isActive)
            <span class="text-sm text-green-700 font-medium">Active</span>
          @endif
        </div>

        @if ($cfg)
          <div class="space-y-1 text-sm">
            <div>Base URL: {{ $cfg->base_url }}</div>
            <div>Integrator Key: ***{{ $cfg->integrator_key_last4 ?? '—' }}</div>
            <div>User Key: ***{{ $cfg->user_key_last4 ?? '—' }}</div>
            <div>Facility: {{ $cfg->facility_license ?? '—' }}</div>
            <div>Live Sync: {{ $cfg->enabled_live_sync ? 'Enabled' : 'Disabled' }}</div>
            <div>Last Test:
              @if($cfg->last_tested_at)
                {{ $cfg->last_tested_at instanceof \Illuminate\Support\Carbon
                    ? $cfg->last_tested_at->diffForHumans()
                    : \Illuminate\Support\Carbon::parse($cfg->last_tested_at)->diffForHumans() }}
              @else
                —
              @endif
              ({{ $cfg->last_test_status ?? '—' }})
            </div>
          </div>

          {{-- Buttons for test + activate --}}
          <div class="mt-3 flex gap-3">
            <form method="POST" action="{{ route('settings.metrc.test', $cfg->id) }}">
              @csrf
              <button type="submit"
                      class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm rounded">
                Test Connectivity
              </button>
            </form>

            @unless ($cfg->is_active)
              <form method="POST" action="{{ route('settings.metrc.activate', $cfg->id) }}">
                @csrf
                <button type="submit"
                        class="px-3 py-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-sm rounded">
                  Set Active
                </button>
              </form>
            @endunless
          </div>
        @else
          {{-- No config yet --}}
          <p class="text-slate-500 text-sm italic">No configuration stored yet.</p>
          <div class="mt-3 flex gap-3">
            <button type="button" disabled
                    class="px-3 py-1 bg-gray-100 text-gray-400 text-sm rounded cursor-not-allowed"
                    title="Save a configuration first">
              Test Connectivity
            </button>
            <button type="button" disabled
                    class="px-3 py-1 bg-gray-100 text-gray-400 text-sm rounded cursor-not-allowed"
                    title="Save a configuration first">
              Set Active
            </button>
          </div>
        @endif
      </div>
    @endforeach
  </div>
</div>
@endsection
