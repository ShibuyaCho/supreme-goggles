// Core POS functionality extracted from pos.js
// This consolidates the cannabisPOS function and related utilities

function cannabisPOS() {
    return {
        // Authentication state
        isAuthenticated: false,
        currentUser: null,
        showAuthModal: false,
        metrcConnected: false,

        // Login form data
        loginEmail: '',
        loginPassword: '',
        employeeId: '',
        employeePin: '',

        // Application state
        currentPage: 'pos',
        viewMode: 'cards',
        sortOrder: 'name-asc',
        searchQuery: '',
        selectedCategory: '',
        taxRate: 20.0,
        medicalTaxRate: 0.0,
        selectedCustomer: null,
        selectedProduct: null,
        ageVerified: false,
        cartExpanded: true,
        cartViewMode: 'narrow',

        // Modal states
        showCustomerModal: false,
        showNewSaleModal: false,
        showPaymentModal: false,
        showSettingsModal: false,

        // Data collections
        products: [],
        customers: [],
        cart: [],
        sales: [],

        // Initialization
        async init() {
            await this.checkAuthStatus();
            if (this.isAuthenticated) {
                await this.loadInitialData();
            }
        },

        // Authentication methods
        async checkAuthStatus() {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                this.showAuthModal = true;
                return;
            }

            try {
                const response = await axios.get('/api/user');
                this.currentUser = response.data;
                this.isAuthenticated = true;
                this.showAuthModal = false;
            } catch (error) {
                console.error('Auth check failed:', error);
                this.logout();
            }
        },

        async handleLogin(email, password) {
            try {
                const response = await axios.post('/api/login', {
                    email: email,
                    password: password
                });

                if (response.data.token) {
                    localStorage.setItem('auth_token', response.data.token);
                    localStorage.setItem('user_data', JSON.stringify(response.data.user));
                    this.currentUser = response.data.user;
                    this.isAuthenticated = true;
                    this.showAuthModal = false;
                    await this.loadInitialData();
                }
            } catch (error) {
                console.error('Login failed:', error);
                this.showError('Login failed. Please check your credentials.');
            }
        },

        async handlePinLogin(employeeId, pin) {
            try {
                const response = await axios.post('/api/pin-login', {
                    employee_id: employeeId,
                    pin: pin
                });

                if (response.data.token) {
                    localStorage.setItem('auth_token', response.data.token);
                    localStorage.setItem('user_data', JSON.stringify(response.data.user));
                    this.currentUser = response.data.user;
                    this.isAuthenticated = true;
                    this.showAuthModal = false;
                    await this.loadInitialData();
                }
            } catch (error) {
                console.error('PIN login failed:', error);
                this.showError('PIN login failed. Please check your credentials.');
            }
        },

        logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            this.currentUser = null;
            this.isAuthenticated = false;
            this.showAuthModal = true;
            this.resetState();
        },

        // Data loading methods
        async loadInitialData() {
            try {
                await Promise.all([
                    this.loadProducts(),
                    this.loadCustomers(),
                    this.checkMetrcConnection()
                ]);
            } catch (error) {
                console.error('Failed to load initial data:', error);
            }
        },

        async loadProducts() {
            try {
                const response = await axios.get('/api/products');
                this.products = response.data;
            } catch (error) {
                console.error('Failed to load products:', error);
            }
        },

        async loadCustomers() {
            try {
                const response = await axios.get('/api/customers');
                this.customers = response.data;
            } catch (error) {
                console.error('Failed to load customers:', error);
            }
        },

        async checkMetrcConnection() {
            try {
                const response = await axios.get('/api/metrc/status');
                this.metrcConnected = response.data.connected;
            } catch (error) {
                console.error('Failed to check METRC connection:', error);
                this.metrcConnected = false;
            }
        },

        // Utility methods
        showError(message) {
            // You can implement your toast/notification system here
            console.error(message);
            alert(message); // Temporary fallback
        },

        resetState() {
            this.currentPage = 'pos';
            this.cart = [];
            this.selectedCustomer = null;
            this.selectedProduct = null;
            this.searchQuery = '';
            this.selectedCategory = '';
        },

        // Navigation
        setCurrentPage(page) {
            this.currentPage = page;
        },

        getCurrentPageTitle() {
            const titles = {
                'pos': 'Point of Sale',
                'customers': 'Customer Management',
                'products': 'Products',
                'metrc-vendors': 'METRC Transfers',
                'employees': 'Employees',
                'rooms-drawers': 'Rooms & Drawers',
                'price-tiers': 'Price Tiers',
                'sales': 'Sales',
                'order-queue': 'Order Queue',
                'inventory-evaluation': 'Inventory Evaluation Report',
                'analytics': 'Analytics',
                'reports': 'Reports',
                'deals': 'Deals & Specials',
                'loyalty': 'Loyalty Program',
                'settings': 'Settings'
            };
            return titles[this.currentPage] || 'Unknown Page';
        }
    };
}

// Make the function globally available
window.cannabisPOS = cannabisPOS;
