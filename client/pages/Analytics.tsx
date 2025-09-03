import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Label } from "@/components/ui/label";
import { Progress } from "@/components/ui/progress";
import { DatePickerWithRange } from "@/components/ui/date-range-picker";
import {
  BarChart3,
  TrendingUp,
  TrendingDown,
  DollarSign,
  ShoppingCart,
  Users,
  Package,
  Calendar,
  Download,
  Eye,
  Leaf,
  Clock,
  Printer,
  Store,
  MapPin,
  Building2,
  X
} from "lucide-react";

const salesData = {
  today: {
    revenue: 3250.75,
    transactions: 48,
    customers: 42,
    avgOrderValue: 67.72,
    change: {
      revenue: 12.5,
      transactions: -3.2,
      customers: 8.1,
      avgOrderValue: 15.8
    }
  },
  week: {
    revenue: 18750.25,
    transactions: 285,
    customers: 198,
    avgOrderValue: 65.79
  },
  month: {
    revenue: 87425.50,
    transactions: 1350,
    customers: 892,
    avgOrderValue: 64.76
  }
};

const topProducts = [
  { name: "Blue Dream", sales: 45, revenue: 2025.00, category: "Flower" },
  { name: "Gummy Bears", sales: 38, revenue: 950.00, category: "Edibles" },
  { name: "OG Kush", sales: 35, revenue: 1750.00, category: "Flower" },
  { name: "Vape Cartridge", sales: 28, revenue: 1540.00, category: "Vapes" },
  { name: "CBD Tincture", sales: 22, revenue: 1430.00, category: "Tinctures" }
];

const categoryData = [
  { name: "Flower", sales: 125, revenue: 5875.00, percentage: 35.2 },
  { name: "Edibles", sales: 89, revenue: 2670.00, percentage: 15.9 },
  { name: "Vapes", sales: 67, revenue: 3685.00, percentage: 21.9 },
  { name: "Concentrates", sales: 34, revenue: 3230.00, percentage: 19.2 },
  { name: "Pre-Rolls", sales: 45, revenue: 1575.00, percentage: 9.4 }
];

const customerInsights = {
  newCustomers: 28,
  returningCustomers: 164,
  loyaltyMembers: 89,
  medicalPatients: 156,
  caregivers: 23
};

const inventoryAlerts = [
  { product: "Sour Diesel", stock: 3, reorderPoint: 10, status: "critical" },
  { product: "Hash", stock: 8, reorderPoint: 15, status: "low" },
  { product: "Chocolate Bar", stock: 12, reorderPoint: 20, status: "low" },
  { product: "Rosin", stock: 2, reorderPoint: 8, status: "critical" }
];

const employeeMetrics = [
  { name: "Sarah Johnson", sales: 12450.25, transactions: 89, avgOrder: 139.89 },
  { name: "Mike Chen", sales: 10875.50, transactions: 76, avgOrder: 143.10 },
  { name: "Emma Rodriguez", sales: 9320.75, transactions: 68, avgOrder: 137.07 },
  { name: "David Kim", sales: 8150.00, transactions: 62, avgOrder: 131.45 }
];

// ASPD (Average Sold Per Day) data for current month
const aspdData = [
  { id: "1", name: "Shake Special", category: "Flower", totalSold: 450, unitsSold: 450, totalRevenue: 481.50, daysInRange: 15, aspd: 30.0 },
  { id: "2", name: "Outdoor Special", category: "Flower", totalSold: 320, unitsSold: 320, totalRevenue: 572.80, daysInRange: 15, aspd: 21.33 },
  { id: "3", name: "House Blend", category: "Flower", totalSold: 180, unitsSold: 180, totalRevenue: 720.00, daysInRange: 15, aspd: 12.0 },
  { id: "4", name: "Blue Dream", category: "Flower", totalSold: 125, unitsSold: 125, totalRevenue: 875.00, daysInRange: 15, aspd: 8.33 },
  { id: "5", name: "OG Kush", category: "Flower", totalSold: 95, unitsSold: 95, totalRevenue: 1140.00, daysInRange: 15, aspd: 6.33 },
  { id: "6", name: "Gelato", category: "Flower", totalSold: 68, unitsSold: 68, totalRevenue: 952.00, daysInRange: 15, aspd: 4.53 },
  { id: "7", name: "Rainbow Belts", category: "Flower", totalSold: 45, unitsSold: 45, totalRevenue: 720.00, daysInRange: 15, aspd: 3.0 },
  { id: "8", name: "Sour Diesel Pre-Roll", category: "Pre-Rolls", totalSold: 240, unitsSold: 240, totalRevenue: 1200.00, daysInRange: 15, aspd: 16.0 },
  { id: "9", name: "Strawberry Gummies", category: "Edibles", totalSold: 85, unitsSold: 85, totalRevenue: 850.00, daysInRange: 15, aspd: 5.67 },
  { id: "10", name: "Live Resin Cart", category: "Concentrates", totalSold: 42, unitsSold: 42, totalRevenue: 1260.00, daysInRange: 15, aspd: 2.8 }
];

// Sales trend data for last 30 days
const salesTrendData = [
  { date: "2024-01-01", sales: 2150.50, transactions: 35, customers: 28 },
  { date: "2024-01-02", sales: 2850.75, transactions: 42, customers: 38 },
  { date: "2024-01-03", sales: 3200.25, transactions: 48, customers: 45 },
  { date: "2024-01-04", sales: 2950.00, transactions: 44, customers: 41 },
  { date: "2024-01-05", sales: 3850.25, transactions: 58, customers: 52 },
  { date: "2024-01-06", sales: 4200.75, transactions: 62, customers: 56 },
  { date: "2024-01-07", sales: 3650.50, transactions: 54, customers: 48 },
  { date: "2024-01-08", sales: 2750.25, transactions: 41, customers: 35 },
  { date: "2024-01-09", sales: 3150.00, transactions: 47, customers: 42 },
  { date: "2024-01-10", sales: 3950.75, transactions: 59, customers: 53 },
  { date: "2024-01-11", sales: 3450.25, transactions: 51, customers: 46 },
  { date: "2024-01-12", sales: 4100.50, transactions: 61, customers: 55 },
  { date: "2024-01-13", sales: 4500.75, transactions: 67, customers: 60 },
  { date: "2024-01-14", sales: 3850.25, transactions: 57, customers: 51 },
  { date: "2024-01-15", sales: 3250.75, transactions: 48, customers: 42 }, // today
  { date: "2024-01-16", sales: 3650.50, transactions: 54, customers: 49 },
  { date: "2024-01-17", sales: 3950.25, transactions: 58, customers: 52 },
  { date: "2024-01-18", sales: 4250.75, transactions: 63, customers: 57 },
  { date: "2024-01-19", sales: 3850.50, transactions: 57, customers: 51 },
  { date: "2024-01-20", sales: 4150.25, transactions: 62, customers: 55 },
  { date: "2024-01-21", sales: 4650.75, transactions: 69, customers: 62 },
  { date: "2024-01-22", sales: 4200.50, transactions: 62, customers: 56 },
  { date: "2024-01-23", sales: 3750.25, transactions: 56, customers: 50 },
  { date: "2024-01-24", sales: 4050.75, transactions: 60, customers: 54 },
  { date: "2024-01-25", sales: 4350.50, transactions: 65, customers: 58 },
  { date: "2024-01-26", sales: 4750.25, transactions: 71, customers: 64 },
  { date: "2024-01-27", sales: 4450.75, transactions: 66, customers: 59 },
  { date: "2024-01-28", sales: 4150.50, transactions: 62, customers: 55 },
  { date: "2024-01-29", sales: 4550.25, transactions: 68, customers: 61 },
  { date: "2024-01-30", sales: 4850.75, transactions: 72, customers: 65 }
];

// Weekly trend data for comparison
const weeklyTrendData = [
  { week: "Week 1", sales: 19250.50, avgDaily: 2750.07, transactions: 295, customers: 265 },
  { week: "Week 2", sales: 23850.75, avgDaily: 3407.25, transactions: 387, customers: 341 },
  { week: "Week 3", sales: 27950.25, avgDaily: 3992.89, transactions: 426, customers: 378 },
  { week: "Week 4", sales: 31200.75, avgDaily: 4457.25, transactions: 464, customers: 418 }
];

