(function(){
  try {
    const url = window.__SUPABASE_URL || (typeof SUPABASE_URL !== 'undefined' ? SUPABASE_URL : null);
    const key = window.__SUPABASE_ANON_KEY || (typeof SUPABASE_ANON_KEY !== 'undefined' ? SUPABASE_ANON_KEY : null);
    if (!url || !key || !window.supabase) return;

    const client = window.supabase.createClient(url, key, {
      realtime: { params: { eventsPerSecond: 5 } },
    });

    const getStoreFilter = () => {
      try {
        const raw = localStorage.getItem('pos_store');
        const store = raw ? JSON.parse(raw) : null;
        if (store && store.id) return `store_id=eq.${String(store.id)}`;
      } catch (e) {}
      return null;
    };

    const tables = [
      'customers',
      'employees',
      'price_tiers',
      'loyalty_members',
      'products',
      'sales',
      'deals',
      'pos_settings',
    ];

    const subscribe = (table) => {
      const filter = getStoreFilter();
      const chan = client.channel(`realtime:${table}`)
        .on(
          'postgres_changes',
          { event: '*', schema: 'public', table, filter: filter || undefined },
          (payload) => {
            try {
              const evt = new CustomEvent('realtime:table-changed', {
                detail: { table, type: payload.eventType, new: payload.new, old: payload.old, payload },
              });
              window.dispatchEvent(evt);
            } catch (e) {}
          },
        )
        .subscribe((status) => {
          // no-op
        });
      return chan;
    };

    tables.forEach(subscribe);

    // If store changes, resubscribe with new filter
    window.addEventListener('storage', (e) => {
      if (e.key === 'pos_store') {
        try { client.getChannels().forEach((c) => client.removeChannel(c)); } catch (e) {}
        tables.forEach(subscribe);
      }
    });
  } catch (e) {}
})();
