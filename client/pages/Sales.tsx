import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from "@/components/ui/alert-dialog";
import {
  Search,
  Calendar,
  DollarSign,
  Users,
  ShoppingCart,
  Eye,
  Trash2,
  AlertTriangle,
  FileText,
  CreditCard,
  Banknote,
  Smartphone,
  Gift,
  Filter,
  Upload,
  Database,
  CheckCircle,
  Printer,
  Download,
  Receipt,
  QrCode,
  Tag
} from "lucide-react";

interface SaleItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
  total: number;
  metrcTag?: string; // Full metrc tag for the item
}

interface Sale {
  id: string;
  timestamp: string;
  customer?: {
    name: string;
    type: "recreational" | "medical";
    loyaltyId?: string;
    medicalCard?: {
      number: string;
      issueDate: string;
      expirationDate: string;
    };
  };
  items: SaleItem[];
  subtotal: number;
  tax: number;
  total: number;
  paymentMethod: "card" | "cash" | "mobile" | "gift";
  cashReceived?: number;
  change?: number;
  employee: string;
  status: "completed" | "voided";
  voidReason?: string;
  voidedBy?: string;
  voidedAt?: string;
  loyaltyPointsEarned?: number;
}

// Sample sales data
const sampleSales: Sale[] = [
  {
    id: "TXN-20240115-001",
    timestamp: "2024-01-15T09:15:30Z",
    customer: {
      name: "John Doe",
      type: "recreational",
      loyaltyId: "LOY-001"
    },
    items: [
      { id: "1", name: "Blue Dream", price: 7.00, quantity: 2, total: 14.00, metrcTag: "1A4000000000022000000126" },
      { id: "2", name: "Gummy Bears", price: 25.00, quantity: 1, total: 25.00, metrcTag: "1A4000000000022000000143" }
    ],
    subtotal: 39.00,
    tax: 3.12,
    total: 42.12,
    paymentMethod: "card",
    employee: "Sarah Johnson",
    status: "completed",
    loyaltyPointsEarned: 42
  },
  {
    id: "TXN-20240115-002",
    timestamp: "2024-01-15T10:30:15Z",
    customer: {
      name: "Jane Smith",
      type: "medical",
      medicalCard: {
        number: "MD123456789",
        issueDate: "2023-06-15",
        expirationDate: "2024-06-15"
      }
    },
    items: [
      { id: "3", name: "CBD Tincture", price: 45.00, quantity: 1, total: 45.00, metrcTag: "1A4000000000022000000157" },
      { id: "4", name: "OG Kush", price: 12.00, quantity: 1, total: 12.00, metrcTag: "1A4000000000022000000127" }
    ],
    subtotal: 57.00,
    tax: 0.00, // Medical exempt
    total: 57.00,
    paymentMethod: "cash",
    cashReceived: 60.00,
    change: 3.00,
    employee: "Mike Chen",
    status: "completed"
  },
  {
    id: "TXN-20240115-003",
    timestamp: "2024-01-15T11:45:22Z",
    items: [
      { id: "5", name: "Pre-Roll Pack", price: 20.00, quantity: 2, total: 40.00, metrcTag: "1A4000000000022000000134" }
    ],
    subtotal: 40.00,
    tax: 3.20,
    total: 43.20,
    paymentMethod: "mobile",
    employee: "Emma Rodriguez",
    status: "completed"
  },
  {
    id: "TXN-20240115-004",
    timestamp: "2024-01-15T13:20:45Z",
    customer: {
      name: "Bob Wilson",
      type: "recreational",
      loyaltyId: "LOY-003"
    },
    items: [
      { id: "6", name: "Gelato", price: 14.00, quantity: 1, total: 14.00, metrcTag: "1A4000000000022000000128" },
      { id: "7", name: "Chocolate Bar", price: 18.00, quantity: 1, total: 18.00, metrcTag: "1A4000000000022000000165" }
    ],
    subtotal: 32.00,
    tax: 2.56,
    total: 34.56,
    paymentMethod: "card",
    employee: "Sarah Johnson",
    status: "voided",
    voidReason: "Customer requested refund - product defect",
    voidedBy: "Sarah Johnson",
    voidedAt: "2024-01-15T13:25:30Z"
  },
  // Previous day sales
  {
    id: "TXN-20240114-001",
    timestamp: "2024-01-14T15:30:15Z",
    customer: {
      name: "Alice Brown",
      type: "medical",
      medicalCard: {
        number: "MD987654321",
        issueDate: "2023-08-20",
        expirationDate: "2024-08-20"
      }
    },
    items: [
      { id: "8", name: "Live Resin", price: 50.00, quantity: 1, total: 50.00, metrcTag: "1A4000000000022000000189" },
      { id: "9", name: "Blue Dream", price: 7.00, quantity: 3, total: 21.00, metrcTag: "1A4000000000022000000126" }
    ],
    subtotal: 71.00,
    tax: 0.00,
    total: 71.00,
    paymentMethod: "cash",
    cashReceived: 80.00,
    change: 9.00,
    employee: "Mike Chen",
    status: "completed"
  }
];

