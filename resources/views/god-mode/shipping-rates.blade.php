@extends('god-mode.layout')

@section('title', 'ტარიფები')

@section('content')
    <div class="god-header">
        <h2 class="god-page-title"><i class="fas fa-truck" style="color: var(--god-primary); margin-right: 10px;"></i>ტრანსპორტირების ტარიფები</h2>
        @if($fileInfo)
            <a href="{{ route('god.shipping-rates.download') }}" class="god-btn god-btn-secondary">
                <i class="fas fa-download"></i> CSV ჩამოტვირთვა
            </a>
        @endif
    </div>

    {{-- File Info --}}
    <div class="god-card" style="margin-bottom: 24px;">
        <div class="god-card-header" style="margin-bottom: 16px;">
            <h3 class="god-card-title"><i class="fas fa-info-circle" style="margin-right: 8px; color: var(--god-info);"></i>ფაილის ინფორმაცია</h3>
        </div>
        @if($fileInfo)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                <div style="background: var(--god-bg); border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 24px; font-weight: 700; color: var(--god-success);">{{ $summary['total'] }}</div>
                    <div style="font-size: 12px; color: var(--god-text-muted); margin-top: 4px;">სულ ლოკაციები</div>
                </div>
                <div style="background: var(--god-bg); border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 24px; font-weight: 700; color: var(--god-info);">{{ $summary['copart'] }}</div>
                    <div style="font-size: 12px; color: var(--god-text-muted); margin-top: 4px;">Copart</div>
                </div>
                <div style="background: var(--god-bg); border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 24px; font-weight: 700; color: var(--god-warning);">{{ $summary['iaai'] }}</div>
                    <div style="font-size: 12px; color: var(--god-text-muted); margin-top: 4px;">IAAI</div>
                </div>
                <div style="background: var(--god-bg); border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 14px; font-weight: 600; color: var(--god-text);">{{ $fileInfo['size'] }} KB</div>
                    <div style="font-size: 12px; color: var(--god-text-muted); margin-top: 4px;">ფაილის ზომა</div>
                    <div style="font-size: 11px; color: var(--god-text-muted); margin-top: 2px;">{{ $fileInfo['modified'] }}</div>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 30px; color: var(--god-text-muted);">
                <i class="fas fa-exclamation-triangle" style="font-size: 32px; color: var(--god-warning); display: block; margin-bottom: 12px;"></i>
                CSV ფაილი ჯერ არ არის ატვირთული.
            </div>
        @endif
    </div>

    {{-- Upload --}}
    <div class="god-card" style="margin-bottom: 24px;">
        <div class="god-card-header" style="margin-bottom: 16px;">
            <h3 class="god-card-title"><i class="fas fa-upload" style="margin-right: 8px; color: var(--god-primary);"></i>CSV ფაილის ატვირთვა</h3>
        </div>
        <div id="upload-zone" style="border: 2px dashed var(--god-border); border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s ease;"
             ondragover="event.preventDefault(); this.style.borderColor='var(--god-primary)'; this.style.background='rgba(220,38,38,0.05)';"
             ondragleave="this.style.borderColor='var(--god-border)'; this.style.background='transparent';"
             ondrop="handleDrop(event)"
             onclick="document.getElementById('csv-input').click();">
            <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: var(--god-primary); margin-bottom: 12px; display: block;"></i>
            <div style="font-size: 14px; color: var(--god-text); margin-bottom: 6px;">ჩააგდეთ CSV ფაილი აქ ან დააჭირეთ ასარჩევად</div>
            <div style="font-size: 12px; color: var(--god-text-muted);">მხოლოდ .csv ფორმატი, მაქს. 5MB</div>
            <input type="file" id="csv-input" accept=".csv" style="display: none;" onchange="handleFileSelect(this.files[0])">
        </div>

        {{-- Upload progress --}}
        <div id="upload-progress" style="display: none; margin-top: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="flex: 1; background: var(--god-bg); border-radius: 6px; height: 8px; overflow: hidden;">
                    <div id="progress-bar" style="width: 0%; height: 100%; background: var(--god-primary); border-radius: 6px; transition: width 0.3s ease;"></div>
                </div>
                <span id="progress-text" style="font-size: 12px; color: var(--god-text-muted); min-width: 40px;">0%</span>
            </div>
        </div>

        {{-- Selected file info --}}
        <div id="selected-file" style="display: none; margin-top: 16px; padding: 12px; background: var(--god-bg); border-radius: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-file-csv" style="color: var(--god-success); font-size: 20px;"></i>
                    <div>
                        <div id="file-name" style="font-size: 13px; font-weight: 500;"></div>
                        <div id="file-size" style="font-size: 11px; color: var(--god-text-muted);"></div>
                    </div>
                </div>
                <button onclick="uploadFile()" class="god-btn god-btn-primary" id="upload-btn">
                    <i class="fas fa-upload"></i> ატვირთვა
                </button>
            </div>
        </div>
    </div>

    {{-- Rates Preview --}}
    <div class="god-card">
        <div class="god-card-header" style="margin-bottom: 16px;">
            <h3 class="god-card-title"><i class="fas fa-table" style="margin-right: 8px; color: var(--god-success);"></i>ტარიფების გადახედვა</h3>
            <div style="display: flex; gap: 8px;">
                <button id="tab-copart" class="god-btn god-btn-primary" onclick="switchTab('COPART')" style="font-size: 12px; padding: 6px 14px;">Copart</button>
                <button id="tab-iaai" class="god-btn god-btn-secondary" onclick="switchTab('IAAI')" style="font-size: 12px; padding: 6px 14px;">IAAI</button>
            </div>
        </div>

        <div id="preview-loading" style="text-align: center; padding: 30px; display: none;">
            <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: var(--god-primary);"></i>
            <div style="margin-top: 8px; color: var(--god-text-muted); font-size: 13px;">იტვირთება...</div>
        </div>

        <div id="preview-empty" style="text-align: center; padding: 30px; color: var(--god-text-muted); display: none;">
            <i class="fas fa-inbox" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
            მონაცემები არ არის.
        </div>

        {{-- Search --}}
        <div style="margin-bottom: 16px;">
            <input type="text" id="search-input" placeholder="ლოკაციის ძიება..."
                   oninput="filterTable()"
                   style="width: 100%; max-width: 360px; padding: 10px 14px; background: var(--god-bg); border: 1px solid var(--god-border); border-radius: 8px; color: var(--god-text); font-size: 13px; outline: none;">
        </div>

        <div style="overflow-x: auto;">
            <table id="rates-table" style="width: 100%; border-collapse: collapse; font-size: 13px; display: none;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--god-border);">
                        <th style="text-align: left; padding: 10px 12px; color: var(--god-text-muted); font-weight: 600;">#</th>
                        <th style="text-align: left; padding: 10px 12px; color: var(--god-text-muted); font-weight: 600;">ლოკაცია</th>
                        <th style="text-align: right; padding: 10px 12px; color: var(--god-text-muted); font-weight: 600;">სედანი</th>
                        <th style="text-align: right; padding: 10px 12px; color: var(--god-text-muted); font-weight: 600;">ჯიპი</th>
                        <th style="text-align: right; padding: 10px 12px; color: var(--god-text-muted); font-weight: 600;">პიკაპი</th>
                        <th style="text-align: right; padding: 10px 12px; color: var(--god-text-muted); font-weight: 600;">მინივენი</th>
                        <th style="text-align: right; padding: 10px 12px; color: var(--god-text-muted); font-weight: 600;">სპრინტერი</th>
                        <th style="text-align: right; padding: 10px 12px; color: var(--god-text-muted); font-weight: 600;">მოტო</th>
                    </tr>
                </thead>
                <tbody id="rates-tbody"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let selectedFile = null;
    let previewData = {};
    let currentTab = 'COPART';

    // Load preview on page load
    document.addEventListener('DOMContentLoaded', loadPreview);

    function handleDrop(e) {
        e.preventDefault();
        e.target.closest('#upload-zone').style.borderColor = 'var(--god-border)';
        e.target.closest('#upload-zone').style.background = 'transparent';
        const file = e.dataTransfer.files[0];
        if (file) handleFileSelect(file);
    }

    function handleFileSelect(file) {
        if (!file) return;
        if (!file.name.endsWith('.csv')) {
            godToast('მხოლოდ .csv ფორმატის ფაილი შეიძლება!', 'error');
            return;
        }
        selectedFile = file;
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-size').textContent = (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('selected-file').style.display = 'block';
    }

    async function uploadFile() {
        if (!selectedFile) return;

        const btn = document.getElementById('upload-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> იტვირთება...';

        document.getElementById('upload-progress').style.display = 'block';

        const formData = new FormData();
        formData.append('csv_file', selectedFile);

        try {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("god.shipping-rates.upload") }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    document.getElementById('progress-bar').style.width = pct + '%';
                    document.getElementById('progress-text').textContent = pct + '%';
                }
            };

            xhr.onload = function() {
                const data = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && data.success) {
                    godToast(data.message, 'success');
                    // Reload page after short delay to show updated info
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    godToast(data.error || 'ატვირთვა ვერ მოხერხდა.', 'error');
                    resetUploadUI();
                }
            };

            xhr.onerror = function() {
                godToast('სერვერთან კავშირის შეცდომა.', 'error');
                resetUploadUI();
            };

            xhr.send(formData);
        } catch (err) {
            godToast('შეცდომა: ' + err.message, 'error');
            resetUploadUI();
        }
    }

    function resetUploadUI() {
        const btn = document.getElementById('upload-btn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> ატვირთვა';
        document.getElementById('upload-progress').style.display = 'none';
        document.getElementById('progress-bar').style.width = '0%';
        document.getElementById('progress-text').textContent = '0%';
    }

    async function loadPreview() {
        const loading = document.getElementById('preview-loading');
        const empty = document.getElementById('preview-empty');
        const table = document.getElementById('rates-table');

        loading.style.display = 'block';
        table.style.display = 'none';
        empty.style.display = 'none';

        try {
            const res = await fetch('{{ route("god.shipping-rates.preview") }}');
            const json = await res.json();

            if (json.success && json.data) {
                previewData = json.data;
                renderTable();
            } else {
                empty.style.display = 'block';
            }
        } catch (err) {
            empty.style.display = 'block';
        }

        loading.style.display = 'none';
    }

    function switchTab(tab) {
        currentTab = tab;
        document.getElementById('tab-copart').className = tab === 'COPART' ? 'god-btn god-btn-primary' : 'god-btn god-btn-secondary';
        document.getElementById('tab-iaai').className = tab === 'IAAI' ? 'god-btn god-btn-primary' : 'god-btn god-btn-secondary';
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('rates-tbody');
        const table = document.getElementById('rates-table');
        const empty = document.getElementById('preview-empty');
        const data = previewData[currentTab] || [];

        if (data.length === 0) {
            table.style.display = 'none';
            empty.style.display = 'block';
            return;
        }

        const query = (document.getElementById('search-input').value || '').toLowerCase().trim();
        const filtered = query ? data.filter(r => r.location.toLowerCase().includes(query)) : data;

        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--god-text-muted);">შედეგი ვერ მოიძებნა.</td></tr>';
        } else {
            tbody.innerHTML = filtered.map((r, i) => `
                <tr style="border-bottom: 1px solid var(--god-border); transition: background 0.15s;"
                    onmouseover="this.style.background='var(--god-surface-hover)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:10px 12px;color:var(--god-text-muted);">${i + 1}</td>
                    <td style="padding:10px 12px;font-weight:500;">${r.location}</td>
                    <td style="padding:10px 12px;text-align:right;color:var(--god-success);">$${r.sedan.toLocaleString()}</td>
                    <td style="padding:10px 12px;text-align:right;">$${r.suv.toLocaleString()}</td>
                    <td style="padding:10px 12px;text-align:right;">$${r.pickup.toLocaleString()}</td>
                    <td style="padding:10px 12px;text-align:right;">$${r.minivan.toLocaleString()}</td>
                    <td style="padding:10px 12px;text-align:right;">$${r.sprinter.toLocaleString()}</td>
                    <td style="padding:10px 12px;text-align:right;">$${r.moto.toLocaleString()}</td>
                </tr>
            `).join('');
        }

        table.style.display = 'table';
        empty.style.display = 'none';
    }

    function filterTable() {
        renderTable();
    }
</script>
@endpush
