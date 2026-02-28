@extends('layouts.app')

@section('title', 'საფულე')

@section('page-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white">საფულე</h1>
        <p class="text-dark-400 mt-1">
            @if(auth()->user()->isAdmin() && auth()->id() !== $user->id)
                {{ $user->full_name ?? $user->username }} - ბალანსის დეტალები
            @else
                თქვენი ბალანსის დეტალები
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if(auth()->user()->isAdmin())
        <a href="{{ route('wallet.create') }}" class="btn-primary inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            ბალანსის შევსება
        </a>
        @endif
        @if(auth()->id() === $user->id)
        <div class="relative" x-data="transferDropdown({
            balance: {{ (float)$user->balance }},
            cars: @js($carsForTransfer->map(fn($c) => ['id' => $c->id, 'vin' => $c->vin, 'make_model' => $c->make_model])->values()->all()),
            carsWithOverpayment: @js($carsWithOverpayment ?? []),
            walletToCarUrl: @js(route('wallet.transfer-wallet-to-car')),
            carToCarUrl: @js(route('wallet.transfer-car-to-car')),
            csrf: @js(csrf_token())
        })">
            <button type="button" @click="open = !open" class="btn-primary inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                თანხის გადატანა
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" @click.outside="open = false" x-cloak
                 class="absolute right-0 mt-2 w-56 rounded-lg bg-dark-800 border border-dark-600 shadow-xl z-50 py-1">
                <a href="#" @click.prevent="openWalletToCarModal(); open = false" class="block px-4 py-2 text-sm text-white hover:bg-dark-700">საფულიდან მანქანაზე</a>
                <a href="#" @click.prevent="openCarToCarModal(); open = false" class="block px-4 py-2 text-sm text-white hover:bg-dark-700">მანქანიდან მანქანაზე</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('content')
<!-- Balance Card -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="glass-card p-6 text-center">
        <p class="text-dark-400 text-sm">მიმდინარე ბალანსი</p>
        <p class="text-4xl font-bold text-green-400 mt-2">{{ $user->getFormattedBalance() }}</p>
    </div>
    
    <div class="glass-card p-6 text-center">
        <p class="text-dark-400 text-sm">ჯამური შევსება</p>
        <p class="text-2xl font-bold text-white mt-2">
            ${{ number_format($transactions->sum('amount'), 2) }}
        </p>
    </div>
    
    <div class="glass-card p-6 text-center">
        <p class="text-dark-400 text-sm">ტრანზაქციების რაოდენობა</p>
        <p class="text-2xl font-bold text-white mt-2">{{ $transactions->total() }}</p>
    </div>
</div>

