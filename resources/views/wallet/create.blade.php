@extends('layouts.app')

@section('title', 'ბალანსის შევსება')

@section('page-header')
<div class="flex items-center gap-4">
    <a href="{{ route('wallet.index') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-white">ბალანსის შევსება</h1>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('wallet.store') }}" class="max-w-xl space-y-6">
    @csrf

    <div class="glass-card p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">მომხმარებელი *</label>
            <select name="user_id" class="form-input w-full" required>
                <option value="">აირჩიეთ მომხმარებელი</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->full_name ?? $user->username }} {{ $user->username !== ($user->full_name ?? '') ? ' (' . $user->username . ')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('user_id')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">თანხა ($) *</label>
            <input type="number" name="amount" value="{{ old('amount') }}" class="form-input w-full" step="0.01" min="0.01" required placeholder="0.00">
            @error('amount')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">კომენტარი</label>
            <textarea name="comment" rows="3" class="form-input w-full" placeholder="ბალანსის შევსება">{{ old('comment') }}</textarea>
            @error('comment')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex justify-end gap-4">
        <a href="{{ route('wallet.index') }}" class="btn-secondary">გაუქმება</a>
        <button type="submit" class="btn-primary">შევსება</button>
    </div>
</form>
@endsection
