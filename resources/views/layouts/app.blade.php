<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Cannabis POS System')</title>

    <script>window["_fs_namespace"] = window["_fs_namespace"] || "FS_cpos";</script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    @stack('styles')
    
    <style>
        /* Custom POS Styles */
        .transition-all { transition: all 0.2s ease-in-out; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
        .shadow-md { box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
        .shadow-xl { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); }
        
        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Custom scrollbar */
        .scrollbar-thin {
            scrollbar-width: thin;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Cannabis-specific colors */
        .text-cannabis-green { color: #16a34a; }
        .bg-cannabis-green { background-color: #16a34a; }
        .border-cannabis-green { border-color: #16a34a; }
        
        /* POS specific utilities */
        .product-card:hover {
            transform: translateY(-1px);
        }
        
        .cart-item {
            border-left: 3px solid transparent;
        }
        
        .cart-item.selected {
            border-left-color: #16a34a;
            background-color: #f0fdf4;
        }
        
        /* Modal animations */
        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }
        
        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 150ms ease-out, transform 150ms ease-out;
        }
        
        /* Toast notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease-in-out;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        /* Print styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
        
        /* Responsive grid adjustments */
        @media (max-width: 768px) {
            .pos-grid {
                grid-template-columns: 1fr;
            }
            
            .cart-panel {
                position: fixed;
                right: -100%;
                top: 0;
                height: 100vh;
                z-index: 40;
                transition: right 0.3s ease-in-out;
            }
            
            .cart-panel.open {
                right: 0;
            }
        }
    </style>
    <!-- Core libraries -->
    <script src="{{ asset('lib/axios/axios.min.js') }}" defer></script>
    <script src="{{ asset('js/auth.js') }}" defer></script>
    <script src="{{ asset('js/pos.js') }}" defer></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
                    <!-- Quick Actions -->
                    <button id="global-refresh-metrc" class="hidden md:inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-cannabis-green hover:bg-green-700 rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 19A9 9 0 0019 5"/></svg>
                        Refresh METRC
                    </button>
                    <a href="{{ route('rooms-drawers.index') }}" class="hidden md:inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Create Room
                    </a>
                    <a href="{{ route('rooms-drawers.index') }}" class="hidden md:inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3M7 4H5a1 1 0 00-1 1v16a1 1 0 001 1h14a1 1 0 001-1V5a1 1 0 00-1-1h-2M9 9h6m-6 4h6m-3 4h3"/></svg>
                        Create Drawer
                    </a>
                    <!-- Current Employee -->
                    <div class="hidden md:flex items-center text-sm text-gray-700 relative" id="user-menu-container">
                        <button id="user-menu-button" class="flex items-center hover:text-cannabis-green">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>{{ auth()->user()->name ?? 'Employee' }}</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="user-menu-dropdown" class="hidden absolute right-0 top-full mt-2 w-40 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                            <button id="logout-button" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Log Out</button>
                        </div>
                    </div>

                    <!-- Tax Display and Settings -->
                    <button onclick="openSettingsModal()" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span id="tax-display">Tax: 20.0%</span>
                    </button>

                    <!-- Customer Info -->
                    <button onclick="CannabisPOS.openModal('customer-select-modal')" class="hidden md:flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Customer Info
                    </button>

                    <!-- Order Queue -->
                    <a href="{{ route('order-queue.index') }}" class="hidden md:flex items-center px-3 py-2 text-sm font-medium text-purple-700 bg-purple-100 hover:bg-purple-200 rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Order Queue
                        <span class="ml-1 bg-purple-600 text-white text-xs rounded-full px-1.5 py-0.5">3</span>
                    </a>

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
                
                // Show toast
                setTimeout(() => toast.classList.add('show'), 100);
                
                // Hide and remove toast
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            },
            
            formatCurrency: function(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(amount);
            },
            
            formatDate: function(date) {
                return new Intl.DateTimeFormat('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                }).format(new Date(date));
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

            // User menu dropdown + logout
            const userBtn = document.getElementById('user-menu-button');
            const userDropdown = document.getElementById('user-menu-dropdown');
            const logoutBtn = document.getElementById('logout-button');
            if (userBtn && userDropdown) {
                userBtn.addEventListener('click', function(e){
                    e.stopPropagation();
                    userDropdown.classList.toggle('hidden');
                });
                document.addEventListener('click', function(){
                    userDropdown.classList.add('hidden');
                });
            }
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async function(){
                    try {
                        if (window.posAuth && typeof window.posAuth.logout === 'function') {
                            await window.posAuth.logout();
                        }
                    } catch (e) {}
                    // Force re-authentication
                    window.location.reload();
                });
            }
        });

        // CSRF token setup for AJAX requests
        window.axios = window.axios || {};
        window.axios.defaults = {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
    </script>

    <script>
    // POS product-card tweaks applied globally
    (function(){
      // hide category / sold by line directly under product name
      const style = document.createElement('style');
      style.textContent = `.product-card h3 + p{display:none!important}`;
      document.head.appendChild(style);

      function injectDeleteButtons(){
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
          if (card.querySelector('.delete-product')) return;
          const id = card.getAttribute('data-product-id') || card.dataset.id;
          const nameEl = card.querySelector('h3, h4');
          const name = nameEl ? nameEl.textContent.trim() : 'this product';
          if (!id) return; // skip if no id on card
          const btn = document.createElement('button');
          btn.className = 'delete-product absolute top-2 right-2 p-1 rounded bg-white/80 hover:bg-white text-red-600 shadow border border-red-200';
          btn.setAttribute('data-product-id', id);
          btn.setAttribute('data-product-name', name);
          btn.title = 'Delete Product';
          btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
          card.style.position = 'relative';
          card.appendChild(btn);
        });
      }

      document.addEventListener('DOMContentLoaded', function(){
        injectDeleteButtons();
        setTimeout(injectDeleteButtons, 500);
        setTimeout(injectDeleteButtons, 1500);
      });

      // Delegated delete handler
      document.addEventListener('click', function(e){
        const btn = e.target.closest('.delete-product');
        if (!btn) return;
        e.preventDefault();
        const id = btn.getAttribute('data-product-id');
        const name = btn.getAttribute('data-product-name') || 'this product';
        if (!id) return;
        if (!confirm(`Delete ${name}? This cannot be undone.`)) return;
        window.POS?.showLoading?.();
        (window.axios || axios).delete(`/api/products/${id}/delete`)
          .then(res => {
            const ok = res.status >= 200 && res.status < 300;
            const data = res.data || {};
            if (ok && (data.success ?? true)) {
              window.POS?.showToast?.('Product deleted', 'success');
              const card = btn.closest('.product-card, .product-row');
              if (card) card.remove();
            } else {
              const msg = data?.message || data?.error || 'Failed to delete product';
              window.POS?.showToast?.(msg, 'error');
            }
          })
          .catch((err) => {
            const msg = err?.response?.data?.message || 'Failed to delete product';
            window.POS?.showToast?.(msg, 'error')
          })
          .finally(() => window.POS?.hideLoading?.());
      });

      // Refresh METRC from nav button
      document.addEventListener('DOMContentLoaded', function(){
        const btn = document.getElementById('global-refresh-metrc');
        if (!btn) return;
        btn.addEventListener('click', async function(){
          try {
            window.POS?.showLoading?.();
            const res = await (window.axios || axios).get('/api/metrc/transfers/incoming');
            if (!res || res.status < 200 || res.status >= 300) throw new Error('Refresh failed');
            const count = Array.isArray(res.data?.transfers) ? res.data.transfers.length : (res.data?.count || 0);
            window.POS?.showToast?.(`Incoming transfers refreshed${count ? ` (${count})` : ''}`, 'success');
          } catch(e) {
            window.POS?.showToast?.('Failed to refresh METRC data', 'error');
          } finally {
            window.POS?.hideLoading?.();
          }
        });
      });
    })();
    </script>

    @stack('scripts')
</body>
</html>
