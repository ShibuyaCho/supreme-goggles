<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportService
{
    protected array $supportedFormats = ['pdf', 'excel', 'csv'];

    /**
     * Export data in the specified format
     */
    public function export(string $reportType, array $data, string $format = 'pdf', array $options = [])
    {
        if (!in_array($format, $this->supportedFormats)) {
            throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }

        $filename = $this->generateFilename($reportType, $format);

        return match($format) {
            'pdf' => $this->exportToPdf($reportType, $data, $filename, $options),
            'excel' => $this->exportToExcel($reportType, $data, $filename, $options),
            'csv' => $this->exportToCsv($reportType, $data, $filename, $options),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    /**
     * Export to PDF format
     */
    protected function exportToPdf(string $reportType, array $data, string $filename, array $options = [])
    {
        $view = $this->getReportView($reportType);
        $orientation = $options['orientation'] ?? 'portrait';
        $paperSize = $options['paper_size'] ?? 'a4';

        $pdf = Pdf::loadView($view, [
            'data' => $data,
            'reportType' => $reportType,
            'generatedAt' => now(),
            'options' => $options
        ])
        ->setPaper($paperSize, $orientation)
        ->setOptions([
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => public_path(),
        ]);

        return $pdf->download($filename);
    }

    /**
     * Export to Excel format
     */
    protected function exportToExcel(string $reportType, array $data, string $filename, array $options = [])
    {
        $export = new class($reportType, $data, $options) implements \Maatwebsite\Excel\Concerns\FromCollection,
                                                                     \Maatwebsite\Excel\Concerns\WithHeadings,
                                                                     \Maatwebsite\Excel\Concerns\WithStyles,
                                                                     \Maatwebsite\Excel\Concerns\WithTitle,
                                                                     \Maatwebsite\Excel\Concerns\WithColumnFormatting,
                                                                     \Maatwebsite\Excel\Concerns\ShouldAutoSize
        {
            protected string $reportType;
            protected array $data;
            protected array $options;

            public function __construct(string $reportType, array $data, array $options = [])
            {
                $this->reportType = $reportType;
                $this->data = $data;
                $this->options = $options;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return $this->getHeadingsForReport($this->reportType);
            }

            public function title(): string
            {
                return ucwords(str_replace('_', ' ', $this->reportType)) . ' Report';
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true, 'size' => 12]],
                    'A:Z' => ['alignment' => ['horizontal' => 'left']],
                ];
            }

            public function columnFormats(): array
            {
                return $this->getColumnFormatsForReport($this->reportType);
            }

            protected function getHeadingsForReport(string $reportType): array
            {
                return match($reportType) {
                    'sales' => ['Date', 'Transaction ID', 'Customer', 'Items', 'Subtotal', 'Tax', 'Total', 'Payment Method'],
                    'inventory' => ['Product Name', 'SKU', 'Category', 'Quantity', 'Unit Cost', 'Unit Price', 'Total Value', 'Room', 'METRC Tag'],
                    'customers' => ['Customer Name', 'Type', 'Email', 'Phone', 'Total Visits', 'Total Spent', 'Average Order', 'Last Visit'],
                    'products' => ['Name', 'Category', 'SKU', 'Price', 'Cost', 'Quantity', 'Room', 'THC%', 'CBD%', 'METRC Tag'],
                    'analytics' => ['Metric', 'Value', 'Period', 'Change', 'Percentage'],
                    'metrc' => ['Package Tag', 'Product', 'Quantity', 'Unit', 'Status', 'Location', 'Last Modified'],
                    'compliance' => ['Date', 'Type', 'Description', 'Status', 'Employee', 'Notes'],
                    'employees' => ['Name', 'Role', 'Employee ID', 'Email', 'Hours Worked', 'Sales Count', 'Performance Score'],
                    default => ['Column 1', 'Column 2', 'Column 3']
                };
            }

            protected function getColumnFormatsForReport(string $reportType): array
            {
                return match($reportType) {
                    'sales' => [
                        'E' => '#,##0.00', // Subtotal
                        'F' => '#,##0.00', // Tax
                        'G' => '#,##0.00', // Total
                    ],
                    'inventory' => [
                        'E' => '#,##0.00', // Unit Cost
                        'F' => '#,##0.00', // Unit Price
                        'G' => '#,##0.00', // Total Value
                    ],
                    'customers' => [
                        'F' => '#,##0.00', // Total Spent
                        'G' => '#,##0.00', // Average Order
                    ],
                    'products' => [
                        'D' => '#,##0.00', // Price
                        'E' => '#,##0.00', // Cost
                        'H' => '0.00%', // THC%
                        'I' => '0.00%', // CBD%
                    ],
                    default => []
                };
            }
        };

        return Excel::download($export, $filename);
    }

    /**
     * Export to CSV format
     */
    protected function exportToCsv(string $reportType, array $data, string $filename, array $options = [])
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($reportType, $data) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            $headings = $this->getCsvHeadingsForReport($reportType);
            fputcsv($file, $headings);
            
            // Add data rows
            foreach ($data as $row) {
                if (is_array($row)) {
                    fputcsv($file, array_values($row));
                } elseif (is_object($row)) {
                    fputcsv($file, array_values((array) $row));
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get appropriate view for PDF reports
     */
    protected function getReportView(string $reportType): string
    {
        return match($reportType) {
            'sales' => 'exports.pdf.sales',
            'inventory' => 'exports.pdf.inventory',
            'customers' => 'exports.pdf.customers',
            'products' => 'exports.pdf.products',
            'analytics' => 'exports.pdf.analytics',
            'metrc' => 'exports.pdf.metrc',
            'compliance' => 'exports.pdf.compliance',
            'employees' => 'exports.pdf.employees',
            'daily_summary' => 'exports.pdf.daily_summary',
            'tax_report' => 'exports.pdf.tax_report',
            default => 'exports.pdf.generic'
        };
    }

    /**
     * Generate filename for export
     */
    protected function generateFilename(string $reportType, string $format): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extension = $format === 'excel' ? 'xlsx' : $format;
        
        return "cannabis_pos_{$reportType}_report_{$timestamp}.{$extension}";
    }

    /**
     * Get CSV headings for different report types
     */
    protected function getCsvHeadingsForReport(string $reportType): array
    {
        return match($reportType) {
            'sales' => ['Date', 'Transaction ID', 'Customer', 'Items', 'Subtotal', 'Tax', 'Total', 'Payment Method'],
            'inventory' => ['Product Name', 'SKU', 'Category', 'Quantity', 'Unit Cost', 'Unit Price', 'Total Value', 'Room', 'METRC Tag'],
            'customers' => ['Customer Name', 'Type', 'Email', 'Phone', 'Total Visits', 'Total Spent', 'Average Order', 'Last Visit'],
            'products' => ['Name', 'Category', 'SKU', 'Price', 'Cost', 'Quantity', 'Room', 'THC%', 'CBD%', 'METRC Tag'],
            'analytics' => ['Metric', 'Value', 'Period', 'Change', 'Percentage'],
            'metrc' => ['Package Tag', 'Product', 'Quantity', 'Unit', 'Status', 'Location', 'Last Modified'],
            'compliance' => ['Date', 'Type', 'Description', 'Status', 'Employee', 'Notes'],
            'employees' => ['Name', 'Role', 'Employee ID', 'Email', 'Hours Worked', 'Sales Count', 'Performance Score'],
            default => ['Column 1', 'Column 2', 'Column 3']
        };
    }

    /**
     * Validate and prepare data for export
     */
    public function prepareDataForExport(Collection $data, string $reportType): array
    {
        return $data->map(function ($item) use ($reportType) {
            return $this->formatDataItem($item, $reportType);
        })->toArray();
    }

    /**
     * Format individual data items based on report type
     */
    protected function formatDataItem($item, string $reportType): array
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        return match($reportType) {
            'sales' => [
                'date' => $item->created_at ?? $item->date ?? '',
                'transaction_id' => $item->id ?? $item->transaction_id ?? '',
                'customer' => $item->customer_name ?? 'Walk-in',
                'items' => $item->item_count ?? $item->items ?? 0,
                'subtotal' => number_format($item->subtotal ?? 0, 2),
                'tax' => number_format($item->tax ?? 0, 2),
                'total' => number_format($item->total ?? 0, 2),
                'payment_method' => $item->payment_method ?? 'Cash'
            ],
            'inventory' => [
                'name' => $item->name ?? '',
                'sku' => $item->sku ?? '',
                'category' => $item->category ?? '',
                'quantity' => $item->quantity ?? 0,
                'unit_cost' => number_format($item->cost ?? 0, 2),
                'unit_price' => number_format($item->price ?? 0, 2),
                'total_value' => number_format(($item->price ?? 0) * ($item->quantity ?? 0), 2),
                'room' => $item->room ?? '',
                'metrc_tag' => $item->metrc_tag ?? ''
            ],
            'customers' => [
                'name' => ($item->first_name ?? '') . ' ' . ($item->last_name ?? ''),
                'type' => $item->customer_type ?? $item->type ?? '',
                'email' => $item->email ?? '',
                'phone' => $item->phone ?? '',
                'visits' => $item->visit_count ?? $item->visits ?? 0,
                'total_spent' => number_format($item->total_spent ?? 0, 2),
                'average_order' => number_format($item->avg_order ?? 0, 2),
                'last_visit' => $item->last_visit ?? ''
            ],
            default => (array) $item
        };
    }

    /**
     * Get supported export formats
     */
    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }

    /**
     * Generate report metadata
     */
    public function generateReportMetadata(string $reportType, array $options = []): array
    {
        return [
            'report_type' => $reportType,
            'generated_at' => now()->toISOString(),
            'generated_by' => auth()->user()->name ?? 'System',
            'company' => 'Cannabis POS System',
            'filters' => $options['filters'] ?? [],
            'date_range' => $options['date_range'] ?? null,
            'total_records' => $options['total_records'] ?? 0
        ];
    }
}