const endOfDayData = {
  totalSales: 3250.75,
  totalTax: 650.15,
  cashSales: 1850.25,
  debitSales: 1075.50,
  creditSales: 325.00,
  customerCount: 42,
  storeName: "Cannabest Dispensary - Main",
  generatedBy: "Sarah Johnson",
  monthlySalesTotal: 87425.50,
  dayOfMonth: 15,
  daysInMonth: 31,
  drawerBreakdowns: [
    {
      name: "Cash Register 1",
      employee: "Emma Rodriguez",
      openingAmount: 300.00,
      dailySales: 1250.00,
      expectedClosing: 875.00,
      actualCounted: 850.00,
      variance: -25.00,
      status: "under"
    },
    {
      name: "Cash Register 2",
      employee: "John Smith",
      openingAmount: 250.00,
      dailySales: 975.50,
      expectedClosing: 725.50,
      actualCounted: 725.50,
      variance: 0.00,
      status: "exact"
    },
    {
      name: "Cash Register 3",
      employee: "Lisa Park",
      openingAmount: 200.00,
      dailySales: 850.75,
      expectedClosing: 650.75,
      actualCounted: 675.25,
      variance: 24.50,
      status: "over"
    }
  ]
};

export default function Analytics() {
  const [timeframe, setTimeframe] = useState("today");
  const [selectedTab, setSelectedTab] = useState("overview");
  const [selectedLocation, setSelectedLocation] = useState("all");
  const [customDateRange, setCustomDateRange] = useState<{startDate: string, endDate: string} | null>(null);
  const [showSalesTrendDialog, setShowSalesTrendDialog] = useState(false);

  // Store locations data
  const storeLocations = [
    { id: "all", name: "All Locations", address: "Combined View" },
    { id: "main", name: "Cannabest Main Store", address: "123 Cannabis St, Portland, OR" },
    { id: "downtown", name: "Cannabest Downtown", address: "456 Main St, Portland, OR" },
    { id: "eastside", name: "Cannabest Eastside", address: "789 Division St, Portland, OR" }
  ];

  // Individual location data for breakdown
  const locationBreakdownData = {
    main: {
      today: { revenue: 1500.25, transactions: 22, customers: 20, avgOrderValue: 68.20 },
      week: { revenue: 8750.50, transactions: 135, customers: 95, avgOrderValue: 64.82 },
      month: { revenue: 42500.75, transactions: 650, customers: 430, avgOrderValue: 65.38 }
    },
    downtown: {
      today: { revenue: 1200.75, transactions: 18, customers: 16, avgOrderValue: 66.71 },
      week: { revenue: 6500.25, transactions: 98, customers: 68, avgOrderValue: 66.33 },
      month: { revenue: 30250.50, transactions: 485, customers: 325, avgOrderValue: 62.37 }
    },
    eastside: {
      today: { revenue: 549.75, transactions: 8, customers: 6, avgOrderValue: 68.72 },
      week: { revenue: 3499.50, transactions: 52, customers: 35, avgOrderValue: 67.30 },
      month: { revenue: 14674.25, transactions: 215, customers: 137, avgOrderValue: 68.25 }
    }
  };

  // Get current location name for display
  const currentLocationName = storeLocations.find(loc => loc.id === selectedLocation)?.name || "All Locations";

  const printOverview = () => {
    const currentData = timeframe === 'today' ? salesData.today :
                        timeframe === 'week' ? salesData.week : salesData.month;

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>Complete Analytics Overview Report</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
              .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
              .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
              .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center; }
              .stat-value { font-size: 20px; font-weight: bold; color: #333; }
              .stat-label { font-size: 12px; color: #666; margin-bottom: 5px; }
              .section { margin: 30px 0; }
              .section-title { font-size: 16px; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
              table { width: 100%; border-collapse: collapse; margin: 15px 0; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; font-weight: bold; }
              .location-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 15px 0; }
              .location-card { border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
              .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; page-break-inside: avoid; }
              @media print { .pagebreak { page-break-before: always; } }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>Complete Analytics Overview Report</h1>
              <p><strong>Store:</strong> ${currentLocationName}</p>
              <p><strong>Period:</strong> ${timeframe.charAt(0).toUpperCase() + timeframe.slice(1)} • <strong>Generated:</strong> ${new Date().toLocaleString()}</p>
            </div>

            <!-- Key Metrics -->
            <div class="section">
              <div class="section-title">Key Performance Metrics</div>
              <div class="stats-grid">
                <div class="stat-card">
                  <div class="stat-label">Revenue</div>
                  <div class="stat-value">$${currentData.revenue.toLocaleString()}</div>
                  ${timeframe === 'today' && currentData.change ? `<div style="font-size: 10px; color: ${currentData.change.revenue > 0 ? 'green' : 'red'}">${currentData.change.revenue > 0 ? '+' : ''}${currentData.change.revenue}% vs yesterday</div>` : ''}
                </div>
                <div class="stat-card">
                  <div class="stat-label">Transactions</div>
                  <div class="stat-value">${currentData.transactions}</div>
                  ${timeframe === 'today' && currentData.change ? `<div style="font-size: 10px; color: ${currentData.change.transactions > 0 ? 'green' : 'red'}">${currentData.change.transactions > 0 ? '+' : ''}${currentData.change.transactions}% vs yesterday</div>` : ''}
                </div>
                <div class="stat-card">
                  <div class="stat-label">Customers</div>
                  <div class="stat-value">${currentData.customers}</div>
                  ${timeframe === 'today' && currentData.change ? `<div style="font-size: 10px; color: ${currentData.change.customers > 0 ? 'green' : 'red'}">${currentData.change.customers > 0 ? '+' : ''}${currentData.change.customers}% vs yesterday</div>` : ''}
                </div>
                <div class="stat-card">
                  <div class="stat-label">Average Order Value</div>
                  <div class="stat-value">$${currentData.avgOrderValue.toFixed(2)}</div>
                  ${timeframe === 'today' && currentData.change ? `<div style="font-size: 10px; color: ${currentData.change.avgOrderValue > 0 ? 'green' : 'red'}">${currentData.change.avgOrderValue > 0 ? '+' : ''}${currentData.change.avgOrderValue}% vs yesterday</div>` : ''}
                </div>
              </div>
            </div>

            ${selectedLocation === "all" ? `
            <!-- Location Performance Breakdown -->
            <div class="section">
              <div class="section-title">Individual Location Performance</div>
              <div class="location-grid">
                ${storeLocations.filter(loc => loc.id !== "all").map(location => {
                  const locationData = locationBreakdownData[location.id];
                  const currentLocationData = timeframe === 'today' ? locationData.today :
                                            timeframe === 'week' ? locationData.week :
                                            locationData.month;
                  return `
                    <div class="location-card">
                      <h4 style="margin: 0 0 10px 0; font-size: 14px;">${location.name}</h4>
                      <div style="font-size: 10px; color: #666; margin-bottom: 10px;">${location.address}</div>
                      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-size: 11px;">
                        <div><strong>Revenue:</strong> $${currentLocationData.revenue.toLocaleString()}</div>
                        <div><strong>Transactions:</strong> ${currentLocationData.transactions}</div>
                        <div><strong>Customers:</strong> ${currentLocationData.customers}</div>
                        <div><strong>Avg Order:</strong> $${currentLocationData.avgOrderValue.toFixed(2)}</div>
                      </div>
                      <div style="margin-top: 8px; font-size: 10px; color: #666;">
                        <strong>Contribution:</strong> ${((currentLocationData.revenue / currentData.revenue) * 100).toFixed(1)}% of total
                      </div>
                    </div>
                  `;
                }).join('')}
              </div>
            </div>
            ` : ''}

            <!-- Category Performance -->
            <div class="section">
              <div class="section-title">Category Performance</div>
              <table>
                <tr>
                  <th>Category</th>
                  <th>Sales Count</th>
                  <th>Revenue</th>
                  <th>Percentage</th>
                </tr>
                ${categoryData.map(category => `
                  <tr>
                    <td>${category.name}</td>
                    <td>${category.sales}</td>
                    <td>$${category.revenue.toLocaleString()}</td>
                    <td>${category.percentage}%</td>
                  </tr>
                `).join('')}
              </table>
            </div>

            <!-- Top Products -->
            <div class="section">
              <div class="section-title">Top Selling Products</div>
              <table>
                <tr>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Units Sold</th>
                  <th>Revenue</th>
                </tr>
                ${topProducts.map(product => `
                  <tr>
                    <td>${product.name}</td>
                    <td>${product.category}</td>
                    <td>${product.sales}</td>
                    <td>$${product.revenue.toLocaleString()}</td>
                  </tr>
                `).join('')}
              </table>
            </div>

            <!-- Customer Insights -->
            <div class="section">
              <div class="section-title">Customer Insights</div>
              <div class="stats-grid">
                <div class="stat-card">
                  <div class="stat-label">New Customers</div>
                  <div class="stat-value">${customerInsights.newCustomers}</div>
                </div>
                <div class="stat-card">
                  <div class="stat-label">Returning Customers</div>
                  <div class="stat-value">${customerInsights.returningCustomers}</div>
                </div>
                <div class="stat-card">
                  <div class="stat-label">Loyalty Members</div>
                  <div class="stat-value">${customerInsights.loyaltyMembers}</div>
                </div>
                <div class="stat-card">
                  <div class="stat-label">Medical Patients</div>
                  <div class="stat-value">${customerInsights.medicalPatients}</div>
                </div>
              </div>
            </div>

            <!-- Employee Performance -->
            <div class="section">
              <div class="section-title">Employee Performance</div>
              <table>
                <tr>
                  <th>Employee</th>
                  <th>Sales</th>
                  <th>Transactions</th>
                  <th>Avg Order</th>
                </tr>
                ${employeeMetrics.map(emp => `
                  <tr>
                    <td>${emp.name}</td>
                    <td>$${emp.sales.toLocaleString()}</td>
                    <td>${emp.transactions}</td>
                    <td>$${emp.avgOrder.toFixed(2)}</td>
                  </tr>
                `).join('')}
              </table>
            </div>

            <!-- Inventory Alerts -->
            <div class="section">
              <div class="section-title">Critical Inventory Alerts</div>
              <table>
                <tr>
                  <th>Product</th>
                  <th>Current Stock</th>
                  <th>Reorder Point</th>
                  <th>Status</th>
                </tr>
                ${inventoryAlerts.map(item => `
                  <tr style="background-color: ${item.status === 'critical' ? '#ffebee' : item.status === 'low' ? '#fff3e0' : 'white'}">
                    <td>${item.product}</td>
                    <td>${item.stock}</td>
                    <td>${item.reorderPoint}</td>
                    <td style="font-weight: bold; color: ${item.status === 'critical' ? 'red' : item.status === 'low' ? 'orange' : 'green'}">
                      ${item.status.toUpperCase()}
                    </td>
                  </tr>
                `).join('')}
              </table>
            </div>

            <div class="footer">
              <p><strong>Complete Analytics Overview Report</strong></p>
              <p>Generated by Cannabest POS System • ${new Date().toLocaleString()}</p>
              <p>Report includes all key metrics, location performance, product sales, customer insights, and inventory status</p>
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const printProducts = () => {
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>Product Analytics Report</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
              table { width: 100%; border-collapse: collapse; margin: 20px 0; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; font-weight: bold; }
              .category-section { margin: 30px 0; }
              .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>Product Analytics Report</h1>
              <p>Period: ${timeframe.charAt(0).toUpperCase() + timeframe.slice(1)} • Generated: ${new Date().toLocaleString()}</p>
            </div>

            <div class="category-section">
              <h2>Top Selling Products</h2>
              <table>
                <tr>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Units Sold</th>
                  <th>Revenue</th>
                  <th>Avg Price</th>
                </tr>
                ${topProducts.map(product => `
                  <tr>
                    <td>${product.name}</td>
                    <td>${product.category}</td>
                    <td>${product.unitsSold}</td>
                    <td>$${product.revenue.toFixed(2)}</td>
                    <td>$${product.avgPrice.toFixed(2)}</td>
                  </tr>
                `).join('')}
              </table>
            </div>

            <div class="footer">
              Generated by Cannabest POS System
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const printCustomers = () => {
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>Customer Analytics Report</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
              .metrics-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0; }
              .metric-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center; }
              .metric-value { font-size: 20px; font-weight: bold; color: #333; }
              .metric-label { font-size: 14px; color: #666; }
              .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>Customer Analytics Report</h1>
              <p>Period: ${timeframe.charAt(0).toUpperCase() + timeframe.slice(1)} • Generated: ${new Date().toLocaleString()}</p>
            </div>

            <div class="metrics-grid">
              <div class="metric-card">
                <div class="metric-value">${customerMetrics.newCustomers}</div>
                <div class="metric-label">New Customers</div>
              </div>
              <div class="metric-card">
                <div class="metric-value">${customerMetrics.returningCustomers}</div>
                <div class="metric-label">Returning Customers</div>
              </div>
              <div class="metric-card">
                <div class="metric-value">$${customerMetrics.avgSpendPerCustomer.toFixed(2)}</div>
                <div class="metric-label">Avg Spend per Customer</div>
              </div>
              <div class="metric-card">
                <div class="metric-value">${customerMetrics.loyaltyMembers}</div>
                <div class="metric-label">Loyalty Members</div>
              </div>
              <div class="metric-card">
                <div class="metric-value">${customerMetrics.medicalPatients}</div>
                <div class="metric-label">Medical Patients</div>
              </div>
              <div class="metric-card">
                <div class="metric-value">${customerMetrics.totalCustomers}</div>
                <div class="metric-label">Total Customers</div>
              </div>
            </div>

            <div class="footer">
              Generated by Cannabest POS System
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const printInventory = () => {
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>Inventory Analytics Report</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
              table { width: 100%; border-collapse: collapse; margin: 20px 0; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; font-weight: bold; }
              .low-stock { background-color: #ffebee; }
              .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>Inventory Analytics Report</h1>
              <p>Generated: ${new Date().toLocaleString()}</p>
            </div>

            <h2>Inventory Status by Category</h2>
            <table>
              <tr>
                <th>Category</th>
                <th>Items in Stock</th>
                <th>Total Value</th>
                <th>Low Stock Items</th>
                <th>Out of Stock Items</th>
              </tr>
              ${inventoryMetrics.map(metric => `
                <tr ${metric.lowStockItems > 0 ? 'class="low-stock"' : ''}>
                  <td>${metric.category}</td>
                  <td>${metric.itemsInStock}</td>
                  <td>$${metric.totalValue.toFixed(2)}</td>
                  <td>${metric.lowStockItems}</td>
                  <td>${metric.outOfStockItems}</td>
                </tr>
              `).join('')}
            </table>

            <div class="footer">
              Generated by Cannabest POS System
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const printEmployees = () => {
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>Employee Performance Report</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
              table { width: 100%; border-collapse: collapse; margin: 20px 0; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; font-weight: bold; }
              .summary { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px; }
              .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>Employee Performance Report</h1>
              <p>Period: ${timeframe.charAt(0).toUpperCase() + timeframe.slice(1)} • Generated: ${new Date().toLocaleString()}</p>
            </div>

            <h2>Employee Performance Metrics</h2>
            <table>
              <tr>
                <th>Employee</th>
                <th>Total Sales</th>
                <th>Transactions</th>
                <th>Avg Order Value</th>
                <th>Hours Worked</th>
                <th>Sales per Hour</th>
              </tr>
              ${employeeMetrics.map(emp => `
                <tr>
                  <td>${emp.name}</td>
                  <td>$${emp.sales.toFixed(2)}</td>
                  <td>${emp.transactions}</td>
                  <td>$${emp.avgOrderValue.toFixed(2)}</td>
                  <td>${emp.hoursWorked}</td>
                  <td>$${emp.salesPerHour.toFixed(2)}</td>
                </tr>
              `).join('')}
            </table>

            <div class="summary">
              <h3>Store Summary</h3>
              <p><strong>Total Sales:</strong> $${employeeMetrics.reduce((sum, emp) => sum + emp.sales, 0).toLocaleString()}</p>
              <p><strong>Total Transactions:</strong> ${employeeMetrics.reduce((sum, emp) => sum + emp.transactions, 0)}</p>
              <p><strong>Store Average Order:</strong> $${(employeeMetrics.reduce((sum, emp) => sum + emp.sales, 0) / employeeMetrics.reduce((sum, emp) => sum + emp.transactions, 0)).toFixed(2)}</p>
            </div>

            <div class="footer">
              Generated by Cannabest POS System
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const printASPD = () => {
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>ASPD Report</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
              table { width: 100%; border-collapse: collapse; margin: 20px 0; }
              th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
              th { background-color: #f2f2f2; font-weight: bold; }
              .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>ASPD - Average Sold Per Day Report</h1>
              <p>Period: ${timeframe === "custom" && customDateRange
                ? `${new Date(customDateRange.startDate).toLocaleDateString()} - ${new Date(customDateRange.endDate).toLocaleDateString()}`
                : "current month"} (${aspdData[0]?.daysInRange || 15} days)</p>
              <p>Generated: ${new Date().toLocaleString()}</p>
            </div>

            <table>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Total Sold</th>
                <th>Days in Range</th>
                <th>Average Sold Per Day</th>
                <th>Revenue per Day</th>
              </tr>
              ${aspdData.map(item => `
                <tr>
                  <td>${item.product}</td>
                  <td>${item.category}</td>
                  <td>${item.totalSold}</td>
                  <td>${item.daysInRange}</td>
                  <td>${item.avgPerDay.toFixed(2)}</td>
                  <td>$${item.revenuePerDay.toFixed(2)}</td>
                </tr>
              `).join('')}
            </table>

            <div class="footer">
              Generated by Cannabest POS System
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const downloadEndOfDayPDF = () => {
    const monthlyPace = (endOfDayData.monthlySalesTotal / endOfDayData.dayOfMonth) * endOfDayData.daysInMonth;

    const reportContent = `
      <div style="font-family: Arial, sans-serif; padding: 20px;">
        <div style="border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px;">
          <h1>End of Day Report</h1>
          <p><strong>Store:</strong> ${endOfDayData.storeName}</p>
          <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
          <p><strong>Generated By:</strong> ${endOfDayData.generatedBy}</p>
          <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
        </div>

        <h2>Sales Summary</h2>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Total Sales:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">$${endOfDayData.totalSales.toFixed(2)}</td></tr>
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Total Tax:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">$${endOfDayData.totalTax.toFixed(2)}</td></tr>
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Customer Count:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">${endOfDayData.customerCount}</td></tr>
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Average per Customer:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">$${(endOfDayData.totalSales / endOfDayData.customerCount).toFixed(2)}</td></tr>
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Cash Sales:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">$${endOfDayData.cashSales.toFixed(2)}</td></tr>
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Debit Sales:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">$${endOfDayData.debitSales.toFixed(2)}</td></tr>
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Credit Sales:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">$${endOfDayData.creditSales.toFixed(2)}</td></tr>
        </table>

        <h2>Monthly Performance</h2>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Monthly Sales Total:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">$${endOfDayData.monthlySalesTotal.toFixed(2)}</td></tr>
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Day of Month:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">${endOfDayData.dayOfMonth} of ${endOfDayData.daysInMonth}</td></tr>
          <tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>Monthly Pace:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">$${monthlyPace.toFixed(2)}</td></tr>
        </table>

        <h2>Drawer Breakdowns</h2>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
          <tr>
            <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Drawer</th>
            <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Employee</th>
            <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Opening</th>
            <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Sales</th>
            <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Expected</th>
            <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Counted</th>
            <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Variance</th>
          </tr>
          ${endOfDayData.drawerBreakdowns.map(drawer => `
            <tr>
              <td style="border: 1px solid #ddd; padding: 8px;">${drawer.name}</td>
              <td style="border: 1px solid #ddd; padding: 8px;">${drawer.employee}</td>
              <td style="border: 1px solid #ddd; padding: 8px;">$${drawer.openingAmount.toFixed(2)}</td>
              <td style="border: 1px solid #ddd; padding: 8px;">$${drawer.dailySales.toFixed(2)}</td>
              <td style="border: 1px solid #ddd; padding: 8px;">$${drawer.expectedClosing.toFixed(2)}</td>
              <td style="border: 1px solid #ddd; padding: 8px;">$${drawer.actualCounted.toFixed(2)}</td>
              <td style="border: 1px solid #ddd; padding: 8px; color: ${drawer.variance > 0 ? 'green' : drawer.variance < 0 ? 'red' : 'black'};">$${drawer.variance.toFixed(2)}</td>
            </tr>
          `).join('')}
        </table>

        <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #666;">
          Generated by Cannabest POS System
        </div>
      </div>
    `;

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head><title>End of Day Report</title></head>
          <body>${reportContent}</body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const downloadEndOfDayExcel = () => {
    const monthlyPace = (endOfDayData.monthlySalesTotal / endOfDayData.dayOfMonth) * endOfDayData.daysInMonth;

    const csvData = [
      ['End of Day Report'],
      ['Store Name', endOfDayData.storeName],
      ['Date', new Date().toLocaleDateString()],
      ['Generated By', endOfDayData.generatedBy],
      [''],
      ['Sales Summary'],
      ['Metric', 'Amount'],
      ['Total Sales', endOfDayData.totalSales.toFixed(2)],
      ['Total Tax', endOfDayData.totalTax.toFixed(2)],
      ['Customer Count', endOfDayData.customerCount],
      ['Average per Customer', (endOfDayData.totalSales / endOfDayData.customerCount).toFixed(2)],
      ['Cash Sales', endOfDayData.cashSales.toFixed(2)],
      ['Debit Sales', endOfDayData.debitSales.toFixed(2)],
      ['Credit Sales', endOfDayData.creditSales.toFixed(2)],
      [''],
      ['Monthly Performance'],
      ['Monthly Sales Total', endOfDayData.monthlySalesTotal.toFixed(2)],
      ['Day of Month', `${endOfDayData.dayOfMonth} of ${endOfDayData.daysInMonth}`],
      ['Monthly Pace', monthlyPace.toFixed(2)],
      [''],
      ['Drawer Breakdowns'],
      ['Drawer', 'Employee', 'Opening', 'Sales', 'Expected', 'Counted', 'Variance'],
      ...endOfDayData.drawerBreakdowns.map(drawer => [
        drawer.name,
        drawer.employee,
        drawer.openingAmount.toFixed(2),
        drawer.dailySales.toFixed(2),
        drawer.expectedClosing.toFixed(2),
        drawer.actualCounted.toFixed(2),
        drawer.variance.toFixed(2)
      ])
    ];

    const csvContent = csvData.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `End_of_Day_Report_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const currentData = salesData[timeframe as keyof typeof salesData];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Analytics Dashboard</h1>
            <p className="text-sm opacity-80">
              {selectedLocation === "all"
                ? "Combined performance across all locations"
                : `Performance for ${currentLocationName}`}
            </p>
          </div>
          <div className="flex items-center gap-4">
            <Select value={selectedLocation} onValueChange={setSelectedLocation}>
              <SelectTrigger className="w-64 bg-white/70 border-white/90 text-gray-900 font-medium">
                <div className="flex items-center gap-2">
                  <Store className="w-4 h-4" />
                  <SelectValue placeholder="Select location" />
                </div>
              </SelectTrigger>
              <SelectContent>
                {storeLocations.map(location => (
                  <SelectItem key={location.id} value={location.id}>
                    <div className="flex items-center gap-2">
                      {location.id === "all" ? (
                        <Building2 className="w-4 h-4" />
                      ) : (
                        <MapPin className="w-4 h-4" />
                      )}
                      <div>
                        <div className="font-medium">{location.name}</div>
                        <div className="text-xs text-gray-500">{location.address}</div>
                      </div>
                    </div>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <Select value={timeframe} onValueChange={(value) => {
              setTimeframe(value);
              if (value !== "custom") {
                setCustomDateRange(null);
              }
            }}>
              <SelectTrigger className="w-40 bg-white/70 border-white/90 text-gray-900 font-medium">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="today">Today</SelectItem>
                <SelectItem value="week">This Week</SelectItem>
                <SelectItem value="month">This Month</SelectItem>
                <SelectItem value="custom">Custom Range</SelectItem>
              </SelectContent>
            </Select>
            {timeframe === "custom" && (
              <DatePickerWithRange
                value={customDateRange}
                onDateRangeChange={setCustomDateRange}
                className="w-48"
              />
            )}
            <Button variant="outline" className="header-button-visible">
              <Download className="w-4 h-4 mr-2" />
              Export
            </Button>
          </div>
        </div>
      </header>

      <div className="container mx-auto p-6">
        <Tabs value={selectedTab} onValueChange={setSelectedTab}>
          <TabsList className="grid w-full grid-cols-7">
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="products">Products</TabsTrigger>
            <TabsTrigger value="customers">Customers</TabsTrigger>
            <TabsTrigger value="inventory">Inventory</TabsTrigger>
            <TabsTrigger value="employees">Employees</TabsTrigger>
            <TabsTrigger value="aspd">ASPD</TabsTrigger>
            <TabsTrigger value="eod">End of Day</TabsTrigger>
          </TabsList>

          <TabsContent value="overview" className="space-y-6">
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold">Overview Analytics</h2>
              <Button onClick={printOverview} variant="outline">
                <Printer className="w-4 h-4 mr-2" />
                Print Overview
              </Button>
            </div>
            {/* Key Metrics */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Revenue</CardTitle>
                  <DollarSign className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">${currentData.revenue.toLocaleString()}</div>
                  {timeframe === 'today' && currentData.change && (
                    <div className={`text-xs flex items-center ${currentData.change.revenue > 0 ? 'text-green-600' : 'text-red-600'}`}>
                      {currentData.change.revenue > 0 ? <TrendingUp className="w-3 h-3 mr-1" /> : <TrendingDown className="w-3 h-3 mr-1" />}
                      {Math.abs(currentData.change.revenue)}% from yesterday
                    </div>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Transactions</CardTitle>
                  <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{currentData.transactions}</div>
                  {timeframe === 'today' && currentData.change && (
                    <div className={`text-xs flex items-center ${currentData.change.transactions > 0 ? 'text-green-600' : 'text-red-600'}`}>
                      {currentData.change.transactions > 0 ? <TrendingUp className="w-3 h-3 mr-1" /> : <TrendingDown className="w-3 h-3 mr-1" />}
                      {Math.abs(currentData.change.transactions)}% from yesterday
                    </div>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Customers</CardTitle>
                  <Users className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{currentData.customers}</div>
                  {timeframe === 'today' && currentData.change && (
                    <div className={`text-xs flex items-center ${currentData.change.customers > 0 ? 'text-green-600' : 'text-red-600'}`}>
                      {currentData.change.customers > 0 ? <TrendingUp className="w-3 h-3 mr-1" /> : <TrendingDown className="w-3 h-3 mr-1" />}
                      {Math.abs(currentData.change.customers)}% from yesterday
                    </div>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Avg Order Value</CardTitle>
                  <BarChart3 className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">${currentData.avgOrderValue.toFixed(2)}</div>
                  {timeframe === 'today' && currentData.change && (
                    <div className={`text-xs flex items-center ${currentData.change.avgOrderValue > 0 ? 'text-green-600' : 'text-red-600'}`}>
                      {currentData.change.avgOrderValue > 0 ? <TrendingUp className="w-3 h-3 mr-1" /> : <TrendingDown className="w-3 h-3 mr-1" />}
                      {Math.abs(currentData.change.avgOrderValue)}% from yesterday
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>

            {/* Individual Location Breakdown - only show when "All Locations" is selected */}
            {selectedLocation === "all" && (
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <h3 className="text-lg font-semibold">Location Performance Breakdown</h3>
                  <Badge variant="outline">Individual Store Analytics</Badge>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                  {storeLocations.filter(loc => loc.id !== "all").map((location) => {
                    const locationData = locationBreakdownData[location.id as keyof typeof locationBreakdownData];
                    const currentLocationData = timeframe === 'today' ? locationData.today :
                                              timeframe === 'week' ? locationData.week :
                                              locationData.month;

                    return (
                      <Card key={location.id} className="relative">
                        <CardHeader className="pb-3">
                          <div className="flex items-center justify-between">
                            <CardTitle className="text-base">{location.name}</CardTitle>
                            <MapPin className="w-4 h-4 text-muted-foreground" />
                          </div>
                          <p className="text-xs text-muted-foreground">{location.address}</p>
                        </CardHeader>
                        <CardContent className="space-y-4">
                          <div className="grid grid-cols-2 gap-4">
                            <div className="text-center p-3 bg-green-50 rounded-lg">
                              <div className="text-lg font-bold text-green-700">
                                ${currentLocationData.revenue.toLocaleString()}
                              </div>
                              <div className="text-xs text-green-600">Revenue</div>
                            </div>
                            <div className="text-center p-3 bg-blue-50 rounded-lg">
                              <div className="text-lg font-bold text-blue-700">
                                {currentLocationData.transactions}
                              </div>
                              <div className="text-xs text-blue-600">Transactions</div>
                            </div>
                          </div>

                          <div className="grid grid-cols-2 gap-4">
                            <div className="text-center p-3 bg-purple-50 rounded-lg">
                              <div className="text-lg font-bold text-purple-700">
                                {currentLocationData.customers}
                              </div>
                              <div className="text-xs text-purple-600">Customers</div>
                            </div>
                            <div className="text-center p-3 bg-orange-50 rounded-lg">
                              <div className="text-lg font-bold text-orange-700">
                                ${currentLocationData.avgOrderValue.toFixed(2)}
                              </div>
                              <div className="text-xs text-orange-600">Avg Order</div>
                            </div>
                          </div>

                          {/* Location performance indicator */}
                          <div className="pt-2 border-t">
                            <div className="flex items-center justify-between text-xs">
                              <span className="text-muted-foreground">Store Contribution</span>
                              <span className="font-medium">
                                {((currentLocationData.revenue / currentData.revenue) * 100).toFixed(1)}%
                              </span>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              </div>
            )}

            {/* Charts Placeholder */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center justify-between">
                    Sales Trend (Last 30 Days)
                    <Button variant="ghost" size="sm" onClick={() => setShowSalesTrendDialog(true)}>
                      <Eye className="w-4 h-4 mr-2" />
                      View Details
                    </Button>
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {/* Daily Sales Chart Visualization */}
                    <div className="h-48 relative">
                      <div className="absolute inset-0 flex items-end justify-between gap-1 px-2">
                        {salesTrendData.slice(-15).map((day, index) => {
                          const maxSales = Math.max(...salesTrendData.slice(-15).map(d => d.sales));
                          const height = (day.sales / maxSales) * 100;
                          const isToday = day.date === "2024-01-15";
                          return (
                            <div key={index} className="flex flex-col items-center flex-1">
                              <div
                                className={`w-full ${isToday ? 'bg-primary' : 'bg-blue-500'} rounded-t transition-all hover:opacity-80 min-h-[4px]`}
                                style={{ height: `${height}%` }}
                                title={`${day.date}: $${day.sales.toFixed(2)}`}
                              />
                              <span className="text-xs text-muted-foreground mt-1 rotate-45 origin-left">
                                {new Date(day.date).getDate()}
                              </span>
                            </div>
                          );
                        })}
                      </div>
                    </div>

                    {/* Trend Summary */}
                    <div className="grid grid-cols-3 gap-4 pt-4 border-t">
                      <div className="text-center">
                        <div className="text-2xl font-bold text-green-600">
                          ${(salesTrendData.slice(-7).reduce((sum, day) => sum + day.sales, 0) / 7).toFixed(0)}
                        </div>
                        <div className="text-sm text-muted-foreground">Avg Daily (7d)</div>
                      </div>
                      <div className="text-center">
                        <div className="text-2xl font-bold text-blue-600">
                          ${Math.max(...salesTrendData.slice(-30).map(d => d.sales)).toFixed(0)}
                        </div>
                        <div className="text-sm text-muted-foreground">Peak Day</div>
                      </div>
                      <div className="text-center">
                        <div className="text-2xl font-bold text-purple-600">
                          {(((salesTrendData.slice(-7).reduce((sum, day) => sum + day.sales, 0) / 7) /
                             (salesTrendData.slice(-14, -7).reduce((sum, day) => sum + day.sales, 0) / 7) - 1) * 100).toFixed(1)}%
                        </div>
                        <div className="text-sm text-muted-foreground">Week Growth</div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Category Breakdown</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {categoryData.map((category, index) => (
                      <div key={index} className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <Leaf className="w-4 h-4 text-success" />
                          <span className="font-medium">{category.name}</span>
                        </div>
                        <div className="text-right">
                          <div className="font-semibold">${category.revenue.toLocaleString()}</div>
                          <div className="text-sm text-muted-foreground">{category.percentage}%</div>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="products" className="space-y-6">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold">Product Analytics</h2>
              <Button onClick={printProducts} variant="outline">
                <Printer className="w-4 h-4 mr-2" />
                Print Products
              </Button>
            </div>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Top Selling Products</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {topProducts.map((product, index) => (
                      <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div>
                          <div className="font-medium">{product.name}</div>
                          <div className="text-sm text-muted-foreground">{product.category}</div>
                        </div>
                        <div className="text-right">
                          <div className="font-semibold">{product.sales} sold</div>
                          <div className="text-sm text-muted-foreground">${product.revenue.toLocaleString()}</div>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Category Performance</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {categoryData.map((category, index) => (
                      <div key={index} className="space-y-2">
                        <div className="flex justify-between">
                          <span className="font-medium">{category.name}</span>
                          <span className="text-sm">{category.percentage}%</span>
                        </div>
                        <Progress value={category.percentage} className="h-2" />
                        <div className="flex justify-between text-sm text-muted-foreground">
                          <span>{category.sales} units</span>
                          <span>${category.revenue.toLocaleString()}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="customers" className="space-y-6">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold">Customer Analytics</h2>
              <Button onClick={printCustomers} variant="outline">
                <Printer className="w-4 h-4 mr-2" />
                Print Customers
              </Button>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-blue-600">{customerInsights.newCustomers}</div>
                  <div className="text-sm text-muted-foreground">New Customers</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-green-600">{customerInsights.returningCustomers}</div>
                  <div className="text-sm text-muted-foreground">Returning</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-purple-600">{customerInsights.loyaltyMembers}</div>
                  <div className="text-sm text-muted-foreground">Loyalty Members</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-orange-600">{customerInsights.medicalPatients}</div>
                  <div className="text-sm text-muted-foreground">Medical Patients</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-teal-600">{customerInsights.caregivers}</div>
                  <div className="text-sm text-muted-foreground">Caregivers</div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="inventory" className="space-y-6">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold">Inventory Analytics</h2>
              <Button onClick={printInventory} variant="outline">
                <Printer className="w-4 h-4 mr-2" />
                Print Inventory
              </Button>
            </div>
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Package className="w-5 h-5" />
                  Inventory Alerts
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {inventoryAlerts.map((item, index) => (
                    <div key={index} className="flex items-center justify-between p-3 border rounded">
                      <div className="flex items-center gap-3">
                        <Badge variant={item.status === 'critical' ? 'destructive' : 'secondary'}>
                          {item.status}
                        </Badge>
                        <span className="font-medium">{item.product}</span>
                      </div>
                      <div className="text-right">
                        <div className="font-semibold">{item.stock} in stock</div>
                        <div className="text-sm text-muted-foreground">Reorder at {item.reorderPoint}</div>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="employees" className="space-y-6">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold">Employee Performance</h2>
              <Button onClick={printEmployees} variant="outline">
                <Printer className="w-4 h-4 mr-2" />
                Print Employees
              </Button>
            </div>
            <Card>
              <CardHeader>
                <CardTitle>Employee Performance vs Store Totals</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {(() => {
                    const totalStoreSales = employeeMetrics.reduce((sum, emp) => sum + emp.sales, 0);
                    const totalStoreTransactions = employeeMetrics.reduce((sum, emp) => sum + emp.transactions, 0);

                    return employeeMetrics.map((employee, index) => {
                      const salesPercentage = (employee.sales / totalStoreSales * 100);
                      const transactionPercentage = (employee.transactions / totalStoreTransactions * 100);

                      return (
                        <div key={index} className="p-4 bg-gray-50 rounded-lg">
                          <div className="flex items-center justify-between mb-3">
                            <div className="flex items-center gap-3">
                              <div className="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-primary-foreground font-semibold">
                                {employee.name.split(' ').map(n => n[0]).join('')}
                              </div>
                              <span className="font-medium">{employee.name}</span>
                            </div>
                            <div className="text-right">
                              <div className="font-semibold">${employee.sales.toLocaleString()}</div>
                              <div className="text-sm text-muted-foreground">
                                {employee.transactions} transactions • ${employee.avgOrder.toFixed(2)} avg
                              </div>
                            </div>
                          </div>

                          <div className="grid grid-cols-2 gap-4">
                            <div>
                              <div className="flex justify-between items-center mb-1">
                                <span className="text-sm text-muted-foreground">Sales Share</span>
                                <span className="text-sm font-medium">{salesPercentage.toFixed(1)}%</span>
                              </div>
                              <div className="w-full bg-gray-200 rounded-full h-2">
                                <div
                                  className="bg-blue-600 h-2 rounded-full"
                                  style={{ width: `${salesPercentage}%` }}
                                ></div>
                              </div>
                            </div>
                            <div>
                              <div className="flex justify-between items-center mb-1">
                                <span className="text-sm text-muted-foreground">Transaction Share</span>
                                <span className="text-sm font-medium">{transactionPercentage.toFixed(1)}%</span>
                              </div>
                              <div className="w-full bg-gray-200 rounded-full h-2">
                                <div
                                  className="bg-green-600 h-2 rounded-full"
                                  style={{ width: `${transactionPercentage}%` }}
                                ></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    });
                  })()}

                  {/* Store Totals Summary */}
                  <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 className="font-medium mb-2">Store Totals</h4>
                    <div className="grid grid-cols-3 gap-4 text-sm">
                      <div>
                        <span className="text-muted-foreground">Total Sales</span>
                        <div className="font-semibold">${employeeMetrics.reduce((sum, emp) => sum + emp.sales, 0).toLocaleString()}</div>
                      </div>
                      <div>
                        <span className="text-muted-foreground">Total Transactions</span>
                        <div className="font-semibold">{employeeMetrics.reduce((sum, emp) => sum + emp.transactions, 0)}</div>
                      </div>
                      <div>
                        <span className="text-muted-foreground">Store Average Order</span>
                        <div className="font-semibold">${(employeeMetrics.reduce((sum, emp) => sum + emp.sales, 0) / employeeMetrics.reduce((sum, emp) => sum + emp.transactions, 0)).toFixed(2)}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="aspd" className="space-y-6">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold">ASPD Report</h2>
              <Button onClick={printASPD} variant="outline">
                <Printer className="w-4 h-4 mr-2" />
                Print ASPD
              </Button>
            </div>
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div>
                    <CardTitle>ASPD - Average Sold Per Day</CardTitle>
                    <p className="text-sm text-muted-foreground">
                      Product sales averages for {timeframe === "custom" && customDateRange
                        ? `${new Date(customDateRange.startDate).toLocaleDateString()} - ${new Date(customDateRange.endDate).toLocaleDateString()}`
                        : "current month"}
                      ({aspdData[0]?.daysInRange || 15} days)
                    </p>
                  </div>
                  <div className="flex gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => {
                        const csvData = [
                          ['ASPD Report - Average Sold Per Day'],
                          ['Generated Date', new Date().toLocaleDateString()],
                          ['Period', timeframe === "custom" && customDateRange
                            ? `${new Date(customDateRange.startDate).toLocaleDateString()} - ${new Date(customDateRange.endDate).toLocaleDateString()}`
                            : "current month"],
                          ['Days in Range', aspdData[0]?.daysInRange || 15],
                          [''],
                          ['Product', 'Category', 'Total Sold', 'Revenue', 'ASPD', 'Trend'],
                          ...aspdData
                            .sort((a, b) => b.aspd - a.aspd)
                            .map(product => [
                              product.name,
                              product.category,
                              product.unitsSold,
                              product.totalRevenue.toFixed(2),
                              product.aspd.toFixed(1),
                              product.aspd > 5 ? 'Strong' : product.aspd > 2 ? 'Stable' : 'Slow'
                            ]),
                          [''],
                          ['Summary Statistics'],
                          ['Total Units Sold', aspdData.reduce((sum, p) => sum + p.unitsSold, 0)],
                          ['Total Revenue', aspdData.reduce((sum, p) => sum + p.totalRevenue, 0).toFixed(2)],
                          ['Average ASPD', (aspdData.reduce((sum, p) => sum + p.aspd, 0) / aspdData.length).toFixed(1)],
                          ['High Performers (>5/day)', aspdData.filter(p => p.aspd > 5).length]
                        ];

                        const csvContent = csvData.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement("a");
                        const url = URL.createObjectURL(blob);
                        link.setAttribute("href", url);
                        link.setAttribute("download", `ASPD_Report_${new Date().toISOString().split('T')[0]}.csv`);
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                      }}
                    >
                      <Download className="w-4 h-4 mr-2" />
                      Export CSV
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => {
                        const printWindow = window.open('', '_blank');
                        if (printWindow) {
                          printWindow.document.write(`
                            <html>
                              <head>
                                <title>ASPD Report - Average Sold Per Day</title>
                                <style>
                                  body { font-family: Arial, sans-serif; margin: 40px; }
                                  .header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                                  table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                                  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                  th { background-color: #f2f2f2; }
                                  .top-performer { background-color: #dcfce7; }
                                  .summary { margin-top: 30px; }
                                  .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0; }
                                  .summary-card { padding: 15px; border: 1px solid #ddd; text-align: center; }
                                </style>
                              </head>
                              <body>
                                <div class="header">
                                  <h1>ASPD Report - Average Sold Per Day</h1>
                                  <p><strong>Period:</strong> ${timeframe === "custom" && customDateRange
                                    ? `${new Date(customDateRange.startDate).toLocaleDateString()} - ${new Date(customDateRange.endDate).toLocaleDateString()}`
                                    : "current month"} (${aspdData[0]?.daysInRange || 15} days)</p>
                                  <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
                                </div>

                                <table>
                                  <thead>
                                    <tr>
                                      <th>Product</th>
                                      <th>Category</th>
                                      <th>Total Sold</th>
                                      <th>Revenue</th>
                                      <th>ASPD</th>
                                      <th>Trend</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    ${aspdData
                                      .sort((a, b) => b.aspd - a.aspd)
                                      .map((product, index) => `
                                        <tr class="${index < 3 ? 'top-performer' : ''}">
                                          <td>${product.name}${index < 3 ? ' ⭐' : ''}</td>
                                          <td>${product.category}</td>
                                          <td>${product.unitsSold}</td>
                                          <td>$${product.totalRevenue.toFixed(2)}</td>
                                          <td>${product.aspd.toFixed(1)}</td>
                                          <td>${product.aspd > 5 ? '↗ Strong' : product.aspd > 2 ? '→ Stable' : '↘ Slow'}</td>
                                        </tr>
                                      `).join('')}
                                  </tbody>
                                </table>

                                <div class="summary">
                                  <h2>Summary Statistics</h2>
                                  <div class="summary-grid">
                                    <div class="summary-card">
                                      <h3>${aspdData.reduce((sum, p) => sum + p.unitsSold, 0)}</h3>
                                      <p>Total Units Sold</p>
                                    </div>
                                    <div class="summary-card">
                                      <h3>$${aspdData.reduce((sum, p) => sum + p.totalRevenue, 0).toFixed(2)}</h3>
                                      <p>Total Revenue</p>
                                    </div>
                                    <div class="summary-card">
                                      <h3>${(aspdData.reduce((sum, p) => sum + p.aspd, 0) / aspdData.length).toFixed(1)}</h3>
                                      <p>Average ASPD</p>
                                    </div>
                                    <div class="summary-card">
                                      <h3>${aspdData.filter(p => p.aspd > 5).length}</h3>
                                      <p>High Performers</p>
                                    </div>
                                  </div>
                                </div>

                                <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #666;">
                                  Generated by Cannabest POS System
                                </div>
                              </body>
                            </html>
                          `);
                          printWindow.document.close();
                          printWindow.print();
                        }
                      }}
                    >
                      <Printer className="w-4 h-4 mr-2" />
                      Print
                    </Button>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  <div className="grid grid-cols-6 gap-4 p-3 bg-gray-100 rounded-lg font-medium text-sm">
                    <div>Product</div>
                    <div>Category</div>
                    <div className="text-right">Total Sold</div>
                    <div className="text-right">Revenue</div>
                    <div className="text-right">ASPD</div>
                    <div className="text-right">Trend</div>
                  </div>

                  {aspdData
                    .sort((a, b) => b.aspd - a.aspd)
                    .map((product, index) => {
                      const isTopPerformer = index < 3;
                      const trendDirection = product.aspd > 5 ? "up" : product.aspd > 2 ? "stable" : "down";

                      return (
                        <div key={product.id} className={`grid grid-cols-6 gap-4 p-3 rounded-lg border ${isTopPerformer ? 'bg-green-50 border-green-200' : 'bg-gray-50'}`}>
                          <div className="flex items-center gap-2">
                            <span className="font-medium">{product.name}</span>
                            {isTopPerformer && (
                              <Badge variant="default" className="text-xs">Top</Badge>
                            )}
                          </div>
                          <div>
                            <Badge variant="outline">{product.category}</Badge>
                          </div>
                          <div className="text-right">
                            <div className="font-semibold">{product.unitsSold}</div>
                            <div className="text-xs text-muted-foreground">units</div>
                          </div>
                          <div className="text-right">
                            <div className="font-semibold">${product.totalRevenue.toFixed(2)}</div>
                            <div className="text-xs text-muted-foreground">${(product.totalRevenue / product.unitsSold).toFixed(2)}/unit</div>
                          </div>
                          <div className="text-right">
                            <div className="font-semibold text-lg">{product.aspd.toFixed(1)}</div>
                            <div className="text-xs text-muted-foreground">per day</div>
                          </div>
                          <div className="text-right">
                            <Badge
                              variant={trendDirection === "up" ? "default" : trendDirection === "stable" ? "secondary" : "destructive"}
                              className="text-xs"
                            >
                              {trendDirection === "up" ? "↗ Strong" : trendDirection === "stable" ? "��� Stable" : "↘ Slow"}
                            </Badge>
                          </div>
                        </div>
                      );
                    })}
                </div>

                {/* Summary Stats */}
                <div className="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                  <Card>
                    <CardContent className="p-4 text-center">
                      <div className="text-2xl font-bold text-blue-600">
                        {aspdData.reduce((sum, p) => sum + p.unitsSold, 0)}
                      </div>
                      <div className="text-sm text-muted-foreground">Total Units Sold</div>
                    </CardContent>
                  </Card>

                  <Card>
                    <CardContent className="p-4 text-center">
                      <div className="text-2xl font-bold text-green-600">
                        ${aspdData.reduce((sum, p) => sum + p.totalRevenue, 0).toFixed(2)}
                      </div>
                      <div className="text-sm text-muted-foreground">Total Revenue</div>
                    </CardContent>
                  </Card>

                  <Card>
                    <CardContent className="p-4 text-center">
                      <div className="text-2xl font-bold text-purple-600">
                        {(aspdData.reduce((sum, p) => sum + p.aspd, 0) / aspdData.length).toFixed(1)}
                      </div>
                      <div className="text-sm text-muted-foreground">Average ASPD</div>
                    </CardContent>
                  </Card>

                  <Card>
                    <CardContent className="p-4 text-center">
                      <div className="text-2xl font-bold text-orange-600">
                        {aspdData.filter(p => p.aspd > 5).length}
                      </div>
                      <div className="text-sm text-muted-foreground">High Performers (&gt;5/day)</div>
                    </CardContent>
                  </Card>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="eod" className="space-y-6">
            {/* Report Header */}
            <Card>
              <CardContent className="p-4">
                <div className="flex justify-between items-start">
                  <div>
                    <h2 className="text-xl font-semibold">{endOfDayData.storeName}</h2>
                    <p className="text-sm text-muted-foreground">End of Day Report</p>
                    <p className="text-sm text-muted-foreground">Generated by: {endOfDayData.generatedBy}</p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm text-muted-foreground">Date: {new Date().toLocaleDateString()}</p>
                    <p className="text-sm text-muted-foreground">Time: {new Date().toLocaleTimeString()}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* End of Day Summary */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Sales</CardTitle>
                  <DollarSign className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">${endOfDayData.totalSales.toFixed(2)}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Tax</CardTitle>
                  <BarChart3 className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">${endOfDayData.totalTax.toFixed(2)}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Cash Sales</CardTitle>
                  <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">${endOfDayData.cashSales.toFixed(2)}</div>
                  <div className="text-xs text-muted-foreground">
                    {((endOfDayData.cashSales / endOfDayData.totalSales) * 100).toFixed(1)}% of total
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Debit Sales</CardTitle>
                  <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">${endOfDayData.debitSales.toFixed(2)}</div>
                  <div className="text-xs text-muted-foreground">
                    {((endOfDayData.debitSales / endOfDayData.totalSales) * 100).toFixed(1)}% of total
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Credit Sales</CardTitle>
                  <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">${endOfDayData.creditSales.toFixed(2)}</div>
                  <div className="text-xs text-muted-foreground">
                    {((endOfDayData.creditSales / endOfDayData.totalSales) * 100).toFixed(1)}% of total
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Customer Count</CardTitle>
                  <Users className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{endOfDayData.customerCount}</div>
                  <div className="text-xs text-muted-foreground">
                    ${(endOfDayData.totalSales / endOfDayData.customerCount).toFixed(2)} avg per customer
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Monthly Performance */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Card>
                <CardHeader>
                  <CardTitle className="text-sm font-medium">Monthly Sales Total</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">${endOfDayData.monthlySalesTotal.toLocaleString()}</div>
                  <div className="text-xs text-muted-foreground">
                    Total sales for {new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="text-sm font-medium">Monthly Pace</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    ${((endOfDayData.monthlySalesTotal / endOfDayData.dayOfMonth) * endOfDayData.daysInMonth).toLocaleString()}
                  </div>
                  <div className="text-xs text-muted-foreground">
                    Projected monthly total at current pace (day {endOfDayData.dayOfMonth} of {endOfDayData.daysInMonth})
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Drawer Breakdowns */}
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>Individual Drawer Breakdowns</CardTitle>
                <div className="flex gap-2">
                  <Button onClick={downloadEndOfDayPDF}>
                    <Download className="w-4 h-4 mr-2" />
                    Export PDF
                  </Button>
                  <Button variant="outline" onClick={downloadEndOfDayExcel}>
                    <Download className="w-4 h-4 mr-2" />
                    Export Excel
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {endOfDayData.drawerBreakdowns.map((drawer, index) => (
                    <div key={index} className="p-4 border rounded-lg">
                      <div className="flex items-center justify-between mb-3">
                        <div>
                          <h3 className="font-semibold">{drawer.name}</h3>
                          <p className="text-sm text-muted-foreground">Operator: {drawer.employee}</p>
                        </div>
                        <Badge
                          variant={
                            drawer.status === 'exact' ? 'default' :
                            drawer.status === 'over' ? 'secondary' : 'destructive'
                          }
                        >
                          {drawer.status === 'exact' ? 'Exact' :
                           drawer.status === 'over' ? `$${drawer.variance.toFixed(2)} Over` :
                           `$${Math.abs(drawer.variance).toFixed(2)} Under`}
                        </Badge>
                      </div>

                      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                        <div>
                          <Label className="text-xs text-muted-foreground">Opening</Label>
                          <div className="font-semibold">${drawer.openingAmount.toFixed(2)}</div>
                        </div>
                        <div>
                          <Label className="text-xs text-muted-foreground">Daily Sales</Label>
                          <div className="font-semibold">${drawer.dailySales.toFixed(2)}</div>
                        </div>
                        <div>
                          <Label className="text-xs text-muted-foreground">Expected</Label>
                          <div className="font-semibold">${drawer.expectedClosing.toFixed(2)}</div>
                        </div>
                        <div>
                          <Label className="text-xs text-muted-foreground">Counted</Label>
                          <div className="font-semibold">${drawer.actualCounted.toFixed(2)}</div>
                        </div>
                        <div>
                          <Label className="text-xs text-muted-foreground">Variance</Label>
                          <div className={`font-semibold ${
                            drawer.variance > 0 ? 'text-green-600' :
                            drawer.variance < 0 ? 'text-red-600' : 'text-gray-600'
                          }`}>
                            ${drawer.variance.toFixed(2)}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>

                {/* Summary Totals */}
                <div className="mt-6 p-4 bg-gray-50 rounded-lg">
                  <h4 className="font-semibold mb-3">Daily Summary</h4>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                      <Label className="text-xs text-muted-foreground">Total Expected</Label>
                      <div className="font-semibold">
                        ${endOfDayData.drawerBreakdowns.reduce((sum, d) => sum + d.expectedClosing, 0).toFixed(2)}
                      </div>
                    </div>
                    <div>
                      <Label className="text-xs text-muted-foreground">Total Counted</Label>
                      <div className="font-semibold">
                        ${endOfDayData.drawerBreakdowns.reduce((sum, d) => sum + d.actualCounted, 0).toFixed(2)}
                      </div>
                    </div>
                    <div>
                      <Label className="text-xs text-muted-foreground">Total Variance</Label>
                      <div className={`font-semibold ${
                        endOfDayData.drawerBreakdowns.reduce((sum, d) => sum + d.variance, 0) > 0 ? 'text-green-600' :
                        endOfDayData.drawerBreakdowns.reduce((sum, d) => sum + d.variance, 0) < 0 ? 'text-red-600' : 'text-gray-600'
                      }`}>
                        ${endOfDayData.drawerBreakdowns.reduce((sum, d) => sum + d.variance, 0).toFixed(2)}
                      </div>
                    </div>
                    <div>
                      <Label className="text-xs text-muted-foreground">Drawers Status</Label>
                      <div className="text-sm">
                        {endOfDayData.drawerBreakdowns.filter(d => d.status === 'exact').length} Exact, {' '}
                        {endOfDayData.drawerBreakdowns.filter(d => d.status === 'over').length} Over, {' '}
                        {endOfDayData.drawerBreakdowns.filter(d => d.status === 'under').length} Under
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>

        {/* Sales Trend Details Dialog */}
        {showSalesTrendDialog && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl font-semibold">Sales Trend Details (Last 30 Days)</h2>
                <Button variant="ghost" onClick={() => setShowSalesTrendDialog(false)}>
                  <X className="w-4 h-4" />
                </Button>
              </div>

              <div className="space-y-6">
                {/* Summary Statistics */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div className="text-center p-4 bg-blue-50 rounded-lg">
                    <div className="text-2xl font-bold text-blue-600">
                      ${salesTrendData.reduce((sum, day) => sum + day.sales, 0).toFixed(2)}
                    </div>
                    <div className="text-sm text-blue-600">Total Sales (30 days)</div>
                  </div>
                  <div className="text-center p-4 bg-green-50 rounded-lg">
                    <div className="text-2xl font-bold text-green-600">
                      ${(salesTrendData.reduce((sum, day) => sum + day.sales, 0) / 30).toFixed(2)}
                    </div>
                    <div className="text-sm text-green-600">Average Daily Sales</div>
                  </div>
                  <div className="text-center p-4 bg-purple-50 rounded-lg">
                    <div className="text-2xl font-bold text-purple-600">
                      ${Math.max(...salesTrendData.map(d => d.sales)).toFixed(2)}
                    </div>
                    <div className="text-sm text-purple-600">Best Day</div>
                  </div>
                  <div className="text-center p-4 bg-orange-50 rounded-lg">
                    <div className="text-2xl font-bold text-orange-600">
                      {salesTrendData.reduce((sum, day) => sum + day.transactions, 0)}
                    </div>
                    <div className="text-sm text-orange-600">Total Transactions</div>
                  </div>
                </div>

                {/* Daily Breakdown Table */}
                <div>
                  <h3 className="text-lg font-semibold mb-4">Daily Breakdown</h3>
                  <div className="overflow-x-auto">
                    <table className="w-full border-collapse border border-gray-300">
                      <thead>
                        <tr className="bg-gray-50">
                          <th className="border border-gray-300 px-4 py-2 text-left">Date</th>
                          <th className="border border-gray-300 px-4 py-2 text-right">Sales</th>
                          <th className="border border-gray-300 px-4 py-2 text-right">Transactions</th>
                          <th className="border border-gray-300 px-4 py-2 text-right">Customers</th>
                          <th className="border border-gray-300 px-4 py-2 text-right">Avg per Transaction</th>
                          <th className="border border-gray-300 px-4 py-2 text-center">Day of Week</th>
                        </tr>
                      </thead>
                      <tbody>
                        {salesTrendData.map((day, index) => {
                          const dayOfWeek = new Date(day.date).toLocaleDateString('en-US', { weekday: 'short' });
                          const avgPerTransaction = day.transactions > 0 ? (day.sales / day.transactions) : 0;
                          const isToday = day.date === "2024-01-15";

                          return (
                            <tr key={index} className={isToday ? 'bg-blue-50' : 'hover:bg-gray-50'}>
                              <td className="border border-gray-300 px-4 py-2">
                                {new Date(day.date).toLocaleDateString()}
                                {isToday && <span className="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">Today</span>}
                              </td>
                              <td className="border border-gray-300 px-4 py-2 text-right font-medium">
                                ${day.sales.toFixed(2)}
                              </td>
                              <td className="border border-gray-300 px-4 py-2 text-right">
                                {day.transactions}
                              </td>
                              <td className="border border-gray-300 px-4 py-2 text-right">
                                {day.customers}
                              </td>
                              <td className="border border-gray-300 px-4 py-2 text-right">
                                ${avgPerTransaction.toFixed(2)}
                              </td>
                              <td className="border border-gray-300 px-4 py-2 text-center">
                                {dayOfWeek}
                              </td>
                            </tr>
                          );
                        })}
                      </tbody>
                    </table>
                  </div>
                </div>

                {/* Action Buttons */}
                <div className="flex justify-end gap-2">
                  <Button
                    variant="outline"
                    onClick={() => {
                      const printWindow = window.open('', '_blank');
                      if (printWindow) {
                        printWindow.document.write(`
                          <html>
                            <head>
                              <title>Sales Trend Details Report</title>
                              <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                th { background-color: #f2f2f2; }
                                .header { text-align: center; margin-bottom: 30px; }
                                .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0; }
                                .summary-card { text-align: center; padding: 15px; border: 1px solid #ddd; }
                              </style>
                            </head>
                            <body>
                              <div class="header">
                                <h1>Sales Trend Details Report</h1>
                                <p>30-Day Sales Analysis • Generated: ${new Date().toLocaleString()}</p>
                              </div>

                              <div class="summary">
                                <div class="summary-card">
                                  <h3>$${salesTrendData.reduce((sum, day) => sum + day.sales, 0).toFixed(2)}</h3>
                                  <p>Total Sales (30 days)</p>
                                </div>
                                <div class="summary-card">
                                  <h3>$${(salesTrendData.reduce((sum, day) => sum + day.sales, 0) / 30).toFixed(2)}</h3>
                                  <p>Average Daily Sales</p>
                                </div>
                                <div class="summary-card">
                                  <h3>$${Math.max(...salesTrendData.map(d => d.sales)).toFixed(2)}</h3>
                                  <p>Best Day</p>
                                </div>
                                <div class="summary-card">
                                  <h3>${salesTrendData.reduce((sum, day) => sum + day.transactions, 0)}</h3>
                                  <p>Total Transactions</p>
                                </div>
                              </div>

                              <h2>Daily Breakdown</h2>
                              <table>
                                <tr>
                                  <th>Date</th>
                                  <th>Day</th>
                                  <th>Sales</th>
                                  <th>Transactions</th>
                                  <th>Customers</th>
                                  <th>Avg per Transaction</th>
                                </tr>
                                ${salesTrendData.map(day => {
                                  const dayOfWeek = new Date(day.date).toLocaleDateString('en-US', { weekday: 'short' });
                                  const avgPerTransaction = day.transactions > 0 ? (day.sales / day.transactions) : 0;
                                  return `
                                    <tr>
                                      <td>${new Date(day.date).toLocaleDateString()}</td>
                                      <td>${dayOfWeek}</td>
                                      <td>$${day.sales.toFixed(2)}</td>
                                      <td>${day.transactions}</td>
                                      <td>${day.customers}</td>
                                      <td>$${avgPerTransaction.toFixed(2)}</td>
                                    </tr>
                                  `;
                                }).join('')}
                              </table>

                              <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #666;">
                                Generated by Cannabest POS System
                              </div>
                            </body>
                          </html>
                        `);
                        printWindow.document.close();
                        printWindow.print();
                      }
                    }}
                  >
                    <Printer className="w-4 h-4 mr-2" />
                    Print Report
                  </Button>
                  <Button onClick={() => setShowSalesTrendDialog(false)}>
                    Close
                  </Button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
