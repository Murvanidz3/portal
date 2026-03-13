@extends('layouts.app')

@section('title', $car->make_model)

@section('page-header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('cars.index') }}" class="p-2 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors">
                <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $car->make_model }}</h1>
                <p class="text-dark-400 mt-1">{{ $car->year }} | {{ $car->vin }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <span class="badge-{{ $car->status }} px-3 py-1 rounded-full text-sm font-medium">
                {{ $car->status_label }}
            </span>

            {{-- Invoice Buttons - admin and dealer only --}}
            @if(!auth()->user()->isClient())
                @if($car->vehicle_cost > 0)
                    <a href="{{ route('invoices.generate-from-car', [$car, 'vehicle']) }}" target="_blank"
                        class="flex items-center gap-2 px-3 py-1.5 bg-primary-500/20 hover:bg-primary-500/30 text-primary-400 rounded-lg text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        ავტომობილის ინვოისი
                    </a>
                @endif

                @if($car->shipping_cost > 0 || $car->additional_cost > 0)
                    <a href="{{ route('invoices.generate-from-car', [$car, 'shipping']) }}" target="_blank"
                        class="flex items-center gap-2 px-3 py-1.5 bg-primary-500/20 hover:bg-primary-500/30 text-primary-400 rounded-lg text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        ტრანსპორტირების ინვოისი
                    </a>
                @endif
            @endif

            {{-- Edit button - admin only --}}
            @if(auth()->user()->isAdmin())
            <a href="{{ route('cars.edit', $car) }}"
                class="flex items-center gap-2 px-3 py-1.5 bg-primary-500 hover:bg-primary-600 text-white rounded-lg text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                რედაქტირება
            </a>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Main Photo Slider -->
            @php
                $allPhotos = $car->files->sortByDesc(function ($file) use ($car) {
                    return $file->file_path === $car->main_photo ? 1 : 0;
                })->values();
            @endphp
            <div class="glass-card overflow-hidden" x-data="photoSlider({{ $allPhotos->count() }})">
                <div class="relative aspect-video bg-dark-800">
                    @if($allPhotos->count() > 0)
                        @foreach($allPhotos as $index => $photo)
                            <img src="{{ $photo->url }}" alt="{{ $car->make_model }}"
                                class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300"
                                :class="currentSlide === {{ $index }} ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                                @click="openLightbox({{ $index }})">
                        @endforeach
                    @else
                        <img src="/images/no-photo.png" alt="{{ $car->make_model }}" class="w-full h-full object-cover">
                    @endif

                    @if($allPhotos->count() > 1)
                        <!-- Slider Controls -->
                        <button @click="prevSlide()"
                            class="absolute left-2 top-1/2 -translate-y-1/2 z-20 p-2 rounded-full bg-black/50 hover:bg-black/70 text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button @click="nextSlide()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 z-20 p-2 rounded-full bg-black/50 hover:bg-black/70 text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Slide Counter -->
                        <div
                            class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 px-3 py-1 rounded-full bg-black/50 text-white text-sm">
                            <span x-text="currentSlide + 1"></span> / {{ $allPhotos->count() }}
                        </div>

                        <!-- Dot Indicators (for small number of photos) -->
                        @if($allPhotos->count() <= 10)
                            <div class="absolute bottom-12 left-1/2 -translate-x-1/2 z-20 flex gap-1.5">
                                @foreach($allPhotos as $index => $photo)
                                    <button @click="goToSlide({{ $index }})"
                                        :class="currentSlide === {{ $index }} ? 'bg-white' : 'bg-white/40'"
                                        class="w-2 h-2 rounded-full transition-all hover:bg-white/70"></button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Lightbox Modal -->
                @php $lightboxUrls = $allPhotos->pluck('url')->values()->toJson(); @endphp
                <div x-show="lightboxOpen" x-cloak
                    @keydown.escape.window="closeLightbox()"
                    @keydown.arrow-left.window="if(zoomLevel === 1) prevSlide()"
                    @keydown.arrow-right.window="if(zoomLevel === 1) nextSlide()"
                    class="fixed inset-0 z-50 select-none"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                    {{-- Layer 1: backdrop — click here closes lightbox --}}
                    <div class="absolute inset-0 bg-black/90 backdrop-blur-md"
                        @click="closeLightbox()"></div>

                    {{-- Layer 2: UI controls (pointer events, no click-close) --}}

                    {{-- Close button --}}
                    <button @click.stop="closeLightbox()"
                        class="absolute top-4 right-4 z-[70] p-2 rounded-full bg-white/10 hover:bg-white/25 text-white transition-all" title="დახურვა (Esc)">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Zoom % badge --}}
                    <div x-show="zoomLevel > 1" x-cloak
                        class="absolute top-4 left-4 z-[70] px-3 py-1 rounded-full bg-black/60 text-white text-sm font-medium pointer-events-none">
                        <span x-text="Math.round(zoomLevel * 100) + '%'"></span>
                    </div>

                    @if($allPhotos->count() > 0)
                        {{-- Layer 3: image — wheel & pan handled via vanilla JS in init() --}}
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="relative flex items-center justify-center pointer-events-auto"
                                style="max-width: 90vw; max-height: 88vh; width: 90vw; height: 88vh;"
                                x-ref="imgContainer"
                                @click.stop
                                @touchstart.passive="onTouchStart($event)"
                                @touchend.passive="onTouchEnd($event)">
                                <img x-ref="lightboxImg"
                                    :src="photoUrls[currentSlide]"
                                    alt="{{ $car->make_model }}"
                                    :style="`
                                        transform: scale(${zoomLevel}) translate(${panX / zoomLevel}px, ${panY / zoomLevel}px);
                                        transform-origin: center center;
                                        transition: ${isPanning ? 'none' : 'transform 0.15s ease'};
                                        cursor: ${!isMobile && zoomLevel > 1 ? (isPanning ? 'grabbing' : 'grab') : 'default'};
                                        max-width: 90vw;
                                        max-height: 88vh;
                                    `"
                                    class="object-contain block"
                                    draggable="false">
                            </div>
                        </div>

                        {{-- Scroll hint — desktop only --}}
                        <div x-show="showScrollHint && !isMobile" x-cloak
                            class="absolute bottom-14 left-1/2 -translate-x-1/2 z-[70] px-4 py-2 rounded-full bg-black/60 text-white/70 text-xs pointer-events-none">
                            გადაახვიეთ scroll-ით გასადიდებლად
                        </div>

                        @if($allPhotos->count() > 1)
                            <button @click.stop="prevSlide()"
                                class="absolute left-4 top-1/2 -translate-y-1/2 z-[70] p-3 rounded-full bg-white/10 hover:bg-white/25 text-white transition-all">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button @click.stop="nextSlide()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 z-[70] p-3 rounded-full bg-white/10 hover:bg-white/25 text-white transition-all">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-[70] px-4 py-2 rounded-full bg-black/50 text-white text-sm pointer-events-none">
                                <span x-text="currentSlide + 1"></span> / {{ $allPhotos->count() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Photo Gallery Tabs -->
            <div class="glass-card p-6" x-data="photoGallery()">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 border-b border-white/5 pb-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <button @click="switchTab('auction')"
                            :class="activeTab === 'auction' ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            აუქციონი ({{ $auctionPhotos->count() }})
                        </button>
                        <button @click="switchTab('pickup')"
                            :class="activeTab === 'pickup' ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            აყვანა ({{ $pickupPhotos->count() }})
                        </button>
                        <button @click="switchTab('warehouse')"
                            :class="activeTab === 'warehouse' ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            საწყობი ({{ $warehousePhotos->count() }})
                        </button>
                        <button @click="switchTab('poti')"
                            :class="activeTab === 'poti' ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            ფოთი ({{ $potiPhotos->count() }})
                        </button>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div class="flex items-center gap-2">
                            <!-- Selected count badge -->
                            <span x-show="selectedPhotos.length > 0" x-cloak
                                class="px-2 py-1 bg-primary-500/20 text-primary-400 rounded text-xs font-medium">
                                <span x-text="selectedPhotos.length"></span> მონიშნული
                            </span>

                            <!-- Select All / Deselect All -->
                            <button @click="toggleSelectAll()"
                                class="px-3 py-1.5 bg-dark-800 hover:bg-dark-700 text-dark-300 hover:text-white rounded text-xs font-medium transition-all">
                                <span x-text="isAllSelected() ? 'გაუქმება' : 'ყველას მონიშვნა'"></span>
                            </button>

                            <!-- Delete Selected -->
                            <button @click="deleteSelected()" x-show="selectedPhotos.length > 0" x-cloak
                                class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded text-xs font-medium transition-all flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                წაშლა (<span x-text="selectedPhotos.length"></span>)
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Auction Photos -->
                <div x-show="activeTab === 'auction'" class="grid grid-cols-4 gap-2">
                    @forelse($auctionPhotos as $photo)
                        <div class="relative aspect-video bg-dark-800 rounded-lg overflow-hidden group"
                            :class="selectedPhotos.includes({{ $photo->id }}) ? 'ring-2 ring-primary-500' : ''">
                            <img src="{{ $photo->url }}" alt=""
                                class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                @click="setSliderPhoto({{ $car->files->search(fn($f) => $f->id === $photo->id) }})">
                            @if(auth()->user()->isAdmin())
                                <!-- Checkbox -->
                                <div class="absolute top-1 left-1">
                                    <input type="checkbox" :checked="selectedPhotos.includes({{ $photo->id }})"
                                        @change="togglePhoto({{ $photo->id }})"
                                        class="w-4 h-4 rounded border-dark-600 bg-dark-800/80 text-primary-500 focus:ring-primary-500 cursor-pointer">
                                </div>
                                <!-- Action buttons -->
                                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click.stop="setMainPhoto({{ $photo->id }})"
                                        class="p-1.5 bg-primary-500 hover:bg-primary-600 rounded text-white transition-colors"
                                        title="მთავარ ფოტოდ დაყენება">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="deleteSinglePhoto({{ $photo->id }})"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 rounded text-white transition-colors"
                                        title="წაშლა">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="col-span-4 text-dark-500 text-center py-4">ფოტოები არ არის</p>
                    @endforelse
                </div>

                <!-- Pickup Photos -->
                <div x-show="activeTab === 'pickup'" x-cloak class="grid grid-cols-4 gap-2">
                    @forelse($pickupPhotos as $photo)
                        <div class="relative aspect-video bg-dark-800 rounded-lg overflow-hidden group"
                            :class="selectedPhotos.includes({{ $photo->id }}) ? 'ring-2 ring-primary-500' : ''">
                            <img src="{{ $photo->url }}" alt=""
                                class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                @click="setSliderPhoto({{ $car->files->search(fn($f) => $f->id === $photo->id) }})">
                            @if(auth()->user()->isAdmin())
                                <!-- Checkbox -->
                                <div class="absolute top-1 left-1">
                                    <input type="checkbox" :checked="selectedPhotos.includes({{ $photo->id }})"
                                        @change="togglePhoto({{ $photo->id }})"
                                        class="w-4 h-4 rounded border-dark-600 bg-dark-800/80 text-primary-500 focus:ring-primary-500 cursor-pointer">
                                </div>
                                <!-- Action buttons -->
                                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click.stop="setMainPhoto({{ $photo->id }})"
                                        class="p-1.5 bg-primary-500 hover:bg-primary-600 rounded text-white transition-colors"
                                        title="მთავარ ფოტოდ დაყენება">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="deleteSinglePhoto({{ $photo->id }})"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 rounded text-white transition-colors"
                                        title="წაშლა">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="col-span-4 text-dark-500 text-center py-4">ფოტოები არ არის</p>
                    @endforelse
                </div>

                <!-- Warehouse Photos -->
                <div x-show="activeTab === 'warehouse'" x-cloak class="grid grid-cols-4 gap-2">
                    @forelse($warehousePhotos as $photo)
                        <div class="relative aspect-video bg-dark-800 rounded-lg overflow-hidden group"
                            :class="selectedPhotos.includes({{ $photo->id }}) ? 'ring-2 ring-primary-500' : ''">
                            <img src="{{ $photo->url }}" alt=""
                                class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                @click="setSliderPhoto({{ $car->files->search(fn($f) => $f->id === $photo->id) }})">
                            @if(auth()->user()->isAdmin())
                                <!-- Checkbox -->
                                <div class="absolute top-1 left-1">
                                    <input type="checkbox" :checked="selectedPhotos.includes({{ $photo->id }})"
                                        @change="togglePhoto({{ $photo->id }})"
                                        class="w-4 h-4 rounded border-dark-600 bg-dark-800/80 text-primary-500 focus:ring-primary-500 cursor-pointer">
                                </div>
                                <!-- Action buttons -->
                                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click.stop="setMainPhoto({{ $photo->id }})"
                                        class="p-1.5 bg-primary-500 hover:bg-primary-600 rounded text-white transition-colors"
                                        title="მთავარ ფოტოდ დაყენება">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="deleteSinglePhoto({{ $photo->id }})"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 rounded text-white transition-colors"
                                        title="წაშლა">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="col-span-4 text-dark-500 text-center py-4">ფოტოები არ არის</p>
                    @endforelse
                </div>

                <!-- Poti Photos -->
                <div x-show="activeTab === 'poti'" x-cloak class="grid grid-cols-4 gap-2">
                    @forelse($potiPhotos as $photo)
                        <div class="relative aspect-video bg-dark-800 rounded-lg overflow-hidden group"
                            :class="selectedPhotos.includes({{ $photo->id }}) ? 'ring-2 ring-primary-500' : ''">
                            <img src="{{ $photo->url }}" alt=""
                                class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                @click="setSliderPhoto({{ $car->files->search(fn($f) => $f->id === $photo->id) }})">
                            @if(auth()->user()->isAdmin())
                                <!-- Checkbox -->
                                <div class="absolute top-1 left-1">
                                    <input type="checkbox" :checked="selectedPhotos.includes({{ $photo->id }})"
                                        @change="togglePhoto({{ $photo->id }})"
                                        class="w-4 h-4 rounded border-dark-600 bg-dark-800/80 text-primary-500 focus:ring-primary-500 cursor-pointer">
                                </div>
                                <!-- Action buttons -->
                                <div class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click.stop="setMainPhoto({{ $photo->id }})"
                                        class="p-1.5 bg-primary-500 hover:bg-primary-600 rounded text-white transition-colors"
                                        title="მთავარ ფოტოდ დაყენება">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="deleteSinglePhoto({{ $photo->id }})"
                                        class="p-1.5 bg-red-500 hover:bg-red-600 rounded text-white transition-colors"
                                        title="წაშლა">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="col-span-4 text-dark-500 text-center py-4">ფოტოები არ არის</p>
                    @endforelse
                </div>
            </div>

            <!-- Notes -->
            @if($car->notes)
                <div class="glass-card p-6">
                    <h3 class="text-lg font-semibold text-white mb-3">შენიშვნები</h3>
                    <p class="text-dark-300">{{ $car->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Change (admin only) -->
            @if(auth()->user()->isAdmin())
                <div class="glass-card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">სტატუსის შეცვლა</h3>

                    <form method="POST" action="{{ route('cars.update-status', $car) }}">
                        @csrf
                        @method('PATCH')

                        <select name="status" class="form-input w-full mb-3">
                            @foreach(\App\Models\Car::getStatusOptions() as $key => $label)
                                <option value="{{ $key }}" {{ $car->status == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn-primary w-full">განახლება</button>
                    </form>
                </div>
            @endif

            <!-- Car Details -->
            <div class="glass-card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">დეტალები</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-dark-400">VIN:</dt>
                        <dd class="text-white font-mono text-sm">{{ $car->vin }}</dd>
                    </div>
                    @if($car->lot_number)
                        <div class="flex justify-between">
                            <dt class="text-dark-400">ლოტი:</dt>
                            <dd class="text-white">{{ $car->lot_number }}</dd>
                        </div>
                    @endif
                    @if($car->auction_name)
                        <div class="flex justify-between">
                            <dt class="text-dark-400">აუქციონი:</dt>
                            <dd class="text-white">{{ $car->auction_name }}</dd>
                        </div>
                    @endif
                    @if($car->container_number)
                        <div class="flex justify-between">
                            <dt class="text-dark-400">კონტეინერი:</dt>
                            <dd class="text-white font-mono">{{ $car->container_number }}</dd>
                        </div>
                    @endif
                    @if($car->purchase_date)
                        <div class="flex justify-between">
                            <dt class="text-dark-400">შეძენის თარიღი:</dt>
                            <dd class="text-white">{{ $car->purchase_date->format('d.m.Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Recipient (მიმღები) Info - editable on view -->
            <div class="glass-card p-6" x-data="recipientCard({
                    updateUrl: @js(route('cars.update-recipient', $car)),
                    displayName: @js($car->getClientDisplayName()),
                    displayPhone: @js($car->getClientPhone()),
                    displayIdNumber: @js($car->client_id_number ?? ''),
                    clientName: @js($car->client_name ?? ''),
                    clientPhone: @js($car->client_phone ?? ''),
                    clientIdNumber: @js($car->client_id_number ?? ''),
                    clientUserId: @js($car->client_user_id ?? ''),
                    canEdit: @json($canEditCar),
                    clients: @js($clients->map(fn($u) => ['id' => $u->id, 'name' => $u->full_name ?? $u->username])->values()->all())
                })">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-white">მიმღები</h3>
                    @if($canEditCar)
                        <div class="flex items-center gap-2" x-show="canEdit">
                            <template x-if="!editing">
                                <button type="button" @click="startEdit()"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium rounded-lg bg-primary-500/20 hover:bg-primary-500/30 text-primary-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    რედაქტირება
                                </button>
                            </template>
                            <template x-if="editing">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="save()" :disabled="saving"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium rounded-lg bg-green-500/20 hover:bg-green-500/30 text-green-400 transition-colors disabled:opacity-50">
                                        <span x-show="!saving">შენახვა</span>
                                        <span x-show="saving">იგზავნება...</span>
                                    </button>
                                    <button type="button" @click="cancelEdit()" :disabled="saving"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium rounded-lg bg-dark-600 hover:bg-dark-500 text-dark-300 transition-colors">
                                        გაუქმება
                                    </button>
                                </div>
                            </template>
                        </div>
                    @endif
                </div>

                <div x-show="!editing" class="space-y-3">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-dark-400">სახელი:</dt>
                            <dd class="text-white" x-text="displayName || '—'"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-dark-400">ტელეფონი:</dt>
                            <dd class="text-white" x-text="displayPhone || '—'"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-dark-400">პ/ნ:</dt>
                            <dd class="text-white" x-text="displayIdNumber || '—'"></dd>
                        </div>
                    </dl>
                </div>

                <form x-show="editing" @submit.prevent="save()" class="space-y-4" x-cloak>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">მომხმარებელი (სისტემიდან)</label>
                        <select x-model="clientUserId"
                            class="input-field w-full bg-dark-800 border-dark-600 text-white rounded-lg px-3 py-2 text-sm">
                            <option value="">— ხელით შევსება —</option>
                            <template x-for="c in clients" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">სახელი</label>
                        <input type="text" x-model="clientName" maxlength="100"
                            class="input-field w-full bg-dark-800 border-dark-600 text-white rounded-lg px-3 py-2 text-sm"
                            placeholder="სახელი">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">ტელეფონი</label>
                        <input type="text" x-model="clientPhone" maxlength="50"
                            class="input-field w-full bg-dark-800 border-dark-600 text-white rounded-lg px-3 py-2 text-sm"
                            placeholder="ტელეფონი">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">პ/ნ</label>
                        <input type="text" x-model="clientIdNumber" maxlength="50"
                            class="input-field w-full bg-dark-800 border-dark-600 text-white rounded-lg px-3 py-2 text-sm"
                            placeholder="პირადი ნომერი">
                    </div>
                    <p x-show="errorMsg" class="text-sm text-red-400" x-text="errorMsg"></p>
                </form>
            </div>

            <!-- Financial Info -->
            <div class="glass-card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">ფინანსები</h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-dark-400">მანქანის ფასი:</dt>
                        <dd class="text-white">${{ number_format($car->vehicle_cost, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-dark-400">ტრანსპორტირება:</dt>
                        <dd class="text-white">${{ number_format($car->shipping_cost + $car->additional_cost, 2) }}</dd>
                    </div>
                    {{-- დამატებითი ხარჯები დამალულია view-დან და დაჯამებულია ტრანსპორტირებასთან --}}

                    <div class="border-t border-white/10 pt-3">
                        <div class="flex justify-between">
                            <dt class="text-dark-400 font-medium">სულ:</dt>
                            <dd class="text-white font-bold">{{ $car->formatted_total }}</dd>
                        </div>
                        <div class="flex justify-between mt-2">
                            <dt class="text-dark-400">გადახდილი:</dt>
                            <dd class="text-green-400">{{ $car->formatted_paid }}</dd>
                        </div>
                        <div class="flex justify-between mt-2">
                            <dt class="text-dark-400 font-medium">
                                {{ $car->hasOverpayment() ? 'პლიუს ბალანსი:' : 'დარჩენილი:' }}
                            </dt>
                            <dd
                                class="{{ $car->hasOverpayment() ? 'text-green-400' : ($car->hasDebt() ? 'text-red-400' : 'text-green-400') }} font-bold">
                                {{ $car->hasOverpayment() ? $car->formatted_overpayment : $car->formatted_debt }}
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>

            <!-- Transactions (payment history - visible to all) -->
            @if($car->transactions->count() > 0)
                <div class="glass-card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">გადახდები</h3>

                    <div class="space-y-2">
                        @foreach($car->transactions as $transaction)
                            <div class="flex justify-between items-center p-2 rounded-lg bg-dark-800/50">
                                <div>
                                    <p class="text-sm text-white">${{ number_format($transaction->amount, 2) }}</p>
                                    <p class="text-xs text-dark-500">{{ $transaction->payment_date->format('d.m.Y') }}</p>
                                </div>
                                @if(!auth()->user()->isClient())
                                    <span class="text-xs text-dark-400">{{ $transaction->purpose_label }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Dates section for clients --}}
            @if(auth()->user()->isClient())
                <div class="glass-card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">თარიღები</h3>
                    <dl class="space-y-3">
                        @if($car->purchase_date)
                            <div class="flex justify-between">
                                <dt class="text-dark-400">შეძენის თარიღი:</dt>
                                <dd class="text-white">{{ $car->purchase_date->format('d.m.Y') }}</dd>
                            </div>
                        @endif
                        @if($car->loading_date)
                            <div class="flex justify-between">
                                <dt class="text-dark-400">ჩატვირთვის თარიღი:</dt>
                                <dd class="text-white">{{ $car->loading_date->format('d.m.Y') }}</dd>
                            </div>
                        @endif
                        @if($car->estimated_arrival_date)
                            <div class="flex justify-between">
                                <dt class="text-dark-400">სავარაუდო ჩამოსვლა:</dt>
                                <dd class="text-white">{{ $car->estimated_arrival_date->format('d.m.Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // Recipient (მიმღები) card – inline edit/save
            function recipientCard(config) {
                return {
                    ...config,
                    editing: false,
                    saving: false,
                    errorMsg: '',
                    startEdit() {
                        this.editing = true;
                        this.errorMsg = '';
                    },
                    cancelEdit() {
                        this.editing = false;
                        this.errorMsg = '';
                    },
                    save() {
                        this.saving = true;
                        this.errorMsg = '';
                        const body = {
                            client_name: this.clientName || null,
                            client_phone: this.clientPhone || null,
                            client_id_number: this.clientIdNumber || null,
                            client_user_id: this.clientUserId || null
                        };
                        fetch(this.updateUrl, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(body)
                        })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success && data.recipient) {
                                    this.displayName = data.recipient.name || '—';
                                    this.displayPhone = data.recipient.phone || '—';
                                    this.displayIdNumber = data.recipient.id_number || '—';
                                    this.editing = false;
                                } else {
                                    this.errorMsg = data.message || 'შეცდომა';
                                }
                            })
                            .catch(() => {
                                this.errorMsg = 'შეცდომა ქსელში';
                            })
                            .finally(() => {
                                this.saving = false;
                            });
                    }
                };
            }

            // Photo Slider Alpine.js Component
            function photoSlider(totalPhotos) {
                return {
                    currentSlide: 0,
                    totalSlides: totalPhotos,
                    lightboxOpen: false,
                    zoomLevel: 1,
                    panX: 0,
                    panY: 0,
                    isPanning: false,
                    panStartX: 0,
                    panStartY: 0,
                    showScrollHint: false,
                    isMobile: false,
                    _hintTimer: null,
                    _wheelHandler: null,
                    _mouseMoveHandler: null,
                    _mouseUpHandler: null,
                    // touch swipe tracking
                    _touchStartX: 0,
                    _touchStartY: 0,
                    photoUrls: {!! $lightboxUrls ?? '[]' !!},

                    init() {
                        // Detect mobile/touch device
                        this.isMobile = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

                        if (!this.isMobile) {
                            // Scroll-to-zoom: must use passive:false to call preventDefault()
                            this._wheelHandler = (e) => {
                                if (!this.lightboxOpen) return;
                                e.preventDefault();
                                e.stopPropagation();
                                this.onWheel(e);
                            };
                            document.addEventListener('wheel', this._wheelHandler, { passive: false });

                            // Document-level pan handlers — arrow functions preserve 'this'
                            this._mouseMoveHandler = (e) => {
                                if (!this.lightboxOpen || !this.isPanning) return;
                                e.preventDefault();
                                this.panX = e.clientX - this.panStartX;
                                this.panY = e.clientY - this.panStartY;
                            };
                            this._mouseUpHandler = (e) => {
                                if (!this.isPanning) return;
                                e.preventDefault();
                                this.isPanning = false;
                            };
                            this._mouseDownHandler = (e) => {
                                if (!this.lightboxOpen || this.zoomLevel <= 1) return;
                                if (e.button !== 0) return;
                                // Only start pan if click is inside the image container
                                const container = this.$refs.imgContainer;
                                if (!container || !container.contains(e.target)) return;
                                e.preventDefault();
                                e.stopPropagation();
                                this.isPanning = true;
                                this.panStartX = e.clientX - this.panX;
                                this.panStartY = e.clientY - this.panY;
                            };
                            document.addEventListener('mousedown', this._mouseDownHandler);
                            document.addEventListener('mousemove', this._mouseMoveHandler);
                            document.addEventListener('mouseup', this._mouseUpHandler);
                        }
                    },

                    nextSlide() {
                        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                        this.resetZoom();
                    },

                    prevSlide() {
                        this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                        this.resetZoom();
                    },

                    goToSlide(index) {
                        this.currentSlide = index;
                        this.resetZoom();
                    },

                    openLightbox(index) {
                        this.currentSlide = index;
                        this.resetZoom();
                        this.lightboxOpen = true;
                        if (!this.isMobile) {
                            this.showScrollHint = true;
                            clearTimeout(this._hintTimer);
                            this._hintTimer = setTimeout(() => { this.showScrollHint = false; }, 2500);
                        }
                    },

                    closeLightbox() {
                        this.lightboxOpen = false;
                        this.resetZoom();
                    },

                    resetZoom() {
                        this.zoomLevel = 1;
                        this.panX = 0;
                        this.panY = 0;
                        this.isPanning = false;
                    },

                    onWheel(e) {
                        const delta = e.deltaY < 0 ? 1 : -1;
                        const step = 0.15;
                        const newZoom = Math.min(5, Math.max(1, this.zoomLevel + delta * step));

                        if (newZoom === this.zoomLevel) return;

                        const container = this.$refs.imgContainer;
                        const rect = container.getBoundingClientRect();
                        const mouseX = e.clientX - rect.left - rect.width / 2;
                        const mouseY = e.clientY - rect.top - rect.height / 2;

                        const scale = newZoom / this.zoomLevel;
                        this.panX = mouseX - scale * (mouseX - this.panX);
                        this.panY = mouseY - scale * (mouseY - this.panY);
                        this.zoomLevel = parseFloat(newZoom.toFixed(2));

                        if (this.zoomLevel <= 1) this.resetZoom();
                    },


                    // Touch swipe handlers (mobile — no zoom, just slide navigation)
                    onTouchStart(e) {
                        this._touchStartX = e.touches[0].clientX;
                        this._touchStartY = e.touches[0].clientY;
                    },

                    onTouchEnd(e) {
                        const dx = e.changedTouches[0].clientX - this._touchStartX;
                        const dy = e.changedTouches[0].clientY - this._touchStartY;
                        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
                            dx < 0 ? this.nextSlide() : this.prevSlide();
                        }
                    }
                }
            }

            // Photo Gallery with Multi-select
            function photoGallery() {
                return {
                    activeTab: 'auction',
                    selectedPhotos: [],
                    photosByTab: {
                        auction: {!! json_encode($auctionPhotos->pluck('id')->toArray()) !!},
                        pickup: {!! json_encode($pickupPhotos->pluck('id')->toArray()) !!},
                        warehouse: {!! json_encode($warehousePhotos->pluck('id')->toArray()) !!},
                        poti: {!! json_encode($potiPhotos->pluck('id')->toArray()) !!}
                    },

                    switchTab(tab) {
                        this.activeTab = tab;
                        this.selectedPhotos = [];
                    },

                    togglePhoto(photoId) {
                        const index = this.selectedPhotos.indexOf(photoId);
                        if (index > -1) {
                            this.selectedPhotos.splice(index, 1);
                        } else {
                            this.selectedPhotos.push(photoId);
                        }
                    },

                    isAllSelected() {
                        const currentTabPhotos = this.photosByTab[this.activeTab];
                        return currentTabPhotos.length > 0 &&
                            currentTabPhotos.every(id => this.selectedPhotos.includes(id));
                    },

                    toggleSelectAll() {
                        const currentTabPhotos = this.photosByTab[this.activeTab];
                        if (this.isAllSelected()) {
                            // Deselect all from current tab
                            this.selectedPhotos = this.selectedPhotos.filter(id => !currentTabPhotos.includes(id));
                        } else {
                            // Select all from current tab
                            currentTabPhotos.forEach(id => {
                                if (!this.selectedPhotos.includes(id)) {
                                    this.selectedPhotos.push(id);
                                }
                            });
                        }
                    },

                    setSliderPhoto(index) {
                        const sliderComponent = document.querySelector('[x-data*="photoSlider"]');
                        if (sliderComponent && sliderComponent._x_dataStack) {
                            sliderComponent._x_dataStack[0].currentSlide = index;
                            sliderComponent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    },

                    deleteSinglePhoto(fileId) {
                        if (!confirm('ნამდვილად გსურთ ფოტოს წაშლა?')) return;

                        fetch('/cars/{{ $car->id }}/files/' + fileId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }).then(r => r.json()).then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.message || 'შეცდომა წაშლისას');
                            }
                        }).catch(() => {
                            alert('შეცდომა წაშლისას');
                        });
                    },

                    deleteSelected() {
                        if (this.selectedPhotos.length === 0) return;

                        const count = this.selectedPhotos.length;
                        if (!confirm(`ნამდვილად გსურთ ${count} ფოტოს წაშლა?`)) return;

                        fetch('/cars/{{ $car->id }}/files/bulk-delete', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ file_ids: this.selectedPhotos })
                        }).then(r => r.json()).then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.message || 'შეცდომა წაშლისას');
                            }
                        }).catch(() => {
                            alert('შეცდომა წაშლისას');
                        });
                    },

                    setMainPhoto(fileId) {
                        fetch('/cars/{{ $car->id }}/files/' + fileId + '/main', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }).then(r => r.json()).then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.message || 'შეცდომა');
                            }
                        }).catch(() => {
                            alert('შეცდომა');
                        });
                    }
                }
            }

            // Global function to set slider from gallery (fallback)
            function setSliderPhoto(index) {
                const sliderComponent = document.querySelector('[x-data*="photoSlider"]');
                if (sliderComponent && sliderComponent._x_dataStack) {
                    sliderComponent._x_dataStack[0].currentSlide = index;
                    sliderComponent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        </script>
    @endpush
@endsection