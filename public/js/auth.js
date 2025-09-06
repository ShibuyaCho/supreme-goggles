// Cannabis POS Authentication and API Handler
class POSAuth {
    constructor() {
        this.token = localStorage.getItem('pos_token');
        this.user = JSON.parse(localStorage.getItem('pos_user') || 'null');
        this.baseUrl = '/api';
        this.setupAxiosInterceptors();
    }

    /**
     * Setup Axios interceptors for authentication and error handling
     */
    setupAxiosInterceptors() {
        // Request interceptor to add auth token
        axios.interceptors.request.use(
            (config) => {
                if (this.token) {
                    config.headers.Authorization = `Bearer ${this.token}`;
                }
                config.headers['Content-Type'] = 'application/json';
                config.headers['Accept'] = 'application/json';
                return config;
            },
            (error) => {
                return Promise.reject(error);
            }
        );

        // Response interceptor to handle auth errors
        axios.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    this.logout();
                    try { document.dispatchEvent(new CustomEvent('pos-unauthorized')); } catch(e) {}
                }
                return Promise.reject(error);
            }
        );
    }

    /**
     * Login with email and password
     */
    async login(email, password, remember = false) {
        const attempt = async (url) => axios.post(url, { email, password, remember });
        try {
            let response;
            try {
                response = await attempt(`${this.baseUrl}/auth/login`);
            } catch (e) {
                if (e?.response?.status === 404) {
                    response = await attempt(`${this.baseUrl}/login`);
                } else { throw e; }
            }

            const { user, token } = response.data;
            this.setAuth(user, token);
            return { success: true, user, message: response.data.message };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.error || 'Login failed',
                errors: error.response?.data?.errors
            };
        }
    }

    /**
     * Login with employee PIN (for POS terminals)
     */
    async pinLogin(employeeId, pin) {
        const attempt = async (url) => axios.post(url, { employee_id: employeeId, pin });
        try {
            let response;
            try {
                response = await attempt(`${this.baseUrl}/auth/pin-login`);
            } catch (e) {
                if (e?.response?.status === 404) {
                    response = await attempt(`${this.baseUrl}/pin-login`);
                } else { throw e; }
            }

            const { employee, token } = response.data;
            const user = {
                id: employee.id,
                name: employee.name,
                role: employee.role,
                permissions: employee.permissions,
                employee: employee
            };
            this.setAuth(user, token);
            return { success: true, user, employee, message: response.data.message };
        } catch (error) {
            return { success: false, message: error.response?.data?.error || 'PIN login failed' };
        }
    }

    /**
     * Set authentication data
     */
    setAuth(user, token) {
        this.token = token;
        this.user = user;
        localStorage.setItem('pos_token', token);
        localStorage.setItem('pos_user', JSON.stringify(user));
    }

    /**
     * Logout user
     */
    async logout() {
        try {
            if (this.token) {
                await axios.post(`${this.baseUrl}/auth/logout`);
            }
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            this.clearAuth();
        }
    }

    /**
     * Clear authentication data
     */
    clearAuth() {
        this.token = null;
        this.user = null;
        localStorage.removeItem('pos_token');
        localStorage.removeItem('pos_user');
    }

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!this.token && !!this.user;
    }

    /**
     * Check if user has specific permission
     */
    hasPermission(permission) {
        if (!this.user) return false;
        if (this.user.role === 'admin') return true;
        return this.user.permissions?.includes(permission) || 
               this.user.permissions?.includes('*');
    }

    /**
     * Check if user has specific role
     */
    hasRole(role) {
        return this.user?.role === role;
    }

    /**
     * Get current user
     */
    getUser() {
        return this.user;
    }

    /**
     * Refresh user data from server
     */
    async refreshUser() {
        try {
            const response = await axios.get(`${this.baseUrl}/auth/me`);
            this.user = response.data.user;
            localStorage.setItem('pos_user', JSON.stringify(this.user));
            return this.user;
        } catch (error) {
            console.error('Failed to refresh user:', error);
            return null;
        }
    }

    /**
     * Refresh authentication token
     */
    async refreshToken() {
        try {
            const response = await axios.post(`${this.baseUrl}/auth/refresh`);
            this.token = response.data.token;
            localStorage.setItem('pos_token', this.token);
            return true;
        } catch (error) {
            console.error('Failed to refresh token:', error);
            this.logout();
            return false;
        }
    }

    /**
     * Change password
     */
    async changePassword(currentPassword, newPassword, confirmPassword) {
        try {
            const response = await axios.post(`${this.baseUrl}/auth/change-password`, {
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: confirmPassword
            });

            return {
                success: true,
                message: response.data.message
            };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.error || 'Password change failed',
                errors: error.response?.data?.errors
            };
        }
    }

    /**
     * Test METRC connection
     */
    async testMetrcConnection() {
        try {
            const response = await axios.post(`${this.baseUrl}/auth/verify-metrc`);
            return {
                success: true,
                data: response.data
            };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.error || 'METRC verification failed',
                data: error.response?.data
            };
        }
    }

    /**
     * Make authenticated API request
     */
    async apiRequest(method, endpoint, data = null) {
        try {
            const config = {
                method,
                url: `${this.baseUrl}${endpoint}`,
            };

            if (data) {
                if (method.toLowerCase() === 'get') {
                    config.params = data;
                } else {
                    config.data = data;
                }
            }

            const response = await axios(config);
            return {
                success: true,
                data: response.data
            };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.error || 'API request failed',
                errors: error.response?.data?.errors,
                status: error.response?.status
            };
        }
    }

    /**
     * Get products with filtering
     */
    async getProducts(filters = {}) {
        return this.apiRequest('get', '/products', filters);
    }

    /**
     * Get customers
     */
    async getCustomers(search = '') {
        return this.apiRequest('get', '/customers', { search });
    }

    /**
     * Process payment
     */
    async processPayment(paymentData) {
        return this.apiRequest('post', '/pos/process-payment', paymentData);
    }

    /**
     * Get METRC package details
     */
    async getMetrcPackage(packageTag) {
        return this.apiRequest('get', `/metrc/packages/${packageTag}`);
    }

    /**
     * Test METRC connection via dedicated endpoint
     */
    async testMetrcConnectionDirect() {
        return this.apiRequest('get', '/metrc/test-connection');
    }

    /**
     * Initialize authentication check on page load
     */
    init() {
        // Check if token is expired (basic check)
        if (this.token && this.user) {
            // Verify token is still valid by making a test request
            this.refreshUser().catch(() => {
                this.logout();
            });
        }

        return this.isAuthenticated();
    }
}

// Global auth instance
window.posAuth = new POSAuth();

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    posAuth.init();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = POSAuth;
}
