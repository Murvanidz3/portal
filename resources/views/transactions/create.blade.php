@extends('layouts.app')

@section('title', 'გადახდის დამატება')

@section('page-header')
    <div class="flex items-center gap-4">
        <a href="{{ route('transactions.index') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
            <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
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
                    <p class="text-sm text-dark-400 mt-1">დავალიანება: <span
                            class="text-red-400">{{ $car->formatted_debt }}</span></p>
                </div>
                <input type="hidden" name="car_id" value="{{ $car->id }}">
            @else
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '',
                    selectedLabel: '',
                    cars: [
                        @foreach($cars as $c)
                            { id: {{ $c->id }}, label: '{{ addslashes($c->make_model) }} - {{ $c->vin }}', vin: '{{ $c->vin }}', name: '{{ addslashes($c->make_model) }}' },
                        @endforeach
                    ],
                    get filteredCars() {
                        if (!this.search) return this.cars;
                        const q = this.search.toLowerCase();
                        return this.cars.filter(c => c.vin.toLowerCase().includes(q) || c.name.toLowerCase().includes(q));
                    },
                    selectCar(car) {
                        this.selectedId = car.id;
                        this.selectedLabel = car.label;
                        this.search = car.label;
                        this.open = false;
                    },
                    clear() {
                        this.selectedId = '';
                        this.selectedLabel = '';
                        this.search = '';
                    }
                }">
                    <label class="block text-sm font-medium text-dark-300 mb-2">მანქანა</label>
                    <input type="hidden" name="car_id" :value="selectedId">
                    <div class="relative">
                        <div class="relative">
                            <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                @keydown.escape="open = false" @keydown.arrow-down.prevent="open = true"
                                class="form-input w-full pr-10" placeholder="ძიება VIN კოდით ან სახელით..." autocomplete="off">
                            <button type="button" x-show="selectedId" @click="clear()"
                                class="absolute right-8 top-1/2 -translate-y-1/2 text-dark-400 hover:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div class="absolute right-2 top-1/2 -translate-y-1/2 text-dark-500 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 mt-1 w-full max-h-60 overflow-y-auto rounded-xl bg-dark-800 border border-white/10 shadow-xl shadow-black/30"
                            x-cloak>
                            <template x-if="filteredCars.length === 0">
                                <div class="px-4 py-3 text-sm text-dark-400 text-center">მანქანა ვერ მოიძებნა</div>
                            </template>
                            <template x-for="car in filteredCars" :key="car.id">
                                <button type="button" @click="selectCar(car)"
                                    class="w-full text-left px-4 py-3 text-sm hover:bg-primary-500/10 transition-colors flex items-center justify-between"
                                    :class="selectedId === car.id ? 'bg-primary-500/10 text-primary-400' : 'text-white'">
                                    <div>
                                        <span x-text="car.name" class="font-medium"></span>
                                        <span class="text-dark-400 ml-2 text-xs" x-text="car.vin"></span>
                                    </div>
                                    <svg x-show="selectedId === car.id" class="w-4 h-4 text-primary-400 flex-shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
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
                <input type="number" name="amount" value="{{ old('amount') }}" class="form-input w-full" step="0.01"
                    min="0.01" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">თარიღი *</label>
                <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"
                    class="form-input w-full" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-dark-300 mb-2">ტიპი *</label>
                <select name="purpose" class="form-input w-full" required>
                    @foreach($purposes as $key => $label)
                        <option value="{{ $key }}" {{ old('purpose', 'vehicle_payment') == $key ? 'selected' : '' }}>{{ $label }}
                        </option>
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