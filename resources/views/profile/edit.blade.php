@extends('layouts.app')

@section('title', 'პროფილი')

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-white">პროფილი</h1>
    <p class="text-dark-400 mt-1">პროფილის პარამეტრები</p>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Form -->
    <div class="lg:col-span-2 space-y-6">
        <form method="POST" action="{{ route('profile.update') }}" class="glass-card p-6">
            @csrf
            @method('PUT')
            
            <h2 class="text-lg font-semibold text-white mb-4">პირადი ინფორმაცია</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">მომხმარებლის სახელი</label>
                    <input type="text" value="{{ $user->username }}" class="form-input w-full bg-dark-800" disabled>
                    <p class="text-xs text-dark-500 mt-1">მომხმარებლის სახელის შეცვლა შეუძლებელია</p>
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
            </div>
            
            <div class="flex justify-end mt-6">
                <button type="submit" class="btn-primary">შენახვა</button>
            </div>
        </form>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Profile Card -->
        <div class="glass-card p-6 text-center">
            <div class="w-20 h-20 rounded-full bg-primary-600/20 flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl font-bold text-primary-400">{{ substr($user->username, 0, 1) }}</span>
            </div>
            <h3 class="text-lg font-semibold text-white">{{ $user->full_name ?? $user->username }}</h3>
            <p class="text-dark-400">{{ $user->role_label }}</p>
            <p class="text-sm text-dark-500 mt-2">{{ $user->email }}</p>
        </div>
        
        <!-- Stats -->
        <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">სტატისტიკა</h3>
            
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-dark-400">მანქანები:</dt>
                    <dd class="text-white">{{ $user->cars()->count() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">ბალანსი:</dt>
                    <dd class="text-green-400">{{ $user->getFormattedBalance() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">SMS:</dt>
                    <dd class="{{ $user->sms_enabled ? 'text-green-400' : 'text-dark-500' }}">
                        {{ $user->sms_enabled ? 'ჩართული' : 'გამორთული' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">რეგისტრაცია:</dt>
                    <dd class="text-white">{{ $user->created_at?->format('d.m.Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
