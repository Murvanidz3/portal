<!DOCTYPE html>
<html lang="ka" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') -
        {{ $godModeBranding['brand_company_name'] ?? config('app.name', 'OneCar CRM') }}
    </title>

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

    <!-- Notification Popup Container -->
    <div id="notification-popup-container" class="fixed top-4 right-4 z-[60] space-y-3 max-w-sm w-full"
        style="pointer-events: none;"></div>

    <style>
        .notification-popup {
            pointer-events: auto;
            animation: slideInRight 0.4s ease-out;
        }

        .notification-popup.hiding {
            animation: slideOutRight 0.3s ease-in forwards;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Check for unread notifications on page load
            fetch('{{ route("notifications.recent") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.notifications && data.notifications.length > 0) {
                        // Get dismissed notification IDs from sessionStorage
                        let dismissed = JSON.parse(sessionStorage.getItem('dismissed_notifications') || '[]');

                        let unread = data.notifications.filter(n => !n.is_read && !dismissed.includes(n.id));

                        // Show max 3 popups
                        unread.slice(0, 3).forEach((notification, index) => {
                            setTimeout(() => showNotificationPopup(notification), index * 300);
                        });

                        // If more than 3 unread
                        if (unread.length > 3) {
                            setTimeout(() => {
                                showNotificationPopup({
                                    id: 'more',
                                    message: `+${unread.length - 3} სხვა შეტყობინება`,
                                    url: '{{ route("notifications.index") }}'
                                });
                            }, 3 * 300 + 200);
                        }
                    }
                })
                .catch(() => { });
        });

        function showNotificationPopup(notification) {
            const container = document.getElementById('notification-popup-container');
            const popup = document.createElement('div');
            popup.className = 'notification-popup bg-dark-800 border border-primary-500/30 rounded-xl p-4 shadow-2xl shadow-primary-500/10 cursor-pointer';
            popup.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary-600/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white font-medium">${notification.message}</p>
                    </div>
                    <button class="text-dark-400 hover:text-white flex-shrink-0" onclick="event.stopPropagation(); dismissPopup(this.closest('.notification-popup'), ${notification.id})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `;

            popup.addEventListener('click', function () {
                window.location.href = notification.url || '{{ route("notifications.index") }}';
            });

            container.appendChild(popup);

            // Auto dismiss after 8 seconds
            setTimeout(() => dismissPopup(popup, notification.id), 8000);
        }

        function dismissPopup(popup, notificationId) {
            if (!popup || popup.classList.contains('hiding')) return;
            popup.classList.add('hiding');

            // Save dismissed ID to session
            if (notificationId && notificationId !== 'more') {
                let dismissed = JSON.parse(sessionStorage.getItem('dismissed_notifications') || '[]');
                dismissed.push(notificationId);
                sessionStorage.setItem('dismissed_notifications', JSON.stringify(dismissed));
            }

            setTimeout(() => popup.remove(), 300);
        }
    </script>

    @stack('scripts')

</body>

</html>