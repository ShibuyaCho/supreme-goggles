// Universal Modal Keyboard Handler (framework-agnostic)
// Adds Escape to close and Enter to submit for any visible modal across the app

(function () {
  // Global error surfacing so cross-origin/minified errors aren't lost
  try {
    window.addEventListener("error", function (e) {
      try {
        const msg = (e && e.message) || "Script error";
        const file = (e && e.filename) || "";
        const line = (e && e.lineno) || 0;
        const col = (e && e.colno) || 0;
        const detail = file ? `${msg} @ ${file}:${line}:${col}` : msg;
        console.error("GlobalError:", e?.error || e);
        if (window.POS && typeof window.POS.showToast === "function")
          window.POS.showToast(detail, "error");
      } catch (_) {}
    });
    window.addEventListener("unhandledrejection", function (e) {
      try {
        const reason =
          (e && (e.reason?.message || e.reason)) ||
          "Unhandled promise rejection";
        console.error("UnhandledRejection:", e?.reason || e);
        if (window.POS && typeof window.POS.showToast === "function")
          window.POS.showToast(String(reason), "error");
      } catch (_) {}
    });
  } catch (_) {}

  function isVisible(el) {
    if (!el) return false;
    const style = window.getComputedStyle(el);
    return (
      style.display !== "none" &&
      style.visibility !== "hidden" &&
      style.opacity !== "0"
    );
  }

  function getOpenModals() {
    // Any element with class "modal" that is currently visible
    return Array.from(document.querySelectorAll(".modal")).filter(isVisible);
  }

  function findCloseButton(modal) {
    // Common close/selectors
    const selectors = [
      "[data-modal-close]",
      '[aria-label="Close"]',
      ".close",
      'button[title="Close"]',
      "button:has(svg), button:has(span)",
      "button",
    ];
    for (const sel of selectors) {
      const btns = Array.from(modal.querySelectorAll(sel)).filter((b) => {
        const text = (b.textContent || "").trim().toLowerCase();
        return (
          isVisible(b) &&
          (b.getAttribute("data-modal-close") !== null ||
            text === "cancel" ||
            text === "close" ||
            text === "×" ||
            text === "x")
        );
      });
      if (btns.length) return btns[0];
    }
    return null;
  }

  function findSubmitButton(modal) {
    const candidates = modal.querySelectorAll(
      '[data-modal-default], button[type="submit"], .bg-cannabis-green, .bg-yellow-600',
    );
    for (const el of candidates) {
      if (isVisible(el)) return el;
    }
    return null;
  }

  function onKeyDown(e) {
    const modals = getOpenModals();
    if (!modals.length) return;
    const top = modals[modals.length - 1];

    if (e.key === "Escape") {
      const closeBtn = findCloseButton(top);
      if (closeBtn) {
        e.preventDefault();
        closeBtn.click();
      }
    } else if (e.key === "Enter") {
      if (e.target && ["TEXTAREA"].includes(e.target.tagName)) return;
      const submitBtn = findSubmitButton(top);
      if (submitBtn) {
        e.preventDefault();
        submitBtn.click();
      }
    }
  }

  document.addEventListener("keydown", onKeyDown, true);

  // Global METRC refresh utility usable from any page (demo or live)
  window.__refreshMetrc = async function () {
    try {
      const res = await fetch("/api/metrc/debug/packages?diagnose=1", {
        credentials: "same-origin",
      });
      const text = await res.text();
      let data = {};
      try {
        data = JSON.parse(text);
      } catch (_) {
        data = {};
      }
      if (!res.ok) {
        const bodyMsg =
          data && (data.message || data.error)
            ? ` - ${data.message || data.error}`
            : "";
        throw new Error(`HTTP ${res.status}${bodyMsg}`);
      }
      const count = Number((data && (data.count || data.TotalRecords)) || 0);
      const best = data && (data.best_license || data.best_license_number);
      const used =
        data && (data.used_license || data.license || data.licenseNumber);
      const extra =
        best || used
          ? ` (best: ${best || "n/a"}; using: ${used || "n/a"})`
          : "";
      const msg = `METRC packages retrieved: ${count}${extra}`;
      if (window.POS && typeof window.POS.showToast === "function") {
        window.POS.showToast(msg, count > 0 ? "success" : "warning");
      } else {
        alert(msg);
      }
    } catch (e) {
      const msg = (e && e.message) || "Failed to refresh METRC";
      if (window.POS && typeof window.POS.showToast === "function") {
        window.POS.showToast(`Failed to refresh METRC: ${msg}`, "error");
      } else {
        alert(`Failed to refresh METRC: ${msg}`);
      }
    }
  };

  // Bind to known buttons if present
  [
    "global-refresh-metrc",
    "global-refresh-metrc-demo",
    "settings-refresh-metrc",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el)
      el.addEventListener("click", (e) => {
        e.preventDefault();
        window.__refreshMetrc();
      });
  });

  // Global Loyalty enroll fallback (works even if Alpine fails)
  if (!window.__loyaltyEnrollFallback) {
    window.__loyaltyEnrollFallback = async function (e) {
      try {
        if (e) e.preventDefault();
        const byId = (id) => document.getElementById(id);
        const name = (byId("loyalty-enroll-name") || {}).value || "";
        const phone = (byId("loyalty-enroll-phone") || {}).value || "";
        const email = (byId("loyalty-enroll-email") || {}).value || "";
        const tier = (byId("loyalty-enroll-tier") || {}).value || "Bronze";
        const pts =
          parseInt(
            ((byId("loyalty-enroll-points") || {}).value || "0").trim() || "0",
            10,
          ) || 0;
        if (!name || !phone || !email) {
          const warn = "Please fill name, phone and email";
          if (window.POS?.showToast) window.POS.showToast(warn, "warning");
          else alert(warn);
          return;
        }
        const payload = { name, phone, email, tier, starting_points: pts };
        let ok = false,
          data = null,
          msg = null;
        if (window.posAuth && typeof window.posAuth.apiRequest === "function") {
          const res = await window.posAuth.apiRequest(
            "post",
            "/loyalty/enroll",
            payload,
          );
          ok = !!res?.success && res?.data?.success !== false;
          data = res?.data || null;
          msg = res?.message || res?.data?.message || null;
        }
        if (!ok) {
          const csrf =
            (document.querySelector('meta[name="csrf-token"]') || {}).content ||
            "";
          const resp = await fetch("/loyalty/enroll", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              "X-Requested-With": "XMLHttpRequest",
              "X-CSRF-TOKEN": csrf,
            },
            body: JSON.stringify(payload),
            credentials: "same-origin",
          });
          try {
            data = await resp.json();
          } catch (_) {
            data = {};
          }
          ok = resp.ok && data?.success !== false;
          if (!ok && resp.redirected) msg = "Session expired. Please log in.";
        }
        const toastMsg = ok
          ? `Welcome ${data?.customer?.name || name}! You've been enrolled.`
          : msg || data?.message || "Enrollment failed";
        if (window.POS?.showToast)
          window.POS.showToast(toastMsg, ok ? "success" : "error");
        else alert(toastMsg);
        if (ok) {
          try {
            location.reload();
          } catch (_) {}
        }
      } catch (err) {
        const m = err?.message || "Enrollment error";
        if (window.POS?.showToast) window.POS.showToast(m, "error");
        else alert(m);
      }
    };
  }

  // Bind fallback to button if present (initial)
  const enrollBtn = document.getElementById("loyalty-enroll-btn");
  if (enrollBtn && !enrollBtn.__boundLoyalty) {
    enrollBtn.__boundLoyalty = true;
    enrollBtn.addEventListener("click", (e) => {
      e.preventDefault();
      window.__loyaltyEnrollFallback(e);
    });
  }

  // Event delegation: works even if button is created later (modal)
  document.addEventListener(
    "click",
    (e) => {
      const btn = e.target?.closest?.("#loyalty-enroll-btn");
      if (btn) {
        e.preventDefault();
        window.__loyaltyEnrollFallback(e);
      }
    },
    true,
  );

  // Intercept direct form submit as last resort
  document.addEventListener(
    "submit",
    (e) => {
      const form = e.target;
      try {
        if (form && form.matches('form[action*="/loyalty/enroll"]')) {
          e.preventDefault();
          window.__loyaltyEnrollFallback(e);
        }
      } catch (_) {}
    },
    true,
  );
})();
