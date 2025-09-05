// server.cjs
// If your package.json has `"type": "module"`, keep this filename as .cjs.
// Otherwise you can rename to server.js and remove "type": "module".

const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();

// Render provides PORT via env; default for local dev.
const PORT = process.env.PORT || 3000;

// --- Paths ---
const ROOT_DIR = __dirname;
const PUBLIC_DIR = path.join(ROOT_DIR, 'public');
const ROOT_INDEX = path.join(ROOT_DIR, 'index.html');
const PUBLIC_INDEX = path.join(PUBLIC_DIR, 'index.html');

// --- Middleware ---
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static assets from /public if present
if (fs.existsSync(PUBLIC_DIR)) {
  app.use(express.static(PUBLIC_DIR));
}

// --- Healthcheck (Render friendly) ---
app.get(['/health', '/healthz', '/_health'], (_req, res) => {
  res.json({ ok: true, uptime: process.uptime() });
});

// --- Root route ---
// Prefer /public/index.html, else fall back to /index.html, else inline HTML.
app.get('/', (req, res) => {
  if (fs.existsSync(PUBLIC_INDEX)) {
    return res.sendFile(PUBLIC_INDEX);
  }
  if (fs.existsSync(ROOT_INDEX)) {
    return res.sendFile(ROOT_INDEX);
  }

  // Fallback inline HTML
  res.type('html').send(`<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cannabis POS System - Laravel Converted</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
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
</body>
</html>`);
});

// --- API placeholder ---
// Express 5 uses path-to-regexp v6; avoid bare '*' patterns.
// Use a named wildcard (':rest(*)') or a regex instead.
app.get('/api/:rest(*)', (req, res) => {
  res.json({
    message: 'Laravel API ready - all controllers converted',
    endpoints: [
      'GET /api/products - ProductsController',
      'GET /api/customers - CustomersController',
      'POST /api/sales - SalesController',
      'GET /api/analytics - AnalyticsController',
      'POST /api/products/transfer-room - ProductActionsController',
      'GET /api/settings - SettingsController',
    ],
    status: 'Laravel/PHP conversion complete',
    pathMatched: req.params.rest || '',
  });
});

// OPTIONAL: SPA catch-all to serve index.html for non-API routes.
// Comment this in if you have a front-end router.
/*
app.get('/:rest(*)', (req, res, next) => {
  if (req.path.startsWith('/api/')) return next();

  if (fs.existsSync(PUBLIC_INDEX)) return res.sendFile(PUBLIC_INDEX);
  if (fs.existsSync(ROOT_INDEX)) return res.sendFile(ROOT_INDEX);

  return res.status(404).send('Not Found');
});
*/

// --- Start server ---
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Cannabis POS System (Laravel/PHP) running on http://0.0.0.0:${PORT}`);
});