export default function Sales() {
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [searchQuery, setSearchQuery] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [paymentFilter, setPaymentFilter] = useState("all");
  const [selectedSale, setSelectedSale] = useState<Sale | null>(null);
  const [voidReason, setVoidReason] = useState("");
  const [sales, setSales] = useState<Sale[]>(sampleSales);
  const [metrcSyncing, setMetrcSyncing] = useState(false);
  const [lastMetrcSync, setLastMetrcSync] = useState<string | null>(null);

  const filteredSales = sales.filter(sale => {
    const saleDate = new Date(sale.timestamp).toISOString().split('T')[0];
    const matchesDate = saleDate === selectedDate;
    const matchesSearch = !searchQuery || 
      sale.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      sale.customer?.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      sale.employee.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesStatus = statusFilter === "all" || sale.status === statusFilter;
    const matchesPayment = paymentFilter === "all" || sale.paymentMethod === paymentFilter;
    
    return matchesDate && matchesSearch && matchesStatus && matchesPayment;
  });

  const todaysSales = filteredSales.filter(sale => sale.status === "completed");
  const voidedSales = filteredSales.filter(sale => sale.status === "voided");

  const totalRevenue = todaysSales.reduce((sum, sale) => sum + sale.total, 0);
  const totalTax = todaysSales.reduce((sum, sale) => sum + sale.tax, 0);
  const totalTransactions = todaysSales.length;

  const handleVoidSale = (sale: Sale) => {
    if (!voidReason.trim()) return;

    setSales(prevSales =>
      prevSales.map(s =>
        s.id === sale.id
          ? {
              ...s,
              status: "voided" as const,
              voidReason,
              voidedBy: "Current User",
              voidedAt: new Date().toISOString()
            }
          : s
      )
    );
    setVoidReason("");
    setSelectedSale(null);
  };

  const handleMetrcSync = async () => {
    setMetrcSyncing(true);
    try {
      const completed = filteredSales.filter((s) => s.status === "completed");
      let pushed = 0;
      for (const sale of completed) {
        const transactions = sale.items
          .filter((it) => !!it.metrcTag)
          .map((it) => ({
            package_label: it.metrcTag as string,
            quantity: it.quantity,
            unit_of_measure: "Each",
            total_amount: it.total,
          }));
        if (transactions.length === 0) continue;
        const body = {
          sales_datetime: new Date(sale.timestamp).toISOString(),
          sales_customer_type:
            sale.customer?.type === "medical" ? "Patient" : "Consumer",
          transactions,
        };
        try {
          const res = await fetch("/api/metrc/sales/receipts", {
            method: "POST",
            headers: { "Content-Type": "application/json", Accept: "application/json" },
            body: JSON.stringify(body),
          });
          if (res.ok) pushed += 1;
        } catch (e) {
          // continue to next sale
        }
      }
      setLastMetrcSync(new Date().toISOString());
      alert(
        pushed > 0
          ? `Successfully pushed ${pushed} sale(s) to METRC.`
          : "No eligible sales with METRC tags to push."
      );
    } finally {
      setMetrcSyncing(false);
    }
  };

  const getPaymentIcon = (method: string) => {
    switch (method) {
      case "card": return <CreditCard className="w-4 h-4" />;
      case "cash": return <Banknote className="w-4 h-4" />;
      case "mobile": return <Smartphone className="w-4 h-4" />;
      case "gift": return <Gift className="w-4 h-4" />;
      default: return <DollarSign className="w-4 h-4" />;
    }
  };

  const handlePrintReport = () => {
    const reportContent = `
      <html>
        <head>
          <title>Sales Management Report</title>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 12px;
              line-height: 1.4;
              color: #000;
              background: white;
            }
            .header {
              text-align: center;
              border-bottom: 2px solid #000;
              padding-bottom: 10px;
              margin-bottom: 20px;
            }
            .summary {
              display: flex;
              justify-content: space-around;
              margin-bottom: 20px;
              font-weight: bold;
            }
            .sales-table {
              width: 100%;
              border-collapse: collapse;
              margin-bottom: 20px;
            }
            .sales-table th, .sales-table td {
              border: 1px solid #000;
              padding: 8px;
              text-align: left;
            }
            .sales-table th {
              background-color: #f0f0f0;
              font-weight: bold;
            }
            .total-row {
              background-color: #f9f9f9;
              font-weight: bold;
            }
            .void-row {
              color: #999;
              text-decoration: line-through;
            }
            @media print {
              body { margin: 0; }
              .no-print { display: none; }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>SALES MANAGEMENT REPORT</h1>
            <p>Generated: ${new Date().toLocaleString()}</p>
            <p>Date Range: ${selectedDate} | Employee: All | Status: ${statusFilter}</p>
          </div>

          <div class="summary">
            <div>Total Revenue: $${totalRevenue.toFixed(2)}</div>
            <div>Total Transactions: ${totalTransactions}</div>
            <div>Total Tax: $${totalTax.toFixed(2)}</div>
            <div>Voided Sales: ${voidedSales.length}</div>
          </div>

          <table class="sales-table">
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>Time</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Payment</th>
                <th>Total</th>
                <th>Employee</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              ${filteredSales.map(sale => `
                <tr class="${sale.status === 'voided' ? 'void-row' : ''}">
                  <td>${sale.id}</td>
                  <td>${new Date(sale.timestamp).toLocaleString()}</td>
                  <td>${sale.customer?.name || 'Walk-in'} ${sale.customer?.type ? `(${sale.customer.type})` : ''}</td>
                  <td>
                    ${sale.items.map(item =>
                      `${item.name} (${item.quantity}x) ${item.metrcTag ? `[...${item.metrcTag.slice(-5)}]` : ''}`
                    ).join('<br>')}
                  </td>
                  <td>${sale.paymentMethod}</td>
                  <td>$${sale.total.toFixed(2)}</td>
                  <td>${sale.employee}</td>
                  <td>${sale.status}${sale.voidReason ? `<br><small>${sale.voidReason}</small>` : ''}</td>
                </tr>
              `).join('')}
            </tbody>
            <tfoot>
              <tr class="total-row">
                <td colspan="5">TOTALS</td>
                <td>$${totalRevenue.toFixed(2)}</td>
                <td colspan="2">${totalTransactions} transactions</td>
              </tr>
            </tfoot>
          </table>

          <div style="margin-top: 20px; font-size: 10px; color: #666;">
            <p>Report includes all ${statusFilter} sales for the specified date range.</p>
            <p>Last Metrc Sync: ${lastMetrcSync ? new Date(lastMetrcSync).toLocaleString() : 'Never'}</p>
          </div>
        </body>
      </html>
    `;

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(reportContent);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    }
  };

  const handleReprintReceipt = (sale: Sale) => {
    const receiptContent = `
      <html>
        <head>
          <title>Receipt - ${sale.id}</title>
          <style>
            body {
              font-family: 'Courier New', monospace;
              font-size: 12px;
              line-height: 1.3;
              color: #000;
              background: white;
              margin: 20px;
              max-width: 300px;
            }
            .header {
              text-align: center;
              border-bottom: 1px dashed #000;
              padding-bottom: 10px;
              margin-bottom: 10px;
            }
            .line-item {
              display: flex;
              justify-content: space-between;
              margin: 2px 0;
            }
            .total-section {
              border-top: 1px dashed #000;
              margin-top: 10px;
              padding-top: 10px;
            }
            .footer {
              text-align: center;
              margin-top: 15px;
              font-size: 10px;
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h2>CANNABEST DISPENSARY</h2>
            <p>123 Cannabis St, Portland, OR</p>
            <p>Transaction: ${sale.id}</p>
            <p>${new Date(sale.timestamp).toLocaleString()}</p>
            <p>Cashier: ${sale.employee}</p>
            ${sale.customer ? `<p>Customer: ${sale.customer.name}</p>` : ''}
          </div>

          <div class="items">
            ${sale.items.map(item => `
              <div class="line-item">
                <span>${item.name} x${item.quantity}</span>
                <span>$${item.total.toFixed(2)}</span>
              </div>
              ${item.metrcTag ? `<div style="font-size: 9px; color: #666;">METRC: ...${item.metrcTag.slice(-5)}</div>` : ''}
            `).join('')}
          </div>

          <div class="total-section">
            <div class="line-item">
              <span>Subtotal:</span>
              <span>$${sale.subtotal.toFixed(2)}</span>
            </div>
            <div class="line-item">
              <span>Tax:</span>
              <span>$${sale.tax.toFixed(2)}</span>
            </div>
            <div class="line-item" style="font-weight: bold;">
              <span>Total:</span>
              <span>$${sale.total.toFixed(2)}</span>
            </div>
            <div class="line-item">
              <span>Payment: ${sale.paymentMethod}</span>
              <span></span>
            </div>
            ${sale.cashReceived ? `
              <div class="line-item">
                <span>Cash Received:</span>
                <span>$${sale.cashReceived.toFixed(2)}</span>
              </div>
              <div class="line-item">
                <span>Change:</span>
                <span>$${sale.change?.toFixed(2) || '0.00'}</span>
              </div>
            ` : ''}
          </div>

          <div class="footer">
            <p>Thank you for shopping with us!</p>
            <p>Visit us at cannabest.com</p>
            <p>Receipt reprinted on ${new Date().toLocaleString()}</p>
          </div>
        </body>
      </html>
    `;

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(receiptContent);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    }
  };

  const handleReprintExitLabel = (sale: Sale) => {
    const exitLabelContent = `
      <html>
        <head>
          <title>Exit Label - ${sale.id}</title>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 11px;
              color: #000;
              background: white;
              margin: 10px;
              max-width: 200px;
            }
            .exit-label {
              border: 2px solid #000;
              padding: 8px;
              text-align: center;
            }
            .warning {
              background-color: #ff0000;
              color: white;
              font-weight: bold;
              padding: 4px;
              margin: 4px 0;
            }
            .info {
              margin: 3px 0;
              font-weight: bold;
            }
          </style>
        </head>
        <body>
          <div class="exit-label">
            <div class="warning">EXIT PACKAGE</div>
            <div class="info">CANNABEST DISPENSARY</div>
            <div>License: 100-0001</div>
            <div>Transaction: ${sale.id}</div>
            <div>${new Date(sale.timestamp).toLocaleDateString()}</div>
            ${sale.customer ? `<div>Customer: ${sale.customer.name}</div>` : ''}
            <div class="warning">FOR OREGON USE ONLY</div>
            <div>Items: ${sale.items.length}</div>
            <div>Total: $${sale.total.toFixed(2)}</div>
            <div style="font-size: 9px; margin-top: 8px;">
              This package contains cannabis products.<br>
              Keep away from children and pets.<br>
              Do not operate machinery.
            </div>
          </div>
        </body>
      </html>
    `;

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(exitLabelContent);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    }
  };

  const handleReprintBarcode = (sale: Sale) => {
    const barcodeContent = `
      <html>
        <head>
          <title>Barcode - ${sale.id}</title>
          <style>
            body {
              font-family: 'Courier New', monospace;
              font-size: 10px;
              color: #000;
              background: white;
              margin: 10px;
              text-align: center;
            }
            .barcode-container {
              border: 1px solid #000;
              padding: 10px;
              display: inline-block;
            }
            .barcode {
              font-family: 'Courier New', monospace;
              font-size: 20px;
              letter-spacing: 2px;
              font-weight: bold;
              margin: 10px 0;
            }
            .barcode-lines {
              font-family: monospace;
              font-size: 24px;
              line-height: 0.8;
              margin: 5px 0;
            }
          </style>
        </head>
        <body>
          <div class="barcode-container">
            <div>CANNABEST DISPENSARY</div>
            <div>Transaction Barcode</div>
            <div class="barcode">${sale.id.replace(/-/g, '')}</div>
            <div class="barcode-lines">||||| |||| |||| |||||</div>
            <div>${new Date(sale.timestamp).toLocaleDateString()}</div>
            <div>$${sale.total.toFixed(2)}</div>
            ${sale.items.map(item => `
              <div style="font-size: 8px; margin: 2px 0;">
                ${item.name.substring(0, 20)}${item.name.length > 20 ? '...' : ''}
                ${item.metrcTag ? ` [${item.metrcTag.slice(-5)}]` : ''}
              </div>
            `).join('')}
          </div>
        </body>
      </html>
    `;

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(barcodeContent);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    }
  };

  const handleExportReport = () => {
    const csvContent = [
      ['Transaction ID', 'Date', 'Time', 'Customer', 'Customer Type', 'Items', 'Subtotal', 'Tax', 'Total', 'Payment Method', 'Employee', 'Status', 'Void Reason'].join(','),
      ...filteredSales.map(sale => [
        sale.id,
        new Date(sale.timestamp).toLocaleDateString(),
        new Date(sale.timestamp).toLocaleTimeString(),
        sale.customer?.name || 'Walk-in',
        sale.customer?.type || '',
        sale.items.map(item => `${item.name} (${item.quantity}x)`).join('; '),
        sale.subtotal.toFixed(2),
        sale.tax.toFixed(2),
        sale.total.toFixed(2),
        sale.paymentMethod,
        sale.employee,
        sale.status,
        sale.voidReason || ''
      ].map(field => `"${field}"`).join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `sales-report-${selectedDate}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Sales Management</h1>
            <p className="text-sm opacity-80">View and manage completed sales transactions</p>
          </div>
          <div className="flex items-center gap-4">
            {lastMetrcSync && (
              <div className="text-sm opacity-75">
                Last sync: {new Date(lastMetrcSync).toLocaleString()}
              </div>
            )}
            <Button
              onClick={handlePrintReport}
              variant="outline"
              className="header-button-visible"
            >
              <Printer className="w-4 h-4 mr-2" />
              Print Report
            </Button>
            <Button
              onClick={handleExportReport}
              variant="outline"
              className="header-button-visible"
            >
              <Download className="w-4 h-4 mr-2" />
              Export CSV
            </Button>
            <Button
              onClick={handleMetrcSync}
              disabled={metrcSyncing}
              aria-label="Push Sales to METRC"
              title="Push Sales to METRC"
              className="bg-green-600 hover:bg-green-700 text-white"
            >
              {metrcSyncing ? (
                <>
                  <Upload className="w-4 h-4 mr-2 animate-spin" />
                  <span>Syncing...</span>
                </>
              ) : (
                <>
                  <Database className="w-4 h-4 mr-2" />
                  <span>Push Sales to METRC</span>
                </>
              )}
            </Button>
          </div>
        </div>
      </header>

      <div className="container mx-auto p-6">
        {/* Controls */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div>
            <Label htmlFor="date-select">Date</Label>
            <Input
              id="date-select"
              type="date"
              value={selectedDate}
              onChange={(e) => setSelectedDate(e.target.value)}
            />
          </div>
          
          <div>
            <Label htmlFor="search">Search</Label>
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                id="search"
                placeholder="Transaction ID, customer, employee..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>

          <div>
            <Label htmlFor="status-filter">Status</Label>
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="voided">Voided</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label htmlFor="payment-filter">Payment</Label>
            <Select value={paymentFilter} onValueChange={setPaymentFilter}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Methods</SelectItem>
                <SelectItem value="card">Card</SelectItem>
                <SelectItem value="cash">Cash</SelectItem>
                <SelectItem value="mobile">Mobile</SelectItem>
                <SelectItem value="gift">Gift Card</SelectItem>
              </SelectContent>
            </Select>
          </div>

        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Revenue</p>
                  <p className="text-2xl font-bold">${totalRevenue.toFixed(2)}</p>
                </div>
                <DollarSign className="w-8 h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Transactions</p>
                  <p className="text-2xl font-bold">{totalTransactions}</p>
                </div>
                <ShoppingCart className="w-8 h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Tax Collected</p>
                  <p className="text-2xl font-bold">${totalTax.toFixed(2)}</p>
                </div>
                <FileText className="w-8 h-8 text-purple-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Voided Sales</p>
                  <p className="text-2xl font-bold">{voidedSales.length}</p>
                </div>
                <AlertTriangle className="w-8 h-8 text-red-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Sales List */}
        <Card>
          <CardHeader>
            <CardTitle>Sales for {new Date(selectedDate).toLocaleDateString()}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {filteredSales.length === 0 ? (
                <div className="text-center py-8 text-muted-foreground">
                  No sales found for the selected criteria
                </div>
              ) : (
                filteredSales.map((sale) => (
                  <div
                    key={sale.id}
                    className={`p-4 border rounded-lg ${
                      sale.status === "voided" ? "bg-red-50 border-red-200" : "bg-white"
                    }`}
                  >
                    <div className="flex items-center justify-between">
                      <div className="space-y-2">
                        <div className="flex items-center gap-3">
                          <span className="font-mono text-sm font-medium">{sale.id}</span>
                          <Badge variant={sale.status === "completed" ? "default" : "destructive"}>
                            {sale.status}
                          </Badge>
                          {sale.customer?.type === "medical" && (
                            <Badge variant="secondary">Medical</Badge>
                          )}
                          {sale.loyaltyPointsEarned && (
                            <Badge variant="outline">+{sale.loyaltyPointsEarned} pts</Badge>
                          )}
                        </div>
                        
                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                          <span>{new Date(sale.timestamp).toLocaleTimeString()}</span>
                          <span className="flex items-center gap-1">
                            {getPaymentIcon(sale.paymentMethod)}
                            {sale.paymentMethod}
                          </span>
                          <span>{sale.employee}</span>
                          {sale.customer && (
                            <span className="flex items-center gap-1">
                              <Users className="w-4 h-4" />
                              {sale.customer.name}
                            </span>
                          )}
                        </div>

                        <div className="text-sm">
                          <span className="text-muted-foreground">Items: </span>
                          {sale.items.map(item =>
                            `${item.name} (${item.quantity})${item.metrcTag ? ` [Metrc: ...${item.metrcTag.slice(-5)}]` : ''}`
                          ).join(", ")}
                        </div>

                        {sale.voidReason && (
                          <div className="bg-red-100 border border-red-200 rounded p-2 text-sm">
                            <strong>Void Reason:</strong> {sale.voidReason}
                            <br />
                            <span className="text-red-600">
                              Voided by {sale.voidedBy} at {sale.voidedAt ? new Date(sale.voidedAt).toLocaleString() : "N/A"}
                            </span>
                          </div>
                        )}
                      </div>

                      <div className="flex items-center gap-3">
                        <div className="text-right">
                          <div className="text-lg font-bold">${sale.total.toFixed(2)}</div>
                          <div className="text-sm text-muted-foreground">
                            ${sale.subtotal.toFixed(2)} + ${sale.tax.toFixed(2)} tax
                          </div>
                        </div>

                        <div className="flex gap-2">
                          <Dialog>
                            <DialogTrigger asChild>
                              <Button variant="outline" size="sm">
                                <Eye className="w-4 h-4" />
                              </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-md">
                              <DialogHeader>
                                <DialogTitle>Sale Details</DialogTitle>
                              </DialogHeader>
                              <div className="space-y-4">
                                <div>
                                  <div className="font-medium">{sale.id}</div>
                                  <div className="text-sm text-muted-foreground">
                                    {new Date(sale.timestamp).toLocaleString()}
                                  </div>
                                </div>

                                {sale.customer && (
                                  <div>
                                    <div className="font-medium">Customer</div>
                                    <div className="text-sm">{sale.customer.name}</div>
                                    <div className="text-sm text-muted-foreground">
                                      {sale.customer.type} customer
                                    </div>
                                    {sale.customer.medicalCard && (
                                      <div className="text-sm text-muted-foreground">
                                        Medical Card: {sale.customer.medicalCard.number}
                                      </div>
                                    )}
                                  </div>
                                )}

                                <div>
                                  <div className="font-medium">Items</div>
                                  {sale.items.map((item, index) => (
                                    <div key={index} className="border-b pb-2 mb-2 last:border-b-0">
                                      <div className="flex justify-between text-sm">
                                        <span>{item.name} x{item.quantity}</span>
                                        <span>${item.total.toFixed(2)}</span>
                                      </div>
                                      {item.metrcTag && (
                                        <div className="text-xs text-muted-foreground mt-1">
                                          Metrc: ...{item.metrcTag.slice(-5)}
                                        </div>
                                      )}
                                    </div>
                                  ))}
                                </div>

                                <div className="border-t pt-2">
                                  <div className="flex justify-between text-sm">
                                    <span>Subtotal</span>
                                    <span>${sale.subtotal.toFixed(2)}</span>
                                  </div>
                                  <div className="flex justify-between text-sm">
                                    <span>Tax</span>
                                    <span>${sale.tax.toFixed(2)}</span>
                                  </div>
                                  <div className="flex justify-between font-medium">
                                    <span>Total</span>
                                    <span>${sale.total.toFixed(2)}</span>
                                  </div>
                                </div>
                              </div>
                            </DialogContent>
                          </Dialog>

                          {sale.status === "completed" && (
                            <>
                              <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handleReprintReceipt(sale)}
                                title="Reprint Receipt"
                              >
                                <Receipt className="w-4 h-4" />
                              </Button>
                              <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handleReprintExitLabel(sale)}
                                title="Reprint Exit Label"
                              >
                                <Tag className="w-4 h-4" />
                              </Button>
                              <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handleReprintBarcode(sale)}
                                title="Reprint Barcode"
                              >
                                <QrCode className="w-4 h-4" />
                              </Button>
                              <AlertDialog>
                                <AlertDialogTrigger asChild>
                                  <Button
                                    variant="destructive"
                                    size="sm"
                                    onClick={() => setSelectedSale(sale)}
                                  >
                                    <Trash2 className="w-4 h-4" />
                                  </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                  <AlertDialogHeader>
                                    <AlertDialogTitle>Void Sale</AlertDialogTitle>
                                    <AlertDialogDescription>
                                      Are you sure you want to void this sale? This action cannot be undone.
                                    </AlertDialogDescription>
                                  </AlertDialogHeader>
                                  <div className="my-4">
                                    <Label htmlFor="void-reason">Reason for voiding (required)</Label>
                                    <Textarea
                                      id="void-reason"
                                      placeholder="Enter reason for voiding this sale..."
                                      value={voidReason}
                                      onChange={(e) => setVoidReason(e.target.value)}
                                      className="mt-2"
                                    />
                                  </div>
                                  <AlertDialogFooter>
                                    <AlertDialogCancel onClick={() => {
                                      setVoidReason("");
                                      setSelectedSale(null);
                                    }}>
                                      Cancel
                                    </AlertDialogCancel>
                                    <AlertDialogAction
                                      onClick={() => handleVoidSale(sale)}
                                      disabled={!voidReason.trim()}
                                      className="bg-red-600 hover:bg-red-700"
                                    >
                                      Void Sale
                                    </AlertDialogAction>
                                  </AlertDialogFooter>
                                </AlertDialogContent>
                              </AlertDialog>
                            </>
                          )}
                        </div>
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
