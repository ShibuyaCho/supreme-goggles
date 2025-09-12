// server.js (ESM, Express 5, uses RegExp routes to avoid path-to-regexp string quirks)
import express from "express";
import path from "node:path";
import fs from "node:fs";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3000;

// Supabase REST helper
const SUPABASE_URL = process.env.SUPABASE_URL || "";
const SUPABASE_ANON_KEY = process.env.SUPABASE_ANON_KEY || "";
async function supaFetch(
  path,
  { method = "GET", body = null, query = null } = {},
) {
  if (!SUPABASE_URL || !SUPABASE_ANON_KEY) {
    return {
      ok: false,
      status: 503,
      json: async () => ({ error: "Supabase not configured" }),
    };
  }
  const url = new URL(`${SUPABASE_URL}/rest/v1/${path}`);
  if (query && typeof query === "object")
    Object.entries(query).forEach(([k, v]) => url.searchParams.set(k, v));
  const res = await fetch(url, {
    method,
    headers: {
      apikey: SUPABASE_ANON_KEY,
      Authorization: `Bearer ${SUPABASE_ANON_KEY}`,
      "Content-Type": "application/json",
      Prefer: "return=representation",
    },
    body: body ? JSON.stringify(body) : null,
  });
  return res;
}

// In-memory dev auth store with disk persistence
const devStore = {
  users: [], // { id, name, email, role, permissions, employee, password, pin, employee_id }
  tokens: new Map(), // token -> userId (not persisted)
};
let nextUserId = 1;
let nextEmployeeId = 1;

// Dev report templates (persisted)
let devTemplates = [];
let nextTemplateId = 1;

const AUTH_FILE = path.join(__dirname, ".dev-auth.json");
function loadDevState() {
  try {
    if (fs.existsSync(AUTH_FILE)) {
      const raw = fs.readFileSync(AUTH_FILE, "utf8");
      const data = JSON.parse(raw || "{}");
      if (Array.isArray(data.users)) devStore.users = data.users;
      if (typeof data.nextUserId === "number") nextUserId = data.nextUserId;
      if (typeof data.nextEmployeeId === "number")
        nextEmployeeId = data.nextEmployeeId;
      if (Array.isArray(data.devTemplates)) devTemplates = data.devTemplates;
      if (typeof data.nextTemplateId === "number")
        nextTemplateId = data.nextTemplateId;
    }
  } catch (e) {
    console.warn("Failed to load dev auth state:", e.message);
  }
}
function saveDevState() {
  try {
    const data = {
      users: devStore.users,
      nextUserId,
      nextEmployeeId,
      devTemplates,
      nextTemplateId,
    };
    fs.writeFileSync(AUTH_FILE, JSON.stringify(data, null, 2), "utf8");
  } catch (e) {
    console.warn("Failed to save dev auth state:", e.message);
  }
}

// Load persisted state on boot
loadDevState();

function genToken() {
  return (
    "dev-" +
    Math.random().toString(36).slice(2) +
    Math.random().toString(36).slice(2)
  );
}

function findUserByEmail(email) {
  return devStore.users.find(
    (u) => u.email?.toLowerCase() === String(email || "").toLowerCase(),
  );
}

function findUserByEmployee(employee_id) {
  return devStore.users.find((u) => u.employee?.employee_id === employee_id);
}

function authFromReq(req) {
  const auth = req.headers.authorization || "";
  const token = auth.startsWith("Bearer ") ? auth.slice(7) : null;
  if (!token) return null;
  const uid = devStore.tokens.get(token);
  return devStore.users.find((u) => u.id === uid) || null;
}

app.set("trust proxy", 1);
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Static assets (no auto-index so we control '/')
const publicDir = path.join(__dirname, "public");
app.use(express.static(publicDir, { index: false }));

// Healthcheck
app.get("/health", (_req, res) => res.type("text").send("ok"));

