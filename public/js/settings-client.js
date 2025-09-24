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
          const cid = canonicalizeId(ck, "");
          return cid || "default";
        }
        return "default";
      }
      const s = JSON.parse(raw);
      const id = canonicalizeId(
        s && s.id ? s.id : "",
        s && s.name ? s.name : "",
      );
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

  const STORE_ID_ALIAS = {
    "THC Barbur": "Today's Herbal Choice Barbur",
    "THC Stayton": "Today's Herbal Choice Stayton",
    "THC Molalla": "Today's Herbal Choice Molalla",
    "THC Milwaukie": "Today's Herbal Choice Milwaukie",
    "THC Forest Grove": "Today's Herbal Choice Forest Grove",
    "THC Tillamook": "Today's Herbal Choice Tillamook",
    "THC Rainier": "Today's Herbal Choice Rainier",
    thcbarbur: "Today's Herbal Choice Barbur",
    thcstayton: "Today's Herbal Choice Stayton",
    thcmolalla: "Today's Herbal Choice Molalla",
    thcmilwaukie: "Today's Herbal Choice Milwaukie",
    thcforestgrove: "Today's Herbal Choice Forest Grove",
    thctillamook: "Today's Herbal Choice Tillamook",
    thcrainier: "Today's Herbal Choice Rainier",
    // Canonicalize common full-name slugs (with/without smart quotes)
    todaysherbalchoicebarbur: "Today's Herbal Choice Barbur",
    todaysherbalchoicestayton: "Today's Herbal Choice Stayton",
    todaysherbalchoicemolalla: "Today's Herbal Choice Molalla",
    todaysherbalchoicemilwaukie: "Today's Herbal Choice Milwaukie",
    todaysherbalchoiceforestgrove: "Today's Herbal Choice Forest Grove",
    todaysherbalchoicetillamook: "Today's Herbal Choice Tillamook",
    todaysherbalchoicerainier: "Today's Herbal Choice Rainier",
  };
  function canonicalizeId(rawId, rawName) {
    try {
      const norm = (s)=> String(s==null?"":s)
        .replace(/[’‘`]/g, "'")
        .replace(/\u2019/g, "'")
        .trim();
      const idRaw = norm(rawId);
      const nameRaw = norm(rawName);
      if (idRaw && idRaw.includes("Today's Herbal Choice")) return idRaw;
      if (nameRaw && nameRaw.includes("Today's Herbal Choice")) return nameRaw;
      if (STORE_ID_ALIAS[nameRaw]) return STORE_ID_ALIAS[nameRaw];
      const slug = (idRaw || nameRaw)
        .toLowerCase()
        .replace(/[’‘`]/g, "")
        .replace(/\s+/g, "")
        .replace(/[^a-z0-9_.-]/g, "");
      if (STORE_ID_ALIAS[slug]) return STORE_ID_ALIAS[slug];
      if (/^todaysherbalchoice[a-z]/.test(slug)) {
        // Map "todaysherbalchoice<branch>" => canonical title case string
        const branch = slug.replace(/^todaysherbalchoice/, "");
        const titled = branch.replace(/(^|\b)([a-z])/g, (m,_b,c)=>c.toUpperCase());
        return "Today's Herbal Choice " + titled.replace(/([a-z])([A-Z])/g, "$1 $2");
      }
      if (idRaw) return idRaw;
      return nameRaw || "default";
    } catch (_) {
      return (rawId && String(rawId)) || "default";
    }
  }

  async function httpGet(path, params) {
    // Prefer posAuth if present; it automatically adds headers
    if (window.posAuth) {
      const res = await window.posAuth.apiRequest(
        "get",
        path.replace(/^\/api/, ""),
        params || {},
      );
      if (res && res.success === false) {
        const err = new Error(res.message || "API request failed");
        err.response = { data: res, status: res.status };
        throw err;
      }
      if (res && res.data) return res.data;
    }
    const sid = currentStoreId();
    const sname = currentStoreName();
    const headers = { Accept: "application/json", "X-Store-ID": sid };
    try {
      const cname = canonicalizeId('', sname || sid);
      headers["X-Store-Name"] = cname || (sname || '');
    } catch(_) { if (sname) headers["X-Store-Name"] = sname; }
    const ax =
      typeof window !== "undefined" && window.axios
        ? window.axios
        : typeof axios !== "undefined"
          ? axios
          : null;
    if (ax) {
      const cfg = { headers };
      if (params) {
        cfg.params = params;
        if (params.nocache) cfg.headers["Cache-Control"] = "no-cache";
      }
      const r = await ax.get(path, cfg);
      return r.data;
    }
    const url = new URL(path, location.origin);
    if (params)
      Object.entries(params).forEach(([k, v]) =>
        url.searchParams.set(k, String(v)),
      );
    const res = await fetch(url.toString(), { headers });
    if (!res.ok) throw new Error(`GET ${path} failed ${res.status}`);
    return res.json();
  }
  async function httpPost(path, body, params) {
    // Use backend API for settings to leverage Laravel cache/validation
    if (window.posAuth) {
      const res = await window.posAuth.apiRequest(
        "post",
        path.replace(/^\/api/, ""),
        body || {},
      );
      if (res && res.success === false) {
        const err = new Error(res.message || "API request failed");
        err.response = { data: res, status: res.status };
        throw err;
      }
      if (res && res.data) return res.data;
    }
    const sid = currentStoreId();
    const sname = currentStoreName();
    const headers = {
      "Content-Type": "application/json",
      Accept: "application/json",
      "X-Store-ID": sid,
    };
    try {
      const cname = canonicalizeId('', sname || sid);
      headers["X-Store-Name"] = cname || (sname || '');
    } catch(_) { if (sname) headers["X-Store-Name"] = sname; }
    try {
      const meta = document.querySelector('meta[name="csrf-token"]');
      const token = meta && meta.getAttribute("content");
      if (token) headers["X-CSRF-TOKEN"] = token;
    } catch (_) {}
    try {
      const ver = (body && typeof body === 'object') ? body.settings_version : null;
      if (typeof ver === 'number' && Number.isFinite(ver)) headers['X-Settings-Version'] = String(ver);
    } catch(_){}
    const ax =
      typeof window !== "undefined" && window.axios
        ? window.axios
        : typeof axios !== "undefined"
          ? axios
          : null;
    if (ax) {
      const cfg = { headers };
      if (params) {
        cfg.params = params;
        if (params.nocache) cfg.headers["Cache-Control"] = "no-cache";
      }
      const r = await ax.post(path, body || {}, cfg);
      return r.data;
    }
    const url = new URL(path, location.origin);
    if (params)
      Object.entries(params).forEach(([k, v]) =>
        url.searchParams.set(k, String(v)),
      );
    const res = await fetch(url.toString(), {
      method: "POST",
      headers,
      body: JSON.stringify(body || {}),
    });
    if (!res.ok) throw new Error(`POST ${path} failed ${res.status}`);
    return res.json();
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
        Prefer: "resolution=merge-duplicates,return=representation",
        "X-Store-ID": sid,
      },
      (init && init.headers) || {},
    );
    const controller = new AbortController();
    const to = setTimeout(() => controller.abort(), 10000);
    try {
      return await fetch(
        url,
        Object.assign({}, init || {}, { headers, signal: controller.signal }),
      );
    } finally {
      clearTimeout(to);
    }
  }

  async function supaReqRetry(path, init, attempts = 3) {
    let lastErr = null;
    for (let i = 0; i < attempts; i++) {
      try {
        const res = await supaReq(path, init);
        if (res && res.ok) return res;
        lastErr = new Error(
          `supabase ${init && init.method ? init.method : "GET"} failed (${res?.status || "n/a"})`,
        );
      } catch (e) {
        lastErr = e;
      }
      await new Promise((r) => setTimeout(r, 150 * (i + 1)));
    }
    if (lastErr) throw lastErr;
    return supaReq(path, init);
  }

  function saveSnapshot(sid, settings){
    try{
      const key = `cpos_settings_snapshots_${sid}`;
      const list = JSON.parse(localStorage.getItem(key) || '[]');
      const entry = { ts: Date.now(), settings };
      const next = [entry].concat(Array.isArray(list)?list:[]).slice(0,5);
      localStorage.setItem(key, JSON.stringify(next));
    }catch(_){ }
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
      try {
        const sid = currentStoreId();
        localStorage.setItem(
          `cannabisPOS-taxSettings_${sid}`,
          JSON.stringify(tax),
        );
      } catch (_) {}
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
      try {
        const sid = currentStoreId();
        localStorage.setItem(
          `cannabisPOS-salesSettings_${sid}`,
          JSON.stringify(sales),
        );
      } catch (_) {}
      localStorage.setItem("cannabisPOS-salesSettings", JSON.stringify(sales));
    } catch (_) {}
    try {
      const print = {
        autoprint: !!merged.receipt_autoprint,
        printLabels: !!(merged.__ui_print_labels ?? merged.print_labels),
        receiptTemplate: (function (v) {
          v = String(v || "standard");
          return ["standard", "detailed", "minimal"].includes(v)
            ? v
            : "standard";
        })(merged.__ui_receipt_template ?? merged.receipt_template),
        paperSize: String(merged.receipt_paper_size ?? "80mm"),
        categoriesAutoprint: Array.isArray(merged.receipt_categories_autoprint)
          ? merged.receipt_categories_autoprint
          : [],
      };
      try {
        const sid = currentStoreId();
        localStorage.setItem(
          `cannabisPOS-printSettings_${sid}`,
          JSON.stringify(print),
        );
      } catch (_) {}
      localStorage.setItem("cannabisPOS-printSettings", JSON.stringify(print));
    } catch (_) {}
  }

  function pick(obj, keys) {
    const out = {};
    keys.forEach((k) => {
      if (Object.prototype.hasOwnProperty.call(obj, k)) out[k] = obj[k];
    });
    return out;
  }
  const SEC = {
    Store_Information: [
      "store_name",
      "license_number",
      "store_address",
      "store_phone",
      "store_email",
      "business_hours",
    ],
    Tax_Configuration: [
      "sales_tax",
      "excise_tax",
      "cannabis_tax",
      "tax_inclusive",
    ],
    "Sales_&_Transaction_Settings": [
      "require_customer",
      "age_verification",
      "limit_enforcement",
      "accept_cash",
      "accept_debit",
      "accept_check",
      "round_to_nearest",
      "minimum_price_enabled",
      "minimum_price_amount",
      "minimum_price_categories",
      "inventory_view_mode",
      "expandable_cart",
      "weight_threshold",
    ],
    Printing_Preferences: [
      "receipt_autoprint",
      "receipt_categories_autoprint",
      "receipt_show_tax_breakdown",
      "receipt_show_metrc",
      "receipt_show_loyalty",
      "receipt_show_qr_code",
      "default_receipt_printer",
      "receipt_paper_size",
      "exit_label_categories",
      "receipt_template",
      "print_labels",
      "receipt_footer",
    ],
    Metrc_Integration: [
      "metrc_enabled",
      "metrc_user_key",
      "metrc_vendor_key",
      "metrc_facility",
      "metrc_auto_push_sales",
    ],
    "Auto_Delete_Zero-Quantity_Products": [
      "auto_delete_zero_quantity",
      "auto_delete_zero_days",
    ],
  };

  function extractSections(src) {
    const s = src || {};
    return {
      Store_Information: pick(s, SEC["Store_Information"]),
      Tax_Configuration: pick(s, SEC["Tax_Configuration"]),
      "Sales_&_Transaction_Settings": pick(
        s,
        SEC["Sales_&_Transaction_Settings"],
      ),
      Printing_Preferences: pick(s, SEC["Printing_Preferences"]),
      Metrc_Integration: pick(s, SEC["Metrc_Integration"]),
      "Auto_Delete_Zero-Quantity_Products": pick(
        s,
        SEC["Auto_Delete_Zero-Quantity_Products"],
      ),
      store_name: s.store_name || null,
    };
  }
  function deepNormalize(value) {
    const norm = (v) => {
      if (v == null) return v;
      if (Array.isArray(v)) {
        const arr = v.map(norm);
        // Sort arrays of primitives; for objects sort by JSON string
        if (arr.every((x) => x == null || typeof x !== "object")) {
          return arr.slice().sort((a, b) => {
            const sa = typeof a === "string" ? a : String(a);
            const sb = typeof b === "string" ? b : String(b);
            return sa.localeCompare(sb);
          });
        }
        return arr
          .slice()
          .sort((a, b) => JSON.stringify(a).localeCompare(JSON.stringify(b)));
      }
      if (typeof v === "object") {
        const keys = Object.keys(v).sort();
        const out = {};
        for (const k of keys) {
          out[k] = norm(v[k]);
        }
        return out;
      }
      return v;
    };
    return norm(value);
  }
  async function backgroundReconcile(expected, sid) {
    try {
      const storeId = sid || currentStoreId();
      const want = deepNormalize(extractSections(expected));
      for (let i = 0; i < 8; i++) {
        await new Promise((r) => setTimeout(r, 250));
        let cur = null;
        try {
          const g = await httpGet("/api/settings/pos", { nocache: true });
          const s = g && (g.settings || g) ? g.settings || g : {};
          cur = deepNormalize(extractSections(s));
        } catch (_) {
          cur = null;
        }
        if (cur && JSON.stringify(cur) === JSON.stringify(want)) return true;
        try {
          const nowIso = new Date().toISOString();
          const row = Object.assign({ id: storeId, updated_at: nowIso }, want);
          await supaReqRetry(`pos_settings?on_conflict=id`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify([row]),
          });
        } catch (_) {
          /* retry loop */
        }
      }
    } catch (_) {
      /* swallow */
    }
    return false;
  }

  function outboxKey(sid) {
    return `cpos_settings_outbox_${sid}`;
  }
  async function flushSettingsOutbox(sid) {
    try {
      const raw = localStorage.getItem(outboxKey(sid));
      if (!raw) return false;
      const payload = JSON.parse(raw);
      if (!payload || typeof payload !== "object") {
        localStorage.removeItem(outboxKey(sid));
        return false;
      }
      const nowIso = new Date().toISOString();
      const row = {
        id: sid,
        store_name: payload.store_name || null,
        updated_at: nowIso,
        Store_Information: pick(payload, SEC["Store_Information"]),
        Tax_Configuration: pick(payload, SEC["Tax_Configuration"]),
        "Sales_&_Transaction_Settings": pick(
          payload,
          SEC["Sales_&_Transaction_Settings"],
        ),
        Printing_Preferences: pick(payload, SEC["Printing_Preferences"]),
        Metrc_Integration: pick(payload, SEC["Metrc_Integration"]),
        "Auto_Delete_Zero-Quantity_Products": pick(
          payload,
          SEC["Auto_Delete_Zero-Quantity_Products"],
        ),
      };
      const r = await supaReqRetry(`pos_settings?on_conflict=id`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify([row]),
      });
      if (r && r.ok) {
        localStorage.removeItem(outboxKey(sid));
        return true;
      }
    } catch (_) {}
    return false;
  }
  try {
    setInterval(() => {
      try {
        const sid = currentStoreId();
        flushSettingsOutbox(sid);
      } catch (_) {}
    }, 15000);
  } catch (_) {}

  async function getFromServer(sid, noCache = false) {
    // Read directly from Supabase pos_settings (no API hop)
    const tryIds = [sid];
    if (!tryIds.includes("defaultstore") && sid === "default")
      tryIds.push("defaultstore");
    if (!tryIds.includes("default")) tryIds.push("default");
    for (const id of tryIds) {
      try {
        const sname = currentStoreName();
        const filter =
          sname && sname.trim()
            ? `pos_settings?or=(store_name.eq.${encodeURIComponent(sname.trim())},id.eq.${encodeURIComponent(id)})&select=*`
            : `pos_settings?id=eq.${encodeURIComponent(id)}&select=*`;
        const r = await supaReqRetry(filter, {
          method: "GET",
          headers: noCache ? { "Cache-Control": "no-cache" } : {},
        });
        if (!r.ok) continue;
        const arr = await r.json();
        const row = Array.isArray(arr) && arr[0] ? arr[0] : null;
        if (row) {
          // Compose from dedicated columns if present; fallback to legacy settings
          let composed = {};
          try {
            if (
              row["Store_Information"] &&
              typeof row["Store_Information"] === "object"
            )
              composed = Object.assign(composed, row["Store_Information"]);
          } catch (_) {}
          try {
            if (
              row["Tax_Configuration"] &&
              typeof row["Tax_Configuration"] === "object"
            )
              composed = Object.assign(composed, row["Tax_Configuration"]);
          } catch (_) {}
          try {
            if (
              row["Sales_&_Transaction_Settings"] &&
              typeof row["Sales_&_Transaction_Settings"] === "object"
            )
              composed = Object.assign(
                composed,
                row["Sales_&_Transaction_Settings"],
              );
          } catch (_) {}
          try {
            if (
              row["Printing_Preferences"] &&
              typeof row["Printing_Preferences"] === "object"
            )
              composed = Object.assign(composed, row["Printing_Preferences"]);
          } catch (_) {}
          try {
            if (
              row["Metrc_Integration"] &&
              typeof row["Metrc_Integration"] === "object"
            )
              composed = Object.assign(composed, row["Metrc_Integration"]);
          } catch (_) {}
          try {
            if (
              row["Auto_Delete_Zero-Quantity_Products"] &&
              typeof row["Auto_Delete_Zero-Quantity_Products"] === "object"
            )
              composed = Object.assign(
                composed,
                row["Auto_Delete_Zero-Quantity_Products"],
              );
          } catch (_) {}
          if (row.store_name) composed.store_name = row.store_name;
          // Legacy support
          if (
            (!composed || Object.keys(composed).length === 0) &&
            row.settings
          ) {
            if (typeof row.settings === "object") composed = row.settings;
            else {
              try {
                composed = JSON.parse(row.settings);
              } catch (_) {
                composed = {};
              }
            }
          }
          return { settings: composed, updated_at: row.updated_at || null };
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
      const scrub = (src) => {
        try {
          const s = { ...(src || {}) };
          if (s.metrc_user_key !== undefined) delete s.metrc_user_key;
          if (s.metrc_vendor_key !== undefined) delete s.metrc_vendor_key;
          return s;
        } catch (_) {
          return src;
        }
      };
      const clean = scrub(settings);
      try {
        const json = JSON.stringify(clean);
        localStorage.setItem(LS_KEY(sid), json);
      } catch (_) {}
      try {
        const json = JSON.stringify(clean);
        if (json && json.length <= 3500) {
          writeCookie(CK_KEY(sid), json);
        } else {
          const compact = JSON.stringify({
            store_name: clean.store_name || "",
            sales_tax: clean.sales_tax ?? 0,
            excise_tax: clean.excise_tax ?? 0,
            cannabis_tax: clean.cannabis_tax ?? clean.sales_tax ?? 0,
            receipt_autoprint: !!(
              clean.receipt_autoprint ?? clean.auto_print_receipt
            ),
          });
          writeCookie(CK_KEY(sid), compact);
        }
      } catch (_) {}
    },

    async get(force = false) {
      const sid = currentStoreId();
      try {
        await flushSettingsOutbox(sid);
      } catch (_) {}
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
        const hasServer =
          data && typeof data === "object" && Object.keys(data).length > 0;
        const merged = hasServer
          ? { ...DEFAULTS, ...data }
          : { ...DEFAULTS, ...localPrev };
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
          // Write compatibility keys used by other pages
          const compat = Object.assign({}, merged, { lastUpdated: Date.now() });
          localStorage.setItem(
            `cannabisPOS-storeSettings_${sid}`,
            JSON.stringify(compat),
          );
          localStorage.setItem(
            "cannabisPOS-storeSettings",
            JSON.stringify(compat),
          );
        } catch (_) {}
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
            window.dispatchEvent(
              new CustomEvent("settings-updated", {
                detail: { settings: merged, storeId: sid },
              }),
            );
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
              try {
                const sid2 = currentStoreId();
                localStorage.setItem(
                  `cannabisPOS-priceTiers-backup_${sid2}`,
                  JSON.stringify(arr),
                );
              } catch (_) {}
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
          const localPrev = this.loadLocal(sid) || {};
          const hasServer =
            settings &&
            typeof settings === "object" &&
            Object.keys(settings).length > 0;
          if (
            (!Number.isFinite(Number(settings.cannabis_tax)) ||
              Number(settings.cannabis_tax) === 0) &&
            Number.isFinite(Number(settings.sales_tax))
          ) {
            settings.cannabis_tax = Number(settings.sales_tax);
          }
          const merged = hasServer
            ? { ...DEFAULTS, ...settings }
            : { ...DEFAULTS, ...localPrev };
          const updatedAt =
            data && (data.settings_updated_at || data.updated_at)
              ? data.settings_updated_at || data.updated_at
              : null;
          this.saveLocal(sid, merged);
          try {
            const compat = Object.assign({}, merged, {
              lastUpdated: Date.now(),
            });
            localStorage.setItem(
              `cannabisPOS-storeSettings_${sid}`,
              JSON.stringify(compat),
            );
            localStorage.setItem(
              "cannabisPOS-storeSettings",
              JSON.stringify(compat),
            );
          } catch (_) {}
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
              window.dispatchEvent(
                new CustomEvent("settings-updated", {
                  detail: { settings: merged, storeId: sid },
                }),
              );
            } catch (_) {}
            // Persist price tiers backup (namespaced + legacy)
            try {
              const arr = Array.isArray(merged.price_tiers)
                ? merged.price_tiers
                : Array.isArray(merged.priceTiers)
                  ? merged.priceTiers
                  : [];
              if (arr && arr.length) {
                try {
                  const sid2 = currentStoreId();
                  localStorage.setItem(
                    `cannabisPOS-priceTiers-backup_${sid2}`,
                    JSON.stringify(arr),
                  );
                } catch (_) {}
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
      // Normalize arrays possibly sent as JSON strings
      [
        "exit_label_categories",
        "receipt_categories_autoprint",
        "minimum_price_categories",
        "business_hours",
      ].forEach((k) => {
        const v = patched[k];
        if (typeof v === "string") {
          try {
            const p = JSON.parse(v);
            if (Array.isArray(p)) patched[k] = p;
          } catch (_) {}
        }
      });
      // Clamp numerics to sane ranges
      const clamp = (n, lo, hi) => {
        const x = Number(n);
        return Number.isFinite(x) ? Math.min(hi, Math.max(lo, x)) : n;
      };
      if (patched.sales_tax != null)
        patched.sales_tax = clamp(patched.sales_tax, 0, 100);
      if (patched.excise_tax != null)
        patched.excise_tax = clamp(patched.excise_tax, 0, 100);
      if (patched.cannabis_tax != null)
        patched.cannabis_tax = clamp(patched.cannabis_tax, 0, 100);
      if (patched.minimum_price_amount != null)
        patched.minimum_price_amount = Math.max(
          0,
          Number(patched.minimum_price_amount) || 0,
        );
      if (patched.weight_threshold != null)
        patched.weight_threshold = Math.max(
          0,
          Number(patched.weight_threshold) || 0,
        );
      if (patched.auto_delete_zero_days != null)
        patched.auto_delete_zero_days = clamp(
          patched.auto_delete_zero_days,
          1,
          30,
        );
      // Validate receipt_template if present
      if (patched.receipt_template != null) {
        const t = String(patched.receipt_template || "standard");
        if (!["standard", "detailed", "minimal"].includes(t))
          patched.receipt_template = "standard";
      }
      const merged = { ...DEFAULTS, ...base, ...patched };
      this.saveLocal(sid, merged);
      try {
        const compat = Object.assign({}, merged, { lastUpdated: Date.now() });
        localStorage.setItem(
          `cannabisPOS-storeSettings_${sid}`,
          JSON.stringify(compat),
        );
        localStorage.setItem(
          "cannabisPOS-storeSettings",
          JSON.stringify(compat),
        );
      } catch (_) {}
      // First, try direct Supabase upsert (authoritative). If it succeeds, update caches and return success immediately.
      let last = null;
      try {
        const sidNow = currentStoreId();
        const nowIso = new Date().toISOString();
        const payload = [
          {
            id: sidNow,
            store_name: merged.store_name ?? "",
            updated_at: nowIso,
            Store_Information: pick(merged, SEC["Store_Information"]),
            Tax_Configuration: pick(merged, SEC["Tax_Configuration"]),
            "Sales_&_Transaction_Settings": pick(
              merged,
              SEC["Sales_&_Transaction_Settings"],
            ),
            Printing_Preferences: pick(merged, SEC["Printing_Preferences"]),
            Metrc_Integration: pick(merged, SEC["Metrc_Integration"]),
            "Auto_Delete_Zero-Quantity_Products": pick(
              merged,
              SEC["Auto_Delete_Zero-Quantity_Products"],
            ),
          },
        ];
        let r0 = await supaReqRetry(`pos_settings?on_conflict=id`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });
        if (!r0 || !r0.ok) {
          try {
            const txt = r0 ? await r0.text() : "";
            last = new Error(
              `supabase upsert failed (${r0?.status || "n/a"}): ${txt}`,
            );
          } catch (eTxt) {
            last = eTxt;
          }
          // Fallback: PATCH existing row by id (avoids on_conflict semantics)
          try {
            const rPatch = await supaReqRetry(
              `pos_settings?id=eq.${encodeURIComponent(sidNow)}`,
              {
                method: "PATCH",
                headers: {
                  "Content-Type": "application/json",
                  Prefer: "resolution=merge-duplicates,return=representation",
                },
                body: JSON.stringify(payload[0]),
              },
            );
            if (rPatch && rPatch.ok) {
              r0 = rPatch;
            }
          } catch (ePatch) {
            try {
              await fetch("/api/activity", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                  action: "settings-save-patch-failed",
                  storeId: sidNow,
                  message: String((ePatch && ePatch.message) || "patch failed"),
                }),
              });
            } catch (_) {}
          }
        }
        if (r0 && r0.ok) {
          try {
            const ver0 = await supaReqRetry(
              `pos_settings?id=eq.${encodeURIComponent(sidNow)}&select=*`,
              { method: "GET" },
            );
            if (!ver0 || !ver0.ok) {
              try {
                const txt = ver0 ? await ver0.text() : "";
                last = new Error(
                  `supabase verify failed (${ver0?.status || "n/a"}): ${txt}`,
                );
              } catch (eTxt) {
                last = eTxt;
              }
            }
            if (ver0 && ver0.ok) {
              const arr0 = await ver0.json();
              const row0 = Array.isArray(arr0) && arr0[0] ? arr0[0] : null;
              let composed0 = {};
              try {
                if (row0["Store_Information"])
                  composed0 = Object.assign(
                    composed0,
                    row0["Store_Information"],
                  );
              } catch (_) {}
              try {
                if (row0["Tax_Configuration"])
                  composed0 = Object.assign(
                    composed0,
                    row0["Tax_Configuration"],
                  );
              } catch (_) {}
              try {
                if (row0["Sales_&_Transaction_Settings"])
                  composed0 = Object.assign(
                    composed0,
                    row0["Sales_&_Transaction_Settings"],
                  );
              } catch (_) {}
              try {
                if (row0["Printing_Preferences"])
                  composed0 = Object.assign(
                    composed0,
                    row0["Printing_Preferences"],
                  );
              } catch (_) {}
              try {
                if (row0["Metrc_Integration"])
                  composed0 = Object.assign(
                    composed0,
                    row0["Metrc_Integration"],
                  );
              } catch (_) {}
              try {
                if (row0["Auto_Delete_Zero-Quantity_Products"])
                  composed0 = Object.assign(
                    composed0,
                    row0["Auto_Delete_Zero-Quantity_Products"],
                  );
              } catch (_) {}
              if (row0.store_name) composed0.store_name = row0.store_name;
              if (!composed0 || Object.keys(composed0).length === 0)
                composed0 = merged;
              const m0 = { ...DEFAULTS, ...composed0 };
              this.saveLocal(sidNow, m0);
              try {
                const compat0 = Object.assign({}, m0, {
                  lastUpdated: Date.now(),
                });
                localStorage.setItem(
                  `cannabisPOS-storeSettings_${sidNow}`,
                  JSON.stringify(compat0),
                );
                localStorage.setItem(
                  "cannabisPOS-storeSettings",
                  JSON.stringify(compat0),
                );
              } catch (_) {}
              try {
                writeCookie("cpos_store_id", sidNow);
              } catch (_) {}
              try {
                localStorage.setItem(
                  "cannabisPOS-weightThreshold",
                  String(m0.weight_threshold ?? 0),
                );
              } catch (_) {}
              try {
                writeUiCachesFromSettings(m0);
              } catch (_) {}
              try {
                window.dispatchEvent(
                  new CustomEvent("settings:updated", {
                    detail: { settings: m0, storeId: sidNow },
                  }),
                );
                try {
                  window.dispatchEvent(
                    new CustomEvent("settings-updated", { detail: m0 }),
                  );
                } catch (_) {}
              } catch (_) {}
              return { success: true, settings: m0 };
            }
          } catch (e) {
            last = e;
          }
        }
        // Upsert succeeded but verification failed; treat as success with optimistic caches
        if (r0 && r0.ok) {
          try {
            this.saveLocal(sidNow, merged);
          } catch (_) {}
          try { saveSnapshot(sidNow, merged); } catch(_){ }
          try {
            const compat = Object.assign({}, merged, {
              lastUpdated: Date.now(),
            });
            localStorage.setItem(
              `cannabisPOS-storeSettings_${sidNow}`,
              JSON.stringify(compat),
            );
            localStorage.setItem(
              "cannabisPOS-storeSettings",
              JSON.stringify(compat),
            );
          } catch (_) {}
          try {
            writeCookie("cpos_store_id", sidNow);
          } catch (_) {}
          try {
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
                detail: { settings: merged, storeId: sidNow },
              }),
            );
            try {
              window.dispatchEvent(
                new CustomEvent("settings-updated", { detail: merged }),
              );
            } catch (_) {}
          } catch (_) {}
          try {
            await fetch("/api/activity", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                action: "settings-save-verified-soft-fail",
                storeId: sidNow,
              }),
            });
          } catch (_) {}
          try {
            backgroundReconcile(merged, sidNow);
          } catch (_) {}
          return { success: true, settings: merged };
        }
      } catch (e) {
        last = e;
      }
      // Fire and retry server save
      for (let i = 0; i < 3; i++) {
        try {
          const data = await httpPost("/api/settings/pos", merged);
          // Sync local store context if server resolved a different canonical id/name
          try{
            const sidSrv = (function(){ try{ return String((data && data.store_id) || (data && data.settings && data.settings.id) || ""); }catch(_){ return ""; } })();
            const snameSrv = (function(){ try{ return String((data && data.store_name) || (data && data.settings && data.settings.store_name) || ""); }catch(_){ return ""; } })();
            const cid = canonicalizeId(sidSrv || currentStoreId(), snameSrv || currentStoreName());
            if (cid && cid !== currentStoreId()){
              try { localStorage.setItem('pos_store', JSON.stringify({ id: cid, name: snameSrv || cid })); writeCookie('cpos_store_id', cid); window.dispatchEvent(new Event('storage')); } catch(_){ }
            }
          }catch(_){ }
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
          try { saveSnapshot(sid, m); } catch(_){ }
          try {
            const compat = Object.assign({}, m, { lastUpdated: Date.now() });
            localStorage.setItem(
              `cannabisPOS-storeSettings_${sid}`,
              JSON.stringify(compat),
            );
            localStorage.setItem(
              "cannabisPOS-storeSettings",
              JSON.stringify(compat),
            );
          } catch (_) {}
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
              window.dispatchEvent(
                new CustomEvent("settings-updated", { detail: m }),
              );
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
                try {
                  const sid2 = currentStoreId();
                  localStorage.setItem(
                    `cannabisPOS-priceTiers-backup_${sid2}`,
                    JSON.stringify(arr),
                  );
                } catch (_) {}
                localStorage.setItem(
                  "cannabisPOS-priceTiers-backup",
                  JSON.stringify(arr),
                );
              }
            } catch (_) {}
          } catch (_) {}
          try {
            backgroundReconcile(m, sid);
          } catch (_) {}
          return { success: true, settings: m };
        } catch (e) {
          try{
            const resp = e && e.response && e.response.data ? e.response.data : null;
            const msg = resp && resp.message ? String(resp.message) : "";
            if (msg === 'verification_mismatch'){
              try{ await backgroundReconcile(merged, currentStoreId()); }catch(_){ }
            }
            const status = e && e.response && e.response.status ? Number(e.response.status) : 0;
            if (status === 409 && msg === 'stale_write' && resp && resp.server_settings){
              try {
                const srv = resp.server_settings || {};
                if (typeof resp.server_version === 'number') merged.settings_version = resp.server_version;
                Object.assign(merged, srv);
                continue; // retry loop with merged
              } catch(_){ }
            }
          }catch(_){ }
          last = e;
          await new Promise((r) => setTimeout(r, 200 * (i + 1)));
        }
      }
      // Backend failed: last-resort direct Supabase upsert to avoid data loss
      try {
        const sid = currentStoreId();
        const now = new Date().toISOString();
        const r = await supaReqRetry(`pos_settings?on_conflict=id`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify([
            {
              id: sid,
              store_name: merged.store_name ?? "",
              updated_at: now,
              Store_Information: pick(merged, SEC["Store_Information"]),
              Tax_Configuration: pick(merged, SEC["Tax_Configuration"]),
              "Sales_&_Transaction_Settings": pick(
                merged,
                SEC["Sales_&_Transaction_Settings"],
              ),
              Printing_Preferences: pick(merged, SEC["Printing_Preferences"]),
              Metrc_Integration: pick(merged, SEC["Metrc_Integration"]),
              "Auto_Delete_Zero-Quantity_Products": pick(
                merged,
                SEC["Auto_Delete_Zero-Quantity_Products"],
              ),
            },
          ]),
        });
        if (r.ok) {
          // Verify read-after-write
          try {
            const ver = await supaReqRetry(
              `pos_settings?id=eq.${encodeURIComponent(sid)}&select=*`,
              { method: "GET" },
            );
            if (ver.ok) {
              const arr = await ver.json();
              const row = Array.isArray(arr) && arr[0] ? arr[0] : null;
              if (row) {
                let composedR = {};
                try {
                  if (row["Store_Information"])
                    composedR = Object.assign(
                      composedR,
                      row["Store_Information"],
                    );
                } catch (_) {}
                try {
                  if (row["Tax_Configuration"])
                    composedR = Object.assign(
                      composedR,
                      row["Tax_Configuration"],
                    );
                } catch (_) {}
                try {
                  if (row["Sales_&_Transaction_Settings"])
                    composedR = Object.assign(
                      composedR,
                      row["Sales_&_Transaction_Settings"],
                    );
                } catch (_) {}
                try {
                  if (row["Printing_Preferences"])
                    composedR = Object.assign(
                      composedR,
                      row["Printing_Preferences"],
                    );
                } catch (_) {}
                try {
                  if (row["Metrc_Integration"])
                    composedR = Object.assign(
                      composedR,
                      row["Metrc_Integration"],
                    );
                } catch (_) {}
                try {
                  if (row["Auto_Delete_Zero-Quantity_Products"])
                    composedR = Object.assign(
                      composedR,
                      row["Auto_Delete_Zero-Quantity_Products"],
                    );
                } catch (_) {}
                if (row.store_name) composedR.store_name = row.store_name;
                const m = { ...DEFAULTS, ...composedR };
                this.saveLocal(sid, m);
                try { saveSnapshot(sid, m); } catch(_){ }
                try {
                  const compat = Object.assign({}, m, {
                    lastUpdated: Date.now(),
                  });
                  localStorage.setItem(
                    `cannabisPOS-storeSettings_${sid}`,
                    JSON.stringify(compat),
                  );
                  localStorage.setItem(
                    "cannabisPOS-storeSettings",
                    JSON.stringify(compat),
                  );
                } catch (_) {}
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
                  const sid = currentStoreId();
                  localStorage.setItem(
                    `cannabisPOS-weightThreshold_${sid}`,
                    String(m.weight_threshold ?? 0),
                  );
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
                    window.dispatchEvent(
                      new CustomEvent("settings-updated", { detail: m }),
                    );
                  } catch (_) {}
                } catch (_) {}
                return { success: true, settings: m };
              }
            }
          } catch (_) {}
          // Upsert succeeded but verification failed; accept optimistic success
          try {
            this.saveLocal(sid, merged);
          } catch (_) {}
          try {
            const compat = Object.assign({}, merged, {
              lastUpdated: Date.now(),
            });
            localStorage.setItem(
              `cannabisPOS-storeSettings_${sid}`,
              JSON.stringify(compat),
            );
            localStorage.setItem(
              "cannabisPOS-storeSettings",
              JSON.stringify(compat),
            );
          } catch (_) {}
          try {
            writeCookie("cpos_store_id", sid);
          } catch (_) {}
          try {
            const sid2 = currentStoreId();
            localStorage.setItem(
              `cannabisPOS-weightThreshold_${sid2}`,
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
              window.dispatchEvent(
                new CustomEvent("settings-updated", { detail: merged }),
              );
            } catch (_) {}
          } catch (_) {}
          try {
            backgroundReconcile(merged, sid);
          } catch (_) {}
          return { success: true, settings: merged };
        }
      } catch (_) {}
      let msg = "settings save failed";
      try {
        if (last && typeof last === "object") {
          msg = last.message || msg;
          if (!msg && last.response && last.response.data) {
            msg = String(
              last.response.data.message || last.response.data.error || msg,
            );
          }
        } else if (typeof last === "string" && last) {
          msg = last;
        }
      } catch (_) {}
      try {
        await fetch("/api/activity", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "settings-save-error",
            storeId: sid,
            message: msg,
          }),
        });
      } catch (_) {}
      try {
        localStorage.setItem(outboxKey(sid), JSON.stringify(merged));
      } catch (_) {}
      try { saveSnapshot(sid, merged); } catch(_){ }
      return {
        success: false,
        settings: merged,
        message: msg,
        error: last || new Error(msg),
      };
    },
  };

  // Hardening: flush outbox and reconcile on connectivity/visibility changes
  try {
    window.addEventListener('online', function(){
      try { flushSettingsOutbox(currentStoreId()); } catch(_){ }
      try { const local = SettingsClient.loadLocal(currentStoreId()); if (local) backgroundReconcile(local, currentStoreId()); } catch(_){ }
    });
    document.addEventListener('visibilitychange', function(){
      if (document.visibilityState === 'visible'){
        try { flushSettingsOutbox(currentStoreId()); } catch(_){ }
      }
    });
  } catch(_){}

  // Track server updated_at to avoid stale cache overlays
  try {
    window.addEventListener('settings:updated', function(e){
      try {
        const sid = (e && e.detail && e.detail.storeId) ? e.detail.storeId : currentStoreId();
        const stamp = (e && e.detail && e.detail.settings && e.detail.settings.updated_at) ? e.detail.settings.updated_at : null;
        if (stamp) localStorage.setItem(`cpos_settings_updated_at_${sid}`, String(stamp));
      } catch(_){ }
    });
  } catch(_){}

  SettingsClient.currentStoreName = currentStoreName;
  SettingsClient.canonicalizeId = canonicalizeId;
  window.SettingsClient = SettingsClient;

  // Periodic reconcile (once per minute)
  try {
    let lastRecon = 0;
    setInterval(function(){
      try{
        if (document.hidden) return;
        const now = Date.now(); if (now - lastRecon < 60000) return; lastRecon = now;
        const sid = currentStoreId();
        const local = SettingsClient.loadLocal(sid);
        if (local) backgroundReconcile(local, sid);
      } catch(_){}
    }, 15000);
  } catch(_){ }
})();
