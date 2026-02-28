@extends('god-mode.layout')

@section('title', 'ტრანსპორტირების ტარიფები')

@section('content')
    <div class="god-header">
        <h1 class="god-page-title">
            <i class="fas fa-shipping-fast" style="margin-right: 10px;"></i>
            ტრანსპორტირების ტარიფები
        </h1>
        <div>
            <a href="{{ route('god.shipping-rates.template') }}" class="god-btn god-btn-secondary god-btn-sm">
                <i class="fas fa-download"></i> შაბლონის ჩამოტვირთვა
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="god-stats-grid"
        style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px;">
        <div class="god-stat-card">
            <div class="god-stat-value">{{ $stats['total_users'] }}</div>
            <div class="god-stat-label">მომხმარებლები</div>
        </div>
        <div class="god-stat-card">
            <div class="god-stat-value">{{ $stats['users_with_rates'] }}</div>
            <div class="god-stat-label">ტარიფებით</div>
        </div>
        <div class="god-stat-card">
            <div class="god-stat-value">{{ number_format($stats['total_rates']) }}</div>
            <div class="god-stat-label">სულ ტარიფი</div>
        </div>
        <div class="god-stat-card">
            <div class="god-stat-value">{{ $stats['total_files'] }}</div>
            <div class="god-stat-label">აქტიური ფაილი</div>
        </div>
    </div>

    <!-- Search -->
    <div class="god-card" style="margin-bottom: 20px;">
        <form action="{{ route('god.shipping-rates.index') }}" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" value="{{ $search }}" placeholder="მოძებნეთ მომხმარებლით..." class="god-input"
                style="flex: 1;">
            <button type="submit" class="god-btn god-btn-primary">
                <i class="fas fa-search"></i> ძებნა
            </button>
            @if($search)
                <a href="{{ route('god.shipping-rates.index') }}" class="god-btn god-btn-secondary">
                    <i class="fas fa-times"></i> გასუფთავება
                </a>
            @endif
        </form>
    </div>

    <!-- Users List -->
    <div class="god-card">
        <div class="god-card-header">
            <h3 class="god-card-title">მომხმარებლები</h3>
        </div>

        <table class="god-table">
            <thead>
                <tr>
                    <th>მომხმარებელი</th>
                    <th>როლი</th>
                    <th style="text-align: center;">ტარიფები</th>
                    <th>აქტიური ფაილი</th>
                    <th style="text-align: center;">მოქმედება</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar"
                                    style="width: 36px; height: 36px; background: var(--god-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $user->full_name }}</strong>
                                    <br>
                                    <small style="color: var(--god-text-muted);">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleColors = [
                                    'admin' => 'var(--god-danger)',
                                    'dealer' => 'var(--god-warning)',
                                    'client' => 'var(--god-success)',
                                ];
                            @endphp
                            <span class="god-badge"
                                style="background: {{ $roleColors[$user->role] ?? 'var(--god-text-muted)' }};">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if($user->active_rates_count > 0)
                                <span class="god-badge" style="background: var(--god-success);">
                                    {{ $user->active_rates_count }}
                                </span>
                            @else
                                <span style="color: var(--god-text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($user->shippingFiles->first())
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-file-excel" style="color: #22c55e;"></i>
                                    <span style="font-size: 13px;">{{ $user->shippingFiles->first()->original_name }}</span>
                                </div>
                            @else
                                <span style="color: var(--god-text-muted); font-size: 13px;">ფაილი არ აიტვირთა</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('god.shipping-rates.show', $user) }}" class="god-btn god-btn-primary god-btn-sm">
                                <i class="fas fa-cog"></i> მართვა
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--god-text-muted);">
                            <i class="fas fa-users"
                                style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 15px;"></i>
                            მომხმარებლები არ მოიძებნა
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($users->hasPages())
            <div style="padding: 15px; border-top: 1px solid var(--god-border);">
                {{ $users->appends(['search' => $search])->links() }}
            </div>
        @endif
    </div>

    <style>
        .god-stat-card {
            background: var(--god-card-bg);
            border: 1px solid var(--god-border);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .god-stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--god-primary);
        }

        .god-stat-label {
            font-size: 13px;
            color: var(--god-text-muted);
            margin-top: 5px;
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
    </style>
@endsection