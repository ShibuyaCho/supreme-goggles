(function () {
  // Ensure Supabase config is present even if the page didn't inject it
  try {
    if (!window.__SUPABASE_URL)
      window.__SUPABASE_URL = "https://yyitwchajkruipsjvifn.supabase.co";
    if (!window.__SUPABASE_ANON_KEY)
      window.__SUPABASE_ANON_KEY =
        "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inl5aXR3Y2hhamtydWlwc2p2aWZuIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc2OTQwNDUsImV4cCI6MjA3MzI3MDA0NX0.-fKS2ODSPNjLEx6HPrTlvXSV6hZqjdyFweIz8_f2ao8";
  } catch (_) {}
  function ensureContainer() {
    let el = document.getElementById("store-manager-root");
    if (el) return el;
    el = document.createElement("div");
    el.id = "store-manager-root";
    document.body.appendChild(el);
    return el;
  }
  function closeAll() {
    const root = ensureContainer();
    root.innerHTML = "";
  }
  function showToast(msg, type) {
    try {
      const t = document.createElement("div");
      t.className =
        "fixed top-4 right-4 z-[9999] px-4 py-2 rounded text-white " +
        (type === "error"
          ? "bg-red-600"
          : type === "success"
            ? "bg-green-600"
            : "bg-blue-600");
      t.textContent = msg;
      document.body.appendChild(t);
      setTimeout(() => t.remove(), 2500);
    } catch (_) {
      alert(msg);
    }
  }
  function sanitizeStoreId(input) {
    let id = String(input || "")
      .trim()
      .toLowerCase();
    id = id.replace(/\s+/g, "");
    id = id.replace(/[^a-z0-9_.-]/g, "");
    if (!id) id = "default";
    if (id === "defaultstore") id = "default";
    return id;
  }
  function renderModal(inner) {
    const root = ensureContainer();
    root.innerHTML = "";
    const wrap = document.createElement("div");
    wrap.className = "fixed inset-0 z-[9998]";
    wrap.innerHTML = `
      <div class="absolute inset-0 bg-black/40" data-close="1"></div>
      <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-lg shadow-xl overflow-hidden">${inner}</div>
      </div>`;
    wrap.addEventListener("click", (e) => {
      if (e.target.getAttribute && e.target.getAttribute("data-close") === "1")
        closeAll();
    });
    root.appendChild(wrap);
  }
  function mainMenu() {
    renderModal(`
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-1">Store Manager</h3>
        <p class="text-sm text-gray-600 mb-4">Add a new store or switch to an existing one.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <button id="sm-add" class="px-4 py-3 rounded bg-green-600 text-white hover:bg-green-700">Add Store</button>
          <button id="sm-switch" class="px-4 py-3 rounded bg-blue-600 text-white hover:bg-blue-700">Switch Store</button>
        </div>
      </div>`);
    document.getElementById("sm-add").onclick = addStoreEmbedded;
    document.getElementById("sm-switch").onclick = switchStoreModal;
  }
  // Expose for fallbacks
  window.mainMenu = mainMenu;
  async function getCurrentSettings() {
    try {
      if (window.SettingsClient && typeof SettingsClient.get === "function") {
        const sid = (function () {
          try {
            const raw = localStorage.getItem("pos_store");
            const s = raw ? JSON.parse(raw) : null;
            return (s && s.id) || "default";
          } catch (e) {
            return "default";
          }
        })();
        const resp = await SettingsClient.get(true);
        if (resp && (resp.settings || resp)) return resp.settings || resp;
      }
    } catch (_) {}
    return {};
  }
  async function addStoreModal() {
    const settings = await getCurrentSettings();
    const name = settings.store_name || "";
    renderModal(`
      <form id="add-store-form" class="p-6 space-y-4">
        <h3 class="text-lg font-semibold">Create New Store</h3>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Store ID</label>
          <input id="store-id" type="text" placeholder="e.g. downtown" class="w-full px-3 py-2 border rounded" required />
          <p class="text-xs text-gray-500 mt-1">Lowercase letters, numbers, dashes, dots, or underscores only.</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
          <input id="store-name" type="text" value="${name.replace(/"/g, "&quot;")}" class="w-full px-3 py-2 border rounded" required />
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" data-close="1" class="px-4 py-2 border rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create Store</button>
        </div>
      </form>`);
    const form = document.getElementById("add-store-form");
    form.onsubmit = async function (e) {
      e.preventDefault();
      const sidRaw = document.getElementById("store-id").value;
      const sname = document.getElementById("store-name").value || "Store";
      const sid = sanitizeStoreId(sidRaw);
      const payload = Object.assign({}, settings, { store_name: sname });
      try {
        const headers = {
          "Content-Type": "application/json",
          "X-Store-ID": sid,
        };
        const res = await (window.axios || axios).post(
          "/api/settings/pos",
          payload,
          { headers },
        );
        if (res && res.data && res.data.success) {
          try {
            localStorage.setItem(
              "pos_store",
              JSON.stringify({ id: sid, name: sname }),
            );
          } catch (_) {}
          try {
            window.dispatchEvent(new Event("storage"));
          } catch (_) {}
          if (typeof window.updateStoreHeaderLabel === "function")
            window.updateStoreHeaderLabel();
          closeAll();
          showToast("Store created and selected", "success");
          return;
        }
        showToast("Failed to create store", "error");
      } catch (err) {
        showToast(
          err?.response?.data?.message ||
            err?.message ||
            "Failed to create store",
          "error",
        );
      }
    };
  }
  async function addStoreEmbedded() {
    // Auto-generate a new store id (no prompt)
    const sid = "store-" + Date.now().toString(36);
    // Prepare minimal settings to create the store immediately so it appears in Switch list
    let base = {};
    try {
      if (
        window.SettingsClient &&
        typeof SettingsClient.defaults === "function"
      )
        base = SettingsClient.defaults();
    } catch (_) {}
    try {
      const current = await getCurrentSettings();
      if (current && typeof current === "object")
        base = Object.assign({}, base, current);
    } catch (_) {}
    base = Object.assign({ store_name: sid }, base);
    try {
      await (window.axios || axios).post("/api/settings/pos", base, {
        headers: { "Content-Type": "application/json", "X-Store-ID": sid },
      });
    } catch (_) {
      /* best-effort */
    }

    // Select the new store locally
    let prev = null;
    try {
      const raw = localStorage.getItem("pos_store");
      prev = raw ? JSON.parse(raw) : null;
    } catch (_) {}
    try {
      localStorage.setItem(
        "pos_store",
        JSON.stringify({ id: sid, name: base.store_name || sid }),
      );
    } catch (_) {}
    try {
      window.dispatchEvent(new Event("storage"));
    } catch (_) {}
    if (typeof window.updateStoreHeaderLabel === "function")
      window.updateStoreHeaderLabel();

    // Open embedded Settings so user can complete configuration
    renderModal(`
      <div class="p-0">
        <div class="flex items-center justify-between px-4 py-3 border-b">
          <div>
            <div class="font-semibold">New Store Settings</div>
            <div class="text-xs text-gray-500">Store ID: ${sid}</div>
          </div>
          <div class="flex items-center gap-2">
            <button id="sm-cancel" class="px-3 py-1 border rounded text-sm">Cancel</button>
            <button id="sm-done" class="px-3 py-1 bg-green-600 text-white rounded text-sm">Done</button>
          </div>
        </div>
        <div class="h-[70vh] max-h-[80vh]">
          <iframe src="/settings?embed=1" class="w-full h-full" style="border:0;"></iframe>
        </div>
      </div>`);
    document.getElementById("sm-cancel").onclick = function () {
      try {
        if (prev) localStorage.setItem("pos_store", JSON.stringify(prev));
        else localStorage.removeItem("pos_store");
      } catch (_) {}
      try {
        window.dispatchEvent(new Event("storage"));
      } catch (_) {}
      if (typeof window.updateStoreHeaderLabel === "function")
        window.updateStoreHeaderLabel();
      closeAll();
    };
    document.getElementById("sm-done").onclick = async function () {
      try {
        const r = await (window.axios || axios).get("/api/settings/pos", {
          headers: { "X-Store-ID": sid },
        });
        if (r && r.data && (r.data.settings || r.data.success)) {
          showToast("Store saved and selected", "success");
          closeAll();
          return;
        }
      } catch (_) {}
      showToast(
        "Finished. If not saved inside Settings, please save and try again.",
        "info",
      );
      closeAll();
    };
  }

  async function switchStoreModal() {
    renderModal(`<div class="p-6">
      <h3 class="text-lg font-semibold mb-1">Switch Store</h3>
      <p class="text-sm text-gray-600 mb-4">Select a store to switch to.</p>
      <div id="store-list" class="max-h-80 overflow-auto divide-y border rounded"></div>
      <div class="flex justify-end pt-3"><button data-close="1" class="px-4 py-2 border rounded">Close</button></div>
    </div>`);
    const listEl = document.getElementById("store-list");
    listEl.innerHTML =
      '<div class="p-4 text-sm text-gray-500">Loading stores…</div>';
    try {
      let rows = [];
      // Try open endpoint first (works even when not logged in)
      try {
        const res = await (window.axios || axios).get(
          "/api/settings/stores/open",
          { headers: { Accept: "application/json" } },
        );
        rows =
          res && res.data && Array.isArray(res.data.stores)
            ? res.data.stores
            : [];
      } catch (_) {
        rows = [];
      }
      // Then try protected endpoint via posAuth if still empty
      if (
        (!Array.isArray(rows) || rows.length === 0) &&
        window.posAuth &&
        typeof window.posAuth.apiRequest === "function"
      ) {
        try {
          const r = await window.posAuth.apiRequest("get", "/settings/stores");
          const payload = r && (r.data || r) ? r.data || r : {};
          if (payload && Array.isArray(payload.stores)) rows = payload.stores;
        } catch (_) {}
      }
      if (!Array.isArray(rows) || rows.length === 0) {
        try {
          const res = await (window.axios || axios).get(
            "/api/settings/stores",
            { headers: { Accept: "application/json" } },
          );
          rows =
            res && res.data && Array.isArray(res.data.stores)
              ? res.data.stores
              : [];
        } catch (_) {
          rows = [];
        }
      }
      if (!Array.isArray(rows) || rows.length === 0) {
        try {
          const base = (window.__SUPABASE_URL || "").replace(/\/$/, "");
          const key = window.__SUPABASE_ANON_KEY || "";
          if (base && key) {
            const url = `${base}/rest/v1/pos_settings?select=id,settings,updated_at&order=updated_at.desc`;
            const r = await fetch(url, {
              headers: {
                apikey: key,
                Authorization: `Bearer ${key}`,
                Accept: "application/json",
              },
            });
            if (r.ok) {
              const arr = await r.json();
              rows = (arr || []).map((row) => {
                const id = String(row.id || "");
                const name = row?.settings?.store_name
                  ? String(row.settings.store_name)
                  : id;
                return { id, name, updated_at: row.updated_at || null };
              });
            }
          }
        } catch (_) {
          rows = [];
        }
      }
      if (!Array.isArray(rows) || rows.length === 0) {
        try {
          const cands = [];
          for (let i = 0; i < localStorage.length; i++) {
            const k = localStorage.key(i) || "";
            if (k.startsWith("cpos_settings_")) cands.push(k);
          }
          rows = cands.map((k) => {
            const sid = k.replace("cpos_settings_", "");
            let name = sid;
            try {
              const st = JSON.parse(localStorage.getItem(k) || "{}");
              if (st && st.store_name) name = String(st.store_name);
            } catch (_) {}
            return { id: sid, name, updated_at: null };
          });
        } catch (_) {
          rows = [];
        }
      }
      if (!Array.isArray(rows) || rows.length === 0) {
        const hasSupa = !!(window.__SUPABASE_URL && window.__SUPABASE_ANON_KEY);
        const hint = hasSupa
          ? ""
          : '<div class="mt-1 text-xs text-amber-600">Supabase URL/Anon Key not detected on this page; using offline/local only.</div>';
        listEl.innerHTML =
          '<div class="p-4 text-sm text-gray-500">No stores found. Use Add Store.</div>' +
          hint;
        return;
      }
      listEl.innerHTML = rows
        .map((r) => {
          const id = r.id || "";
          const name = r.name || id;
          const updated = r.updated_at
            ? new Date(r.updated_at).toLocaleString()
            : "";
          return `<button data-id="${String(id).replace(/"/g, "&quot;")}" data-name="${String(name).replace(/"/g, "&quot;")}" class="w-full text-left px-4 py-3 hover:bg-gray-50">\n          <div class=\"font-medium\">${name}</div>\n          <div class=\"text-xs text-gray-500\">${id}${updated ? ` • Updated ${updated}` : ""}</div>\n        </button>`;
        })
        .join("");
      listEl.querySelectorAll("button[data-id]").forEach((btn) => {
        btn.addEventListener("click", () => {
          const sid = sanitizeStoreId(btn.getAttribute("data-id"));
          const sname = btn.getAttribute("data-name") || sid;
          try {
            localStorage.setItem(
              "pos_store",
              JSON.stringify({ id: sid, name: sname }),
            );
          } catch (_) {}
          try {
            window.dispatchEvent(new Event("storage"));
          } catch (_) {}
          if (typeof window.updateStoreHeaderLabel === "function")
            window.updateStoreHeaderLabel();
          closeAll();
          showToast("Switched to " + sname, "success");
        });
      });
    } catch (e) {
      listEl.innerHTML =
        '<div class="p-4 text-sm text-red-600">Failed to load stores</div>';
    }
  }
  // Expose switch-only entry point
  window.switchStoreModal = switchStoreModal;
  // Alias legacy entry to switch only (no Add)
  window.addOrSwitchStore = function () {
    switchStoreModal();
  };
  function bindHeaderStoreButton() {
    try {
      var btn = document.getElementById("header-store-button");
      if (btn && !btn.dataset.storeBound) {
        btn.dataset.storeBound = "1";
        btn.addEventListener("click", function (e) {
          try {
            e.preventDefault();
          } catch (_) {}
          if (window.switchStoreModal) window.switchStoreModal();
          else if (window.addOrSwitchStore) window.addOrSwitchStore();
        });
      }
    } catch (_) {}
  }
  // Bind immediately if DOM is ready, and also on DOMContentLoaded
  if (document.readyState !== "loading") bindHeaderStoreButton();
  try {
    document.addEventListener("DOMContentLoaded", function () {
      bindHeaderStoreButton();
      // Permanently remove any legacy Default Store dropdown and Clear buttons
      try {
        document
          .querySelectorAll('button[onclick*="pos_store"]')
          .forEach(function (el) {
            var code = el.getAttribute("onclick") || "";
            if (
              code.includes("removeItem('pos_store'") ||
              code.includes('removeItem("pos_store"')
            ) {
              el.remove();
            }
          });
        var legacySwitch = Array.from(
          document.querySelectorAll("button"),
        ).filter(function (b) {
          return (b.textContent || "").trim() === "Switch Store…";
        });
        legacySwitch.forEach(function (b) {
          var box = b.closest(".relative");
          if (box) box.remove();
        });
        var legacyClearStore = Array.from(
          document.querySelectorAll("button"),
        ).filter(function (b) {
          return (b.textContent || "").trim() === "Clear Store";
        });
        legacyClearStore.forEach(function (b) {
          var box = b.closest(".relative");
          if (box) box.remove();
        });
      } catch (_) {}
    });
  } catch (_) {}
  // As a resilience measure, observe DOM mutations to (re)bind if header is rebuilt
  try {
    var mo = new MutationObserver(function () {
      bindHeaderStoreButton();
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  } catch (_) {}
})();
