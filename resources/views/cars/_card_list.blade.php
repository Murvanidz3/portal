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

        @if($car->hasDebt() && !auth()->user()->isClient())
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
                @if(auth()->user()->isAdmin())
                <a href="{{ route('cars.edit', $car) }}"
                   class="p-2 text-dark-400 hover:text-primary-400 transition-colors"
                   title="რედაქტირება">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                @endif
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
