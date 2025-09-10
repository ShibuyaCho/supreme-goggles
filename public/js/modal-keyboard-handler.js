// Universal Modal Keyboard Handler (framework-agnostic)
// Adds Escape to close and Enter to submit for any visible modal across the app

(function () {
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
      const res = await fetch("/api/metrc/debug/packages", {
        credentials: "same-origin",
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json().catch(() => ({}));
      const count = Number((data && (data.count || data.TotalRecords)) || 0);
      if (window.POS && typeof window.POS.showToast === "function") {
        window.POS.showToast(`METRC packages retrieved: ${count}`, "success");
      } else {
        alert(`METRC packages retrieved: ${count}`);
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
})();
