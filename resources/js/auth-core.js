// Core Authentication functionality extracted from auth.js
// Handles token management and API authentication

class AuthManager {
  constructor() {
    this.token = localStorage.getItem("auth_token");
    this.user = this.getStoredUser();
    this.refreshTimer = null;
  }

  getStoredUser() {
    const userData = localStorage.getItem("user_data");
    return userData ? JSON.parse(userData) : null;
  }

  isAuthenticated() {
    return !!(this.token && this.user);
  }

  async login(credentials) {
    try {
      const response = await axios.post("/api/login", credentials);

      if (response.data.token) {
        this.setAuth(response.data.token, response.data.user);
        this.startTokenRefresh();
        return { success: true, user: response.data.user };
      }

      return { success: false, error: "Invalid response from server" };
    } catch (error) {
      console.error("Login error:", error);
      return {
        success: false,
        error: error.response?.data?.message || "Login failed",
      };
    }
  }

  async pinLogin(employeeId, pin) {
    try {
      const response = await axios.post("/api/pin-login", {
        employee_id: employeeId,
        pin: pin,
      });

      if (response.data.token) {
        this.setAuth(response.data.token, response.data.user);
        this.startTokenRefresh();
        return { success: true, user: response.data.user };
      }

      return { success: false, error: "Invalid response from server" };
    } catch (error) {
      console.error("PIN login error:", error);
      return {
        success: false,
        error: error.response?.data?.message || "PIN login failed",
      };
    }
  }

  async logout() {
    try {
      if (this.token) {
        try {
          await axios.post("/api/auth/logout");
        } catch (e) {
          if (e?.response?.status === 404) {
            await axios.post("/api/logout");
          } else {
            throw e;
          }
        }
      }
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      this.clearAuth();
    }
  }

  setAuth(token, user) {
    this.token = token;
    this.user = user;
    localStorage.setItem("auth_token", token);
    localStorage.setItem("user_data", JSON.stringify(user));

    // Update axios default headers
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  }

  clearAuth() {
    this.token = null;
    this.user = null;
    localStorage.removeItem("auth_token");
    localStorage.removeItem("user_data");

    // Remove axios authorization header
    delete axios.defaults.headers.common["Authorization"];

    this.stopTokenRefresh();
  }

  async refreshToken() {
    try {
      let response;
      try {
        response = await axios.post("/api/auth/refresh");
      } catch (e) {
        if (e?.response?.status === 404) {
          response = await axios.post("/api/refresh");
        } else {
          throw e;
        }
      }

      if (response.data.token) {
        this.setAuth(response.data.token, response.data.user || this.user);
        return true;
      }

      return false;
    } catch (error) {
      console.error("Token refresh failed:", error);
      this.clearAuth();
      return false;
    }
  }

  startTokenRefresh() {
    // Refresh token every 50 minutes (assuming 60-minute expiry)
    this.refreshTimer = setInterval(
      async () => {
        await this.refreshToken();
      },
      50 * 60 * 1000,
    );
  }

  stopTokenRefresh() {
    if (this.refreshTimer) {
      clearInterval(this.refreshTimer);
      this.refreshTimer = null;
    }
  }

  async checkPermission(permission) {
    if (!this.user || !this.user.permissions) {
      return false;
    }

    // Admin has all permissions
    if (this.user.permissions.includes("*")) {
      return true;
    }

    if (this.user.permissions.includes(permission)) return true;
    // Support wildcard namespace, e.g., "products:*"
    const idx = permission.indexOf(":");
    if (idx > 0) {
      const ns = permission.slice(0, idx);
      if (this.user.permissions.includes(`${ns}:*`)) return true;
    }
    return false;
  }

  hasRole(role) {
    return this.user?.role === role;
  }

  getUserInfo() {
    return this.user;
  }

  getToken() {
    return this.token;
  }
}

// Create global auth manager instance
window.authManager = new AuthManager();

// Set initial axios header if token exists
if (window.authManager.token) {
  axios.defaults.headers.common["Authorization"] =
    `Bearer ${window.authManager.token}`;
}
