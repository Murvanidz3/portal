<!DOCTYPE html>
<html lang="ka">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'God Mode') - Super Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --god-bg: #0a0a0a;
            --god-surface: #141414;
            --god-surface-hover: #1f1f1f;
            --god-border: #2a2a2a;
            --god-text: #f5f5f5;
            --god-text-muted: #888;
            --god-primary: #dc2626;
            --god-primary-hover: #ef4444;
            --god-success: #22c55e;
            --god-warning: #f59e0b;
            --god-danger: #ef4444;
            --god-info: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--god-bg);
            color: var(--god-text);
            min-height: 100vh;
        }

        /* Layout */
        .god-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .god-sidebar {
            width: 260px;
            background: var(--god-surface);
            border-right: 1px solid var(--god-border);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .god-logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--god-border);
            margin-bottom: 20px;
        }

        .god-logo h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--god-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .god-logo h1 i {
            font-size: 24px;
        }

        .god-logo span {
            font-size: 11px;
            color: var(--god-text-muted);
            display: block;
            margin-top: 5px;
        }

        .god-nav {
            padding: 0 10px;
        }

        .god-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: var(--god-text-muted);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        .god-nav-item:hover {
            background: var(--god-surface-hover);
            color: var(--god-text);
        }

        .god-nav-item.active {
            background: var(--god-primary);
            color: white;
        }

        .god-nav-item i {
            width: 20px;
            text-align: center;
        }

        .god-nav-section {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--god-text-muted);
            padding: 15px 15px 8px;
            letter-spacing: 1px;
        }

        /* Main Content */
        .god-main {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        /* Header */
        .god-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .god-page-title {
            font-size: 24px;
            font-weight: 600;
        }

        .god-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .god-user-name {
            font-size: 14px;
            color: var(--god-text-muted);
        }

        /* Cards */
        .god-card {
            background: var(--god-surface);
            border: 1px solid var(--god-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .god-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--god-border);
        }

        .god-card-title {
            font-size: 16px;
            font-weight: 600;
        }

        /* Grid */
        .god-grid {
            display: grid;
            gap: 20px;
        }

        .god-grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .god-grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .god-grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        /* Stats */
        .god-stat {
            background: var(--god-surface);
            border: 1px solid var(--god-border);
            border-radius: 12px;
            padding: 20px;
        }

        .god-stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--god-primary);
        }

        .god-stat-label {
            font-size: 13px;
            color: var(--god-text-muted);
            margin-top: 5px;
        }

        /* Buttons */
        .god-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .god-btn-primary {
            background: var(--god-primary);
            color: white;
        }

        .god-btn-primary:hover {
            background: var(--god-primary-hover);
        }

        .god-btn-secondary {
            background: var(--god-surface-hover);
            color: var(--god-text);
            border: 1px solid var(--god-border);
        }

        .god-btn-secondary:hover {
            background: var(--god-border);
        }

        .god-btn-danger {
            background: var(--god-danger);
            color: white;
        }

        .god-btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Toggle Switch */
        .god-toggle {
            position: relative;
            width: 48px;
            height: 26px;
        }

        .god-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .god-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--god-border);
            transition: 0.3s;
            border-radius: 26px;
        }

        .god-toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        .god-toggle input:checked+.god-toggle-slider {
            background: var(--god-success);
        }

        .god-toggle input:checked+.god-toggle-slider:before {
            transform: translateX(22px);
        }

        /* Tables */
        .god-table {
            width: 100%;
            border-collapse: collapse;
        }

        .god-table th,
        .god-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--god-border);
        }

        .god-table th {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--god-text-muted);
            font-weight: 600;
        }

        .god-table tr:hover td {
            background: var(--god-surface-hover);
        }

        /* Form Inputs */
        .god-input {
            background: var(--god-bg);
            border: 1px solid var(--god-border);
            color: var(--god-text);
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
        }

        .god-input:focus {
            outline: none;
            border-color: var(--god-primary);
        }

        /* Color Picker */
        .god-color-picker {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .god-color-preview {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 2px solid var(--god-border);
            cursor: pointer;
        }

        .god-color-input {
            width: 100px;
        }

        /* Alerts */
        .god-alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .god-alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid var(--god-success);
            color: var(--god-success);
        }

        .god-alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--god-danger);
            color: var(--god-danger);
        }

        /* Badge */
        .god-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .god-badge-success {
            background: rgba(34, 197, 94, 0.2);
            color: var(--god-success);
        }

        .god-badge-danger {
            background: rgba(239, 68, 68, 0.2);
            color: var(--god-danger);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .god-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .god-grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .god-sidebar {
                display: none;
            }

            .god-main {
                margin-left: 0;
            }

            .god-grid-2,
            .god-grid-3,
            .god-grid-4 {
                grid-template-columns: 1fr;
            }
        }

        /* Toast Notifications */
        .god-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            background: var(--god-surface);
            border: 1px solid var(--god-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }

        .god-toast.show {
            transform: translateX(0);
        }

        .god-toast-success {
            border-color: var(--god-success);
        }

        .god-toast-error {
            border-color: var(--god-danger);
        }

        /* Loading */
        .god-loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--god-border);
            border-top-color: var(--god-primary);
            border-radius: 50%;
            animation: god-spin 0.8s linear infinite;
        }

        @keyframes god-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Confirmation Modal */
        .god-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .god-modal-overlay.show {
            display: flex;
        }

        .god-modal {
            background: var(--god-surface);
            border: 1px solid var(--god-border);
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
        }

        .god-modal-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .god-modal-body {
            color: var(--god-text-muted);
            margin-bottom: 25px;
        }

        .god-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="god-layout">
        @auth('god')
            <aside class="god-sidebar">
                <div class="god-logo">
                    <h1><i class="fas fa-shield-halved"></i> GOD MODE</h1>
                    <span>Super Admin Control Panel</span>
                </div>

                <nav class="god-nav">
                    <div class="god-nav-section">მთავარი</div>
                    <a href="{{ route('god.dashboard') }}"
                        class="god-nav-item {{ request()->routeIs('god.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-gauge-high"></i> Dashboard
                    </a>

                    <div class="god-nav-section">კონტროლი</div>
                    <a href="{{ route('god.permissions') }}"
                        class="god-nav-item {{ request()->routeIs('god.permissions') ? 'active' : '' }}">
                        <i class="fas fa-key"></i> უფლებები
                    </a>
                    <a href="{{ route('god.styles') }}"
                        class="god-nav-item {{ request()->routeIs('god.styles') ? 'active' : '' }}">
                        <i class="fas fa-palette"></i> სტილები
                    </a>
                    <a href="{{ route('god.shipping-rates.index') }}"
                        class="god-nav-item {{ request()->routeIs('god.shipping-rates.*') ? 'active' : '' }}">
                        <i class="fas fa-shipping-fast"></i> ტრანსპორტირება
                    </a>

                    <div class="god-nav-section">ლოგები</div>
                    <a href="{{ route('god.audit-logs') }}"
                        class="god-nav-item {{ request()->routeIs('god.audit-logs') ? 'active' : '' }}">
                        <i class="fas fa-scroll"></i> Audit Logs
                    </a>
                </nav>
            </aside>
        @endauth

        <main class="god-main">
            @yield('content')
        </main>
    </div>

    <!-- Toast Container -->
    <div id="god-toast" class="god-toast"></div>

    <!-- Confirmation Modal -->
    <div id="god-confirm-modal" class="god-modal-overlay">
        <div class="god-modal">
            <h3 class="god-modal-title" id="god-confirm-title">დადასტურება</h3>
            <p class="god-modal-body" id="god-confirm-message">დარწმუნებული ხართ?</p>
            <div class="god-modal-actions">
                <button class="god-btn god-btn-secondary" onclick="godCloseConfirm()">გაუქმება</button>
                <button class="god-btn god-btn-danger" id="god-confirm-btn">დადასტურება</button>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Toast function
        function godToast(message, type = 'success') {
            const toast = document.getElementById('god-toast');
            toast.textContent = message;
            toast.className = 'god-toast god-toast-' + type + ' show';
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Confirm dialog
        let confirmCallback = null;
        function godConfirm(title, message, callback) {
            document.getElementById('god-confirm-title').textContent = title;
            document.getElementById('god-confirm-message').textContent = message;
            document.getElementById('god-confirm-modal').classList.add('show');
            confirmCallback = callback;
        }

        function godCloseConfirm() {
            document.getElementById('god-confirm-modal').classList.remove('show');
            confirmCallback = null;
        }

        document.getElementById('god-confirm-btn').addEventListener('click', function () {
            if (confirmCallback) {
                confirmCallback();
            }
            godCloseConfirm();
        });

        // AJAX helper
        async function godFetch(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            };

            const response = await fetch(url, { ...defaultOptions, ...options });
            return response.json();
        }
    </script>
    @stack('scripts')
</body>

</html>