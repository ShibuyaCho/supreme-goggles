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
    receipt_show_tax_breakdown: true,
    receipt_show_metrc: true,
    receipt_show_loyalty: true,
    receipt_show_qr_code: false,
    default_receipt_printer: "",
    receipt_paper_size: "80mm",
    exit_label_categories: [
      "Flower",
      "Pre-Rolls",
      "Infused",
      "Edibles",
      "Concentrates",
      "Vape Products",
      "Tinctures",
      "Topicals",
      "Capsules",
      "Beverages",
      "Suppositories",
      "Clones/Seeds",
      "Immature Plants",
      "Mature Plants",
      "Hemp",
      "Accessories",
      "Inhalable Cannabinoids",
      "Clones",
      "Seeds",
    ],

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
    theme_color: "green",
    font_size: "medium",
    high_contrast: false,
    reduce_motion: false,

    // Inventory/scale
    weight_threshold: 0,

    // Auto delete
    auto_delete_zero_quantity: false,
    auto_delete_zero_days: 1,

    // METRC Integration
    metrc_enabled: true,
    metrc_user_key: "",
    metrc_vendor_key: "",
    metrc_facility: "",
    metrc_auto_push_sales: false,

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
      if (!raw) {
        const ck = readCookie("cpos_store_id");
        if (ck && typeof ck === "string") {
          let cid = ck
            .trim()
            .toLowerCase()
            .replace(/\s+/g, "")
            .replace(/[^a-z0-9_.-]/g, "");
          if (cid === "defaultstore") cid = "default";
          return cid || "default";
        }
        return "default";
      }
      const s = JSON.parse(raw);
      let id = s && s.id ? String(s.id) : "default";
      id = id
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "")
        .replace(/[^a-z0-9_.-]/g, "");
      if (id === "defaultstore") id = "default";
      try {
        writeCookie("cpos_store_id", id || "default");
      } catch (_) {}
      return id || "default";
    } catch (_) {
      return "default";
    }
  }
  function currentStoreName() {
    try {
      const raw = localStorage.getItem("pos_store");
      if (!raw) return "";
      const s = JSON.parse(raw);
      const n =
        s && (s.name || s.store_name) ? String(s.name || s.store_name) : "";
      return n.trim();
    } catch (_) {
      return "";
    }
  }
  function readCookie(name) {
    try {
      const m = document.cookie.match(
        new RegExp(
          "(?:^|; )" +
            name.replace(/([.$?*|{}()\[\]\\\/+^])/g, "\\$1") +
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
    const sname = currentStoreName();
    const cfg = { headers: { Accept: "application/json", "X-Store-ID": sid } };
    if (sname) cfg.headers["X-Store-Name"] = sname;
    if (params) {
      cfg.params = params;
      if (params.nocache) cfg.headers["Cache-Control"] = "no-cache";
    }
    const r = await (window.axios || axios).get(path, cfg);
    return r.data;
  }
  async function httpPost(path, body, params) {
    // Use backend API for settings to leverage Laravel cache/validation
    // Fallback for other endpoints (unchanged)
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
    const sname = currentStoreName();
    const cfg = {
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Store-ID": sid,
        ...(sname ? { "X-Store-Name": sname } : {}),
      },
    };
    try {
      const meta = document.querySelector('meta[name="csrf-token"]');
      const token = meta && meta.getAttribute("content");
      if (token) cfg.headers["X-CSRF-TOKEN"] = token;
    } catch (_) {}
    if (params) {
      cfg.params = params;
      if (params.nocache) cfg.headers["Cache-Control"] = "no-cache";
    }
    const r = await (window.axios || axios).post(path, body || {}, cfg);
    return r.data;
  }

  async function supaReq(path, init) {
    const base = (window.__SUPABASE_URL || "").replace(/\/$/, "");
    const key = window.__SUPABASE_ANON_KEY || "";
    if (!base || !key) throw new Error("supabase not configured");
    const url = `${base}/rest/v1/${path}`;
    const sid = currentStoreId();
    const headers = Object.assign(
      {
        apikey: key,
        Authorization: `Bearer ${key}`,
        Accept: "application/json",
        "X-Store-ID": sid,
      },
      (init && init.headers) || {},
    );
    return fetch(url, Object.assign({}, init || {}, { headers }));
  }

  function writeUiCachesFromSettings(merged) {
    try {
      const stateRate = Number(merged.sales_tax ?? 0) || 0;
      const recRate = Number(merged.cannabis_tax ?? 0);
      const tax = {
        recreationalRate:
          Number.isFinite(recRate) && recRate > 0 ? recRate : stateRate,
        includeInPrice: !!merged.tax_inclusive,
        localRate: Number(merged.excise_tax ?? 0) || 0,
        stateRate,
      };
      localStorage.setItem("cannabisPOS-taxSettings", JSON.stringify(tax));
    } catch (_) {}
    try {
      const sales = {
        minimumSale: Number(merged.minimum_price_amount ?? 0) || 0,
        enforceMinimumSale: !!merged.minimum_price_enabled,
        dailyLimit:
          Number(merged.__ui_daily_limit ?? merged.daily_limit ?? 0) || 0,
        requireCustomerInfo: !!merged.require_customer,
        autoDeleteZeroQuantity: !!merged.auto_delete_zero_quantity,
        autoDeleteZeroDays: Number(merged.auto_delete_zero_days ?? 1) || 1,
      };
      localStorage.setItem("cannabisPOS-salesSettings", JSON.stringify(sales));
    } catch (_) {}
    try {
      const print = {
        autoprint: !!merged.receipt_autoprint,
        printLabels: !!(merged.__ui_print_labels ?? merged.print_labels),
        receiptTemplate: String(
          merged.__ui_receipt_template ?? merged.receipt_template ?? "standard",
        ),
        paperSize: String(merged.receipt_paper_size ?? "80mm"),
        categoriesAutoprint: Array.isArray(merged.receipt_categories_autoprint)
          ? merged.receipt_categories_autoprint
          : [],
      };
      localStorage.setItem("cannabisPOS-printSettings", JSON.stringify(print));
    } catch (_) {}
  }

  async function getFromServer(sid, noCache = false) {
    // Read directly from Supabase pos_settings (no API hop)
    const tryIds = [sid];
    if (!tryIds.includes("defaultstore") && sid === "default")
      tryIds.push("defaultstore");
    if (!tryIds.includes("default")) tryIds.push("default");
    for (const id of tryIds) {
      try {
        const r = await supaReq(
          `pos_settings?id=eq.${encodeURIComponent(id)}&select=*`,
          {
            method: "GET",
            headers: noCache ? { "Cache-Control": "no-cache" } : {},
          },
        );
        if (!r.ok) continue;
        const arr = await r.json();
        const row = Array.isArray(arr) && arr[0] ? arr[0] : null;
        if (row) {
          let settings = {};
          if (row.settings && typeof row.settings === "object")
            settings = row.settings;
          else if (row.settings && typeof row.settings === "string") {
            try {
              settings = JSON.parse(row.settings);
            } catch (_) {
              settings = {};
            }
          }
          return { settings, updated_at: row.updated_at || null };
        }
      } catch (_) {}
    }
    return { settings: {}, updated_at: null };
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
      // 1) Try Laravel API first (authoritative merge of defaults + remote + local DB)
      try {
        const resp = await httpGet("/api/settings/pos", { nocache: true });
        const data =
          resp && typeof resp === "object" ? resp.settings || resp : {};
        const localPrev = this.loadLocal(sid) || {};
        const merged = { ...DEFAULTS, ...data };
        function isMasked(v) {
          return (
            typeof v === "string" &&
            (v.trim() === "••••••••" || /^[*•]+$/.test(v.trim()))
          );
        }
        ["metrc_user_key", "metrc_vendor_key"].forEach((k) => {
          if (isMasked(merged[k]))
            merged[k] = localPrev && localPrev[k] ? localPrev[k] : "";
        });
        const updatedAt =
          resp && (resp.settings_updated_at || resp.updated_at)
            ? resp.settings_updated_at || resp.updated_at
            : null;
        this.saveLocal(sid, merged);
        try {
          writeCookie("cpos_store_id", sid);
        } catch (_) {}
        try {
          const sid = currentStoreId();
          localStorage.setItem(
            `cannabisPOS-weightThreshold_${sid}`,
            String(merged.weight_threshold ?? 0),
          );
          localStorage.setItem(
            "cannabisPOS-weightThreshold",
            String(merged.weight_threshold ?? 0),
          );
        } catch (_) {}
        try {
          writeUiCachesFromSettings(merged);
        } catch (_) {}
        try {
          window.dispatchEvent(
            new CustomEvent("settings:updated", {
              detail: { settings: merged, storeId: sid },
            }),
          );
          try {
            window.dispatchEvent(new CustomEvent("settings-updated", { detail: merged }));
          } catch (_) {}
          try {
            writeCookie("cpos_store_id", sid);
          } catch (_) {}
          try {
            const arr = Array.isArray(merged.price_tiers)
              ? merged.price_tiers
              : Array.isArray(merged.priceTiers)
                ? merged.priceTiers
                : [];
            if (arr && arr.length) {
              localStorage.setItem(
                "cannabisPOS-priceTiers-backup",
                JSON.stringify(arr),
              );
            }
          } catch (_) {}
        } catch (_) {}
        return { success: true, settings: merged, updated_at: updatedAt };
      } catch (e1) {}

      // 2) Fallback to Supabase direct read
      let last = null;
      for (let i = 0; i < 3; i++) {
        try {
          const data = await getFromServer(sid, true);
          const settings =
            data && typeof data === "object" && (data.settings || data)
              ? data.settings || data
              : {};
          if (
            (!Number.isFinite(Number(settings.cannabis_tax)) ||
              Number(settings.cannabis_tax) === 0) &&
            Number.isFinite(Number(settings.sales_tax))
          ) {
            settings.cannabis_tax = Number(settings.sales_tax);
          }
          const merged = { ...DEFAULTS, ...settings };
          const updatedAt =
            data && (data.settings_updated_at || data.updated_at)
              ? data.settings_updated_at || data.updated_at
              : null;
          this.saveLocal(sid, merged);
          try {
            writeCookie("cpos_store_id", sid);
          } catch (_) {}
          try {
            const raw = localStorage.getItem("pos_store");
            const cur = raw ? JSON.parse(raw) : null;
            const displayName = merged.store_name || (cur && cur.name) || sid;
            if (!cur || cur.id !== sid || cur.name !== displayName) {
              localStorage.setItem(
                "pos_store",
                JSON.stringify({ id: sid, name: displayName }),
              );
              try {
                writeCookie("cpos_store_id", sid);
              } catch (_) {}
              try {
                if (typeof window.updateStoreHeaderLabel === "function")
                  window.updateStoreHeaderLabel();
              } catch (_) {}
            }
          } catch (_) {}
          try {
          const sid = currentStoreId();
          localStorage.setItem(
            `cannabisPOS-weightThreshold_${sid}`,
            String(merged.weight_threshold ?? 0),
          );
          localStorage.setItem(
            "cannabisPOS-weightThreshold",
            String(merged.weight_threshold ?? 0),
          );
        } catch (_) {}
          try {
            writeUiCachesFromSettings(merged);
          } catch (_) {}
          try {
            // Broadcast settings update (read-only hydration; no write-back)
            window.dispatchEvent(
              new CustomEvent("settings:updated", {
                detail: { settings: merged, storeId: sid },
              }),
            );
            try {
              window.dispatchEvent(new CustomEvent("settings-updated", { detail: merged }));
            } catch (_) {}
          try {
            window.dispatchEvent(new CustomEvent("settings-updated", { detail: merged }));
          } catch (_) {}
            // Persist price tiers backup
            try {
              const arr = Array.isArray(merged.price_tiers)
                ? merged.price_tiers
                : Array.isArray(merged.priceTiers)
                  ? merged.priceTiers
                  : [];
              if (arr && arr.length) {
                localStorage.setItem(
                  "cannabisPOS-priceTiers-backup",
                  JSON.stringify(arr),
                );
              }
            } catch (_) {}
          } catch (_) {}
          return { success: true, settings: merged, updated_at: updatedAt };
        } catch (e) {
          last = e;
          await new Promise((r) => setTimeout(r, 200 * (i + 1)));
        }
      }

      // 3) Fallback to local defaults
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
      // Prefetch current from API to avoid overwriting other fields
      try {
        const resp = await httpGet("/api/settings/pos", { nocache: true });
        const cur =
          resp && (resp.settings || resp) ? resp.settings || resp : {};
        if (cur && typeof cur === "object") base = { ...base, ...cur };
      } catch (_) {}
      // Preserve existing METRC keys if patch contains masked values
      function isMasked(v) {
        return (
          typeof v === "string" &&
          (v.trim() === "••••••••" || /^[*•]+$/.test(v.trim()))
        );
      }
      // Preserve explicit clears: do not strip null/empty strings from patch
      const _pin = { ...(patch || {}) };
      const patched = { ..._pin };
      if (isMasked(patched.metrc_user_key))
        patched.metrc_user_key = base.metrc_user_key || "";
      if (isMasked(patched.metrc_vendor_key))
        patched.metrc_vendor_key = base.metrc_vendor_key || "";
      const merged = { ...DEFAULTS, ...base, ...patched };
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
          // Read-after-write verification against Laravel API (bypass cache)
          try {
            const verifyResp = await httpGet("/api/settings/pos", {
              nocache: true,
            });
            const vs =
              verifyResp && (verifyResp.settings || verifyResp)
                ? verifyResp.settings || verifyResp
                : {};
            if (
              (!Number.isFinite(Number(vs.cannabis_tax)) ||
                Number(vs.cannabis_tax) === 0) &&
              Number.isFinite(Number(vs.sales_tax))
            ) {
              vs.cannabis_tax = Number(vs.sales_tax);
            }
            if (vs && Object.keys(vs).length) m = { ...DEFAULTS, ...vs };
          } catch (_) {}
          this.saveLocal(sid, m);
          try {
            writeCookie("cpos_store_id", sid);
          } catch (_) {}
          try {
            const raw = localStorage.getItem("pos_store");
            const cur = raw ? JSON.parse(raw) : null;
            const displayName = m.store_name || (cur && cur.name) || sid;
            if (!cur || cur.id !== sid || cur.name !== displayName) {
              localStorage.setItem(
                "pos_store",
                JSON.stringify({ id: sid, name: displayName }),
              );
              try {
                writeCookie("cpos_store_id", sid);
              } catch (_) {}
              try {
                if (typeof window.updateStoreHeaderLabel === "function")
                  window.updateStoreHeaderLabel();
              } catch (_) {}
            }
          } catch (_) {}
          try {
            localStorage.setItem(
              "cannabisPOS-weightThreshold",
              String(m.weight_threshold ?? 0),
            );
          } catch (_) {}
          try {
            writeUiCachesFromSettings(m);
          } catch (_) {}
          try {
            window.dispatchEvent(
              new CustomEvent("settings:updated", {
                detail: { settings: m, storeId: sid },
              }),
            );
            try {
              window.dispatchEvent(new CustomEvent("settings-updated", { detail: m }));
            } catch (_) {}
            try {
              writeCookie("cpos_store_id", sid);
            } catch (_) {}
            try {
              const arr = Array.isArray(m.price_tiers)
                ? m.price_tiers
                : Array.isArray(m.priceTiers)
                  ? m.priceTiers
                  : [];
              if (arr && arr.length) {
                localStorage.setItem(
                  "cannabisPOS-priceTiers-backup",
                  JSON.stringify(arr),
                );
              }
            } catch (_) {}
          } catch (_) {}
          return { success: true, settings: m };
        } catch (e) {
          last = e;
          await new Promise((r) => setTimeout(r, 200 * (i + 1)));
        }
      }
      // Backend failed: last-resort direct Supabase upsert to avoid data loss
      try {
        const sid = currentStoreId();
        const now = new Date().toISOString();
        const r = await supaReq(`pos_settings?on_conflict=id`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify([
            { id: sid, settings: merged, updated_at: now },
          ]),
        });
        if (r.ok) {
          // Verify read-after-write
          try {
            const ver = await supaReq(
              `pos_settings?id=eq.${encodeURIComponent(sid)}&select=*`,
              { method: "GET" },
            );
            if (ver.ok) {
              const arr = await ver.json();
              const row = Array.isArray(arr) && arr[0] ? arr[0] : null;
              if (row && row.settings && typeof row.settings === "object") {
                const m = { ...DEFAULTS, ...row.settings };
                this.saveLocal(sid, m);
                try {
                  writeCookie("cpos_store_id", sid);
                } catch (_) {}
                try {
                  const raw = localStorage.getItem("pos_store");
                  const cur = raw ? JSON.parse(raw) : null;
                  const displayName = m.store_name || (cur && cur.name) || sid;
                  if (!cur || cur.id !== sid || cur.name !== displayName) {
                    localStorage.setItem(
                      "pos_store",
                      JSON.stringify({ id: sid, name: displayName }),
                    );
                    try {
                      writeCookie("cpos_store_id", sid);
                    } catch (_) {}
                    try {
                      if (typeof window.updateStoreHeaderLabel === "function")
                        window.updateStoreHeaderLabel();
                    } catch (_) {}
                  }
                } catch (_) {}
                try {
                  localStorage.setItem(
                    "cannabisPOS-weightThreshold",
                    String(m.weight_threshold ?? 0),
                  );
                } catch (_) {}
                try {
                  writeUiCachesFromSettings(m);
                } catch (_) {}
                try {
                  window.dispatchEvent(
              new CustomEvent("settings:updated", {
                detail: { settings: m, storeId: sid },
              }),
            );
            try {
              window.dispatchEvent(new CustomEvent("settings-updated", { detail: m }));
            } catch (_) {}
                } catch (_) {}
                return { success: true, settings: m };
              }
            }
          } catch (_) {}
        }
      } catch (_) {}
      return {
        success: false,
        settings: merged,
        error: last || new Error("settings save failed"),
      };
    },
  };

  SettingsClient.currentStoreName = currentStoreName;
  window.SettingsClient = SettingsClient;
})();
