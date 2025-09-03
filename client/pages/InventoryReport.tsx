import { useState, useMemo } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import {
  BarChart3,
  DollarSign,
  Package,
  TrendingUp,
  FileText,
  Download,
  ArrowLeft,
  Filter,
  RefreshCw,
  Printer
} from "lucide-react";
import { useNavigate } from "react-router-dom";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

// Sample inventory data (in real app, this would come from API)
const allInventoryItems = [
  // Sales Floor Items
  {
    id: "sf1",
    name: "Blue Dream",
    category: "Flower",
    price: 7.00,
    cost: 3.50,
    stock: 150,
    room: "Sales Floor",
    supplier: "Green Valley Supply"
  },
  {
    id: "sf2",
    name: "OG Kush",
    category: "Flower", 
    price: 12.00,
    cost: 6.00,
    stock: 100,
    room: "Sales Floor",
    supplier: "Pacific Coast Cannabis"
  },
  {
    id: "sf3",
    name: "Gummy Bears",
    category: "Edibles",
    price: 25.00,
    cost: 12.50,
    stock: 100,
    room: "Sales Floor",
    supplier: "Edible Creations Co"
  },
  {
    id: "sf4",
    name: "Vape Cartridge",
    category: "Vapes",
    price: 55.00,
    cost: 27.50,
    stock: 40,
    room: "Sales Floor",
    supplier: "Vapor Tech Solutions"
  },
  {
    id: "sf5",
    name: "Pre-Roll Pack",
    category: "Pre-Rolls",
    price: 35.00,
    cost: 17.50,
    stock: 60,
    room: "Sales Floor",
    supplier: "Roll Masters"
  },
  {
    id: "sf6",
    name: "CBD Topical Balm",
    category: "Topicals",
    price: 45.00,
    cost: 22.50,
    stock: 40,
    room: "Sales Floor",
    supplier: "Wellness Products Inc"
  },
  {
    id: "sf7",
    name: "Infused Blunt",
    category: "Infused Pre-Rolls",
    price: 42.00,
    cost: 21.00,
    stock: 35,
    room: "Sales Floor",
    supplier: "Blunt Masters Inc"
  },
  // Storage Items
  {
    id: "inv1",
    name: "OG Kush Reserve",
    category: "Flower",
    price: 12.00,
    cost: 6.00,
    stock: 89,
    room: "Secure Vault",
    supplier: "Premium Cannabis Co"
  },
  {
    id: "inv2",
    name: "Bulk Shake Mix",
    category: "Flower",
    price: 2.50,
    cost: 1.25,
    stock: 2500,
    room: "Main Storage Vault",
    supplier: "Wholesale Cannabis Supply"
  },
  {
    id: "inv3",
    name: "Premium Live Resin Cart",
    category: "Vapes",
    price: 85.00,
    cost: 42.50,
    stock: 45,
    room: "Secure Vault",
    supplier: "Extract Artisans"
  },
  {
    id: "inv4",
    name: "Edible Bulk Gummies",
    category: "Edibles",
    price: 18.00,
    cost: 9.00,
    stock: 350,
    room: "Edibles Storage",
    supplier: "Edible Creations Co"
  },
  {
    id: "inv5",
    name: "Hash - Premium Bubble",
    category: "Concentrates",
    price: 95.00,
    cost: 47.50,
    stock: 12,
    room: "Secure Vault",
    supplier: "Artisan Hash Collective"
  },
  {
    id: "inv6",
    name: "CBD Topical Balm Bulk",
    category: "Topicals",
    price: 35.00,
    cost: 17.50,
    stock: 78,
    room: "Back Room",
    supplier: "Wellness Products Inc"
  },
  {
    id: "inv7",
    name: "Chocolate Bar Bulk",
    category: "Edibles",
    price: 30.00,
    cost: 15.00,
    stock: 80,
    room: "Edibles Storage",
    supplier: "Sweet Relief Co"
  },
  {
    id: "inv8",
    name: "Rosin",
    category: "Concentrates",
    price: 95.00,
    cost: 47.50,
    stock: 12,
    room: "Secure Vault",
    supplier: "Pure Extracts LLC"
  },
  {
    id: "inv9",
    name: "Infused Blunt Bulk",
    category: "Infused Pre-Rolls",
    price: 42.00,
    cost: 21.00,
    stock: 85,
    room: "Back Room",
    supplier: "Blunt Masters Inc"
  }
];

