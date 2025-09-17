(function () {
  const COOKIE_MAX_AGE = 60 * 60 * 24 * 365; // 1 year
  const LS_KEY = (sid) => `cpos_settings_${sid}`;
  const CK_KEY = (sid) => `cpos_settings_${sid}`;
  const DEFAULTS = {
    // Store info
    store_name: "Cannabest POS",
    store_address: "",
    store_phone: "",
    store_email: "",
    website: "",
    store_manager: "",
    license_number: "",
    receipt_footer:
      "Thank you for your business!\nKeep receipt for returns and warranty.",

    // Taxes
    sales_tax: 0,
    excise_tax: 10,
    cannabis_tax: 17,
    tax_inclusive: false,

    // Receipt/printing
    auto_print_receipt: false,
    receipt_autoprint: false,
    receipt_categories_autoprint: [],
    receipt_paper_size: "80mm",

    // POS behavior / payments
    require_customer: true,
    age_verification: true,
    limit_enforcement: true,
    accept_cash: true,
    accept_debit: true,
    accept_check: false,
    round_to_nearest: false,

    // Pricing
    minimum_price_enabled: false,
    minimum_price_amount: 0.01,
    minimum_price_categories: [],

    // Display & inventory
    inventory_view_mode: "cards",
    expandable_cart: true,

    // Auto delete
    auto_delete_zero_quantity: false,
    auto_delete_zero_days: 1,

    // Hours
    business_hours: [
      { day: "Monday", is_open: true, open_time: "09:00", close_time: "21:00" },
      {
        day: "Tuesday",
        is_open: true,
        open_time: "09:00",
        close_time: "21:00",
      },
      {
        day: "Wednesday",
        is_open: true,
        open_time: "09:00",
        close_time: "21:00",
      },
      {
        day: "Thursday",
        is_open: true,
        open_time: "09:00",
        close_time: "21:00",
      },
      { day: "Friday", is_open: true, open_time: "09:00", close_time: "21:00" },
      {
        day: "Saturday",
        is_open: true,
        open_time: "10:00",
        close_time: "20:00",
      },
      { day: "Sunday", is_open: true, open_time: "11:00", close_time: "19:00" },
    ],
  };

  function currentStoreId() {
    try {
      const raw = localStorage.getItem("pos_store");
      if (!raw) return "default";
      const s = JSON.parse(raw);
      let id = s && s.id ? String(s.id) : "default";
      id = id
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "")
        .replace(/[^a-z0-9_.-]/g, "");
      if (id === "defaultstore") id = "default";
      return id || "default";
    } catch (_) {
      return "default";
    }
  }
  function readCookie(name) {
    try {
      const m = document.cookie.match(
        new RegExp(
          "(?:^|; )" +
            name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, "\\$1") +
            "=([^;]*)",
        ),
      );
      return m ? decodeURIComponent(m[1]) : null;
    } catch (_) {
      return null;
    }
  }
  function writeCookie(name, value) {
    try {
      document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${COOKIE_MAX_AGE}`;
    } catch (_) {}
  }

  async function httpGet(path, params) {
    // Prefer posAuth if present; it automatically adds headers
    if (window.posAuth) {
      const res = await window.posAuth.apiRequest(
        "get",
        path.replace(/^\/api/, ""),
        params || {},
      );
      if (res && res.success && res.data) return res.data;
      if (res && res.data) return res.data;
    }
    const sid = currentStoreId();
    const cfg = { headers: { Accept: "application/json", "X-Store-ID": sid } };
    if (params) {
      cfg.params = params;
      if (params.nocache) cfg.headers["Cache-Control"] = "no-cache";
    }
    const r = await (window.axios || axios).get(path, cfg);
    return r.data;
  }
  async function httpPost(path, body, params) {
    if (window.posAuth) {
      const res = await window.posAuth.apiRequest(
        "post",
        path.replace(/^\/api/, ""),
        params ? { ...(body || {}), ...(params || {}) } : body || {},
      );
      if (res && res.success && res.data) return res.data;
      if (res && res.data) return res.data;
    }
    const sid = currentStoreId();
    const cfg = {
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Store-ID": sid,
      },
    };
    if (params) {
      cfg.params = params;
      if (params.nocache) cfg.headers["Cache-Control"] = "no-cache";
    }
    const r = await (window.axios || axios).post(path, body || {}, cfg);
    return r.data;
  }

  async function getFromServer(sid, noCache = false) {
    // Try Laravel first
    try {
      return await httpGet("/api/settings/pos", {
        store: sid,
        nocache: noCache ? 1 : 0,
      });
    } catch (_) {}
    // Try Node alias (if applicable)
    try {
      return await httpGet("/api/settings/pos", {
        store: sid,
        nocache: noCache ? 1 : 0,
      });
    } catch (_) {}
    return null;
  }

  const SettingsClient = {
    defaults: () => ({ ...DEFAULTS }),
    currentStoreId,

    loadLocal(sid) {
      try {
        const s = localStorage.getItem(LS_KEY(sid));
        if (s) return JSON.parse(s);
      } catch (_) {}
      try {
        const c = readCookie(CK_KEY(sid));
        if (c) return JSON.parse(c);
      } catch (_) {}
      return null;
    },
    saveLocal(sid, settings) {
      try {
        localStorage.setItem(LS_KEY(sid), JSON.stringify(settings));
      } catch (_) {}
      try {
        writeCookie(CK_KEY(sid), JSON.stringify(settings));
      } catch (_) {}
    },

    async get(force = false) {
      const sid = currentStoreId();
      if (!force) {
        const local = this.loadLocal(sid);
        if (local)
          return { success: true, settings: { ...DEFAULTS, ...local } };
      }
      // Server with retries
      let last = null;
      for (let i = 0; i < 3; i++) {
        try {
          const data = await getFromServer(sid, true);
          const settings =
            data && typeof data === "object" && (data.settings || data)
              ? data.settings || data
              : {};
          const merged = { ...DEFAULTS, ...settings };
          this.saveLocal(sid, merged);
          try {
            window.dispatchEvent(
              new CustomEvent("settings:updated", {
                detail: { settings: merged, storeId: sid },
              }),
            );
          } catch (_) {}
          return { success: true, settings: merged };
        } catch (e) {
          last = e;
          await new Promise((r) => setTimeout(r, 200 * (i + 1)));
        }
      }
      // Fallback to local defaults
      const fallback = this.loadLocal(sid) || DEFAULTS;
      return {
        success: false,
        settings: { ...DEFAULTS, ...fallback },
        error: last,
      };
    },

    async save(patch) {
      const sid = currentStoreId();
      let base = this.loadLocal(sid) || {};
      // Prefetch current from server to avoid overwriting other fields
      try {
        const srv = await getFromServer(sid, true);
        const cur = srv && (srv.settings || srv) ? srv.settings || srv : {};
        if (cur && typeof cur === "object") base = { ...base, ...cur };
      } catch (_) {}
      const merged = { ...DEFAULTS, ...base, ...(patch || {}) };
      this.saveLocal(sid, merged);
      // Fire and retry server save
      let last = null;
      for (let i = 0; i < 3; i++) {
        try {
          const data = await httpPost("/api/settings/pos", merged, {
            store: sid,
          });
          const s =
            data && (data.settings || data) ? data.settings || data : merged;
          let m = { ...DEFAULTS, ...s };
          // Read-after-write verification (bypass cache)
          try {
            const verify = await getFromServer(sid, true);
            const vs =
              verify && (verify.settings || verify)
                ? verify.settings || verify
                : {};
            if (vs && Object.keys(vs).length) m = { ...DEFAULTS, ...vs };
          } catch (_) {}
          this.saveLocal(sid, m);
          try {
            window.dispatchEvent(
              new CustomEvent("settings:updated", {
                detail: { settings: m, storeId: sid },
              }),
            );
          } catch (_) {}
          return { success: true, settings: m };
        } catch (e) {
          last = e;
          await new Promise((r) => setTimeout(r, 200 * (i + 1)));
        }
      }
      return { success: false, settings: merged, error: last };
    },
  };

  window.SettingsClient = SettingsClient;
})();
