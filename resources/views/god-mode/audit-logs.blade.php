@extends('god-mode.layout')

@section('title', 'Audit Logs')

@section('content')
    <div class="god-header">
        <h1 class="god-page-title">Audit Logs</h1>
    </div>

    <div class="god-card">
        <div class="god-card-header">
            <h3 class="god-card-title">მოქმედებების ისტორია</h3>
        </div>

        @if($logs->count() > 0)
            <table class="god-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>მოქმედება</th>
                        <th>Super Admin</th>
                        <th>სამიზნე</th>
                        <th>IP მისამართი</th>
                        <th>თარიღი</th>
                        <th>დეტალები</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td><code>#{{ $log->id }}</code></td>
                            <td>
                                <span
                                    class="god-badge {{ in_array($log->action, ['login', 'logout']) ? 'god-badge-success' : 'god-badge-danger' }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td>{{ $log->superAdmin->username ?? 'N/A' }}</td>
                            <td>
                                @if($log->target_type && $log->target_id)
                                    <code style="font-size: 11px;">{{ class_basename($log->target_type) }}#{{ $log->target_id }}</code>
                                @else
                                    <span style="color: var(--god-text-muted);">-</span>
                                @endif
                            </td>
                            <td><code>{{ $log->ip_address }}</code></td>
                            <td>{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                            <td>
                                @if($log->old_value || $log->new_value)
                                    <button class="god-btn god-btn-sm god-btn-secondary"
                                        onclick="showDetails({{ $log->id }}, {{ json_encode($log->old_value) }}, {{ json_encode($log->new_value) }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @else
                                    <span style="color: var(--god-text-muted);">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                {{ $logs->links() }}
            </div>
        @else
            <p style="color: var(--god-text-muted); text-align: center; padding: 50px;">ლოგები არ მოიძებნა</p>
        @endif
    </div>

    <!-- Details Modal -->
    <div id="details-modal" class="god-modal-overlay">
        <div class="god-modal" style="max-width: 700px;">
            <h3 class="god-modal-title">ცვლილების დეტალები</h3>
            <div class="god-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4 style="margin-bottom: 10px; color: var(--god-danger);">ძველი მნიშვნელობა</h4>
                        <pre id="old-value"
                            style="background: var(--god-bg); padding: 15px; border-radius: 8px; font-size: 12px; overflow-x: auto; max-height: 300px;"></pre>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 10px; color: var(--god-success);">ახალი მნიშვნელობა</h4>
                        <pre id="new-value"
                            style="background: var(--god-bg); padding: 15px; border-radius: 8px; font-size: 12px; overflow-x: auto; max-height: 300px;"></pre>
                    </div>
                </div>
            </div>
            <div class="god-modal-actions">
                <button class="god-btn god-btn-secondary" onclick="closeDetailsModal()">დახურვა</button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showDetails(id, oldValue, newValue) {
                document.getElementById('old-value').textContent = JSON.stringify(oldValue, null, 2);
                document.getElementById('new-value').textContent = JSON.stringify(newValue, null, 2);
                document.getElementById('details-modal').classList.add('show');
            }

            function closeDetailsModal() {
                document.getElementById('details-modal').classList.remove('show');
            }
        </script>
    @endpush
@endsection