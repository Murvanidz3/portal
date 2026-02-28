@extends('layouts.app')

@section('title', 'გადახდის დამატება')

@section('page-header')
<div class="flex items-center gap-4">
    <a href="{{ route('transactions.index') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h1 class="text-2xl font-bold text-white">გადახდის დამატება</h1>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('transactions.store') }}" class="max-w-xl space-y-6">
    @csrf
    
    <div class="glass-card p-6 space-y-4">
        @if($car)
        <div class="p-3 rounded-lg bg-primary-500/10 border border-primary-500/20">
            <p class="text-sm text-dark-400">მანქანა:</p>
            <p class="text-white font-medium">{{ $car->make_model }} - {{ $car->vin }}</p>
            <p class="text-sm text-dark-400 mt-1">დარჩენილი დავალიანება: <span class="text-red-400">{{ $car->formatted_debt }}</span></p>
        </div>
        <input type="hidden" name="car_id" value="{{ $car->id }}">
        @else
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">მანქანა</label>
            <select name="car_id" class="form-input w-full">
                <option value="">აირჩიეთ მანქანა</option>
                @foreach($cars as $c)
                    <option value="{{ $c->id }}">{{ $c->make_model }} - {{ $c->vin }}</option>
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
                    <option value="{{ $user->id }}">{{ $user->full_name ?? $user->username }}</option>
                @endforeach
            </select>
        </div>
        @endif
        
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">თანხა ($) *</label>
            <input type="number" name="amount" value="{{ old('amount') }}" class="form-input w-full" step="0.01" min="0.01" required>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">თარიღი *</label>
            <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="form-input w-full" required>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">ტიპი *</label>
            <select name="purpose" class="form-input w-full" required>
                @foreach($purposes as $key => $label)
                    <option value="{{ $key }}" {{ old('purpose', 'vehicle_payment') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-dark-300 mb-2">კომენტარი</label>
            <textarea name="comment" rows="2" class="form-input w-full">{{ old('comment') }}</textarea>
        </div>
        
        @if($car || request('car_id'))
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="update_car_paid" value="1" checked
                   class="w-4 h-4 rounded border-dark-600 bg-dark-800 text-primary-600">
            <span class="text-dark-300">განაახლე მანქანის გადახდილი თანხა</span>
        </label>
        @endif
    </div>
    
    <div class="flex justify-end gap-4">
        <a href="{{ route('transactions.index') }}" class="btn-secondary">გაუქმება</a>
        <button type="submit" class="btn-primary">შენახვა</button>
    </div>
</form>
@endsection
