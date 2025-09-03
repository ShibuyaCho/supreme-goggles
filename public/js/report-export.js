/**
 * Cannabis POS Report Export Functionality
 * Provides PDF, Excel, and CSV export capabilities for all reports
 */

// Report Export Manager
class ReportExportManager {
    constructor() {
        this.supportedFormats = ['pdf', 'excel', 'csv'];
        this.availableReports = {};
        this.currentFilters = {};
        this.init();
    }

    async init() {
        try {
            await this.loadAvailableReports();
            this.bindEvents();
        } catch (error) {
            console.error('Failed to initialize report export manager:', error);
        }
    }

    /**
     * Load available report types from the server
     */
    async loadAvailableReports() {
        try {
            const response = await axios.get('/api/reports/available', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            });
            
            this.availableReports = response.data;
        } catch (error) {
            console.error('Failed to load available reports:', error);
            // Fallback to default report types
            this.availableReports = {
                'sales': 'Sales Report',
                'inventory': 'Inventory Report',
                'customers': 'Customer Analytics',
                'products': 'Product Performance',
                'metrc': 'METRC Compliance',
                'employees': 'Employee Performance',
                'daily_summary': 'Daily Summary',
                'tax_report': 'Tax Report'
            };
        }
    }

    /**
     * Bind export button events
     */
    bindEvents() {
        // Export buttons with data attributes
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-export-report]')) {
                e.preventDefault();
                const button = e.target.closest('[data-export-report]');
                this.handleExportClick(button);
            }
        });

        // Export form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('export-form')) {
                e.preventDefault();
                this.handleExportFormSubmit(e.target);
            }
        });
    }

    /**
     * Handle export button clicks
     */
    async handleExportClick(button) {
        const reportType = button.dataset.exportReport;
        const format = button.dataset.exportFormat || 'pdf';
        const filters = button.dataset.exportFilters ? JSON.parse(button.dataset.exportFilters) : {};
        
        // Show export modal if multiple formats are supported
        if (!button.dataset.exportFormat) {
            this.showExportModal(reportType, filters);
        } else {
            await this.exportReport(reportType, format, filters);
        }
    }

    /**
     * Handle export form submissions
     */
    async handleExportFormSubmit(form) {
        const formData = new FormData(form);
        const exportData = {
            report_type: formData.get('report_type'),
            format: formData.get('format'),
            start_date: formData.get('start_date'),
            end_date: formData.get('end_date'),
            filters: this.getFormFilters(form),
            include_charts: formData.get('include_charts') === 'on',
            orientation: formData.get('orientation') || 'portrait',
            paper_size: formData.get('paper_size') || 'a4'
        };

        await this.exportReport(
            exportData.report_type,
            exportData.format,
            exportData.filters,
            exportData
        );
    }

    /**
     * Show export modal with format options
     */
    showExportModal(reportType, filters = {}) {
        const modal = this.createExportModal(reportType, filters);
        document.body.appendChild(modal);
        
        // Show modal
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    /**
     * Create export modal element
     */
    createExportModal(reportType, filters = {}) {
        const modal = document.createElement('div');
        modal.className = 'export-modal-overlay';
        modal.innerHTML = `
            <div class="export-modal">
                <div class="export-modal-header">
                    <h3>Export ${this.availableReports[reportType] || 'Report'}</h3>
                    <button type="button" class="close-modal">&times;</button>
                </div>
                
                <form class="export-form">
                    <input type="hidden" name="report_type" value="${reportType}">
                    
                    <div class="form-group">
                        <label for="export_format">Export Format:</label>
                        <select name="format" id="export_format" required>
                            <option value="pdf">📄 PDF Document</option>
                            <option value="excel">📊 Excel Spreadsheet</option>
                            <option value="csv">📋 CSV File</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Start Date:</label>
                            <input type="date" name="start_date" id="start_date" 
                                   value="${this.getDefaultStartDate()}">
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date:</label>
                            <input type="date" name="end_date" id="end_date" 
                                   value="${this.getDefaultEndDate()}">
                        </div>
                    </div>
                    
                    <div class="pdf-options" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="orientation">Orientation:</label>
                                <select name="orientation" id="orientation">
                                    <option value="portrait">Portrait</option>
                                    <option value="landscape">Landscape</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="paper_size">Paper Size:</label>
                                <select name="paper_size" id="paper_size">
                                    <option value="a4">A4</option>
                                    <option value="letter">Letter</option>
                                    <option value="legal">Legal</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="include_charts" checked>
                                Include charts and graphs
                            </label>
                        </div>
                    </div>
                    
                    ${this.createFilterInputs(reportType, filters)}
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Report
                        </button>
                    </div>
                </form>
            </div>
        `;

        // Bind modal events
        this.bindModalEvents(modal);
        
        return modal;
    }

    /**
     * Create filter inputs based on report type
     */
    createFilterInputs(reportType, existingFilters = {}) {
        const filterGroups = {
            sales: [
                { name: 'employee_id', label: 'Employee', type: 'select', options: 'employees' },
                { name: 'payment_method', label: 'Payment Method', type: 'select', options: ['cash', 'card', 'check', 'other'] },
                { name: 'customer_type', label: 'Customer Type', type: 'select', options: ['consumer', 'patient', 'caregiver'] }
            ],
            inventory: [
                { name: 'category', label: 'Category', type: 'select', options: 'categories' },
                { name: 'room', label: 'Room', type: 'select', options: 'rooms' },
                { name: 'low_stock', label: 'Low Stock Only', type: 'checkbox' },
                { name: 'out_of_stock', label: 'Out of Stock Only', type: 'checkbox' }
            ],
            customers: [
                { name: 'customer_type', label: 'Customer Type', type: 'select', options: ['all', 'consumer', 'patient', 'caregiver'] }
            ]
        };

        const filters = filterGroups[reportType] || [];
        
        if (filters.length === 0) {
            return '';
        }

        let filtersHtml = '<div class="filters-section"><h4>Filters</h4>';
        
        filters.forEach(filter => {
            filtersHtml += `<div class="form-group">`;
            filtersHtml += `<label for="filter_${filter.name}">${filter.label}:</label>`;
            
            switch (filter.type) {
                case 'select':
                    filtersHtml += `<select name="filters[${filter.name}]" id="filter_${filter.name}">`;
                    filtersHtml += `<option value="">All</option>`;
                    
                    if (Array.isArray(filter.options)) {
                        filter.options.forEach(option => {
                            const selected = existingFilters[filter.name] === option ? 'selected' : '';
                            filtersHtml += `<option value="${option}" ${selected}>${option.charAt(0).toUpperCase() + option.slice(1)}</option>`;
                        });
                    }
                    filtersHtml += `</select>`;
                    break;
                    
                case 'checkbox':
                    const checked = existingFilters[filter.name] ? 'checked' : '';
                    filtersHtml += `<input type="checkbox" name="filters[${filter.name}]" id="filter_${filter.name}" value="1" ${checked}>`;
                    break;
            }
            
            filtersHtml += `</div>`;
        });
        
        filtersHtml += '</div>';
        return filtersHtml;
    }

    /**
     * Bind modal events
     */
    bindModalEvents(modal) {
        // Close modal events
        modal.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => this.closeModal(modal));
        });

        // Close on overlay click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeModal(modal);
            }
        });

        // Format change events
        const formatSelect = modal.querySelector('[name="format"]');
        const pdfOptions = modal.querySelector('.pdf-options');
        
        if (formatSelect && pdfOptions) {
            formatSelect.addEventListener('change', (e) => {
                pdfOptions.style.display = e.target.value === 'pdf' ? 'block' : 'none';
            });
        }
    }

    /**
     * Close and remove modal
     */
    closeModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }

    /**
     * Export report with specified parameters
     */
    async exportReport(reportType, format, filters = {}, options = {}) {
        try {
            // Show loading indicator
            this.showLoadingIndicator();

            const requestData = {
                report_type: reportType,
                format: format,
                start_date: options.start_date || this.getDefaultStartDate(),
                end_date: options.end_date || this.getDefaultEndDate(),
                filters: { ...this.currentFilters, ...filters },
                include_charts: options.include_charts || false,
                orientation: options.orientation || 'portrait',
                paper_size: options.paper_size || 'a4'
            };

            const response = await axios.post('/api/reports/export', requestData, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Content-Type': 'application/json'
                },
                responseType: 'blob'
            });

            // Create download link
            this.downloadFile(response.data, this.generateFilename(reportType, format), format);
            
            // Show success message
            this.showSuccessMessage(`${format.toUpperCase()} report exported successfully!`);
            
            // Close modal if open
            const modal = document.querySelector('.export-modal-overlay');
            if (modal) {
                this.closeModal(modal);
            }

        } catch (error) {
            console.error('Export failed:', error);
            this.showErrorMessage('Failed to export report. Please try again.');
        } finally {
            this.hideLoadingIndicator();
        }
    }

    /**
     * Download file from blob
     */
    downloadFile(blob, filename, format) {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up
        setTimeout(() => {
            window.URL.revokeObjectURL(url);
        }, 100);
    }

    /**
     * Generate filename for export
     */
    generateFilename(reportType, format) {
        const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
        const extension = format === 'excel' ? 'xlsx' : format;
        return `cannabis_pos_${reportType}_${timestamp}.${extension}`;
    }

    /**
     * Get form filters
     */
    getFormFilters(form) {
        const filters = {};
        const formData = new FormData(form);
        
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('filters[') && value) {
                const filterName = key.slice(8, -1); // Remove 'filters[' and ']'
                filters[filterName] = value;
            }
        }
        
        return filters;
    }

    /**
     * Get default start date (30 days ago)
     */
    getDefaultStartDate() {
        const date = new Date();
        date.setDate(date.getDate() - 30);
        return date.toISOString().split('T')[0];
    }

    /**
     * Get default end date (today)
     */
    getDefaultEndDate() {
        return new Date().toISOString().split('T')[0];
    }

    /**
     * Show loading indicator
     */
    showLoadingIndicator() {
        const loader = document.createElement('div');
        loader.id = 'export-loader';
        loader.className = 'export-loader';
        loader.innerHTML = `
            <div class="loader-content">
                <div class="spinner"></div>
                <p>Generating report...</p>
            </div>
        `;
        
        document.body.appendChild(loader);
    }

    /**
     * Hide loading indicator
     */
    hideLoadingIndicator() {
        const loader = document.getElementById('export-loader');
        if (loader) {
            loader.remove();
        }
    }

    /**
     * Show success message
     */
    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    /**
     * Show error message
     */
    showErrorMessage(message) {
        this.showToast(message, 'error');
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        // Use existing toast system if available, otherwise create simple notification
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                background: ${type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#0891b2'};
                color: white;
                border-radius: 6px;
                z-index: 10000;
                font-size: 14px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }

    /**
     * Set current filters (useful for page-specific filtering)
     */
    setCurrentFilters(filters) {
        this.currentFilters = filters;
    }

    /**
     * Add quick export buttons to any container
     */
    addQuickExportButtons(container, reportType, filters = {}) {
        const buttonsHtml = `
            <div class="export-buttons">
                <button type="button" data-export-report="${reportType}" data-export-format="pdf" 
                        data-export-filters='${JSON.stringify(filters)}'
                        class="btn btn-export btn-pdf">
                    📄 PDF
                </button>
                <button type="button" data-export-report="${reportType}" data-export-format="excel" 
                        data-export-filters='${JSON.stringify(filters)}'
                        class="btn btn-export btn-excel">
                    📊 Excel
                </button>
                <button type="button" data-export-report="${reportType}" data-export-format="csv" 
                        data-export-filters='${JSON.stringify(filters)}'
                        class="btn btn-export btn-csv">
                    📋 CSV
                </button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', buttonsHtml);
    }
}

// Initialize export manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.reportExportManager = new ReportExportManager();
});

// Export for use in other scripts
window.ReportExportManager = ReportExportManager;
