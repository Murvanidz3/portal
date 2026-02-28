@extends('layouts.app')

@section('title', 'ტრანზაქციის რედაქტირება')

@section('page-header')
<div class="flex items-center gap-4">
    <a href="{{ route('transactions.index') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-white">ტრანზაქციის რედაქტირება</h1>
</div>
@endsection

@section('content')
@if($errors->any())
<div class="mb-4 p-4 rounded-lg bg-red-500/10 border border-red-500/20">
    <ul class="text-sm text-red-400 space-y-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('transactions.update', $transaction) }}" class="max-w-xl space-y-6">
    @csrf
    @method('PUT')
    
    <div class="glass-card p-6 space-y-4">
        @if($transaction->car)
        <div class="p-3 rounded-lg bg-primary-500/10 border border-primary-500/20">
            <p class="text-sm text-dark-400">მანქანა:</p>
            <p class="text-white font-medium">{{ $transaction->car->make_model }} - {{ $transaction->car->vin }}</p>
            @if($transaction->car->debt > 0)
            <p class="text-sm text-dark-400 mt-1">დარჩენილი დავალიანება: <span class="text-red-400">{{ number_format($transaction->car->debt, 2) }} $</span></p>
            @endif
        </div>
        <input type="hidden" name="car_id" value="{{ $transaction->car_id }}">
        @else
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">მანქანა</label>
            <select name="car_id" class="form-input w-full">
                <option value="">აირჩიეთ მანქანა</option>
                @foreach($cars as $c)
                    <option value="{{ $c->id }}" {{ old('car_id', $transaction->car_id) == $c->id ? 'selected' : '' }}>
                        {{ $c->make_model }} - {{ $c->vin }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        
        @if(auth()->user()->isAdmin())
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">მომხმარებელი</label>
            <select name="user_id" class="form-input w-full">
                <option value="">აირჩიეთ მომხმარებელი</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id', $transaction->user_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->full_name ?? $user->username }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">თანხა ($) *</label>
            <input type="number" name="amount" value="{{ old('amount', $transaction->amount) }}" class="form-input w-full" step="0.01" min="0.01" required>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">თარიღი *</label>
            <input type="date" name="payment_date" value="{{ old('payment_date', $transaction->payment_date?->format('Y-m-d')) }}" class="form-input w-full" required>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">ტიპი *</label>
            <select name="purpose" class="form-input w-full" required>
                @foreach($purposes as $key => $label)
                    <option value="{{ $key }}" {{ old('purpose', $transaction->purpose) == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">კომენტარი</label>
            <textarea name="comment" rows="2" class="form-input w-full">{{ old('comment', $transaction->comment) }}</textarea>
        </div>
    </div>
    
    <div class="flex justify-end gap-4">
        <a href="{{ route('transactions.index') }}" class="btn-secondary">გაუქმება</a>
        <button type="submit" class="btn-primary">შენახვა</button>
    </div>
</form>
@endsection
