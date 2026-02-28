@extends('layouts.app')

@section('title', 'რედაქტირება - ' . $car->make_model)

@section('page-header')
<div class="flex items-center gap-4">
    <a href="{{ route('cars.show', $car) }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-white">{{ $car->make_model }}</h1>
        <p class="text-dark-400 mt-1">რედაქტირება | {{ $car->vin }}</p>
    </div>
</div>
@endsection

@section('content')
@php
    // Helper to get make and model from car
    $carMake = old('make', $car->make ?? (explode(' ', $car->make_model ?? '', 2)[0] ?? ''));
    $carModel = old('model', $car->model ?? (isset(explode(' ', $car->make_model ?? '', 2)[1]) ? explode(' ', $car->make_model, 2)[1] : ''));
@endphp

<form method="POST" action="{{ route('cars.update', $car) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    
    <!-- Validation Errors -->
    @if($errors->any())
        <div class="glass-card p-4 bg-red-500/10 border border-red-500/20">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-red-400 font-semibold mb-2">გთხოვთ გამასწოროთ შემდეგი შეცდომები:</h3>
                    <ul class="list-disc list-inside text-sm text-red-300 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    
    <!-- ინფორმაცია მანქანის შესახებ -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
            </svg>
            ინფორმაცია მანქანის შესახებ
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Make -->
            <div>
                <label for="make" class="block text-sm font-medium text-dark-300 mb-2">
                    მწარმოებელი <span class="text-red-400">*</span>
                </label>
                <input type="text" name="make" id="make" value="{{ $carMake }}" 
                       class="form-input w-full" required>
                @error('make')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Model -->
            <div>
                <label for="model" class="block text-sm font-medium text-dark-300 mb-2">
                    მოდელი <span class="text-red-400">*</span>
                </label>
                <input type="text" name="model" id="model" value="{{ $carModel }}" 
                       class="form-input w-full" required>
                @error('model')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Year -->
            <div>
                <label for="year" class="block text-sm font-medium text-dark-300 mb-2">
                    წელი <span class="text-red-400">*</span>
                </label>
                <input type="number" name="year" id="year" value="{{ old('year', $car->year) }}" 
                       class="form-input w-full" min="1900" max="{{ date('Y') + 1 }}" required>
                @error('year')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- VIN -->
            <div>
                <label for="vin" class="block text-sm font-medium text-dark-300 mb-2">
                    VIN Code <span class="text-red-400">*</span>
                </label>
                <input type="text" name="vin" id="vin" value="{{ old('vin', $car->vin) }}" 
                       class="form-input w-full uppercase" maxlength="17" required>
                @error('vin')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Lot Number -->
            <div>
                <label for="lot_number" class="block text-sm font-medium text-dark-300 mb-2">
                    Lot ნომერი <span class="text-red-400">*</span>
                </label>
                <input type="text" name="lot_number" id="lot_number" value="{{ old('lot_number', $car->lot_number) }}" 
                       class="form-input w-full" required>
                @error('lot_number')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Auction Name -->
            <div>
                <label for="auction_name" class="block text-sm font-medium text-dark-300 mb-2">
                    აუქციონის სახელი <span class="text-red-400">*</span>
                </label>
                <select name="auction_name" id="auction_name" class="form-input w-full" required>
                    <option value="">აირჩიეთ</option>
                    <option value="COPART" {{ old('auction_name', $car->auction_name) == 'COPART' ? 'selected' : '' }}>COPART</option>
                    <option value="IAAI" {{ old('auction_name', $car->auction_name) == 'IAAI' ? 'selected' : '' }}>IAAI</option>
                    <option value="Manheim" {{ old('auction_name', $car->auction_name) == 'Manheim' ? 'selected' : '' }}>Manheim</option>
                    <option value="Other" {{ old('auction_name', $car->auction_name) == 'Other' ? 'selected' : '' }}>სხვა</option>
                </select>
                @error('auction_name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Purchase Date -->
            <div>
                <label for="purchase_date" class="block text-sm font-medium text-dark-300 mb-2">შეძენის თარიღი</label>
                <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', $car->purchase_date?->format('Y-m-d')) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Arrival Date -->
            <div>
                <label for="arrival_date" class="block text-sm font-medium text-dark-300 mb-2">სავარაუდო ჩამოსვლის თარიღი</label>
                <input type="date" name="arrival_date" id="arrival_date" value="{{ old('arrival_date', $car->arrival_date?->format('Y-m-d')) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Document Received At -->
            <div>
                <label for="document_received_at" class="block text-sm font-medium text-dark-300 mb-2">საწყობში საბუთის მიღება</label>
                <input type="date" name="document_received_at" id="document_received_at" value="{{ old('document_received_at', $car->document_received_at?->format('Y-m-d')) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Document Issued At -->
            <div>
                <label for="document_issued_at" class="block text-sm font-medium text-dark-300 mb-2">საბუთის გაცემის თარიღი</label>
                <input type="date" name="document_issued_at" id="document_issued_at" value="{{ old('document_issued_at', $car->document_issued_at?->format('Y-m-d')) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Dealer -->
            @if(auth()->user()->isAdmin() && $dealers->count() > 1)
            <div>
                <label for="user_id" class="block text-sm font-medium text-dark-300 mb-2">დილერი</label>
                <select name="user_id" id="user_id" class="form-input w-full">
                    @foreach($dealers as $dealer)
                        <option value="{{ $dealer->id }}" {{ old('user_id', $car->user_id) == $dealer->id ? 'selected' : '' }}>
                            {{ $dealer->full_name ?? $dealer->username }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-dark-300 mb-2">სტატუსი <span class="text-red-400">*</span></label>
                <select name="status" id="status" class="form-input w-full" required>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ old('status', $car->status) == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    
    <!-- ინფორმაცია ტრანსპორტირებაზე -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            ინფორმაცია ტრანსპორტირებაზე
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Container Number -->
            <div>
                <label for="container_number" class="block text-sm font-medium text-dark-300 mb-2">კონტეინერი</label>
                <input type="text" name="container_number" id="container_number" value="{{ old('container_number', $car->container_number) }}" 
                       class="form-input w-full uppercase">
            </div>
            
            <!-- Booking Number -->
            <div>
                <label for="booking_number" class="block text-sm font-medium text-dark-300 mb-2">ბუქინგის ნომერი</label>
                <input type="text" name="booking_number" id="booking_number" value="{{ old('booking_number', $car->booking_number) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Shipping Line -->
            <div>
                <label for="shipping_line" class="block text-sm font-medium text-dark-300 mb-2">საზღვაო ხაზი</label>
                <input type="text" name="shipping_line" id="shipping_line" value="{{ old('shipping_line', $car->shipping_line) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Auction Location (გამშვები პორტი) -->
            <div>
                <label for="auction_location" class="block text-sm font-medium text-dark-300 mb-2">გამშვები პორტი</label>
                <input type="text" name="auction_location" id="auction_location" value="{{ old('auction_location', $car->auction_location) }}" 
                       class="form-input w-full" placeholder="მაგ: CA-LOS ANGELES">
            </div>
            
            <!-- Vessel -->
            <div>
                <label for="vessel" class="block text-sm font-medium text-dark-300 mb-2">გემი</label>
                <input type="text" name="vessel" id="vessel" value="{{ old('vessel', $car->vessel) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Loading Date -->
            <div>
                <label for="loading_date" class="block text-sm font-medium text-dark-300 mb-2">ჩატვირთვის თარიღი</label>
                <input type="date" name="loading_date" id="loading_date" value="{{ old('loading_date', $car->loading_date?->format('Y-m-d')) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Estimated Arrival Date -->
            <div>
                <label for="estimated_arrival_date" class="block text-sm font-medium text-dark-300 mb-2">შემოსვლის სავარაუდო თარიღი</label>
                <input type="date" name="estimated_arrival_date" id="estimated_arrival_date" value="{{ old('estimated_arrival_date', $car->estimated_arrival_date?->format('Y-m-d')) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Terminal -->
            <div>
                <label for="terminal" class="block text-sm font-medium text-dark-300 mb-2">ტერმინალი</label>
                <input type="text" name="terminal" id="terminal" value="{{ old('terminal', $car->terminal) }}" 
                       class="form-input w-full">
            </div>
        </div>
    </div>
    
    <!-- ინფორმაცია ავტომობილის მიმღებზე -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            ინფორმაცია ავტომობილის მიმღებზე
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Client User -->
            @if($clients->count() > 0)
            <div>
                <label for="client_user_id" class="block text-sm font-medium text-dark-300 mb-2">კლიენტი (სისტემიდან)</label>
                <select name="client_user_id" id="client_user_id" class="form-input w-full">
                    <option value="">აირჩიეთ კლიენტი</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_user_id', $car->client_user_id) == $client->id ? 'selected' : '' }}>
                            {{ $client->full_name ?? $client->username }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <!-- Client Name -->
            <div>
                <label for="client_name" class="block text-sm font-medium text-dark-300 mb-2">სახელი და გვარი</label>
                <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $car->client_name) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Client ID Number -->
            <div>
                <label for="client_id_number" class="block text-sm font-medium text-dark-300 mb-2">პირადი ნომერი</label>
                <input type="text" name="client_id_number" id="client_id_number" value="{{ old('client_id_number', $car->client_id_number) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Client Phone -->
            <div>
                <label for="client_phone" class="block text-sm font-medium text-dark-300 mb-2">ტელეფონი</label>
                <input type="text" name="client_phone" id="client_phone" value="{{ old('client_phone', $car->client_phone) }}" 
                       class="form-input w-full">
            </div>
            
            <!-- Dealer Phone -->
            <div>
                <label for="dealer_phone" class="block text-sm font-medium text-dark-300 mb-2">დილერის ტელეფონი</label>
                <input type="text" name="dealer_phone" id="dealer_phone" value="{{ old('dealer_phone', $car->dealer_phone) }}" 
                       class="form-input w-full">
            </div>
        </div>
    </div>
    
    <!-- ინფორმაცია ფინანსებზე -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            ინფორმაცია ფინანსებზე
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Vehicle Cost -->
            <div>
                <label for="vehicle_cost" class="block text-sm font-medium text-dark-300 mb-2">
                    აუქციონის ღირებულება ($) <span class="text-red-400">*</span>
                </label>
                <input type="number" name="vehicle_cost" id="vehicle_cost" value="{{ old('vehicle_cost', $car->vehicle_cost) }}" 
                       class="form-input w-full" step="0.01" min="0" required>
                @error('vehicle_cost')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Dealer Profit -->
            <div>
                <label for="dealer_profit" class="block text-sm font-medium text-dark-300 mb-2">დილერის მოგება ($)</label>
                <input type="number" name="dealer_profit" id="dealer_profit" value="{{ old('dealer_profit', $car->dealer_profit ?? 0) }}" 
                       class="form-input w-full" step="0.01" min="0">
            </div>
            
            <!-- Discount -->
            <div>
                <label for="discount" class="block text-sm font-medium text-dark-300 mb-2">ფასდაკლება ($)</label>
                <input type="number" name="discount" id="discount" value="{{ old('discount', $car->discount ?? 0) }}" 
                       class="form-input w-full" step="0.01" min="0">
            </div>
            
            <!-- Additional Cost -->
            <div>
                <label for="additional_cost" class="block text-sm font-medium text-dark-300 mb-2">დამატებითი ხარჯები ($)</label>
                <input type="number" name="additional_cost" id="additional_cost" value="{{ old('additional_cost', $car->additional_cost ?? 0) }}" 
                       class="form-input w-full" step="0.01" min="0">
            </div>
            
            <!-- Transfer Commission -->
            <div>
                <label for="transfer_commission" class="block text-sm font-medium text-dark-300 mb-2">გადარიცხვის საკომისიო ($)</label>
                <input type="number" name="transfer_commission" id="transfer_commission" value="{{ old('transfer_commission', $car->transfer_commission ?? 0) }}" 
                       class="form-input w-full" step="0.01" min="0">
            </div>
            
            <!-- Shipping Cost -->
            <div>
                <label for="shipping_cost" class="block text-sm font-medium text-dark-300 mb-2">
                    ტრანსპორტირების ფასი ($) <span class="text-red-400">*</span>
                </label>
                <input type="number" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost', $car->shipping_cost) }}" 
                       class="form-input w-full" step="0.01" min="0" required>
                @error('shipping_cost')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Paid Amount -->
            <div>
                <label for="paid_amount" class="block text-sm font-medium text-dark-300 mb-2">გადახდილი ($)</label>
                <input type="number" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', $car->paid_amount ?? 0) }}" 
                       class="form-input w-full" step="0.01" min="0">
            </div>
            
            <!-- Remaining / Plus balance -->
            <div class="flex items-end">
                <div class="p-3 rounded-lg bg-dark-800 w-full">
                    @if($car->hasOverpayment())
                        <span class="text-dark-400 text-sm">პლიუს ბალანსი:</span>
                        <span class="text-lg font-bold text-green-400 ml-2">
                            {{ $car->formatted_overpayment }}
                        </span>
                    @else
                        <span class="text-dark-400 text-sm">დარჩენილი:</span>
                        <span class="text-lg font-bold {{ $car->hasDebt() ? 'text-red-400' : 'text-green-400' }} ml-2">
                            {{ $car->formatted_debt }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Photos -->
    <div class="glass-card p-6" x-data="{ 
        selectedCount: 0, 
        selectedFiles: [], 
        compressing: false,
        handlePhotoSelection(event) {
            this.selectedCount = event.target.files.length;
            this.selectedFiles = Array.from(event.target.files);
        }
    }">
        <h2 class="text-lg font-semibold text-white mb-4">ფოტოების დამატება</h2>
        
        <div class="mb-4">
            <label for="photo_category" class="block text-sm font-medium text-dark-300 mb-2">კატეგორია</label>
            <select name="photo_category" id="photo_category" class="form-input w-48">
                <option value="auction">აუქციონი</option>
                <option value="pickup">აყვანა</option>
                <option value="warehouse">საწყობი</option>
                <option value="poti">ფოთი</option>
            </select>
        </div>
        
        <div class="border-2 border-dashed rounded-lg p-8 text-center transition-colors"
             :class="selectedCount > 0 ? 'border-primary-500 bg-primary-500/5' : 'border-dark-600 hover:border-primary-500'">
            <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="hidden"
                   @change="handlePhotoSelection($event)">
            <label for="photos" class="cursor-pointer">
                <template x-if="selectedCount === 0">
                    <div>
                        <svg class="w-12 h-12 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-dark-300 mb-2">აირჩიეთ ახალი ფოტოები</p>
                        <p class="text-dark-500 text-sm">დააჭირეთ ან ჩააგდეთ ფოტოები</p>
                    </div>
                </template>
                <template x-if="selectedCount > 0">
                    <div>
                        <svg class="w-12 h-12 mx-auto text-primary-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-primary-400 font-medium mb-1">
                            <span x-text="selectedCount"></span> ფოტო არჩეულია
                        </p>
                        <p class="text-dark-400 text-sm">დააჭირეთ სხვა ფოტოების ასარჩევად</p>
                    </div>
                </template>
            </label>
        </div>
        
        <!-- Selected files preview -->
        <div x-show="selectedCount > 0" x-cloak class="mt-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-dark-400">არჩეული ფოტოები:</span>
                <button type="button" 
                        @click="document.getElementById('photos').value = ''; selectedCount = 0; selectedFiles = []"
                        class="text-xs text-red-400 hover:text-red-300">
                    გასუფთავება
                </button>
            </div>
            <div class="flex flex-wrap gap-2">
                <template x-for="(file, index) in selectedFiles.slice(0, 8)" :key="index">
                    <div class="px-2 py-1 bg-dark-800 rounded text-xs text-dark-300 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name"></span>
                    </div>
                </template>
                <template x-if="selectedFiles.length > 8">
                    <div class="px-2 py-1 bg-primary-500/20 rounded text-xs text-primary-400">
                        +<span x-text="selectedFiles.length - 8"></span> სხვა
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Existing Photos -->
        @if($car->files->count() > 0)
        <div class="mt-6">
            <h3 class="text-sm font-medium text-dark-300 mb-3">არსებული ფოტოები</h3>
            <div class="grid grid-cols-6 gap-2">
                @foreach($car->files as $file)
                <div class="relative aspect-video bg-dark-800 rounded-lg overflow-hidden group">
                    <img src="{{ $file->url }}" alt="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button type="button" 
                                onclick="setMainPhoto({{ $file->id }})"
                                class="p-1 bg-primary-500 rounded text-white text-xs"
                                title="მთავარი ფოტო">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        <button type="button" 
                                onclick="deletePhoto({{ $file->id }})"
                                class="p-1 bg-red-500 rounded text-white text-xs"
                                title="წაშლა">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <span class="absolute bottom-1 left-1 text-xs bg-black/50 px-1 rounded">
                        {{ $file->category_label }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    
    <!-- Notes -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">შენიშვნები</h2>
        <textarea name="notes" id="notes" rows="3" class="form-input w-full resize-none">{{ old('notes', $car->notes) }}</textarea>
    </div>
    
    <!-- Submit -->
    <div class="flex items-center justify-end gap-3">
        @if(auth()->user()->isAdmin())
        <button type="button" 
                onclick="showDeleteModal()"
                class="btn-secondary text-red-400 hover:text-red-300 hover:bg-red-500/10 px-4 py-2">
            წაშლა
        </button>
        @endif
        
        <a href="{{ route('cars.show', $car) }}" class="btn-secondary px-4 py-2">გაუქმება</a>
        <button type="submit" class="btn-primary px-4 py-2">
            შენახვა
        </button>
    </div>
</form>

<!-- Delete Confirmation Modal (Outside the form) -->
@if(auth()->user()->isAdmin())
<div id="deleteModal" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden"
     style="display: none;">
    <div class="bg-dark-800 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-white">დადასტურება</h3>
                <p class="text-dark-400 text-sm mt-1">ნამდვილად გსურთ ავტომობილის წაშლა?</p>
            </div>
        </div>
        
        <form method="POST" action="{{ route('cars.destroy', $car) }}" class="mt-6">
            @csrf
            @method('DELETE')
            <div class="flex items-center justify-end gap-3">
                <button type="button" 
                        onclick="hideDeleteModal()"
                        class="px-4 py-2 rounded-lg bg-dark-700 text-dark-300 hover:bg-dark-600 transition-colors">
                    არა
                </button>
                <button type="submit" 
                        class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 transition-colors">
                    კი
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function deletePhoto(fileId) {
    if (!confirm('ნამდვილად გსურთ ფოტოს წაშლა?')) return;
    
    fetch('/cars/{{ $car->id }}/files/' + fileId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
    });
}

function setMainPhoto(fileId) {
    fetch('/cars/{{ $car->id }}/files/' + fileId + '/main', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(r => r.json()).then(data => {
        if (data.success) location.reload();
    });
}

function showDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.remove('hidden');
        }, 10);
    }
}

