@extends('layouts.app')

@section('title', 'რედაქტირება - ' . ($user->full_name ?? $user->username))

@section('page-header')
<div class="flex items-center gap-4">
    <a href="{{ route('users.index') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-white">{{ $user->full_name ?? $user->username }}</h1>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('users.update', $user) }}" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')
    
    <div class="glass-card p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">მომხმარებლის სახელი *</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-input w-full" required>
                @error('username') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">სრული სახელი</label>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="form-input w-full">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input w-full">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">ტელეფონი</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input w-full">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">ახალი პაროლი</label>
                <input type="password" name="password" class="form-input w-full" placeholder="დატოვეთ ცარიელი თუ არ გსურთ შეცვლა">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">პაროლის დადასტურება</label>
                <input type="password" name="password_confirmation" class="form-input w-full">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">როლი *</label>
                <select name="role" class="form-input w-full" required>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">ბალანსი ($)</label>
                <input type="number" name="balance" value="{{ old('balance', $user->balance) }}" class="form-input w-full" step="0.01" min="0">
            </div>
            
            <div class="md:col-span-2 flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="sms_enabled" value="1" {{ old('sms_enabled', $user->sms_enabled) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-dark-600 bg-dark-800 text-primary-600">
                    <span class="text-dark-300">SMS ჩართული</span>
                </label>
                
                @if($user->role !== 'admin')
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="approved" value="1" {{ old('approved', $user->approved) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-dark-600 bg-dark-800 text-primary-600">
                    <span class="text-dark-300">დადასტურებული</span>
                </label>
                @else
                <div class="flex items-center gap-2">
                    <span class="text-green-400 font-medium">აქტიური (ადმინისტრატორი)</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="flex justify-end gap-4">
        <a href="{{ route('users.index') }}" class="btn-secondary">გაუქმება</a>
        <button type="submit" class="btn-primary">შენახვა</button>
    </div>
</form>
@endsection