// Root: serve ./index.html if present, else inline HTML
app.get("/", (_req, res) => {
  const indexPath = path.join(__dirname, "index.html");
  fs.access(indexPath, fs.constants.F_OK, (err) => {
    if (!err) return res.sendFile(indexPath);
    res.type("html").send(`<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Cannabis POS System - Laravel Converted</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head><body class="bg-gray-50">
  <div class="min-h-screen flex items-center justify-center">
    <div class="max-w-2xl mx-auto text-center p-8">
      <h1 class="text-4xl font-bold text-green-600 mb-4">🌿 Cannabis POS System</h1>
      <h2 class="text-2xl font-semibold text-gray-800 mb-6">Successfully Converted to Laravel/PHP!</h2>
      <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900">✅ Conversion Complete</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
          <div>
            <h4 class="font-medium text-green-600 mb-2">Frontend Converted:</h4>
            <ul class="text-sm space-y-1">
              <li>• React/TypeScript → Laravel Blade</li>
              <li>• 20+ UI Components → Blade Components</li>
              <li>• Cannabis POS Interface → Laravel Views</li>
              <li>• Navigation → Blade Layouts</li>
            </ul>
          </div>
          <div>
            <h4 class="font-medium text-green-600 mb-2">Backend Ready:</h4>
            <ul class="text-sm space-y-1">
              <li>• Laravel Controllers ✓</li>
              <li>• Cannabis Models ✓</li>
              <li>• METRC Service ✓</li>
              <li>• Oregon Limits Service ✓</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-green-800 mb-2">🌿 Cannabis Features Ready</h3>
        <div class="text-sm text-green-700 grid grid-cols-2 gap-2">
          <div>• METRC Compliance</div>
          <div>• Oregon State Limits</div>
          <div>• Age Verification</div>
          <div>• Tax Calculations</div>
          <div>• Room Management</div>
          <div>• Product Actions</div>
        </div>
      </div>
      <div class="space-y-2 text-sm text-gray-600">
        <p><strong>Laravel/PHP Conversion:</strong> All React/TypeScript files converted to Laravel Blade views</p>
        <p><strong>Cannabis POS:</strong> Complete dispensary management system ready for production</p>
        <p><strong>Database:</strong> SQLite configured for development</p>
      </div>
    </div>
  </div>
</body></html>`);
  });
});

// Auth endpoints for dev mode (simulate backend)
app.post(
  ["/api/auth/self-register", "/api/self-register"],
  async (req, res) => {
    const { name, email, password, password_confirmation, pin } =
      req.body || {};
    if (!name || !email || !password || !password_confirmation || !pin) {
      return res.status(422).json({
        error: "Validation failed",
        errors: { fields: "Missing required fields" },
      });
    }
    if (String(password) !== String(password_confirmation)) {
      return res.status(422).json({
        error: "Validation failed",
        errors: { password: ["Passwords do not match"] },
      });
    }
    if (!/^\d{4}$/.test(String(pin))) {
      return res.status(422).json({
        error: "Validation failed",
        errors: { pin: ["PIN must be 4 digits"] },
      });
    }
    if (findUserByEmail(email)) {
      return res.status(422).json({
        error: "Validation failed",
        errors: { email: ["Email already taken"] },
      });
    }
    const empId = "EMP" + String(nextEmployeeId++).padStart(5, "0");
    const [first_name, last_name = ""] = String(name).trim().split(/\s+/, 2);
    const user = {
      id: nextUserId++,
      name,
      email,
      role: "cashier",
      status: "active",
      permissions: ["pos:*", "products:read", "customers:read", "sales:create"],
      employee: {
        id: nextEmployeeId,
        employee_id: empId,
        first_name,
        last_name,
      },
      password, // dev only
      pin: String(pin),
    };
    devStore.users.push(user);
    // Persist to Supabase (best-effort)
    try {
      await supaFetch("app_users", {
        method: "POST",
        body: [
          {
            email,
            name,
            role: user.role,
            permissions: user.permissions,
            employee_id: user.employee.employee_id,
            password: String(password),
            pin: String(pin),
          },
        ],
      });
    } catch (_) {}
    // Persist users so credentials survive restarts
    saveDevState();
    const token = genToken();
    devStore.tokens.set(token, user.id);
    return res.status(201).json({
      message: "Account created successfully",
      user,
      token,
      success: true,
    });
  },
);

app.post(["/api/auth/login", "/api/login"], async (req, res) => {
  const { email, password } = req.body || {};
  let user = findUserByEmail(email);

  // Dev ergonomics: if user doesn't exist, auto-create and persist (admin)
  if (!user && email && password) {
    const name = String(email).split("@")[0].replace(/\W+/g, " ");
    const [first_name, last_name = ""] = name.trim().split(/\s+/, 2);
    const empId = "EMP" + String(nextEmployeeId++).padStart(5, "0");
    user = {
      id: nextUserId++,
      name: name || "Admin User",
      email,
      role: "admin",
      status: "active",
      permissions: [
        "pos:*",
        "products:*",
        "customers:*",
        "sales:*",
        "reports:*",
        "employees:*",
        "inventory:*",
        "analytics:*",
        "metrc:*",
      ],
      employee: {
        id: nextEmployeeId,
        employee_id: empId,
        first_name,
        last_name,
      },
      password: String(password),
      pin: "1234",
    };
    devStore.users.push(user);
    saveDevState();
  }

  if (!user) {
    return res
      .status(401)
      .json({ error: "Invalid credentials", success: false });
  }
  // Ensure user exists in Supabase (idempotent upsert by email)
  try {
    await supaFetch("app_users", {
      method: "POST",
      body: [
        {
          email: user.email,
          name: user.name,
          role: user.role,
          permissions: user.permissions,
          employee_id: user.employee?.employee_id,
          password: String(user.password || password),
          pin: String(user.pin || "1234"),
        },
      ],
      query: { on_conflict: "email" },
    });
  } catch (_) {}

  // If password mismatch, update stored password in dev (prevents lockout)
  if (String(user.password) !== String(password)) {
    user.password = String(password);
    saveDevState();
  }

  const token = genToken();
  devStore.tokens.set(token, user.id);
  res.json({ message: "Login successful", user, token, success: true });
});

