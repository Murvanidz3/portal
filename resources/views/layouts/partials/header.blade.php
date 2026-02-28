{{-- Header Component --}}
<header class="sticky top-0 z-30 bg-dark-900/80 backdrop-blur-xl border-b border-white/5">
    <div class="flex items-center justify-between h-16 px-4 lg:px-6">
        
        {{-- Left Side --}}
        <div class="flex items-center">
            {{-- Mobile Menu Button --}}
            <button 
                @click="sidebarOpen = !sidebarOpen" 
                class="lg:hidden p-2 rounded-lg text-dark-400 hover:text-white hover:bg-dark-800 transition-colors mr-3"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            
            {{-- Page Title --}}
            <div>
                <h1 class="text-lg font-semibold text-white">@yield('title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                    <nav class="text-sm text-dark-400">
                        @yield('breadcrumb')
                    </nav>
                @endif
            </div>
        </div>
        
        {{-- Right Side --}}
        <div class="flex items-center space-x-3">
            
            {{-- Balance (for dealers) --}}
            @if(auth()->user()->isDealer() || auth()->user()->isAdmin())
            <a href="{{ route('wallet.index') }}" class="hidden md:flex items-center px-4 py-2 rounded-lg bg-green-500/10 border border-green-500/20 hover:bg-green-500/20 transition-colors">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span class="text-green-400 font-semibold">{{ auth()->user()->getFormattedBalance() }}</span>
            </a>
            @endif
            
            {{-- Notifications --}}
            <div x-data="{ open: false }" class="relative">
                <button 
                    @click="open = !open" 
                    class="relative p-2 rounded-lg text-dark-400 hover:text-white hover:bg-dark-800 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(auth()->user()->getUnreadNotificationsCount() > 0)
                    <span class="absolute top-0 right-0 w-5 h-5 flex items-center justify-center text-xs font-bold text-white bg-red-500 rounded-full">
                        {{ auth()->user()->getUnreadNotificationsCount() }}
                    </span>
                    @endif
                </button>
                
                {{-- Notifications Dropdown --}}
                <div 
                    x-show="open" 
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-80 rounded-xl bg-dark-800 border border-white/10 shadow-xl shadow-black/20 overflow-hidden"
                    x-cloak
                >
                    <div class="p-4 border-b border-white/5">
                        <h3 class="font-semibold text-white">შეტყობინებები</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                        <a href="{{ route('notifications.index') }}" 
                           class="block px-4 py-3 hover:bg-dark-700/50 transition-colors {{ !$notification->is_read ? 'bg-primary-500/5' : '' }}">
                            <div class="flex items-start">
                                <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center flex-shrink-0 mr-3">
                                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white {{ !$notification->is_read ? 'font-medium' : '' }} line-clamp-2">
                                        {{ Str::limit($notification->message, 80) }}
                                    </p>
                                    <p class="text-xs text-dark-400 mt-1">{{ $notification->time_ago }}</p>
                                </div>
                            </div>
                        </a>
                        @empty
                        <div class="px-4 py-8 text-center text-dark-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-sm">შეტყობინებები არ არის</p>
                        </div>
                        @endforelse
                    </div>
                    <a href="{{ route('notifications.index') }}" class="block px-4 py-3 text-center text-sm font-medium text-primary-400 hover:text-primary-300 border-t border-white/5">
                        ყველას ნახვა
                    </a>
                </div>
            </div>
            
            {{-- Profile Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button 
                    @click="open = !open" 
                    class="flex items-center space-x-3 p-2 rounded-lg hover:bg-dark-800 transition-colors"
                >
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-semibold text-sm">
                        {{ strtoupper(substr(auth()->user()->full_name ?? auth()->user()->username, 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-sm font-medium text-white">{{ auth()->user()->full_name ?? auth()->user()->username }}</p>
                        <p class="text-xs text-dark-400 capitalize">{{ auth()->user()->role_label }}</p>
                    </div>
                    <svg class="w-4 h-4 text-dark-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                {{-- Profile Menu --}}
                <div 
                    x-show="open" 
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 rounded-xl bg-dark-800 border border-white/10 shadow-xl shadow-black/20 overflow-hidden"
                    x-cloak
                >
                    <div class="p-4 border-b border-white/5">
                        <p class="text-sm font-medium text-white">{{ auth()->user()->full_name ?? auth()->user()->username }}</p>
                        <p class="text-xs text-dark-400">{{ auth()->user()->phone }}</p>
                    </div>
                    <div class="py-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-700/50 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            პროფილი
                        </a>
                        <a href="{{ route('profile.change-password') }}" class="flex items-center px-4 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-700/50 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            პაროლის შეცვლა
                        </a>
                    </div>
                    <div class="py-2 border-t border-white/5">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                გასვლა
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</header>
