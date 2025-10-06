@extends('layouts.app')

@section('title', 'Oregon METRC Compliance Checklist')

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="max-w-5xl mx-auto px-6 py-10">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Oregon Cannabis POS Compliance Checklist (METRC Integration)</h1>
      <a href="{{ route('metrc.transfers') }}" class="text-sm text-cannabis-green hover:underline">Back to METRC</a>
    </div>

    <div class="rounded-lg bg-white shadow">
      <div class="p-6 space-y-8">
        <p class="text-gray-600">Use this before going live, during audits, or when updating your METRC integration.</p>

        <section>
          <h2 class="text-lg font-semibold mb-2">1. System Configuration</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>METRC API keys (user/vendor) securely stored in environment variables, never in source code</li>
            <li>METRC facility license number matches the licensed business in Oregon</li>
            <li>Oregon-specific endpoint configured (api-or.metrc.com)</li>
            <li>All credentials (user/vendor/facility) are valid, current, and rotated regularly</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">2. API Connectivity</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>METRC connection test passes (GET /api/metrc/test-connection)</li>
            <li>POS retrieves packages, items, transfers, and tags from METRC</li>
            <li>POS can report sales, receipts, deliveries, and inventory changes to METRC</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">3. Inventory Controls</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>All cannabis inventory is tagged and tracked with valid METRC package tags</li>
            <li>Inventory movements (room transfers, adjustments, destruction) reported to METRC</li>
            <li>Package status updates (active/inactive/finished) kept in sync</li>
            <li>Discrepancies between local inventory and METRC flagged and investigated</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">4. Sales Reporting</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>Every cannabis sale reported to METRC within 24 hours (Oregon mandate)</li>
            <li>Receipts include package tag, product, quantity, price, taxes, customer type</li>
            <li>Voids/returns reported to METRC for adjustment</li>
            <li>Medical vs. recreational sales properly distinguished</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">5. Compliance Rules</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>Oregon daily purchase limits enforced at POS</li>
            <li>Age verification required for every transaction</li>
            <li>Customer type tracked and reported</li>
            <li>Taxes (sales, excise, cannabis) calculated and displayed as mandated</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">6. Audit & Logging</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>All METRC API calls logged (endpoint, timestamp, status, payload size)</li>
            <li>Audit trail available for inventory changes, sales, and syncs</li>
            <li>Reports suitable for OLCC audits can be produced</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">7. Security</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>API keys, facility, and user info never exposed to frontend/public endpoints</li>
            <li>HTTPS enforced for all requests</li>
            <li>Access to METRC features restricted to authorized roles</li>
            <li>Failed syncs, errors, and rate limits logged and alert admins</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">8. Backup & Recovery</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>Regular database backups scheduled and tested</li>
            <li>POS can re-sync inventory and sales to METRC after downtime</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">9. Testing & Sandbox</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>Development/testing uses METRC sandbox (never live)</li>
            <li>Sync and reporting logic tested for edge cases</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">10. Documentation & Training</h2>
          <ul class="list-disc ml-6 space-y-1 text-gray-700">
            <li>Staff trained on Oregon compliance and METRC reporting</li>
            <li>User manuals/documentation available for compliance procedures</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold mb-2">Oregon-Specific References</h2>
          <ul class="list-disc ml-6 space-y-1 text-blue-700">
            <li><a class="hover:underline" href="https://www.oregon.gov/olcc/marijuana/Pages/Metrc.aspx" target="_blank" rel="noreferrer noopener">Oregon OLCC METRC Guidance</a></li>
            <li><a class="hover:underline" href="https://www.oregon.gov/olcc/marijuana/pages/rules.aspx" target="_blank" rel="noreferrer noopener">OLCC Cannabis Rules</a></li>
            <li><a class="hover:underline" href="https://api-docs.metrc.com/" target="_blank" rel="noreferrer noopener">METRC API Documentation</a></li>
            <li><a class="hover:underline" href="https://www.oregon.gov/olcc/marijuana/Documents/SalesLimits.pdf" target="_blank" rel="noreferrer noopener">Oregon Purchase Limits</a></li>
          </ul>
        </section>

        <div class="pt-4 border-t">
          <p class="text-sm text-gray-500">Tip: Run this checklist before every major release and during compliance audits.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
