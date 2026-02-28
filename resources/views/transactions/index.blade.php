@extends('layouts.app')

@section('title', 'ტრანზაქციები')

@section('page-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white">ტრანზაქციები</h1>
        <p class="text-dark-400 mt-1">ამ თვეში: ${{ number_format($totals['this_month'], 2) }}</p>
    </div>
    <a href="{{ route('transactions.create') }}" class="btn-primary inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        გადახდის დამატება
    </a>
</div>
@endsection

@section('content')
<!-- Filters -->
<div class="glass-card p-4 mb-6">
    <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="ძიება..." class="form-input flex-1 min-w-[200px]">
        
        <select name="purpose" class="form-input">
            <option value="">ყველა ტიპი</option>
            @foreach($purposes as $key => $label)
                <option value="{{ $key }}" {{ request('purpose') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input" placeholder="დან">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input" placeholder="მდე">
        
        <button type="submit" class="btn-secondary">ძიება</button>
    </form>
</div>

<!-- Transactions Table -->
<div class="glass-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full table-dark">
            <thead>
                <tr>
                    <th class="text-left">თარიღი</th>
                    <th class="text-left">მანქანა/მომხმარებელი</th>
                    <th class="text-left">ტიპი</th>
                    <th class="text-right">თანხა</th>
                    <th class="text-left">კომენტარი</th>
                    <th class="text-right">მოქმედება</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td class="text-dark-300">{{ $transaction->formatted_date }}</td>
                    <td>
                        @if($transaction->car)
                            <a href="{{ route('cars.show', $transaction->car) }}" class="text-primary-400 hover:text-primary-300">
                                {{ $transaction->car->make_model }}
                            </a>
                        @elseif($transaction->user)
                            <span class="text-white">{{ $transaction->user->full_name ?? $transaction->user->username }}</span>
                        @else
                            <span class="text-dark-500">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-primary-500/20 text-primary-400">
                            {{ $transaction->purpose_label }}
                        </span>
                    </td>
                    <td class="text-right text-green-400 font-medium">{{ $transaction->formatted_amount }}</td>
                    <td class="text-dark-400 text-sm">{{ Str::limit($transaction->comment, 30) }}</td>
                    <td class="text-right">
                        @if(auth()->user()->isAdmin())
                        <div class="flex items-center justify-end gap-2" x-data="{ showDeleteConfirm: false }">
                            <a href="{{ route('transactions.edit', $transaction) }}" class="p-2 text-dark-400 hover:text-primary-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button type="button" 
                                    @click="showDeleteConfirm = true"
                                    class="p-2 text-dark-400 hover:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            
                            <!-- Delete Confirmation Modal -->
                            <div x-show="showDeleteConfirm" 
                                 x-cloak
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
                                 @click.self="showDeleteConfirm = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                <div class="bg-dark-800 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-white">დადასტურება</h3>
                                            <p class="text-dark-400 text-sm mt-1">ნამდვილად გსურთ ტრანზაქციის წაშლა?</p>
                                            <p class="text-dark-500 text-xs mt-2">ეს ოპერაცია განაახლებს ფინანსურ ბალანსებს!</p>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="mt-6">
                                        @csrf
                                        @method('DELETE')
                                        <div class="flex items-center justify-end gap-3">
                                            <button type="button" 
                                                    @click="showDeleteConfirm = false"
                                                    class="px-4 py-2 rounded-lg bg-dark-700 text-dark-300 hover:bg-dark-600 transition-colors">
                                                არა
                                            </button>
                                            <button type="submit" 
                                                    class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 transition-colors">
                                                კი, წაშლა
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-dark-500">ტრანზაქციები ვერ მოიძებნა</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $transactions->links() }}</div>
@endsection
