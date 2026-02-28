@extends('god-mode.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="god-header">
        <h1 class="god-page-title">Dashboard</h1>
        <div class="god-user-info">
            <span class="god-user-name">{{ auth('god')->user()->full_name ?? auth('god')->user()->username }}</span>
            <form action="{{ route('god.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="god-btn god-btn-secondary god-btn-sm">
                    <i class="fas fa-sign-out-alt"></i> გასვლა
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="god-grid god-grid-4" style="margin-bottom: 30px;">
        <div class="god-stat">
            <div class="god-stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="god-stat-label">მომხმარებლები</div>
        </div>
        <div class="god-stat">
            <div class="god-stat-value">{{ number_format($stats['total_cars']) }}</div>
            <div class="god-stat-label">მანქანები</div>
        </div>
        <div class="god-stat">
            <div class="god-stat-value">{{ number_format($stats['total_transactions']) }}</div>
            <div class="god-stat-label">ტრანზაქციები</div>
        </div>
        <div class="god-stat">
            <div class="god-stat-value">{{ number_format($stats['cars_in_transit']) }}</div>
            <div class="god-stat-label">გზაში მყოფი</div>
        </div>
    </div>

    <div class="god-grid god-grid-2">
        <!-- Quick Actions -->
        <div class="god-card">
            <div class="god-card-header">
                <h3 class="god-card-title">სწრაფი მოქმედებები</h3>
            </div>
            <div style="display: grid; gap: 15px;">
                <a href="{{ route('god.permissions') }}" class="god-btn god-btn-secondary"
                    style="justify-content: flex-start;">
                    <i class="fas fa-key"></i> უფლებების მართვა
                </a>
                <a href="{{ route('god.styles') }}" class="god-btn god-btn-secondary" style="justify-content: flex-start;">
                    <i class="fas fa-palette"></i> სტილების რედაქტირება
                </a>
                <a href="{{ route('god.audit-logs') }}" class="god-btn god-btn-secondary"
                    style="justify-content: flex-start;">
                    <i class="fas fa-scroll"></i> Audit Logs ნახვა
                </a>
            </div>
        </div>

        <!-- System Overview -->
        <div class="god-card">
            <div class="god-card-header">
                <h3 class="god-card-title">სისტემის მიმოხილვა</h3>
            </div>
            <table class="god-table">
                <tr>
                    <td>დილერები</td>
                    <td style="text-align: right;"><strong>{{ $stats['dealers_count'] }}</strong></td>
                </tr>
                <tr>
                    <td>კლიენტები</td>
                    <td style="text-align: right;"><strong>{{ $stats['clients_count'] }}</strong></td>
                </tr>
                <tr>
                    <td>უფლებების ჯგუფები</td>
                    <td style="text-align: right;"><strong>{{ $permissionGroups }}</strong></td>
                </tr>
                <tr>
                    <td>სტილების ჯგუფები</td>
                    <td style="text-align: right;"><strong>{{ $styleGroups }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="god-card" style="margin-top: 20px;">
        <div class="god-card-header">
            <h3 class="god-card-title">ბოლო აქტივობა</h3>
            <a href="{{ route('god.audit-logs') }}" class="god-btn god-btn-sm god-btn-secondary">ყველას ნახვა</a>
        </div>
        @if($recentLogs->count() > 0)
            <table class="god-table">
                <thead>
                    <tr>
                        <th>მოქმედება</th>
                        <th>ადმინი</th>
                        <th>IP</th>
                        <th>თარიღი</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs as $log)
                        <tr>
                            <td>{{ $log->action_label }}</td>
                            <td>{{ $log->superAdmin->username ?? 'N/A' }}</td>
                            <td><code>{{ $log->ip_address }}</code></td>
                            <td>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: var(--god-text-muted); text-align: center; padding: 30px;">აქტივობა არ არის</p>
        @endif
    </div>
@endsection