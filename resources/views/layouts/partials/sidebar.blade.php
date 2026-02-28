{{-- Sidebar Component --}}
<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 sidebar transform transition-transform duration-300 ease-in-out"
    id="sidebar"
>
    <div class="flex flex-col h-full">
        
        {{-- Logo --}}
        <div class="flex items-center justify-center h-16 px-4 border-b border-white/5">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto" onerror="this.style.display='none'">
                <span class="text-xl font-bold text-white">{{ \App\Models\Setting::getSiteName() }}</span>
            </a>
        </div>
        
        {{-- Navigation --}}
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('dashboard') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>მთავარი</span>
            </a>
            
            {{-- Cars --}}
            <a href="{{ route('cars.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('cars.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                </svg>
                <span>მანქანები</span>
            </a>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isDealer())
            
            {{-- Section Divider --}}
            <div class="pt-4 pb-2 px-4">
                <p class="text-xs font-semibold text-dark-500 uppercase tracking-wider">ფინანსები</p>
            </div>
            
            {{-- Finance --}}
            <a href="{{ route('finance.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('finance.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>ფინანსები</span>
            </a>
            
            {{-- Wallet --}}
            <a href="{{ route('wallet.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('wallet.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>საფულე</span>
            </a>
            
            {{-- Transactions --}}
            <a href="{{ route('transactions.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('transactions.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>ტრანზაქციები</span>
            </a>
            
            {{-- Invoices --}}
            <a href="{{ route('invoices.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('invoices.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>ინვოისები</span>
            </a>
            
            @endif
            
            @if(auth()->user()->isAdmin())
            
            {{-- Section Divider --}}
            <div class="pt-4 pb-2 px-4">
                <p class="text-xs font-semibold text-dark-500 uppercase tracking-wider">ადმინისტრირება</p>
            </div>
            
            {{-- Users --}}
            <a href="{{ route('users.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('users.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>მომხმარებლები</span>
            </a>
            
            {{-- SMS Manager --}}
            <a href="{{ route('sms.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('sms.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <span>SMS მენეჯერი</span>
            </a>
            
            {{-- Calculator --}}
            <a href="{{ route('calculator.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('calculator.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>კალკულატორი</span>
            </a>
            
            {{-- Settings --}}
            <a href="{{ route('settings.index') }}" 
               class="sidebar-item flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('settings.*') ? 'active' : 'text-dark-300 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>პარამეტრები</span>
            </a>
            
            @endif
            
        </nav>
        
        {{-- Sidebar Footer --}}
        <div class="p-4 border-t border-white/5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-item w-full flex items-center px-4 py-3 text-sm font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>გასვლა</span>
                </button>
            </form>
        </div>
        
    </div>
</aside>
