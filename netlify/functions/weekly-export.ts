/*
  Weekly offsite export to Supabase Storage.
  - Fetches key tables via Supabase REST
  - Generates CSVs
  - Uploads to Storage bucket "db-exports" under weekly/YYYY-MM-DD/
*/

const SUPABASE_URL = process.env.SUPABASE_URL || "";
const SUPABASE_ANON_KEY = process.env.SUPABASE_ANON_KEY || "";

// Minimal CSV serializer
function csvEscape(value: unknown): string {
  if (value === null || value === undefined) return "";
  const s = typeof value === "string" ? value : JSON.stringify(value);
  const needsQuotes = /[",\n\r]/.test(s) || s.includes(",");
  const escaped = s.replace(/"/g, '""');
  return needsQuotes ? `"${escaped}"` : escaped;
}

function toCSV(rows: any[]): string {
  if (!rows || rows.length === 0) return "";
  const headers = Array.from(
    rows.reduce((set: Set<string>, r: any) => {
      Object.keys(r || {}).forEach((k) => set.add(k));
      return set;
    }, new Set<string>()),
  );
  const headerLine = headers.map(csvEscape).join(",");
  const lines = rows.map((row) =>
    headers.map((h) => csvEscape((row as any)[h])).join(","),
  );
  return [headerLine, ...lines].join("\n");
}

async function fetchAll(table: string): Promise<any[]> {
  const url = new URL(`${SUPABASE_URL}/rest/v1/${table}`);
  url.searchParams.set("select", "*");
  url.searchParams.set("limit", "200000");
  const res = await fetch(url, {
    headers: {
      apikey: SUPABASE_ANON_KEY,
      Authorization: `Bearer ${SUPABASE_ANON_KEY}`,
    },
  });
  if (!res.ok) {
    // Table may not exist or be protected; return empty to keep job resilient
    return [];
  }
  return await res.json();
}

async function uploadToStorage(
  path: string,
  content: string,
  contentType = "text/csv",
): Promise<boolean> {
  const url = new URL(`${SUPABASE_URL}/storage/v1/object/${path}`);
  const res = await fetch(url, {
    method: "POST",
    headers: {
      apikey: SUPABASE_ANON_KEY,
      Authorization: `Bearer ${SUPABASE_ANON_KEY}`,
      "content-type": contentType,
      "x-upsert": "true",
    },
    body: content,
  });
  return res.ok;
}

export const handler = async () => {
  if (!SUPABASE_URL || !SUPABASE_ANON_KEY) {
    return {
      statusCode: 500,
      body: "Missing Supabase configuration",
    };
  }

  const date = new Date();
  const yyyy = date.getUTCFullYear();
  const mm = String(date.getUTCMonth() + 1).padStart(2, "0");
  const dd = String(date.getUTCDate()).padStart(2, "0");
  const datePrefix = `weekly/${yyyy}-${mm}-${dd}`;

  const tables = [
    "app_users",
    "employees",
    "customers",
    "products",
    "deals",
    "price_tiers",
    "sales",
    "loyalty_transactions",
    "inventory_movements",
    "metrc_logs",
    "activity_logs",
    "report_templates",
    "pos_settings",
  ];

  const results: Record<string, string> = {};

  for (const table of tables) {
    try {
      const rows = await fetchAll(table);
      const csv = rows.length ? toCSV(rows) : "";
      const content = csv || "";
      const relPath = `db-exports/${datePrefix}/${table}.csv`;
      const ok = await uploadToStorage(relPath, content);
      results[table] = ok ? "uploaded" : "skipped";
    } catch (e) {
      results[table] = "error";
    }
  }

  return {
    statusCode: 200,
    body: JSON.stringify({ ok: true, results }),
  };
};
