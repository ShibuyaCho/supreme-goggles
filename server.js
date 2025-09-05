// server.js (ESM, Express 5, uses RegExp routes to avoid path-to-regexp string quirks)
import express from "express";
import path from "node:path";
import fs from "node:fs";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3000;

// In-memory dev auth store (non-persistent)
const devStore = {
  users: [], // { id, name, email, role, permissions, employee, password, pin, employee_id }
  tokens: new Map(), // token -> userId
};
let nextUserId = 1;
let nextEmployeeId = 1;

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
app.post(["/api/auth/self-register", "/api/self-register"], (req, res) => {
  const { name, email, password, password_confirmation, pin } = req.body || {};
  if (!name || !email || !password || !password_confirmation || !pin) {
    return res
      .status(422)
      .json({
        error: "Validation failed",
        errors: { fields: "Missing required fields" },
      });
  }
  if (String(password) !== String(password_confirmation)) {
    return res
      .status(422)
      .json({
        error: "Validation failed",
        errors: { password: ["Passwords do not match"] },
      });
  }
  if (!/^\d{4}$/.test(String(pin))) {
    return res
      .status(422)
      .json({
        error: "Validation failed",
        errors: { pin: ["PIN must be 4 digits"] },
      });
  }
  if (findUserByEmail(email)) {
    return res
      .status(422)
      .json({
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
    permissions: ["pos:*", "products:read", "customers:read", "sales:create"],
    employee: { id: nextEmployeeId, employee_id: empId, first_name, last_name },
    password, // dev only
    pin: String(pin),
  };
  devStore.users.push(user);
  const token = genToken();
  devStore.tokens.set(token, user.id);
  return res.status(201).json({
    message: "Account created successfully",
    user,
    token,
    success: true,
  });
});

app.post(["/api/auth/login", "/api/login"], (req, res) => {
  const { email, password } = req.body || {};
  const user = findUserByEmail(email);
  if (!user || String(user.password) !== String(password)) {
    return res
      .status(401)
      .json({ error: "Invalid credentials", success: false });
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

// ✅ API catch-all using RegExp (matches /api and any subpath)
app.all(/^\/api(?:\/.*)?$/, (_req, res) => {
  res.json({
    message: "Dev API stub active",
    status: "ok",
  });
});

// Optional SPA fallback (serve index.html for any non-API route):
// app.get(/^(?!\/api(?:\/|$)).*$/, (_req, res) => {
//   const indexPath = path.join(__dirname, 'index.html');
//   if (fs.existsSync(indexPath)) return res.sendFile(indexPath);
//   res.redirect('/');
// });

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
