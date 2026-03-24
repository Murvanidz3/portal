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

<!-- Shipping Rates CSV -->
<div class="glass-card p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-white">
            <i class="fas fa-truck" style="margin-right: 8px; color: var(--color-primary, #3b82f6);"></i>ტრანსპორტირების ტარიფები
        </h2>
        @if($csvInfo)
            <a href="{{ route('settings.shipping-rates.download') }}" class="btn-secondary" style="font-size: 13px;">
                <i class="fas fa-download"></i> CSV ჩამოტვირთვა
            </a>
        @endif
    </div>

    @if($csvInfo)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded-lg bg-dark-800/50 text-center">
                <div class="text-2xl font-bold text-green-400">{{ $csvInfo['total'] }}</div>
                <div class="text-xs text-dark-400 mt-1">სულ ლოკაციები</div>
            </div>
            <div class="p-4 rounded-lg bg-dark-800/50 text-center">
                <div class="text-2xl font-bold text-blue-400">{{ $csvInfo['copart'] }}</div>
                <div class="text-xs text-dark-400 mt-1">Copart</div>
            </div>
            <div class="p-4 rounded-lg bg-dark-800/50 text-center">
                <div class="text-2xl font-bold text-yellow-400">{{ $csvInfo['iaai'] }}</div>
                <div class="text-xs text-dark-400 mt-1">IAAI</div>
            </div>
            <div class="p-4 rounded-lg bg-dark-800/50 text-center">
                <div class="text-sm font-semibold text-white">{{ $csvInfo['size'] }} KB</div>
                <div class="text-xs text-dark-400 mt-1">{{ $csvInfo['modified'] }}</div>
            </div>
        </div>
    @else
        <div class="text-center py-6 text-dark-400 mb-4">
            <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl block mb-2"></i>
            CSV ფაილი ჯერ არ არის ატვირთული.
        </div>
    @endif

    <div id="csv-upload-zone" 
         style="border: 2px dashed var(--border-color, #334155); border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease;"
         ondragover="event.preventDefault(); this.style.borderColor='var(--color-primary, #3b82f6)'; this.style.background='rgba(59,130,246,0.05)';"
         ondragleave="this.style.borderColor='var(--border-color, #334155)'; this.style.background='transparent';"
         ondrop="handleCsvDrop(event)"
         onclick="document.getElementById('csv-file-input').click();">
        <i class="fas fa-cloud-upload-alt text-3xl mb-3 block" style="color: var(--color-primary, #3b82f6);"></i>
        <div class="text-sm text-white mb-1">ჩააგდეთ CSV ფაილი აქ ან დააჭირეთ ასარჩევად</div>
        <div class="text-xs text-dark-400">მხოლოდ .csv ფორმატი, მაქს. 5MB</div>
        <input type="file" id="csv-file-input" accept=".csv" style="display: none;" onchange="handleCsvSelect(this.files[0])">
    </div>

    <div id="csv-selected-file" style="display: none; margin-top: 12px; padding: 12px; border-radius: 8px;" class="bg-dark-800/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-file-csv text-green-400 text-xl"></i>
                <div>
                    <div id="csv-file-name" class="text-sm font-medium text-white"></div>
                    <div id="csv-file-size" class="text-xs text-dark-400"></div>
                </div>
            </div>
            <button onclick="uploadCsvFile()" class="btn-primary" id="csv-upload-btn" style="font-size: 13px;">
                <i class="fas fa-upload"></i> ატვირთვა
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
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

// CSV Upload
let csvSelectedFile = null;

function handleCsvDrop(e) {
    e.preventDefault();
    const zone = e.target.closest('#csv-upload-zone');
    zone.style.borderColor = 'var(--border-color, #334155)';
    zone.style.background = 'transparent';
    const file = e.dataTransfer.files[0];
    if (file) handleCsvSelect(file);
}

function handleCsvSelect(file) {
    if (!file) return;
    if (!file.name.endsWith('.csv')) {
        alert('მხოლოდ .csv ფორმატის ფაილი შეიძლება!');
        return;
    }
    csvSelectedFile = file;
    document.getElementById('csv-file-name').textContent = file.name;
    document.getElementById('csv-file-size').textContent = (file.size / 1024).toFixed(1) + ' KB';
    document.getElementById('csv-selected-file').style.display = 'block';
}

function uploadCsvFile() {
    if (!csvSelectedFile) return;

    const btn = document.getElementById('csv-upload-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> იტვირთება...';

    const formData = new FormData();
    formData.append('csv_file', csvSelectedFile);

    fetch('{{ route("settings.shipping-rates.upload") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.error || 'ატვირთვა ვერ მოხერხდა.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload"></i> ატვირთვა';
        }
    })
    .catch(() => {
        alert('სერვერთან კავშირის შეცდომა.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> ატვირთვა';
    });
}
</script>
@endpush
@endsection
