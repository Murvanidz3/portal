@extends('god-mode.layout')

@section('title', 'უფლებების მართვა')

@push('styles')
<style>
    /* Enhanced Toggle with Status Indicator */
    .god-toggle-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .god-toggle-status {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .god-toggle-status .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
        box-shadow: 0 0 6px currentColor;
    }

    .god-toggle-status.status-on {
        color: var(--god-success);
    }

    .god-toggle-status.status-on .status-dot {
        background: var(--god-success);
        box-shadow: 0 0 8px var(--god-success), 0 0 12px var(--god-success);
        animation: pulse-green 2s ease-in-out infinite;
    }

    .god-toggle-status.status-off {
        color: var(--god-danger);
    }

    .god-toggle-status.status-off .status-dot {
        background: var(--god-danger);
        box-shadow: 0 0 8px var(--god-danger);
    }

    @keyframes pulse-green {
        0%, 100% {
            box-shadow: 0 0 4px var(--god-success), 0 0 8px var(--god-success);
        }
        50% {
            box-shadow: 0 0 8px var(--god-success), 0 0 16px var(--god-success);
        }
    }

    /* Improved Toggle Button */
    .god-toggle-enhanced {
        position: relative;
        width: 52px;
        height: 28px;
        cursor: pointer;
    }

    .god-toggle-enhanced input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .god-toggle-enhanced .toggle-track {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #3a3a3a;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 28px;
        border: 2px solid var(--god-danger);
    }

    .god-toggle-enhanced .toggle-track:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 2px;
        bottom: 2px;
        background: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .god-toggle-enhanced input:checked + .toggle-track {
        background: var(--god-success);
        border-color: var(--god-success);
    }

    .god-toggle-enhanced input:checked + .toggle-track:before {
        transform: translateX(24px);
    }

    .god-toggle-enhanced input:focus + .toggle-track {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
    }

    /* Table cell centering */
    .toggle-cell {
        text-align: center;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="god-header">
    <h1 class="god-page-title">უფლებების მართვა</h1>
    <div>
        <button class="god-btn god-btn-danger god-btn-sm" onclick="resetAllPermissions()">
            <i class="fas fa-undo"></i> ყველას აღდგენა
        </button>
    </div>
</div>

<div class="god-alert god-alert-success" style="display: none;" id="success-alert">
    <i class="fas fa-check-circle"></i>
    <span id="success-message"></span>
</div>

<!-- Legend -->
<div class="god-card" style="padding: 15px; margin-bottom: 20px;">
    <div style="display: flex; gap: 30px; align-items: center; justify-content: center;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="status-dot" style="width: 10px; height: 10px; border-radius: 50%; background: var(--god-success); box-shadow: 0 0 8px var(--god-success);"></span>
            <span style="font-size: 13px;">ჩართული</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="status-dot" style="width: 10px; height: 10px; border-radius: 50%; background: var(--god-danger); box-shadow: 0 0 8px var(--god-danger);"></span>
            <span style="font-size: 13px;">გამორთული</span>
        </div>
    </div>
</div>

<!-- Permission Groups -->
@foreach($permissions as $group => $items)
<div class="god-card">
    <div class="god-card-header">
        <h3 class="god-card-title">
            <i class="fas fa-folder-open" style="margin-right: 10px; color: var(--god-primary);"></i>
            {{ $groupLabels[$group] ?? ucfirst($group) }}
        </h3>
    </div>
    
    <table class="god-table">
        <thead>
            <tr>
                <th style="width: 25%;">ფუნქცია</th>
                <th style="width: 20%;">აღწერა</th>
                <th class="toggle-cell">გლობალური</th>
                <th class="toggle-cell">Admin</th>
                <th class="toggle-cell">Dealer</th>
                <th class="toggle-cell">Client</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $permission)
            <tr data-permission-id="{{ $permission['id'] }}">
                <td>
                    <strong>{{ $permission['feature_name'] }}</strong>
                    <br>
                    <code style="font-size: 11px; color: var(--god-text-muted);">{{ $permission['feature_key'] }}</code>
                </td>
                <td style="color: var(--god-text-muted); font-size: 13px;">
                    {{ $permission['description'] ?? '-' }}
                </td>
                <td class="toggle-cell">
                    <div class="god-toggle-wrapper">
                        <label class="god-toggle-enhanced">
                            <input 
                                type="checkbox" 
                                id="perm_{{ $permission['id'] }}_global"
                                {{ $permission['is_enabled_global'] ? 'checked' : '' }}
                                onchange="updatePermission({{ $permission['id'] }}, 'is_enabled_global', this.checked, this)"
                            >
                            <span class="toggle-track"></span>
                        </label>
                        <div class="god-toggle-status {{ $permission['is_enabled_global'] ? 'status-on' : 'status-off' }}" data-for="perm_{{ $permission['id'] }}_global">
                            <span class="status-dot"></span>
                            <span class="status-text">{{ $permission['is_enabled_global'] ? 'ON' : 'OFF' }}</span>
                        </div>
                    </div>
                </td>
                <td class="toggle-cell">
                    <div class="god-toggle-wrapper">
                        <label class="god-toggle-enhanced">
                            <input 
                                type="checkbox" 
                                id="perm_{{ $permission['id'] }}_admin"
                                {{ $permission['is_enabled_admin'] ? 'checked' : '' }}
                                onchange="updatePermission({{ $permission['id'] }}, 'is_enabled_admin', this.checked, this)"
                            >
                            <span class="toggle-track"></span>
                        </label>
                        <div class="god-toggle-status {{ $permission['is_enabled_admin'] ? 'status-on' : 'status-off' }}" data-for="perm_{{ $permission['id'] }}_admin">
                            <span class="status-dot"></span>
                            <span class="status-text">{{ $permission['is_enabled_admin'] ? 'ON' : 'OFF' }}</span>
                        </div>
                    </div>
                </td>
                <td class="toggle-cell">
                    <div class="god-toggle-wrapper">
                        <label class="god-toggle-enhanced">
                            <input 
                                type="checkbox" 
                                id="perm_{{ $permission['id'] }}_dealer"
                                {{ $permission['is_enabled_dealer'] ? 'checked' : '' }}
                                onchange="updatePermission({{ $permission['id'] }}, 'is_enabled_dealer', this.checked, this)"
                            >
                            <span class="toggle-track"></span>
                        </label>
                        <div class="god-toggle-status {{ $permission['is_enabled_dealer'] ? 'status-on' : 'status-off' }}" data-for="perm_{{ $permission['id'] }}_dealer">
                            <span class="status-dot"></span>
                            <span class="status-text">{{ $permission['is_enabled_dealer'] ? 'ON' : 'OFF' }}</span>
                        </div>
                    </div>
                </td>
                <td class="toggle-cell">
                    <div class="god-toggle-wrapper">
                        <label class="god-toggle-enhanced">
                            <input 
                                type="checkbox" 
                                id="perm_{{ $permission['id'] }}_client"
                                {{ $permission['is_enabled_client'] ? 'checked' : '' }}
                                onchange="updatePermission({{ $permission['id'] }}, 'is_enabled_client', this.checked, this)"
                            >
                            <span class="toggle-track"></span>
                        </label>
                        <div class="god-toggle-status {{ $permission['is_enabled_client'] ? 'status-on' : 'status-off' }}" data-for="perm_{{ $permission['id'] }}_client">
                            <span class="status-dot"></span>
                            <span class="status-text">{{ $permission['is_enabled_client'] ? 'ON' : 'OFF' }}</span>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

