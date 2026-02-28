@extends('layouts.app')

@section('title', 'პაროლის შეცვლა')

@section('page-header')
<div class="flex items-center gap-4">
    <a href="{{ route('profile.edit') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-white">პაროლის შეცვლა</h1>
        <p class="text-dark-400 mt-1">შეცვალეთ თქვენი პაროლი</p>
    </div>
</div>
@endsection

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="glass-card p-6">
            <form method="POST" action="{{ route('profile.change-password.update') }}" class="space-y-6">
            @csrf
            
            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-dark-300 mb-2">
                    მიმდინარე პაროლი *
                </label>
                <input type="password" 
                       name="current_password" 
                       id="current_password" 
                       class="form-input w-full" 
                       required 
                       autocomplete="current-password">
                @error('current_password')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-dark-300 mb-2">
                    ახალი პაროლი *
                </label>
                <input type="password" 
                       name="password" 
                       id="password" 
                       class="form-input w-full" 
                       required 
                       autocomplete="new-password"
                       minlength="6">
                @error('password')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-dark-300 mb-2">
                    პაროლის დადასტურება *
                </label>
                <input type="password" 
                       name="password_confirmation" 
                       id="password_confirmation" 
                       class="form-input w-full" 
                       required 
                       autocomplete="new-password"
                       minlength="6">
            </div>
            
            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('profile.edit') }}" class="btn-secondary px-4 py-2">
                    გაუქმება
                </a>
                <button type="submit" class="btn-primary px-4 py-2">
                    შენახვა
                </button>
            </div>
        </form>
        </div>
    </div>
</div>
@endsection