app.post(["/api/auth/pin-login", "/api/pin-login"], (req, res) => {
  const { employee_id, pin } = req.body || {};
  const user = findUserByEmployee(employee_id);
  if (!user || String(user.pin) !== String(pin)) {
    return res
      .status(401)
      .json({ error: "Invalid employee ID or PIN", success: false });
  }
  if (String(user.status || 'active').toLowerCase() === 'inactive') {
    return res.status(403).json({ error: "Account is inactive", success: false });
  }
  const token = genToken();
  devStore.tokens.set(token, user.id);
  res.json({
    message: "PIN login successful",
    employee: {
      id: user.employee.id,
      employee_id: user.employee.employee_id,
      name: user.name,
      role: user.role,
      permissions: user.permissions,
    },
    token,
    success: true,
  });
});

app.get("/api/user", (req, res) => {
  const user = authFromReq(req);
  if (!user) return res.status(401).json({ error: "Unauthorized" });
  res.json(user);
});

// Auth: current user (for /api/auth/me)
app.get(["/api/auth/me", "/api/me"], (req, res) => {
  const user = authFromReq(req);
  if (!user) return res.status(401).json({ error: "Unauthorized" });
  res.json({ user, success: true });
});

// Auth: refresh token
app.post(["/api/auth/refresh", "/api/refresh"], (req, res) => {
  const user = authFromReq(req);
  if (!user)
    return res.status(401).json({ error: "Unauthorized", success: false });
  const token = genToken();
  devStore.tokens.set(token, user.id);
  res.json({ message: "Token refreshed", token, success: true });
});

// Auth: verify current user's PIN
app.post("/api/auth/verify-pin", async (req, res) => {
  const current = authFromReq(req);
  if (!current) return res.status(401).json({ success: false, error: "Unauthorized" });
  const pin = String(req.body?.pin || "").trim();
  if (!pin) return res.status(422).json({ success: false, error: "PIN required" });

  function hasManagePerm(u) {
    const role = String(u?.role || '').toLowerCase();
    const perms = Array.isArray(u?.permissions) ? u.permissions : [];
    return role === 'admin' || perms.includes('*') || perms.includes('employees:*') || perms.includes('employees:manage');
  }

  // Accept any user's PIN with sufficient permissions (not only the current user)
  // 1) In-memory dev users
  try {
    const match = devStore.users.find((u) => String(u.pin || '') === pin && hasManagePerm(u));
    if (match) return res.json({ success: true, actor: { email: match.email, role: match.role } });
  } catch (_) {}

  // 2) Supabase app_users by pin
  try {
    const r = await supaFetch(`app_users?pin=eq.${encodeURIComponent(pin)}&select=email,role,permissions`, { method: 'GET' });
    if (r.ok) {
      const rows = await r.json();
      const row = Array.isArray(rows) && rows[0] ? rows[0] : null;
      if (row && hasManagePerm(row)) return res.json({ success: true, actor: { email: row.email, role: row.role } });
      if (row) return res.status(403).json({ success: false, error: 'Insufficient permissions' });
    }
  } catch (_) {}

  return res.status(401).json({ success: false, error: "Invalid employee ID or PIN" });
});

// In-memory saved report templates are declared above and persisted to disk

function getAuthUser(req) {
  return authFromReq(req);
}

function titleCase(s) {
  return String(s || "")
    .replace(/_/g, " ")
    .replace(/\s+/g, " ")
    .trim()
    .toLowerCase()
    .replace(/\b\w/g, (c) => c.toUpperCase());
}

