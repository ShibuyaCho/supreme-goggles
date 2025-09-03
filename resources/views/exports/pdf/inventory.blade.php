@extends('exports.pdf.base')

@section('title', 'Inventory Report - Cannabis POS')

@section('report-title', 'Inventory Evaluation Report')

@section('additional-styles')
<style>
    .inventory-summary {
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
        gap: 12px;
    }
    
    .summary-card {
        flex: 1;
        background: #f0fdf4;
        border: 1px solid #16a34a;
        border-radius: 6px;
        padding: 12px;
        text-align: center;
    }
    
    .summary-card .title {
        font-size: 9px;
        color: #16a34a;
        text-transform: uppercase;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .summary-card .value {
        font-size: 14px;
        font-weight: bold;
        color: #15803d;
    }
    
    .category-section {
        margin-bottom: 25px;
        page-break-inside: avoid;
    }
    
    .category-header {
        background: #16a34a;
        color: white;
        padding: 8px 12px;
        font-weight: bold;
        font-size: 12px;
        margin-bottom: 10px;
    }
    
    .stock-status {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .stock-in-stock {
        background: #dcfce7;
        color: #16a34a;
    }
    
    .stock-low-stock {
        background: #fef3c7;
        color: #d97706;
    }
    
    .stock-out-of-stock {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .stock-expired {
        background: #fecaca;
        color: #991b1b;
    }
    
    .stock-expiring-soon {
        background: #fed7aa;
        color: #ea580c;
    }
    
    .metrc-tag {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 8px;
        background: #f3f4f6;
        padding: 1px 4px;
        border-radius: 2px;
        color: #6b7280;
    }
    
    .margin-positive {
        color: #16a34a;
        font-weight: bold;
    }
    
    .margin-negative {
        color: #dc2626;
        font-weight: bold;
    }
    
    .thc-cbd {
        font-size: 9px;
        color: #7c3aed;
    }
</style>
@endsection

@section('content')
<div class="inventory-report">
    <!-- Inventory Summary -->
    @php
        $totalProducts = count($data);
        $totalValue = collect($data)->sum('total_value');
        $totalCost = collect($data)->sum('total_cost');
        $totalQuantity = collect($data)->sum('quantity');
        $avgMargin = $totalValue > 0 ? (($totalValue - $totalCost) / $totalValue) * 100 : 0;
        $categoryCounts = collect($data)->groupBy('category')->map->count();
        $lowStockItems = collect($data)->where('status', 'Low Stock')->count();
        $outOfStockItems = collect($data)->where('status', 'Out of Stock')->count();
    @endphp
    
    <div class="inventory-summary">
        <div class="summary-card">
            <div class="title">Total Products</div>
            <div class="value">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="summary-card">
            <div class="title">Total Value</div>
            <div class="value">${{ number_format($totalValue, 0) }}</div>
        </div>
        <div class="summary-card">
            <div class="title">Total Cost</div>
            <div class="value">${{ number_format($totalCost, 0) }}</div>
        </div>
        <div class="summary-card">
            <div class="title">Avg Margin</div>
            <div class="value">{{ number_format($avgMargin, 1) }}%</div>
        </div>
        <div class="summary-card">
            <div class="title">Low Stock Items</div>
            <div class="value" style="color: #d97706;">{{ $lowStockItems }}</div>
        </div>
        <div class="summary-card">
            <div class="title">Out of Stock</div>
            <div class="value" style="color: #dc2626;">{{ $outOfStockItems }}</div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <h3 style="margin-bottom: 15px; color: #16a34a;">Category Analysis</h3>
    
    @php
        $categoryBreakdown = collect($data)->groupBy('category')->map(function($products, $category) {
            return [
                'category' => $category,
                'count' => $products->count(),
                'total_value' => $products->sum('total_value'),
                'total_cost' => $products->sum('total_cost'),
                'avg_margin' => $products->avg('margin_percent'),
                'total_quantity' => $products->sum('quantity')
            ];
        })->sortByDesc('total_value');
    @endphp
    
    <table class="report-table">
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-center">Products</th>
                <th class="text-right">Total Qty</th>
                <th class="text-right">Total Cost</th>
                <th class="text-right">Total Value</th>
                <th class="text-right">Avg Margin</th>
                <th class="text-right">% of Inventory</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryBreakdown as $cat)
            <tr>
                <td><strong>{{ ucfirst($cat['category']) }}</strong></td>
                <td class="text-center">{{ number_format($cat['count']) }}</td>
                <td class="text-right">{{ number_format($cat['total_quantity'], 1) }}</td>
                <td class="text-right currency">${{ number_format($cat['total_cost'], 2) }}</td>
                <td class="text-right currency">${{ number_format($cat['total_value'], 2) }}</td>
                <td class="text-right {{ $cat['avg_margin'] >= 0 ? 'margin-positive' : 'margin-negative' }}">
                    {{ number_format($cat['avg_margin'], 1) }}%
                </td>
                <td class="text-right">{{ number_format(($cat['total_value'] / $totalValue) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Detailed Product Inventory -->
    <div class="page-break"></div>
    <h3 style="margin-bottom: 15px; color: #16a34a;">Detailed Product Inventory</h3>
    
    @foreach($categoryBreakdown as $categoryData)
    <div class="category-section">
        <div class="category-header">
            {{ ucfirst($categoryData['category']) }} 
            ({{ $categoryData['count'] }} products, ${{ number_format($categoryData['total_value'], 0) }} value)
        </div>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Cost</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Value</th>
                    <th class="text-right">Margin</th>
                    <th>Room</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach(collect($data)->where('category', $categoryData['category'])->sortByDesc('total_value') as $product)
                <tr>
                    <td>
                        <strong>{{ $product['name'] }}</strong>
                        @if($product['thc'] || $product['cbd'])
                        <br><span class="thc-cbd">
                            @if($product['thc'])THC: {{ $product['thc'] }}%@endif
                            @if($product['thc'] && $product['cbd']) | @endif
                            @if($product['cbd'])CBD: {{ $product['cbd'] }}%@endif
                        </span>
                        @endif
                        @if($product['strain'])
                        <br><span class="text-small" style="color: #6b7280;">{{ $product['strain'] }}</span>
                        @endif
                    </td>
                    <td>{{ $product['sku'] }}</td>
                    <td class="text-center">
                        {{ number_format($product['quantity'], 1) }}
                        @if($product['unit'])
                        <br><span class="text-small">{{ $product['unit'] }}</span>
                        @endif
                    </td>
                    <td class="text-right currency">${{ number_format($product['cost'], 2) }}</td>
                    <td class="text-right currency">${{ number_format($product['price'], 2) }}</td>
                    <td class="text-right currency">${{ number_format($product['total_value'], 2) }}</td>
                    <td class="text-right {{ $product['margin_percent'] >= 0 ? 'margin-positive' : 'margin-negative' }}">
                        {{ number_format($product['margin_percent'], 1) }}%
                    </td>
                    <td>{{ $product['room'] ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span class="stock-status stock-{{ strtolower(str_replace(' ', '-', $product['status'])) }}">
                            {{ $product['status'] }}
                        </span>
                    </td>
                </tr>
                
                <!-- Additional product details row -->
                @if($product['metrc_tag'] || $product['batch_id'] || $product['vendor'] || $product['expiration_date'])
                <tr>
                    <td colspan="9" style="background: #f9fafb; font-size: 9px; color: #6b7280; padding: 4px 6px;">
                        @if($product['metrc_tag'])
                            <span class="metrc-tag">METRC: {{ $product['metrc_tag'] }}</span>
                        @endif
                        @if($product['batch_id'])
                            | Batch: {{ $product['batch_id'] }}
                        @endif
                        @if($product['vendor'])
                            | Vendor: {{ $product['vendor'] }}
                        @endif
                        @if($product['expiration_date'])
                            | Expires: {{ \Carbon\Carbon::parse($product['expiration_date'])->format('M j, Y') }}
                        @endif
                        @if($product['last_sold'])
                            | Last Sold: {{ $product['last_sold'] }}
                        @endif
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <!-- Stock Alerts Section -->
    @php
        $alertItems = collect($data)->whereIn('status', ['Low Stock', 'Out of Stock', 'Expired', 'Expiring Soon']);
    @endphp
    
    @if($alertItems->count() > 0)
    <div class="page-break"></div>
    <h3 style="margin-bottom: 15px; color: #dc2626;">⚠️ Stock Alerts & Action Items</h3>
    
    <table class="report-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th class="text-center">Current Qty</th>
                <th class="text-center">Alert Type</th>
                <th>Room</th>
                <th>Action Required</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alertItems->sortBy('status') as $item)
            <tr>
                <td>
                    <strong>{{ $item['name'] }}</strong>
                    @if($item['sku'])
                    <br><span class="text-small">{{ $item['sku'] }}</span>
                    @endif
                </td>
                <td>{{ ucfirst($item['category']) }}</td>
                <td class="text-center">{{ number_format($item['quantity'], 1) }}</td>
                <td class="text-center">
                    <span class="stock-status stock-{{ strtolower(str_replace(' ', '-', $item['status'])) }}">
                        {{ $item['status'] }}
                    </span>
                </td>
                <td>{{ $item['room'] ?? 'N/A' }}</td>
                <td style="font-size: 10px;">
                    @switch($item['status'])
                        @case('Out of Stock')
                            <strong>URGENT:</strong> Restock immediately
                            @break
                        @case('Low Stock')
                            Reorder soon (recommend {{ number_format($item['quantity'] * 3, 0) }} units)
                            @break
                        @case('Expired')
                            <strong>REMOVE:</strong> Expired product - remove from sales floor
                            @break
                        @case('Expiring Soon')
                            <strong>PRIORITY:</strong> Sell or discount before expiration
                            @break
                    @endswitch
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Inventory Insights -->
    <div class="report-summary mt-20">
        <div class="summary-title">Inventory Performance Insights</div>
        <div style="text-align: left; font-size: 11px; line-height: 1.6;">
            @php
                $topValueProduct = collect($data)->sortByDesc('total_value')->first();
                $topMarginProduct = collect($data)->sortByDesc('margin_percent')->first();
                $oldestStock = collect($data)->whereNotNull('last_sold')->sortBy('last_sold')->first();
            @endphp
            
            <div style="margin-bottom: 10px;">
                <strong>Highest Value Item:</strong>
                {{ $topValueProduct['name'] }} (${{ number_format($topValueProduct['total_value'], 2) }} total value)
            </div>
            
            <div style="margin-bottom: 10px;">
                <strong>Best Margin:</strong>
                {{ $topMarginProduct['name'] }} ({{ number_format($topMarginProduct['margin_percent'], 1) }}% margin)
            </div>
            
            @if($oldestStock)
            <div style="margin-bottom: 10px;">
                <strong>Slowest Moving:</strong>
                {{ $oldestStock['name'] }} (last sold: {{ $oldestStock['last_sold'] }})
            </div>
            @endif
            
            <div style="margin-bottom: 10px;">
                <strong>Category Distribution:</strong>
                {{ $categoryCounts->map(function($count, $category) { return ucfirst($category) . ' (' . $count . ')'; })->join(', ') }}
            </div>
            
            <div>
                <strong>Inventory Health:</strong>
                @if($outOfStockItems == 0 && $lowStockItems < 5)
                    <span style="color: #16a34a;">✓ Excellent - Well stocked across all categories</span>
                @elseif($outOfStockItems == 0 && $lowStockItems < 15)
                    <span style="color: #d97706;">⚠ Good - Monitor low stock items</span>
                @else
                    <span style="color: #dc2626;">⚠ Needs Attention - Multiple stock alerts require action</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
