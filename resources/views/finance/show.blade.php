@extends('layouts.app')

@section('title', ($dealer->full_name ?? $dealer->username) . ' - ფინანსები')

@section('page-header')
    <div class="flex items-center gap-4">
        <a href="{{ route('finance.index') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
            <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $dealer->full_name ?? $dealer->username }}</h1>
            <p class="text-dark-400 mt-1">ფინანსური მიმოხილვა</p>
        </div>
    </div>
@endsection

@section('content')
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-dark-400 text-sm">სულ ღირებულება</p>
            <p class="text-2xl font-bold text-white mt-1">${{ number_format($summary['total_cost'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-dark-400 text-sm">გადახდილი</p>
            <p class="text-2xl font-bold text-green-400 mt-1">${{ number_format($summary['total_paid'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-dark-400 text-sm">დარჩენილი დავალიანება</p>
            <p class="text-2xl font-bold text-red-400 mt-1">${{ number_format($summary['total_debt'], 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-dark-400 text-sm">მანქანები ვალით</p>
            <p class="text-2xl font-bold text-primary-400 mt-1">{{ $summary['cars_with_debt'] }}</p>
        </div>
    </div>

    <!-- Cars Table -->
    <div class="glass-card p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">მანქანები</h2>

        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr>
                        <th class="text-left">მანქანა</th>
                        <th class="text-left">VIN</th>
                        <th class="text-right">ფასი</th>
                        <th class="text-right">ტრანსპორტირება</th>
                        <th class="text-right">სულ</th>
                        <th class="text-right">გადახდილი</th>
                        <th class="text-right">ნაშთი</th>
                        <th class="text-center">სტატუსი</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cars as $car)
                        <tr>
                            <td>
                                <a href="{{ route('cars.show', $car) }}"
                                    class="text-primary-400 hover:text-primary-300 font-medium">
                                    {{ $car->make_model }}
                                </a>
                            </td>
                            <td class="font-mono text-sm text-dark-300">{{ $car->vin }}</td>
                            <td class="text-right text-white">${{ number_format($car->vehicle_cost, 2) }}</td>
                            <td class="text-right text-white">${{ number_format($car->shipping_cost, 2) }}</td>
                            <td class="text-right text-white font-medium">${{ number_format($car->total_cost, 2) }}</td>
                            <td class="text-right text-green-400">${{ number_format($car->paid_amount, 2) }}</td>
                            <td
                                class="text-right {{ $car->calculated_debt > 0 ? 'text-red-400' : 'text-green-400' }} font-medium">
                                ${{ number_format($car->calculated_debt, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge-{{ $car->status }} px-2 py-1 rounded-full text-xs">
                                    {{ $car->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-dark-500 py-8">მანქანები არ არის</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transactions -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">ტრანზაქციების ისტორია</h2>

        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full table-dark">
                    <thead>
                        <tr>
                            <th class="text-left">თარიღი</th>
                            <th class="text-left">მანქანა</th>
                            <th class="text-left">ტიპი</th>
                            <th class="text-right">თანხა</th>
                            <th class="text-left">კომენტარი</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td class="text-dark-300">{{ $transaction->payment_date->format('d.m.Y') }}</td>
                                <td>
                                    @if($transaction->car)
                                        <a href="{{ route('cars.show', $transaction->car) }}"
                                            class="text-primary-400 hover:text-primary-300">
                                            {{ $transaction->car->make_model }}
                                        </a>
                                    @else
                                        <span class="text-dark-500">—</span>
                                    @endif
                                </td>
                                <td class="text-dark-300">{{ $transaction->purpose_label }}</td>
                                <td class="text-right text-green-400 font-medium">${{ number_format($transaction->amount, 2) }}</td>
                                <td class="text-dark-400 text-sm">{{ $transaction->comment ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @else
            <p class="text-center text-dark-500 py-8">ტრანზაქციები არ არის</p>
        @endif
    </div>
@endsection