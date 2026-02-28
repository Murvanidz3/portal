@extends('god-mode.layout')

@section('title', $user->full_name . ' - ტრანსპორტირების ტარიფები')

@section('content')
    <div class="god-header">
        <div>
            <a href="{{ route('god.shipping-rates.index') }}" class="god-btn god-btn-secondary god-btn-sm"
                style="margin-bottom: 10px;">
                <i class="fas fa-arrow-left"></i> უკან
            </a>
            <h1 class="god-page-title">
                <i class="fas fa-user" style="margin-right: 10px;"></i>
                {{ $user->full_name }}
            </h1>
            <p style="color: var(--god-text-muted); margin-top: 5px;">{{ $user->email }}</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <!-- Left Column: Upload & File History -->
        <div>
            <!-- Upload New File -->
            <div class="god-card" style="margin-bottom: 20px;">
                <div class="god-card-header">
                    <h3 class="god-card-title">
                        <i class="fas fa-upload" style="margin-right: 10px; color: var(--god-primary);"></i>
                        ფაილის ატვირთვა
                    </h3>
                </div>
                <div class="god-card-body" style="padding: 20px;">
                    <form action="{{ route('god.shipping-rates.upload', $user) }}" method="POST"
                        enctype="multipart/form-data" id="upload-form">
                        @csrf

                        <div class="upload-zone" id="upload-zone">
                            <input type="file" name="excel_file" id="excel_file" accept=".csv" style="display: none;"
                                required>
                            <div class="upload-icon">
                                <i class="fas fa-file-csv"></i>
                            </div>
                            <div class="upload-text">
                                გადმოათრიეთ CSV ფაილი ან <span class="upload-link">აირჩიეთ</span>
                            </div>
                            <div class="upload-hint">
                                დასაშვებია მხოლოდ CSV ფორმატი (მაქს. 5MB)
                            </div>
                            <div class="selected-file" id="selected-file" style="display: none;">
                                <i class="fas fa-file-alt"></i>
                                <span id="file-name"></span>
                                <button type="button" class="remove-file" onclick="removeFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        @error('excel_file')
                            <div style="color: var(--god-danger); font-size: 13px; margin-top: 10px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror

                        <button type="submit" class="god-btn god-btn-primary" style="width: 100%; margin-top: 15px;"
                            id="upload-btn" disabled>
                            <i class="fas fa-cloud-upload-alt"></i> ატვირთვა და დამუშავება
                        </button>
                    </form>
                </div>
            </div>

            <!-- File History -->
            <div class="god-card">
                <div class="god-card-header">
                    <h3 class="god-card-title">
                        <i class="fas fa-history" style="margin-right: 10px; color: var(--god-primary);"></i>
                        ფაილების ისტორია
                    </h3>
                </div>

                @if($fileHistory->count() > 0)
                    <table class="god-table">
                        <thead>
                            <tr>
                                <th>ფაილი</th>
                                <th>თარიღი</th>
                                <th style="text-align: center;">სტატუსი</th>
                                <th style="text-align: center;">მოქმედება</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fileHistory as $file)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <i class="fas fa-file-excel" style="color: #22c55e;"></i>
                                            <span
                                                style="font-size: 13px;">{{ \Illuminate\Support\Str::limit($file->original_name, 25) }}</span>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; color: var(--god-text-muted);">
                                        {{ $file->uploaded_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if($file->is_active)
                                            <span class="god-badge" style="background: var(--god-success);">აქტიური</span>
                                        @else
                                            <span class="god-badge" style="background: var(--god-text-muted);">არააქტიური</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            @if(!$file->is_active)
                                                <form action="{{ route('god.shipping-rates.activate', $file) }}" method="POST"
                                                    style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="god-btn god-btn-success god-btn-sm"
                                                        title="გააქტიურება">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('god.shipping-rates.delete', $file) }}" method="POST"
                                                style="display: inline;" onsubmit="return confirm('დარწმუნებული ხართ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="god-btn god-btn-danger god-btn-sm" title="წაშლა">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding: 40px; text-align: center; color: var(--god-text-muted);">
                        <i class="fas fa-folder-open"
                            style="font-size: 36px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                        ფაილები არ აიტვირთა
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Current Rates -->
        <div>
            <div class="god-card">
                <div class="god-card-header">
                    <h3 class="god-card-title">
                        <i class="fas fa-list-alt" style="margin-right: 10px; color: var(--god-primary);"></i>
                        მიმდინარე ტარიფები
                    </h3>
                    @if($activeFile)
                        <span style="font-size: 12px; color: var(--god-text-muted);">
                            ფაილი: {{ $activeFile->original_name }}
                        </span>
                    @endif
                </div>

                @if(count($rates['copart']) > 0 || count($rates['iaai']) > 0)
                    <!-- Search -->
                    <div style="padding: 15px; border-bottom: 1px solid var(--god-border);">
                        <input type="text" id="rate-search" placeholder="მოძებნეთ ლოკაცია..." class="god-input"
                            style="width: 100%;">
                    </div>

                    <!-- Tabs -->
                    <div class="rate-tabs">
                        <button class="rate-tab active" data-tab="copart">
                            COPART
                            <span class="count">{{ count($rates['copart']) }}</span>
                        </button>
                        <button class="rate-tab" data-tab="iaai">
                            IAAI
                            <span class="count">{{ count($rates['iaai']) }}</span>
                        </button>
                    </div>

                    <!-- Copart Rates -->
                    <div class="rate-content active" id="copart-rates">
                        <div class="rate-list">
                            @foreach($rates['copart'] as $rate)
                                <div class="rate-item" data-location="{{ strtolower($rate->location_name) }}">
                                    <span class="rate-location">{{ $rate->location_name }}</span>
                                    <span class="rate-price">${{ number_format($rate->price, 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- IAAI Rates -->
                    <div class="rate-content" id="iaai-rates" style="display: none;">
                        <div class="rate-list">
                            @foreach($rates['iaai'] as $rate)
                                <div class="rate-item" data-location="{{ strtolower($rate->location_name) }}">
                                    <span class="rate-location">{{ $rate->location_name }}</span>
                                    <span class="rate-price">${{ number_format($rate->price, 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div style="padding: 60px 20px; text-align: center; color: var(--god-text-muted);">
                        <i class="fas fa-truck" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 15px;"></i>
                        <p>ტარიფები არ არის ატვირთული</p>
                        <p style="font-size: 13px; margin-top: 10px;">ატვირთეთ CSV ფაილი ტარიფების დასამატებლად</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .upload-zone {
            border: 2px dashed var(--god-border);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.02);
        }

        .upload-zone:hover,
        .upload-zone.dragover {
            border-color: var(--god-primary);
            background: rgba(59, 130, 246, 0.05);
        }

        .upload-icon {
            font-size: 48px;
            color: #22c55e;
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 16px;
            color: var(--god-text);
            margin-bottom: 8px;
        }

        .upload-link {
            color: var(--god-primary);
            text-decoration: underline;
        }

        .upload-hint {
            font-size: 13px;
            color: var(--god-text-muted);
        }

        .selected-file {
            margin-top: 15px;
            padding: 10px 15px;
            background: var(--god-primary);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .selected-file i {
            color: white;
        }

        .selected-file span {
            color: white;
            font-weight: 500;
        }

        .remove-file {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
        }

        .remove-file:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .god-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }

        .rate-tabs {
            display: flex;
            border-bottom: 1px solid var(--god-border);
        }

        .rate-tab {
            flex: 1;
            padding: 15px;
            border: none;
            background: none;
            color: var(--god-text-muted);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }

        .rate-tab:hover {
            color: var(--god-text);
            background: rgba(255, 255, 255, 0.02);
        }

        .rate-tab.active {
            color: var(--god-primary);
            border-bottom-color: var(--god-primary);
        }

        .rate-tab .count {
            display: inline-block;
            background: var(--god-border);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 8px;
        }

        .rate-tab.active .count {
            background: var(--god-primary);
            color: white;
        }

        .rate-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .rate-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid var(--god-border);
        }

        .rate-item:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .rate-item.hidden {
            display: none;
        }

        .rate-location {
            font-size: 13px;
            color: var(--god-text);
        }

        .rate-price {
            font-weight: 600;
            color: var(--god-success);
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const uploadZone = document.getElementById('upload-zone');
                const fileInput = document.getElementById('excel_file');
                const selectedFile = document.getElementById('selected-file');
                const fileName = document.getElementById('file-name');
                const uploadBtn = document.getElementById('upload-btn');

                // Click to select file
                uploadZone.addEventListener('click', function () {
                    fileInput.click();
                });

                // File selection
                fileInput.addEventListener('change', function () {
                    if (this.files.length > 0) {
                        showSelectedFile(this.files[0]);
                    }
                });

                // Drag and drop
                uploadZone.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                uploadZone.addEventListener('dragleave', function () {
                    this.classList.remove('dragover');
                });

                uploadZone.addEventListener('drop', function (e) {
                    e.preventDefault();
                    this.classList.remove('dragover');

                    if (e.dataTransfer.files.length > 0) {
                        fileInput.files = e.dataTransfer.files;
                        showSelectedFile(e.dataTransfer.files[0]);
                    }
                });

                function showSelectedFile(file) {
                    fileName.textContent = file.name;
                    selectedFile.style.display = 'inline-flex';
                    uploadBtn.disabled = false;
                }

                window.removeFile = function () {
                    fileInput.value = '';
                    selectedFile.style.display = 'none';
                    uploadBtn.disabled = true;
                };

                // Tabs
                document.querySelectorAll('.rate-tab').forEach(tab => {
                    tab.addEventListener('click', function () {
                        document.querySelectorAll('.rate-tab').forEach(t => t.classList.remove('active'));
                        document.querySelectorAll('.rate-content').forEach(c => c.style.display = 'none');

                        this.classList.add('active');
                        document.getElementById(this.dataset.tab + '-rates').style.display = 'block';
                    });
                });

                // Search
                document.getElementById('rate-search')?.addEventListener('input', function () {
                    const query = this.value.toLowerCase();

                    document.querySelectorAll('.rate-item').forEach(item => {
                        const location = item.dataset.location;
                        if (location.includes(query)) {
                            item.classList.remove('hidden');
                        } else {
                            item.classList.add('hidden');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection