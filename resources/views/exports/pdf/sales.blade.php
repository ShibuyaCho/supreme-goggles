@extends('exports.pdf.base')

@section('title', 'Sales Report - Cannabis POS')

@section('report-title', 'Sales Report')

@section('additional-styles')
<style>
    .sales-summary {
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
        gap: 15px;
    }
    
    .summary-card {
        flex: 1;
        background: #f0f9ff;
        border: 1px solid #0891b2;
        border-radius: 6px;
        padding: 15px;
        text-align: center;
    }
    
    .summary-card .title {
        font-size: 10px;
        color: #0891b2;
        text-transform: uppercase;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .summary-card .value {
        font-size: 16px;
        font-weight: bold;
        color: #0c4a6e;
    }
    
    .transaction-details {
        background: #f8fafc;
        border-left: 4px solid #0891b2;
        padding: 10px;
        margin: 5px 0;
        font-size: 10px;
    }
    
    .payment-method {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .payment-cash {
        background: #dcfce7;
        color: #16a34a;
    }
    
    .payment-card {
        background: #dbeafe;
        color: #2563eb;
    }
    
    .payment-other {
        background: #f3e8ff;
        color: #7c3aed;
    }
</style>
@endsection

@section('content')
<div class="sales-report">
    <!-- Sales Summary -->
    @php
        $totalTransactions = count($data);
        $totalRevenue = collect($data)->sum('total');
        $totalTax = collect($data)->sum('tax');
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        $totalDiscount = collect($data)->sum('discount');
    @endphp
    
    <div class="sales-summary">
        <div class="summary-card">
            <div class="title">Total Transactions</div>
            <div class="value">{{ number_format($totalTransactions) }}</div>
        </div>
        <div class="summary-card">
            <div class="title">Total Revenue</div>
            <div class="value">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="title">Total Tax</div>
            <div class="value">${{ number_format($totalTax, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="title">Avg Transaction</div>
            <div class="value">${{ number_format($avgTransaction, 2) }}</div>
        </div>
        @if($totalDiscount > 0)
        <div class="summary-card">
            <div class="title">Total Discounts</div>
            <div class="value">${{ number_format($totalDiscount, 2) }}</div>
        </div>
        @endif
    </div>

    <!-- Sales Transactions Table -->
    <h3 style="margin-bottom: 15px; color: #0891b2;">Transaction Details</h3>
    
    <table class="report-table">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Transaction ID</th>
                <th>Customer</th>
                <th>Employee</th>
                <th>Items</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Tax</th>
                @if($totalDiscount > 0)
                <th class="text-right">Discount</th>
                @endif
                <th class="text-right">Total</th>
                <th>Payment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $sale)
            <tr>
                <td>{{ \Carbon\Carbon::parse($sale['date'])->format('m/d/Y H:i') }}</td>
                <td>{{ $sale['transaction_id'] }}</td>
                <td>{{ $sale['customer_name'] }}</td>
                <td>{{ $sale['employee'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $sale['item_count'] }}</td>
                <td class="text-right currency">${{ number_format($sale['subtotal'], 2) }}</td>
                <td class="text-right currency">${{ number_format($sale['tax'], 2) }}</td>
                @if($totalDiscount > 0)
                <td class="text-right currency">${{ number_format($sale['discount'] ?? 0, 2) }}</td>
                @endif
                <td class="text-right currency font-bold">${{ number_format($sale['total'], 2) }}</td>
                <td>
                    <span class="payment-method payment-{{ strtolower($sale['payment_method']) }}">
                        {{ ucfirst($sale['payment_method']) }}
                    </span>
                </td>
            </tr>
            
            <!-- Show item details for transactions with multiple items -->
            @if(isset($sale['items']) && count($sale['items']) > 1)
            <tr>
                <td colspan="{{ $totalDiscount > 0 ? 10 : 9 }}" class="transaction-details">
                    <strong>Items:</strong>
                    @foreach($sale['items'] as $item)
                        {{ $item['product_name'] }} ({{ $item['quantity'] }}x @ ${{ number_format($item['price'], 2) }})
                        @if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #e0f2fe; font-weight: bold;">
                <td colspan="{{ $totalDiscount > 0 ? 5 : 5 }}" class="text-right"><strong>TOTALS:</strong></td>
                <td class="text-right currency">${{ number_format($totalRevenue - $totalTax, 2) }}</td>
                <td class="text-right currency">${{ number_format($totalTax, 2) }}</td>
                @if($totalDiscount > 0)
                <td class="text-right currency">${{ number_format($totalDiscount, 2) }}</td>
                @endif
                <td class="text-right currency font-bold">${{ number_format($totalRevenue, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Payment Method Breakdown -->
    @php
        $paymentBreakdown = collect($data)->groupBy('payment_method')->map(function($sales, $method) {
            return [
                'method' => $method,
                'count' => $sales->count(),
                'total' => $sales->sum('total')
            ];
        });
    @endphp

    @if($paymentBreakdown->count() > 1)
    <div class="page-break"></div>
    <h3 style="margin-bottom: 15px; color: #0891b2;">Payment Method Breakdown</h3>
    
    <table class="report-table">
        <thead>
            <tr>
                <th>Payment Method</th>
                <th class="text-center">Transaction Count</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentBreakdown as $payment)
            <tr>
                <td>
                    <span class="payment-method payment-{{ strtolower($payment['method']) }}">
                        {{ ucfirst($payment['method']) }}
                    </span>
                </td>
                <td class="text-center">{{ number_format($payment['count']) }}</td>
                <td class="text-right currency">${{ number_format($payment['total'], 2) }}</td>
                <td class="text-right">{{ number_format(($payment['total'] / $totalRevenue) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Customer Type Analysis -->
    @php
        $customerTypes = collect($data)->groupBy('customer_type')->map(function($sales, $type) {
            return [
                'type' => $type,
                'count' => $sales->count(),
                'total' => $sales->sum('total'),
                'avg' => $sales->avg('total')
            ];
        });
    @endphp

    @if($customerTypes->count() > 1)
    <h3 style="margin: 25px 0 15px 0; color: #0891b2;">Customer Type Analysis</h3>
    
    <table class="report-table">
        <thead>
            <tr>
                <th>Customer Type</th>
                <th class="text-center">Transactions</th>
                <th class="text-right">Total Revenue</th>
                <th class="text-right">Average Sale</th>
                <th class="text-right">% of Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customerTypes as $type)
            <tr>
                <td>{{ ucfirst($type['type']) }}</td>
                <td class="text-center">{{ number_format($type['count']) }}</td>
                <td class="text-right currency">${{ number_format($type['total'], 2) }}</td>
                <td class="text-right currency">${{ number_format($type['avg'], 2) }}</td>
                <td class="text-right">{{ number_format(($type['total'] / $totalRevenue) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Sales Performance Insights -->
    <div class="report-summary mt-20">
        <div class="summary-title">Sales Performance Insights</div>
        <div style="text-align: left; font-size: 11px; line-height: 1.6;">
            <div style="margin-bottom: 10px;">
                <strong>Peak Performance:</strong>
                @php
                    $maxSale = collect($data)->sortByDesc('total')->first();
                @endphp
                Highest single transaction: <strong>${{ number_format($maxSale['total'], 2) }}</strong>
                ({{ $maxSale['customer_name'] }} on {{ \Carbon\Carbon::parse($maxSale['date'])->format('M j, Y') }})
            </div>
            
            @if($totalDiscount > 0)
            <div style="margin-bottom: 10px;">
                <strong>Discount Impact:</strong>
                Total discounts of ${{ number_format($totalDiscount, 2) }} represent 
                {{ number_format(($totalDiscount / ($totalRevenue + $totalDiscount)) * 100, 1) }}% of gross sales
            </div>
            @endif
            
            <div style="margin-bottom: 10px;">
                <strong>Average Items per Transaction:</strong>
                {{ number_format(collect($data)->avg('item_count'), 1) }} items
            </div>
            
            @if(isset($options['date_range']['start']))
            <div>
                <strong>Reporting Period:</strong>
                {{ \Carbon\Carbon::parse($options['date_range']['start'])->diffInDays(\Carbon\Carbon::parse($options['date_range']['end'])) + 1 }} days
                ({{ number_format($totalRevenue / (max(1, \Carbon\Carbon::parse($options['date_range']['start'])->diffInDays(\Carbon\Carbon::parse($options['date_range']['end'])) + 1)), 2) }} daily average)
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
