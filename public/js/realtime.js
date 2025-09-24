(function () {
  try {
    const url =
      window.__SUPABASE_URL ||
      (typeof SUPABASE_URL !== "undefined" ? SUPABASE_URL : null);
    const key =
      window.__SUPABASE_ANON_KEY ||
      (typeof SUPABASE_ANON_KEY !== "undefined" ? SUPABASE_ANON_KEY : null);
    if (!url || !key || !window.supabase) return;

    // Reuse a single client
    const client = (window.__sbClient =
      window.__sbClient ||
      window.supabase.createClient(url, key, {
        realtime: { params: { eventsPerSecond: 5 } },
      }));

    const getStoreFilter = () => {
      try {
        const raw = localStorage.getItem("pos_store");
        const store = raw ? JSON.parse(raw) : null;
        if (store && store.id) return `store_id=eq.${String(store.id)}`;
      } catch (_) {}
      return null;
    };

    // Default tables covered by realtime; more can be added at runtime
    const defaultTables = [
      "customers",
      "employees",
      "products",
      "product_catalogue_items",
      "deals",
      "price_tiers",
      "pos_settings",
      "sales",
      "sale_items",
      "saved_sales",
      "rooms",
      "drawers",
      "loyalty_members",
      "loyalty_transactions",
      "report_templates",
      "time_clock_entries",
    ];

    const state = { channels: {}, tables: new Set(defaultTables) };

    const dispatch = (name, detail) => {
      try {
        window.dispatchEvent(new CustomEvent(name, { detail }));
      } catch (_) {}
    };

    const subscribe = (table) => {
      const filter = getStoreFilter();
      try {
        // Clean any existing channel
        const key = `realtime:${table}`;
        const prev = state.channels[key];
        if (prev) {
          try {
            client.removeChannel(prev);
          } catch (_) {}
        }
        const chan = client
          .channel(key)
          .on(
            "postgres_changes",
            {
              event: "*",
              schema: "public",
              table,
              filter:
                table === "customers" || table === "loyalty_members"
                  ? undefined
                  : filter || undefined,
            },
            (payload) => {
              const info = {
                table,
                type: payload.eventType,
                new: payload.new,
                old: payload.old,
                payload,
              };
              // Generic event (back-compat)
              dispatch("realtime:table-changed", info);
              // Table-specific event
              dispatch(`realtime:${table}`, info);
              // Type-specific event
              if (payload.eventType) {
                dispatch(
                  `realtime:${table}:${String(payload.eventType).toLowerCase()}`,
                  info,
                );
              }
            },
          )
          .subscribe((status) => {
            dispatch("realtime:status", { table, status });
          });
        state.channels[key] = chan;
        return chan;
      } catch (e) {
        dispatch("realtime:status", {
          table,
          status: "ERROR",
          error: String((e && e.message) || e),
        });
        return null;
      }
    };

    const resubscribeAll = () => {
      try {
        Object.values(state.channels).forEach((c) => {
          try {
            client.removeChannel(c);
          } catch (_) {}
        });
        state.channels = {};
      } catch (_) {}
      Array.from(state.tables).forEach(subscribe);
    };

    // Initial subscriptions
    resubscribeAll();

    // Re-subscribe when store context changes
    window.addEventListener("storage", (e) => {
      if (e && e.key === "pos_store") resubscribeAll();
    });
    // Network/visibility resilience
    window.addEventListener("online", resubscribeAll);
    document.addEventListener("visibilitychange", () => {
      if (document.visibilityState === "visible") resubscribeAll();
    });

    // Expose simple manager for dynamic control
    window.realtime = window.realtime || {
      getClient: () => client,
      getChannels: () => client.getChannels(),
      subscribeTable: (t) => {
        state.tables.add(String(t));
        return subscribe(String(t));
      },
      addTables: (arr) => {
        (arr || []).forEach((t) => state.tables.add(String(t)));
        resubscribeAll();
      },
      resubscribe: resubscribeAll,
    };
  } catch (_) {}
})();