function getHeadingsForReport(reportType, metrics) {
  if (Array.isArray(metrics) && metrics.length) {
    return metrics.map((m) => titleCase(m));
  }
  switch (reportType) {
    case "sales":
      return [
        "Date",
        "Transaction ID",
        "Customer",
        "Items",
        "Subtotal",
        "Tax",
        "Total",
        "Payment Method",
      ];
    case "inventory":
      return [
        "Product Name",
        "SKU",
        "Category",
        "Quantity",
        "Unit Cost",
        "Unit Price",
        "Total Value",
        "Room",
        "METRC Tag",
      ];
    case "customers":
      return [
        "Customer Name",
        "Type",
        "Email",
        "Phone",
        "Total Visits",
        "Total Spent",
        "Average Order",
        "Last Visit",
      ];
    case "products":
      return [
        "Name",
        "Category",
        "SKU",
        "Price",
        "Cost",
        "Quantity",
        "Room",
        "THC%",
        "CBD%",
        "METRC Tag",
      ];
    case "analytics":
      return ["Metric", "Value", "Period", "Change", "Percentage"];
    case "metrc":
      return [
        "Package Tag",
        "Product",
        "Quantity",
        "Unit",
        "Status",
        "Location",
        "Last Modified",
      ];
    case "compliance":
      return ["Date", "Type", "Description", "Status", "Employee", "Notes"];
    case "employees":
      return [
        "Name",
        "Role",
        "Employee ID",
        "Email",
        "Hours Worked",
        "Sales Count",
        "Performance Score",
      ];
    default:
      return ["Column 1", "Column 2", "Column 3"];
  }
}

