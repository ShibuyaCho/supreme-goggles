@extends('exports.pdf.base')

@section('title')
{{ ucwords(str_replace('_', ' ', $reportType)) }} Report - Cannabis POS
@endsection

@section('report-title')
{{ ucwords(str_replace('_', ' ', $reportType)) }} Report
@endsection

@section('content')
<div class="generic-report">
    
    @if(count($data) > 0)
        @php
            // Get the first item to determine the structure
            $firstItem = is_array($data[0]) ? $data[0] : (array) $data[0];
            $headers = array_keys($firstItem);
            
            // Format headers to be more readable
            $formattedHeaders = array_map(function($header) {
                return ucwords(str_replace('_', ' ', $header));
            }, $headers);
        @endphp
        
        <!-- Data Summary -->
        <div style="background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-left: 4px solid #16a34a;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Total Records:</strong> {{ number_format(count($data)) }}
                </div>
                @if(isset($options['date_range']['start']) && $options['date_range']['start'])
                <div>
                    <strong>Date Range:</strong> 
                    {{ \Carbon\Carbon::parse($options['date_range']['start'])->format('M j, Y') }} - 
                    {{ \Carbon\Carbon::parse($options['date_range']['end'])->format('M j, Y') }}
                </div>
                @endif
                <div>
                    <strong>Generated:</strong> {{ now()->format('M j, Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Main Data Table -->
        <table class="report-table">
            <thead>
                <tr>
                    @foreach($formattedHeaders as $header)
                    <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    @php
                        $rowData = is_array($row) ? $row : (array) $row;
                    @endphp
                    <tr>
                        @foreach($headers as $key)
                        <td>
                            @php
                                $value = $rowData[$key] ?? '';
                                
                                // Format different types of data
                                if (is_numeric($value)) {
                                    // Check if it looks like currency
                                    if (in_array($key, ['price', 'cost', 'total', 'revenue', 'amount', 'subtotal', 'tax', 'discount']) || 
                                        (is_float($value) && $value > 0 && strlen((string)$value) <= 10)) {
                                        echo '$' . number_format($value, 2);
                                    } else {
                                        echo number_format($value, is_float($value) ? 2 : 0);
                                    }
                                } elseif (strtotime($value) && strlen($value) > 8) {
                                    // Format dates
                                    echo \Carbon\Carbon::parse($value)->format('M j, Y H:i');
                                } elseif (is_bool($value)) {
                                    echo $value ? 'Yes' : 'No';
                                } elseif (is_array($value)) {
                                    echo implode(', ', $value);
                                } else {
                                    echo strlen($value) > 50 ? substr($value, 0, 47) . '...' : $value;
                                }
                            @endphp
                        </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Basic Statistics -->
        @php
            $numericColumns = [];
            foreach($headers as $header) {
                $values = array_column($data, $header);
                $numericValues = array_filter($values, 'is_numeric');
                if (count($numericValues) > 0) {
                    $numericColumns[$header] = [
                        'total' => array_sum($numericValues),
                        'average' => array_sum($numericValues) / count($numericValues),
                        'count' => count($numericValues)
                    ];
                }
            }
        @endphp

        @if(count($numericColumns) > 0)
        <div class="page-break"></div>
        <h3 style="margin-bottom: 15px; color: #16a34a;">Statistical Summary</h3>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Average</th>
                    <th class="text-center">Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($numericColumns as $column => $stats)
                <tr>
                    <td><strong>{{ ucwords(str_replace('_', ' ', $column)) }}</strong></td>
                    <td class="text-right">
                        @if(in_array($column, ['price', 'cost', 'total', 'revenue', 'amount', 'subtotal', 'tax', 'discount']))
                            ${{ number_format($stats['total'], 2) }}
                        @else
                            {{ number_format($stats['total'], 2) }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if(in_array($column, ['price', 'cost', 'total', 'revenue', 'amount', 'subtotal', 'tax', 'discount']))
                            ${{ number_format($stats['average'], 2) }}
                        @else
                            {{ number_format($stats['average'], 2) }}
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($stats['count']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Report Notes -->
        <div class="report-summary mt-20">
            <div class="summary-title">Report Information</div>
            <div style="text-align: left; font-size: 11px; line-height: 1.6;">
                <div style="margin-bottom: 10px;">
                    <strong>Report Type:</strong> {{ ucwords(str_replace('_', ' ', $reportType)) }}
                </div>
                
                <div style="margin-bottom: 10px;">
                    <strong>Data Structure:</strong> {{ count($headers) }} columns, {{ count($data) }} rows
                </div>
                
                @if(isset($options['filters']) && count($options['filters']) > 0)
                <div style="margin-bottom: 10px;">
                    <strong>Applied Filters:</strong>
                    @foreach($options['filters'] as $key => $value)
                        {{ ucwords(str_replace('_', ' ', $key)) }}: {{ $value }}@if(!$loop->last), @endif
                    @endforeach
                </div>
                @endif
                
                <div style="margin-bottom: 10px;">
                    <strong>Generated By:</strong> {{ auth()->user()->name ?? 'System' }} on {{ now()->format('F j, Y \a\t g:i A') }}
                </div>
                
                @if(count($numericColumns) > 0)
                <div>
                    <strong>Numeric Metrics:</strong> {{ count($numericColumns) }} quantitative measures analyzed
                </div>
                @endif
            </div>
        </div>

    @else
        <!-- No Data Message -->
        <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">
            <div style="font-size: 48px; color: #dee2e6; margin-bottom: 20px;">📊</div>
            <h3 style="color: #6c757d; margin-bottom: 10px;">No Data Available</h3>
            <p style="color: #6c757d; font-size: 14px;">
                No records were found for the specified criteria.
            </p>
            
            @if(isset($options['filters']) && count($options['filters']) > 0)
            <div style="margin-top: 20px; font-size: 12px; color: #6c757d;">
                <strong>Applied Filters:</strong><br>
                @foreach($options['filters'] as $key => $value)
                    {{ ucwords(str_replace('_', ' ', $key)) }}: {{ $value }}<br>
                @endforeach
            </div>
            @endif
            
            @if(isset($options['date_range']['start']) && $options['date_range']['start'])
            <div style="margin-top: 15px; font-size: 12px; color: #6c757d;">
                <strong>Date Range:</strong> 
                {{ \Carbon\Carbon::parse($options['date_range']['start'])->format('M j, Y') }} - 
                {{ \Carbon\Carbon::parse($options['date_range']['end'])->format('M j, Y') }}
            </div>
            @endif
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px;">
            <h4 style="color: #856404; margin-bottom: 10px;">💡 Suggestions</h4>
            <ul style="color: #856404; font-size: 12px; margin-left: 20px;">
                <li>Try expanding your date range</li>
                <li>Remove or adjust applied filters</li>
                <li>Verify that data exists for the selected criteria</li>
                <li>Contact support if you expect data to be available</li>
            </ul>
        </div>
    @endif

</div>
@endsection
