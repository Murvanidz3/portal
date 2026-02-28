@extends('layouts.app')

@section('title', 'ფინანსები')

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-white">ფინანსები</h1>
    <p class="text-dark-400 mt-1">ფინანსური მიმოხილვა</p>
</div>
@endsection

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-dark-400 text-sm">სულ ღირებულება</p>
        <p class="text-2xl font-bold text-white mt-1">${{ number_format($totals['total_cost'], 2) }}</p>
    </div>
    <div class="stat-card">
        <p class="text-dark-400 text-sm">გადახდილი</p>
        <p class="text-2xl font-bold text-green-400 mt-1">${{ number_format($totals['total_paid'], 2) }}</p>
    </div>
    <div class="stat-card">
        <p class="text-dark-400 text-sm">დარჩენილი დავალიანება</p>
        <p class="text-2xl font-bold text-red-400 mt-1">${{ number_format($totals['total_debt'], 2) }}</p>
    </div>
    <div class="stat-card">
        <p class="text-dark-400 text-sm">სულ მანქანები</p>
        <p class="text-2xl font-bold text-primary-400 mt-1">{{ $totals['total_cars'] }}</p>
    </div>
</div>

<!-- Dealers Overview -->
@if(auth()->user()->isAdmin())
<div class="glass-card p-6 mb-6">
    <h2 class="text-lg font-semibold text-white mb-4">დილერების მიმოხილვა</h2>
    
    <div class="overflow-x-auto">
        <table class="w-full table-dark">
            <thead>
                <tr>
                    <th class="text-left">დილერი</th>
                    <th class="text-right">მანქანები</th>
                    <th class="text-right">სულ ღირებულება</th>
                    <th class="text-right">გადახდილი</th>
                    <th class="text-right">დავალიანება</th>
                    <th class="text-right">მოქმედება</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dealers as $dealer)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary-600/20 flex items-center justify-center">
                                <span class="text-primary-400 font-bold">{{ substr($dealer->username, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ $dealer->full_name ?? $dealer->username }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-right text-dark-300">{{ $dealer->cars_count }}</td>
                    <td class="text-right text-white">${{ number_format($dealer->total_cost, 2) }}</td>
                    <td class="text-right text-green-400">${{ number_format($dealer->total_paid, 2) }}</td>
                    <td class="text-right {{ $dealer->total_debt > 0 ? 'text-red-400' : 'text-green-400' }} font-medium">
                        ${{ number_format($dealer->total_debt, 2) }}
                    </td>
                    <td class="text-right">
                        <a href="{{ route('finance.show', $dealer) }}" class="text-primary-400 hover:text-primary-300">
                            დეტალები
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Recent Transactions -->
<div class="glass-card p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-white">ბოლო ტრანზაქციები</h2>
        <a href="{{ route('transactions.index') }}" class="text-sm text-primary-400 hover:text-primary-300">ყველა</a>
    </div>
    
    @if($recentTransactions->count() > 0)
    <div class="space-y-3">
        @foreach($recentTransactions as $transaction)
        <div class="flex items-center justify-between p-3 rounded-lg bg-dark-800/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-medium">{{ $transaction->formatted_amount }}</p>
                    <p class="text-sm text-dark-500">
                        {{ $transaction->car?->make_model ?? $transaction->user?->full_name ?? 'უცნობი' }}
                        - {{ $transaction->purpose_label }}
                    </p>
                </div>
            </div>
            <span class="text-sm text-dark-400">{{ $transaction->formatted_date }}</span>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-center text-dark-500 py-8">ტრანზაქციები არ არის</p>
    @endif
</div>
@endsection