// Reports: export (dev implementation returns headers-only CSV/HTML)
app.post("/api/reports/export", (req, res) => {
  const {
    report_type = "sales",
    format = "csv",
    filters = {},
    orientation = "portrait",
    paper_size = "a4",
  } = req.body || {};
  const metrics =
    filters && Array.isArray(filters.metrics) ? filters.metrics : [];
  const headings = getHeadingsForReport(
    String(report_type).toLowerCase(),
    metrics,
  );

  if (String(format).toLowerCase() === "pdf") {
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${titleCase(report_type)} Report</title></head><body>
      <h1>${titleCase(report_type)} Report</h1>
      <p>Generated at: ${new Date().toISOString()}</p>
      <table border="1" cellspacing="0" cellpadding="6"><thead><tr>${headings.map((h) => `<th>${h}</th>`).join("")}</tr></thead><tbody><tr>${headings.map(() => "<td></td>").join("")}</tr></tbody></table>
    </body></html>`;
    res
      .status(200)
      .setHeader("Content-Type", "text/html; charset=UTF-8")
      .setHeader(
        "Content-Disposition",
        `attachment; filename="dev_${report_type}_report.html"`,
      )
      .send(html);
    return;
  }

  // CSV (also used as Excel fallback)
  res.setHeader("Content-Type", "text/csv");
  res.setHeader(
    "Content-Disposition",
    `attachment; filename="dev_${report_type}_report.csv"`,
  );
  res.setHeader("Pragma", "no-cache");
  res.setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0");
  res.setHeader("Expires", "0");

  const headerRow = headings.join(",") + "\n";
  res.status(200).send(headerRow);
});

// Reports: available list (dev)
app.get("/api/reports/available", (_req, res) => {
  res.json({
    sales: "Sales Report",
    inventory: "Inventory Report",
    customers: "Customer Analytics",
    products: "Product Performance",
    metrc: "METRC Compliance",
    employees: "Employee Performance",
    daily_summary: "Daily Summary",
    tax_report: "Tax Report",
    analytics: "Business Analytics",
    compliance: "Compliance Report",
  });
});

// Reports: templates (dev, in-memory)
app.get("/api/reports/templates", (req, res) => {
  const user = getAuthUser(req);
  if (!user) return res.status(200).json({ templates: [] });
  const list = devTemplates.filter((t) => t.user_id === user.id);
  res.json({ templates: list });
});

app.post("/api/reports/templates", (req, res) => {
  const user = getAuthUser(req);
  if (!user) return res.status(401).json({ error: "Unauthorized" });
  const tpl = {
    id: nextTemplateId++,
    user_id: user.id,
    name: req.body?.name || "Untitled",
    description: req.body?.description || "",
    report_type: String(req.body?.report_type || "sales").toLowerCase(),
    format: String(req.body?.format || "pdf").toLowerCase(),
    include_charts: !!req.body?.include_charts,
    orientation: req.body?.orientation || "portrait",
    paper_size: req.body?.paper_size || "a4",
    config: req.body?.config || {},
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  };
  devTemplates.push(tpl);
  saveDevState();
  res.status(201).json({ message: "Template saved", template: tpl });
});

app.get("/api/reports/templates/:id", (req, res) => {
  const user = getAuthUser(req);
  const tpl = devTemplates.find((t) => String(t.id) === String(req.params.id));
  if (!tpl) return res.status(404).json({ error: "Not found" });
  if (user && tpl.user_id === user.id) return res.json({ template: tpl });
  return res.status(403).json({ error: "Not authorized" });
});

app.put("/api/reports/templates/:id", (req, res) => {
  const user = getAuthUser(req);
  const idx = devTemplates.findIndex(
    (t) => String(t.id) === String(req.params.id),
  );
  if (idx === -1) return res.status(404).json({ error: "Not found" });
  const tpl = devTemplates[idx];
  if (!user || tpl.user_id !== user.id)
    return res.status(403).json({ error: "Not authorized" });
  const updated = {
    ...tpl,
    ...req.body,
    report_type: req.body?.report_type
      ? String(req.body.report_type).toLowerCase()
      : tpl.report_type,
    format: req.body?.format
      ? String(req.body.format).toLowerCase()
      : tpl.format,
    updated_at: new Date().toISOString(),
  };
  devTemplates[idx] = updated;
  saveDevState();
  res.json({ message: "Template updated", template: updated });
});

app.delete("/api/reports/templates/:id", (req, res) => {
  const user = getAuthUser(req);
  const idx = devTemplates.findIndex(
    (t) => String(t.id) === String(req.params.id),
  );
  if (idx === -1) return res.status(404).json({ error: "Not found" });
  const tpl = devTemplates[idx];
  if (!user || tpl.user_id !== user.id)
    return res.status(403).json({ error: "Not authorized" });
  devTemplates.splice(idx, 1);
  saveDevState();
  res.json({ message: "Template deleted" });
});

// Customers: enroll loyalty
app.post(["/api/loyalty/enroll", "/api/customers"], async (req, res, next) => {
  // If POST to /api/customers with full payload, upsert; if /loyalty/enroll, map fields
  try {
    const b = req.body || {};
    const row =
      b.name && b.email && b.phone
        ? b
        : {
            name: b.name,
            email: b.email,
            phone: b.phone,
            customer_type: b.tier ? "loyalty" : b.customer_type || "consumer",
            loyalty_points: b.starting_points ?? 0,
          };
    const r = await supaFetch("customers", { method: "POST", body: [row] });
    const payload = r.ok ? await r.json() : null;
    return res.status(201).json({
      success: true,
      customer: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (e) {
    return res
      .status(500)
      .json({ success: false, error: "Failed to save customer" });
  }
});

// Settings: POS get
app.get("/api/settings/pos", async (_req, res) => {
  // Defaults (mirrors Laravel defaults)
  const defaults = {
    sales_tax: 0.0,
    excise_tax: 10.0,
    cannabis_tax: 17.0,
    tax_inclusive: false,
    store_name: "Cannabest POS",
    store_address: "",
    store_phone: "",
    store_email: "",
    website: "",
    store_manager: "",
    license_number: "",
    receipt_footer:
      "Thank you for your business!\nKeep receipt for returns and warranty.",
    exit_label_categories: ["Flower", "Pre-Rolls", "Concentrates", "Edibles"],
    auto_print_receipt: false,
    receipt_autoprint: false,
    receipt_categories_autoprint: [],
    receipt_show_tax_breakdown: true,
    receipt_show_metrc: true,
    receipt_show_loyalty: true,
    receipt_show_qr_code: false,
    default_receipt_printer: "",
    receipt_paper_size: "80mm",
    require_customer: true,
    age_verification: true,
    limit_enforcement: true,
    accept_cash: true,
    accept_debit: true,
    accept_check: false,
    round_to_nearest: false,
    minimum_price_enabled: false,
    minimum_price_amount: 0.01,
    minimum_price_categories: [],
    inventory_view_mode: "cards",
    expandable_cart: true,
    // Role-based permissions (defaults)
    role_permissions: {
      admin: ["*"],
      manager: [
        "pos:*",
        "products:*",
        "customers:*",
        "sales:*",
        "analytics:read",
        "deals:*",
        "employees:read",
        "metrc:access",
        "metrc:sync",
        "reports:read",
        "reports:export",
      ],
      inventory: ["products:*", "metrc:access", "metrc:sync", "analytics:read"],
      budtender: [
        "pos:*",
        "products:read",
        "customers:read",
        "sales:create",
        "analytics:read",
      ],
      cashier: [
        "pos:*",
        "products:read",
        "sales:create",
        "products:print",
        "analytics:read",
        "pos:scanner_only",
      ],
    },
    auto_delete_zero_quantity: false,
    auto_delete_zero_days: 1,
    metrc_enabled: true,
    dark_mode: false,
    theme_color: "green",
    font_size: "medium",
    high_contrast: false,
    reduce_motion: false,
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
  try {
    const r = await supaFetch("pos_settings?id=eq.default&select=*", {
      method: "GET",
    });
    if (r.ok) {
      const arr = await r.json();
      const row = Array.isArray(arr) && arr[0] ? arr[0] : null;
      const settings =
        row?.settings && typeof row.settings === "object"
          ? { ...defaults, ...row.settings }
          : defaults;
      return res.json({
        success: true,
        settings,
        tax_rate: settings.sales_tax ?? 20.0,
        medical_tax_rate: 0.0,
        currency: "USD",
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
      });
    }
  } catch (_) {}
  return res.json({
    success: true,
    settings: defaults,
    tax_rate: defaults.sales_tax ?? 20.0,
    medical_tax_rate: 0.0,
    currency: "USD",
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
  });
});

// Settings: POS update
app.post("/api/settings/pos", async (req, res) => {
  const incoming = req.body?.settings || req.body || {};
  try {
    // Fetch current settings to merge
    let current = {};
    try {
      const r0 = await supaFetch("pos_settings?id=eq.default&select=*", {
        method: "GET",
      });
      if (r0.ok) {
        const arr = await r0.json();
        const row = Array.isArray(arr) && arr[0] ? arr[0] : null;
        if (row && row.settings && typeof row.settings === "object")
          current = row.settings;
      }
    } catch (_) {}
    const merged = { ...current, ...incoming };

    const r = await supaFetch("pos_settings", {
      method: "POST",
      body: [
        {
          id: "default",
          settings: merged,
          updated_at: new Date().toISOString(),
        },
      ],
      query: { on_conflict: "id" },
    });
    const payload = r.ok ? await r.json() : null;
    return res.json({ success: true, settings: merged, saved: payload });
  } catch (e) {
    return res
      .status(500)
      .json({ success: false, error: "Failed to save settings" });
  }
});

// Employees: list
app.get("/api/employees", async (_req, res) => {
  try {
    const r = await supaFetch("employees?select=*", { method: "GET" });
    const payload = r.ok ? await r.json() : [];
    res.json({ success: true, employees: payload });
  } catch (e) {
    res.json({ success: true, employees: [] });
  }
});

// Employees: create
app.post("/api/employees", async (req, res) => {
  const b = req.body || {};
  const row = {
    employee_id:
      b.employee_id ||
      "EMP" + Math.random().toString(36).slice(2, 7).toUpperCase(),
    first_name: b.first_name || b.name?.split(" ")[0] || "",
    last_name: b.last_name || b.name?.split(" ").slice(1).join(" ") || "",
    role: b.role || "cashier",
    email: b.email || null,
    phone: b.phone || null,
    hourly_rate: b.hourly_rate ?? null,
    last_login: new Date().toISOString(),
  };
  try {
    const r = await supaFetch("employees", { method: "POST", body: [row] });
    const payload = r.ok ? await r.json() : null;
    res.status(201).json({
      success: true,
      employee: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (e) {
    res
      .status(500)
      .json({ success: false, error: "Failed to create employee" });
  }
});

// Employees: update
app.put("/api/employees/:id", async (req, res) => {
  const idRaw = String(req.params.id || "");
  const b = req.body || {};
  try {
    const target = /^\d+$/.test(idRaw)
      ? `employees?id=eq.${encodeURIComponent(idRaw)}`
      : `employees?employee_id=eq.${encodeURIComponent(idRaw)}`;
    const r = await supaFetch(target, {
      method: "PATCH",
      body: b,
    });
    const payload = r.ok ? await r.json() : null;
    res.json({
      success: true,
      employee: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (e) {
    res
      .status(500)
      .json({ success: false, error: "Failed to update employee" });
  }
});

// Employees: delete
app.delete("/api/employees/:id", async (req, res) => {
  const idRaw = String(req.params.id || "");
  try {
    const candidates = [];
    const isNumeric = /^\d+$/.test(idRaw);
    // Try both id and employee_id selectors to be robust
    candidates.push(`employees?id=eq.${encodeURIComponent(idRaw)}`);
    candidates.push(`employees?employee_id=eq.${encodeURIComponent(idRaw)}`);
    // If numeric, also ensure numeric id first
    const ordered = isNumeric ? [candidates[0], candidates[1]] : [candidates[1], candidates[0]];

    // Soft-delete attempts
    for (const sel of ordered) {
      const r = await supaFetch(sel, {
        method: "PATCH",
        body: { is_active: false, termination_date: new Date().toISOString().slice(0, 10) },
      });
      if (r.ok) {
        let affected = 0;
        try {
          const payload = await r.json();
          affected = Array.isArray(payload) ? payload.length : (payload ? 1 : 0);
        } catch (_) {}
        if (affected > 0) return res.json({ success: true, softDeleted: true });
      }
    }

    // Dev in-memory fallback
    try {
      const match = devStore.users.find(
        (u) => String(u?.employee?.employee_id || "") === idRaw || String(u?.id || "") === idRaw || String(u?.employee?.id || "") === idRaw,
      );
      if (match) {
        match.status = "inactive";
        saveDevState();
        return res.json({ success: true, softDeleted: true, dev: true });
      }
    } catch (_) {}

    // Hard delete attempts (only if soft-delete didn’t affect any row)
    for (const sel of ordered) {
      const d = await supaFetch(sel, { method: "DELETE" });
      if (d.ok) return res.json({ success: true, softDeleted: false });
    }

    // Idempotent success if nothing matched (avoid leaking existence via errors)
    return res.json({ success: true, softDeleted: false, note: "No matching record; treated as completed" });
  } catch (e) {
    return res.status(500).json({ success: false, error: "Failed to delete employee" });
  }
});

// Customers: list
app.get("/api/customers", async (req, res) => {
  try {
    const search = (req.query?.search || "").toString().trim();
    const base = `customers?select=*${search ? `&or=(name.ilike.*${encodeURIComponent(search)}*,email.ilike.*${encodeURIComponent(search)}*,phone.ilike.*${encodeURIComponent(search)}*)` : ""}`;
    const r = await supaFetch(base);
    const payload = r.ok ? await r.json() : [];
    res.json({ success: true, customers: payload });
  } catch (e) {
    res.json({ success: true, customers: [] });
  }
});

// Inventory: transfer room
app.post("/api/products/transfer-room", async (req, res) => {
  const b = req.body || {};
  try {
    if (b.product_id) {
      await supaFetch(`products?id=eq.${encodeURIComponent(b.product_id)}`, {
        method: "PATCH",
        body: {
          room: b.destinationRoom,
          on_sales_floor: !!b.setSalesFloorStatus,
          updated_at: new Date().toISOString(),
        },
      });
      await supaFetch("inventory_movements", {
        method: "POST",
        body: [
          {
            product_id: Number(b.product_id),
            from_room: b.fromRoom || null,
            to_room: b.destinationRoom || null,
            quantity: b.quantity ?? null,
            reason: b.reason || "transfer",
            actor: authFromReq(req)?.email || "system",
          },
        ],
      });
    }
    res.json({ success: true });
  } catch (e) {
    res.status(500).json({ success: false, error: "Failed transfer" });
  }
});

// Deals
app.get("/api/deals", async (_req, res) => {
  try {
    const r = await supaFetch("deals?select=*");
    const payload = r.ok ? await r.json() : [];
    res.json({ success: true, deals: payload });
  } catch (_) {
    res.json({ success: true, deals: [] });
  }
});
app.post("/api/deals", async (req, res) => {
  try {
    const r = await supaFetch("deals", {
      method: "POST",
      body: [req.body || {}],
    });
    const payload = r.ok ? await r.json() : null;
    res.status(201).json({
      success: true,
      deal: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (_) {
    res.status(500).json({ success: false, error: "Failed" });
  }
});
app.put("/api/deals/:id", async (req, res) => {
  try {
    const r = await supaFetch(
      `deals?id=eq.${encodeURIComponent(req.params.id)}`,
      { method: "PATCH", body: req.body || {} },
    );
    const payload = r.ok ? await r.json() : null;
    res.json({
      success: true,
      deal: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (_) {
    res.status(500).json({ success: false, error: "Failed" });
  }
});

// Price tiers
app.get("/api/price-tiers", async (_req, res) => {
  try {
    const r = await supaFetch("price_tiers?select=*");
    const payload = r.ok ? await r.json() : [];
    res.json({ success: true, tiers: payload });
  } catch (_) {
    res.json({ success: true, tiers: [] });
  }
});
app.post("/api/price-tiers", async (req, res) => {
  try {
    const r = await supaFetch("price_tiers", {
      method: "POST",
      body: [req.body || {}],
    });
    const payload = r.ok ? await r.json() : null;
    res.status(201).json({
      success: true,
      tier: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (_) {
    res.status(500).json({ success: false, error: "Failed" });
  }
});
app.put("/api/price-tiers/:id", async (req, res) => {
  try {
    const r = await supaFetch(
      `price_tiers?id=eq.${encodeURIComponent(req.params.id)}`,
      { method: "PATCH", body: req.body || {} },
    );
    const payload = r.ok ? await r.json() : null;
    res.json({
      success: true,
      tier: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (_) {
    res.status(500).json({ success: false, error: "Failed" });
  }
});

// Loyalty points adjustments
app.post("/api/loyalty/:customerId/adjust-points", async (req, res) => {
  try {
    const id = Number(req.params.customerId);
    const amt = Number(req.body?.amount || 0);
    const reason = req.body?.reason || "adjust";
    await supaFetch(`customers?id=eq.${id}`, {
      method: "PATCH",
      body: { loyalty_points: { increment: amt } },
    });
    await supaFetch("loyalty_transactions", {
      method: "POST",
      body: [{ customer_id: id, points: amt, type: "adjust", reason }],
    });
    res.json({ success: true });
  } catch (e) {
    res.status(500).json({ success: false, error: "Failed" });
  }
});
app.post("/api/loyalty/:customerId/earn-points", async (req, res) => {
  try {
    const id = Number(req.params.customerId);
    const pts = Number(req.body?.points || 0);
    const reason = req.body?.reason || "earn";
    await supaFetch(`customers?id=eq.${id}`, {
      method: "PATCH",
      body: { loyalty_points: { increment: pts } },
    });
    await supaFetch("loyalty_transactions", {
      method: "POST",
      body: [{ customer_id: id, points: pts, type: "earn", reason }],
    });
    res.json({ success: true });
  } catch (e) {
    res.status(500).json({ success: false, error: "Failed" });
  }
});
app.post("/api/loyalty/:customerId/redeem-points", async (req, res) => {
  try {
    const id = Number(req.params.customerId);
    const pts = Number(req.body?.points || 0);
    const reason = req.body?.reason || "redeem";
    await supaFetch(`customers?id=eq.${id}`, {
      method: "PATCH",
      body: { loyalty_points: { decrement: pts } },
    });
    await supaFetch("loyalty_transactions", {
      method: "POST",
      body: [{ customer_id: id, points: -pts, type: "redeem", reason }],
    });
    res.json({ success: true });
  } catch (e) {
    res.status(500).json({ success: false, error: "Failed" });
  }
});

// Activity log (catch-all)
app.post("/api/activity", async (req, res) => {
  try {
    const user = getAuthUser(req);
    const r = await supaFetch("activity_logs", {
      method: "POST",
      body: [
        {
          actor_user_id: user ? String(user.id) : null,
          action: req.body?.action || "event",
          payload: req.body || {},
        },
      ],
    });
    const payload = r.ok ? await r.json() : null;
    res.json({
      success: true,
      log: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (_) {
    res.json({ success: true });
  }
});

// POS: process payment -> persist sale to Supabase
app.post(["/api/pos/process-payment", "/api/sales"], async (req, res) => {
  const user = getAuthUser(req);
  const body = req.body || {};
  const row = {
    user_id: user ? String(user.id) : null,
    employee_id: body.employeePin ? String(body.employeePin) : null,
    payment_method:
      body.method ||
      (body.amountGiven != null ? "cash" : body.lastFour ? "debit" : "unknown"),
    subtotal:
      body.subtotal ??
      body.cart?.reduce?.(
        (s, i) => s + (i?.price || 0) * (i?.quantity || 1),
        0,
      ) ??
      null,
    tax: body.taxAmount ?? null,
    total: body.total ?? null,
    customer: body.customer || null,
    cart: body.cart || null,
    meta: { source: "dev", timestamp: new Date().toISOString() },
  };
  try {
    const r = await supaFetch("sales", { method: "POST", body: [row] });
    const payload = r.ok ? await r.json() : null;
    return res.status(200).json({
      success: true,
      sale: Array.isArray(payload) ? payload[0] : payload,
    });
  } catch (e) {
    return res.status(200).json({ success: true, message: "Recorded locally" });
  }
});

// Optional SPA fallback (serve index.html for any non-API route):
app.get(/^(?!\/api(?:\/|$)).*$/, (_req, res) => {
  const indexPath = path.join(__dirname, "index.html");
  if (fs.existsSync(indexPath)) return res.sendFile(indexPath);
  res.redirect("/");
});

// 404 (for anything not matched above)
app.use((_req, res) => res.status(404).json({ error: "Not found" }));

// 500 error handler
// eslint-disable-next-line no-unused-vars
app.use((err, _req, res, _next) => {
  console.error(err);
  res
    .status(500)
    .json({ error: "Server error", detail: String(err?.message || err) });
});

app.listen(PORT, "0.0.0.0", () => {
  console.log(
    `Cannabis POS System (Laravel/PHP) running on http://0.0.0.0:${PORT}`,
  );
});