export default function InventoryReport() {
  const navigate = useNavigate();
  const [selectedCategory, setSelectedCategory] = useState<string>("all");
  const [reportType, setReportType] = useState<"summary" | "detailed">("summary");

  // Get unique categories
  const allCategories = useMemo(() => {
    const categories = [...new Set(allInventoryItems.map(item => item.category))];
    return categories.sort();
  }, []);

  // Filter items based on selected category
  const filteredItems = useMemo(() => {
    if (selectedCategory === "all") {
      return allInventoryItems;
    }
    return allInventoryItems.filter(item => item.category === selectedCategory);
  }, [selectedCategory]);
  
  // Calculate category totals
  const categoryTotals = useMemo(() => {
    const totals: Record<string, { totalCost: number; totalValue: number; itemCount: number; stockCount: number }> = {};

    filteredItems.forEach(item => {
      if (!totals[item.category]) {
        totals[item.category] = { totalCost: 0, totalValue: 0, itemCount: 0, stockCount: 0 };
      }
      
      const itemTotalCost = item.cost * item.stock;
      const itemTotalValue = item.price * item.stock;
      
      totals[item.category].totalCost += itemTotalCost;
      totals[item.category].totalValue += itemTotalValue;
      totals[item.category].itemCount += 1;
      totals[item.category].stockCount += item.stock;
    });
    
    return totals;
  }, []);
  
  // Calculate grand totals
  const grandTotals = useMemo(() => {
    return Object.values(categoryTotals).reduce(
      (acc, category) => ({
        totalCost: acc.totalCost + category.totalCost,
        totalValue: acc.totalValue + category.totalValue,
        itemCount: acc.itemCount + category.itemCount,
        stockCount: acc.stockCount + category.stockCount
      }),
      { totalCost: 0, totalValue: 0, itemCount: 0, stockCount: 0 }
    );
  }, [categoryTotals]);
  
  const generateReport = () => {
    const reportDate = new Date().toLocaleDateString();
    const categoryFilter = selectedCategory === "all" ? "All Categories" : selectedCategory;
    const reportContent = `
INVENTORY EVALUATION REPORT
Generated: ${reportDate}
Category Filter: ${categoryFilter}
Report Type: ${reportType}

=== CATEGORY BREAKDOWN ===
${Object.entries(categoryTotals)
  .sort(([,a], [,b]) => b.totalCost - a.totalCost)
  .map(([category, data]) =>
    `${category}:
    Total Cost: $${data.totalCost.toFixed(2)}
    Total Value: $${data.totalValue.toFixed(2)}
    Items: ${data.itemCount} (${data.stockCount} units)
    Margin: $${(data.totalValue - data.totalCost).toFixed(2)} (${(((data.totalValue - data.totalCost) / data.totalCost) * 100).toFixed(1)}%)
    `
  ).join('\n')}

=== GRAND TOTALS ===
Total Inventory Cost: $${grandTotals.totalCost.toFixed(2)}
Total Inventory Value: $${grandTotals.totalValue.toFixed(2)}
Total Items: ${grandTotals.itemCount} (${grandTotals.stockCount} units)
Total Potential Margin: $${(grandTotals.totalValue - grandTotals.totalCost).toFixed(2)}
Average Margin: ${(((grandTotals.totalValue - grandTotals.totalCost) / grandTotals.totalCost) * 100).toFixed(1)}%
    `;

    const blob = new Blob([reportContent], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `inventory-evaluation-report-${reportDate.replace(/\//g, '-')}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  };

  const printReport = () => {
    const reportDate = new Date().toLocaleDateString();
    const categoryFilter = selectedCategory === "all" ? "All Categories" : selectedCategory;

    const detailedItemsHTML = reportType === "detailed" && selectedCategory !== "all"
      ? `
        <div style="margin-bottom: 30px;">
          <h3 style="margin: 20px 0 10px 0; color: #2563eb;">Individual Items in ${selectedCategory}</h3>
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
              <tr style="background-color: #f3f4f6;">
                <th style="border: 1px solid #d1d5db; padding: 8px; text-align: left;">Item Name</th>
                <th style="border: 1px solid #d1d5db; padding: 8px; text-align: left;">Room</th>
                <th style="border: 1px solid #d1d5db; padding: 8px; text-align: right;">Units</th>
                <th style="border: 1px solid #d1d5db; padding: 8px; text-align: right;">Unit Price</th>
                <th style="border: 1px solid #d1d5db; padding: 8px; text-align: right;">Total Value</th>
                <th style="border: 1px solid #d1d5db; padding: 8px; text-align: right;">Margin %</th>
              </tr>
            </thead>
            <tbody>
              ${filteredItems
                .filter(item => item.category === selectedCategory)
                .sort((a, b) => (b.price * b.stock) - (a.price * a.stock))
                .map(item => {
                  const itemValue = item.price * item.stock;
                  const itemCost = item.cost * item.stock;
                  const itemMargin = ((itemValue - itemCost) / itemCost * 100);
                  return `
                    <tr>
                      <td style="border: 1px solid #d1d5db; padding: 8px;">${item.name}</td>
                      <td style="border: 1px solid #d1d5db; padding: 8px;">${item.room}</td>
                      <td style="border: 1px solid #d1d5db; padding: 8px; text-align: right;">${item.stock}</td>
                      <td style="border: 1px solid #d1d5db; padding: 8px; text-align: right;">$${item.price.toFixed(2)}</td>
                      <td style="border: 1px solid #d1d5db; padding: 8px; text-align: right; font-weight: bold;">$${itemValue.toLocaleString()}</td>
                      <td style="border: 1px solid #d1d5db; padding: 8px; text-align: right;">${itemMargin.toFixed(1)}%</td>
                    </tr>
                  `;
                }).join('')}
            </tbody>
          </table>
        </div>
      ` : '';

    const printContent = `
      <html>
        <head>
          <title>Inventory Evaluation Report</title>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 12px;
              line-height: 1.4;
              color: #000;
              background: white;
              margin: 20px;
            }
            .header {
              text-align: center;
              border-bottom: 2px solid #000;
              padding-bottom: 15px;
              margin-bottom: 25px;
            }
            .header h1 {
              margin: 0 0 10px 0;
              font-size: 20px;
              font-weight: bold;
            }
            .summary-grid {
              display: grid;
              grid-template-columns: repeat(4, 1fr);
              gap: 15px;
              margin-bottom: 25px;
              padding: 15px;
              background-color: #f9fafb;
              border: 1px solid #d1d5db;
            }
            .summary-item {
              text-align: center;
            }
            .summary-value {
              font-size: 18px;
              font-weight: bold;
              margin-bottom: 5px;
            }
            .summary-label {
              font-size: 10px;
              color: #6b7280;
            }
            .category-section {
              margin-bottom: 20px;
              border: 1px solid #d1d5db;
              border-radius: 8px;
              padding: 15px;
            }
            .category-header {
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 10px;
              padding-bottom: 8px;
              border-bottom: 1px solid #e5e7eb;
            }
            .category-name {
              font-size: 16px;
              font-weight: bold;
            }
            .category-badge {
              background-color: #3b82f6;
              color: white;
              padding: 2px 8px;
              border-radius: 4px;
              font-size: 10px;
            }
            .category-stats {
              display: grid;
              grid-template-columns: repeat(4, 1fr);
              gap: 10px;
              margin-bottom: 10px;
            }
            .stat-item {
              text-align: center;
            }
            .stat-label {
              font-size: 10px;
              color: #6b7280;
              margin-bottom: 2px;
            }
            .stat-value {
              font-weight: bold;
              font-size: 12px;
            }
            .cost { color: #dc2626; }
            .value { color: #16a34a; }
            .margin { color: #2563eb; }
            .total-section {
              background-color: #f3f4f6;
              padding: 20px;
              border-radius: 8px;
              margin-top: 25px;
            }
            .total-grid {
              display: grid;
              grid-template-columns: repeat(4, 1fr);
              gap: 15px;
              text-align: center;
            }
            .total-item {
              border-right: 1px solid #d1d5db;
            }
            .total-item:last-child {
              border-right: none;
            }
            .total-value {
              font-size: 20px;
              font-weight: bold;
              margin-bottom: 5px;
            }
            .total-label {
              font-size: 12px;
              color: #6b7280;
            }
            @media print {
              body { margin: 0; }
              .no-print { display: none; }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>INVENTORY EVALUATION REPORT</h1>
            <p>Generated: ${new Date().toLocaleString()}</p>
            <p>Category Filter: ${categoryFilter} | Report Type: ${reportType}</p>
          </div>

          <div class="summary-grid">
            <div class="summary-item">
              <div class="summary-value cost">$${grandTotals.totalCost.toLocaleString()}</div>
              <div class="summary-label">Total Inventory Cost</div>
            </div>
            <div class="summary-item">
              <div class="summary-value value">$${grandTotals.totalValue.toLocaleString()}</div>
              <div class="summary-label">Total Inventory Value</div>
            </div>
            <div class="summary-item">
              <div class="summary-value margin">$${(grandTotals.totalValue - grandTotals.totalCost).toLocaleString()}</div>
              <div class="summary-label">Potential Margin</div>
            </div>
            <div class="summary-item">
              <div class="summary-value">${(((grandTotals.totalValue - grandTotals.totalCost) / grandTotals.totalCost) * 100).toFixed(1)}%</div>
              <div class="summary-label">Average Margin</div>
            </div>
          </div>

          ${detailedItemsHTML}

          <h2 style="margin: 25px 0 15px 0;">Category Breakdown</h2>
          ${Object.entries(categoryTotals)
            .sort(([,a], [,b]) => b.totalCost - a.totalCost)
            .map(([category, data]) => {
              const margin = data.totalValue - data.totalCost;
              const marginPercentage = ((margin / data.totalCost) * 100);
              return `
                <div class="category-section">
                  <div class="category-header">
                    <div class="category-name">${category}</div>
                    <div class="category-badge">${marginPercentage.toFixed(1)}% margin</div>
                  </div>
                  <div style="font-size: 11px; color: #6b7280; margin-bottom: 10px;">
                    ${data.itemCount} items • ${data.stockCount.toLocaleString()} units
                  </div>
                  <div class="category-stats">
                    <div class="stat-item">
                      <div class="stat-label">Total Cost</div>
                      <div class="stat-value cost">$${data.totalCost.toLocaleString()}</div>
                    </div>
                    <div class="stat-item">
                      <div class="stat-label">Total Value</div>
                      <div class="stat-value value">$${data.totalValue.toLocaleString()}</div>
                    </div>
                    <div class="stat-item">
                      <div class="stat-label">Potential Margin</div>
                      <div class="stat-value margin">$${margin.toLocaleString()}</div>
                    </div>
                    <div class="stat-item">
                      <div class="stat-label">Avg Unit Cost</div>
                      <div class="stat-value">$${(data.totalCost / data.stockCount).toFixed(2)}</div>
                    </div>
                  </div>
                </div>
              `;
            }).join('')}

          <div class="total-section">
            <h3 style="text-align: center; margin: 0 0 15px 0;">Total Inventory Summary</h3>
            <div class="total-grid">
              <div class="total-item">
                <div class="total-value cost">$${grandTotals.totalCost.toLocaleString()}</div>
                <div class="total-label">Total Investment</div>
              </div>
              <div class="total-item">
                <div class="total-value value">$${grandTotals.totalValue.toLocaleString()}</div>
                <div class="total-label">Potential Revenue</div>
              </div>
              <div class="total-item">
                <div class="total-value margin">$${(grandTotals.totalValue - grandTotals.totalCost).toLocaleString()}</div>
                <div class="total-label">Potential Profit</div>
              </div>
              <div class="total-item">
                <div class="total-value">${grandTotals.stockCount.toLocaleString()}</div>
                <div class="total-label">Total Units</div>
              </div>
            </div>
            <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #d1d5db;">
              <div style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">
                Average Margin: ${(((grandTotals.totalValue - grandTotals.totalCost) / grandTotals.totalCost) * 100).toFixed(1)}%
              </div>
              <div style="font-size: 10px; color: #6b7280;">
                Report generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}
              </div>
            </div>
          </div>
        </body>
      </html>
    `;

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(printContent);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    }
  };
  
  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => navigate(-1)}
              className="header-button-visible"
            >
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back
            </Button>
            <div>
              <h1 className="text-xl font-semibold">Inventory Evaluation Report</h1>
              <p className="text-sm opacity-80">
                {selectedCategory === "all"
                  ? "Complete inventory cost analysis by category"
                  : `Analysis for ${selectedCategory} category`}
              </p>
            </div>
          </div>
          <div className="flex gap-2">
            <Select value={selectedCategory} onValueChange={setSelectedCategory}>
              <SelectTrigger className="w-48 bg-white/70 border-white/90 text-gray-900 font-medium">
                <Filter className="w-4 h-4 mr-2" />
                <SelectValue placeholder="Select category" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Categories</SelectItem>
                {allCategories.map(category => (
                  <SelectItem key={category} value={category}>{category}</SelectItem>
                ))}
              </SelectContent>
            </Select>
            <Select value={reportType} onValueChange={(value: "summary" | "detailed") => setReportType(value)}>
              <SelectTrigger className="w-32 bg-white/70 border-white/90 text-gray-900 font-medium">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="summary">Summary</SelectItem>
                <SelectItem value="detailed">Detailed</SelectItem>
              </SelectContent>
            </Select>
            <Button
              variant="outline"
              size="sm"
              onClick={printReport}
              className="header-button-visible"
            >
              <Printer className="w-4 h-4 mr-2" />
              Print Report
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={generateReport}
              className="header-button-visible"
            >
              <Download className="w-4 h-4 mr-2" />
              Export Report
            </Button>
          </div>
        </div>
      </header>

      <div className="container mx-auto p-6">
        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-red-600">
                ${grandTotals.totalCost.toLocaleString()}
              </div>
              <div className="text-sm text-muted-foreground">Total Inventory Cost</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-green-600">
                ${grandTotals.totalValue.toLocaleString()}
              </div>
              <div className="text-sm text-muted-foreground">Total Inventory Value</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-blue-600">
                ${(grandTotals.totalValue - grandTotals.totalCost).toLocaleString()}
              </div>
              <div className="text-sm text-muted-foreground">Potential Margin</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-purple-600">
                {(((grandTotals.totalValue - grandTotals.totalCost) / grandTotals.totalCost) * 100).toFixed(1)}%
              </div>
              <div className="text-sm text-muted-foreground">Average Margin</div>
            </CardContent>
          </Card>
        </div>

        {/* Category Filter Info */}
        {selectedCategory !== "all" && (
          <Card className="mb-6 border-blue-200 bg-blue-50">
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <Filter className="w-5 h-5 text-blue-600" />
                  <div>
                    <div className="font-semibold text-blue-900">Filtered Report: {selectedCategory}</div>
                    <div className="text-sm text-blue-700">Showing data for {selectedCategory} category only</div>
                  </div>
                </div>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setSelectedCategory("all")}
                  className="text-blue-600 border-blue-300 hover:bg-blue-100"
                >
                  <RefreshCw className="w-4 h-4 mr-2" />
                  Show All Categories
                </Button>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Category Breakdown */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <BarChart3 className="w-5 h-5" />
              Cost Analysis {selectedCategory === "all" ? "by Category" : `- ${selectedCategory}`}
            </CardTitle>
          </CardHeader>
          <CardContent>
            {reportType === "detailed" && selectedCategory !== "all" && (
              <div className="mb-6">
                <h4 className="font-semibold mb-3 flex items-center gap-2">
                  <Package className="w-4 h-4" />
                  Individual Items in {selectedCategory}
                </h4>
                <div className="space-y-2 max-h-96 overflow-y-auto">
                  {filteredItems
                    .filter(item => item.category === selectedCategory)
                    .sort((a, b) => (b.price * b.stock) - (a.price * a.stock))
                    .map(item => {
                      const itemValue = item.price * item.stock;
                      const itemCost = item.cost * item.stock;
                      const itemMargin = ((itemValue - itemCost) / itemCost * 100);

                      return (
                        <div key={item.id} className="flex items-center justify-between p-3 border rounded bg-gray-50">
                          <div className="flex-1">
                            <div className="font-medium">{item.name}</div>
                            <div className="text-sm text-gray-600">
                              {item.room} • {item.stock} units • ${item.price}/unit
                            </div>
                          </div>
                          <div className="text-right">
                            <div className="font-bold text-green-600">${itemValue.toLocaleString()}</div>
                            <div className="text-sm text-gray-600">{itemMargin.toFixed(1)}% margin</div>
                          </div>
                        </div>
                      );
                    })}
                </div>
                <Separator className="my-4" />
              </div>
            )}
            <div className="space-y-4">
              {Object.entries(categoryTotals)
                .sort(([,a], [,b]) => b.totalCost - a.totalCost)
                .map(([category, data]) => {
                  const margin = data.totalValue - data.totalCost;
                  const marginPercentage = ((margin / data.totalCost) * 100);
                  
                  return (
                    <div key={category} className="border rounded-lg p-4">
                      <div className="flex items-center justify-between mb-3">
                        <div className="flex items-center gap-3">
                          <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <Package className="w-6 h-6 text-blue-600" />
                          </div>
                          <div>
                            <h3 className="font-semibold text-lg">{category}</h3>
                            <div className="text-sm text-gray-600">
                              {data.itemCount} items • {data.stockCount.toLocaleString()} units
                            </div>
                          </div>
                        </div>
                        <div className="text-right">
                          <Badge 
                            variant={marginPercentage > 50 ? "default" : marginPercentage > 25 ? "secondary" : "outline"}
                            className="mb-1"
                          >
                            {marginPercentage.toFixed(1)}% margin
                          </Badge>
                        </div>
                      </div>
                      
                      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                          <span className="text-gray-600 font-semibold">Total Cost:</span>
                          <div className="font-bold text-red-600 text-lg">${data.totalCost.toLocaleString()}</div>
                        </div>
                        <div>
                          <span className="text-gray-600 font-semibold">Total Value:</span>
                          <div className="font-bold text-green-600 text-lg">${data.totalValue.toLocaleString()}</div>
                        </div>
                        <div>
                          <span className="text-gray-600 font-semibold">Potential Margin:</span>
                          <div className="font-bold text-blue-600 text-lg">${margin.toLocaleString()}</div>
                        </div>
                        <div>
                          <span className="text-gray-600 font-semibold">Avg Unit Cost:</span>
                          <div className="font-bold text-gray-900">${(data.totalCost / data.stockCount).toFixed(2)}</div>
                        </div>
                      </div>
                      
                      {/* Visual margin bar */}
                      <div className="mt-3">
                        <div className="flex justify-between text-xs text-gray-500 mb-1">
                          <span>Cost vs Value</span>
                          <span>{marginPercentage.toFixed(1)}% margin</span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-2">
                          <div 
                            className="bg-gradient-to-r from-red-500 via-yellow-500 to-green-500 h-2 rounded-full"
                            style={{ width: `${Math.min(marginPercentage, 100)}%` }}
                          ></div>
                        </div>
                      </div>
                    </div>
                  );
                })}
            </div>
          </CardContent>
        </Card>

        {/* Grand Total Summary */}
        <Card className="mt-6">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5" />
              Total Inventory Summary
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="bg-gray-50 rounded-lg p-6">
              <div className="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                  <div className="text-2xl font-bold text-red-600">${grandTotals.totalCost.toLocaleString()}</div>
                  <div className="text-sm text-gray-600">Total Investment</div>
                </div>
                <div>
                  <div className="text-2xl font-bold text-green-600">${grandTotals.totalValue.toLocaleString()}</div>
                  <div className="text-sm text-gray-600">Potential Revenue</div>
                </div>
                <div>
                  <div className="text-2xl font-bold text-blue-600">${(grandTotals.totalValue - grandTotals.totalCost).toLocaleString()}</div>
                  <div className="text-sm text-gray-600">Potential Profit</div>
                </div>
                <div>
                  <div className="text-2xl font-bold text-purple-600">{grandTotals.stockCount.toLocaleString()}</div>
                  <div className="text-sm text-gray-600">Total Units</div>
                </div>
              </div>
              
              <Separator className="my-4" />
              
              <div className="text-center">
                <div className="text-lg font-medium text-gray-700 mb-2">
                  Average Margin: {(((grandTotals.totalValue - grandTotals.totalCost) / grandTotals.totalCost) * 100).toFixed(1)}%
                </div>
                <div className="text-sm text-gray-500">
                  Report generated on {new Date().toLocaleDateString()} at {new Date().toLocaleTimeString()}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
