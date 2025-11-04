// Cannabis POS Application Entry Point
// Consolidated JavaScript dependencies for production

import '../css/app.css'

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

// Register the component using Alpine.data so you can reference it by name in Blade
Alpine.data('settingsManager', (initial = {}) => ({
  // ----- STATE -----
  activeTab: 'general',
  settings: {
    // hydrate from Blade-injected initial settings
    ...({
      // reasonable safe defaults so bindings don't explode before hydration
      store_name: 'Cannabis POS',
      store_manager: '',
      store_phone: '',
      store_email: '',
      store_address: '',
      website: '',
      license_number: '',
      receipt_footer: "Thank you for your business!\nKeep receipt for returns and warranty.",
      business_hours: [],
      sales_tax: 0, excise_tax: 0, cannabis_tax: 0, tax_inclusive: false,
      exit_label_categories: [],
      receipt_autoprint: false,
      receipt_categories_autoprint: [],
      receipt_show_tax_breakdown: true,
      receipt_show_metrc: true,
      receipt_show_loyalty: true,
      receipt_show_qr_code: false,
      default_receipt_printer: '',
      receipt_paper_size: '80mm',
      minimum_price_enabled: false,
      minimum_price_amount: 0.01,
      minimum_price_categories: [],
      inventory_view_mode: 'cards',
      expandable_cart: true,
      auto_delete_zero_quantity: false,
      auto_delete_zero_days: 1,
      dark_mode: false,
      theme_color: 'green',
      font_size: 'medium',
      high_contrast: false,
      reduce_motion: false,
      metrc_enabled: true,
      metrc_user_key: '',
      metrc_vendor_key: '',
      metrc_facility: ''
    }),
    ...(initial.settings || {})
  },

  categories: initial.categories || [
    'Flower','Pre-Rolls','Concentrates','Edibles','Vape','Topicals','Tinctures','Gear'
  ],
  stores: initial.stores || [],

  // ----- LIFECYCLE -----
  boot() {
    // nothing fancy here, but you can pull latest from API if you want
    // this.hydrateFromApi();
  },

  // ----- ACTIONS -----
  selectTab(tab) { this.activeTab = tab; },

  toggleInArray(key, value) {
    const arr = this.settings[key] || [];
    const idx = arr.indexOf(value);
    if (idx === -1) arr.push(value); else arr.splice(idx, 1);
    this.settings[key] = arr;
  },

  async hydrateFromApi() {
    try {
      const r = await fetch('/api/settings', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
      if (!r.ok) return;
      const js = await r.json();
      if (js?.success && js.settings) this.settings = { ...this.settings, ...js.settings };
    } catch(e) { console.error(e); }
  },

  async saveSettings() {
    try {
      const r = await fetch('/api/settings', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        credentials: 'include', // if using Sanctum cookie; remove if you use Bearer
        body: JSON.stringify(this.settings)
      });
      const js = await r.json().catch(() => ({}));
      if (r.ok && js.success) {
        this.toast('Settings saved', 'success');
      } else {
        this.toast(js.message || `Save failed (${r.status})`, 'error');
      }
    } catch(e) {
      console.error(e);
      this.toast('Unexpected error saving settings', 'error');
    }
  },

  toast(msg, type='info') {
    // replace with your real toast
    console[type === 'error' ? 'error' : 'log']('[Toast]', type.toUpperCase(), msg);
  }
}));

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
