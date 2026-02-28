@extends('layouts.app')

@section('title', 'ახალი ინვოისი')

@section('page-header')
<div class="flex items-center gap-4">
    <a href="{{ route('invoices.index') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-white">ახალი ინვოისი</h1>
        <p class="text-dark-400 mt-1">შეავსეთ ინვოისის ინფორმაცია</p>
    </div>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('invoices.store') }}" class="space-y-6" x-data="invoiceForm()" @submit.prevent="submitInvoice($event)">
    @csrf
    
    @if(isset($selectedCarId))
        <input type="hidden" name="car_id" value="{{ $selectedCarId }}">
    @endif
    
    <!-- Invoice Type -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">ინვოისის ტიპი</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="relative cursor-pointer">
                <input type="radio" name="type" value="vehicle" 
                       x-model="invoiceType"
                       class="sr-only" required>
                <div class="p-4 rounded-lg border-2 transition-all"
                     :class="invoiceType === 'vehicle' ? 'border-primary-500 bg-primary-500/10' : 'border-dark-700 bg-dark-800/50'">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                             :class="invoiceType === 'vehicle' ? 'border-primary-500' : 'border-dark-500'">
                            <div class="w-3 h-3 rounded-full bg-primary-500 transition-all"
                                 :class="invoiceType === 'vehicle' ? 'block' : 'hidden'"></div>
                        </div>
                        <div>
                            <div class="font-medium text-white">ავტომობილის ღირებულება</div>
                            <div class="text-sm text-dark-400">ავტომობილის შეძენის ინვოისი</div>
                        </div>
                    </div>
                </div>
            </label>
            
            <label class="relative cursor-pointer">
                <input type="radio" name="type" value="shipping" 
                       x-model="invoiceType"
                       class="sr-only" required>
                <div class="p-4 rounded-lg border-2 transition-all"
                     :class="invoiceType === 'shipping' ? 'border-primary-500 bg-primary-500/10' : 'border-dark-700 bg-dark-800/50'">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                             :class="invoiceType === 'shipping' ? 'border-primary-500' : 'border-dark-500'">
                            <div class="w-3 h-3 rounded-full bg-primary-500 transition-all"
                                 :class="invoiceType === 'shipping' ? 'block' : 'hidden'"></div>
                        </div>
                        <div>
                            <div class="font-medium text-white">ტრანსპორტირების გადასახადი</div>
                            <div class="text-sm text-dark-400">ტრანსპორტირების ინვოისი</div>
                        </div>
                    </div>
                </div>
            </label>
        </div>
        @error('type')
            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>
    
    <!-- Car Selection -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">მანქანის არჩევა</h2>
        
        <div>
            <label for="car_id" class="block text-sm font-medium text-dark-300 mb-2">აირჩიეთ მანქანა (არასავალდებულო)</label>
            <div class="relative">
                <select id="car_id" 
                        x-model="selectedCarId"
                        @change="loadCarData()"
                        :disabled="loading"
                        class="form-input w-full">
                    <option value="">-- აირჩიეთ მანქანა --</option>
                    @foreach($cars as $car)
                        <option value="{{ $car->id }}" {{ isset($selectedCarId) && $selectedCarId == $car->id ? 'selected' : '' }}>
                            {{ $car->make_model }} 
                            @if($car->year) ({{ $car->year }}) @endif
                            - {{ $car->vin }}
                        </option>
                    @endforeach
                </select>
                <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin h-5 w-5 text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-dark-500 mt-1">მანქანის არჩევისას ველები ავტომატურად შეივსება</p>
        </div>
    </div>
    
    <!-- Common Fields -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">ძირითადი ინფორმაცია</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Personal ID -->
            <div>
                <label for="personal_id" class="block text-sm font-medium text-dark-300 mb-2">პირადი ნომერი</label>
                <input type="text" name="personal_id" id="personal_id" 
                       x-model="formData.personal_id"
                       value="{{ old('personal_id') }}" 
                       class="form-input w-full" maxlength="50">
                @error('personal_id')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- VIN -->
            <div>
                <label for="vin" class="block text-sm font-medium text-dark-300 mb-2">VIN</label>
                <input type="text" name="vin" id="vin" 
                       x-model="formData.vin"
                       value="{{ old('vin') }}" 
                       class="form-input w-full uppercase" maxlength="17">
                @error('vin')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Make/Model -->
            <div>
                <label for="make_model" class="block text-sm font-medium text-dark-300 mb-2">წელი/მარკა/მოდელი</label>
                <input type="text" name="make_model" id="make_model" 
                       x-model="formData.make_model"
                       value="{{ old('make_model') }}" 
                       class="form-input w-full" maxlength="100">
                @error('make_model')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Year -->
            <div>
                <label for="year" class="block text-sm font-medium text-dark-300 mb-2">წელი</label>
                <input type="number" name="year" id="year" 
                       x-model="formData.year"
                       value="{{ old('year') }}" 
                       class="form-input w-full" min="1900" max="{{ date('Y') + 1 }}">
                @error('year')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Client Name -->
            <div class="md:col-span-2">
                <label for="client_name" class="block text-sm font-medium text-dark-300 mb-2">კლიენტის სახელი</label>
                <input type="text" name="client_name" id="client_name" 
                       x-model="formData.client_name"
                       value="{{ old('client_name') }}" 
                       class="form-input w-full" maxlength="100">
                @error('client_name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
    
    <!-- Vehicle Cost (only for vehicle type) -->
    <div class="glass-card p-6" x-show="invoiceType === 'vehicle'" x-cloak>
        <h2 class="text-lg font-semibold text-white mb-4">ავტომობილის ღირებულება</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="vehicle_cost" class="block text-sm font-medium text-dark-300 mb-2">ავტომობილის ფასი ($)</label>
                <input type="number" name="vehicle_cost" id="vehicle_cost" 
                       x-model="formData.vehicle_cost"
                       value="{{ old('vehicle_cost') }}" 
                       step="0.01" min="0"
                       class="form-input w-full" 
                       x-bind:required="invoiceType === 'vehicle'">
                @error('vehicle_cost')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
    
    <!-- Shipping Cost (only for shipping type) -->
    <div class="glass-card p-6" x-show="invoiceType === 'shipping'" x-cloak>
        <h2 class="text-lg font-semibold text-white mb-4">ტრანსპორტირების გადასახადი</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="shipping_cost" class="block text-sm font-medium text-dark-300 mb-2">ტრანსპორტირების თანხა ($)</label>
                <input type="number" name="shipping_cost" id="shipping_cost" 
                       x-model="formData.shipping_cost"
                       value="{{ old('shipping_cost') }}" 
                       step="0.01" min="0"
                       class="form-input w-full"
                       x-bind:required="invoiceType === 'shipping'">
                @error('shipping_cost')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
    
    <!-- Notes -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">დამატებითი ინფორმაცია</h2>
        
        <div>
            <label for="notes" class="block text-sm font-medium text-dark-300 mb-2">შენიშვნები</label>
            <textarea name="notes" id="notes" rows="3" 
                      class="form-input w-full">{{ old('notes') }}</textarea>
            @error('notes')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
    
    <!-- Submit -->
    <div class="flex justify-end gap-4">
        <a href="{{ route('invoices.index') }}" class="btn-secondary">გაუქმება</a>
        <button type="submit" class="btn-primary" :disabled="loading">
            <span x-show="!loading">ინვოისის შექმნა</span>
            <span x-show="loading" x-cloak>მიმდინარეობს...</span>
        </button>
    </div>
</form>

<script>
function invoiceForm() {
    return {
        invoiceType: '{{ old('type', isset($selectedType) ? $selectedType : '') }}',
        selectedCarId: '{{ isset($selectedCarId) ? $selectedCarId : '' }}',
        formData: {
            personal_id: '{{ old('personal_id', '') }}',
            vin: '{{ old('vin', '') }}',
            make_model: '{{ old('make_model', '') }}',
            year: '{{ old('year', '') }}',
            client_name: '{{ old('client_name', '') }}',
            vehicle_cost: '{{ old('vehicle_cost', '') }}',
            shipping_cost: '{{ old('shipping_cost', '') }}',
        },
        loading: false,
        
        init() {
            // Auto-load car data if car is pre-selected
            if (this.selectedCarId) {
                this.loadCarData();
            }
        },
        
        async loadCarData() {
            if (!this.selectedCarId) {
                return;
            }
            
            this.loading = true;
            
            try {
                const response = await fetch(`/invoices/car/${this.selectedCarId}/data`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (!response.ok) {
                    throw new Error('მონაცემების მიღება ვერ მოხერხდა');
                }
                
                const carData = await response.json();
                
                // Fill form fields
                this.formData.personal_id = carData.personal_id || '';
                this.formData.vin = carData.vin || '';
                this.formData.make_model = carData.make_model || '';
                this.formData.year = carData.year || '';
                this.formData.client_name = carData.client_name || '';
                
                // Fill cost based on invoice type
                if (this.invoiceType === 'vehicle') {
                    this.formData.vehicle_cost = carData.vehicle_cost || '';
                } else if (this.invoiceType === 'shipping') {
                    this.formData.shipping_cost = carData.shipping_cost || '';
                }
                
            } catch (error) {
                console.error('Error loading car data:', error);
                alert('მანქანის მონაცემების ჩატვირთვა ვერ მოხერხდა');
            } finally {
                this.loading = false;
            }
        },
        
        async submitInvoice(event) {
            event.preventDefault();
            this.loading = true;
            
            const form = event.target;
            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.redirect) {
                        // Open invoice in new tab
                        window.open(data.redirect, '_blank');
                        // Redirect back to invoices index
                        window.location.href = '{{ route("invoices.index") }}';
                    } else {
                        throw new Error('ინვოისის URL ვერ მიიღება');
                    }
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'ინვოისის შექმნა ვერ მოხერხდა');
                }
            } catch (error) {
                console.error('Error creating invoice:', error);
                alert(error.message || 'ინვოისის შექმნა ვერ მოხერხდა');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
