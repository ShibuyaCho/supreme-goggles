// Cannabis POS Authentication and API Handler
class POSAuth {
  constructor() {
    // Helpers for cookie fallback (for environments where localStorage may be cleared)
    const getCookie = (name) => {
      try {
        const match = document.cookie.match(
          new RegExp(
            "(?:^|; )" +
              name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, "\\$1") +
              "=([^;]*)",
          ),
        );
        return match ? decodeURIComponent(match[1]) : null;
      } catch (e) {
        return null;
      }
    };
    const setCookie = (name, value, days = 30) => {
      try {
        const d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${encodeURIComponent(value)}; expires=${d.toUTCString()}; path=/`;
      } catch (e) {}
    };
    const deleteCookie = (name) => {
      try {
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
      } catch (e) {}
    };
    this._cookies = { getCookie, setCookie, deleteCookie };

    // Single source of truth keys
    this.KEY_TOKEN = "pos_token";
    this.KEY_USER = "pos_user";

    // Read token/user (with legacy fallback on read only)
    const posToken =
      (typeof localStorage !== "undefined" &&
        localStorage.getItem(this.KEY_TOKEN)) ||
      null;
    const altToken =
      (typeof localStorage !== "undefined" &&
        localStorage.getItem("auth_token")) ||
      null;
    const cookieToken = getCookie(this.KEY_TOKEN);
    const posUserStr =
      (typeof localStorage !== "undefined" &&
        localStorage.getItem(this.KEY_USER)) ||
      null;
    const altUserStr =
      (typeof localStorage !== "undefined" &&
        localStorage.getItem("user_data")) ||
      null;

    this.token = posToken || altToken || cookieToken || null;
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

    // Multi-tab token sync
    try {
      window.addEventListener("storage", (e) => {
        if (!e) return;
        if (e.key === this.KEY_TOKEN) {
          const next = e.newValue || null;
          if (!next) {
            // Token removed in another tab
            this.clearAuth();
          } else if (next !== this.token) {
            this.token = next;
            try {
              axios.defaults.headers = axios.defaults.headers || {};
              axios.defaults.headers.common =
                axios.defaults.headers.common || {};
              axios.defaults.headers.common["Authorization"] =
                `Bearer ${this.token}`;
            } catch (_) {}
          }
        }
        if (e.key === this.KEY_USER && e.newValue) {
          try {
            this.user = JSON.parse(e.newValue);
          } catch (_) {}
        }
      });
    } catch (_) {}

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
        // Multi-store context headers (UI-managed)
        try {
          const raw = localStorage.getItem("pos_store");
          if (raw) {
            const store = JSON.parse(raw);
            if (store && store.id) {
              let sid = String(store.id || "default");
              sid = sid
                .trim()
                .toLowerCase()
                .replace(/\s+/g, "")
                .replace(/[^a-z0-9_.-]/g, "");
              if (sid === "defaultstore") sid = "default";
              config.headers["X-Store-ID"] = sid || "default";
            }
            if (store && store.orgId)
              config.headers["X-Org-ID"] = String(store.orgId);
          }
        } catch (e) {}
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
          // Refresh failed: clear all auth state to avoid stale token loops
          try { this.clearAuth(); } catch (_) {}
          try { document.dispatchEvent(new CustomEvent("pos-unauthorized")); } catch (e) {}
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
      localStorage.setItem(this.KEY_TOKEN, token);
      localStorage.setItem(this.KEY_USER, JSON.stringify(user));
      // Clean up legacy keys to avoid drift
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user_data");
    } catch (e) {}
    try {
      // Cookie fallback to survive certain reload scenarios and environments
      this._cookies?.setCookie?.(this.KEY_TOKEN, token, 30);
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
      localStorage.removeItem(this.KEY_TOKEN);
    } catch (e) {}
    try {
      localStorage.removeItem(this.KEY_USER);
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
      this._cookies?.deleteCookie?.(this.KEY_TOKEN);
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
    return !!this.token;
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
      const serverUser = response?.data?.user || null;
      if (serverUser) {
        // Prevent role downgrades; prefer the higher role and union permissions
        const current = this.user || {};
        const rank = (r) =>
          ({ cashier: 1, budtender: 2, inventory: 3, manager: 4, admin: 5 })[
            String(r || "").toLowerCase()
          ] || 0;
        const bestRole =
          rank(current.role) >= rank(serverUser.role)
            ? current.role || serverUser.role
            : serverUser.role;
        const permsA = Array.isArray(current.permissions)
          ? current.permissions
          : [];
        const permsB = Array.isArray(serverUser.permissions)
          ? serverUser.permissions
          : [];
        const hasAll =
          String(bestRole).toLowerCase() === "admin" ||
          permsA.includes("*") ||
          permsB.includes("*");
        const unionPerms = hasAll
          ? ["*"]
          : Array.from(new Set([...(permsA || []), ...(permsB || [])]));
        const mergedEmployee = {
          ...(serverUser.employee || {}),
          ...(current.employee || {}),
          role:
            bestRole ||
            serverUser.employee?.role ||
            current.employee?.role ||
            serverUser.role ||
            current.role ||
            "cashier",
          permissions: unionPerms,
        };
        this.user = {
          ...serverUser,
          ...current,
          role: bestRole || serverUser.role || current.role,
          permissions: unionPerms,
          employee: mergedEmployee,
        };
        try {
          localStorage.setItem("pos_user", JSON.stringify(this.user));
          localStorage.setItem("user_data", JSON.stringify(this.user));
        } catch (e) {}
      }
      this.touchActivity();
      return this.user;
    } catch (error) {
      console.error("Failed to refresh user:", error);
      return this.user || null;
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
        localStorage.setItem(this.KEY_TOKEN, this.token);
        // Remove legacy
        localStorage.removeItem("auth_token");
      } catch (e) {}
      try {
        this._cookies?.setCookie?.(this.KEY_TOKEN, token, 30);
      } catch (e) {}
      try {
        axios.defaults.headers = axios.defaults.headers || {};
        axios.defaults.headers.common = axios.defaults.headers.common || {};
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
      } catch (e) {}
      return true;
    } catch (error) {
      console.warn("Failed to refresh token:", error);
      try { this.clearAuth(); } catch (_) {}
      try { document.dispatchEvent(new CustomEvent("pos-unauthorized")); } catch (_) {}
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
    if (this.token) {
      // If we have a token but not a user, fetch user info to rehydrate session
      if (!this.user) {
        this.refreshUser().catch(() => {});
      } else {
        // Lazy refresh user when we already have cached data
        this.refreshUser().catch(() => {});
      }
    }
    return this.isAuthenticated();
  }
}

// Global auth instance
window.posAuth = new POSAuth();

// Expose a readiness promise so other modules can await auth init
window.posAuthReady = new Promise((resolve) => {
  document.addEventListener("DOMContentLoaded", async () => {
    try {
      window.posAuth.init();
    } catch (_) {}
    resolve();
  });
});

// Export for use in other modules
if (typeof module !== "undefined" && module.exports) {
  module.exports = POSAuth;
}
