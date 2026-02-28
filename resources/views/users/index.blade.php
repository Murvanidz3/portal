@extends('layouts.app')

@section('title', 'მომხმარებლები')

@section('page-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white">მომხმარებლები</h1>
        <p class="text-dark-400 mt-1">სულ: {{ $users->total() }} მომხმარებელი</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn-primary inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        დამატება
    </a>
</div>
@endsection

@section('content')
<!-- Filters -->
<div class="glass-card p-4 mb-6">
    <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="ძიება..." class="form-input w-full">
        </div>
        
        <select name="role" class="form-input">
            <option value="">ყველა როლი</option>
            @foreach($roles as $key => $label)
                <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        
        <select name="approved" class="form-input">
            <option value="">ყველა სტატუსი</option>
            <option value="1" {{ request('approved') === '1' ? 'selected' : '' }}>აქტიური</option>
            <option value="0" {{ request('approved') === '0' ? 'selected' : '' }}>დაბლოკილი</option>
        </select>
        
        <button type="submit" class="btn-secondary">ძიება</button>
    </form>
</div>

<!-- Role Tabs -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('users.index') }}" 
       class="px-4 py-2 rounded-lg text-sm font-medium {{ !request('role') ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300' }}">
        ყველა ({{ array_sum($roleCounts) }})
    </a>
    @foreach($roles as $key => $label)
        <a href="{{ route('users.index', ['role' => $key]) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium {{ request('role') == $key ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300' }}">
            {{ $label }} ({{ $roleCounts[$key] ?? 0 }})
        </a>
    @endforeach
</div>

<!-- Users Table -->
<div class="glass-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full table-dark">
            <thead>
                <tr>
                    <th class="text-left">მომხმარებელი</th>
                    <th class="text-left">როლი</th>
                    <th class="text-left">ტელეფონი</th>
                    <th class="text-right">ბალანსი</th>
                    <th class="text-center">SMS</th>
                    <th class="text-center">სტატუსი</th>
                    <th class="text-right">მოქმედებები</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary-600/20 flex items-center justify-center">
                                <span class="text-primary-400 font-bold">{{ substr($user->username, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ $user->full_name ?? $user->username }}</p>
                                <p class="text-sm text-dark-500">{{ $user->username }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                            {{ $user->role == 'admin' ? 'bg-red-500/20 text-red-400' : ($user->role == 'dealer' ? 'bg-blue-500/20 text-blue-400' : 'bg-green-500/20 text-green-400') }}">
                            {{ $user->role_label }}
                        </span>
                    </td>
                    <td class="text-dark-300">{{ $user->phone ?? '-' }}</td>
                    <td class="text-right text-white font-medium">{{ $user->getFormattedBalance() }}</td>
                    <td class="text-center">
                        <button onclick="toggleSms({{ $user->id }})" 
                                class="p-1 rounded {{ $user->sms_enabled ? 'text-green-400' : 'text-dark-500' }}"
                                id="sms-btn-{{ $user->id }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                            </svg>
                        </button>
                    </td>
                    <td class="text-center">
                        @if($user->role === 'admin')
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                აქტიური
                            </span>
                        @else
                            <button onclick="toggleApproval({{ $user->id }})"
                                    class="px-2 py-1 rounded-full text-xs font-medium {{ $user->approved ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}"
                                    id="approval-btn-{{ $user->id }}">
                                {{ $user->approved ? 'აქტიური' : 'დაბლოკილი' }}
                            </button>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('users.edit', $user) }}" class="p-2 text-dark-400 hover:text-primary-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                  onsubmit="return confirm('ნამდვილად გსურთ წაშლა?')">
                                @csrf
                                @method('DELETE')
                                <button class="p-2 text-dark-400 hover:text-red-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-dark-500">მომხმარებლები ვერ მოიძებნა</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $users->links() }}</div>

@push('scripts')
<script>
function toggleApproval(userId) {
    fetch(`/users/${userId}/toggle-approval`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const btn = document.getElementById(`approval-btn-${userId}`);
            btn.textContent = data.approved ? 'აქტიური' : 'დაბლოკილი';
            btn.className = `px-2 py-1 rounded-full text-xs font-medium ${data.approved ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'}`;
        }
    });
}

function toggleSms(userId) {
    fetch(`/users/${userId}/toggle-sms`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const btn = document.getElementById(`sms-btn-${userId}`);
            btn.className = `p-1 rounded ${data.sms_enabled ? 'text-green-400' : 'text-dark-500'}`;
        }
    });
}
</script>
@endpush
@endsection
