@extends('layouts.app')

@section('title', 'პარამეტრები')

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-white">პარამეტრები</h1>
    <p class="text-dark-400 mt-1">სისტემის კონფიგურაცია</p>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
    @csrf
    @method('PUT')
    
    @foreach($groups as $groupKey => $groupLabel)
    @if(isset($settings[$groupKey]) && $settings[$groupKey]->count() > 0)
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">{{ $groupLabel }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($settings[$groupKey] as $setting)
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">{{ $setting->setting_key }}</label>
                @if(strlen($setting->setting_value) > 100)
                    <textarea name="settings[{{ $setting->setting_key }}]" rows="3" class="form-input w-full">{{ $setting->setting_value }}</textarea>
                @else
                    <input type="text" name="settings[{{ $setting->setting_key }}]" value="{{ $setting->setting_value }}" class="form-input w-full">
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach
    
    <div class="flex justify-end">
        <button type="submit" class="btn-primary">შენახვა</button>
    </div>
</form>

<!-- System Actions -->
<div class="glass-card p-6 mt-6">
    <h2 class="text-lg font-semibold text-white mb-4">სისტემის მოქმედებები</h2>
    
    <div class="flex flex-wrap gap-4">
        <button onclick="toggleMaintenance()" class="btn-secondary" id="maintenance-btn">
            მეინთენანს რეჟიმი
        </button>
        
        <button onclick="clearCache()" class="btn-secondary">
            ქეშის გასუფთავება
        </button>
        
        <button onclick="getSystemInfo()" class="btn-secondary">
            სისტემის ინფო
        </button>
    </div>
    
    <div id="system-info" class="mt-4 hidden">
        <div class="p-4 rounded-lg bg-dark-800/50">
            <pre id="system-info-content" class="text-sm text-dark-300 whitespace-pre-wrap"></pre>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleMaintenance() {
    fetch('{{ route("settings.toggle-maintenance") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        alert(data.message);
    });
}

function clearCache() {
    fetch('{{ route("settings.clear-cache") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        alert(data.message);
    });
}

function getSystemInfo() {
    fetch('{{ route("settings.system-info") }}', {
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        document.getElementById('system-info').classList.remove('hidden');
        document.getElementById('system-info-content').textContent = JSON.stringify(data, null, 2);
    });
}
</script>
@endpush
@endsection
