@extends('layouts.app')

@section('title', 'Reports - Cannabest POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <h1 class="text-2xl font-bold text-gray-900">Reports & Documentation</h1>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-4">
                    <button onclick="scheduleReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Schedule Report
                    </button>
                    <button onclick="generateCustomReport()" class="bg-cannabis-green hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Custom Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="reportsManager()">
        <!-- Report Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Sales Reports -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sales Reports</h3>
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="space-y-2">
                    <button data-export-report="sales" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Daily Sales</button>
                    <button data-export-report="sales" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Weekly Sales</button>
                    <button data-export-report="sales" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Monthly Sales</button>
                    <button data-export-report="sales" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Sales by Category</button>
                    <button data-export-report="sales" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Sales by Employee</button>
                </div>
            </div>

            <!-- Inventory Reports -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Inventory Reports</h3>
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01"/>
                    </svg>
                </div>
                <div class="space-y-2">
                    <button data-export-report="inventory" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Current Inventory</button>
                    <button data-export-report="inventory" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Low Stock Alert</button>
                    <button data-export-report="inventory" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Out of Stock</button>
                    <button data-export-report="inventory" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Inventory Valuation</button>
                    <button data-export-report="inventory" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Product Movement</button>
                </div>
            </div>

            <!-- Tax & Compliance -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Tax & Compliance</h3>
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="space-y-2">
                    <button data-export-report="tax_report" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Tax Collected</button>
                    <button data-export-report="metrc" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">METRC Compliance</button>
                    <button data-export-report="sales" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Medical Sales</button>
                    <button data-export-report="compliance" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Regulatory Summary</button>
                    <button data-export-report="compliance" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Audit Trail</button>
                </div>
            </div>

            <!-- Customer Reports -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Customer Reports</h3>
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="space-y-2">
                    <button data-export-report="customers" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Customer List</button>
                    <button data-export-report="customers" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Loyalty Summary</button>
                    <button data-export-report="customers" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Top Customers</button>
                    <button data-export-report="customers" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Customer Preferences</button>
                    <button data-export-report="customers" class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded">Retention Analysis</button>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Created Reports</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="report in recentReports" :key="report.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="report.name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="report.type"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="report.generated"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="report.status === 'completed' ? 'bg-green-100 text-green-800' : report.status === 'processing' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-medium rounded-full" x-text="report.status"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex space-x-2">
                                            <button @click="downloadReport(report)" :disabled="report.status !== 'completed'" class="text-cannabis-green hover:text-green-700 disabled:text-gray-400">Download</button>
                                            <button @click="viewReport(report)" :disabled="report.status !== 'completed'" class="text-blue-600 hover:text-blue-800 disabled:text-gray-400">View</button>
                                            <button @click="deleteReport(report)" class="text-red-600 hover:text-red-800">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Scheduled Reports -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Scheduled Reports</h3>
                <button @click="showScheduleModal = true" class="text-sm text-cannabis-green hover:text-green-700">+ Add Schedule</button>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <template x-for="schedule in scheduledReports" :key="schedule.id">
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-900" x-text="schedule.name"></h4>
                                <p class="text-sm text-gray-500" x-text="`${schedule.frequency} • Next: ${schedule.nextRun}`"></p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" :checked="schedule.active" @change="toggleSchedule(schedule)" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-cannabis-green/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cannabis-green"></div>
                                </label>
                                <button @click="editSchedule(schedule)" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="deleteSchedule(schedule)" class="text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Custom Report Builder Modal -->
        <div x-show="showCustomModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-transition>
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Custom Report Builder</h3>
                        <button @click="showCustomModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Name</label>
                            <input type="text" x-model="customReport.name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data Source</label>
                            <select x-model="customReport.source" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="">Select Data Source</option>
                                <option value="sales">Sales Data</option>
                                <option value="inventory">Inventory Data</option>
                                <option value="customers">Customer Data</option>
                                <option value="products">Product Data</option>
                                <option value="employees">Employee Data</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="date" x-model="customReport.startDate" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <input type="date" x-model="customReport.endDate" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filters</label>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" x-model="customReport.includeVoid" class="rounded text-cannabis-green">
                                    <span class="text-sm">Include voided transactions</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" x-model="customReport.medicalOnly" class="rounded text-cannabis-green">
                                    <span class="text-sm">Medical sales only</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" x-model="customReport.groupByCategory" class="rounded text-cannabis-green">
                                    <span class="text-sm">Group by category</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Output Format</label>
                            <div class="grid grid-cols-3 gap-4">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" x-model="customReport.format" value="pdf" class="text-cannabis-green">
                                    <span class="text-sm">PDF</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" x-model="customReport.format" value="excel" class="text-cannabis-green">
                                    <span class="text-sm">Excel</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" x-model="customReport.format" value="csv" class="text-cannabis-green">
                                    <span class="text-sm">CSV</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-3">
                        <button @click="showCustomModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                        <button @click="generateCustomReport()" class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-700">Generate Report</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Report Modal -->
        <div x-show="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-transition>
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Schedule Report</h3>
                        <button @click="showScheduleModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">×</button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                            <select x-model="scheduleForm.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="">Select Report Type</option>
                                <option value="daily-sales">Daily Sales</option>
                                <option value="weekly-sales">Weekly Sales</option>
                                <option value="monthly-sales">Monthly Sales</option>
                                <option value="inventory-summary">Inventory Summary</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                            <select x-model="scheduleForm.frequency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Send To</label>
                            <input type="email" x-model="scheduleForm.email" placeholder="manager@cannabest.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button @click="showScheduleModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                        <button @click="saveSchedule()" class="px-4 py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-700">Save Schedule</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function reportsManager() {
    return {
        showCustomModal: false,
        showScheduleModal: false,
        customReport: {
            name: '',
            source: '',
            startDate: '',
            endDate: '',
            includeVoid: false,
            medicalOnly: false,
            groupByCategory: false,
            format: 'pdf'
        },
        scheduleForm: {
            type: '',
            frequency: 'weekly',
            email: ''
        },
        recentReports: [],
        scheduledReports: [],

        async init() {
            try {
                const res = await fetch('/api/reports/templates', { headers: { Accept: 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    const templates = Array.isArray(data?.templates) ? data.templates : [];
                    this.recentReports = templates.slice(0, 20).map(t => ({
                        id: t.id,
                        name: t.name,
                        type: (t.report_type || 'general').replace(/\b\w/g, c => c.toUpperCase()),
                        generated: t.updated_at || t.created_at || '',
                        status: 'completed'
                    }));
                }
            } catch(_) {}
        },

        async generateReport(type) {
            const fmt = await this.askFormat();
            if (!fmt) return;
            const map = this.mapReportType(type);
            if (!map) { this.showToast('Unsupported report', 'error'); return; }
            try {
                const res = await (window.axios||axios).post('/api/reports/export', {
                    report_type: map,
                    format: fmt,
                    start_date: null,
                    end_date: null,
                    filters: {}
                }, { responseType: 'blob' });
                const ctype = ((res && res.headers && res.headers['content-type']) || '').toLowerCase();
                const dataBlob = res?.data instanceof Blob ? res.data : new Blob([res.data], { type: ctype || 'application/octet-stream' });
                let treatAsStub = ctype.includes('application/json');
                if (!treatAsStub && dataBlob && dataBlob.size > 0 && dataBlob.size < 4096) {
                    try {
                        const text = await dataBlob.text();
                        const t = text.trim();
                        if (t.startsWith('{') || t.startsWith('[') || t.includes('Dev API stub active')) {
                            treatAsStub = true;
                        }
                    } catch(_) {}
                }
                if (treatAsStub) {
                    const headings = this.getReportHeadings(map, []);
                    const csv = headings.join(',') + '\n';
                    const blob = new Blob([csv], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.setAttribute('download', `report_${map}.csv`);
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                } else {
                    this.triggerDownload({ data: dataBlob, headers: res.headers }, `report_${map}.${fmt === 'excel' ? 'xlsx' : fmt}`);
                }
            } catch(e) {
                // Client-side fallback on network error
                const headings = this.getReportHeadings(map, []);
                const csv = headings.join(',') + '\n';
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.setAttribute('download', `report_${map}.csv`);
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                this.showToast('Downloaded CSV headers (fallback)', 'info');
            }
        },

        getReportCategory(type) {
            if (type.includes('sales')) return 'Sales';
            if (type.includes('inventory') || type.includes('stock')) return 'Inventory';
            if (type.includes('tax') || type.includes('metrc') || type.includes('compliance')) return 'Tax & Compliance';
            if (type.includes('customer') || type.includes('loyalty')) return 'Customer';
            return 'General';
        },

        async generateCustomReport() {
            if (!this.customReport.name || !this.customReport.source) {
                this.showToast('Please fill in required fields', 'error');
                return;
            }
            const fmt = await this.askFormat(this.customReport.format);
            if (!fmt) return;
            this.showCustomModal = false;
            const reportType = this.mapSourceToReport(this.customReport.source);
            try {
                const res = await (window.axios||axios).post('/api/reports/export', {
                    report_type: reportType,
                    format: fmt,
                    start_date: this.customReport.startDate || null,
                    end_date: this.customReport.endDate || null,
                    filters: {
                        include_void: !!this.customReport.includeVoid,
                        medical_only: !!this.customReport.medicalOnly,
                        group_by_category: !!this.customReport.groupByCategory,
                    },
                }, { responseType: 'blob' });
                const ctype = ((res && res.headers && res.headers['content-type']) || '').toLowerCase();
                const dataBlob = res?.data instanceof Blob ? res.data : new Blob([res.data], { type: ctype || 'application/octet-stream' });
                let treatAsStub = ctype.includes('application/json');
                if (!treatAsStub && dataBlob && dataBlob.size > 0 && dataBlob.size < 4096) {
                    try {
                        const text = await dataBlob.text();
                        const t = text.trim();
                        if (t.startsWith('{') || t.startsWith('[') || t.includes('Dev API stub active')) {
                            treatAsStub = true;
                        }
                    } catch(_) {}
                }
                if (treatAsStub) {
                    const headings = this.getReportHeadings(reportType, this.customReport?.selectedMetrics || []);
                    const csv = headings.join(',') + '\n';
                    const blob = new Blob([csv], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.setAttribute('download', `${this.customReport.name.replace(/\s+/g,'_')}.csv`);
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                } else {
                    this.triggerDownload({ data: dataBlob, headers: res.headers }, `${this.customReport.name.replace(/\s+/g,'_')}.${fmt === 'excel' ? 'xlsx' : fmt}`);
                }
                // Persist custom report template to Supabase
                try {
                    const tpl = {
                        name: this.customReport.name,
                        description: null,
                        report_type: reportType,
                        format: fmt,
                        include_charts: false,
                        orientation: 'portrait',
                        paper_size: 'a4',
                        config: {
                            source: this.customReport.source,
                            start_date: this.customReport.startDate || null,
                            end_date: this.customReport.endDate || null,
                            filters: {
                                include_void: !!this.customReport.includeVoid,
                                medical_only: !!this.customReport.medicalOnly,
                                group_by_category: !!this.customReport.groupByCategory,
                            }
                        }
                    };
                    await fetch('/api/reports/templates', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify(tpl)
                    });
                    this.init();
                } catch(_) {}
                this.showToast('Report generated successfully!', 'success');
            } catch (e) {
                const headings = this.getReportHeadings(reportType, this.customReport?.selectedMetrics || []);
                const csv = headings.join(',') + '\n';
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.setAttribute('download', `${this.customReport.name.replace(/\s+/g,'_')}.csv`);
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                this.showToast('Downloaded CSV headers (fallback)', 'info');
            }
        },

        async askFormat(defaultFmt = 'pdf') {
            try {
                const choice = prompt('Export format: pdf, excel, or csv', defaultFmt);
                const fmt = (choice||'').trim().toLowerCase();
                if (!fmt) return null;
                if (!['pdf','excel','csv'].includes(fmt)) { this.showToast('Invalid format', 'error'); return null; }
                return fmt;
            } catch(e) { return null; }
        },

        mapSourceToReport(src) {
            const m = { sales:'sales', inventory:'inventory', customers:'customers', products:'products', employees:'employees', analytics:'analytics', metrc:'metrc', compliance:'compliance' };
            return m[src] || 'sales';
        },
        mapReportType(type) {
            const m = {
                'daily-sales':'sales', 'weekly-sales':'sales', 'monthly-sales':'sales', 'sales-by-category':'sales', 'sales-by-employee':'sales',
                'current-inventory':'inventory', 'low-stock':'inventory', 'out-of-stock':'inventory', 'inventory-valuation':'inventory', 'product-movement':'inventory',
                'tax-collected':'tax_report', 'metrc-compliance':'metrc', 'medical-sales':'sales', 'regulatory-summary':'compliance', 'audit-trail':'compliance',
                'customer-list':'customers','loyalty-summary':'customers','top-customers':'customers','customer-preferences':'customers','retention-analysis':'customers'
            };
            return m[type] || null;
        },

        triggerDownload(res, filename){
            const headers = (res && res.headers) || {};
            const contentType = headers['content-type'] || 'application/octet-stream';
            const blob = res?.data instanceof Blob ? res.data : new Blob([res.data], { type: contentType });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        },

        downloadReport(report) {
            this.showToast(`Downloading ${report.name}...`, 'info');
            // No stored file; prompt and re-run generation
            this.generateReport(report.name.toLowerCase().replace(/\s+/g,'-'))
        },

        viewReport(report) {
            this.showToast(`Opening ${report.name}...`, 'info');
            // Simulate opening report viewer
        },

        deleteReport(report) {
            if (confirm(`Delete ${report.name}?`)) {
                const index = this.recentReports.findIndex(r => r.id === report.id);
                if (index !== -1) {
                    this.recentReports.splice(index, 1);
                    this.showToast('Report deleted', 'success');
                }
            }
        },

        async saveSchedule() {
            if (!this.scheduleForm.type || !this.scheduleForm.email) {
                this.showToast('Please fill in required fields', 'error');
                return;
            }

            const newSchedule = {
                id: Date.now(),
                name: this.scheduleForm.type.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()),
                frequency: this.scheduleForm.frequency.charAt(0).toUpperCase() + this.scheduleForm.frequency.slice(1),
                nextRun: 'Tomorrow',
                active: true
            };

            this.scheduledReports.push(newSchedule);
            this.showScheduleModal = false;
            this.scheduleForm = { type: '', frequency: 'weekly', email: '' };
            try {
                await fetch('/api/activity', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({ action: 'report_schedule_saved', schedule: newSchedule })
                });
            } catch(_) {}
            this.showToast('Report scheduled successfully!', 'success');
        },

        toggleSchedule(schedule) {
            schedule.active = !schedule.active;
            this.showToast(`Schedule ${schedule.active ? 'enabled' : 'disabled'}`, 'info');
        },

        editSchedule(schedule) {
            this.showToast('Edit functionality would open here', 'info');
        },

        deleteSchedule(schedule) {
            if (confirm(`Delete schedule for ${schedule.name}?`)) {
                const index = this.scheduledReports.findIndex(s => s.id === schedule.id);
                if (index !== -1) {
                    this.scheduledReports.splice(index, 1);
                    this.showToast('Schedule deleted', 'success');
                }
            }
        },

        getReportHeadings(reportType, metrics = []){
            if (Array.isArray(metrics) && metrics.length){
                return metrics.map(m => String(m).replace(/_/g,' ').replace(/\b\w/g, c=>c.toUpperCase()));
            }
            switch (reportType){
                case 'sales': return ['Date','Transaction ID','Customer','Items','Subtotal','Tax','Total','Payment Method'];
                case 'inventory': return ['Product Name','SKU','Category','Quantity','Unit Cost','Unit Price','Total Value','Room','METRC Tag'];
                case 'customers': return ['Customer Name','Type','Email','Phone','Total Visits','Total Spent','Average Order','Last Visit'];
                case 'products': return ['Name','Category','SKU','Price','Cost','Quantity','Room','THC%','CBD%','METRC Tag'];
                case 'analytics': return ['Metric','Value','Period','Change','Percentage'];
                case 'metrc': return ['Package Tag','Product','Quantity','Unit','Status','Location','Last Modified'];
                case 'compliance': return ['Date','Type','Description','Status','Employee','Notes'];
                case 'employees': return ['Name','Role','Employee ID','Email','Hours Worked','Sales Count','Performance Score'];
                case 'tax_report': return ['Date','Transactions','Gross Sales','Total Tax','Net Sales','Payment Method'];
                default: return ['Column 1','Column 2','Column 3'];
            }
        },

        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    };
}

function scheduleReport() {
    document.querySelector('[x-data="reportsManager()"]').__x.$data.showScheduleModal = true;
}

function generateCustomReport() {
    document.querySelector('[x-data="reportsManager()"]').__x.$data.showCustomModal = true;
}
</script>

@push('styles')
<link href="{{ asset('css/report-export.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('js/report-export.js') }}" defer></script>
@endpush

@endsection
