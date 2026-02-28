@extends('layouts.app')

@section('title', 'მთავარი')

@section('content')
<div class="space-y-6">
    
    {{-- Stats Cards --}}
    @if(!auth()->user()->isClient())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Total Cars --}}
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-dark-400 uppercase tracking-wider">სულ მანქანები</p>
                    <p class="text-2xl font-bold text-white mt-2">{{ $stats['total_cars'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                </div>
            </div>
        </div>
        
        {{-- In Transit --}}
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-dark-400 uppercase tracking-wider">გზაშია</p>
                    <p class="text-2xl font-bold text-cyan-400 mt-2">{{ $stats['in_transit'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
        
        {{-- Delivered --}}
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-dark-400 uppercase tracking-wider">გაყვანილი</p>
                    <p class="text-2xl font-bold text-green-400 mt-2">{{ $stats['delivered'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>
        
        {{-- Total Debt --}}
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-dark-400 uppercase tracking-wider">დავალიანება</p>
                    <p class="text-2xl font-bold text-red-400 mt-2">
                        {{ number_format($stats['total_debt'], 0) }}$
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
    </div>
    @endif
    
    {{-- Recent Cars --}}
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">ბოლო მანქანები</h2>
            <a href="{{ route('cars.index') }}" class="text-sm text-primary-400 hover:text-primary-300">ყველა</a>
        </div>
        
        @if($recentCars->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($recentCars as $car)
            <a href="{{ route('cars.show', $car) }}" class="block bg-dark-800/50 rounded-xl overflow-hidden hover:bg-dark-800 transition-colors">
                <div class="aspect-video bg-dark-900 relative">
                    <img src="{{ $car->main_photo_url }}" 
                         alt="{{ $car->make_model }}" 
                         class="w-full h-full object-cover"
                         onerror="this.src='/images/no-photo.png'">
                    <div class="absolute top-2 right-2">
                        <span class="badge-{{ $car->status }} px-2 py-1 rounded-full text-xs">
                            {{ $car->status_label }}
                        </span>
                    </div>
                </div>
                <div class="p-3">
                    <h3 class="font-medium text-white truncate">{{ $car->make_model }}</h3>
                    <p class="text-sm text-dark-400 truncate">{{ $car->vin }}</p>
                    <div class="flex items-center justify-between mt-2 text-xs">
                        <span class="text-green-400">{{ $car->formatted_paid }}</span>
                        <span class="{{ $car->hasDebt() ? 'text-red-400' : 'text-green-400' }}">
                            {{ $car->formatted_debt }}
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <svg class="w-16 h-16 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-dark-400">მანქანები არ არის</p>
            <a href="{{ route('cars.create') }}" class="btn-primary mt-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                დამატება
            </a>
        </div>
        @endif
    </div>
    
    {{-- Quick Actions & Recent Transactions --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Quick Actions --}}
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">სწრაფი მოქმედებები</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('cars.create') }}" class="p-4 rounded-xl bg-primary-500/10 border border-primary-500/20 hover:bg-primary-500/20 transition-colors text-center">
                    <svg class="w-8 h-8 mx-auto text-primary-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-sm text-dark-300">მანქანის დამატება</span>
                </a>
                
                <a href="{{ route('calculator.index') }}" class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 hover:bg-blue-500/20 transition-colors text-center">
                    <svg class="w-8 h-8 mx-auto text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm text-dark-300">კალკულატორი</span>
                </a>
                
                <a href="{{ route('transactions.create') }}" class="p-4 rounded-xl bg-green-500/10 border border-green-500/20 hover:bg-green-500/20 transition-colors text-center">
                    <svg class="w-8 h-8 mx-auto text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="text-sm text-dark-300">გადახდის დამატება</span>
                </a>
                
                <a href="{{ route('finance.index') }}" class="p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20 hover:bg-yellow-500/20 transition-colors text-center">
                    <svg class="w-8 h-8 mx-auto text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-sm text-dark-300">ფინანსები</span>
                </a>
            </div>
        </div>
        
        {{-- Recent Transactions --}}
        @if(!auth()->user()->isClient())
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">ბოლო გადახდები</h2>
                <a href="{{ route('transactions.index') }}" class="text-sm text-primary-400 hover:text-primary-300">ყველა</a>
            </div>
            
            @if(count($recentTransactions) > 0)
            <div class="space-y-3">
                @foreach($recentTransactions as $transaction)
                <div class="flex items-center justify-between p-3 rounded-lg bg-dark-800/50">
                    <div>
                        <p class="text-white font-medium">{{ $transaction->formatted_amount }}</p>
                        <p class="text-sm text-dark-500">{{ $transaction->car?->make_model ?? 'ბალანსი' }}</p>
                    </div>
                    <span class="text-xs text-dark-400">{{ $transaction->formatted_date }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-center text-dark-500 py-8">გადახდები არ არის</p>
            @endif
        </div>
        @endif
    </div>
    
</div>
@endsection
