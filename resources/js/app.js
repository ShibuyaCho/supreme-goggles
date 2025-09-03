// Cannabis POS Application Entry Point
// Consolidated JavaScript dependencies for production

// Import Alpine.js
import Alpine from 'alpinejs';

// Import Axios
import axios from 'axios';

// Import QRious
import QRious from 'qrious';

// Import local modules
import './pos-core.js';
import './auth-core.js';

// Configure Axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Get CSRF token from meta tag
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

// Configure axios interceptors for auth
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Handle unauthorized - redirect to login
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            window.location.reload();
        }
        return Promise.reject(error);
    }
);

// Make axios globally available
window.axios = axios;

// Make QRious globally available
window.QRious = QRious;

// Configure Alpine.js
Alpine.store('app', {
    version: '1.0.0',
    environment: 'production',
    apiBaseUrl: '/api',
    debug: false
});

// Start Alpine.js
Alpine.start();

// Make Alpine globally available (for compatibility)
window.Alpine = Alpine;

// Production error handling
window.addEventListener('error', (event) => {
    console.error('Application Error:', {
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        error: event.error
    });
    
    // In production, you might want to send this to a logging service
    // logErrorToService(event);
});

// Unhandled promise rejection handling
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason);
    
    // In production, you might want to send this to a logging service
    // logErrorToService(event);
});

console.log('Cannabis POS Application Loaded - Production Mode');
