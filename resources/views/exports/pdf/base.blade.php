<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cannabis POS Report')</title>
    <style>
        /* Base styles for PDF reports */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header styles */
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 15px;
        }

        .report-header h1 {
            font-size: 24px;
            color: #16a34a;
            margin-bottom: 5px;
        }

        .report-header .company-name {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }

        .report-header .report-info {
            font-size: 11px;
            color: #888;
        }

        /* Metadata section */
        .report-metadata {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #16a34a;
        }

        .metadata-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .metadata-item {
            flex: 1;
            min-width: 200px;
        }

        .metadata-label {
            font-weight: bold;
            color: #555;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metadata-value {
            color: #333;
            font-size: 12px;
            margin-top: 3px;
        }

        /* Table styles */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .report-table th {
            background: #16a34a;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        .report-table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 11px;
            vertical-align: top;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .report-table tbody tr:hover {
            background-color: #e8f5e8;
        }

        /* Numeric columns */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .currency {
            font-family: 'DejaVu Sans Mono', monospace;
            font-weight: bold;
        }

        /* Summary section */
        .report-summary {
            background: #f0fdf4;
            border: 2px solid #16a34a;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }

        .summary-title {
            font-size: 16px;
            font-weight: bold;
            color: #16a34a;
            margin-bottom: 15px;
            text-align: center;
        }

        .summary-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .summary-item {
            flex: 1;
            min-width: 150px;
            text-align: center;
        }

        .summary-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #16a34a;
            margin-top: 5px;
        }

        /* Footer */
        .report-footer {
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            font-size: 10px;
            color: #888;
        }

        /* Page break utilities */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* Status badges */
        .status-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-warning {
            background: #fef3c7;
            color: #d97706;
        }

        /* Utilities */
        .font-bold {
            font-weight: bold;
        }

        .text-small {
            font-size: 10px;
        }

        .text-large {
            font-size: 14px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        /* Chart placeholder (for when charts are included) */
        .chart-placeholder {
            border: 2px dashed #ddd;
            padding: 40px;
            text-align: center;
            color: #888;
            font-style: italic;
            margin: 20px 0;
        }

        /* Landscape orientation adjustments */
        @media print and (orientation: landscape) {
            .report-table {
                font-size: 10px;
            }
            
            .report-table th,
            .report-table td {
                padding: 4px;
            }
        }

        /* Additional spacing for different report types */
        .inventory-report .report-table th {
            background: #059669;
        }

        .sales-report .report-table th {
            background: #0891b2;
        }

        .customer-report .report-table th {
            background: #7c3aed;
        }

        .employee-report .report-table th {
            background: #dc2626;
        }

        .metrc-report .report-table th {
            background: #ea580c;
        }
    </style>
    @yield('additional-styles')
</head>
<body>
    <div class="container">
        <!-- Report Header -->
        <div class="report-header">
            <h1>@yield('report-title', 'Cannabis POS Report')</h1>
            <div class="company-name">Cannabis POS System</div>
            <div class="report-info">
                Generated on: {{ now()->format('F j, Y \a\t g:i A') }}
                @if(auth()->check())
                    | Generated by: {{ auth()->user()->name }}
                @endif
            </div>
        </div>

        <!-- Report Metadata -->
        @if(isset($options['metadata']))
        <div class="report-metadata">
            <div class="metadata-grid">
                <div class="metadata-item">
                    <div class="metadata-label">Report Type</div>
                    <div class="metadata-value">{{ ucwords(str_replace('_', ' ', $reportType)) }}</div>
                </div>
                @if(isset($options['date_range']['start']) && $options['date_range']['start'])
                <div class="metadata-item">
                    <div class="metadata-label">Date Range</div>
                    <div class="metadata-value">
                        {{ \Carbon\Carbon::parse($options['date_range']['start'])->format('M j, Y') }} - 
                        {{ \Carbon\Carbon::parse($options['date_range']['end'])->format('M j, Y') }}
                    </div>
                </div>
                @endif
                <div class="metadata-item">
                    <div class="metadata-label">Total Records</div>
                    <div class="metadata-value">{{ number_format($options['total_records'] ?? count($data)) }}</div>
                </div>
                @if(isset($options['filters']) && count($options['filters']) > 0)
                <div class="metadata-item">
                    <div class="metadata-label">Applied Filters</div>
                    <div class="metadata-value">
                        @foreach($options['filters'] as $key => $value)
                            {{ ucwords(str_replace('_', ' ', $key)) }}: {{ $value }}<br>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Report Content -->
        <div class="report-content">
            @yield('content')
        </div>

        <!-- Report Footer -->
        <div class="report-footer">
            <div>Cannabis POS System - Professional Cannabis Point of Sale</div>
            <div>This report is confidential and intended for authorized personnel only.</div>
            <div style="margin-top: 10px;">
                <strong>METRC Compliant</strong> | 
                Oregon Cannabis Tracking System Compatible | 
                Report ID: {{ uniqid('RPT-') }}
            </div>
        </div>
    </div>
</body>
</html>