@push('scripts')
<script>
    async function updatePermission(id, field, value, inputElement) {
        // Update status indicator immediately for responsive feel
        const statusDiv = inputElement.closest('.god-toggle-wrapper').querySelector('.god-toggle-status');
        updateStatusIndicator(statusDiv, value);

        try {
            const data = {};
            data[field] = value;

            const response = await fetch(`{{ url('god/permissions') }}/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                godToast(result.message, 'success');
            } else {
                // Revert on error
                inputElement.checked = !value;
                updateStatusIndicator(statusDiv, !value);
                godToast('შეცდომა: ' + (result.message || 'უცნობი შეცდომა'), 'error');
            }
        } catch (error) {
            // Revert on error
            inputElement.checked = !value;
            updateStatusIndicator(statusDiv, !value);
            console.error('Error:', error);
            godToast('შეცდომა: ' + error.message, 'error');
        }
    }

    function updateStatusIndicator(statusDiv, isOn) {
        if (isOn) {
            statusDiv.classList.remove('status-off');
            statusDiv.classList.add('status-on');
            statusDiv.querySelector('.status-text').textContent = 'ON';
        } else {
            statusDiv.classList.remove('status-on');
            statusDiv.classList.add('status-off');
            statusDiv.querySelector('.status-text').textContent = 'OFF';
        }
    }

    function resetAllPermissions() {
        godConfirm(
            'ყველა უფლების აღდგენა',
            'დარწმუნებული ხართ? ეს დააბრუნებს ყველა უფლებას საწყის მდგომარეობაში.',
            async () => {
                try {
                    const response = await fetch('{{ route("god.permissions.reset") }}', {
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
</script>
@endpush
@endsection