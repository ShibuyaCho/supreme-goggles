<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Cannabis POS System')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Styles -->
    @stack('styles')

    <style>
        /* Custom POS Styles */
        .transition-all { transition: all 0.2s ease-in-out; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
        .shadow-md { box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
        .shadow-xl { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); }

        /* Loading states */
        .loading { opacity: 0.6; pointer-events: none; }

        /* Custom scrollbar */
        .scrollbar-thin { scrollbar-width: thin; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: #f1f5f9; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Cannabis-specific colors */
        .text-cannabis-green { color: #16a34a; }
        .bg-cannabis-green { background-color: #16a34a; }
        .border-cannabis-green { border-color: #16a34a; }

        /* POS specific utilities */
        .product-card:hover { transform: translateY(-1px); }
        .cart-item { border-left: 3px solid transparent; }
        .cart-item.selected { border-left-color: #16a34a; background-color: #f0fdf4; }

        /* Modal animations */
        .modal-enter { opacity: 0; transform: scale(0.95); }
        .modal-enter-active { opacity: 1; transform: scale(1); transition: opacity 150ms ease-out, transform 150ms ease-out; }

        /* Toast notifications */
        .toast { position: fixed; top: 20px; right: 20px; z-index: 1000; opacity: 0; transform: translateX(100%); transition: all 0.3s ease-in-out; }
        .toast.show { opacity: 1; transform: translateX(0); }

        /* Print styles */
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; }
        }

        /* Responsive grid adjustments */
        @media (max-width: 768px) {
            .pos-grid { grid-template-columns: 1fr; }
            .cart-panel { position: fixed; right: -100%; top: 0; height: 100vh; z-index: 40; transition: right 0.3s ease-in-out; }
            .cart-panel.open { right: 0; }
        }
    </style>

    {{-- ===== AUTH GUARD & apiFetch ===== --}}
    <script>
    (function () {
      // Allow-list of public pages that should NOT force a token
      var path = location.pathname.replace(/\/+$/, '');
      var isPublic =
        path === '/login' ||
        path === '/register' ||
        path === '/forgot-password' ||
        path.startsWith('/password/') ||
        path === '/privacy' ||
        path === '/terms';

      if (!isPublic) {
        var token = localStorage.getItem('api_token');
        if (!token) {
          location.replace('/login');
          return;
        }
      }

      // apiFetch helper: adds Bearer and handles 401 -> logout + redirect
      window.apiFetch = async function (url, opts) {
        opts = opts || {};
        var headers = Object.assign({
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + (localStorage.getItem('api_token') || '')
        }, opts.headers || {});
        var res = await fetch(url, Object.assign({}, opts, { headers }));

        if (res.status === 401) {
          try {
            await fetch('/api/auth/logout', {
              method: 'POST',
              headers: { 'Accept': 'application/json', 'Authorization': headers['Authorization'] }
            });
          } catch (_) {}
          localStorage.removeItem('api_token');
          location.replace('/login');
          throw new Error('Unauthenticated');
        }

        return res;
      };

      // Logout helper
      window.appLogout = async function () {
        var t = localStorage.getItem('api_token');
        if (t) {
          try {
            await fetch('/api/auth/logout', { method: 'POST', headers: { 'Accept':'application/json', 'Authorization':'Bearer ' + t } });
          } catch (_) {}
        }
        localStorage.removeItem('api_token');
        location.replace('/login');
      };
    })();
    </script>
</head>

<body class="font-sans antialiased bg-gray-50">
    <!-- Global Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Navigation -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-cannabis-green">Cannabis POS</h1>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="{{ route('pos.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pos.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                                Point of Sale
                            </a>
                            <a href="{{ route('customers.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('customers.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                                Customers
                            </a>
                            <a href="{{ route('products.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('products.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                                Inventory
                            </a>
                            <a href="{{ route('analytics.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('analytics.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                                Analytics
                            </a>
                            <a href="{{ route('sales.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('sales.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                                Sales
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Current Employee -->
                    <div class="hidden md:flex items-center text-sm text-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ auth()->user()?->name ?? 'Employee' }}
                    </div>

                    <!-- Tax Display and Settings -->
                    <button onclick="openSettingsModal()" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span id="tax-display">Tax: 20.0%</span>
                    </button>

                    <!-- Logout -->
                    <button
                        class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                        onclick="window.appLogout()"
                        title="Sign out"
                    >
                        Logout
                    </button>

                    <!-- Mobile menu button -->
                    <button id="mobile-menu-button" class="md:hidden text-gray-700 hover:text-cannabis-green p-2 rounded-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="md:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t">
                <a href="{{ route('pos.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('pos.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                    Point of Sale
                </a>
                <a href="{{ route('customers.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('customers.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                    Customers
                </a>
                <a href="{{ route('products.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('products.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                    Inventory
                </a>
                <a href="{{ route('analytics.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('analytics.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                    Analytics
                </a>
                <a href="{{ route('sales.index') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('sales.*') ? 'bg-cannabis-green text-white' : 'text-gray-700 hover:text-cannabis-green hover:bg-gray-50' }}">
                    Sales
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Global Modals -->
    @include('pos.modals.product-actions')
    @include('pos.modals.transfer-room')
    @include('pos.modals.settings')
    @include('pos.modals.customer-select')
    @include('pos.modals.age-verification')
    @include('pos.modals.payment')
    @include('pos.modals.new-customer')
    @include('pos.modals.new-sale')

    <!-- Toast Notifications -->
    @include('components.toast')

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <svg class="animate-spin h-5 w-5 text-cannabis-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700">Processing...</span>
        </div>
    </div>

    <!-- Base JavaScript -->
    <script>
        // Global POS utilities
        window.POS = {
            showLoading: function() {
                document.getElementById('loading-overlay').classList.remove('hidden');
                document.getElementById('loading-overlay').classList.add('flex');
            },
            hideLoading: function() {
                document.getElementById('loading-overlay').classList.add('hidden');
                document.getElementById('loading-overlay').classList.remove('flex');
            },
            showToast: function(message, type = 'info', duration = 3000) {
                const toast = document.createElement('div');
                toast.className = `toast px-6 py-4 rounded-lg shadow-lg text-white ${
                    type === 'success' ? 'bg-green-600' :
                    type === 'error' ? 'bg-red-600' :
                    type === 'warning' ? 'bg-yellow-600' : 'bg-blue-600'
                }`;
                toast.textContent = message;
                document.getElementById('toast-container').appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 100);
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            },
            formatCurrency: function(amount) {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
            },
            formatDate: function(date) {
                return new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(date));
            }
        };

        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });

        // CSRF token setup for AJAX requests (for any session-based endpoints)
        window.axios = window.axios || {};
        window.axios.defaults = {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
    </script>

    @stack('scripts')
</body>
</html>
