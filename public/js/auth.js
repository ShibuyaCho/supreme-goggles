// Cannabis POS Authentication and API Handler
class POSAuth {
  constructor() {
    const posToken = localStorage.getItem("pos_token");
    const altToken = localStorage.getItem("auth_token");
    const posUserStr = localStorage.getItem("pos_user");
    const altUserStr = localStorage.getItem("user_data");
    this.token = posToken || altToken || null;
    this.user = null;
    try {
      this.user = posUserStr
        ? JSON.parse(posUserStr)
        : altUserStr
          ? JSON.parse(altUserStr)
          : null;
    } catch (e) {
      this.user = null;
    }
    this.baseUrl = "/api";
    this.inactivityMs = 365 * 24 * 60 * 60 * 1000; // 1 year (effectively disabled)
    if (this.token) {
      axios.defaults.headers = axios.defaults.headers || {};
      axios.defaults.headers.common = axios.defaults.headers.common || {};
      axios.defaults.headers.common["Authorization"] = `Bearer ${this.token}`;
    }
    this.setupAxiosInterceptors();
    this.setupActivityTracking();
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
        config.headers["Content-Type"] = "application/json";
        config.headers["Accept"] = "application/json";
        return config;
      },
      (error) => Promise.reject(error),
    );

    // Response interceptor: try refresh once on 401, then retry
    axios.interceptors.response.use(
      (response) => response,
      async (error) => {
        const original = error?.config || {};
        if (error.response?.status === 401 && !original._retry) {
          original._retry = true;
          const refreshed = await this.refreshToken();
          if (refreshed) {
            original.headers = original.headers || {};
            original.headers["Authorization"] = `Bearer ${this.token}`;
            try {
              return await axios(original);
            } catch (e) {}
          }
          // Only logout if inactivity exceeded; otherwise keep modal
          const inactive = this.isInactiveBeyondLimit();
          if (inactive) this.logout();
          try {
            document.dispatchEvent(new CustomEvent("pos-unauthorized"));
          } catch (e) {}
        }
        return Promise.reject(error);
      },
    );
  }

  /**
   * Login with email and password
   */
  async login(email, password, remember = false) {
    const payload = { email: String(email || "").trim(), password, remember };
    const attempt = async (url) => axios.post(url, payload);
    try {
      let response;
      try {
        response = await attempt(`${this.baseUrl}/auth/login`);
      } catch (e) {
        if (e?.response?.status === 404) {
          response = await attempt(`${this.baseUrl}/login`);
        } else {
          throw e;
        }
      }

      const { user, token } = response.data;
      this.setAuth(user, token);
      return { success: true, user, message: response.data.message };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.error || "Login failed",
        errors: error.response?.data?.errors,
      };
    }
  }

  /**
   * Login with employee PIN (for POS terminals)
   */
  async pinLogin(employeeId, pin) {
    const attempt = async (url) =>
      axios.post(url, { employee_id: employeeId, pin });
    try {
      let response;
      try {
        response = await attempt(`${this.baseUrl}/auth/pin-login`);
      } catch (e) {
        if (e?.response?.status === 404) {
          response = await attempt(`${this.baseUrl}/pin-login`);
        } else {
          throw e;
        }
      }

      const { employee, token } = response.data;
      const user = {
        id: employee.id,
        name: employee.name,
        role: employee.role,
        permissions: employee.permissions,
        employee: employee,
      };
      this.setAuth(user, token);
      return { success: true, user, employee, message: response.data.message };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.error || "PIN login failed",
      };
    }
  }

  /**
   * Set authentication data
   */
  setAuth(user, token) {
    this.token = token;
    this.user = user;
    try {
      localStorage.setItem("pos_token", token);
      localStorage.setItem("pos_user", JSON.stringify(user));
      // Back-compat keys
      localStorage.setItem("auth_token", token);
      localStorage.setItem("user_data", JSON.stringify(user));
    } catch (e) {}
    try {
      axios.defaults.headers = axios.defaults.headers || {};
      axios.defaults.headers.common = axios.defaults.headers.common || {};
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    } catch (e) {}
    this.touchActivity();
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
      console.error("Logout error:", error);
    } finally {
      this.clearAuth();
    }
  }

  // Inactivity tracking
  setupActivityTracking() {
    const update = this.touchActivity.bind(this);
    [
      "click",
      "keydown",
      "mousemove",
      "scroll",
      "touchstart",
      "touchmove",
    ].forEach((evt) => {
      window.addEventListener(evt, update, { passive: true });
    });
    // Initialize if absent
    if (!localStorage.getItem("pos_last_activity")) this.touchActivity();
  }
  touchActivity() {
    try {
      localStorage.setItem("pos_last_activity", String(Date.now()));
    } catch (e) {}
  }
  isInactiveBeyondLimit() {
    try {
      const v = Number(localStorage.getItem("pos_last_activity") || "0");
      if (!v) return false;
      return Date.now() - v > this.inactivityMs;
    } catch (e) {
      return false;
    }
  }

  /**
   * Clear authentication data
   */
  clearAuth() {
    this.token = null;
    this.user = null;
    try {
      localStorage.removeItem("pos_token");
    } catch (e) {}
    try {
      localStorage.removeItem("pos_user");
    } catch (e) {}
    try {
      localStorage.removeItem("pos_last_activity");
    } catch (e) {}
    try {
      localStorage.removeItem("auth_token");
    } catch (e) {}
    try {
      localStorage.removeItem("user_data");
    } catch (e) {}
    try {
      localStorage.removeItem("cannabisPOS-auth");
    } catch (e) {}
    try {
      if (axios?.defaults?.headers?.common)
        delete axios.defaults.headers.common["Authorization"];
    } catch (e) {}
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
    if (this.user.role === "admin") return true;
    return (
      this.user.permissions?.includes(permission) ||
      this.user.permissions?.includes("*")
    );
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
      try {
        localStorage.setItem("pos_user", JSON.stringify(this.user));
        localStorage.setItem("user_data", JSON.stringify(this.user));
      } catch (e) {}
      this.touchActivity();
      return this.user;
    } catch (error) {
      console.error("Failed to refresh user:", error);
      return null;
    }
  }

  /**
   * Refresh authentication token
   */
  async refreshToken() {
    try {
      const response = await axios.post(`${this.baseUrl}/auth/refresh`);
      const token = response?.data?.token;
      if (!token) throw new Error("No token");
      this.token = token;
      try {
        localStorage.setItem("pos_token", this.token);
        localStorage.setItem("auth_token", this.token);
      } catch (e) {}
      try {
        axios.defaults.headers = axios.defaults.headers || {};
        axios.defaults.headers.common = axios.defaults.headers.common || {};
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
      } catch (e) {}
      return true;
    } catch (error) {
      console.warn("Failed to refresh token:", error);
      return false;
    }
  }

  /**
   * Change password
   */
  async changePassword(currentPassword, newPassword, confirmPassword) {
    try {
      const response = await axios.post(
        `${this.baseUrl}/auth/change-password`,
        {
          current_password: currentPassword,
          new_password: newPassword,
          new_password_confirmation: confirmPassword,
        },
      );

      return {
        success: true,
        message: response.data.message,
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.error || "Password change failed",
        errors: error.response?.data?.errors,
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
        data: response.data,
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.error || "METRC verification failed",
        data: error.response?.data,
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
        if (method.toLowerCase() === "get") {
          config.params = data;
        } else {
          config.data = data;
        }
      }

      const response = await axios(config);
      return {
        success: true,
        data: response.data,
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.error || "API request failed",
        errors: error.response?.data?.errors,
        status: error.response?.status,
      };
    }
  }

  /**
   * Get products with filtering
   */
  async getProducts(filters = {}) {
    return this.apiRequest("get", "/products", filters);
  }

  /**
   * Get customers
   */
  async getCustomers(search = "") {
    return this.apiRequest("get", "/customers", { search });
  }

  /**
   * Process payment
   */
  async processPayment(paymentData) {
    return this.apiRequest("post", "/pos/process-payment", paymentData);
  }

  /**
   * Get METRC package details
   */
  async getMetrcPackage(packageTag) {
    return this.apiRequest("get", `/metrc/packages/${packageTag}`);
  }

  /**
   * Test METRC connection via dedicated endpoint
   */
  async testMetrcConnectionDirect() {
    return this.apiRequest("get", "/metrc/test-connection");
  }

  /**
   * Self-register new user with PIN (with endpoint fallback)
   */
  async selfRegister({ name, email, password, passwordConfirm, pin }) {
    const payload = {
      name: String(name || "").trim(),
      email: String(email || "").trim(),
      password,
      password_confirmation: passwordConfirm,
      pin,
    };
    const tryPost = async (url) => axios.post(url, payload);
    try {
      let res;
      try {
        res = await tryPost(`${this.baseUrl}/auth/self-register`);
      } catch (e) {
        if (e?.response?.status === 404) {
          try {
            res = await tryPost(`${this.baseUrl}/self-register`);
          } catch (e2) {
            if (e2?.response?.status === 404) {
              res = await tryPost(`/auth/self-register`);
            } else {
              throw e2;
            }
          }
        } else {
          throw e;
        }
      }
      const { user, token } = res.data || {};
      if (user && token) {
        this.setAuth(user, token);
        return { success: true, user, message: res.data.message };
      }
      return { success: false, message: "Invalid response from server" };
    } catch (error) {
      const message =
        error?.response?.data?.error ||
        error?.response?.data?.message ||
        "Registration failed";
      const errors = error?.response?.data?.errors || null;
      return {
        success: false,
        message,
        errors,
        status: error?.response?.status,
      };
    }
  }

  /**
   * Initialize authentication check on page load
   */
  init() {
    if (this.token && this.user) {
      // Lazy refresh user; don't logout on failure here
      this.refreshUser().catch(() => {});
    }
    return this.isAuthenticated();
  }
}

// Global auth instance
window.posAuth = new POSAuth();

// Auto-initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  posAuth.init();
});

// Export for use in other modules
if (typeof module !== "undefined" && module.exports) {
  module.exports = POSAuth;
}
