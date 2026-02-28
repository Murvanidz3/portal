@extends('layouts.app')

@section('title', 'კალკულატორი')

@section('page-header')
    <div>
        <h1 class="text-2xl font-bold text-white">კალკულატორი</h1>
        <p class="text-dark-400 mt-1">გამოთვალეთ მანქანის ჯამური ღირებულება</p>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calculator.css') }}">
@endpush

@section('content')
    <div class="space-y-6" x-data="{ 
                                                 activeTab: 'shipping',
                                                 init() {
                                                     this.$watch('activeTab', (value) => {
                                                         setTimeout(() => {
                                                             if (value === 'shipping') {
                                                                 if (typeof updateLocationDropdown === 'function') {
                                                                     updateLocationDropdown();
                                                                 }
                                                                 if (typeof calculateShipping === 'function') {
                                                                     calculateShipping();
                                                                 }
                                                             } else if (value === 'auction' && typeof calculateFees === 'function') {
                                                                 calculateFees();
                                                             } else if (value === 'customs' && typeof calculateCustoms === 'function') {
                                                                 calculateCustoms();
                                                             }
                                                         }, 150);
                                                     });
                                                 }
                                             }">
        <!-- Tabs Navigation -->
        <div class="glass-card p-2">
            <div class="flex flex-wrap gap-2">
                <button
                    @click="activeTab = 'shipping'; setTimeout(() => typeof calculateShipping === 'function' && calculateShipping(), 150)"
                    :class="activeTab === 'shipping' ? 'bg-primary-500 text-white border-primary-500' : 'bg-dark-800/50 text-dark-300 border-dark-600 hover:bg-dark-700'"
                    class="flex-1 min-w-[200px] px-4 py-3 rounded-lg text-sm font-medium transition-all border">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    ტრანსპორტირება
                </button>
                <button
                    @click="activeTab = 'auction'; setTimeout(() => typeof calculateFees === 'function' && calculateFees(), 150)"
                    :class="activeTab === 'auction' ? 'bg-primary-500 text-white border-primary-500' : 'bg-dark-800/50 text-dark-300 border-dark-600 hover:bg-dark-700'"
                    class="flex-1 min-w-[200px] px-4 py-3 rounded-lg text-sm font-medium transition-all border">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    დარიცხვის კალკულატორი
                </button>
                <button
                    @click="activeTab = 'customs'; setTimeout(() => typeof calculateCustoms === 'function' && calculateCustoms(), 150)"
                    :class="activeTab === 'customs' ? 'bg-primary-500 text-white border-primary-500' : 'bg-dark-800/50 text-dark-300 border-dark-600 hover:bg-dark-700'"
                    class="flex-1 min-w-[200px] px-4 py-3 rounded-lg text-sm font-medium transition-all border">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    განბაჟების კალკულატორი
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div>

            <!-- Shipping Calculator Tab -->
            <div x-show="activeTab === 'shipping'" x-transition class="tab-pane" id="shipping-pane">
                <div class="glass-card p-4 lg:p-6">
                    <h4 class="text-lg font-semibold text-white mb-4">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        ტრანსპორტირების კალკულატორი
                    </h4>

                    <!-- Main Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="shippingCalculator()" x-init="init()">

                        <!-- Left Column: Selection Fields -->
                        <div class="lg:col-span-1">
                            <label class="block text-xs font-semibold text-dark-400 uppercase tracking-wider mb-3">
                                არჩევნები *
                            </label>
                            <div class="space-y-4">
                                <!-- Vehicle Type -->
                                <div>
                                    <label class="block text-xs text-dark-400 mb-2">აირჩიე ავტომობილის ტიპი</label>
                                    <select x-model="vehicleType" @change="calculate()" class="form-input w-full max-w-xs">
                                        <option value="sedan">Sedan</option>
                                        <option value="sm_suv">S/M SUV</option>
                                        <option value="big_suv">Big SUV</option>
                                        <option value="van">VAN</option>
                                        <option value="sprinter">Sprinter</option>
                                        <option value="pickup">Pickup</option>
                                        <option value="heavy_equip">Heavy Equip</option>
                                        <option value="bob_cat">Bob Cat</option>
                                    </select>
                                </div>

                                <!-- Auction -->
                                <div>
                                    <label class="block text-xs text-dark-400 mb-2">აირჩიე აუქციონი</label>
                                    <select x-model="auction" @change="loadLocations()" class="form-input w-full max-w-xs">
                                        <option value="COPART">Copart</option>
                                        <option value="IAAI">IAAI</option>
                                    </select>
                                </div>

                                <!-- Auction Location -->
                                <div>
                                    <label class="block text-xs text-dark-400 mb-2">აირჩიე აუქციონის ლოკაცია</label>
                                    <div class="relative w-full max-w-xs">
                                        <template x-if="loading">
                                            <div class="form-input w-full flex items-center justify-center">
                                                <svg class="animate-spin h-5 w-5 text-primary-500"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                        stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </div>
                                        </template>
                                        <template x-if="!loading && !hasRates">
                                            <div class="text-yellow-400 text-sm p-3 bg-yellow-500/10 rounded-lg">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                თქვენთვის ტარიფები ატვირთული არ არის. მიმართეთ ადმინისტრატორს.
                                            </div>
                                        </template>
                                        <template x-if="!loading && hasRates">
                                            <div class="relative" @click.outside="dropdownOpen = false">
                                                <input type="text" class="form-input w-full pr-10"
                                                    :placeholder="selectedLocation ? '' : 'ძიება ან არჩევა...'"
                                                    :value="dropdownOpen ? locationSearch : selectedLocation"
                                                    @input="locationSearch = $event.target.value; dropdownOpen = true"
                                                    @focus="dropdownOpen = true" @keydown.escape="dropdownOpen = false"
                                                    autocomplete="off">
                                                <span
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-dark-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </span>
                                                <div x-show="dropdownOpen" x-cloak x-transition
                                                    class="absolute z-50 w-full mt-1 bg-dark-800 border border-dark-600 rounded-lg shadow-xl max-h-60 overflow-auto">
                                                    <template x-if="filteredLocations.length === 0">
                                                        <div class="px-4 py-3 text-dark-500 text-sm">შედეგი ვერ მოიძებნა
                                                        </div>
                                                    </template>
                                                    <template x-if="filteredLocations.length > 0">
                                                        <ul class="py-1">
                                                            <template x-for="loc in filteredLocations" :key="loc.name">
                                                                <li>
                                                                    <button type="button"
                                                                        class="w-full text-left px-4 py-2 text-sm text-white hover:bg-dark-700 focus:bg-dark-700 focus:outline-none flex justify-between"
                                                                        @click="selectLocation(loc)">
                                                                        <span x-text="loc.name"></span>
                                                                    </button>
                                                                </li>
                                                            </template>
                                                        </ul>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Destination Country -->
                                <div>
                                    <label class="block text-xs text-dark-400 mb-2">მიმღები ქვეყანა</label>
                                    <select x-model="destinationCountry" class="form-input w-full max-w-xs" disabled>
                                        <option value="georgia">Georgia</option>
                                    </select>
                                </div>

                                <!-- Destination Port -->
                                <div>
                                    <label class="block text-xs text-dark-400 mb-2">მიმღები პორტი</label>
                                    <select x-model="destinationPort" @change="calculate()"
                                        class="form-input w-full max-w-xs">
                                        <option value="poti">ფოთი</option>
                                        <option value="batumi">ბათუმი</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Results Display -->
                        <!-- Right Column: Results Display -->
                        <!-- Right Column: Results Display -->
                        <div class="lg:col-span-2 bg-dark-800/50 rounded-xl p-6 border border-dark-600 flex flex-col justify-center min-h-[420px]">
                            
                            <h5 class="text-xs font-semibold text-dark-400 uppercase tracking-wider mb-8 w-full text-left">
                                ტრანსპორტირების დეტალები
                            </h5>

                            <div class="flex flex-col md:flex-row items-center justify-center gap-8 w-full">
                                
                                <!-- Chart -->
                                <div class="chart-container flex-shrink-0" id="shippingChartContainer"
                                     style="width: 200px; height: 200px;" 
                                     :style="chartStyle">
                                    <div class="chart-inner">
                                        <span class="chart-total-main" x-text="(totalCost || 0) + ' $'">0 $</span>
                                        <span class="chart-total-text">ტრანსპორტირება</span>
                                    </div>
                                </div>

                                <!-- Breakdown List -->
                                <div class="flex-1 w-full max-w-sm ml-0 md:ml-4">
                                    
                                    <!-- Default State -->
                                    <div x-show="!calculationResult" class="text-center md:text-left text-dark-400 text-sm py-4">
                                        აირჩიეთ ლოკაცია ღირებულების სანახავად
                                    </div>

                                    <!-- Result State -->
                                    <template x-if="calculationResult">
                                        <div class="space-y-3">
                                            <div class="fee-item-color">
                                                <span class="fee-name-group">
                                                    <span class="color-dot dot-blue"></span>
                                                    <span>ლოკაცია:</span>
                                                </span>
                                                <span class="fee-value text-white" x-text="calculationResult.location"></span>
                                            </div>

                                            <div class="fee-item-color">
                                                <span class="fee-name-group">
                                                    <span class="color-dot" style="background-color: #6b7280;"></span>
                                                    <span>ბაზისური:</span>
                                                </span>
                                                <span class="fee-value text-white" x-text="calculationResult.base_rate + ' $'"></span>
                                            </div>

                                            <template x-if="calculationResult.vehicle_adjustment > 0">
                                                <div class="fee-item-color">
                                                    <span class="fee-name-group">
                                                        <span class="color-dot" style="background-color: #f59e0b;"></span>
                                                        <span>ავტ. ტიპი:</span>
                                                    </span>
                                                    <span class="fee-value text-white" x-text="'+ ' + calculationResult.vehicle_adjustment + ' $'"></span>
                                                </div>
                                            </template>

                                             <template x-if="calculationResult.port_adjustment > 0">
                                                <div class="fee-item-color">
                                                    <span class="fee-name-group">
                                                        <span class="color-dot" style="background-color: #8b5cf6;"></span>
                                                        <span>პორტი:</span>
                                                    </span>
                                                    <span class="fee-value text-white" x-text="'+ ' + calculationResult.port_adjustment + ' $'"></span>
                                                </div>
                                            </template>

                                            <div class="total-amount-line mt-4 pt-4 border-t border-dark-600">
                                                <span class="fee-name-group">
                                                    <span class="color-dot dot-black"></span>
                                                    <span>სრული ფასი:</span>
                                                </span>
                                                <span class="text-2xl font-bold text-green-400" x-text="(totalCost || 0) + ' $'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <template x-if="calculationResult">
                                <div class="mt-6 text-center text-[10px] text-dark-500">
                                    <span class="text-primary-400" x-text="calculationResult.auction"></span> ტრანსპორტირება 
                                    <span x-text="destinationPort === 'poti' ? 'ფოთში' : 'ბათუმში'"></span>.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auction Fee Calculator Tab -->
            <div x-show="activeTab === 'auction'" x-transition class="tab-pane" id="auction-pane">
                <div class="glass-card p-4 lg:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Panel: Inputs -->
                        <div class="calc-input-panel">
                            <h4 class="text-lg font-semibold text-white mb-4">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                კალკულატორი
                            </h4>

                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-dark-400 uppercase tracking-wider mb-2">
                                    აირჩიეთ აუქციონი
                                </label>
                                <select id="auctionSelect" class="form-input w-full text-lg py-3">
                                    <option value="COPART">COPART</option>
                                    <option value="IAAI">IAAI</option>
                                </select>
                            </div>

                            <div class="mb-5">
                                <label class="block text-xs font-semibold text-dark-400 uppercase tracking-wider mb-2">
                                    ავტომობილის ფასი ($)
                                </label>
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 rounded-l-lg bg-dark-800 border border-r-0 border-dark-600 text-dark-300">
                                        $
                                    </span>
                                    <input type="number" id="priceInput" class="form-input flex-1 rounded-l-none" min="1"
                                        placeholder="მაგ. 5000">
                                </div>
                            </div>

                            <button type="button" class="btn-calc w-full" onclick="calculateFees()">
                                გამოთვლა
                                <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            <p class="mt-3 text-xs text-dark-500 leading-relaxed">
                                შენიშვნა : გამოთვლები მიახლოებითია და შესაძლებელია ცდომილება 35 $ ფარგლებში
                            </p>
                        </div>

                        <!-- Right Panel: Results -->
                        <div class="pl-0 lg:pl-5 pt-4 lg:pt-0">
                            <h4 class="text-lg font-semibold text-white mb-4 text-center lg:text-left">დარიცხვის დეტალები
                            </h4>

                            <div class="flex flex-col md:flex-row items-center mb-4 gap-4">
                                <div class="chart-container" id="chartContainer">
                                    <div class="chart-inner">
                                        <span class="chart-total-main" id="totalFeeDisplay">0 $</span>
                                        <span class="chart-total-text">დანარიცხი</span>
                                    </div>
                                </div>

                                <div class="flex-1 w-full">
                                    <div class="fee-item-color">
                                        <span class="fee-name-group">
                                            <span class="color-dot dot-blue"></span>
                                            <span>ლოტის ფასი:</span>
                                        </span>
                                        <span class="fee-value text-white" id="priceDisplay">0.00 $</span>
                                    </div>

                                    <div id="feeBreakdown"></div>

                                    <div class="total-amount-line">
                                        <span class="fee-name-group">
                                            <span class="color-dot dot-black"></span>
                                            <span>სრული ფასი:</span>
                                        </span>
                                        <span id="totalDisplay" class="text-green-400">0.00 $</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customs Calculator Tab -->
            <div x-show="activeTab === 'customs'" x-transition class="tab-pane" id="customs-pane">
                <div class="glass-card p-4 lg:p-6">
                    <h4 class="text-lg font-semibold text-white mb-4">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        განბაჟების კალკულატორი
                    </h4>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Panel: Inputs -->
                        <div class="calc-input-panel mb-4 lg:mb-0">
                            <!-- Vehicle Type -->
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-dark-400 uppercase tracking-wider mb-3">
                                    ავტომობილის ტიპი *
                                </label>
                                <div class="flex gap-2 flex-wrap">
                                    <button class="btn-option btn-active" onclick="updateState('type', 'fuel', this)">
                                        საწვავი
                                    </button>
                                    <button class="btn-option" onclick="updateState('type', 'hybrid', this)">
                                        ჰიბრიდი
                                    </button>
                                    <button class="btn-option" onclick="updateState('type', 'electric', this)">
                                        ელექტრო
                                    </button>
                                </div>
                            </div>

                            <!-- Steering Position -->
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-dark-400 uppercase tracking-wider mb-3">
                                    საჭის მდებარეობა *
                                </label>
                                <div class="flex gap-2">
                                    <button class="btn-option btn-active" onclick="updateState('steering', 'left', this)">
                                        მარცხენა
                                    </button>
                                    <button class="btn-option" onclick="updateState('steering', 'right', this)">
                                        მარჯვენა
                                    </button>
                                </div>
                            </div>

                            <!-- Registration Type -->
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-dark-400 uppercase tracking-wider mb-3">
                                    რეგისტრაციის ტიპი *
                                </label>
                                <div class="flex gap-2">
                                    <button class="btn-option btn-active"
                                        onclick="updateState('registration', 'single', this)">
                                        ერთჯერადი
                                    </button>
                                    <button class="btn-option" onclick="updateState('registration', 'double', this)">
                                        ორმაგი
                                    </button>
                                </div>
                            </div>

                            <!-- Year -->
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-dark-400 uppercase tracking-wider mb-2">
                                    წელი *
                                </label>
                                <select id="year" class="form-input w-full text-lg py-3"
                                    onchange="updateState('year', this.value)">
                                    <!-- Options populated by JS -->
                                </select>
                            </div>

                            <!-- Engine Capacity (dropdown style as in design) -->
                            <div class="relative mb-4" x-data="{
                                                                    engineOpen: false,
                                                                    engineDisplay: 'აირჩიეთ',
                                                                    get engineOptions() {
                                                                        var o = []; for (var i = 1; i <= 100; i++) o.push((i/10).toFixed(1)); return o;
                                                                    },
                                                                    selectEngine(val) {
                                                                        this.engineDisplay = val;
                                                                        this.engineOpen = false;
                                                                        var inp = document.getElementById('engine');
                                                                        if (inp) { inp.value = val; inp.dispatchEvent(new Event('input')); }
                                                                    },
                                                                    init() {
                                                                        var inp = document.getElementById('engine');
                                                                        if (inp && inp.value) this.engineDisplay = inp.value; else this.engineDisplay = 'აირჩიეთ';
                                                                    }
                                                                }" @click.outside="engineOpen = false">
                                <label class="block text-xs font-semibold text-dark-400 uppercase tracking-wider mb-2">
                                    ძრავის მოცულობა
                                </label>
                                <input type="hidden" id="engine" value="">
                                <div class="relative">
                                    <button type="button"
                                        class="w-full form-input text-lg py-3 pr-10 text-left rounded-xl border border-dark-600 bg-dark-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                        :class="engineDisplay === 'აირჩიეთ' ? 'text-dark-500' : 'text-white'"
                                        @click="engineOpen = !engineOpen" x-text="engineDisplay">
                                    </button>
                                    <span
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-dark-400">
                                        <svg class="w-5 h-5 transition-transform" :class="engineOpen && 'rotate-180'"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </div>
                                <div x-show="engineOpen" x-cloak x-transition
                                    class="absolute left-0 right-0 z-40 mt-1 overflow-y-auto rounded-xl border border-dark-600 bg-dark-800 shadow-xl"
                                    style="max-height: 11.5rem;">
                                    <ul class="py-1">
                                        <template x-for="opt in engineOptions" :key="opt">
                                            <li>
                                                <button type="button"
                                                    class="w-full text-left px-4 py-2.5 text-base text-white hover:bg-dark-700 focus:bg-dark-700 focus:outline-none"
                                                    :class="engineDisplay === opt && 'bg-primary-500/20 text-primary-400'"
                                                    @click="selectEngine(opt)" x-text="opt"></button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <!-- Info Note -->
                            <div class="text-dark-400 text-xs mt-4" style="line-height: 1.6;">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <strong>შენიშვნა:</strong> გამოთვლები მიახლოებითია და შეიძლება განსხვავდებოდეს რეალური
                                ღირებულებისგან. ზუსტი ინფორმაციისთვის მიმართეთ საბაჟოს სამსახურს.
                            </div>
                        </div>

                        <!-- Right Panel: Results -->
                        <div class="pl-0 lg:pl-5">
                            <h4 class="text-lg font-semibold text-white mb-4 text-center lg:text-left">დაანგარიშების
                                დეტალები</h4>

                            <!-- Fee Breakdown -->
                            <div class="mb-4">
                                <div class="fee-item-color">
                                    <span class="fee-name-group">
                                        <span class="color-dot dot-blue"></span>
                                        <span>აქციზი:</span>
                                    </span>
                                    <span class="fee-value text-white" id="res-excise">0</span>
                                    <span class="text-dark-400 text-xs"> ₾</span>
                                </div>

                                <div class="fee-item-color">
                                    <span class="fee-name-group">
                                        <span class="color-dot" style="background-color: #10b981;"></span>
                                        <span>საბაჟოს მომსახურება:</span>
                                    </span>
                                    <span class="fee-value text-white">150</span>
                                    <span class="text-dark-400 text-xs"> ₾</span>
                                </div>

                                <div class="fee-item-color">
                                    <span class="fee-name-group">
                                        <span class="color-dot" style="background-color: #8b5cf6;"></span>
                                        <span>რეგისტრაციის მოსაკრებელი:</span>
                                    </span>
                                    <span class="fee-value text-white" id="res-reg">200</span>
                                    <span class="text-dark-400 text-xs"> ₾</span>
                                </div>

                                <div class="fee-item-color">
                                    <span class="fee-name-group">
                                        <span class="color-dot" style="background-color: #f59e0b;"></span>
                                        <span>იმპორტის გადასახადი:</span>
                                    </span>
                                    <span class="fee-value text-white" id="res-import">0</span>
                                    <span class="text-dark-400 text-xs"> ₾</span>
                                </div>

                                <div class="fee-item-color">
                                    <span class="fee-name-group">
                                        <span class="color-dot" style="background-color: #ef4444;"></span>
                                        <span>ექსპერტიზა:</span>
                                    </span>
                                    <span class="fee-value text-white">30</span>
                                    <span class="text-dark-400 text-xs"> ₾</span>
                                </div>

                                <div class="fee-item-color">
                                    <span class="fee-name-group">
                                        <span class="color-dot" style="background-color: #ec4899;"></span>
                                        <span>დეკლარაცია:</span>
                                    </span>
                                    <span class="fee-value text-white">50</span>
                                    <span class="text-dark-400 text-xs"> ₾</span>
                                </div>

                                <div class="fee-item-color">
                                    <span class="fee-name-group">
                                        <span class="color-dot" style="background-color: #06b6d4;"></span>
                                        <span>ტრანზიტი:</span>
                                    </span>
                                    <span class="fee-value text-white">50</span>
                                    <span class="text-dark-400 text-xs"> ₾</span>
                                </div>
                            </div>

                            <!-- Circular Chart -->
                            <div class="flex items-center justify-center mb-4">
                                <div class="chart-container" id="customsChartContainer"
                                    style="width: 200px; height: 200px;">
                                    <div class="chart-inner">
                                        <span class="chart-total-main" id="total-price">0 ₾</span>
                                        <span class="chart-total-text">სრული ღირებულება</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Line -->
                            <div class="total-amount-line">
                                <span class="fee-name-group">
                                    <span class="color-dot dot-black"></span>
                                    <span>სრული ღირებულება:</span>
                                </span>
                                <span id="total-price-copy" class="text-green-400">0 ₾</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            // Shipping Calculator Alpine.js Component
            function shippingCalculator() {
                return {
                    // State
                    vehicleType: 'sedan',
                    auction: 'COPART',
                    destinationCountry: 'georgia',
                    destinationPort: 'poti',

                    // Location state
                    locations: [],
                    selectedLocation: '',
                    selectedLocationData: null,
                    locationSearch: '',
                    dropdownOpen: false,
                    loading: true,
                    hasRates: false,
                    debugInfo: null,

                    // Result state
                    calculationResult: null,
                    totalCost: 0,
                    chartStyle: '',

                    // Computed
                    get filteredLocations() {
                        const q = (this.locationSearch || '').toLowerCase().trim();
                        if (!q) return this.locations;
                        return this.locations.filter(loc => loc.name.toLowerCase().includes(q));
                    },

                    // Methods
                    init() {
                        this.loadLocations();
                    },

                    async loadLocations() {
                        this.loading = true;
                        this.selectedLocation = '';
                        this.selectedLocationData = null;
                        this.calculationResult = null;
                        this.totalCost = 0;
                        this.debugInfo = null;

                        try {
                            const response = await fetch(`/calculator/get-locations?auction=${this.auction}`);
                            const data = await response.json();

                            this.hasRates = data.has_rates || false;
                            this.locations = data.locations || [];
                            this.debugInfo = data.debug || null;

                            if (this.debugInfo) console.log('Location Debug:', this.debugInfo);

                        } catch (error) {
                            console.error('Failed to load locations:', error);
                            this.hasRates = false;
                            this.locations = [];
                            this.debugInfo = { error: error.message };
                        }

                        this.loading = false;
                    },

                    selectLocation(loc) {
                        this.selectedLocation = loc.name;
                        this.selectedLocationData = loc;
                        this.dropdownOpen = false;
                        this.locationSearch = '';
                        this.calculate();
                    },

                    async calculate() {
                        if (!this.vehicleType || !this.selectedLocation) {
                            this.calculationResult = null;
                            this.totalCost = 0;
                            this.updateChart();
                            return;
                        }

                        try {
                            const response = await fetch('/calculator/calculate-from-rates', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                                },
                                body: JSON.stringify({
                                    vehicle_type: this.vehicleType,
                                    auction: this.auction,
                                    location: this.selectedLocation,
                                    destination_port: this.destinationPort
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.calculationResult = data;
                                this.totalCost = data.total_cost;
                            } else {
                                this.calculationResult = null;
                                this.totalCost = 0;
                            }

                        } catch (error) {
                            console.error('Calculation error:', error);
                            this.calculationResult = null;
                            this.totalCost = 0;
                        }

                        this.updateChart();
                    },

                    updateChart() {
                        if (!this.calculationResult) {
                            this.chartStyle = '';
                            return;
                        }

                        const total = this.calculationResult.total_cost || 0;
                        const base = this.calculationResult.base_rate || 0;
                        const vehicle = this.calculationResult.vehicle_adjustment || 0;
                        const port = this.calculationResult.port_adjustment || 0;

                        if (total <= 0) {
                            this.chartStyle = '';
                            return;
                        }

                        const basePercent = (base / total) * 100;
                        const vehiclePercent = (vehicle / total) * 100;
                        const portPercent = (port / total) * 100;

                        let gradient = 'conic-gradient(';
                        let currentAngle = 0;

                        // Base rate - blue
                        gradient += `#3b82f6 ${currentAngle}% ${currentAngle + basePercent}%`;
                        currentAngle += basePercent;

                        // Vehicle adjustment - orange
                        if (vehiclePercent > 0) {
                            gradient += `, #f59e0b ${currentAngle}% ${currentAngle + vehiclePercent}%`;
                            currentAngle += vehiclePercent;
                        }

                        // Port adjustment - purple
                        if (portPercent > 0) {
                            gradient += `, #8b5cf6 ${currentAngle}% ${currentAngle + portPercent}%`;
                            currentAngle += portPercent;
                        }

                        gradient += ')';

                        this.chartStyle = `background: ${gradient}; border-radius: 50%;`;
                    }
                }
            }

            // Auction Fee Calculator JavaScript
            const COPART_TOTAL_FEES = [
                { min: 0.00, max: 49.99, fee: 131.00 },
                { min: 50.00, max: 99.99, fee: 131.00 },
                { min: 100.00, max: 199.99, fee: 205.00 },
                { min: 200.00, max: 299.99, fee: 240.00 },
                { min: 300.00, max: 349.99, fee: 265.00 },
                { min: 350.00, max: 399.99, fee: 280.00 },
                { min: 400.00, max: 449.99, fee: 305.00 },
                { min: 450.00, max: 499.99, fee: 315.00 },
                { min: 500.00, max: 549.99, fee: 350.00 },
                { min: 550.00, max: 599.99, fee: 350.00 },
                { min: 600.00, max: 699.99, fee: 365.00 },
                { min: 700.00, max: 799.99, fee: 390.00 },
                { min: 800.00, max: 899.99, fee: 410.00 },
                { min: 900.00, max: 999.99, fee: 425.00 },
                { min: 1000.00, max: 1199.99, fee: 465.00 },
                { min: 1200.00, max: 1299.99, fee: 485.00 },
                { min: 1300.00, max: 1399.99, fee: 500.00 },
                { min: 1400.00, max: 1499.99, fee: 515.00 },
                { min: 1500.00, max: 1599.99, fee: 540.00 },
                { min: 1600.00, max: 1699.99, fee: 555.00 },
                { min: 1700.00, max: 1799.99, fee: 575.00 },
                { min: 1800.00, max: 1999.99, fee: 595.00 },
                { min: 2000.00, max: 2399.99, fee: 630.00 },
                { min: 2400.00, max: 2499.99, fee: 665.00 },
                { min: 2500.00, max: 2999.99, fee: 700.00 },
                { min: 3000.00, max: 3499.99, fee: 745.00 },
                { min: 3500.00, max: 3999.99, fee: 795.00 },
                { min: 4000.00, max: 4499.99, fee: 855.00 },
                { min: 4500.00, max: 4999.99, fee: 880.00 },
                { min: 5000.00, max: 5499.99, fee: 905.00 },
                { min: 5500.00, max: 5999.99, fee: 930.00 },
                { min: 6000.00, max: 6499.99, fee: 975.00 },
                { min: 6500.00, max: 6999.99, fee: 995.00 },
                { min: 7000.00, max: 7499.99, fee: 1030.00 },
                { min: 7500.00, max: 7999.99, fee: 1050.00 },
                { min: 8000.00, max: 8499.99, fee: 1090.00 },
                { min: 8500.00, max: 9999.99, fee: 1110.00 },
                { min: 10000.00, max: 11999.99, fee: 1140.00 },
                { min: 12000.00, max: 12499.99, fee: 1150.00 },
                { min: 12500.00, max: 14999.99, fee: 1165.00 },
                { min: 15000.00, max: 999999.99, fee_percent: 0.06, fee_fixed: 290.00 },
            ];

            const IAAI_TOTAL_FEES = [
                { min: 0, max: 99.99, fee: 166.00 },
                { min: 100.00, max: 199.99, fee: 240.00 },
                { min: 200.00, max: 299.99, fee: 275.00 },
                { min: 300.00, max: 349.99, fee: 315.00 },
                { min: 350.00, max: 399.99, fee: 340.00 },
                { min: 400.00, max: 499.99, fee: 350.00 },
                { min: 500.00, max: 549.99, fee: 375.00 },
                { min: 550.00, max: 599.99, fee: 385.00 },
                { min: 600.00, max: 699.99, fee: 400.00 },
                { min: 700.00, max: 799.99, fee: 425.00 },
                { min: 800.00, max: 899.99, fee: 445.00 },
                { min: 900.00, max: 999.99, fee: 460.00 },
                { min: 1000.00, max: 1199.99, fee: 500.00 },
                { min: 1200.00, max: 1299.99, fee: 520.00 },
                { min: 1300.00, max: 1399.99, fee: 535.00 },
                { min: 1400.00, max: 1499.99, fee: 550.00 },
                { min: 1500.00, max: 1599.99, fee: 575.00 },
                { min: 1600.00, max: 1699.99, fee: 590.00 },
                { min: 1700.00, max: 1799.99, fee: 610.00 },
                { min: 1800.00, max: 1999.99, fee: 630.00 },
                { min: 2000.00, max: 2399.99, fee: 665.00 },
                { min: 2400.00, max: 2499.99, fee: 700.00 },
                { min: 2500.00, max: 2999.99, fee: 735.00 },
                { min: 3000.00, max: 3499.99, fee: 780.00 },
                { min: 3500.00, max: 3999.99, fee: 830.00 },
                { min: 4000.00, max: 4499.99, fee: 890.00 },
                { min: 4500.00, max: 4999.99, fee: 915.00 },
                { min: 5000.00, max: 5999.99, fee: 940.00 },
                { min: 6000.00, max: 6499.99, fee: 1010.00 },
                { min: 6500.00, max: 6999.99, fee: 1030.00 },
                { min: 7000.00, max: 7499.99, fee: 1065.00 },
                { min: 7500.00, max: 7999.99, fee: 1085.00 },
                { min: 8000.00, max: 8499.99, fee: 1125.00 },
                { min: 8500.00, max: 9999.99, fee: 1145.00 },
                { min: 10000.00, max: 11499.99, fee: 1175.00 },
                { min: 11500.00, max: 11999.99, fee: 1185.00 },
                { min: 12000.00, max: 12499.99, fee: 1200.00 },
                { min: 12500.00, max: 14999.99, fee: 1215.00 },
                { min: 15000.00, max: 999999.99, fee_percent: 0.06, fee_fixed: 325.00 },
            ];

            const colorMap = {
                'Buyer Fee': '#10b981',
                'Virtual Bid Fee': '#3b82f6',
                'Gate Fee': '#f59e0b',
                'Environmental Fee': '#ef4444',
                'Broker Fee': '#8b5cf6',
            };
            const FEE_COMPONENTS_FOR_VISUAL = ['Buyer Fee', 'Virtual Bid Fee', 'Gate Fee', 'Environmental Fee', 'Broker Fee'];

            function getFee(price, feeTable) {
                if (price <= 0) return 0;
                const feeEntry = feeTable.find(entry => price >= entry.min && price <= entry.max);
                if (feeEntry) {
                    if (feeEntry.fee) {
                        return feeEntry.fee;
                    } else if (feeEntry.fee_percent) {
                        let calculatedFee = price * feeEntry.fee_percent;
                        if (feeEntry.fee_fixed) {
                            calculatedFee += feeEntry.fee_fixed;
                        }
                        return calculatedFee;
                    }
                }
                return 0;
            }

            function formatCurrency(amount) {
                return `${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} $`;
            }

            function calculateFees() {
                // Null checks to avoid runtime errors
                const auctionEl = document.getElementById('auctionSelect');
                const priceInputEl = document.getElementById('priceInput');
                const feeBreakdownDiv = document.getElementById('feeBreakdown');
                if (!auctionEl || !priceInputEl || !feeBreakdownDiv) return;

                const auction = auctionEl.value;
                const priceInput = priceInputEl.value;
                const price = parseFloat(priceInput);

                feeBreakdownDiv.innerHTML = '';

                if (isNaN(price) || price <= 0) {
                    const zeroPrice = '0.00 $';
                    const priceDisplay = document.getElementById('priceDisplay');
                    const totalFeeDisplay = document.getElementById('totalFeeDisplay');
                    const totalDisplay = document.getElementById('totalDisplay');
                    const chartContainer = document.getElementById('chartContainer');
                    if (priceDisplay) priceDisplay.textContent = zeroPrice;
                    if (totalFeeDisplay) totalFeeDisplay.textContent = '0 $';
                    if (totalDisplay) totalDisplay.textContent = zeroPrice;
                    if (chartContainer) chartContainer.style.background = 'none';
                    return;
                }

                let totalAuctionFee = 0;
                let feesForChart = [];

                const priceDisplay = document.getElementById('priceDisplay');
                if (priceDisplay) priceDisplay.textContent = formatCurrency(price);

                if (auction === 'COPART') {
                    // --- COPART ლოგიკა: სრული დანარიცხის გათვლა ---
                    totalAuctionFee = getFee(price, COPART_TOTAL_FEES);
                    const feePortion = totalAuctionFee / FEE_COMPONENTS_FOR_VISUAL.length;
                    let currentTotal = 0;
                    FEE_COMPONENTS_FOR_VISUAL.forEach((name, index) => {
                        let feeValue = feePortion;
                        if (index === FEE_COMPONENTS_FOR_VISUAL.length - 1) {
                            feeValue = totalAuctionFee - currentTotal;
                        }
                        feesForChart.push({ name: name, value: feeValue, color: colorMap[name] });
                        currentTotal += feeValue;
                    });
                } else if (auction === 'IAAI') {
                    // --- IAAI ლოგიკა: სრული დანარიცხის გათვლა ---
                    totalAuctionFee = getFee(price, IAAI_TOTAL_FEES);
                    const feePortion = totalAuctionFee / FEE_COMPONENTS_FOR_VISUAL.length;
                    let currentTotal = 0;
                    FEE_COMPONENTS_FOR_VISUAL.forEach((name, index) => {
                        let feeValue = feePortion;
                        if (index === FEE_COMPONENTS_FOR_VISUAL.length - 1) {
                            feeValue = totalAuctionFee - currentTotal;
                        }
                        feesForChart.push({ name: name, value: feeValue, color: colorMap[name] });
                        currentTotal += feeValue;
                    });
                }

                // --- შედეგების პანელი (ორივე შემთხვევაში მხოლოდ სულ დანარიცხი) ---
                feeBreakdownDiv.innerHTML = `
                                                                                    <div class="fee-item-color">
                                                                                        <span class="fee-name-group">
                                                                                            <span class="color-dot dot-black"></span>
                                                                                            <span>დარიცხვა :</span>
                                                                                        </span>
                                                                                        <span class="fee-value">${formatCurrency(totalAuctionFee)}</span>
                                                                                    </div>
                                                                                `;

                // --- გრაფიკის განახლება (Conic Gradient) ---
                const chartContainer = document.getElementById('chartContainer');
                let gradientString = 'conic-gradient(';
                let currentAngle = 0;
                const totalFeesForChart = feesForChart.reduce((sum, item) => sum + item.value, 0);

                if (totalFeesForChart > 0) {
                    feesForChart.forEach((item, index) => {
                        const percentage = (item.value / totalFeesForChart) * 100;
                        const endAngle = currentAngle + percentage;
                        if (index > 0) gradientString += ', ';
                        gradientString += `${item.color} ${currentAngle}% ${endAngle}%`;
                        currentAngle = endAngle;
                    });
                } else {
                    gradientString += 'transparent 0% 100%';
                }
                gradientString += ')';
                if (chartContainer) {
                    chartContainer.style.background = gradientString;
                    chartContainer.style.borderRadius = '50%';
                }

                const totalDisplayValue = totalAuctionFee.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                const totalFeeDisplayEl = document.getElementById('totalFeeDisplay');
                const totalDisplayEl = document.getElementById('totalDisplay');
                if (totalFeeDisplayEl) totalFeeDisplayEl.textContent = totalDisplayValue + ' $';
                const totalPrice = price + totalAuctionFee;
                if (totalDisplayEl) totalDisplayEl.textContent = formatCurrency(totalPrice);
            }

            window.calculateFees = calculateFees;
            window.calculateCustoms = calculateCustoms;

            // Customs Calculator
            const currentYear = 2026;
            let state = {
                type: 'fuel',
                steering: 'left',
                registration: 'single',
                year: 2015,
                engine: null
            };

            function getExciseRate(age) {
                if (age >= 0 && age <= 2) return 1.5;
                if (age === 3) return 1.4;
                if (age === 4) return 1.2;
                if (age === 5) return 1.0;
                if (age >= 6 && age <= 8) return 0.8;
                if (age === 9) return 0.9;
                if (age === 10) return 1.1;
                if (age === 11) return 1.3;
                if (age === 12) return 1.5;
                if (age === 13) return 1.8;
                if (age === 14) return 2.1;
                if (age > 14 && age < 40) return 2.4;
                if (age >= 40) return 1.0;
                return 2.4;
            }

            function populateYears() {
                const yearSelect = document.getElementById('year');
                if (!yearSelect) return;
                yearSelect.innerHTML = '';
                for (let y = 2026; y >= 1950; y--) {
                    let option = document.createElement('option');
                    option.value = y;
                    option.innerText = y;
                    if (y == state.year) option.selected = true;
                    yearSelect.appendChild(option);
                }
            }

            function updateState(key, value, element = null) {
                if (key === 'engine') {
                    if (value === '' || value === null || value === undefined) {
                        state[key] = null;
                    } else {
                        const num = parseFloat(value);
                        state[key] = isNaN(num) ? null : num;
                    }
                } else if (key === 'year') {
                    const num = parseInt(value, 10);
                    state[key] = isNaN(num) ? currentYear : num;
                } else {
                    state[key] = value;
                }
                if (element) {
                    const parent = element.parentElement;
                    Array.from(parent.children).forEach(btn => btn.classList.remove('btn-active'));
                    element.classList.add('btn-active');
                }
                calculateCustoms();
            }

            function calculateCustoms() {
                const yearNum = parseInt(state.year, 10);
                if (isNaN(yearNum)) return;
                const age = currentYear - yearNum;
                const engineVal = state.engine === null || state.engine === '' ? null : parseFloat(state.engine);
                const engineCC = (engineVal === null || isNaN(engineVal) || engineVal <= 0 ? 0 : engineVal) * 1000;

                let rate = getExciseRate(age);
                let excise = engineCC * rate;

                if (state.type === 'hybrid') {
                    excise = excise * 0.5;
                } else if (state.type === 'electric') {
                    excise = 0;
                }

                if (state.steering === 'right') {
                    if (state.type === 'electric') {
                        excise = 3000;
                    } else {
                        excise = excise * 3;
                    }
                }

                let importTax = 0;
                if (state.type !== 'electric') {
                    importTax = (engineCC * 0.05) + (engineCC * age * 0.0025);
                }

                const customsService = 150;
                const expertFee = 30;
                const declarationFee = 50;
                const transitFee = 50;
                const registrationFee = (state.registration === 'double') ? 400 : 200;

                const total = excise + customsService + registrationFee + importTax + expertFee + declarationFee + transitFee;

                const exciseEl = document.getElementById('res-excise');
                const regEl = document.getElementById('res-reg');
                const importEl = document.getElementById('res-import');
                const totalEl = document.getElementById('total-price');
                const totalCopyEl = document.getElementById('total-price-copy');

                if (exciseEl) exciseEl.innerText = Math.round(excise).toLocaleString();
                if (regEl) regEl.innerText = registrationFee;
                if (importEl) importEl.innerText = Math.round(importTax).toLocaleString();
                if (totalEl) totalEl.innerText = Math.round(total).toLocaleString() + ' ₾';
                if (totalCopyEl) totalCopyEl.innerText = Math.round(total).toLocaleString() + ' ₾';

                // Update chart
                const chartContainer = document.getElementById('customsChartContainer');
                if (chartContainer && total > 0) {
                    const excisePercent = (excise / total) * 100;
                    const importPercent = (importTax / total) * 100;
                    const otherPercent = 100 - excisePercent - importPercent;
                    chartContainer.style.background = `conic-gradient(#3b82f6 0% ${excisePercent}%, #f59e0b ${excisePercent}% ${excisePercent + importPercent}%, #10b981 ${excisePercent + importPercent}% 100%)`;
                    chartContainer.style.borderRadius = '50%';
                }
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function () {
                // Initialize auction calculator
                const priceInput = document.getElementById('priceInput');
                const auctionSelect = document.getElementById('auctionSelect');

                if (priceInput) {
                    priceInput.addEventListener('input', calculateFees);
                    priceInput.addEventListener('keyup', calculateFees);
                }

                if (auctionSelect) {
                    auctionSelect.addEventListener('change', calculateFees);
                }

                // Initialize shipping calculator
                const vehicleTypeSelect = document.getElementById('shippingVehicleType');
                if (vehicleTypeSelect) {
                    vehicleTypeSelect.addEventListener('change', calculateShipping);
                }

                const shippingAuctionSelect = document.getElementById('shippingAuction');
                if (shippingAuctionSelect) {
                    shippingAuctionSelect.addEventListener('change', function () {
                        updateLocationDropdown();
                        calculateShipping();
                    });
                }

                const shippingInputIds = ['shippingVehicleType', 'shippingAuctionLocation', 'shippingDeparturePort', 'shippingReceivingCountry', 'shippingReceivingPort'];
                shippingInputIds.forEach(function (id) {
                    const input = document.getElementById(id);
                    if (input) {
                        input.addEventListener('change', calculateShipping);
                        input.addEventListener('input', calculateShipping);
                    }
                });

                // Initialize customs calculator
                if (document.getElementById('year')) {
                    populateYears();
                    calculateCustoms();
                }

                const engineInput = document.getElementById('engine');
                if (engineInput) {
                    engineInput.addEventListener('input', function () {
                        updateState('engine', this.value);
                    });
                }

                // Populate location dropdown immediately so search-select list is ready
                if (typeof updateLocationDropdown === 'function') updateLocationDropdown();
                // Run initial shipping calculation after short delay (so Alpine/search-select is ready)
                setTimeout(function () {
                    if (typeof updateLocationDropdown === 'function') updateLocationDropdown();
                    if (typeof calculateShipping === 'function') calculateShipping();
                }, 100);
            });
        </script>
    @endpush
@endsection