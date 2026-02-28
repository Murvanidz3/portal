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

    <!-- Live Preview Panel -->
    <div class="god-card" style="margin-bottom: 30px;">
        <div class="god-card-header">
            <h3 class="god-card-title"><i class="fas fa-eye"></i> პრევიუ</h3>
        </div>
        <div id="preview-panel" style="padding: 20px; background: var(--god-bg); border-radius: 8px;">
            <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                <button class="preview-btn-primary"
                    style="padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer;">პირველადი ღილაკი</button>
                <button class="preview-btn-success"
                    style="padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer;">წარმატება</button>
                <button class="preview-btn-warning"
                    style="padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer;">გაფრთხილება</button>
                <button class="preview-btn-error"
                    style="padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer;">შეცდომა</button>
            </div>
        </div>
    </div>

    <!-- Style Groups -->
    @foreach($styles as $group => $items)
        <div class="god-card">
            <div class="god-card-header">
                <h3 class="god-card-title">
                    @if($group === 'branding')
                        <i class="fas fa-image" style="margin-right: 10px; color: var(--god-primary);"></i>
                    @elseif($group === 'colors')
                        <i class="fas fa-palette" style="margin-right: 10px; color: var(--god-primary);"></i>
                    @else
                        <i class="fas fa-paint-brush" style="margin-right: 10px; color: var(--god-primary);"></i>
                    @endif
                    {{ $groupLabels[$group] ?? ucfirst($group) }}
                </h3>
            </div>

            <div style="display: grid; gap: 20px;">
                @foreach($items as $style)
                    <div class="style-item" data-style-id="{{ $style['id'] }}"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: var(--god-bg); border-radius: 8px;">
                        <div>
                            <strong>{{ $style['style_name'] }}</strong>
                            <br>
                            <code style="font-size: 11px; color: var(--god-text-muted);">{{ $style['style_key'] }}</code>
                        </div>

                        <div style="display: flex; align-items: center; gap: 15px;">
                            @if($style['style_type'] === 'color')
                                <div class="god-color-picker">
                                    <input type="color" class="god-color-preview"
                                        value="{{ $style['style_value'] ?? $style['default_value'] }}"
                                        onchange="updateColor({{ $style['id'] }}, this.value)" style="cursor: pointer;">
                                    <input type="text" class="god-input god-color-input"
                                        value="{{ $style['style_value'] ?? $style['default_value'] }}"
                                        onchange="updateColor({{ $style['id'] }}, this.value)" placeholder="#RRGGBB">
                                </div>
                            @elseif($style['style_type'] === 'image')
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    @if($style['style_value'])
                                        <img src="{{ $style['style_value'] }}" alt="Preview"
                                            style="height: 40px; border-radius: 4px; background: white; padding: 5px;">
                                    @endif
                                    <input type="file" accept="image/*" style="display: none;" id="file-{{ $style['id'] }}"
                                        onchange="uploadImage({{ $style['id'] }}, this.files[0])">
                                    <button class="god-btn god-btn-sm god-btn-secondary"
                                        onclick="document.getElementById('file-{{ $style['id'] }}').click()">
                                        <i class="fas fa-upload"></i> ატვირთვა
                                    </button>
                                </div>
                            @elseif($style['style_type'] === 'text')
                                <input type="text" class="god-input" value="{{ $style['style_value'] ?? $style['default_value'] }}"
                                    onchange="updateText({{ $style['id'] }}, this.value)" style="width: 250px;">
                            @endif

                            <button class="god-btn god-btn-sm god-btn-secondary" onclick="resetStyle({{ $style['id'] }})"
                                title="აღდგენა: {{ $style['default_value'] }}">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @push('styles')
        <style>
            .preview-btn-primary {
                background: var(--color-btn-primary-bg, #3b82f6);
                color: var(--color-btn-primary-text, #ffffff);
            }

            .preview-btn-success {
                background: var(--color-success, #22c55e);
                color: white;
            }

            .preview-btn-warning {
                background: var(--color-warning, #f59e0b);
                color: white;
            }

            .preview-btn-error {
                background: var(--color-error, #ef4444);
                color: white;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function updatePreviewStyles(css) {
                // Create or update style element for preview
                let styleEl = document.getElementById('preview-styles');
                if (!styleEl) {
                    styleEl = document.createElement('style');
                    styleEl.id = 'preview-styles';
                    document.head.appendChild(styleEl);
                }
                styleEl.textContent = css;
            }

            async function updateColor(styleId, value) {
                // Validate hex color
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
                        if (result.css) {
                            updatePreviewStyles(result.css);
                        }
                    } else {
                        godToast('შეცდომა: ' + result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    godToast('შეცდომა: ' + error.message, 'error');
                }
            }

            async function updateText(styleId, value) {
                try {
                    const response = await fetch(`{{ url('god/styles') }}/${styleId}/text`, {
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
                    console.error('Error:', error);
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
                    console.error('Error:', error);
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
                    console.error('Error:', error);
                    godToast('შეცდომა: ' + error.message, 'error');
                }
            }

            function resetAllStyles() {
                godConfirm(
                    'ყველა სტილის აღდგენა',
                    'დარწმუნებული ხართ? ეს დააბრუნებს ყველა სტილს საწყის მდგომარეობაში.',
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
                            console.error('Error:', error);
                            godToast('შეცდომა: ' + error.message, 'error');
                        }
                    }
                );
            }

            // Load initial CSS variables
            fetch('{{ route("god.styles.css") }}')
                .then(response => response.text())
                .then(css => updatePreviewStyles(css));
        </script>
    @endpush
@endsection