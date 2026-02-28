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
    <div class="flex-shrink-0">
        <a href="{{ route('cars.create') }}" class="btn-primary inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            დამატება
        </a>
    </div>
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
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @foreach($cars as $car)
    <div class="glass-card glass-card-hover overflow-hidden">
        <!-- Photo -->
        <a href="{{ route('cars.show', $car) }}" class="block relative aspect-video bg-dark-800 overflow-hidden">
            <img src="{{ $car->main_photo_url }}" 
                 alt="{{ $car->make_model }}" 
                 class="w-full h-full object-cover"
                 onerror="this.src='/images/no-photo.png'">
            
            <!-- Status Badge -->
            <div class="absolute top-2 right-2">
                <span class="badge-{{ $car->status }} px-2 py-1 rounded-full text-xs font-medium">
                    {{ $car->status_label }}
                </span>
            </div>
            
            @if($car->hasDebt())
            <div class="absolute top-2 left-2">
                <span class="bg-red-500/90 text-white px-2 py-1 rounded-full text-xs font-medium">
                    დავალიანება: {{ $car->formatted_debt }}
                </span>
            </div>
            @endif
        </a>
        
        <!-- Info -->
        <div class="p-4">
            <h3 class="font-semibold text-white truncate">{{ $car->make_model }}</h3>
            <p class="text-sm text-dark-400 mt-1">{{ $car->year }} | {{ $car->vin }}</p>
            
            @if($car->lot_number)
            <p class="text-xs text-dark-500 mt-1">ლოტი: {{ $car->lot_number }}</p>
            @endif
            
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5">
                <span class="text-sm text-dark-300">{{ $car->getClientDisplayName() }}</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('cars.edit', $car) }}" 
                       class="p-2 text-dark-400 hover:text-primary-400 transition-colors"
                       title="რედაქტირება">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <a href="{{ route('cars.show', $car) }}" 
                       class="p-2 text-dark-400 hover:text-primary-400 transition-colors"
                       title="ნახვა">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $cars->links() }}
</div>
@else
<div class="glass-card p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
    </svg>
    <h3 class="text-lg font-medium text-white mb-2">მანქანები ვერ მოიძებნა</h3>
    <p class="text-dark-400 mb-4">დაამატეთ ახალი მანქანა ან შეცვალეთ ფილტრები</p>
    <a href="{{ route('cars.create') }}" class="btn-primary inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        დამატება
    </a>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        // Auto-submit on Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }
});
</script>
@endpush
@endsection
