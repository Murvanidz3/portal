@extends('god-mode.layout')

@section('title', 'სტილების მართვა')

@section('content')
    <div class="god-header">
        <h1 class="god-page-title">სტილები და ბრენდინგი</h1>
        <div>
            <button class="god-btn god-btn-danger god-btn-sm" onclick="resetAllStyles()">
                <i class="fas fa-undo"></i> ყველას აღდგენა
            </button>
        </div>
    </div>

    <!-- Primary Color -->
    <div class="god-card" style="margin-bottom: 24px;">
        <div class="god-card-header">
            <h3 class="god-card-title">
                <i class="fas fa-palette" style="margin-right: 10px; color: var(--god-primary);"></i>
                ძირითადი ფერი
            </h3>
        </div>
        @php
            $primaryColor = null;
            foreach ($styles as $group => $items) {
                foreach ($items as $s) {
                    if ($s['style_key'] === 'color_primary') { $primaryColor = $s; break 2; }
                }
            }
        @endphp
        @if($primaryColor)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px; background: var(--god-bg); border-radius: 8px;">
                <div>
                    <strong>ძირითადი ფერი</strong>
                    <p style="color: var(--god-text-muted); font-size: 13px; margin-top: 4px;">
                        ამ ფერით გამოჩნდება ღილაკები, ლინკები და აქტიური ელემენტები საიტზე
                    </p>
                </div>
                <div class="god-color-picker" style="display: flex; align-items: center; gap: 12px;">
                    <input type="color" id="primary-color-picker"
                        value="{{ $primaryColor['style_value'] ?? $primaryColor['default_value'] }}"
                        onchange="updateColor({{ $primaryColor['id'] }}, this.value); document.getElementById('primary-color-hex').value = this.value;"
                        style="cursor: pointer; width: 50px; height: 50px; border: none; border-radius: 10px; background: none;">
                    <input type="text" id="primary-color-hex" class="god-input"
                        value="{{ $primaryColor['style_value'] ?? $primaryColor['default_value'] }}"
                        onchange="updateColor({{ $primaryColor['id'] }}, this.value); document.getElementById('primary-color-picker').value = this.value;"
                        placeholder="#RRGGBB" style="width: 120px; text-align: center; font-family: monospace; font-size: 15px;">
                    <button class="god-btn god-btn-sm god-btn-secondary" onclick="resetStyle({{ $primaryColor['id'] }})"
                        title="აღდგენა: {{ $primaryColor['default_value'] }}">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Logos -->
    <div class="god-card">
        <div class="god-card-header">
            <h3 class="god-card-title">
                <i class="fas fa-image" style="margin-right: 10px; color: var(--god-primary);"></i>
                ლოგოები
            </h3>
        </div>

        @php
            $logos = [];
            $logoKeys = ['brand_header_logo', 'brand_invoice_logo', 'brand_favicon'];
            $logoLabels = [
                'brand_header_logo'  => ['საიტის ლოგო', 'Header-ში გამოჩნდება'],
                'brand_invoice_logo' => ['ინვოისის ლოგო', 'ინვოისის PDF-ში გამოჩნდება'],
                'brand_favicon'      => ['Favicon', 'ბრაუზერის ტაბში გამოჩნდება'],
            ];
            foreach ($styles as $group => $items) {
                foreach ($items as $s) {
                    if (in_array($s['style_key'], $logoKeys)) {
                        $logos[$s['style_key']] = $s;
                    }
                }
            }
        @endphp

        <div style="display: grid; gap: 20px;">
            @foreach($logoKeys as $key)
                @if(isset($logos[$key]))
                    @php $logo = $logos[$key]; $label = $logoLabels[$key]; @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px; background: var(--god-bg); border-radius: 8px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 80px; height: 60px; border-radius: 8px; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                @if($logo['style_value'])
                                    <img src="{{ $logo['style_value'] }}" alt="{{ $label[0] }}" style="max-height: 50px; max-width: 70px; object-fit: contain;">
                                @else
                                    <i class="fas fa-image" style="color: #ccc; font-size: 24px;"></i>
                                @endif
                            </div>
                            <div>
                                <strong>{{ $label[0] }}</strong>
                                <p style="color: var(--god-text-muted); font-size: 13px; margin-top: 4px;">
                                    {{ $label[1] }}
                                </p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="file" accept="image/*" style="display: none;" id="file-{{ $logo['id'] }}"
                                onchange="uploadImage({{ $logo['id'] }}, this.files[0])">
                            <button class="god-btn god-btn-sm god-btn-secondary"
                                onclick="document.getElementById('file-{{ $logo['id'] }}').click()">
                                <i class="fas fa-upload"></i> ატვირთვა
                            </button>
                            <button class="god-btn god-btn-sm god-btn-secondary" onclick="resetStyle({{ $logo['id'] }})"
                                title="აღდგენა">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @push('scripts')
        <script>
            async function updateColor(styleId, value) {
                if (!/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)) {
                    godToast('არასწორი ფერის ფორმატი', 'error');
                    return;
                }
                try {
                    const response = await fetch(`{{ url('god/styles') }}/${styleId}/color`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ value: value })
                    });
                    const result = await response.json();
                    if (result.success) {
                        godToast(result.message, 'success');
                    } else {
                        godToast('შეცდომა: ' + result.message, 'error');
                    }
                } catch (error) {
                    godToast('შეცდომა: ' + error.message, 'error');
                }
            }

            async function uploadImage(styleId, file) {
                if (!file) return;
                const formData = new FormData();
                formData.append('image', file);
                try {
                    const response = await fetch(`{{ url('god/styles') }}/${styleId}/image`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        godToast(result.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        godToast('შეცდომა: ' + result.message, 'error');
                    }
                } catch (error) {
                    godToast('შეცდომა: ' + error.message, 'error');
                }
            }

            async function resetStyle(styleId) {
                try {
                    const response = await fetch(`{{ url('god/styles') }}/${styleId}/reset`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        godToast(result.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        godToast('შეცდომა: ' + result.message, 'error');
                    }
                } catch (error) {
                    godToast('შეცდომა: ' + error.message, 'error');
                }
            }

            function resetAllStyles() {
                godConfirm(
                    'ყველა სტილის აღდგენა',
                    'დარწმუნებული ხართ? ეს დააბრუნებს ძირითად ფერს და ლოგოებს საწყის მდგომარეობაში.',
                    async () => {
                        try {
                            const response = await fetch('{{ route("god.styles.reset-all") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                }
                            });
                            const result = await response.json();
                            if (result.success) {
                                godToast(result.message, 'success');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                godToast('შეცდომა: ' + result.message, 'error');
                            }
                        } catch (error) {
                            godToast('შეცდომა: ' + error.message, 'error');
                        }
                    }
                );
            }
        </script>
    @endpush
@endsection