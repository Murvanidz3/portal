@extends('layouts.app')

@section('title', 'მანქანები')

@section('page-header')
<div class="flex flex-col sm:flex-row sm:items-center gap-4">
    <div class="flex-shrink-0">
        <h1 class="text-2xl font-bold text-white">მანქანები</h1>
        <p class="text-dark-400 mt-1">სულ: {{ $cars->total() }} მანქანა</p>
    </div>
    <div class="flex-1 flex justify-center">
        <form method="GET" action="{{ route('cars.index') }}" class="w-full flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="ძიება..." 
                   class="form-input flex-1 min-w-0">
            <select name="status" class="form-input w-auto min-w-[160px]" onchange="this.form.submit()">
                <option value="">ყველა სტატუსი</option>
                @foreach(\App\Models\Car::getStatusOptions() as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary px-3 py-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            @if(request()->hasAny(['search', 'status', 'dealer_id']))
            <a href="{{ route('cars.index') }}" class="btn-secondary text-red-400 px-3 py-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
            @endif
            @if(request('dealer_id'))
                <input type="hidden" name="dealer_id" value="{{ request('dealer_id') }}">
            @endif
        </form>
    </div>
    @if(auth()->user()->isAdmin())
    <div class="flex-shrink-0">
        <a href="{{ route('cars.create') }}" class="btn-primary inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            დამატება
        </a>
    </div>
    @endif
</div>
@endsection

@section('content')
@if(auth()->user()->isAdmin() && $dealers->count() > 0)
<!-- Dealer Filter -->
<div class="glass-card p-4 mb-6">
    <form method="GET" action="{{ route('cars.index') }}" class="flex flex-wrap gap-4">
        <div class="w-full sm:w-auto">
            <select name="dealer_id" class="form-input w-full" onchange="this.form.submit()">
                <option value="">ყველა დილერი</option>
                @foreach($dealers as $dealer)
                    <option value="{{ $dealer->id }}" {{ request('dealer_id') == $dealer->id ? 'selected' : '' }}>
                        {{ $dealer->full_name ?? $dealer->username }}
                    </option>
                @endforeach
            </select>
        </div>
        @if(request()->hasAny(['search', 'status', 'dealer_id']))
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
        @endif
    </form>
</div>
@endif

<!-- Status Tabs -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('cars.index') }}" 
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ !request('status') ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300 hover:bg-dark-700' }}">
        ყველა ({{ array_sum($statusCounts) }})
    </a>
    @foreach(\App\Models\Car::getStatuses() as $key => $status)
        <a href="{{ route('cars.index', ['status' => $key]) }}" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request('status') == $key ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300 hover:bg-dark-700' }}">
            {{ $status['label'] }} ({{ $statusCounts[$key] ?? 0 }})
        </a>
    @endforeach
</div>

<!-- Cars Grid -->
@if($cars->count() > 0)
<div id="cars-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @include('cars._card_list', ['cars' => $cars])
</div>

<!-- Load More -->
@if($cars->hasMorePages())
<div id="load-more-wrap" class="mt-8 flex justify-center">
    <button id="load-more-btn"
        data-next-page="{{ $cars->currentPage() + 1 }}"
        data-url="{{ route('cars.index') }}"
        style="min-width:280px;"
        class="inline-flex items-center justify-center gap-3 py-4 px-10 bg-dark-800 hover:bg-dark-700 border border-white/10 hover:border-primary-500/40 text-white text-base rounded-2xl font-medium transition-all shadow-lg">
        <span id="load-more-text">მეტი</span>
        <svg id="load-more-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
        <svg id="load-more-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </button>
</div>
@endif
@else
<div class="glass-card p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
    </svg>
    <h3 class="text-lg font-medium text-white mb-2">მანქანები ვერ მოიძებნა</h3>
    <p class="text-dark-400 mb-4">დაამატეთ ახალი მანქანა ან შეცვალეთ ფილტრები</p>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('cars.create') }}" class="btn-primary inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        დამატება
    </a>
    @endif
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search auto-submit on Enter
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') this.closest('form').submit();
        });
    }

    // Load More
    const btn = document.getElementById('load-more-btn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        const page    = btn.dataset.nextPage;
        const baseUrl = btn.dataset.url;

        // Collect current filters from URL
        const params  = new URLSearchParams(window.location.search);
        params.set('page', page);

        // Show spinner
        document.getElementById('load-more-text').textContent = '';
        document.getElementById('load-more-icon').classList.add('hidden');
        document.getElementById('load-more-spinner').classList.remove('hidden');
        btn.disabled = true;

        fetch(`${baseUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            // Append new cards
            document.getElementById('cars-grid').insertAdjacentHTML('beforeend', data.html);

            if (data.has_more) {
                btn.dataset.nextPage = data.next_page;
                document.getElementById('load-more-text').textContent = 'მეტი';
                document.getElementById('load-more-icon').classList.remove('hidden');
                document.getElementById('load-more-spinner').classList.add('hidden');
                btn.disabled = false;
            } else {
                document.getElementById('load-more-wrap').remove();
            }
        })
        .catch(() => {
            document.getElementById('load-more-text').textContent = 'მეტი';
            document.getElementById('load-more-icon').classList.remove('hidden');
            document.getElementById('load-more-spinner').classList.add('hidden');
            btn.disabled = false;
        });
    });
});
</script>
@endpush
@endsection
