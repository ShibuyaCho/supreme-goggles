import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Checkbox } from "@/components/ui/checkbox";
import { DatePickerWithRange } from "@/components/ui/date-range-picker";
import {
  FileText,
  Plus,
  Download,
  Calendar,
  Filter,
  BarChart3,
  PieChart,
  TrendingUp,
  DollarSign,
  Package,
  Users,
  Clock,
  Eye,
  Edit,
  Trash2,
  Play,
  Share,
  Mail,
  Printer
} from "lucide-react";

interface Report {
  id: string;
  name: string;
  type: 'sales' | 'inventory' | 'customer' | 'employee' | 'compliance';
  schedule: 'manual' | 'daily' | 'weekly' | 'monthly';
  lastRun: string;
  metrics: string[];
  filters: {
    dateRange?: string;
    categories?: string[];
    stores?: string[];
    employees?: string[];
    status?: string;
  };
  format: 'pdf' | 'excel' | 'csv';
  recipients: string[];
}

const savedReports: Report[] = [
  {
    id: "1",
    name: "Daily Sales Summary",
    type: "sales",
    schedule: "daily",
    lastRun: "2024-01-15 09:00",
    metrics: ["revenue", "transactions", "avgOrderValue"],
    filters: { dateRange: "today" },
    format: "pdf",
    recipients: ["manager@cannabest.com"]
  },
  {
    id: "2",
    name: "Inventory Evaluation Report",
    type: "inventory",
    schedule: "weekly",
    lastRun: "2024-01-14 18:00",
    metrics: ["stockLevels", "lowStock", "topProducts"],
    filters: { categories: ["Flower", "Edibles"] },
    format: "excel",
    recipients: ["inventory@cannabest.com", "manager@cannabest.com"]
  },
  {
    id: "3",
    name: "Monthly Employee Performance",
    type: "employee",
    schedule: "monthly",
    lastRun: "2024-01-01 08:00",
    metrics: ["sales", "hours", "transactions"],
    filters: { dateRange: "last30days" },
    format: "pdf",
    recipients: ["hr@cannabest.com"]
  },
  {
    id: "4",
    name: "Compliance Report",
    type: "compliance",
    schedule: "weekly",
    lastRun: "2024-01-14 17:00",
    metrics: ["sales", "customers", "inventory"],
    filters: { status: "active" },
    format: "pdf",
    recipients: ["compliance@cannabest.com", "manager@cannabest.com"]
  }
];

const reportTypes = [
  { value: "sales", label: "Sales & Revenue", icon: DollarSign },
  { value: "inventory", label: "Inventory", icon: Package },
  { value: "customer", label: "Customer Analytics", icon: Users },
  { value: "employee", label: "Employee Performance", icon: Users },
  { value: "payroll", label: "Payroll", icon: Clock },
  { value: "compliance", label: "Compliance", icon: FileText },
  { value: "auditing", label: "Inventory Auditing", icon: Package },
  { value: "pennysale", label: "Penny Sale Report", icon: DollarSign },
  { value: "custom", label: "Custom Report", icon: BarChart3 }
];

const availableMetrics = {
  sales: [
    { id: "revenue", label: "Total Revenue" },
    { id: "transactions", label: "Number of Transactions" },
    { id: "avgOrderValue", label: "Average Order Value" },
    { id: "customerCount", label: "Customer Count" },
    { id: "avgSaleAmount", label: "Average Sale Amount" },
    { id: "discounts", label: "Total Discounts Applied" },
    { id: "tax", label: "Tax Collected" }
  ],
  inventory: [
    { id: "stockLevels", label: "Current Stock Levels" },
    { id: "lowStock", label: "Low Stock Alerts" },
    { id: "topProducts", label: "Best Selling Products" },
    { id: "slowMoving", label: "Slow Moving Inventory" },
    { id: "wastage", label: "Product Wastage" }
  ],
  customer: [
    { id: "newCustomers", label: "New Customers" },
    { id: "returningCustomers", label: "Returning Customers" },
    { id: "loyaltyMembers", label: "Loyalty Program Members" },
    { id: "medicalPatients", label: "Medical Patients" },
    { id: "averageSpend", label: "Average Customer Spend" }
  ],
  employee: [
    { id: "salesDollars", label: "Sales in Dollars" },
    { id: "hoursWorked", label: "Hours Worked (to hundredth decimal)" },
    { id: "totalTransactions", label: "Total Transactions" },
    { id: "productivity", label: "Productivity Metrics" },
    { id: "certifications", label: "Certification Status" }
  ],
  payroll: [
    { id: "totalHours", label: "Total Hours Worked" },
    { id: "clockInOut", label: "Clock In/Out Details" },
    { id: "overtimeHours", label: "Overtime Hours" },
    { id: "regularHours", label: "Regular Hours" },
    { id: "dailyBreakdown", label: "Daily Hours Breakdown" }
  ],
  compliance: [
    { id: "onHandVsMetrc", label: "On Hand vs Metrc Quantities" },
    { id: "inventoryOnlyItems", label: "Items in Inventory but not in Metrc" },
    { id: "metrcOnlyItems", label: "Items in Metrc but not in Inventory" },
    { id: "customers", label: "Customer Verification" },
    { id: "waste", label: "Waste Disposal" },
    { id: "security", label: "Security Logs" }
  ],
  auditing: [
    { id: "stockDiscrepancies", label: "Stock Discrepancies" },
    { id: "metrcSync", label: "Metrc Synchronization Status" },
    { id: "inventoryMovements", label: "Inventory Movements" },
    { id: "adjustments", label: "Inventory Adjustments" },
    { id: "auditTrail", label: "Audit Trail" }
  ],
  pennysale: [
    { id: "pennySaleItems", label: "Items Sold for $0.01" },
    { id: "pennySaleTransactions", label: "Penny Sale Transactions" },
    { id: "pennySaleCustomers", label: "Customers with Penny Sales" },
    { id: "pennySaleTotal", label: "Total Penny Sale Revenue" },
    { id: "pennySaleReasonCodes", label: "Discount Reason Codes" }
  ],
  custom: [
    { id: "revenue", label: "Total Revenue" },
    { id: "transactions", label: "Number of Transactions" },
    { id: "customerCount", label: "Customer Count" },
    { id: "avgSaleAmount", label: "Average Sale Amount" },
    { id: "stockLevels", label: "Current Stock Levels" },
    { id: "lowStock", label: "Low Stock Alerts" },
    { id: "salesDollars", label: "Employee Sales in Dollars" },
    { id: "hoursWorked", label: "Employee Hours Worked" },
    { id: "onHandVsMetrc", label: "On Hand vs Metrc Quantities" },
    { id: "loyaltyMembers", label: "Loyalty Program Members" },
    { id: "medicalPatients", label: "Medical Patients" }
  ]
};

// Company users for report sharing
const companyUsers = [
  { id: "1", name: "Sarah Johnson", email: "sarah@cannabest.com", role: "Manager", store: "Main" },
  { id: "2", name: "Mike Chen", email: "mike@cannabest.com", role: "Budtender", store: "Main" },
  { id: "3", name: "Emma Rodriguez", email: "emma@cannabest.com", role: "Cashier", store: "Main" },
  { id: "4", name: "David Kim", email: "david@cannabest.com", role: "Security", store: "Downtown" },
  { id: "5", name: "Lisa Park", email: "lisa@cannabest.com", role: "Budtender", store: "Downtown" },
  { id: "6", name: "John Smith", email: "john@cannabest.com", role: "Manager", store: "Downtown" },
  { id: "7", name: "Maria Garcia", email: "maria@cannabest.com", role: "Assistant Manager", store: "Eastside" },
  { id: "8", name: "Alex Thompson", email: "alex@cannabest.com", role: "Cashier", store: "Eastside" },
  { id: "9", name: "Jennifer Lee", email: "jennifer@cannabest.com", role: "Compliance Officer", store: "Corporate" },
  { id: "10", name: "Robert Wilson", email: "robert@cannabest.com", role: "Regional Manager", store: "Corporate" }
];

const availableCategories = [
  "Flower", "Pre-Rolls", "Concentrates", "Extracts", "Edibles", "Topicals",
  "Tinctures", "Vapes", "Inhalable Cannabinoids", "Clones", "Hemp", "Paraphernalia", "Accessories"
];

const availableStores = [
  "Main", "Downtown", "Eastside", "Westside", "Corporate"
];

