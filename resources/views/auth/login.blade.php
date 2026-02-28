@extends('layouts.auth')

@section('title', 'შესვლა')

@section('content')
<div class="glass-card p-8 animate-fade-in">
    
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-blue-600 mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">{{ \App\Models\Setting::getSiteName() }}</h1>
        <p class="text-dark-400 mt-2">შეიყვანეთ თქვენი მონაცემები</p>
    </div>
    
    {{-- Error Message --}}
    @if($errors->any())
    <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
    @endif
    
    {{-- Login Form --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        
        {{-- Username --}}
        <div>
            <label for="username" class="block text-sm font-medium text-dark-300 mb-2">მომხმარებელი</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-dark-500 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}"
                    class="form-input w-full" 
                    placeholder="შეიყვანეთ მომხმარებელი"
                    required 
                    autofocus
                    style="padding-left: 3.75rem !important;"
                >
            </div>
        </div>
        
        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-dark-300 mb-2">პაროლი</label>
            <div class="relative" x-data="{ showPassword: false }">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-dark-500 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <input 
                    :type="showPassword ? 'text' : 'password'" 
                    id="password" 
                    name="password" 
                    class="form-input w-full pr-12" 
                    placeholder="შეიყვანეთ პაროლი"
                    required
                    style="padding-left: 3.75rem !important;"
                >
                <button 
                    type="button" 
                    @click="showPassword = !showPassword" 
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-dark-500 hover:text-dark-300 transition-colors"
                >
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Remember Me --}}
        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-dark-600 bg-dark-800 text-primary-500 focus:ring-primary-500 focus:ring-offset-dark-900">
                <span class="ml-2 text-sm text-dark-400">დამიმახსოვრე</span>
            </label>
        </div>
        
        {{-- Submit Button --}}
        <button type="submit" class="btn-primary w-full py-3 text-center">
            <span class="flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                შესვლა
            </span>
        </button>
        
    </form>
    
</div>

{{-- Footer --}}
<p class="text-center text-dark-500 text-sm mt-6">
    &copy; {{ date('Y') }} {{ \App\Models\Setting::getSiteName() }}. All rights reserved.
</p>
@endsection
