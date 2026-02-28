<!DOCTYPE html>
<html lang="ka" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') -
        {{ $godModeBranding['brand_company_name'] ?? config('app.name', 'OneCar CRM') }}</title>

    <!-- Favicon (Dynamic from God Mode) -->
    <link rel="icon" href="{{ $godModeBranding['brand_favicon'] ?? asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <!-- God Mode Dynamic Styles -->
    @if(!empty($godModeStyles))
        <style id="god-mode-dynamic-styles">
            {!! $godModeStyles !!}
        </style>
    @endif
</head>

<body class="bg-dark-950 text-white font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex min-h-screen">

        <!-- Sidebar Overlay (Mobile) -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden" id="sidebar-overlay"></div>

        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64">

            <!-- Top Header -->
            @include('layouts.partials.header')

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6 overflow-x-hidden">

                <!-- Flash Messages -->
                @if(session('success'))
                    <div
                        class="mb-4 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 animate-fade-in">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 animate-fade-in">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <!-- Page Header -->
                @hasSection('page-header')
                    <div class="mb-6">
                        @yield('page-header')
                    </div>
                @endif

                <!-- Main Content -->
                @yield('content')

            </main>

            <!-- Footer -->
            <footer class="px-6 py-4 border-t border-white/5 text-center text-sm text-dark-400">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </footer>

        </div>

    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay"
        class="fixed inset-0 z-50 bg-dark-950/80 backdrop-blur-sm items-center justify-center hidden">
        <div class="spinner"></div>
    </div>

    @stack('scripts')

</body>

</html>