<!-- Transactions Table -->
<div class="glass-card overflow-hidden">
    <div class="p-4 border-b border-dark-700">
        <h2 class="text-lg font-semibold text-white">ბალანსის შევსების ისტორია</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full table-dark">
            <thead>
                <tr>
                    <th class="text-left">თარიღი</th>
                    <th class="text-right">თანხა</th>
                    <th class="text-left">კომენტარი</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td class="text-dark-300">{{ $transaction->formatted_date }}</td>
                    <td class="text-right text-green-400 font-medium">{{ $transaction->formatted_amount }}</td>
                    <td class="text-dark-400 text-sm">{{ $transaction->comment ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-8 text-dark-500">ტრანზაქციები ვერ მოიძებნა</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $transactions->links() }}</div>

@if(auth()->id() === $user->id)
<!-- Transfer modals -->
<div x-data="transferModals()" x-cloak
     data-balance="{{ (float)$user->balance }}"
     data-cars="{{ json_encode($carsForTransfer->map(fn($c) => ['id' => $c->id, 'vin' => $c->vin, 'make_model' => $c->make_model])->values()->all()) }}"
     data-cars-overpayment="{{ json_encode($carsWithOverpayment ?? []) }}"
     data-wallet-url="{{ route('wallet.transfer-wallet-to-car') }}"
     data-car-url="{{ route('wallet.transfer-car-to-car') }}"
     data-csrf="{{ csrf_token() }}">
    <div x-show="modalWalletToCar" x-transition class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60" @click="modalWalletToCar = false"></div>
        <div class="relative z-10 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="glass-card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">საფულიდან მანქანაზე</h3>
                <p class="text-dark-400 text-sm mb-2">ხელმისაწვდომი ბალანსი: <span class="text-green-400 font-medium" x-text="'$' + balance.toFixed(2)"></span></p>
                <div class="space-y-4">
                    <div class="relative" @click.outside="carSearchWalletOpen = false">
                        <label class="block text-sm font-medium text-dark-300 mb-1">მანქანა (VIN / სახელი)</label>
                        <input type="text"
                               class="form-input w-full pr-10"
                               :placeholder="getCarLabel(walletToCar.car_id, cars) || 'ძიება VIN-ით ან სახელით...'"
                               :value="carSearchWalletOpen ? carSearchWalletQuery : getCarLabel(walletToCar.car_id, cars)"
                               @input="carSearchWalletQuery = $event.target.value; carSearchWalletOpen = true"
                               @focus="carSearchWalletOpen = true"
                               @keydown.escape="carSearchWalletOpen = false"
                               autocomplete="off">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-dark-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <div x-show="carSearchWalletOpen" x-cloak x-transition
                             class="absolute z-50 w-full mt-1 bg-dark-800 border border-dark-600 rounded-lg shadow-xl max-h-52 overflow-auto">
                            <template x-for="c in filterCars(cars, carSearchWalletQuery)" :key="c.id">
                                <button type="button"
                                        class="w-full text-left px-4 py-2.5 text-sm text-white hover:bg-dark-700 focus:bg-dark-700 focus:outline-none border-b border-dark-700 last:border-0"
                                        @click="walletToCar.car_id = c.id; carSearchWalletOpen = false; carSearchWalletQuery = ''">
                                    <span class="font-mono text-primary-400" x-text="c.vin"></span>
                                    <span class="text-dark-400 ml-2" x-text="' — ' + (c.make_model || '')"></span>
                                </button>
                            </template>
                            <div x-show="filterCars(cars, carSearchWalletQuery).length === 0" class="px-4 py-3 text-dark-500 text-sm">შედეგი ვერ მოიძებნა</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">თანხა ($)</label>
                        <input type="number" x-model.number="walletToCar.amount" step="0.01" min="0.01" :max="balance" class="form-input w-full" placeholder="0.00">
                    </div>
                    <p x-show="errorWallet" class="text-sm text-red-400" x-text="errorWallet"></p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="modalWalletToCar = false" class="btn-secondary">გაუქმება</button>
                    <button type="button" @click="submitWalletToCar()" class="btn-primary">გადატანა</button>
                </div>
            </div>
        </div>
    </div>
    <div x-show="modalCarToCar" x-transition class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60" @click="modalCarToCar = false"></div>
        <div class="relative z-10 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="glass-card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">მანქანიდან მანქანაზე</h3>
                <p class="text-dark-400 text-sm mb-4">გადატანა მხოლოდ პლიუს დეპოზიტიდან.</p>
                <div class="space-y-4">
                    <div class="relative" @click.outside="carSearchFromOpen = false">
                        <label class="block text-sm font-medium text-dark-300 mb-1">საიდან (მანქანა პლიუსით)</label>
                        <input type="text"
                               class="form-input w-full pr-10"
                               :placeholder="getCarOverpaymentLabel(carToCar.from_car_id) || 'ძიება VIN-ით ან სახელით...'"
                               :value="carSearchFromOpen ? carSearchFromQuery : getCarOverpaymentLabel(carToCar.from_car_id)"
                               @input="carSearchFromQuery = $event.target.value; carSearchFromOpen = true"
                               @focus="carSearchFromOpen = true"
                               @keydown.escape="carSearchFromOpen = false"
                               autocomplete="off">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-dark-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <div x-show="carSearchFromOpen" x-cloak x-transition
                             class="absolute z-50 w-full mt-1 bg-dark-800 border border-dark-600 rounded-lg shadow-xl max-h-52 overflow-auto">
                            <template x-for="c in filterCarsOverpayment(carSearchFromQuery)" :key="c.id">
                                <button type="button"
                                        class="w-full text-left px-4 py-2.5 text-sm text-white hover:bg-dark-700 focus:bg-dark-700 focus:outline-none border-b border-dark-700 last:border-0"
                                        @click="carToCar.from_car_id = c.id; carSearchFromOpen = false; carSearchFromQuery = ''; updateMaxCarToCar()">
                                    <span class="font-mono text-primary-400" x-text="c.vin"></span>
                                    <span class="text-dark-400 ml-2" x-text="' — $' + c.transferable"></span>
                                </button>
                            </template>
                            <div x-show="filterCarsOverpayment(carSearchFromQuery).length === 0" class="px-4 py-3 text-dark-500 text-sm">შედეგი ვერ მოიძებნა</div>
                        </div>
                    </div>
                    <div class="relative" @click.outside="carSearchToOpen = false">
                        <label class="block text-sm font-medium text-dark-300 mb-1">სად (მიმღები მანქანა)</label>
                        <input type="text"
                               class="form-input w-full pr-10"
                               :placeholder="getCarLabel(carToCar.to_car_id, cars) || 'ძიება VIN-ით ან სახელით...'"
                               :value="carSearchToOpen ? carSearchToQuery : getCarLabel(carToCar.to_car_id, cars)"
                               @input="carSearchToQuery = $event.target.value; carSearchToOpen = true"
                               @focus="carSearchToOpen = true"
                               @keydown.escape="carSearchToOpen = false"
                               autocomplete="off">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-dark-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <div x-show="carSearchToOpen" x-cloak x-transition
                             class="absolute z-50 w-full mt-1 bg-dark-800 border border-dark-600 rounded-lg shadow-xl max-h-52 overflow-auto">
                            <template x-for="c in filterCars(cars, carSearchToQuery)" :key="c.id">
                                <button type="button"
                                        class="w-full text-left px-4 py-2.5 text-sm text-white hover:bg-dark-700 focus:bg-dark-700 focus:outline-none border-b border-dark-700 last:border-0"
                                        @click="carToCar.to_car_id = c.id; carSearchToOpen = false; carSearchToQuery = ''">
                                    <span class="font-mono text-primary-400" x-text="c.vin"></span>
                                    <span class="text-dark-400 ml-2" x-text="' — ' + (c.make_model || '')"></span>
                                </button>
                            </template>
                            <div x-show="filterCars(cars, carSearchToQuery).length === 0" class="px-4 py-3 text-dark-500 text-sm">შედეგი ვერ მოიძებნა</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">თანხა ($) <span x-show="maxCarToCar > 0" x-text="'(მაქს: ' + maxCarToCar.toFixed(2) + ')'"></span></label>
                        <input type="number" x-model.number="carToCar.amount" step="0.01" min="0.01" class="form-input w-full" placeholder="0.00">
                    </div>
                    <p x-show="errorCarToCar" class="text-sm text-red-400" x-text="errorCarToCar"></p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="modalCarToCar = false" class="btn-secondary">გაუქმება</button>
                    <button type="button" @click="submitCarToCar()" class="btn-primary">გადატანა</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if(auth()->id() === $user->id)
@push('scripts')
<script>
function transferDropdown(config) {
    return { open: false, ...config,
        openWalletToCarModal() { if (window.walletTransferModals) window.walletTransferModals.openWalletToCar(); },
        openCarToCarModal() { if (window.walletTransferModals) window.walletTransferModals.openCarToCar(); }
    };
}
function transferModals() {
    const state = {
        modalWalletToCar: false, modalCarToCar: false,
        balance: 0, cars: [], carsWithOverpayment: [], walletToCarUrl: '', carToCarUrl: '', csrf: '',
        walletToCar: { car_id: '', amount: '' }, carToCar: { from_car_id: '', to_car_id: '', amount: '' },
        maxCarToCar: 0, errorWallet: '', errorCarToCar: '',
        carSearchWalletOpen: false, carSearchWalletQuery: '',
        carSearchFromOpen: false, carSearchFromQuery: '',
        carSearchToOpen: false, carSearchToQuery: '',
        filterCars(list, q) {
            if (!list || !list.length) return [];
            q = (q || '').toLowerCase().trim();
            if (!q) return list;
            return list.filter(function(c) { var vin = (c.vin || '').toLowerCase(); var name = (c.make_model || '').toLowerCase(); return vin.indexOf(q) !== -1 || name.indexOf(q) !== -1; });
        },
        getCarLabel(id, list) {
            if (!id || !list) return '';
            var c = list.find(function(x) { return String(x.id) === String(id); });
            return c ? (c.vin + ' — ' + (c.make_model || '')) : '';
        },
        filterCarsOverpayment(q) {
            if (!this.carsWithOverpayment || !this.carsWithOverpayment.length) return [];
            q = (q || '').toLowerCase().trim();
            if (!q) return this.carsWithOverpayment;
            return this.carsWithOverpayment.filter(function(c) { var vin = (c.vin || '').toLowerCase(); var name = (c.make_model || '').toLowerCase(); return vin.indexOf(q) !== -1 || name.indexOf(q) !== -1; });
        },
        getCarOverpaymentLabel(id) {
            if (!id || !this.carsWithOverpayment) return '';
            var c = this.carsWithOverpayment.find(function(x) { return String(x.id) === String(id); });
            return c ? (c.vin + ' — $' + c.transferable) : '';
        },
        init() {
            const el = this.$el;
            this.balance = parseFloat(el.dataset.balance || 0);
            this.cars = JSON.parse(el.dataset.cars || '[]');
            this.carsWithOverpayment = JSON.parse(el.dataset.carsOverpayment || '[]');
            this.walletToCarUrl = el.dataset.walletUrl || '';
            this.carToCarUrl = el.dataset.carUrl || '';
            this.csrf = el.dataset.csrf || '';
            window.walletTransferModals = this;
        },
        openWalletToCar() { this.errorWallet = ''; this.walletToCar = { car_id: '', amount: '' }; this.carSearchWalletOpen = false; this.carSearchWalletQuery = ''; this.modalWalletToCar = true; },
        openCarToCar() { this.errorCarToCar = ''; this.carToCar = { from_car_id: '', to_car_id: '', amount: '' }; this.maxCarToCar = 0; this.carSearchFromOpen = false; this.carSearchFromQuery = ''; this.carSearchToOpen = false; this.carSearchToQuery = ''; this.modalCarToCar = true; },
        updateMaxCarToCar() {
            const c = this.carsWithOverpayment.find(x => String(x.id) === String(this.carToCar.from_car_id));
            this.maxCarToCar = c ? parseFloat(c.transferable) : 0;
        },
        async submitWalletToCar() {
            this.errorWallet = '';
            if (!this.walletToCar.car_id) { this.errorWallet = 'აირჩიეთ მანქანა'; return; }
            const amount = parseFloat(this.walletToCar.amount);
            if (!amount || amount < 0.01) { this.errorWallet = 'შეიყვანეთ თანხა'; return; }
            if (amount > this.balance) { this.errorWallet = 'საფულეში არასაკმარისი თანხა'; return; }
            const res = await fetch(this.walletToCarUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf }, body: JSON.stringify({ car_id: this.walletToCar.car_id, amount: amount, _token: this.csrf }) });
            const data = await res.json();
            if (data.success) { this.modalWalletToCar = false; this.balance = data.balance ?? this.balance; location.reload(); }
            else { this.errorWallet = data.message || 'შეცდომა'; }
        },
        async submitCarToCar() {
            this.errorCarToCar = '';
            if (!this.carToCar.from_car_id || !this.carToCar.to_car_id) { this.errorCarToCar = 'აირჩიეთ ორივე მანქანა'; return; }
            if (this.carToCar.from_car_id === this.carToCar.to_car_id) { this.errorCarToCar = 'აირჩიეთ სხვადასხვა მანქანა'; return; }
            const amount = parseFloat(this.carToCar.amount);
            if (!amount || amount < 0.01) { this.errorCarToCar = 'შეიყვანეთ თანხა'; return; }
            if (amount > this.maxCarToCar) { this.errorCarToCar = 'თანხა აღემატება გადასატან ლიმიტს'; return; }
            const res = await fetch(this.carToCarUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf }, body: JSON.stringify({ from_car_id: this.carToCar.from_car_id, to_car_id: this.carToCar.to_car_id, amount: amount, _token: this.csrf }) });
            const data = await res.json();
            if (data.success) { this.modalCarToCar = false; location.reload(); }
            else { this.errorCarToCar = data.message || 'შეცდომა'; }
        }
    };
    return state;
}
</script>
@endpush
@endif