function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('hidden');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 200);
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideDeleteModal();
            }
        });
    }
    
    // Add client-side compression for photos
    const form = document.querySelector('form[method="POST"]');
    if (form) {
        form.addEventListener('submit', async function(e) {
            const photoInput = document.getElementById('photos');
            if (photoInput && photoInput.files && photoInput.files.length > 0) {
                e.preventDefault();
                
                // Show compression message
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> კომპრესირება...</span>';
                
                try {
                    // Compress all photos with progress
                    const totalFiles = photoInput.files.length;
                    let compressedCount = 0;
                    
                    const updateProgress = () => {
                        submitBtn.innerHTML = `<span class="flex items-center gap-2"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> კომპრესირება (${compressedCount}/${totalFiles})...</span>`;
                    };
                    
                    const compressedFiles = await compressPhotos(Array.from(photoInput.files), (index) => {
                        compressedCount = index + 1;
                        updateProgress();
                    });
                    
                    // Create new FormData with compressed files
                    const formData = new FormData();
                    
                    // Copy all form fields except photos
                    const formElements = form.querySelectorAll('input, select, textarea');
                    formElements.forEach(element => {
                        if (element.name && element.name !== 'photos[]' && element.type !== 'file') {
                            if (element.type === 'checkbox' || element.type === 'radio') {
                                if (element.checked) {
                                    formData.append(element.name, element.value);
                                }
                            } else {
                                formData.append(element.name, element.value);
                            }
                        }
                    });
                    
                    // Add compressed photos
                    compressedFiles.forEach((file, index) => {
                        formData.append('photos[]', file, file.name);
                    });
                    
                    submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ატვირთვა...</span>';
                    
                    // Submit the form with compressed files
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else {
                        const html = await response.text();
                        document.open();
                        document.write(html);
                        document.close();
                    }
                } catch (error) {
                    console.error('Compression error:', error);
                    alert('ფოტოების კომპრესირება ვერ მოხერხდა. გთხოვთ სცადოთ ხელახლა.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }
});

// Compress photos client-side
async function compressPhotos(files, onProgress) {
    const compressedFiles = [];
    const maxWidth = 1920;
    const maxHeight = 1920;
    const quality = 0.85;
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (!file.type.startsWith('image/')) {
            compressedFiles.push(file);
            if (onProgress) onProgress(i);
            continue;
        }
        
        try {
            const compressedFile = await compressImage(file, maxWidth, maxHeight, quality);
            compressedFiles.push(compressedFile);
            if (onProgress) onProgress(i);
        } catch (error) {
            console.error('Error compressing file:', file.name, error);
            compressedFiles.push(file); // Fallback to original
            if (onProgress) onProgress(i);
        }
    }
    
    return compressedFiles;
}

// Compress single image
function compressImage(file, maxWidth, maxHeight, quality) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                // Calculate new dimensions
                if (width > maxWidth || height > maxHeight) {
                    if (width > height) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    } else {
                        width = (width * maxHeight) / height;
                        height = maxHeight;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob(function(blob) {
                    if (blob) {
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        resolve(compressedFile);
                    } else {
                        reject(new Error('Compression failed'));
                    }
                }, 'image/jpeg', quality);
            };
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}
</script>
@endpush
@endsection