export default function Reports() {
  const [reports, setReports] = useState<Report[]>(savedReports);
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [selectedType, setSelectedType] = useState<string>("");
  const [selectedMetrics, setSelectedMetrics] = useState<string[]>([]);
  const [reportName, setReportName] = useState("");
  const [schedule, setSchedule] = useState("manual");
  const [format, setFormat] = useState("pdf");
  const [recipients, setRecipients] = useState("");
  const [customDateRange, setCustomDateRange] = useState<{startDate: string, endDate: string} | null>(null);
  const [showShareDialog, setShowShareDialog] = useState(false);
  const [reportToShare, setReportToShare] = useState<Report | null>(null);
  const [shareEmails, setShareEmails] = useState("");
  const [dateRangeType, setDateRangeType] = useState("today");
  const [selectedUsers, setSelectedUsers] = useState<string[]>([]);
  const [userSearchQuery, setUserSearchQuery] = useState("");
  const [showASPDChartDialog, setShowASPDChartDialog] = useState(false);
  const [aspdStartDate, setAspdStartDate] = useState(new Date().toISOString().split('T')[0]);
  const [aspdEndDate, setAspdEndDate] = useState(new Date().toISOString().split('T')[0]);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [reportToEdit, setReportToEdit] = useState<Report | null>(null);
  const [selectedCategories, setSelectedCategories] = useState<string[]>([]);
  const [selectedStores, setSelectedStores] = useState<string[]>([]);
  const [showQuickInventoryDialog, setShowQuickInventoryDialog] = useState(false);
  const [quickInventoryCategory, setQuickInventoryCategory] = useState<string>("all");
  const [showPennySaleDialog, setShowPennySaleDialog] = useState(false);
  const [pennySaleStartDate, setPennySaleStartDate] = useState(new Date().toISOString().split('T')[0]);
  const [pennySaleEndDate, setPennySaleEndDate] = useState(new Date().toISOString().split('T')[0]);
  const [showDailySalesDialog, setShowDailySalesDialog] = useState(false);
  const [dailySalesStartDate, setDailySalesStartDate] = useState(new Date().toISOString().split('T')[0]);
  const [dailySalesEndDate, setDailySalesEndDate] = useState(new Date().toISOString().split('T')[0]);
  const [showPerformanceDialog, setShowPerformanceDialog] = useState(false);
  const [performanceStartDate, setPerformanceStartDate] = useState(new Date().toISOString().split('T')[0]);
  const [performanceEndDate, setPerformanceEndDate] = useState(new Date().toISOString().split('T')[0]);
  const [showPayrollDialog, setShowPayrollDialog] = useState(false);
  const [payrollStartDate, setPayrollStartDate] = useState(new Date().toISOString().split('T')[0]);
  const [payrollEndDate, setPayrollEndDate] = useState(new Date().toISOString().split('T')[0]);

  const downloadReportAsPDF = (report: Report) => {
    const reportContent = generateReportContent(report);
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>${report.name}</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 40px; }
              .header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
              .metric { margin: 10px 0; padding: 10px; border-left: 3px solid #007bff; }
              .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
              table { width: 100%; border-collapse: collapse; margin: 20px 0; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; }
            </style>
          </head>
          <body>
            ${reportContent}
            <div class="footer">
              Generated on ${new Date().toLocaleString()} | Cannabest POS System
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const generateDailySalesWithDateRange = (format: 'pdf' | 'excel') => {
    const report: Report = {
      id: 'quick-daily-sales',
      name: `Daily Sales Report (${dailySalesStartDate} to ${dailySalesEndDate})`,
      type: 'sales',
      schedule: 'manual',
      lastRun: '',
      metrics: ['revenue', 'transactions', 'avgOrderValue'],
      filters: { dateRange: `${dailySalesStartDate} to ${dailySalesEndDate}` },
      format: format,
      recipients: []
    };

    if (format === 'pdf') {
      downloadReportAsPDF(report);
    } else {
      downloadReportAsExcel(report);
    }

    setShowDailySalesDialog(false);
  };

  const generatePerformanceWithDateRange = (format: 'pdf' | 'excel') => {
    const report: Report = {
      id: 'quick-performance',
      name: `Employee Performance Report (${performanceStartDate} to ${performanceEndDate})`,
      type: 'employee',
      schedule: 'manual',
      lastRun: '',
      metrics: ['sales', 'productivity', 'transactions'],
      filters: { dateRange: `${performanceStartDate} to ${performanceEndDate}` },
      format: format,
      recipients: []
    };

    if (format === 'pdf') {
      downloadReportAsPDF(report);
    } else {
      downloadReportAsExcel(report);
    }

    setShowPerformanceDialog(false);
  };

  const generatePayrollWithDateRange = (format: 'pdf' | 'excel') => {
    const report: Report = {
      id: 'quick-payroll',
      name: `Payroll Report (${payrollStartDate} to ${payrollEndDate})`,
      type: 'payroll',
      schedule: 'manual',
      lastRun: '',
      metrics: ['totalHours', 'overtime', 'compensation', 'clockIn'],
      filters: { dateRange: `${payrollStartDate} to ${payrollEndDate}` },
      format: format,
      recipients: []
    };

    if (format === 'pdf') {
      downloadReportAsPDF(report);
    } else {
      downloadReportAsExcel(report);
    }

    setShowPayrollDialog(false);
  };

  const generatePennySaleWithDateRange = (format: 'pdf' | 'excel') => {
    const report: Report = {
      id: 'quick-pennysale',
      name: `Penny Sale Report (${pennySaleStartDate} to ${pennySaleEndDate})`,
      type: 'pennysale',
      schedule: 'manual',
      lastRun: '',
      metrics: ['pennySaleItems', 'pennySaleTransactions', 'pennySaleCustomers'],
      filters: { dateRange: `${pennySaleStartDate} to ${pennySaleEndDate}` },
      format: format,
      recipients: []
    };

    if (format === 'pdf') {
      downloadReportAsPDF(report);
    } else {
      downloadReportAsExcel(report);
    }

    setShowPennySaleDialog(false);
  };

  const generateQuickInventoryReport = (format: 'pdf' | 'excel') => {
    const categoryFilter = quickInventoryCategory === "all" ? [] : [quickInventoryCategory];
    const report: Report = {
      id: 'quick-inventory',
      name: `Inventory Report${quickInventoryCategory !== "all" ? ` - ${quickInventoryCategory}` : ''}`,
      type: 'inventory',
      schedule: 'manual',
      lastRun: '',
      metrics: ['stockLevels', 'lowStock'],
      filters: { categories: categoryFilter },
      format: format,
      recipients: []
    };

    if (format === 'pdf') {
      downloadReportAsPDF(report);
    } else {
      downloadReportAsExcel(report);
    }

    setShowQuickInventoryDialog(false);
  };

  const downloadReportAsExcel = (report: Report) => {
    const data = generateReportData(report);
    const csvContent = convertToCSV(data);
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `${report.name.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const downloadASPDChart = (report: Report, startDate: string, endDate: string) => {
    const chartData = generateASPDChartData(startDate, endDate);
    const chartWindow = window.open('', '_blank');
    if (chartWindow) {
      chartWindow.document.write(`
        <html>
          <head>
            <title>ASPD Chart - ${report.name}</title>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .chart-container { width: 800px; height: 600px; margin: 20px auto; }
              .header { text-align: center; margin-bottom: 20px; }
              .no-print { margin: 20px 0; }
              @media print { .no-print { display: none; } }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>ASPD Report Chart</h1>
              <p>Period: ${startDate} to ${endDate}</p>
              <p>Generated: ${new Date().toLocaleString()}</p>
            </div>
            <div class="no-print">
              <button onclick="window.print()">Print Chart</button>
              <button onclick="downloadAsImage()">Download as Image</button>
            </div>
            <div class="chart-container">
              <canvas id="aspdChart"></canvas>
            </div>
            <script>
              const ctx = document.getElementById('aspdChart').getContext('2d');
              const chartData = ${JSON.stringify(chartData)};
              new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    title: {
                      display: true,
                      text: 'ASPD Data Trends Over Time'
                    },
                    legend: {
                      position: 'top'
                    }
                  },
                  scales: {
                    y: {
                      beginAtZero: true,
                      title: {
                        display: true,
                        text: 'Sales ($)'
                      }
                    },
                    x: {
                      title: {
                        display: true,
                        text: 'Date'
                      }
                    }
                  }
                }
              });

              function downloadAsImage() {
                const canvas = document.getElementById('aspdChart');
                const link = document.createElement('a');
                link.download = 'aspd-chart-${new Date().toISOString().split('T')[0]}.png';
                link.href = canvas.toDataURL();
                link.click();
              }
            </script>
          </body>
        </html>
      `);
      chartWindow.document.close();
    }
  };

  const generatePennySaleReport = (report: Report, startDate: string, endDate: string) => {
    // Mock penny sale data - in real app this would come from database
    const pennySaleData = [
      {
        date: "2024-01-15",
        transactionId: "TXN-001",
        customer: "John Doe",
        item: "Blue Dream",
        originalPrice: 12.00,
        salePrice: 0.01,
        reasonCode: "EMPLOYEE-DISCOUNT",
        employee: "Sarah Johnson",
        till: "Till #1 (Main Counter)",
        time: "14:30"
      },
      {
        date: "2024-01-14",
        transactionId: "TXN-002",
        customer: "Jane Smith",
        item: "OG Kush",
        originalPrice: 7.00,
        salePrice: 0.01,
        reasonCode: "DAMAGED-PRODUCT",
        employee: "Mike Chen",
        till: "Till #2 (Express Lane)",
        time: "16:45"
      },
      {
        date: "2024-01-13",
        transactionId: "TXN-003",
        customer: "Mike Johnson",
        item: "Edible Gummies",
        originalPrice: 25.00,
        salePrice: 0.01,
        reasonCode: "CUSTOMER-SERVICE",
        employee: "Sarah Johnson",
        till: "Till #1 (Main Counter)",
        time: "11:15"
      }
    ];

    return `
      <div class="header">
        <h1>Penny Sale Report</h1>
        <p><strong>Date Range:</strong> ${startDate} to ${endDate}</p>
        <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
      </div>
      <div class="content">
        <h2>Sales for $0.01</h2>
        <table style="width: 100%; margin-bottom: 20px;">
          <tr style="background: #e0e0e0;">
            <th style="padding: 8px; text-align: left;">Date</th>
            <th style="padding: 8px; text-align: left;">Transaction ID</th>
            <th style="padding: 8px; text-align: left;">Customer</th>
            <th style="padding: 8px; text-align: left;">Item</th>
            <th style="padding: 8px; text-align: right;">Original Price</th>
            <th style="padding: 8px; text-align: right;">Sale Price</th>
            <th style="padding: 8px; text-align: left;">Reason Code</th>
            <th style="padding: 8px; text-align: left;">Employee</th>
            <th style="padding: 8px; text-align: left;">Till/Register</th>
            <th style="padding: 8px; text-align: left;">Time</th>
          </tr>
          ${pennySaleData.map(sale => `
            <tr>
              <td style="padding: 8px; border-bottom: 1px solid #ddd;">${sale.date}</td>
              <td style="padding: 8px; border-bottom: 1px solid #ddd;">${sale.transactionId}</td>
              <td style="padding: 8px; border-bottom: 1px solid #ddd;">${sale.customer}</td>
              <td style="padding: 8px; border-bottom: 1px solid #ddd;">${sale.item}</td>
              <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">$${sale.originalPrice.toFixed(2)}</td>
              <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd; color: red; font-weight: bold;">$${sale.salePrice.toFixed(2)}</td>
              <td style="padding: 8px; border-bottom: 1px solid #ddd;">${sale.reasonCode}</td>
              <td style="padding: 8px; border-bottom: 1px solid #ddd;">${sale.employee}</td>
              <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold; color: #2563eb;">${sale.till}</td>
              <td style="padding: 8px; border-bottom: 1px solid #ddd;">${sale.time}</td>
            </tr>
          `).join('')}
        </table>

        <h2>Summary</h2>
        <table style="width: 50%;">
          <tr>
            <td style="padding: 5px; font-weight: bold;">Total Penny Sales:</td>
            <td style="padding: 5px;">${pennySaleData.length}</td>
          </tr>
          <tr>
            <td style="padding: 5px; font-weight: bold;">Total Revenue Lost:</td>
            <td style="padding: 5px;">$${pennySaleData.reduce((sum, sale) => sum + (sale.originalPrice - sale.salePrice), 0).toFixed(2)}</td>
          </tr>
          <tr>
            <td style="padding: 5px; font-weight: bold;">Actual Revenue:</td>
            <td style="padding: 5px;">$${(pennySaleData.length * 0.01).toFixed(2)}</td>
          </tr>
        </table>

        <h2>Reason Code Breakdown</h2>
        <table style="width: 50%;">
          ${Object.entries(pennySaleData.reduce((acc, sale) => {
            acc[sale.reasonCode] = (acc[sale.reasonCode] || 0) + 1;
            return acc;
          }, {} as Record<string, number>)).map(([reason, count]) => `
            <tr>
              <td style="padding: 5px; font-weight: bold;">${reason}:</td>
              <td style="padding: 5px;">${count}</td>
            </tr>
          `).join('')}
        </table>
      </div>
    `;
  };

  const generateASPDReportContent = (report: Report, metrics: string[]) => {
    const categories = ['Flower', 'Edibles', 'Concentrates', 'Pre-Rolls', 'Vapes', 'Topicals', 'Clones'];
    const items = {
      'Flower': ['Blue Dream', 'OG Kush', 'Gelato', 'Green Crack'],
      'Edibles': ['Gummy Bears', 'Chocolate Bar', 'Cookies'],
      'Concentrates': ['Live Resin', 'Shatter', 'Wax'],
      'Pre-Rolls': ['Indica Pack', 'Sativa Pack', 'Hybrid Pack'],
      'Vapes': ['Sativa Cart', 'Indica Cart', 'Hybrid Cart'],
      'Topicals': ['Pain Relief Balm', 'CBD Lotion'],
      'Clones': ['Blue Dream Clone', 'OG Kush Clone']
    };

    const categoryBreakdown = categories.map(category => {
      const categoryTotal = Math.random() * 5000 + 1000;
      const itemBreakdown = items[category].map(item => {
        const itemTotal = Math.random() * 800 + 100;
        const units = Math.floor(Math.random() * 50) + 10;
        return { name: item, total: itemTotal, units };
      });
      return { category, total: categoryTotal, items: itemBreakdown };
    });

    return `
      <div class="header">
        <h1>${report.name} - Detailed Breakdown</h1>
        <p><strong>Type:</strong> ASPD (Alcohol and Substance Prevention and Deterrence)</p>
        <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
        <p><strong>Period:</strong> Current Month</p>
      </div>
      <div class="content">
        <h2>Category Breakdown</h2>
        ${categoryBreakdown.map(cat => `
          <div class="category-section">
            <h3 style="background: #f0f0f0; padding: 10px; margin: 15px 0 5px 0;">
              ${cat.category} - Total: $${cat.total.toFixed(2)}
            </h3>
            <table style="width: 100%; margin-bottom: 20px;">
              <tr style="background: #e0e0e0;">
                <th style="padding: 8px; text-align: left;">Item Name</th>
                <th style="padding: 8px; text-align: right;">Units Sold</th>
                <th style="padding: 8px; text-align: right;">Revenue</th>
                <th style="padding: 8px; text-align: right;">Avg Price</th>
              </tr>
              ${cat.items.map(item => `
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #ddd;">${item.name}</td>
                  <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">${item.units}</td>
                  <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">$${item.total.toFixed(2)}</td>
                  <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">$${(item.total / item.units).toFixed(2)}</td>
                </tr>
              `).join('')}
            </table>
          </div>
        `).join('')}

        <h2>Summary Statistics</h2>
        <table style="width: 100%; margin-top: 20px;">
          <tr style="background: #e0e0e0;">
            <th style="padding: 10px; text-align: left;">Category</th>
            <th style="padding: 10px; text-align: right;">Total Revenue</th>
            <th style="padding: 10px; text-align: right;">Percentage of Total</th>
            <th style="padding: 10px; text-align: right;">Units Sold</th>
          </tr>
          ${categoryBreakdown.map(cat => {
            const totalRevenue = categoryBreakdown.reduce((sum, c) => sum + c.total, 0);
            const percentage = (cat.total / totalRevenue * 100).toFixed(1);
            const totalUnits = cat.items.reduce((sum, item) => sum + item.units, 0);
            return `
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;">${cat.category}</td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">$${cat.total.toFixed(2)}</td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">${percentage}%</td>
                <td style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">${totalUnits}</td>
              </tr>
            `;
          }).join('')}
        </table>
      </div>
    `;
  };

  const generateASPDChartData = (startDate: string, endDate: string) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const dates = [];
    const salesData = [];
    const categoryData = {
      'Flower': [],
      'Edibles': [],
      'Concentrates': [],
      'Pre-Rolls': [],
      'Vapes': []
    };

    // Generate daily data points
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
      dates.push(d.toISOString().split('T')[0]);
      const dailyTotal = Math.random() * 3000 + 1000;
      salesData.push(dailyTotal);

      // Generate category-specific data
      Object.keys(categoryData).forEach(category => {
        categoryData[category].push(Math.random() * 600 + 100);
      });
    }

    return {
      labels: dates,
      datasets: [
        {
          label: 'Total Sales',
          data: salesData,
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.1
        },
        ...Object.entries(categoryData).map(([category, data], index) => ({
          label: category,
          data: data,
          borderColor: `hsl(${index * 60}, 70%, 50%)`,
          backgroundColor: `hsla(${index * 60}, 70%, 50%, 0.1)`,
          tension: 0.1
        }))
      ]
    };
  };

  const shareReport = (report: Report) => {
    setReportToShare(report);
    setShareEmails("");
    setShowShareDialog(true);
  };

  const editReport = (report: Report) => {
    setReportToEdit(report);
    setReportName(report.name);
    setSelectedType(report.type);
    setSelectedMetrics(report.metrics);
    setSchedule(report.schedule);
    setFormat(report.format);
    setRecipients(report.recipients.join(", "));
    setShowEditDialog(true);
  };

  const updateReport = () => {
    if (!reportToEdit || !reportName || !selectedType || selectedMetrics.length === 0) {
      alert("Please fill in all required fields");
      return;
    }

    const updatedReport: Report = {
      ...reportToEdit,
      name: reportName,
      type: selectedType as Report['type'],
      metrics: selectedMetrics,
      schedule: schedule as Report['schedule'],
      format: format as Report['format'],
      recipients: recipients.split(",").map(email => email.trim()).filter(email => email.length > 0)
    };

    setReports(prev => prev.map(r => r.id === reportToEdit.id ? updatedReport : r));
    setShowEditDialog(false);
    setReportToEdit(null);
    resetForm();
    alert(`Report "${updatedReport.name}" has been updated successfully!`);
  };

  const deleteReport = (reportId: string) => {
    const report = reports.find(r => r.id === reportId);
    if (report && confirm(`Are you sure you want to delete the report "${report.name}"?`)) {
      setReports(prev => prev.filter(r => r.id !== reportId));
      alert(`Report "${report.name}" has been deleted.`);
    }
  };

  const resetForm = () => {
    setReportName("");
    setSelectedType("");
    setSelectedMetrics([]);
    setSchedule("manual");
    setFormat("pdf");
    setRecipients("");
    setSelectedCategories([]);
    setSelectedStores([]);
  };

  const generateComplianceReport = (report: Report, metrics: string[]) => {
    // Mock Metrc data
    const metrcData = [
      { id: "M001", name: "Blue Dream", category: "Flower", metrcQuantity: 150.5, packageTag: "1A4000000000022000000001" },
      { id: "M002", name: "OG Kush", category: "Flower", metrcQuantity: 89.25, packageTag: "1A4000000000022000000002" },
      { id: "M003", name: "Strawberry Gummies", category: "Edibles", metrcQuantity: 200, packageTag: "1A4000000000022000000003" },
      { id: "M004", name: "Live Resin Cart", category: "Concentrates", metrcQuantity: 45, packageTag: "1A4000000000022000000004" },
      { id: "M005", name: "CBD Tincture", category: "Tinctures", metrcQuantity: 75, packageTag: "1A4000000000022000000005" }
    ];

    // Mock inventory data
    const inventoryData = [
      { id: "I001", name: "Blue Dream", category: "Flower", inventoryQuantity: 148.0, sku: "BD-FL-001" },
      { id: "I002", name: "OG Kush", category: "Flower", inventoryQuantity: 91.5, sku: "OG-FL-002" },
      { id: "I003", name: "Strawberry Gummies", category: "Edibles", inventoryQuantity: 195, sku: "SG-ED-003" },
      { id: "I004", name: "Live Resin Cart", category: "Concentrates", inventoryQuantity: 45, sku: "LR-CO-004" },
      { id: "I006", name: "Sour Diesel", category: "Flower", inventoryQuantity: 125.5, sku: "SD-FL-006" }
    ];

    // Find matches and variances
    const comparisons = [];
    const metrcOnlyItems = [];
    const inventoryOnlyItems = [];

    metrcData.forEach(metrcItem => {
      const inventoryMatch = inventoryData.find(invItem =>
        invItem.name.toLowerCase() === metrcItem.name.toLowerCase()
      );

      if (inventoryMatch) {
        const variance = inventoryMatch.inventoryQuantity - metrcItem.metrcQuantity;
        comparisons.push({
          name: metrcItem.name,
          category: metrcItem.category,
          metrcQuantity: metrcItem.metrcQuantity,
          inventoryQuantity: inventoryMatch.inventoryQuantity,
          variance: variance,
          variancePercent: metrcItem.metrcQuantity > 0 ? (variance / metrcItem.metrcQuantity * 100).toFixed(2) : '0.00',
          packageTag: metrcItem.packageTag,
          sku: inventoryMatch.sku
        });
      } else {
        metrcOnlyItems.push(metrcItem);
      }
    });

    inventoryData.forEach(invItem => {
      const metrcMatch = metrcData.find(metrcItem =>
        metrcItem.name.toLowerCase() === invItem.name.toLowerCase()
      );

      if (!metrcMatch) {
        inventoryOnlyItems.push(invItem);
      }
    });

    return `
      <div class="header" style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">
        <h1 style="color: #d32f2f; font-size: 28px; margin: 0;">COMPLIANCE AUDIT REPORT</h1>
        <p style="font-size: 16px; margin: 5px 0;"><strong>Metrc vs Inventory Comparison</strong></p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Report Type:</strong> ${report.type.toUpperCase()}</p>
      </div>

      <div class="summary" style="background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-left: 4px solid #d32f2f;">
        <h2 style="color: #d32f2f; margin-top: 0;">EXECUTIVE SUMMARY</h2>
        <p><strong>Total Items Compared:</strong> ${comparisons.length}</p>
        <p><strong>Items Only in Metrc:</strong> ${metrcOnlyItems.length}</p>
        <p><strong>Items Only in Inventory:</strong> ${inventoryOnlyItems.length}</p>
        <p><strong>Variances Found:</strong> ${comparisons.filter(c => Math.abs(c.variance) > 0.01).length}</p>
        <p><strong>Compliance Status:</strong> ${comparisons.filter(c => Math.abs(c.variance) > 0.01).length === 0 && metrcOnlyItems.length === 0 && inventoryOnlyItems.length === 0 ? '<span style="color: green;">COMPLIANT</span>' : '<span style="color: red;">NON-COMPLIANT</span>'}</p>
      </div>

      <div class="comparison-table" style="margin-bottom: 30px;">
        <h2 style="color: #d32f2f;">METRC VS INVENTORY COMPARISON</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
          <thead>
            <tr style="background: #d32f2f; color: white;">
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Product Name</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Category</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Metrc Qty</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Inventory Qty</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Variance</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Variance %</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Package Tag</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">SKU</th>
            </tr>
          </thead>
          <tbody>
            ${comparisons.map(item => `
              <tr style="${Math.abs(item.variance) > 0.01 ? 'background: #ffebee;' : ''}">
                <td style="border: 1px solid #ccc; padding: 6px;">${item.name}</td>
                <td style="border: 1px solid #ccc; padding: 6px;">${item.category}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${item.metrcQuantity}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${item.inventoryQuantity}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: ${item.variance > 0 ? 'green' : item.variance < 0 ? 'red' : 'black'};">
                  ${item.variance > 0 ? '+' : ''}${item.variance.toFixed(2)}
                </td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: ${Math.abs(parseFloat(item.variancePercent)) > 5 ? 'red' : 'black'};">
                  ${item.variancePercent}%
                </td>
                <td style="border: 1px solid #ccc; padding: 6px; font-family: monospace; font-size: 10px;">${item.packageTag}</td>
                <td style="border: 1px solid #ccc; padding: 6px;">${item.sku}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>

      ${metrcOnlyItems.length > 0 ? `
        <div class="metrc-only" style="margin-bottom: 30px;">
          <h2 style="color: #ff9800;">ITEMS IN METRC BUT NOT IN INVENTORY</h2>
          <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
              <tr style="background: #ff9800; color: white;">
                <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Product Name</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Category</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Metrc Quantity</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Package Tag</th>
              </tr>
            </thead>
            <tbody>
              ${metrcOnlyItems.map(item => `
                <tr style="background: #fff3e0;">
                  <td style="border: 1px solid #ccc; padding: 6px;">${item.name}</td>
                  <td style="border: 1px solid #ccc; padding: 6px;">${item.category}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${item.metrcQuantity}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; font-family: monospace; font-size: 10px;">${item.packageTag}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      ` : ''}

      ${inventoryOnlyItems.length > 0 ? `
        <div class="inventory-only" style="margin-bottom: 30px;">
          <h2 style="color: #9c27b0;">ITEMS IN INVENTORY BUT NOT IN METRC</h2>
          <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
              <tr style="background: #9c27b0; color: white;">
                <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Product Name</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Category</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Inventory Quantity</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">SKU</th>
              </tr>
            </thead>
            <tbody>
              ${inventoryOnlyItems.map(item => `
                <tr style="background: #f3e5f5;">
                  <td style="border: 1px solid #ccc; padding: 6px;">${item.name}</td>
                  <td style="border: 1px solid #ccc; padding: 6px;">${item.category}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${item.inventoryQuantity}</td>
                  <td style="border: 1px solid #ccc; padding: 6px;">${item.sku}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      ` : ''}

      <div class="footer" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666;">
        <p><strong>Report Generated By:</strong> Cannabis POS System</p>
        <p><strong>Next Audit Due:</strong> ${new Date(Date.now() + 7*24*60*60*1000).toLocaleDateString()}</p>
        <p><strong>Compliance Officer:</strong> ________________________</p>
        <p><strong>Manager Approval:</strong> ________________________</p>
      </div>
    `;
  };

  const generatePayrollReport = (report: Report) => {
    // Mock payroll data with detailed clock in/out times - in real app this would come from time tracking system
    const payrollData = [
      {
        id: "emp1",
        name: "Sarah Johnson",
        role: "Manager",
        payPeriod: "January 1-15, 2024",
        totalHours: 80.25,
        regularHours: 80.00,
        overtimeHours: 0.25,
        hourlyRate: 25.00,
        timeEntries: [
          { date: "2024-01-15", clockIn: "08:00", clockOut: "17:30", break: "0:30", totalHours: 9.0 },
          { date: "2024-01-14", clockIn: "08:15", clockOut: "17:45", break: "0:45", totalHours: 8.75 },
          { date: "2024-01-13", clockIn: "07:45", clockOut: "16:30", break: "0:30", totalHours: 8.25 },
          { date: "2024-01-12", clockIn: "08:00", clockOut: "17:00", break: "1:00", totalHours: 8.0 },
          { date: "2024-01-11", clockIn: "08:30", clockOut: "17:45", break: "0:30", totalHours: 8.75 },
          { date: "2024-01-10", clockIn: "08:00", clockOut: "17:15", break: "0:45", totalHours: 8.5 },
          { date: "2024-01-09", clockIn: "08:15", clockOut: "17:30", break: "0:30", totalHours: 8.75 },
          { date: "2024-01-08", clockIn: "08:00", clockOut: "17:00", break: "1:00", totalHours: 8.0 },
          { date: "2024-01-05", clockIn: "07:30", clockOut: "17:00", break: "0:30", totalHours: 9.0 },
          { date: "2024-01-04", clockIn: "08:00", clockOut: "16:45", break: "0:45", totalHours: 8.0 }
        ]
      },
      {
        id: "emp2",
        name: "Mike Chen",
        role: "Budtender",
        payPeriod: "January 1-15, 2024",
        totalHours: 76.5,
        regularHours: 76.5,
        overtimeHours: 0,
        hourlyRate: 18.50,
        timeEntries: [
          { date: "2024-01-15", clockIn: "09:00", clockOut: "17:00", break: "0:30", totalHours: 7.5 },
          { date: "2024-01-14", clockIn: "09:15", clockOut: "18:00", break: "0:45", totalHours: 8.0 },
          { date: "2024-01-13", clockIn: "09:00", clockOut: "17:30", break: "0:30", totalHours: 8.0 },
          { date: "2024-01-12", clockIn: "09:30", clockOut: "17:45", break: "0:45", totalHours: 7.5 },
          { date: "2024-01-11", clockIn: "09:00", clockOut: "18:00", break: "1:00", totalHours: 8.0 },
          { date: "2024-01-10", clockIn: "09:15", clockOut: "17:30", break: "0:45", totalHours: 7.5 },
          { date: "2024-01-09", clockIn: "09:00", clockOut: "17:00", break: "0:30", totalHours: 7.5 },
          { date: "2024-01-08", clockIn: "09:30", clockOut: "18:15", break: "1:15", totalHours: 7.5 },
          { date: "2024-01-05", clockIn: "09:00", clockOut: "17:45", break: "0:45", totalHours: 8.0 },
          { date: "2024-01-04", clockIn: "09:15", clockOut: "17:30", break: "0:45", totalHours: 7.5 }
        ]
      },
      {
        id: "emp3",
        name: "Emma Rodriguez",
        role: "Cashier",
        payPeriod: "January 1-15, 2024",
        totalHours: 70.0,
        regularHours: 70.0,
        overtimeHours: 0,
        hourlyRate: 16.00,
        timeEntries: [
          { date: "2024-01-15", clockIn: "10:00", clockOut: "18:00", break: "1:00", totalHours: 7.0 },
          { date: "2024-01-14", clockIn: "10:30", clockOut: "18:30", break: "1:00", totalHours: 7.0 },
          { date: "2024-01-13", clockIn: "10:00", clockOut: "17:00", break: "0:30", totalHours: 6.5 },
          { date: "2024-01-12", clockIn: "10:15", clockOut: "18:15", break: "1:00", totalHours: 7.0 },
          { date: "2024-01-11", clockIn: "10:00", clockOut: "18:00", break: "1:00", totalHours: 7.0 },
          { date: "2024-01-10", clockIn: "10:30", clockOut: "17:30", break: "0:30", totalHours: 6.5 },
          { date: "2024-01-09", clockIn: "10:00", clockOut: "18:00", break: "1:00", totalHours: 7.0 },
          { date: "2024-01-08", clockIn: "10:15", clockOut: "17:45", break: "0:30", totalHours: 7.0 },
          { date: "2024-01-05", clockIn: "10:00", clockOut: "18:30", break: "1:30", totalHours: 7.0 },
          { date: "2024-01-04", clockIn: "10:30", clockOut: "18:00", break: "1:00", totalHours: 6.5 }
        ]
      }
    ];

    const totalPayrollHours = payrollData.reduce((sum, emp) => sum + emp.totalHours, 0);
    const totalRegularHours = payrollData.reduce((sum, emp) => sum + emp.regularHours, 0);
    const totalOvertimeHours = payrollData.reduce((sum, emp) => sum + emp.overtimeHours, 0);
    const totalPayrollCost = payrollData.reduce((sum, emp) => sum + (emp.regularHours * emp.hourlyRate) + (emp.overtimeHours * emp.hourlyRate * 1.5), 0);

    return `
      <div class="header" style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">
        <h1 style="color: #1f2937; font-size: 28px; margin: 0;">PAYROLL REPORT</h1>
        <p style="font-size: 16px; margin: 5px 0;"><strong>Employee Hours and Time Tracking</strong></p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Pay Period:</strong> January 1-15, 2024</p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
      </div>

      <div class="summary" style="background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-left: 4px solid #1f2937;">
        <h2 style="color: #1f2937; margin-top: 0;">PAYROLL SUMMARY</h2>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
          <div>
            <p><strong>Total Hours:</strong> <span style="color: #16a34a; font-size: 18px;">${totalPayrollHours}h</span></p>
            <p><strong>Active Employees:</strong> ${payrollData.length}</p>
          </div>
          <div>
            <p><strong>Regular Hours:</strong> ${totalRegularHours}h</p>
            <p><strong>Overtime Hours:</strong> ${totalOvertimeHours}h</p>
          </div>
          <div>
            <p><strong>Total Payroll Cost:</strong> <span style="color: #2563eb; font-size: 16px;">$${totalPayrollCost.toFixed(2)}</span></p>
            <p><strong>Avg Hours/Employee:</strong> ${(totalPayrollHours / payrollData.length).toFixed(1)}h</p>
          </div>
          <div>
            <p><strong>Overtime Rate:</strong> ${((totalOvertimeHours / totalPayrollHours) * 100).toFixed(1)}%</p>
            <p><strong>Pay Period Days:</strong> 15</p>
          </div>
        </div>
      </div>

      ${payrollData.map(employee => `
        <div class="employee-section" style="margin-bottom: 40px; page-break-inside: avoid;">
          <div style="background: #e5e7eb; padding: 10px; margin-bottom: 15px; border-left: 4px solid #374151;">
            <h3 style="color: #1f2937; margin: 0; font-size: 18px;">${employee.name} - ${employee.role}</h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 8px; font-size: 14px;">
              <div><strong>Total Hours:</strong> ${employee.totalHours}h</div>
              <div><strong>Regular Hours:</strong> ${employee.regularHours}h</div>
              <div><strong>Overtime Hours:</strong> ${employee.overtimeHours}h</div>
              <div><strong>Hourly Rate:</strong> $${employee.hourlyRate.toFixed(2)}</div>
            </div>
            <div style="margin-top: 8px; font-size: 14px;">
              <strong>Gross Pay:</strong> $${((employee.regularHours * employee.hourlyRate) + (employee.overtimeHours * employee.hourlyRate * 1.5)).toFixed(2)}
              (Regular: $${(employee.regularHours * employee.hourlyRate).toFixed(2)}, Overtime: $${(employee.overtimeHours * employee.hourlyRate * 1.5).toFixed(2)})
            </div>
          </div>

          <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px;">
            <thead>
              <tr style="background: #374151; color: white;">
                <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Date</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Clock In</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Clock Out</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Break Time</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Total Hours</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Day Type</th>
              </tr>
            </thead>
            <tbody>
              ${employee.timeEntries.map(entry => {
                const dayOfWeek = new Date(entry.date).toLocaleDateString('en-US', { weekday: 'short' });
                const isWeekend = dayOfWeek === 'Sat' || dayOfWeek === 'Sun';
                return `
                  <tr style="${isWeekend ? 'background: #fef3c7;' : ''}">
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: center; font-weight: bold;">
                      ${new Date(entry.date).toLocaleDateString()} (${dayOfWeek})
                    </td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${entry.clockIn}</td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${entry.clockOut}</td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${entry.break}</td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: center; font-weight: bold; color: #16a34a;">
                      ${entry.totalHours.toFixed(2)}h
                    </td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">
                      ${isWeekend ? '<span style="color: #d97706;">Weekend</span>' : 'Weekday'}
                    </td>
                  </tr>
                `;
              }).join('')}
              <tr style="background: #f3f4f6; font-weight: bold;">
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;" colspan="4">TOTAL FOR ${employee.name}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center; color: #16a34a;">${employee.totalHours}h</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">-</td>
              </tr>
            </tbody>
          </table>
        </div>
      `).join('')}

      <div class="payroll-summary-table" style="margin-bottom: 30px;">
        <h2 style="color: #1f2937;">EMPLOYEE PAYROLL SUMMARY</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
          <thead>
            <tr style="background: #1f2937; color: white;">
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Employee Name</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Role</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Regular Hours</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Overtime Hours</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Total Hours</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Hourly Rate</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Gross Pay</th>
            </tr>
          </thead>
          <tbody>
            ${payrollData.map(emp => `
              <tr>
                <td style="border: 1px solid #ccc; padding: 6px; font-weight: bold;">${emp.name}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${emp.role}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${emp.regularHours}h</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: ${emp.overtimeHours > 0 ? '#dc2626' : '#6b7280'};">${emp.overtimeHours}h</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right; font-weight: bold;">${emp.totalHours}h</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${emp.hourlyRate.toFixed(2)}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right; font-weight: bold; color: #16a34a;">
                  $${((emp.regularHours * emp.hourlyRate) + (emp.overtimeHours * emp.hourlyRate * 1.5)).toFixed(2)}
                </td>
              </tr>
            `).join('')}
            <tr style="background: #e5e7eb; font-weight: bold; font-size: 14px;">
              <td style="border: 1px solid #ccc; padding: 8px;">PAYROLL TOTALS</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">-</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">${totalRegularHours}h</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right; color: #dc2626;">${totalOvertimeHours}h</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">${totalPayrollHours}h</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">-</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right; color: #16a34a;">$${totalPayrollCost.toFixed(2)}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="footer" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666;">
        <p><strong>Report Generated By:</strong> Cannabis POS System</p>
        <p><strong>Payroll Administrator:</strong> ________________________</p>
        <p><strong>HR Manager:</strong> ________________________</p>
        <p><strong>Next Pay Period:</strong> January 16-31, 2024</p>
        <p><strong>Note:</strong> Overtime calculated at 1.5x regular hourly rate for hours over 40 per week</p>
      </div>
    `;
  };

  const generateEmployeePerformanceReport = (report: Report) => {
    // Mock employee performance data - in real app this would come from database
    const employees = [
      {
        id: "emp1",
        name: "Sarah Johnson",
        role: "Manager",
        totalTransactions: 95,
        totalSales: 5850.75,
        totalDiscounts: 425.50,
        discountCount: 18,
        loyaltySignups: 8,
        salesVoided: 2,
        voidedAmount: 125.50,
        hoursWorked: 40,
        salesPerHour: 146.27,
        avgTransactionSize: 61.59
      },
      {
        id: "emp2",
        name: "Mike Chen",
        role: "Budtender",
        totalTransactions: 128,
        totalSales: 7250.25,
        totalDiscounts: 380.75,
        discountCount: 22,
        loyaltySignups: 12,
        salesVoided: 1,
        voidedAmount: 45.00,
        hoursWorked: 38,
        salesPerHour: 190.80,
        avgTransactionSize: 56.64
      },
      {
        id: "emp3",
        name: "Emma Rodriguez",
        role: "Cashier",
        totalTransactions: 156,
        totalSales: 8420.50,
        totalDiscounts: 520.25,
        discountCount: 28,
        loyaltySignups: 15,
        salesVoided: 3,
        voidedAmount: 180.75,
        hoursWorked: 35,
        salesPerHour: 240.58,
        avgTransactionSize: 53.98
      },
      {
        id: "emp4",
        name: "Lisa Park",
        role: "Budtender",
        totalTransactions: 89,
        totalSales: 4950.25,
        totalDiscounts: 285.50,
        discountCount: 15,
        loyaltySignups: 6,
        salesVoided: 1,
        voidedAmount: 35.25,
        hoursWorked: 32,
        salesPerHour: 154.70,
        avgTransactionSize: 55.62
      },
      {
        id: "emp5",
        name: "John Smith",
        role: "Assistant Manager",
        totalTransactions: 112,
        totalSales: 6850.75,
        totalDiscounts: 395.25,
        discountCount: 19,
        loyaltySignups: 9,
        salesVoided: 2,
        voidedAmount: 95.50,
        hoursWorked: 36,
        salesPerHour: 190.30,
        avgTransactionSize: 61.17
      }
    ];

    const totalSales = employees.reduce((sum, emp) => sum + emp.totalSales, 0);
    const totalTransactions = employees.reduce((sum, emp) => sum + emp.totalTransactions, 0);
    const totalDiscounts = employees.reduce((sum, emp) => sum + emp.totalDiscounts, 0);
    const totalLoyaltySignups = employees.reduce((sum, emp) => sum + emp.loyaltySignups, 0);
    const totalVoided = employees.reduce((sum, emp) => sum + emp.voidedAmount, 0);
    const totalHours = employees.reduce((sum, emp) => sum + emp.hoursWorked, 0);

    // Sort employees by sales for ranking
    const sortedBySales = [...employees].sort((a, b) => b.totalSales - a.totalSales);

    return `
      <div class="header" style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">
        <h1 style="color: #1f2937; font-size: 28px; margin: 0;">EMPLOYEE PERFORMANCE REPORT</h1>
        <p style="font-size: 16px; margin: 5px 0;"><strong>Individual Employee Performance Analysis</strong></p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Report Period:</strong> ${new Date().toLocaleDateString()}</p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
      </div>

      <div class="summary" style="background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-left: 4px solid #1f2937;">
        <h2 style="color: #1f2937; margin-top: 0;">TEAM PERFORMANCE OVERVIEW</h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
          <div>
            <p><strong>Total Team Sales:</strong> <span style="color: #16a34a; font-size: 18px;">$${totalSales.toLocaleString()}</span></p>
            <p><strong>Total Transactions:</strong> ${totalTransactions}</p>
            <p><strong>Average per Employee:</strong> $${(totalSales / employees.length).toFixed(2)}</p>
          </div>
          <div>
            <p><strong>Total Discounts Given:</strong> $${totalDiscounts.toFixed(2)}</p>
            <p><strong>Total Loyalty Signups:</strong> ${totalLoyaltySignups}</p>
            <p><strong>Discount Rate:</strong> ${((totalDiscounts / totalSales) * 100).toFixed(2)}%</p>
          </div>
          <div>
            <p><strong>Total Sales Voided:</strong> $${totalVoided.toFixed(2)}</p>
            <p><strong>Void Rate:</strong> ${((totalVoided / totalSales) * 100).toFixed(3)}%</p>
            <p><strong>Active Employees:</strong> ${employees.length}</p>
          </div>
        </div>
      </div>

      <div class="performance-table" style="margin-bottom: 30px;">
        <h2 style="color: #1f2937;">INDIVIDUAL EMPLOYEE PERFORMANCE</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
          <thead>
            <tr style="background: #1f2937; color: white;">
              <th style="border: 1px solid #ccc; padding: 6px; text-align: left;">Employee Name</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: center;">Role</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Total Transactions</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Total Sales</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Avg Transaction</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Hours Clocked</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Discounts Given</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: center;">Loyalty Signups</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Sales Voided</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Void Amount</th>
              <th style="border: 1px solid #ccc; padding: 6px; text-align: right;">Sales/Hour</th>
            </tr>
          </thead>
          <tbody>
            ${employees.map((emp, index) => {
              const rank = sortedBySales.findIndex(e => e.id === emp.id) + 1;
              const isTopPerformer = rank <= 2;
              return `
                <tr style="${isTopPerformer ? 'background: #fef3c7;' : ''}">
                  <td style="border: 1px solid #ccc; padding: 6px; font-weight: bold;">
                    ${emp.name} ${isTopPerformer ? `<span style="color: #d97706;">(#${rank})</span>` : ''}
                  </td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${emp.role}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right; font-weight: bold;">${emp.totalTransactions}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right; font-weight: bold; color: #16a34a;">$${emp.totalSales.toFixed(2)}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${emp.avgTransactionSize.toFixed(2)}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right; font-weight: bold; color: #7c3aed;">${emp.hoursWorked}h</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: #dc2626;">
                    $${emp.totalDiscounts.toFixed(2)} (${emp.discountCount})
                  </td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: center; font-weight: bold; color: #2563eb;">${emp.loyaltySignups}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${emp.salesVoided}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: #dc2626;">$${emp.voidedAmount.toFixed(2)}</td>
                  <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${emp.salesPerHour.toFixed(2)}</td>
                </tr>
              `;
            }).join('')}
            <tr style="background: #e5e7eb; font-weight: bold;">
              <td style="border: 1px solid #ccc; padding: 6px;">TEAM TOTALS</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">-</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${totalTransactions}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: #16a34a;">$${totalSales.toFixed(2)}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${(totalSales / totalTransactions).toFixed(2)}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right; font-weight: bold; color: #7c3aed;">${totalHours}h</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: #dc2626;">$${totalDiscounts.toFixed(2)}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: center; color: #2563eb;">${totalLoyaltySignups}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${employees.reduce((sum, emp) => sum + emp.salesVoided, 0)}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: #dc2626;">$${totalVoided.toFixed(2)}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${employees.reduce((sum, emp) => sum + emp.salesPerHour, 0).toFixed(2)}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="top-performers" style="margin-bottom: 30px;">
        <h2 style="color: #1f2937;">TOP PERFORMERS RANKING</h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
          <div>
            <h3 style="color: #16a34a;">🏆 Top Sales Performers</h3>
            <ol style="padding-left: 20px;">
              ${sortedBySales.slice(0, 3).map(emp => `
                <li style="margin: 5px 0;">
                  <strong>${emp.name}</strong> - $${emp.totalSales.toFixed(2)}
                  <br><small style="color: #666;">${emp.totalTransactions} transactions</small>
                </li>
              `).join('')}
            </ol>
          </div>
          <div>
            <h3 style="color: #2563eb;">🎯 Top Loyalty Recruiters</h3>
            <ol style="padding-left: 20px;">
              ${[...employees].sort((a, b) => b.loyaltySignups - a.loyaltySignups).slice(0, 3).map(emp => `
                <li style="margin: 5px 0;">
                  <strong>${emp.name}</strong> - ${emp.loyaltySignups} signups
                  <br><small style="color: #666;">${emp.role}</small>
                </li>
              `).join('')}
            </ol>
          </div>
          <div>
            <h3 style="color: #dc2626;">⚠️ Highest Void Rates</h3>
            <ol style="padding-left: 20px;">
              ${[...employees].sort((a, b) => (b.voidedAmount / b.totalSales) - (a.voidedAmount / a.totalSales)).slice(0, 3).map(emp => `
                <li style="margin: 5px 0;">
                  <strong>${emp.name}</strong> - ${((emp.voidedAmount / emp.totalSales) * 100).toFixed(2)}%
                  <br><small style="color: #666;">$${emp.voidedAmount.toFixed(2)} voided</small>
                </li>
              `).join('')}
            </ol>
          </div>
        </div>
      </div>

      <div class="performance-metrics" style="margin-bottom: 30px;">
        <h2 style="color: #1f2937;">KEY PERFORMANCE METRICS</h2>
        <table style="width: 80%; border-collapse: collapse; font-size: 12px;">
          <thead>
            <tr style="background: #374151; color: white;">
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Metric</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Team Average</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Best Performer</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Needs Improvement</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="border: 1px solid #ccc; padding: 6px; font-weight: bold;">Average Transaction Size</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${(totalSales / totalTransactions).toFixed(2)}</td>
              <td style="border: 1px solid #ccc; padding: 6px;">${[...employees].sort((a, b) => b.avgTransactionSize - a.avgTransactionSize)[0].name} ($${[...employees].sort((a, b) => b.avgTransactionSize - a.avgTransactionSize)[0].avgTransactionSize.toFixed(2)})</td>
              <td style="border: 1px solid #ccc; padding: 6px;">${[...employees].sort((a, b) => a.avgTransactionSize - b.avgTransactionSize)[0].name} ($${[...employees].sort((a, b) => a.avgTransactionSize - b.avgTransactionSize)[0].avgTransactionSize.toFixed(2)})</td>
            </tr>
            <tr>
              <td style="border: 1px solid #ccc; padding: 6px; font-weight: bold;">Sales per Hour</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${(employees.reduce((sum, emp) => sum + emp.salesPerHour, 0) / employees.length).toFixed(2)}</td>
              <td style="border: 1px solid #ccc; padding: 6px;">${[...employees].sort((a, b) => b.salesPerHour - a.salesPerHour)[0].name} ($${[...employees].sort((a, b) => b.salesPerHour - a.salesPerHour)[0].salesPerHour.toFixed(2)})</td>
              <td style="border: 1px solid #ccc; padding: 6px;">${[...employees].sort((a, b) => a.salesPerHour - b.salesPerHour)[0].name} ($${[...employees].sort((a, b) => a.salesPerHour - b.salesPerHour)[0].salesPerHour.toFixed(2)})</td>
            </tr>
            <tr>
              <td style="border: 1px solid #ccc; padding: 6px; font-weight: bold;">Loyalty Signups per Employee</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${(totalLoyaltySignups / employees.length).toFixed(1)}</td>
              <td style="border: 1px solid #ccc; padding: 6px;">${[...employees].sort((a, b) => b.loyaltySignups - a.loyaltySignups)[0].name} (${[...employees].sort((a, b) => b.loyaltySignups - a.loyaltySignups)[0].loyaltySignups})</td>
              <td style="border: 1px solid #ccc; padding: 6px;">${[...employees].sort((a, b) => a.loyaltySignups - b.loyaltySignups)[0].name} (${[...employees].sort((a, b) => a.loyaltySignups - b.loyaltySignups)[0].loyaltySignups})</td>
            </tr>
            <tr>
              <td style="border: 1px solid #ccc; padding: 6px; font-weight: bold;">Void Rate</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${((totalVoided / totalSales) * 100).toFixed(3)}%</td>
              <td style="border: 1px solid #ccc; padding: 6px;">${[...employees].sort((a, b) => (a.voidedAmount / a.totalSales) - (b.voidedAmount / b.totalSales))[0].name} (${(([...employees].sort((a, b) => (a.voidedAmount / a.totalSales) - (b.voidedAmount / b.totalSales))[0].voidedAmount / [...employees].sort((a, b) => (a.voidedAmount / a.totalSales) - (b.voidedAmount / b.totalSales))[0].totalSales) * 100).toFixed(3)}%)</td>
              <td style="border: 1px solid #ccc; padding: 6px;">${[...employees].sort((a, b) => (b.voidedAmount / b.totalSales) - (a.voidedAmount / a.totalSales))[0].name} (${(([...employees].sort((a, b) => (b.voidedAmount / b.totalSales) - (a.voidedAmount / a.totalSales))[0].voidedAmount / [...employees].sort((a, b) => (b.voidedAmount / b.totalSales) - (a.voidedAmount / a.totalSales))[0].totalSales) * 100).toFixed(3)}%)</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="footer" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666;">
        <p><strong>Report Generated By:</strong> Cannabis POS System</p>
        <p><strong>HR Manager:</strong> ________________________</p>
        <p><strong>Store Manager:</strong> ________________________</p>
        <p><strong>Next Performance Review:</strong> ${new Date(Date.now() + 30*24*60*60*1000).toLocaleDateString()}</p>
      </div>
    `;
  };

  const generateDailySalesReport = (report: Report) => {
    // Mock sales data - in real app this would come from database
    const dailyData = {
      date: new Date().toLocaleDateString(),
      totalSales: 8450.75,
      totalVisits: 145,
      totalDebit: 6200.50,
      totalCash: 2250.25,
      averageSale: 58.28,
      monthlyPace: 254523.00, // Based on current daily average
      transactionCount: 145,
      returnCount: 3,
      voidCount: 2
    };

    // Mock top discounts data
    const topDiscounts = [
      { name: "Daily Flower Special", usage: 45, totalDiscount: 425.50, reasonCode: "DAILY-FLOWER" },
      { name: "First Time Customer", usage: 12, totalDiscount: 180.00, reasonCode: "FIRST-TIME" },
      { name: "Loyalty Member 10%", usage: 28, totalDiscount: 315.75, reasonCode: "LOYALTY-10" },
      { name: "Senior Discount", usage: 8, totalDiscount: 95.25, reasonCode: "SENIOR" },
      { name: "Employee Discount", usage: 3, totalDiscount: 67.50, reasonCode: "EMPLOYEE" }
    ];

    // Mock hourly breakdown
    const hourlyBreakdown = [
      { hour: "9:00 AM", sales: 245.50, visits: 8, avgSale: 30.69 },
      { hour: "10:00 AM", sales: 520.75, visits: 15, avgSale: 34.72 },
      { hour: "11:00 AM", sales: 675.25, visits: 18, avgSale: 37.51 },
      { hour: "12:00 PM", sales: 890.50, visits: 22, avgSale: 40.48 },
      { hour: "1:00 PM", sales: 1025.75, visits: 25, avgSale: 41.03 },
      { hour: "2:00 PM", sales: 825.25, visits: 19, avgSale: 43.43 },
      { hour: "3:00 PM", sales: 950.75, visits: 21, avgSale: 45.27 },
      { hour: "4:00 PM", sales: 1100.25, visits: 24, avgSale: 45.84 },
      { hour: "5:00 PM", sales: 1250.50, visits: 28, avgSale: 44.66 },
      { hour: "6:00 PM", sales: 845.75, visits: 20, avgSale: 42.29 },
      { hour: "7:00 PM", sales: 560.50, visits: 15, avgSale: 37.37 }
    ];

    const totalDiscountAmount = topDiscounts.reduce((sum, discount) => sum + discount.totalDiscount, 0);

    return `
      <div class="header" style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">
        <h1 style="color: #1f2937; font-size: 28px; margin: 0;">DAILY SALES REPORT</h1>
        <p style="font-size: 16px; margin: 5px 0;"><strong>Complete Daily Performance Summary</strong></p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Date:</strong> ${dailyData.date}</p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
      </div>

      <div class="summary" style="background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-left: 4px solid #1f2937;">
        <h2 style="color: #1f2937; margin-top: 0;">DAILY PERFORMANCE SUMMARY</h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
          <div>
            <p><strong>Total Sales:</strong> <span style="color: #16a34a; font-size: 18px;">$${dailyData.totalSales.toLocaleString()}</span></p>
            <p><strong>Total Visits:</strong> ${dailyData.totalVisits}</p>
            <p><strong>Average Sale:</strong> $${dailyData.averageSale.toFixed(2)}</p>
          </div>
          <div>
            <p><strong>Total Cash:</strong> $${dailyData.totalCash.toLocaleString()}</p>
            <p><strong>Total Debit:</strong> $${dailyData.totalDebit.toLocaleString()}</p>
            <p><strong>Cash Ratio:</strong> ${((dailyData.totalCash / dailyData.totalSales) * 100).toFixed(1)}%</p>
          </div>
          <div>
            <p><strong>Monthly Pace:</strong> <span style="color: #2563eb; font-size: 16px;">$${dailyData.monthlyPace.toLocaleString()}</span></p>
            <p><strong>Transaction Count:</strong> ${dailyData.transactionCount}</p>
            <p><strong>Returns/Voids:</strong> ${dailyData.returnCount}/${dailyData.voidCount}</p>
          </div>
        </div>
      </div>

      <div class="payment-breakdown" style="margin-bottom: 30px;">
        <h2 style="color: #1f2937;">PAYMENT METHOD BREAKDOWN</h2>
        <table style="width: 60%; border-collapse: collapse; font-size: 14px;">
          <thead>
            <tr style="background: #1f2937; color: white;">
              <th style="border: 1px solid #ccc; padding: 10px; text-align: left;">Payment Method</th>
              <th style="border: 1px solid #ccc; padding: 10px; text-align: right;">Amount</th>
              <th style="border: 1px solid #ccc; padding: 10px; text-align: right;">Percentage</th>
              <th style="border: 1px solid #ccc; padding: 10px; text-align: right;">Transaction Count</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="border: 1px solid #ccc; padding: 8px; font-weight: bold;">Cash</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right; font-weight: bold;">$${dailyData.totalCash.toLocaleString()}</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">${((dailyData.totalCash / dailyData.totalSales) * 100).toFixed(1)}%</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">${Math.round(dailyData.totalVisits * 0.35)}</td>
            </tr>
            <tr>
              <td style="border: 1px solid #ccc; padding: 8px; font-weight: bold;">Debit Card</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right; font-weight: bold;">$${dailyData.totalDebit.toLocaleString()}</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">${((dailyData.totalDebit / dailyData.totalSales) * 100).toFixed(1)}%</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">${Math.round(dailyData.totalVisits * 0.65)}</td>
            </tr>
            <tr style="background: #f9fafb; font-weight: bold;">
              <td style="border: 1px solid #ccc; padding: 8px;">TOTAL</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">$${dailyData.totalSales.toLocaleString()}</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">100.0%</td>
              <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">${dailyData.totalVisits}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="top-discounts" style="margin-bottom: 30px;">
        <h2 style="color: #1f2937;">TOP 5 MOST USED DISCOUNTS</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
          <thead>
            <tr style="background: #374151; color: white;">
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Discount Name</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Usage Count</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Total Discount Amount</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Reason Code</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Avg Discount</th>
            </tr>
          </thead>
          <tbody>
            ${topDiscounts.map((discount, index) => `
              <tr style="${index === 0 ? 'background: #fef3c7;' : ''}">
                <td style="border: 1px solid #ccc; padding: 6px; font-weight: ${index === 0 ? 'bold' : 'normal'};">${discount.name}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center; font-weight: bold;">${discount.usage}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: #dc2626;">$${discount.totalDiscount.toFixed(2)}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center; font-family: monospace; font-size: 10px;">${discount.reasonCode}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${(discount.totalDiscount / discount.usage).toFixed(2)}</td>
              </tr>
            `).join('')}
            <tr style="background: #fee2e2; font-weight: bold;">
              <td style="border: 1px solid #ccc; padding: 6px;">TOTAL DISCOUNTS</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${topDiscounts.reduce((sum, d) => sum + d.usage, 0)}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: #dc2626;">$${totalDiscountAmount.toFixed(2)}</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">-</td>
              <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${(totalDiscountAmount / topDiscounts.reduce((sum, d) => sum + d.usage, 0)).toFixed(2)}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="hourly-breakdown" style="margin-bottom: 30px;">
        <h2 style="color: #1f2937;">HOURLY SALES BREAKDOWN</h2>
        <table style="width: 80%; border-collapse: collapse; font-size: 12px;">
          <thead>
            <tr style="background: #6b7280; color: white;">
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Hour</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Sales Amount</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Visits</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Average Sale</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">% of Daily Total</th>
            </tr>
          </thead>
          <tbody>
            ${hourlyBreakdown.map(hour => `
              <tr>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center; font-weight: bold;">${hour.hour}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${hour.sales.toFixed(2)}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${hour.visits}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">$${hour.avgSale.toFixed(2)}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${((hour.sales / dailyData.totalSales) * 100).toFixed(1)}%</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>

      <div class="monthly-projection" style="background: #eff6ff; padding: 15px; margin-bottom: 20px; border-left: 4px solid #2563eb;">
        <h2 style="color: #1e40af; margin-top: 0;">MONTHLY PROJECTION</h2>
        <p><strong>Based on current daily average of $${dailyData.averageSale.toFixed(2)} per transaction:</strong></p>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
          <div>
            <p>Projected Monthly Sales: <strong style="color: #1d4ed8;">$${dailyData.monthlyPace.toLocaleString()}</strong></p>
            <p>Projected Monthly Visits: <strong>${(dailyData.totalVisits * 30).toLocaleString()}</strong></p>
          </div>
          <div>
            <p>Days remaining in month: <strong>${30 - new Date().getDate()}</strong></p>
            <p>Required daily average to reach goal: <strong>$${((300000 - dailyData.totalSales) / (30 - new Date().getDate())).toFixed(2)}</strong></p>
          </div>
        </div>
      </div>

      <div class="footer" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666;">
        <p><strong>Report Generated By:</strong> Cannabis POS System</p>
        <p><strong>Store Manager:</strong> ________________________</p>
        <p><strong>Regional Manager:</strong> ________________________</p>
        <p><strong>Next Business Day Target:</strong> $${(dailyData.totalSales * 1.05).toFixed(2)} (+5%)</p>
      </div>
    `;
  };

  const generateInventoryReport = (report: Report, selectedCategories?: string[], selectedStores?: string[]) => {
    // Mock inventory data - in real app this would come from database
    const inventoryData = [
      { id: "1", name: "Blue Dream", category: "Flower", store: "Main", onHand: 150.5, unit: "g", lastUpdated: "2024-01-15", lowStock: false, room: "Main Storage Vault" },
      { id: "2", name: "OG Kush", category: "Flower", store: "Main", onHand: 89.25, unit: "g", lastUpdated: "2024-01-15", lowStock: false, room: "Secure Vault" },
      { id: "3", name: "Gelato", category: "Flower", store: "Downtown", onHand: 45.75, unit: "g", lastUpdated: "2024-01-14", lowStock: true, room: "Premium Storage" },
      { id: "4", name: "Strawberry Gummies", category: "Edibles", store: "Main", onHand: 200, unit: "units", lastUpdated: "2024-01-15", lowStock: false, room: "Edibles Storage" },
      { id: "5", name: "Live Resin Cart", category: "Concentrates", store: "Eastside", onHand: 45, unit: "units", lastUpdated: "2024-01-14", lowStock: false, room: "Secure Vault" },
      { id: "6", name: "CBD Tincture", category: "Tinctures", store: "Main", onHand: 75, unit: "units", lastUpdated: "2024-01-13", lowStock: false, room: "Premium Storage" },
      { id: "7", name: "Pre-Roll Pack", category: "Pre-Rolls", store: "Downtown", onHand: 125, unit: "packs", lastUpdated: "2024-01-15", lowStock: false, room: "Sales Floor" },
      { id: "8", name: "Vape Cartridge", category: "Vapes", store: "Eastside", onHand: 38, unit: "units", lastUpdated: "2024-01-14", lowStock: false, room: "Premium Storage" },
      { id: "9", name: "Hash", category: "Concentrates", store: "Main", onHand: 25.5, unit: "g", lastUpdated: "2024-01-12", lowStock: true, room: "Secure Vault" },
      { id: "10", name: "CBD Topical Balm", category: "Topicals", store: "Downtown", onHand: 65, unit: "units", lastUpdated: "2024-01-14", lowStock: false, room: "Back Room" }
    ];

    // Filter by selected categories and stores
    let filteredData = inventoryData;
    if (selectedCategories && selectedCategories.length > 0) {
      filteredData = filteredData.filter(item => selectedCategories.includes(item.category));
    }
    if (selectedStores && selectedStores.length > 0) {
      filteredData = filteredData.filter(item => selectedStores.includes(item.store));
    }

    // Calculate summary statistics
    const totalItems = filteredData.length;
    const lowStockItems = filteredData.filter(item => item.lowStock).length;
    const totalValue = filteredData.reduce((sum, item) => sum + (Math.random() * 1000), 0);
    const categories = [...new Set(filteredData.map(item => item.category))];
    const stores = [...new Set(filteredData.map(item => item.store))];

    return `
      <div class="header" style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">
        <h1 style="color: #1f2937; font-size: 28px; margin: 0;">INVENTORY ON-HAND REPORT</h1>
        <p style="font-size: 16px; margin: 5px 0;"><strong>Current Stock Quantities</strong></p>
        <p style="font-size: 14px; margin: 5px 0;"><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
        ${selectedCategories && selectedCategories.length > 0 ? `<p style="font-size: 14px; margin: 5px 0;"><strong>Categories:</strong> ${selectedCategories.join(', ')}</p>` : ''}
        ${selectedStores && selectedStores.length > 0 ? `<p style="font-size: 14px; margin: 5px 0;"><strong>Stores:</strong> ${selectedStores.join(', ')}</p>` : ''}
      </div>

      <div class="summary" style="background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-left: 4px solid #1f2937;">
        <h2 style="color: #1f2937; margin-top: 0;">INVENTORY SUMMARY</h2>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
          <div>
            <p><strong>Total Items:</strong> ${totalItems}</p>
            <p><strong>Low Stock Items:</strong> ${lowStockItems}</p>
            <p><strong>Categories Included:</strong> ${categories.length}</p>
          </div>
          <div>
            <p><strong>Stores Included:</strong> ${stores.length}</p>
            <p><strong>Total Estimated Value:</strong> $${totalValue.toFixed(2)}</p>
            <p><strong>Last Updated:</strong> ${new Date().toLocaleDateString()}</p>
          </div>
        </div>
      </div>

      <div class="inventory-table" style="margin-bottom: 30px;">
        <h2 style="color: #1f2937;">ON-HAND QUANTITIES BY ITEM</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
          <thead>
            <tr style="background: #1f2937; color: white;">
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Product Name</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Category</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Store</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Room Location</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">On Hand Qty</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Unit</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Status</th>
              <th style="border: 1px solid #ccc; padding: 8px; text-align: center;">Last Updated</th>
            </tr>
          </thead>
          <tbody>
            ${filteredData.map(item => `
              <tr style="${item.lowStock ? 'background: #fef2f2;' : ''}">
                <td style="border: 1px solid #ccc; padding: 6px; font-weight: ${item.lowStock ? 'bold' : 'normal'};">${item.name}</td>
                <td style="border: 1px solid #ccc; padding: 6px;">${item.category}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${item.store}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${item.room}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: right; font-weight: bold; color: ${item.lowStock ? 'red' : 'black'};">
                  ${item.onHand}
                </td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">${item.unit}</td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center;">
                  <span style="background: ${item.lowStock ? '#fecaca' : '#dcfce7'}; color: ${item.lowStock ? '#991b1b' : '#166534'}; padding: 2px 6px; border-radius: 4px; font-size: 10px;">
                    ${item.lowStock ? 'LOW STOCK' : 'IN STOCK'}
                  </span>
                </td>
                <td style="border: 1px solid #ccc; padding: 6px; text-align: center; font-size: 10px;">${item.lastUpdated}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>

      ${categories.length > 1 ? `
        <div class="category-breakdown" style="margin-bottom: 30px;">
          <h2 style="color: #1f2937;">BREAKDOWN BY CATEGORY</h2>
          <table style="width: 60%; border-collapse: collapse; font-size: 12px;">
            <thead>
              <tr style="background: #374151; color: white;">
                <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Category</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Items Count</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Low Stock Items</th>
                <th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Stock Status</th>
              </tr>
            </thead>
            <tbody>
              ${categories.map(category => {
                const categoryItems = filteredData.filter(item => item.category === category);
                const categoryLowStock = categoryItems.filter(item => item.lowStock).length;
                const stockPercentage = ((categoryItems.length - categoryLowStock) / categoryItems.length * 100).toFixed(0);
                return `
                  <tr>
                    <td style="border: 1px solid #ccc; padding: 6px; font-weight: bold;">${category}</td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${categoryItems.length}</td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: right; color: ${categoryLowStock > 0 ? 'red' : 'green'};">${categoryLowStock}</td>
                    <td style="border: 1px solid #ccc; padding: 6px; text-align: right;">${stockPercentage}% OK</td>
                  </tr>
                `;
              }).join('')}
            </tbody>
          </table>
        </div>
      ` : ''}

      <div class="footer" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666;">
        <p><strong>Report Generated By:</strong> Cannabis POS System</p>
        <p><strong>Next Inventory Audit:</strong> ${new Date(Date.now() + 7*24*60*60*1000).toLocaleDateString()}</p>
        <p><strong>Inventory Manager:</strong> ________________________</p>
        <p><strong>Manager Approval:</strong> ________________________</p>
      </div>
    `;
  };

  const generateReportContent = (report: Report) => {
    const metrics = report.metrics.map(metricId => {
      const metric = availableMetrics[report.type]?.find(m => m.id === metricId);
      return metric ? metric.label : metricId;
    });

    // Generate ASPD-specific content with detailed breakdown
    if (report.name.toLowerCase().includes('aspd') || report.name.toLowerCase().includes('alcohol and substance')) {
      return generateASPDReportContent(report, metrics);
    }

    // Generate Penny Sale report
    if (report.type === 'pennysale' || report.name.toLowerCase().includes('penny sale')) {
      return generatePennySaleReport(report, '2024-01-01', '2024-01-31');
    }

    // Generate Compliance Report with Metrc vs Inventory comparison
    if (report.type === 'compliance') {
      return generateComplianceReport(report, metrics);
    }

    // Generate Payroll Report
    if (report.type === 'payroll') {
      return generatePayrollReport(report);
    }

    // Generate Employee Performance Report
    if (report.type === 'employee') {
      return generateEmployeePerformanceReport(report);
    }

    // Generate Daily Sales Report
    if (report.type === 'sales') {
      return generateDailySalesReport(report);
    }

    // Generate Inventory Report with on-hand quantities
    if (report.type === 'inventory') {
      return generateInventoryReport(report, report.filters?.categories, report.filters?.stores);
    }

    return `
      <div class="header">
        <h1>${report.name}</h1>
        <p><strong>Type:</strong> ${report.type.charAt(0).toUpperCase() + report.type.slice(1)}</p>
        <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
        <p><strong>Schedule:</strong> ${report.schedule}</p>
      </div>
      <div class="content">
        <h2>Report Metrics</h2>
        ${metrics.map(metric => `<div class="metric">${metric}</div>`).join('')}

        <h2>Sample Data</h2>
        <table>
          <tr>
            <th>Metric</th>
            <th>Value</th>
            <th>Period</th>
          </tr>
          ${metrics.map(metric => `
            <tr>
              <td>${metric}</td>
              <td>$${(Math.random() * 10000).toFixed(2)}</td>
              <td>Current Period</td>
            </tr>
          `).join('')}
        </table>
      </div>
    `;
  };

  const generateReportData = (report: Report) => {
    const metrics = report.metrics.map(metricId => {
      const metric = availableMetrics[report.type]?.find(m => m.id === metricId);
      return {
        metric: metric ? metric.label : metricId,
        value: (Math.random() * 10000).toFixed(2),
        period: 'Current Period'
      };
    });
    return metrics;
  };

  const convertToCSV = (data: any[]) => {
    if (data.length === 0) return '';
    const headers = Object.keys(data[0]);
    const csvHeaders = headers.join(',');
    const csvRows = data.map(row =>
      headers.map(header => `"${row[header]}"`).join(',')
    );
    return [csvHeaders, ...csvRows].join('\n');
  };

  const runReport = (reportId: string) => {
    setReports(prev => prev.map(report => 
      report.id === reportId 
        ? { ...report, lastRun: new Date().toISOString().slice(0, 16).replace('T', ' ') }
        : report
    ));
  };

  const printASPDChart = (startDate: string, endDate: string) => {
    const chartData = generateASPDChartData(startDate, endDate);
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>ASPD Chart - Printable</title>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .chart-container { width: 800px; height: 600px; margin: 20px auto; }
              .header { text-align: center; margin-bottom: 20px; }
              @media print {
                .no-print { display: none; }
                .chart-container { page-break-inside: avoid; }
              }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>ASPD Chart Report</h1>
              <p>Period: ${startDate} to ${endDate}</p>
              <p>Generated: ${new Date().toLocaleString()}</p>
            </div>
            <div class="no-print">
              <button onclick="window.print()">Print This Chart</button>
            </div>
            <div class="chart-container">
              <canvas id="aspdChart"></canvas>
            </div>
            <script>
              const ctx = document.getElementById('aspdChart').getContext('2d');
              const chartData = ${JSON.stringify(chartData)};
              new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    title: {
                      display: true,
                      text: 'ASPD Data Trends Over Time'
                    },
                    legend: {
                      position: 'top'
                    }
                  },
                  scales: {
                    y: {
                      beginAtZero: true,
                      title: {
                        display: true,
                        text: 'Sales ($)'
                      }
                    },
                    x: {
                      title: {
                        display: true,
                        text: 'Date'
                      }
                    }
                  }
                }
              });

              // Auto-print after chart loads
              setTimeout(() => {
                window.print();
              }, 1000);
            </script>
          </body>
        </html>
      `);
      printWindow.document.close();
    }
  };

  const createReport = () => {
    const newReport: Report = {
      id: Date.now().toString(),
      name: reportName,
      type: selectedType as Report['type'],
      schedule: schedule as Report['schedule'],
      lastRun: "Never",
      metrics: selectedMetrics,
      filters: {
        ...(selectedType === 'inventory' && selectedCategories.length > 0 && { categories: selectedCategories }),
        ...(selectedType === 'inventory' && selectedStores.length > 0 && { stores: selectedStores })
      },
      format: format as Report['format'],
      recipients: recipients.split(',').map(email => email.trim()).filter(Boolean)
    };
    
    setReports(prev => [...prev, newReport]);
    setShowCreateDialog(false);
    
    // Reset form
    setReportName("");
    setSelectedType("");
    setSelectedMetrics([]);
    setSchedule("manual");
    setFormat("pdf");
    setRecipients("");
    setSelectedCategories([]);
    setSelectedStores([]);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Custom Reports</h1>
            <p className="text-sm opacity-80">Create and manage business reports</p>
          </div>
          <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
            <DialogTrigger asChild>
              <Button className="header-button-visible">
                <Plus className="w-4 h-4 mr-2" />
                Create Report
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle>Create Custom Report</DialogTitle>
              </DialogHeader>
              <div className="space-y-6">
                <div>
                  <Label htmlFor="report-name">Report Name</Label>
                  <Input
                    id="report-name"
                    value={reportName}
                    onChange={(e) => setReportName(e.target.value)}
                    placeholder="Enter report name"
                  />
                </div>

                <div>
                  <Label>Report Type</Label>
                  <div className="grid grid-cols-2 gap-3 mt-2">
                    {reportTypes.map(type => {
                      const Icon = type.icon;
                      return (
                        <div
                          key={type.value}
                          className={`p-3 border rounded-lg cursor-pointer transition-colors ${
                            selectedType === type.value ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'
                          }`}
                          onClick={() => {
                            setSelectedType(type.value);
                            setSelectedMetrics([]);
                            setSelectedCategories([]);
                            setSelectedStores([]);
                          }}
                        >
                          <div className="flex items-center gap-2">
                            <Icon className="w-4 h-4" />
                            <span className="font-medium">{type.label}</span>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>

                {selectedType && (
                  <div>
                    <Label>Metrics to Include</Label>
                    <div className="space-y-2 mt-2 max-h-40 overflow-y-auto">
                      {availableMetrics[selectedType as keyof typeof availableMetrics]?.map(metric => (
                        <div key={metric.id} className="flex items-center space-x-2">
                          <Checkbox
                            id={metric.id}
                            checked={selectedMetrics.includes(metric.id)}
                            onCheckedChange={(checked) => {
                              if (checked) {
                                setSelectedMetrics(prev => [...prev, metric.id]);
                              } else {
                                setSelectedMetrics(prev => prev.filter(id => id !== metric.id));
                              }
                            }}
                          />
                          <Label htmlFor={metric.id} className="text-sm">{metric.label}</Label>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {selectedType === 'inventory' && (
                  <div className="space-y-4">
                    <div>
                      <Label>Filter by Categories (optional)</Label>
                      <div className="grid grid-cols-3 gap-2 mt-2 max-h-32 overflow-y-auto">
                        {availableCategories.map(category => (
                          <div key={category} className="flex items-center space-x-2">
                            <Checkbox
                              id={`category-${category}`}
                              checked={selectedCategories.includes(category)}
                              onCheckedChange={(checked) => {
                                if (checked) {
                                  setSelectedCategories(prev => [...prev, category]);
                                } else {
                                  setSelectedCategories(prev => prev.filter(c => c !== category));
                                }
                              }}
                            />
                            <Label htmlFor={`category-${category}`} className="text-sm">{category}</Label>
                          </div>
                        ))}
                      </div>
                      {selectedCategories.length > 0 && (
                        <p className="text-xs text-blue-600 mt-1">
                          Selected: {selectedCategories.join(', ')}
                        </p>
                      )}
                    </div>
                    <div>
                      <Label>Filter by Stores (optional)</Label>
                      <div className="grid grid-cols-3 gap-2 mt-2">
                        {availableStores.map(store => (
                          <div key={store} className="flex items-center space-x-2">
                            <Checkbox
                              id={`store-${store}`}
                              checked={selectedStores.includes(store)}
                              onCheckedChange={(checked) => {
                                if (checked) {
                                  setSelectedStores(prev => [...prev, store]);
                                } else {
                                  setSelectedStores(prev => prev.filter(s => s !== store));
                                }
                              }}
                            />
                            <Label htmlFor={`store-${store}`} className="text-sm">{store}</Label>
                          </div>
                        ))}
                      </div>
                      {selectedStores.length > 0 && (
                        <p className="text-xs text-blue-600 mt-1">
                          Selected: {selectedStores.join(', ')}
                        </p>
                      )}
                    </div>
                  </div>
                )}

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="schedule">Schedule</Label>
                    <Select value={schedule} onValueChange={setSchedule}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="manual">Manual</SelectItem>
                        <SelectItem value="daily">Daily</SelectItem>
                        <SelectItem value="weekly">Weekly</SelectItem>
                        <SelectItem value="monthly">Monthly</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div>
                    <Label htmlFor="format">Format</Label>
                    <Select value={format} onValueChange={setFormat}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="pdf">PDF</SelectItem>
                        <SelectItem value="excel">Excel</SelectItem>
                        <SelectItem value="csv">CSV</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div>
                  <Label htmlFor="recipients">Email Recipients (comma separated)</Label>
                  <Input
                    id="recipients"
                    value={recipients}
                    onChange={(e) => setRecipients(e.target.value)}
                    placeholder="email1@example.com, email2@example.com"
                  />
                </div>

                <Button 
                  onClick={createReport} 
                  className="w-full"
                  disabled={!reportName || !selectedType || selectedMetrics.length === 0}
                >
                  Create Report
                </Button>
              </div>
            </DialogContent>
          </Dialog>
        </div>
      </header>

      <div className="container mx-auto p-6">
        {/* Quick Reports */}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Quick Reports</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
              <div className="space-y-2">
                <Button
                  variant="outline"
                  className="h-16 w-full flex-col"
                  onClick={() => setShowDailySalesDialog(true)}
                >
                  <DollarSign className="w-6 h-6 mb-1" />
                  <span className="text-sm">Daily Sales</span>
                </Button>
                <div className="text-xs text-gray-500 text-center">
                  Select date range & format
                </div>
              </div>
              <div className="space-y-2">
                <Button
                  variant="outline"
                  className="h-16 w-full flex-col"
                  onClick={() => setShowQuickInventoryDialog(true)}
                >
                  <Package className="w-6 h-6 mb-1" />
                  <span className="text-sm">Inventory</span>
                </Button>
                <div className="text-xs text-gray-500 text-center">
                  Select category & format
                </div>
              </div>
              <div className="space-y-2">
                <Button variant="outline" className="h-16 w-full flex-col">
                  <Users className="w-6 h-6 mb-1" />
                  <span className="text-sm">Customers</span>
                </Button>
                <div className="flex gap-1">
                  <Button size="sm" variant="outline" className="flex-1" onClick={() => downloadReportAsPDF({id: 'quick-customers', name: 'Customer Report', type: 'customer', schedule: 'manual', lastRun: '', metrics: ['newCustomers', 'returningCustomers'], filters: {}, format: 'pdf', recipients: []})}>
                    PDF
                  </Button>
                  <Button size="sm" variant="outline" className="flex-1" onClick={() => downloadReportAsExcel({id: 'quick-customers', name: 'Customer Report', type: 'customer', schedule: 'manual', lastRun: '', metrics: ['newCustomers', 'returningCustomers'], filters: {}, format: 'excel', recipients: []})}>
                    Excel
                  </Button>
                </div>
              </div>
              <div className="space-y-2">
                <Button
                  variant="outline"
                  className="h-16 w-full flex-col"
                  onClick={() => setShowPerformanceDialog(true)}
                >
                  <BarChart3 className="w-6 h-6 mb-1" />
                  <span className="text-sm">Performance</span>
                </Button>
                <div className="text-xs text-gray-500 text-center">
                  Select date range & format
                </div>
              </div>
              <div className="space-y-2">
                <Button variant="outline" className="h-16 w-full flex-col">
                  <FileText className="w-6 h-6 mb-1" />
                  <span className="text-sm">Compliance</span>
                </Button>
                <div className="flex gap-1">
                  <Button size="sm" variant="outline" className="flex-1" onClick={() => downloadReportAsPDF({id: 'quick-compliance', name: 'Compliance Report', type: 'compliance', schedule: 'manual', lastRun: '', metrics: ['sales', 'inventory', 'customers'], filters: {}, format: 'pdf', recipients: []})}>
                    PDF
                  </Button>
                  <Button size="sm" variant="outline" className="flex-1" onClick={() => downloadReportAsExcel({id: 'quick-compliance', name: 'Compliance Report', type: 'compliance', schedule: 'manual', lastRun: '', metrics: ['sales', 'inventory', 'customers'], filters: {}, format: 'excel', recipients: []})}>
                    Excel
                  </Button>
                </div>
              </div>
              <div className="space-y-2">
                <Button
                  variant="outline"
                  className="h-16 w-full flex-col"
                  onClick={() => setShowPennySaleDialog(true)}
                >
                  <DollarSign className="w-6 h-6 mb-1" />
                  <span className="text-sm">Penny Sale</span>
                </Button>
                <div className="text-xs text-gray-500 text-center">
                  Select date range & format
                </div>
              </div>
              <div className="space-y-2">
                <Button
                  variant="outline"
                  className="h-16 w-full flex-col"
                  onClick={() => setShowPayrollDialog(true)}
                >
                  <Clock className="w-6 h-6 mb-1" />
                  <span className="text-sm">Payroll</span>
                </Button>
                <div className="text-xs text-gray-500 text-center">
                  Select date range & format
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Saved Reports */}
        <Card>
          <CardHeader>
            <CardTitle>Saved Reports</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {reports.map(report => (
                <div key={report.id} className="flex items-center justify-between p-4 border rounded-lg">
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-2">
                      <h3 className="font-semibold">{report.name}</h3>
                      <Badge variant="outline">{report.type}</Badge>
                      <Badge variant="secondary">{report.schedule}</Badge>
                      <Badge variant="outline">{report.format.toUpperCase()}</Badge>
                    </div>
                    <div className="text-sm text-muted-foreground">
                      <span>Last run: {report.lastRun}</span>
                      {report.recipients.length > 0 && (
                        <span className="ml-4">Recipients: {report.recipients.join(', ')}</span>
                      )}
                    </div>
                    <div className="flex flex-wrap gap-1 mt-2">
                      {report.metrics.slice(0, 3).map(metric => (
                        <Badge key={metric} variant="outline" className="text-xs">
                          {availableMetrics[report.type]?.find(m => m.id === metric)?.label || metric}
                        </Badge>
                      ))}
                      {report.metrics.length > 3 && (
                        <Badge variant="outline" className="text-xs">
                          +{report.metrics.length - 3} more
                        </Badge>
                      )}
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Button
                      size="sm"
                      onClick={() => runReport(report.id)}
                    >
                      <Play className="w-3 h-3 mr-1" />
                      Run
                    </Button>
                    <div className="flex gap-1">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => downloadReportAsPDF(report)}
                      >
                        <Download className="w-3 h-3 mr-1" />
                        PDF
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => downloadReportAsExcel(report)}
                      >
                        <Download className="w-3 h-3 mr-1" />
                        Excel
                      </Button>
                    </div>
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => shareReport(report)}
                    >
                      <Share className="w-3 h-3 mr-1" />
                      Share
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => editReport(report)}
                    >
                      <Edit className="w-3 h-3 mr-1" />
                      Edit
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => deleteReport(report.id)}
                    >
                      <Trash2 className="w-3 h-3" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Share Report Dialog */}
      <Dialog open={showShareDialog} onOpenChange={setShowShareDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Share Report</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            {reportToShare && (
              <div className="p-3 bg-gray-50 rounded-lg">
                <h3 className="font-semibold">{reportToShare.name}</h3>
                <p className="text-sm text-gray-600">Report Type: {reportToShare.type}</p>
              </div>
            )}

            <div>
              <Label>Select Recipients</Label>
              <div className="mt-2">
                <Input
                  placeholder="Search users..."
                  value={userSearchQuery}
                  onChange={(e) => setUserSearchQuery(e.target.value)}
                  className="mb-3"
                />
                <div className="max-h-60 overflow-y-auto border rounded-lg">
                  {companyUsers
                    .filter(user =>
                      user.name.toLowerCase().includes(userSearchQuery.toLowerCase()) ||
                      user.email.toLowerCase().includes(userSearchQuery.toLowerCase()) ||
                      user.role.toLowerCase().includes(userSearchQuery.toLowerCase())
                    )
                    .map(user => (
                      <div key={user.id} className="flex items-center p-3 hover:bg-gray-50 border-b last:border-b-0">
                        <Checkbox
                          id={`user-${user.id}`}
                          checked={selectedUsers.includes(user.id)}
                          onCheckedChange={(checked) => {
                            if (checked) {
                              setSelectedUsers(prev => [...prev, user.id]);
                            } else {
                              setSelectedUsers(prev => prev.filter(id => id !== user.id));
                            }
                          }}
                        />
                        <div className="ml-3 flex-1">
                          <div className="flex items-center justify-between">
                            <div>
                              <p className="font-medium">{user.name}</p>
                              <p className="text-sm text-gray-600">{user.email}</p>
                            </div>
                            <div className="text-right">
                              <Badge variant="outline" className="text-xs">{user.role}</Badge>
                              <p className="text-xs text-gray-500 mt-1">{user.store}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                </div>
                {selectedUsers.length > 0 && (
                  <div className="mt-3 p-2 bg-blue-50 rounded">
                    <p className="text-sm text-blue-800">
                      Selected {selectedUsers.length} recipient{selectedUsers.length > 1 ? 's' : ''}
                    </p>
                  </div>
                )}
              </div>
            </div>

            <div className="flex gap-2">
              <Button
                onClick={() => {
                  const selectedUserEmails = selectedUsers.map(id =>
                    companyUsers.find(user => user.id === id)?.email
                  ).filter(Boolean);

                  if (reportToShare && selectedUserEmails.length > 0) {
                    console.log(`Sharing report "${reportToShare.name}" with:`, selectedUserEmails);
                    alert(`Report "${reportToShare.name}" shared with ${selectedUserEmails.length} recipient(s)`);
                    setShowShareDialog(false);
                    setReportToShare(null);
                    setSelectedUsers([]);
                    setUserSearchQuery("");
                  }
                }}
                className="flex-1"
                disabled={selectedUsers.length === 0}
              >
                <Mail className="w-4 h-4 mr-2" />
                Share Report ({selectedUsers.length})
              </Button>
              <Button variant="outline" onClick={() => {
                setShowShareDialog(false);
                setSelectedUsers([]);
                setUserSearchQuery("");
              }} className="flex-1">
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Edit Report Dialog */}
      <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Edit Report - {reportToEdit?.name}</DialogTitle>
          </DialogHeader>
          <div className="space-y-6">
            <div>
              <Label htmlFor="edit-report-name">Report Name</Label>
              <Input
                id="edit-report-name"
                value={reportName}
                onChange={(e) => setReportName(e.target.value)}
                placeholder="Enter report name"
              />
            </div>

            <div>
              <Label>Report Type</Label>
              <Select value={selectedType} onValueChange={setSelectedType}>
                <SelectTrigger>
                  <SelectValue placeholder="Select report type" />
                </SelectTrigger>
                <SelectContent>
                  {reportTypes.map(type => (
                    <SelectItem key={type.value} value={type.value}>
                      <div className="flex items-center gap-2">
                        <type.icon className="w-4 h-4" />
                        {type.label}
                      </div>
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {selectedType && (
              <div>
                <Label>Metrics to Include</Label>
                <div className="grid grid-cols-2 gap-2 mt-2 max-h-40 overflow-y-auto">
                  {availableMetrics[selectedType]?.map(metric => (
                    <div key={metric.id} className="flex items-center space-x-2">
                      <Checkbox
                        id={`edit-metric-${metric.id}`}
                        checked={selectedMetrics.includes(metric.id)}
                        onCheckedChange={(checked) => {
                          if (checked) {
                            setSelectedMetrics(prev => [...prev, metric.id]);
                          } else {
                            setSelectedMetrics(prev => prev.filter(m => m !== metric.id));
                          }
                        }}
                      />
                      <Label htmlFor={`edit-metric-${metric.id}`} className="text-sm">
                        {metric.label}
                      </Label>
                    </div>
                  ))}
                </div>
              </div>
            )}

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label>Schedule</Label>
                <Select value={schedule} onValueChange={setSchedule}>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="manual">Manual</SelectItem>
                    <SelectItem value="daily">Daily</SelectItem>
                    <SelectItem value="weekly">Weekly</SelectItem>
                    <SelectItem value="monthly">Monthly</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label>Format</Label>
                <Select value={format} onValueChange={setFormat}>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="pdf">PDF</SelectItem>
                    <SelectItem value="excel">Excel</SelectItem>
                    <SelectItem value="csv">CSV</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div>
              <Label htmlFor="edit-recipients">Email Recipients</Label>
              <Input
                id="edit-recipients"
                value={recipients}
                onChange={(e) => setRecipients(e.target.value)}
                placeholder="email1@example.com, email2@example.com"
              />
            </div>

            <div className="flex gap-2">
              <Button onClick={updateReport} className="flex-1">
                Update Report
              </Button>
              <Button
                variant="outline"
                onClick={() => {
                  setShowEditDialog(false);
                  setReportToEdit(null);
                  resetForm();
                }}
                className="flex-1"
              >
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* ASPD Chart Download Dialog */}
      <Dialog open={showASPDChartDialog} onOpenChange={setShowASPDChartDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>ASPD Chart Options</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label htmlFor="aspd-start-date">Start Date</Label>
              <Input
                id="aspd-start-date"
                type="date"
                value={aspdStartDate}
                onChange={(e) => setAspdStartDate(e.target.value)}
              />
            </div>
            <div>
              <Label htmlFor="aspd-end-date">End Date</Label>
              <Input
                id="aspd-end-date"
                type="date"
                value={aspdEndDate}
                onChange={(e) => setAspdEndDate(e.target.value)}
              />
            </div>
            <div className="flex gap-2">
              <Button
                onClick={() => {
                  downloadASPDChart(
                    {id: 'aspd', name: 'ASPD Report', type: 'sales', schedule: 'manual', lastRun: '', metrics: ['revenue', 'categories'], filters: {}, format: 'pdf', recipients: []},
                    aspdStartDate,
                    aspdEndDate
                  );
                  setShowASPDChartDialog(false);
                }}
                className="flex-1"
              >
                <BarChart3 className="w-4 h-4 mr-2" />
                View Chart
              </Button>
              <Button
                variant="outline"
                onClick={() => {
                  printASPDChart(aspdStartDate, aspdEndDate);
                  setShowASPDChartDialog(false);
                }}
                className="flex-1"
              >
                <Printer className="w-4 h-4 mr-2" />
                Print Chart
              </Button>
              <Button variant="outline" onClick={() => setShowASPDChartDialog(false)} className="flex-1">
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Quick Inventory Report Dialog */}
      <Dialog open={showQuickInventoryDialog} onOpenChange={setShowQuickInventoryDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Quick Inventory Report</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label htmlFor="quick-inventory-category">Category Filter</Label>
              <Select value={quickInventoryCategory} onValueChange={setQuickInventoryCategory}>
                <SelectTrigger>
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Categories</SelectItem>
                  {availableCategories.map(category => (
                    <SelectItem key={category} value={category}>{category}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <p className="text-xs text-gray-600 mt-1">
                Select a specific category or "All Categories" for complete inventory
              </p>
            </div>

            <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <div className="text-sm font-medium text-blue-800 mb-1">Report Preview</div>
              <div className="text-sm text-blue-700">
                {quickInventoryCategory === "all"
                  ? "Complete inventory report for all product categories"
                  : `Inventory report filtered for ${quickInventoryCategory} category only`
                }
              </div>
            </div>

            <div className="flex gap-2">
              <Button
                className="flex-1"
                onClick={() => generateQuickInventoryReport('pdf')}
              >
                <FileText className="w-4 h-4 mr-2" />
                Download PDF
              </Button>
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => generateQuickInventoryReport('excel')}
              >
                <Download className="w-4 h-4 mr-2" />
                Download Excel
              </Button>
            </div>

            <div className="flex gap-2">
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => setShowQuickInventoryDialog(false)}
              >
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Penny Sale Report Dialog */}
      <Dialog open={showPennySaleDialog} onOpenChange={setShowPennySaleDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Penny Sale Report</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="penny-start-date">Start Date</Label>
                <Input
                  id="penny-start-date"
                  type="date"
                  value={pennySaleStartDate}
                  onChange={(e) => setPennySaleStartDate(e.target.value)}
                />
              </div>
              <div>
                <Label htmlFor="penny-end-date">End Date</Label>
                <Input
                  id="penny-end-date"
                  type="date"
                  value={pennySaleEndDate}
                  onChange={(e) => setPennySaleEndDate(e.target.value)}
                />
              </div>
            </div>

            <div className="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
              <div className="text-sm font-medium text-yellow-800 mb-1">Report Preview</div>
              <div className="text-sm text-yellow-700">
                Penny sale transactions from {new Date(pennySaleStartDate).toLocaleDateString()} to {new Date(pennySaleEndDate).toLocaleDateString()}
              </div>
              <div className="text-xs text-yellow-600 mt-1">
                Includes items sold for $0.01, transaction details, discount reason codes, and revenue impact analysis
              </div>
            </div>

            <div className="flex gap-2">
              <Button
                className="flex-1"
                onClick={() => generatePennySaleWithDateRange('pdf')}
              >
                <FileText className="w-4 h-4 mr-2" />
                Download PDF
              </Button>
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => generatePennySaleWithDateRange('excel')}
              >
                <Download className="w-4 h-4 mr-2" />
                Download Excel
              </Button>
            </div>

            <div className="flex gap-2">
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => setShowPennySaleDialog(false)}
              >
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Daily Sales Report Dialog */}
      <Dialog open={showDailySalesDialog} onOpenChange={setShowDailySalesDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Daily Sales Report</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="daily-sales-start-date">Start Date</Label>
                <Input
                  id="daily-sales-start-date"
                  type="date"
                  value={dailySalesStartDate}
                  onChange={(e) => setDailySalesStartDate(e.target.value)}
                />
              </div>
              <div>
                <Label htmlFor="daily-sales-end-date">End Date</Label>
                <Input
                  id="daily-sales-end-date"
                  type="date"
                  value={dailySalesEndDate}
                  onChange={(e) => setDailySalesEndDate(e.target.value)}
                />
              </div>
            </div>

            <div className="p-3 bg-green-50 border border-green-200 rounded-lg">
              <div className="text-sm font-medium text-green-800 mb-1">Report Preview</div>
              <div className="text-sm text-green-700">
                Daily sales data from {new Date(dailySalesStartDate).toLocaleDateString()} to {new Date(dailySalesEndDate).toLocaleDateString()}
              </div>
              <div className="text-xs text-green-600 mt-1">
                Includes revenue, transactions, average order value, payment methods, and hourly breakdowns
              </div>
            </div>

            <div className="flex gap-2">
              <Button
                className="flex-1"
                onClick={() => generateDailySalesWithDateRange('pdf')}
              >
                <FileText className="w-4 h-4 mr-2" />
                Download PDF
              </Button>
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => generateDailySalesWithDateRange('excel')}
              >
                <Download className="w-4 h-4 mr-2" />
                Download Excel
              </Button>
            </div>

            <div className="flex gap-2">
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => setShowDailySalesDialog(false)}
              >
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Performance Report Dialog */}
      <Dialog open={showPerformanceDialog} onOpenChange={setShowPerformanceDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Employee Performance Report</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="performance-start-date">Start Date</Label>
                <Input
                  id="performance-start-date"
                  type="date"
                  value={performanceStartDate}
                  onChange={(e) => setPerformanceStartDate(e.target.value)}
                />
              </div>
              <div>
                <Label htmlFor="performance-end-date">End Date</Label>
                <Input
                  id="performance-end-date"
                  type="date"
                  value={performanceEndDate}
                  onChange={(e) => setPerformanceEndDate(e.target.value)}
                />
              </div>
            </div>

            <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <div className="text-sm font-medium text-blue-800 mb-1">Report Preview</div>
              <div className="text-sm text-blue-700">
                Employee performance data from {new Date(performanceStartDate).toLocaleDateString()} to {new Date(performanceEndDate).toLocaleDateString()}
              </div>
              <div className="text-xs text-blue-600 mt-1">
                Includes sales performance, productivity metrics, transaction counts, and individual employee breakdowns
              </div>
            </div>

            <div className="flex gap-2">
              <Button
                className="flex-1"
                onClick={() => generatePerformanceWithDateRange('pdf')}
              >
                <FileText className="w-4 h-4 mr-2" />
                Download PDF
              </Button>
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => generatePerformanceWithDateRange('excel')}
              >
                <Download className="w-4 h-4 mr-2" />
                Download Excel
              </Button>
            </div>

            <div className="flex gap-2">
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => setShowPerformanceDialog(false)}
              >
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Payroll Report Dialog */}
      <Dialog open={showPayrollDialog} onOpenChange={setShowPayrollDialog}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Payroll Report</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="payroll-start-date">Start Date</Label>
                <Input
                  id="payroll-start-date"
                  type="date"
                  value={payrollStartDate}
                  onChange={(e) => setPayrollStartDate(e.target.value)}
                />
              </div>
              <div>
                <Label htmlFor="payroll-end-date">End Date</Label>
                <Input
                  id="payroll-end-date"
                  type="date"
                  value={payrollEndDate}
                  onChange={(e) => setPayrollEndDate(e.target.value)}
                />
              </div>
            </div>

            <div className="p-3 bg-green-50 border border-green-200 rounded-lg">
              <div className="text-sm font-medium text-green-800 mb-1">Report Preview</div>
              <div className="text-sm text-green-700">
                Employee payroll data from {new Date(payrollStartDate).toLocaleDateString()} to {new Date(payrollEndDate).toLocaleDateString()}
              </div>
              <div className="text-xs text-green-600 mt-1">
                Includes hours worked, overtime calculations, hourly rates, total compensation, and detailed time tracking
              </div>
            </div>

            <div className="flex gap-2">
              <Button
                className="flex-1"
                onClick={() => generatePayrollWithDateRange('pdf')}
              >
                <FileText className="w-4 h-4 mr-2" />
                Download PDF
              </Button>
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => generatePayrollWithDateRange('excel')}
              >
                <Download className="w-4 h-4 mr-2" />
                Download Excel
              </Button>
            </div>

            <div className="flex gap-2">
              <Button
                variant="outline"
                className="flex-1"
                onClick={() => setShowPayrollDialog(false)}
              >
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
