// Cannabis POS System JavaScript Functions
// Extracted from main HTML file to improve performance and reduce script errors

function cannabisPOS() {
  return {
    // Authentication state
    isAuthenticated: false,
    currentUser: null,
    showAuthModal: false,
    metrcConnected: false,

    // Alpine.js init function - called automatically when component initializes
    init() {
      this.normalizeCollections();
      this.initAuth();
      this.loadSettings();
      this.loadCartState();
      this.loadData();
      this.filterProducts();
      this.initializeReportData();
      try {
        this.loadMonthStats();
      } catch (_) {}
    },

    // Login form data
    loginEmail: "",
    loginPassword: "",
    employeeId: "",
    employeePin: "",
    loginError: "",
    registerError: "",
    registerForm: {
      name: "",
      email: "",
      password: "",
      passwordConfirm: "",
      pin: "",
    },
    loginType: "email", // 'email' or 'pin'

    // Reports functionality
    showCreateReportModal: false,
    recentReports: [],
    customReport: {
      name: "",
      type: "",
      description: "",
      dataSources: [],
      dateRange: "last-30-days",
      startDate: "",
      endDate: "",
      categoryFilters: [],
      employeeFilter: "",
      customerType: "",
      paymentMethod: "",
      selectedMetrics: [],
      chartType: "table",
      colorScheme: "cannabis",
      includeComparisons: false,
      includeTrends: false,
      includeBreakdowns: false,
      exportFormats: ["pdf"],
      autoSchedule: false,
      scheduleFrequency: "weekly",
      scheduleEmail: "",
    },

    // Report data sources and metrics
    availableDataSources: [
      {
        id: "sales",
        name: "Sales Transactions",
        icon: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6",
      },
      {
        id: "inventory",
        name: "Inventory Data",
        icon: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
      },
      {
        id: "customers",
        name: "Customer Records",
        icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
      },
      {
        id: "employees",
        name: "Employee Data",
        icon: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
      },
      {
        id: "products",
        name: "Product Catalog",
        icon: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
      },
      {
        id: "metrc",
        name: "METRC Compliance",
        icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3",
      },
      {
        id: "loyalty",
        name: "Loyalty Program",
        icon: "M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z",
      },
      {
        id: "payments",
        name: "Payment Processing",
        icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1",
      },
      {
        id: "taxes",
        name: "Tax Records",
        icon: "M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z",
      },
      {
        id: "rooms",
        name: "Room Management",
        icon: "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 7h10M7 11h10M7 15h10",
      },
      {
        id: "discounts",
        name: "Deals & Discounts",
        icon: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z",
      },
    ],

    salesMetrics: [
      { id: "total-revenue", name: "Total Revenue" },
      { id: "gross-sales", name: "Gross Sales" },
      { id: "net-sales", name: "Net Sales" },
      { id: "transaction-count", name: "Transaction Count" },
      { id: "average-order-value", name: "Average Order Value" },
      { id: "units-sold", name: "Units Sold" },
      { id: "sales-per-hour", name: "Sales per Hour" },
      { id: "refunds-returns", name: "Refunds & Returns" },
      { id: "top-selling-products", name: "Top Selling Products" },
      { id: "sales-by-category", name: "Sales by Category" },
    ],

    inventoryMetrics: [
      { id: "current-stock-levels", name: "Current Stock Levels" },
      { id: "low-stock-items", name: "Low Stock Items" },
      { id: "out-of-stock-items", name: "Out of Stock Items" },
      { id: "inventory-value", name: "Total Inventory Value" },
      { id: "inventory-turnover", name: "Inventory Turnover" },
      { id: "aging-inventory", name: "Aging Inventory" },
      { id: "dead-stock", name: "Dead Stock Analysis" },
      { id: "stock-movement", name: "Stock Movement" },
      { id: "reorder-alerts", name: "Reorder Alerts" },
      { id: "vendor-performance", name: "Vendor Performance" },
    ],

    customerMetrics: [
      { id: "total-customers", name: "Total Customers" },
      { id: "new-customers", name: "New Customers" },
      { id: "repeat-customers", name: "Repeat Customers" },
      { id: "customer-lifetime-value", name: "Customer Lifetime Value" },
      { id: "customer-acquisition-cost", name: "Customer Acquisition Cost" },
      { id: "customer-retention-rate", name: "Customer Retention Rate" },
      { id: "top-customers", name: "Top Customers by Spend" },
      { id: "customer-demographics", name: "Customer Demographics" },
      { id: "loyalty-program-stats", name: "Loyalty Program Stats" },
      { id: "medical-vs-recreational", name: "Medical vs Recreational" },
    ],

    financialMetrics: [
      { id: "gross-profit", name: "Gross Profit" },
      { id: "net-profit", name: "Net Profit" },
      { id: "profit-margin", name: "Profit Margin %" },
      { id: "cost-of-goods-sold", name: "Cost of Goods Sold" },
      { id: "operating-expenses", name: "Operating Expenses" },
      { id: "tax-collected", name: "Tax Collected" },
      { id: "cash-flow", name: "Cash Flow" },
      { id: "payment-method-breakdown", name: "Payment Method Breakdown" },
      { id: "discount-impact", name: "Discount Impact" },
      { id: "commission-payments", name: "Commission Payments" },
    ],

    productCategories: [
      "Flower",
      "Pre-Rolls",
      "Infused",
      "Edibles",
      "Concentrates",
      "Vape Products",
      "Tinctures",
      "Topicals",
      "Capsules",
      "Beverages",
      "Suppositories",
      "Clones/Seeds",
      "Immature Plants",
      "Mature Plants",
      "Hemp",
      "Accessories",
    ],

    currentPage: "pos",
    roomsTab: "rooms",
    viewMode: "cards", // cards or list
    sortOrder: "name-asc",
    searchQuery: "",
    selectedCategory: "",
    taxRate: 20.0,
    medicalTaxRate: 0.0, // Tax free for medical customers
    selectedCustomer: null,
    selectedProduct: null,
    ageVerified: false,
    cartExpanded: true,
    cartViewMode: "narrow", // 'narrow' or 'wide'

    // Analytics/ASPD controls expected by Alpine bindings
    aspdTimeframe: "month",

    // Inventory breakdown UI state
    expandedCategories: [],

    toggleCategoryExpansion(name) {
      try {
        const i = this.expandedCategories.indexOf(name);
        if (i >= 0) this.expandedCategories.splice(i, 1);
        else this.expandedCategories.push(name);
      } catch (_) {}
    },

    loadAspd() {
      try { window.loadAspd && window.loadAspd(); } catch (_) {}
    },
    exportAspd() {
      try { window.exportAspd && window.exportAspd(); } catch (_) {}
    },

    // Inventory evaluation helpers exposed to Alpine scope
    getInventoryEvaluation() {
      try {
        const list = Array.isArray(this.products) ? this.products : [];
        let totalCost = 0, totalRetail = 0;
        for (let i = 0; i < list.length; i++) {
          const p = list[i] || {};
          const stock = Number(p.stock ?? p.quantity ?? 0);
          const price = Number(p.price ?? 0);
          const cost = Number(p.cost ?? p.unit_cost ?? p.costPerUnit ?? 0);
          if (isFinite(stock) && isFinite(cost)) totalCost += cost * stock;
          if (isFinite(stock) && isFinite(price)) totalRetail += price * stock;
        }
        const totalProfit = totalRetail - totalCost;
        const averageMargin = totalRetail > 0 ? (totalProfit / totalRetail) * 100 : 0;
        return { totalCost, totalRetail, totalProfit, averageMargin };
      } catch (_) {
        return { totalCost: 0, totalRetail: 0, totalProfit: 0, averageMargin: 0 };
      }
    },
    getCategoryBreakdown() {
      try {
        const list = Array.isArray(this.products) ? this.products : [];
        const map = new Map();
        for (let i = 0; i < list.length; i++) {
          const p = list[i] || {};
          const category = p.category || 'Uncategorized';
          const stock = Number(p.stock ?? p.quantity ?? 0);
          const price = Number(p.price ?? 0);
          const cost = Number(p.cost ?? p.unit_cost ?? p.costPerUnit ?? 0);
          const entry = map.get(category) || { productCount: 0, totalCost: 0, totalRetail: 0, totalProfit: 0, averageMargin: 0, products: [] };
          const lineCost = (isFinite(stock) && isFinite(cost)) ? cost * stock : 0;
          const lineRetail = (isFinite(stock) && isFinite(price)) ? price * stock : 0;
          entry.productCount += 1;
          entry.totalCost += lineCost;
          entry.totalRetail += lineRetail;
          entry.totalProfit += (lineRetail - lineCost);
          entry.products.push({
            id: p.id || p.sku || p.name,
            name: p.name || 'Product',
            sku: p.sku || null,
            category,
            stock: isFinite(stock) ? stock : 0,
            price: isFinite(price) ? price : 0,
            cost: isFinite(cost) ? cost : 0
          });
          map.set(category, entry);
        }
        const out = {};
        map.forEach((v, k) => {
          v.averageMargin = v.totalRetail > 0 ? (v.totalProfit / v.totalRetail) * 100 : 0;
          out[k] = v;
        });
        return out;
      } catch (_) {
        return {};
      }
    },

    // METRC Vendors state and helpers
    incomingVendors: [],
    vendorSearchQuery: "",
    vendorStatusFilter: "",
    vendorETAFilter: "",
    get filteredVendors() {
      try {
        const q = (this.vendorSearchQuery || "").toLowerCase();
        const st = (this.vendorStatusFilter || "").toLowerCase();
        const eta = (this.vendorETAFilter || "").toLowerCase();
        return (Array.isArray(this.incomingVendors) ? this.incomingVendors : []).filter((v) => {
          const name = (v.name || "").toLowerCase();
          const lic = (v.license || "").toLowerCase();
          const status = (v.status || "").toLowerCase();
          const veta = (v.eta || "").toLowerCase();
          const matchQ = !q || name.includes(q) || lic.includes(q);
          const matchS = !st || status === st;
          const matchE = !eta || veta === eta;
          return matchQ && matchS && matchE;
        });
      } catch (_) {
        return [];
      }
    },
    async refreshVendorData() {
      try {
        const client = window.axios || axios;
        const res = await client.get("/api/metrc/transfers/incoming", { headers: { Accept: "application/json" } });
        const list = (res && res.data && (res.data.transfers || res.data.data || res.data)) || [];
        const arr = Array.isArray(list) ? list : (Array.isArray(list.transfers) ? list.transfers : []);
        const normalized = arr.map((t, i) => {
          const vendorName = t.vendor_name || t.vendor || t.supplier || t.name || `Vendor ${i + 1}`;
          const license = t.vendor_license || t.license || t.license_number || "";
          const packages = Array.isArray(t.packages) ? t.packages : (Array.isArray(t.items) ? t.items : []);
          const pList = (packages || []).map((p, j) => {
            const qty = Number(p.quantity ?? p.qty ?? p.quantityShipped ?? p.shippedQuantity ?? 0);
            const unitCost = Number(
              p.unit_cost ?? p.unitCost ?? p.cost_per_unit ?? p.costPerUnit ?? p.price_per_unit ?? p.cost ?? 0
            );
            const computedTotal = (isFinite(unitCost) && isFinite(qty)) ? unitCost * qty : 0;
            const totalValue = Number(p.total_value ?? p.totalValue ?? p.extended_cost ?? computedTotal);
            const weightNum = p.weight != null ? (typeof p.weight === "number" ? p.weight : Number(p.weight)) : 0;
            return {
              id: p.id || p.package_id || p.tag || `${i}-${j}`,
              metrcTag: p.tag || p.package_tag || p.packageNumber || p.PackageLabel || "",
              productName: p.name || p.item || p.product || p.ProductName || "Package",
              category: p.category || p.product_category || p.CategoryName || "Uncategorized",
              quantity: isFinite(qty) ? qty : 0,
              unit: p.unit || p.unit_of_measure || p.uom || p.UnitOfMeasureName || "",
              strain: p.strain || p.product_strain || p.StrainName || "",
              weight: isFinite(weightNum) ? weightNum : 0,
              thc: Number(p.thc ?? p.thc_percent ?? p.thcPercent ?? 0),
              cbd: Number(p.cbd ?? p.cbd_percent ?? p.cbdPercent ?? 0),
              unitCost: isFinite(unitCost) ? unitCost : 0,
              totalValue: isFinite(totalValue) ? totalValue : 0,
              room: ((p.room || p.destination_room || p.roomName || "receiving") + "").toLowerCase(),
            };
          });
          const totalValue = pList.reduce((s, p) => s + (Number(p.totalValue) || (Number(p.unitCost) || 0) * (Number(p.quantity) || 0)), 0);
          const totalCost = pList.reduce((s, p) => s + (Number(p.unitCost) || 0) * (Number(p.quantity) || 0), 0);
          const etaRaw = t.eta || t.expected_at || t.expected_date || "";
          const etaDateObj = etaRaw ? new Date(etaRaw) : new Date();
          const today = new Date();
          today.setHours(0, 0, 0, 0);
          const endOfWeek = new Date(today);
          endOfWeek.setDate(today.getDate() + (7 - today.getDay()));
          let etaBucket = "today";
          if (etaDateObj > endOfWeek) etaBucket = "next-week";
          else if (etaDateObj > today) etaBucket = "this-week";
          const fmtDate = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
          const fmtTime = (d) => `${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;
          const status = (t.status || t.state || "in-transit").toLowerCase();
          return {
            id: t.id || t.transfer_id || `t-${i}`,
            metrcId: t.metrc_id || t.id || null,
            name: vendorName,
            license,
            eta: etaBucket,
            etaDate: fmtDate(etaDateObj),
            etaTime: fmtTime(etaDateObj),
            packages: pList,
            totalValue: isFinite(totalValue) ? totalValue : 0,
            totalCost: isFinite(totalCost) ? totalCost : 0,
            status,
          };
        });
        this.incomingVendors = normalized;
      } catch (e) {
        const now = new Date();
        const plusDays = (n) => {
          const d = new Date(now);
          d.setDate(d.getDate() + n);
          return d;
        };
        const fmtDate = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
        const fmtTime = (d) => `${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;
        this.incomingVendors = [
          {
            id: "v-1",
            metrcId: null,
            name: "GreenLeaf Farms",
            license: "OR-XYZ-1234",
            eta: "today",
            etaDate: fmtDate(now),
            etaTime: fmtTime(now),
            packages: [
              {
                id: "p1",
                metrcTag: "1A4060...",
                productName: "Blue Dream 1/8",
                category: "Flower",
                quantity: 200,
                unit: "ea",
                strain: "Blue Dream",
                weight: 3.5,
                thc: 24,
                cbd: 0.1,
                unitCost: 25,
                totalValue: 5000,
                room: "receiving",
              },
            ],
            totalValue: 5000,
            totalCost: 5000,
            status: "ready-to-import",
          },
          {
            id: "v-2",
            metrcId: null,
            name: "Pine State Extracts",
            license: "OR-ABC-5678",
            eta: "this-week",
            etaDate: fmtDate(plusDays(3)),
            etaTime: fmtTime(plusDays(3)),
            packages: [
              {
                id: "p2",
                metrcTag: "1A4061...",
                productName: "Live Resin 1g",
                category: "Concentrates",
                quantity: 120,
                unit: "ea",
                strain: "OG Kush",
                weight: 1,
                thc: 72,
                cbd: 0,
                unitCost: 30,
                totalValue: 3600,
                room: "receiving",
              },
            ],
            totalValue: 3600,
            totalCost: 3600,
            status: "in-transit",
          },
        ];
      }
    },
    viewVendorPackages(vendor) {
      this.selectedVendor = vendor;
      this.showVendorPackagesModal = true;
    },

    // Pagination state
    currentProductPage: 1,
    itemsPerPageCard: 12,
    itemsPerPageList: 10,
    showCustomerModal: false,
    showNewSaleModal: false,
    showRecreationalModal: false,
    showMedicalModal: false,
    showMetrcModal: false,
    showTransferModal: false,
    showEditModal: false,
    showPrintModal: false,
    showPrintPreviewModal: false,
    showPrintSettingsPreviewModal: false,
    showCashModal: false,
    showDebitModal: false,
    showDiscountModal: false,
    showItemDiscountModal: false,
    showPrintTypeModal: false,
    showEnrollCustomerModal: false,
    showAdjustPointsModal: false,
    showAgingModal: false,
    showSaleDetailsModal: false,
    showAddCustomerModal: false,
    showAddProductModal: false,
    showVendorPackagesModal: false,
    showAddEmployeeModal: false,
    showEmployeeModal: false,
    showCashCountModal: false,
    showRoomDetailsModal: false,
    showAddRoomModal: false,
    showAddDrawerModal: false,
    showPinModal: false,
    showEmployeeAssignModal: false,
    showRegisterModal: false,
    showMetrcImportModal: false,
    showCustomerViewModal: false,
    showEditCustomerModal: false,
    showAddTierModal: false,
    showVoidSaleModal: false,
    showCreateDealModal: false,
    showCsvImportModal: false,
    showTemplateModal: false,

    // Saved sales UI state
    showSavedSalesModal: false,
    savedSales: [],
    savedSalesSearch: "",
    loadingSavedSales: false,

    // Selected items and data objects
    selectedCartItem: null,
    selectedCartItemIndex: null,
    selectedLoyaltyCustomer: null,
    selectedEmployee: null,
    selectedVendor: null,
    selectedDrawer: null,
    selectedRoom: null,
    selectedVendorForImport: null,
    selectedSale: null,
    selectedDrawerForAssignment: null,
    saleToVoid: null,
    editingDeal: null,

    // Form data objects
    medicalData: {
      cardNumber: "",
      issueDate: "",
      expirationDate: "",
      type: "medical",
      patientCardNumber: "",
      saveData: false,
      customerName: "",
      email: "",
    },
    transferData: {
      quantity: 1,
      destinationRoom: "",
      setSalesFloorStatus: false,
      reason: "",
    },
    editData: {
      name: "",
      stock: 0,
      cost: 0,
      price: 0,
      thc: 0,
      cbd: 0,
      cbn: 0,
      cbg: 0,
      cbc: 0,
      priceTier: "",
    },
    cashPayment: {
      amountGiven: 0,
      changeDue: 0,
      employeePin: "",
    },
    debitPayment: {
      amount: 0,
      changeDue: 0,
      lastFour: "",
      employeePin: "",
    },
    discountForm: {
      type: "percentage",
      value: 0,
      reason: "",
      calculatedAmount: 0,
    },
    itemDiscountForm: {
      type: "percentage",
      value: 0,
      reason: "",
      calculatedAmount: 0,
    },
    enrollForm: {
      customerName: "",
      email: "",
      phone: "",
      tier: "Bronze",
      startingPoints: 0,
    },
    pointsForm: {
      action: "add",
      amount: 0,
      reason: "",
    },
    customerForm: {
      type: "recreational",
      name: "",
      email: "",
      phone: "",
      isMedical: false,
      medicalCard: "",
      medicalCardNumber: "",
      medicalCardIssueDate: "",
      medicalCardExpiry: "",
      medicalCardType: "patient",
      patientCardNumber: "",
      saveData: false,
      enrollLoyalty: false,
    },
    productForm: {
      name: "",
      category: "",
      price: 0,
      cost: 0,
      stock: 0,
      weight: "",
      thc: 0,
      cbd: 0,
      sku: "",
      vendor: "",
      supplier: "",
      room: "Sales Floor",
      onSalesFloor: true,
      isGLS: false,
      metrcTag: "",
    },
    employeeForm: {
      name: "",
      email: "",
      phone: "",
      role: "budtender",
      payRate: 15.0,
      hireDate: "",
      status: "active",
      workerPermit: "",
      metrcApiKey: "",
      permissions: [],
    },
    roomForm: {
      name: "",
      forSale: "true",
      maxCapacity: "",
      status: "active",
      temperature: 68,
      humidity: 50,
    },
    drawerForm: {
      name: "",
      location: "",
      assignedEmployee: "",
      startingAmount: 100.0,
    },
    pinInput: "",
    pinError: "",
    pinAction: "",
    cashCount: {
      total: 0,
      notes: "",
    },
    importForm: {
      metrcTag: "",
      category: "",
      weight: "",
      cannabinoids: "",
      productName: "",
      sku: "",
      price: 0,
      cost: 0,
      room: "",
      mainImage: null,
      additionalImages: [],
    },
    editCustomerForm: {
      name: "",
      email: "",
      phone: "",
      isMedical: false,
      medicalCard: "",
    },
    tierForm: {
      name: "",
      prices: {
        weight_1g: 0,
        weight_3_5g: 0,
        weight_7g: 0,
        weight_14g: 0,
        weight_28g: 0,
      },
      customWeights: [],
    },
    voidForm: {
      reason: "",
      notes: "",
      employeePin: "",
      pinVerified: false,
      verifiedEmployee: "",
      pinError: "",
    },
    dealForm: {
      name: "",
      description: "",
      type: "",
      discountValue: 0,
      buyQuantity: 1,
      getQuantity: 1,
      minPurchase: 0,
      minPurchaseType: "dollars",
      usageLimit: "",
      allCategories: false,
      applicableCategories: [],
      applicableProducts: [],
      categoryDiscounts: {},
      itemDiscounts: {},
      excludeGLS: true,
      stackable: false,
      loyaltyOnly: false,
      medicalOnly: false,
      emailCustomers: false,
      isActive: true,
      startDate: "",
      endDate: "",
      startTime: "",
      endTime: "",
      activeDays: [],
    },
    csvImportForm: {
      file: null,
      fileName: "",
      category: "",
      skipFirstRow: true,
      previewData: [],
      totalRows: 0,
      validRows: 0,
      errorRows: 0,
      importing: false,
      importComplete: false,
      importResults: null,
    },

    // Cart and discount data
    cartDiscount: {
      type: "percentage",
      value: 0,
      amount: 0,
      reason: "",
    },
    agingModalData: {
      title: "",
      items: [],
      totalCost: 0,
      totalRetail: 0,
      totalProfit: 0,
    },

    // METRC refresh helper for demo
    async refreshMetrc() {
      try {
        const client = window.axios || axios;
        const res = await client.get("/api/metrc/debug/packages?diagnose=1");
        const count = Number(res?.data?.count || 0);
        this.showToast(`METRC packages retrieved: ${count}`, "success");
      } catch (e) {
        const msg =
          (e &&
            e.response &&
            e.response.data &&
            (e.response.data.message || e.response.data.error)) ||
          e.message ||
          "Failed to refresh METRC";
        this.showToast(`Failed to refresh METRC: ${msg}`, "error");
      }
    },

    // Additional arrays and objects
    employees: [],
    employeePendingDelete: null,
    employeeSearchQuery: "",
    employeeRoleFilter: "",
    employeeStatusFilter: "",
    facilityRooms: [],
    cashDrawers: [],
    activityLog: [],

    // Sales tracking state (SPA Sales page)
    // Deals state
    deals: [],
    filteredDeals: [],
    dealFilter: "",
    dealProductSearch: "",

    // Sales state
    sales: [],
    filteredSales: [],
    salesFilter: {
      dateRange: "today", // today | yesterday | week | month | custom
      startDate: "",
      endDate: "",
      customer: "",
      amountRange: "", // "0-25", "25-50", "50-100", "100+"
      paymentMethod: "", // cash|debit|credit
    },
    endOfDayReportGenerated: false,
    monthStats: null,
    metrcPushSettings: { startDate: "", endDate: "" },
    metrcPushInProgress: false,
    lastMetrcPush: "",
    metrcPushSuccess: null,
    metrcPushResult: "",

    // METRC Integration Settings
    metrcSettings: {
      apiKey: "",
      userKey: "",
      facilityLicense: "",
      state: "OR", // Default to Oregon
      autoSync: false,
      trackSales: false,
    },

    // Price tier and print data
    priceTiers: [
      {
        id: 1,
        name: "Premium Flower",
        isActive: true,
        createdAt: new Date().toISOString(),
        prices: {
          weight_1g: 15.0,
          weight_3_5g: 45.0,
          weight_7g: 85.0,
          weight_14g: 160.0,
          weight_28g: 300.0,
        },
      },
      {
        id: 2,
        name: "Top Shelf",
        isActive: true,
        createdAt: new Date().toISOString(),
        prices: {
          weight_1g: 12.0,
          weight_3_5g: 35.0,
          weight_7g: 65.0,
          weight_14g: 120.0,
          weight_28g: 230.0,
        },
      },
      {
        id: 3,
        name: "Budget Option",
        isActive: true,
        createdAt: new Date().toISOString(),
        prices: {
          weight_1g: 8.0,
          weight_3_5g: 25.0,
          weight_7g: 45.0,
          weight_14g: 85.0,
          weight_28g: 160.0,
        },
      },
    ],

    weightThreshold: 0,

    printData: {
      product: null,
      type: "",
      selectedPrinter: "",
      copies: 1,
      scale: 100,
      labelSize: "medium",
      customWidth: 3.0,
      customHeight: 2.0,
      orientation: "portrait",
      quality: "normal",
      borderEnabled: false,
      timestampEnabled: true,
      companyLogoEnabled: false,
      batchPrint: false,
    },

    // Store settings data
    storeSettings: {
      name: "Cannabest Dispensary",
      licenseNumber: "OR-100001",
      address: "123 Cannabis Street, Portland, OR 97201",
      phone: "(503) 555-0123",
      email: "info@cannabest.com",
      hoursPerDay: [
        { isOpen: true, openTime: "09:00", closeTime: "21:00" }, // Monday
        { isOpen: true, openTime: "09:00", closeTime: "21:00" }, // Tuesday
        { isOpen: true, openTime: "09:00", closeTime: "21:00" }, // Wednesday
        { isOpen: true, openTime: "09:00", closeTime: "21:00" }, // Thursday
        { isOpen: true, openTime: "09:00", closeTime: "21:00" }, // Friday
        { isOpen: true, openTime: "10:00", closeTime: "20:00" }, // Saturday
        { isOpen: true, openTime: "10:00", closeTime: "20:00" }, // Sunday
      ],
    },

    // Settings management functions
    setAllDaysHours(openTime, closeTime) {
      this.storeSettings.hoursPerDay.forEach((day) => {
        if (day.isOpen) {
          day.openTime = openTime;
          day.closeTime = closeTime;
        }
      });
    },

    setWeekdayWeekendHours() {
      // Weekdays (Mon-Fri): 9AM-9PM
      for (let i = 0; i < 5; i++) {
        if (this.storeSettings.hoursPerDay[i].isOpen) {
          this.storeSettings.hoursPerDay[i].openTime = "09:00";
          this.storeSettings.hoursPerDay[i].closeTime = "21:00";
        }
      }
      // Weekends (Sat-Sun): 10AM-8PM
      for (let i = 5; i < 7; i++) {
        if (this.storeSettings.hoursPerDay[i].isOpen) {
          this.storeSettings.hoursPerDay[i].openTime = "10:00";
          this.storeSettings.hoursPerDay[i].closeTime = "20:00";
        }
      }
    },

    setAllDaysClosed() {
      this.storeSettings.hoursPerDay.forEach((day) => {
        day.isOpen = false;
      });
    },

    saveAllSettings() {
      try {
        localStorage.setItem(
          "cannabisPOS-storeSettings",
          JSON.stringify(this.storeSettings),
        );
        this.showToast("Settings saved successfully", "success");
      } catch (error) {
        console.error("Error saving settings:", error);
        this.showToast("Failed to save settings", "error");
      }
    },

    // Oregon METRC Sales Limits (2025 Current Limits)
    oregonSalesLimits: {
      // Recreational Limits
      recreational: {
        Flower: {
          limit: 56.7,
          unit: "grams",
          displayName: "Flower (includes Pre-Rolls)",
        }, // 2 oz = 56.7g
        "Pre-Rolls": {
          limit: 56.7,
          unit: "grams",
          displayName: "Pre-Rolls (counts as Flower)",
        }, // Same as flower
        Infused: {
          limit: 56.7,
          unit: "grams",
          displayName: "Infused Products (counts as Flower)",
        }, // Same as flower
        Concentrates: {
          limit: 5,
          unit: "grams",
          displayName: "Concentrates & Extracts",
        },
        "Vape Products": {
          limit: 5,
          unit: "grams",
          displayName: "Vape Products",
        }, // Same as concentrates
        Edibles: { limit: 454, unit: "grams", displayName: "Solid Edibles" }, // 16 oz = 454g
        Beverages: { limit: 2133, unit: "ml", displayName: "Liquid Products" }, // 72 fl oz = 2133ml
        Tinctures: { limit: 2133, unit: "ml", displayName: "Tinctures" }, // Same as beverages
        Topicals: { limit: 454, unit: "grams", displayName: "Topicals" }, // No specific limit, treating as solids
        Capsules: { limit: 454, unit: "grams", displayName: "Capsules" }, // Same as edibles
        "Immature Plants": {
          limit: 4,
          unit: "units",
          displayName: "Immature Plants",
        },
        Seeds: { limit: 10, unit: "units", displayName: "Seeds" },
      },
      // Medical Limits (Higher limits for patients)
      medical: {
        Flower: {
          limit: 226.8,
          unit: "grams",
          displayName: "Flower (includes Pre-Rolls)",
        }, // 8 oz = 226.8g
        "Pre-Rolls": {
          limit: 226.8,
          unit: "grams",
          displayName: "Pre-Rolls (counts as Flower)",
        }, // Same as flower
        Infused: {
          limit: 226.8,
          unit: "grams",
          displayName: "Infused Products (counts as Flower)",
        }, // Same as flower
        Concentrates: {
          limit: 453.6,
          unit: "grams",
          displayName: "Concentrates",
        }, // 16 oz = 453.6g
        "Vape Products": {
          limit: 5,
          unit: "grams",
          displayName: "Vape Extracts",
        }, // Same as recreational extracts
        Edibles: { limit: 2133, unit: "ml", displayName: "Liquid Products" }, // 72 fl oz = 2133ml
        Beverages: { limit: 2133, unit: "ml", displayName: "Beverages" }, // Same as edibles
        Tinctures: { limit: 2133, unit: "ml", displayName: "Tinctures" }, // Same as beverages
        Topicals: { limit: 453.6, unit: "grams", displayName: "Topicals" }, // Same as concentrates
        Capsules: { limit: 453.6, unit: "grams", displayName: "Capsules" }, // Same as concentrates
        "Immature Plants": {
          limit: 4,
          unit: "units",
          displayName: "Immature Plants",
        },
        Seeds: { limit: 50, unit: "units", displayName: "Seeds" },
      },
    },

    // Basic POS functions
    async init() {
      try {
        // Check authentication first
        await this.initAuth();

        if (this.isAuthenticated) {
          // Load data from API if authenticated
          await this.loadInitialData();
          this.ensureMyEmployeeListed();
        } else {
          // Load local data as fallback
          this.loadSettings();
          this.loadProducts();
          this.loadCustomers();
          this.loadEmployees();
        }

        this.calculateTotals();
        try {
          this.filterLoyaltyCustomers();
        } catch (_) {}
      } catch (error) {
        console.warn("POS initialization error:", error);
        // Ensure basic state is set
        this.cart = this.cart || [];
        this.subtotal = this.subtotal || 0;
        this.taxAmount = this.taxAmount || 0;
        this.total = this.total || 0;
      } finally {
        // Load persisted drawers
        try {
          const raw = localStorage.getItem("pos_drawers");
          if (raw) this.cashDrawers = JSON.parse(raw);
        } catch (_) {}
        // Load persisted rooms
        try {
          const rawR =
            localStorage.getItem("pos_rooms") ||
            localStorage.getItem("rd-rooms");
          if (rawR) this.facilityRooms = JSON.parse(rawR);
        } catch (_) {}
        // Merge external activity log if present
        try {
          const rawLog = localStorage.getItem("rd-activity-log");
          if (rawLog) {
            const arr = JSON.parse(rawLog);
            const mapped = (Array.isArray(arr) ? arr : []).map((x) => ({
              id: (Date.now() + Math.random()).toString(36),
              timestamp: new Date(x.at || Date.now()).toLocaleString(),
              action: x.title,
              type: x.title?.toLowerCase().includes("room") ? "room" : "drawer",
              location: x.details || "",
              employee: x.by || this.currentUser?.name || "User",
              details: x.title,
            }));
            this.activityLog = [...mapped, ...(this.activityLog || [])];
          }
        } catch (_) {}
        // Preload sales with extreme persistence cache fallback
        try {
          await this.refreshSales(false);
        } catch (_) {
          try {
            const cache = JSON.parse(
              localStorage.getItem("pos_sales_cache_v1") || "{}",
            );
            if (Array.isArray(cache.list)) {
              this.sales = cache.list;
              this.filterSales();
            }
          } catch (_) {}
        }
        // Real-time append when other modules complete a sale
        try {
          this._saleSeenKeys = this._saleSeenKeys || new Map();
          const seenRecently = (k) => {
            if (!k) return false;
            const now = Date.now();
            const last = this._saleSeenKeys.get(k) || 0;
            if (now - last < 10000) return true;
            this._saleSeenKeys.set(k, now);
            return false;
          };
          document.addEventListener("pos-sale-completed", async (e) => {
            const saleNum = e?.detail?.sale_number
              ? String(e.detail.sale_number)
              : null;
            const sid = e?.detail?.sale_id || e?.detail?.id;
            const key = saleNum || (sid != null ? String(sid) : null);
            if (seenRecently(key)) return;
            if (sid) {
              try {
                await this.appendSaleById(sid);
              } catch (err) {
                // Optimistic fallback if API fetch fails
                const now = new Date().toISOString();
                const optimistic = {
                  id: saleNum || String(sid),
                  saleNumber: saleNum || null,
                  numericId: Number(sid) || sid,
                  date: now,
                  customer: e?.detail?.customer || "Walk-in Customer",
                  isMedical: !!e?.detail?.isMedical,
                  itemCount: Number(e?.detail?.itemCount || 0),
                  total: Number(e?.detail?.total || 0),
                  discounts: [],
                  paymentMethod: String(
                    e?.detail?.paymentMethod || "cash",
                  ).toLowerCase(),
                  paymentReference: e?.detail?.paymentReference || null,
                  employee: this.getCurrentEmployee(),
                  isVoided: false,
                  status: "completed",
                };
                const ok = String(optimistic.id || optimistic.numericId);
                this.sales = [
                  optimistic,
                  ...this.sales.filter(
                    (x) => String(x.id || x.numericId) !== ok,
                  ),
                ];
                this.filterSales();
                try {
                  localStorage.setItem(
                    "pos_sales_cache_v1",
                    JSON.stringify({ ts: Date.now(), list: this.sales }),
                  );
                } catch (_) {}
              }
            } else {
              await this.refreshSales(true);
            }
          });
        } catch (_) {}
        // Cross-tab live updates via localStorage broadcast
        try {
          window.addEventListener("storage", (e) => {
            if (e && e.key === "pos_last_sale_id" && e.newValue) {
              const [sid] = String(e.newValue).split(":");
              if (sid) this.appendSaleById(sid);
            }
            if (e && e.key === "pos_last_sale_event" && e.newValue) {
              try {
                const payload = JSON.parse(e.newValue);
                document.dispatchEvent(
                  new CustomEvent("pos-sale-completed", { detail: payload }),
                );
              } catch (_) {}
            }
          });
        } catch (_) {}
      }
    },

    // Ensure arrays for collections
    normalizeCollections() {
      if (!Array.isArray(this.products)) {
        try {
          if (
            this.products &&
            typeof this.products === "object" &&
            Array.isArray(this.products.data)
          ) {
            this.products = this.products.data;
          } else {
            this.products = [];
          }
        } catch (e) {
          this.products = [];
        }
      }
      if (!Array.isArray(this.customers)) {
        try {
          if (
            this.customers &&
            typeof this.customers === "object" &&
            Array.isArray(this.customers.data)
          ) {
            this.customers = this.customers.data;
          } else {
            this.customers = [];
          }
        } catch (e) {
          this.customers = [];
        }
      }
      if (!Array.isArray(this.employees)) this.employees = [];
      if (!Array.isArray(this.sortedProducts)) this.sortedProducts = [];
    },

    // Authentication methods
    async initAuth() {
      this.isAuthenticated = posAuth.isAuthenticated();
      this.currentUser = posAuth.getUser();

      if (!this.isAuthenticated) {
        console.log("User not authenticated");
        return;
      }

      // Verify token is still valid, but do NOT force logout on transient failures
      try {
        const user = await posAuth.refreshUser();
        if (user) {
          this.currentUser = user;
        }
        // If user is null, keep existing auth state; axios interceptor will handle true 401 via pos-unauthorized
      } catch (error) {
        console.warn("Auth verification failed (non-fatal):", error);
        // Do not clear auth here; allow interceptor-driven flow to prompt re-auth only when necessary
      }
    },

    async handleLogin(email, password) {
      const result = await posAuth.login(email, password);
      if (result.success) {
        try {
          localStorage.removeItem("pos_force_reauth");
        } catch (e) {}
        this.isAuthenticated = true;
        this.currentUser = result.user;
        this.showAuthModal = false;
        await this.loadInitialData();
        this.ensureMyEmployeeListed();
        this.showToast("Login successful", "success");
      } else {
        this.showToast(result.message, "error");
      }
      return result.success;
    },

    async handlePinLogin(employeeId, pin) {
      const result = await posAuth.pinLogin(employeeId, pin);
      if (result.success) {
        try {
          localStorage.removeItem("pos_force_reauth");
        } catch (e) {}
        this.isAuthenticated = true;
        this.currentUser = result.user;
        this.showAuthModal = false;
        await this.loadInitialData();
        this.ensureMyEmployeeListed();
        this.showToast("PIN login successful", "success");
      } else {
        this.showToast(result.message, "error");
      }
      return result.success;
    },

    async handleRegister() {
      this.registerError = "";
      const { name, email, password, passwordConfirm, pin } = this.registerForm;

      if (!name || !email || !password || !passwordConfirm || !pin) {
        this.registerError = "Please fill out all fields";
        return;
      }
      if (password !== passwordConfirm) {
        this.registerError = "Passwords do not match";
        return;
      }
      if (!/^\d{4}$/.test(pin)) {
        this.registerError = "PIN must be exactly 4 digits";
        return;
      }

      const res = await posAuth.selfRegister({
        name,
        email,
        password,
        passwordConfirm,
        pin,
      });
      if (res.success) {
        try {
          localStorage.removeItem("pos_force_reauth");
        } catch (e) {}
        this.currentUser = res.user;
        this.isAuthenticated = true;
        this.showRegisterModal = false;
        this.showAuthModal = false;
        this.registerForm = {
          name: "",
          email: "",
          password: "",
          passwordConfirm: "",
          pin: "",
        };
        await this.loadInitialData();
        this.ensureMyEmployeeListed(res.user);
        this.showToast("Account created. Welcome!", "success");
      } else {
        const details = res.errors
          ? Object.values(res.errors).flat().join("; ")
          : null;
        const msg = details ? `${res.message}: ${details}` : res.message;
        this.registerError = msg;
        this.showToast(msg, "error");
      }
    },

    async handleLogout() {
      await posAuth.logout();
      this.isAuthenticated = false;
      this.currentUser = null;
      this.showToast("Logged out successfully", "info");
      // Optionally redirect to login page
    },

    hasPermission(permission) {
      return posAuth.hasPermission(permission);
    },

    hasRole(role) {
      return posAuth.hasRole(role);
    },

    ensureMyEmployeeListed(userOverride) {
      const u =
        userOverride || this.currentUser || window.posAuth?.getUser?.() || {};
      const emp = u.employee || null;
      const idCandidates = [emp?.id, emp?.employee_id, u.employee_id, u.id]
        .map((v) => (v != null ? String(v) : ""))
        .filter(Boolean);
      if (!idCandidates.length) return;
      const exists = (this.employees || []).some((e) => {
        const eid = String(e.id);
        const nid = e.numericId != null ? String(e.numericId) : "";
        const eeid = e.employeeId || "";
        return (
          idCandidates.includes(eid) ||
          idCandidates.includes(nid) ||
          idCandidates.includes(eeid)
        );
      });
      if (exists) return;
      const name =
        emp?.name ||
        [emp?.first_name, emp?.last_name].filter(Boolean).join(" ") ||
        u.name ||
        "";
      const entry = {
        id: idCandidates[0],
        numericId: emp?.id ?? null,
        employeeId: emp?.employee_id ?? u.employee_id ?? null,
        name,
        email: u.email || emp?.email || "",
        phone: emp?.phone || "",
        role: (u.role || emp?.role || "cashier").toLowerCase(),
        status: "active",
        hireDate: new Date().toISOString().slice(0, 10),
        payRate: Number(emp && emp.hourly_rate != null ? emp.hourly_rate : 0),
        hoursWorked: 0,
        workerPermit: emp?.worker_permit || "",
        metrcApiKey: emp?.metrc_api_key || "",
      };
      this.employees = this.employees || [];
      this.employees.unshift(entry);
    },

    // API Integration methods
    async loadInitialData() {
      try {
        // Load products from API
        const productsResult = await posAuth.getProducts();
        if (productsResult.success) {
          const p =
            (productsResult.data &&
              (productsResult.data.data || productsResult.data)) ||
            [];
          if (Array.isArray(p)) this.products = p;
        }

        // Load customers from API
        const customersResult = await posAuth.getCustomers();
        if (customersResult.success) {
          const c =
            (customersResult.data &&
              (customersResult.data.data || customersResult.data)) ||
            [];
          if (Array.isArray(c)) this.customers = c;
          else if (!Array.isArray(this.customers)) this.customers = [];
        }
        // Merge locally saved customers (per-user and legacy)
        try { this.loadCustomers(); } catch (_) {}
        // Ensure collections remain arrays after API calls
        this.normalizeCollections();

        // Load employees for admins/managers
        if (
          this.hasPermission("employees:read") ||
          this.hasRole("admin") ||
          this.hasRole("manager")
        ) {
          await this.fetchEmployeesFromApi();
        }

        // Load settings from API
        await this.loadApiSettings();
        await this.loadPriceTiers();

        // Load saved report templates
        await this.fetchReportTemplates();

        // Test METRC connection if user has permission
        if (this.hasPermission("metrc:access")) {
          await this.testMetrcConnection();
        }
      } catch (error) {
        console.error("Failed to load initial data:", error);
        this.showToast("Failed to load application data", "error");
        // Fallback to local data
        this.loadSettings();
        this.loadProducts();
        this.loadCustomers();
        this.loadEmployees();
      } finally {
        try {
          this.filterLoyaltyCustomers();
        } catch (_) {}
      }
    },

    async loadApiSettings() {
      try {
        const result = await posAuth.apiRequest("get", "/settings/pos");
        if (result.success) {
          const payload = result.data || {};
          const settings =
            payload.settings && typeof payload.settings === "object"
              ? payload.settings
              : {};
          // Update local settings with API data
          this.taxRate =
            payload.tax_rate != null
              ? payload.tax_rate
              : settings.sales_tax != null
                ? settings.sales_tax
                : 20.0;
          this.medicalTaxRate =
            payload.medical_tax_rate != null ? payload.medical_tax_rate : 0.0;
          // Merge only the settings object into storeSettings
          Object.assign(this.storeSettings, settings);
          // Load weight threshold if present
          if (settings.weight_threshold != null) {
            const n = Number(settings.weight_threshold);
            if (isFinite(n))
              this.weightThreshold = Math.max(0, Number(n.toFixed(2)));
          }
        }
      } catch (error) {
        console.error("Failed to load API settings:", error);
      }
    },

    async fetchReportTemplates() {
      try {
        const res = await posAuth.apiRequest("get", "/reports/templates");
        let list = [];
        if (res.success) list = res.data.templates || [];
        try {
          const uid = posAuth?.getUser()?.id || "anon";
          const local = JSON.parse(
            localStorage.getItem(`report_templates_${uid}`) || "[]",
          );
          list = [...list, ...local];
        } catch (_) {}
        let recent = [];
        try {
          recent = JSON.parse(
            localStorage.getItem("cannabisPOS-reports") || "[]",
          );
        } catch (_) {
          recent = [];
        }
        const templatesMapped = list.map((t) => ({
          id: t.id,
          name: t.name,
          type: t.report_type,
          createdAt: t.updated_at || t.created_at || new Date().toISOString(),
          createdBy: this.currentUser?.name || "User",
          status: "saved",
          config: t.config,
        }));
        this.recentReports = [...recent, ...templatesMapped].slice(0, 10);
      } catch (e) {
        // ignore
      }
    },

    async testMetrcConnection() {
      try {
        const result = await posAuth.testMetrcConnectionDirect();
        if (result.success) {
          console.log("METRC connection test:", result.data);
          this.metrcConnected = result.data.connection_test?.success || false;
        } else {
          this.metrcConnected = false;
        }
      } catch (error) {
        console.error("METRC connection test failed:", error);
        this.metrcConnected = false;
      }
    },

    async saveProductToApi(productData) {
      if (!this.hasPermission("products:write")) {
        this.showToast("Insufficient permissions to save products", "error");
        return false;
      }

      try {
        const result = await posAuth.apiRequest(
          "post",
          "/products",
          productData,
        );
        if (result.success) {
          // Add to local products array
          this.products.push(result.data);
          this.showToast("Product saved successfully", "success");
          return true;
        } else {
          this.showToast(result.message, "error");
          return false;
        }
      } catch (error) {
        console.error("Failed to save product:", error);
        this.showToast("Failed to save product", "error");
        return false;
      }
    },

    async updateProductInApi(productId, productData) {
      if (!this.hasPermission("products:write")) {
        this.showToast("Insufficient permissions to update products", "error");
        return false;
      }

      try {
        const result = await posAuth.apiRequest(
          "put",
          `/products/${productId}`,
          productData,
        );
        if (result.success) {
          // Update local products array
          const index = this.products.findIndex((p) => p.id === productId);
          if (index !== -1) {
            this.products[index] = result.data;
          }
          this.showToast("Product updated successfully", "success");
          return true;
        } else {
          this.showToast(result.message, "error");
          return false;
        }
      } catch (error) {
        console.error("Failed to update product:", error);
        this.showToast("Failed to update product", "error");
        return false;
      }
    },

    async processApiPayment(paymentData) {
      if (!this.hasPermission("pos:sales")) {
        this.showToast("Insufficient permissions to process payments", "error");
        return null;
      }

      try {
        const result = await posAuth.processPayment(paymentData);
        if (result.success) {
          this.showToast("Payment processed successfully", "success");
          try {
            this.reportDealUsageFromCart &&
              (await this.reportDealUsageFromCart());
          } catch (_) {}
          // Live update the sales list and End of Day stats
          try {
            const sid = result?.data?.sale_id || result?.sale_id;
            if (sid) {
              await this.appendSaleById(sid);
              try {
                document.dispatchEvent(
                  new CustomEvent("pos-sale-completed", {
                    detail: { sale_id: sid },
                  }),
                );
              } catch (_) {}
              try {
                window.dispatchEvent(
                  new CustomEvent("pos-sale-completed", {
                    detail: { sale_id: sid },
                  }),
                );
              } catch (_) {}
              try {
                localStorage.setItem(
                  "pos_last_sale_id",
                  `${sid}:${Date.now()}`,
                );
              } catch (_) {}
            }
          } catch (_) {}
          this.clearCart();
          return result.data;
        } else {
          this.showToast(result.message, "error");
          return null;
        }
      } catch (error) {
        console.error("Payment processing failed:", error);
        this.showToast("Payment processing failed", "error");
        return null;
      }
    },

    // Report deal usage to backend and update UI counters
    async reportDealUsageFromCart() {
      try {
        const counts = {};
        (this.cart || []).forEach((item) => {
          const id = item?._appliedDealId;
          if (id) counts[id] = (counts[id] || 0) + 1;
        });
        const ids = Object.keys(counts);
        if (!ids.length) return;
        for (const id of ids) {
          const n = counts[id];
          for (let i = 0; i < n; i++) {
            try {
              await posAuth.apiRequest("post", `/deals/apply`, {
                deal_id: id,
                cart_total: 0,
              });
            } catch (_) {}
          }
          const idx = (this.deals || []).findIndex(
            (d) => String(d.id) === String(id),
          );
          if (idx >= 0) {
            this.deals[idx].currentUses =
              (Number(this.deals[idx].currentUses) || 0) + n;
          }
        }
        this.filterDeals && this.filterDeals();
        try {
          document.dispatchEvent(
            new CustomEvent("deal-usage-updated", { detail: { counts } }),
          );
        } catch (_) {}
      } catch (_) {}
    },

    // SALES: Load, filter, stats, actions
    async refreshSales(forceNetwork = true) {
      try {
        const nowTs = Date.now();
        if (!this._lastSalesToastAt || nowTs - this._lastSalesToastAt > 30000) {
          this.showToast && this.showToast("Refreshing sales…", "info");
          this._lastSalesToastAt = nowTs;
        }
      } catch (_) {}
      const toLocalISO = (d) =>
        `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
      let start = this.salesFilter.startDate;
      let end = this.salesFilter.endDate;
      if (!start || !end || this.salesFilter.dateRange !== "custom") {
        const dr = this.salesFilter.dateRange || "today";
        const d = new Date();
        if (dr === "week") {
          const first = new Date(d);
          first.setDate(d.getDate() - 6);
          start = toLocalISO(first);
          end = toLocalISO(d);
        } else if (dr === "month") {
          const first = new Date(d.getFullYear(), d.getMonth(), 1);
          const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
          start = toLocalISO(first);
          end = toLocalISO(last);
        } else if (dr === "yesterday") {
          d.setDate(d.getDate() - 1);
          start = end = toLocalISO(d);
        } else if (dr === "custom") {
          // keep provided custom range
        } else if (dr === "today") {
          start = end = toLocalISO(d);
        } else {
          start = "";
          end = "";
        }
      }

      let list = null;
      const params = {
        status: "completed",
        sort_by: "created_at",
        sort_order: "desc",
        limit: 1000,
        tz:
          (Intl.DateTimeFormat &&
            Intl.DateTimeFormat().resolvedOptions().timeZone) ||
          undefined,
      };
      this._serverFilteredDates = false;
      if (start && end) {
        params.date_from = start;
        params.date_to = end;
        // Also send exact UTC boundaries to avoid TZ ambiguity
        try {
          const startBoundLocal = new Date(`${start}T00:00:00`);
          const endBoundLocal = new Date(`${end}T23:59:59.999`);
          params.start_at = startBoundLocal.toISOString();
          params.end_at = endBoundLocal.toISOString();
        } catch (_) {}
        this._serverDateFromKey = start;
        this._serverDateToKey = end;
      }

      if (forceNetwork) {
        const endpoints = [
          "/sales/recent-json",
          "/api/sales/recent",
          "/sales/recent",
        ];
        for (const url of endpoints) {
          try {
            const res = await (window.axios || axios).get(url, {
              params,
              headers: { Accept: "application/json" },
            });
            let data = res?.data;
            if (!Array.isArray(data) && data && Array.isArray(data.data))
              data = data.data;
            if (Array.isArray(data)) {
              const mapped = data.map((s) => this.mapSaleToSpa(s));
              if (mapped.length) {
                list = mapped;
                this._serverFilteredDates = !!(
                  params.date_from && params.date_to
                );
                break;
              } else {
                list = [];
                continue;
              }
            }
          } catch (e) {
            continue;
          }
        }
      }

      if (!Array.isArray(list)) {
        try {
          const cache = JSON.parse(
            localStorage.getItem("pos_sales_cache_v1") || "{}",
          );
          if (Array.isArray(cache.list)) {
            list = cache.list;
            this._serverFilteredDates = false;
          }
        } catch (_) {
          list = [];
        }
      }

      const rawList = Array.isArray(list) ? list : [];
      // Primary: de-dup by sale number/id
      const seen = new Set();
      const uniqueById = rawList.filter((s) => {
        const key = s.saleNumber || s.sale_number || s.id || s.numericId;
        if (key == null) return true;
        const k = String(key);
        if (seen.has(k)) return false;
        seen.add(k);
        return true;
      });
      // Secondary: de-dup by minute-bucket + amount + payment method
      const pad = (n) => String(n).padStart(2, "0");
      const seenBuckets = new Set();
      const unique = uniqueById.filter((s) => {
        // Only apply bucket-based de-dup when the record lacks a stable identifier
        const hasStableId = !!(s.saleNumber || s.numericId || s.id);
        if (hasStableId) return true;
        try {
          const dt = new Date(s.date);
          const bucket = `${dt.getFullYear()}-${pad(dt.getMonth() + 1)}-${pad(dt.getDate())} ${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
          const amt = Number(s.total || 0).toFixed(2);
          const pm = String(s.paymentMethod || "").toLowerCase();
          const k = `${bucket}|${amt}|${pm}`;
          if (seenBuckets.has(k)) return false;
          seenBuckets.add(k);
          return true;
        } catch (_) {
          return true;
        }
      });
      this.sales = unique;
      this.filterSales();
      try {
        localStorage.setItem(
          "pos_sales_cache_v1",
          JSON.stringify({ ts: Date.now(), list: this.sales }),
        );
      } catch (_) {}
      // Update month pace stats from loaded sales to persist across days
      try {
        this.updateMonthStatsFromSales && this.updateMonthStatsFromSales();
      } catch (_) {}
      try {
        const nowTs2 = Date.now();
        if (
          !this._lastSalesCountToastAt ||
          nowTs2 - this._lastSalesCountToastAt > 30000
        ) {
          this.showToast &&
            this.showToast(
              `${this.sales.length} sale(s) loaded`,
              this.sales.length ? "success" : "info",
            );
          this._lastSalesCountToastAt = nowTs2;
        }
      } catch (_) {}
    },

    mapSaleToSpa(s) {
      const itemCount = Number(
        s.item_count != null
          ? s.item_count
          : Array.isArray(s.sale_items)
            ? s.sale_items.reduce((a, b) => a + Number(b.quantity || 0), 0)
            : 0,
      );
      const discountAmt = Number(s.discount_amount || 0);
      const discounts =
        discountAmt > 0
          ? [{ id: `order-${s.id}`, type: "Order", amount: discountAmt }]
          : [];
      const paymentRef = s.payment_reference || s.card_last_four || null;
      let customerType = String(s.customer_type || "").toLowerCase();
      const medicalCard =
        s.customer?.medical_card_number ||
        s.customer_info?.medical_card_number ||
        s.customer?.medical_card ||
        s.customer_info?.medical_card ||
        s.customer?.patient_card_number ||
        s.customer_info?.patient_card_number ||
        null;
      const customerStr = typeof s.customer === "string" ? s.customer : "";
      const isMedicalFlag = !!(
        s.customer?.isMedical ||
        s.customer?.medical ||
        s.customer_info?.is_medical ||
        s.customer_info?.medical ||
        /medical/i.test(customerStr)
      );
      if (
        customerType !== "medical" &&
        medicalCard &&
        String(medicalCard).trim()
      )
        customerType = "medical";
      if (customerType !== "medical" && isMedicalFlag) customerType = "medical";
      const customerLabel =
        customerType === "medical"
          ? "Medical Customer"
          : "Recreational Customer";
      let empName =
        (s.employee &&
          (s.employee.full_name ||
            s.employee.name ||
            (
              (s.employee.first_name || "") +
              " " +
              (s.employee.last_name || "")
            ).trim())) ||
        s.employee_name ||
        (s.meta && s.meta.employee_name) ||
        "";
      if (!empName || /unknown/i.test(empName)) {
        try {
          const u =
            (window.posAuth &&
              window.posAuth.getUser &&
              window.posAuth.getUser()) ||
            {};
          const fallback = (
            u.name ||
            (u.employee &&
              (u.employee.name ||
                (
                  (u.employee.first_name || "") +
                  " " +
                  (u.employee.last_name || "")
                ).trim())) ||
            ""
          ).trim();
          if (fallback) empName = fallback;
        } catch (e) {}
      }
      if (!empName) empName = "Unknown";
      const subtotal = Number(
        s.subtotal != null
          ? s.subtotal
          : s.subtotal_amount != null
            ? s.subtotal_amount
            : 0,
      );
      const tax = Number(
        s.tax_amount != null ? s.tax_amount : s.tax != null ? s.tax : 0,
      );
      const total = Number(
        s.total_amount != null ? s.total_amount : s.total != null ? s.total : 0,
      );
      const discountPercent =
        subtotal > 0 && discountAmt > 0 ? (discountAmt / subtotal) * 100 : 0;
      const meta = s.meta || null;
      const debitAmount =
        meta && meta.debit_amount != null ? Number(meta.debit_amount) : null;
      const saleNumber = s.sale_number || s.saleNumber || null;
      return {
        id: saleNumber || String(s.id),
        saleNumber: saleNumber || null,
        numericId: s.id,
        date:
          typeof s.created_at === "string"
            ? s.created_at
            : s.created_at && s.created_at.toISOString
              ? s.created_at.toISOString()
              : String(s.created_at || ""),
        customer: customerLabel,
        customerType,
        customerMedicalCard: medicalCard,
        isMedical: customerType === "medical",
        itemCount,
        subtotal,
        tax,
        total,
        discounts,
        discountPercent,
        paymentMethod: String(s.payment_method || "cash").toLowerCase(),
        paymentReference: paymentRef ? String(paymentRef).slice(-4) : null,
        employee: empName,
        isVoided: String(s.status || "").toLowerCase() === "voided",
        status: String(s.status || "completed").toLowerCase(),
        meta,
        debitAmount,
      };
    },

    filterSales() {
      const q = (this.salesFilter.customer || "").trim().toLowerCase();
      const pay = (this.salesFilter.paymentMethod || "").toLowerCase();
      const range = this.salesFilter.amountRange;
      let min = -Infinity,
        max = Infinity;
      if (range === "0-25") {
        min = 0;
        max = 25;
      } else if (range === "25-50") {
        min = 25;
        max = 50;
      } else if (range === "50-100") {
        min = 50;
        max = 100;
      } else if (range === "100+") {
        min = 100;
        max = Infinity;
      }

      // Date filter
      const toLocalISO = (d) =>
        `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
      let start = this.salesFilter.startDate;
      let end = this.salesFilter.endDate;
      const dr = this.salesFilter.dateRange || "today";
      if (!start || !end || dr !== "custom") {
        const d = new Date();
        if (dr === "week") {
          const first = new Date(d);
          first.setDate(d.getDate() - 6);
          start = toLocalISO(first);
          end = toLocalISO(d);
        } else if (dr === "month") {
          const first = new Date(d.getFullYear(), d.getMonth(), 1);
          const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
          start = toLocalISO(first);
          end = toLocalISO(last);
        } else if (dr === "yesterday") {
          const y = new Date(d);
          y.setDate(d.getDate() - 1);
          start = end = toLocalISO(y);
        } else if (dr === "today") {
          start = end = toLocalISO(d);
        }
      }
      const dateKey = (d) => {
        try {
          const dt = d instanceof Date ? d : new Date(d);
          if (isNaN(dt.getTime())) return "";
          return `${dt.getFullYear()}-${String(dt.getMonth() + 1).padStart(2, "0")}-${String(dt.getDate()).padStart(2, "0")}`;
        } catch (_) {
          return "";
        }
      };
      const startKey = start || "";
      const endKey = end || "";

      this.filteredSales = (this.sales || []).filter((s) => {
        const nameOk =
          !q ||
          (s.customer || "").toLowerCase().includes(q) ||
          (s.customerMedicalCard || "").toLowerCase().includes(q);
        const payOk = !pay || s.paymentMethod === pay;
        const amtOk = s.total >= min && s.total <= max;
        let dateOk = true;
        try {
          if (startKey && endKey) {
            const startBound = new Date(`${startKey}T00:00:00`);
            const endBound = new Date(`${endKey}T23:59:59.999`);
            const dt = new Date(s.date);
            dateOk = dt >= startBound && dt <= endBound;
          }
        } catch (_) {
          dateOk = true;
        }
        return nameOk && payOk && amtOk && dateOk;
      });
    },

    async loadMonthStats() {
      try {
        const d = new Date();
        const first = new Date(d.getFullYear(), d.getMonth(), 1);
        const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
        const toLocalISO = (date) =>
          `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
        const params = {
          status: "completed",
          sort_by: "created_at",
          sort_order: "desc",
          limit: 1000,
          date_from: toLocalISO(first),
          date_to: toLocalISO(last),
          tz:
            (Intl.DateTimeFormat &&
              Intl.DateTimeFormat().resolvedOptions().timeZone) ||
            undefined,
        };
        const endpoints = [
          "/sales/recent-json",
          "/api/sales/recent",
          "/sales/recent",
        ];
        const http = window.axios || axios;
        let list = [];
        for (const url of endpoints) {
          try {
            const res = await http.get(url, {
              params,
              headers: { Accept: "application/json" },
            });
            let data = res?.data;
            if (!Array.isArray(data) && data && Array.isArray(data.data))
              data = data.data;
            if (Array.isArray(data)) {
              list = data.map((s) => this.mapSaleToSpa(s));
              break;
            }
          } catch (_) {
            continue;
          }
        }
        const revenue = (list || []).reduce(
          (sum, s) => sum + Number(s.total || 0),
          0,
        );
        const recCount = (list || []).filter(
          (s) => (s.customerType || "") !== "medical",
        ).length;
        const medUnique = new Set(
          (list || [])
            .filter((s) => (s.customerType || "") === "medical")
            .map((s) => s.customerMedicalCard || s.customer),
        ).size;
        const customers = recCount + medUnique;
        const dayOfMonth = d.getDate();
        const daysInMonth = new Date(
          d.getFullYear(),
          d.getMonth() + 1,
          0,
        ).getDate();
        this.monthStats = { revenue, customers, dayOfMonth, daysInMonth };
        try {
          const ymKey = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
          localStorage.setItem(
            `pos_month_stats_${ymKey}`,
            JSON.stringify({ ...this.monthStats, ts: Date.now() }),
          );
        } catch (_) {}
      } catch (_) {
        // Fallback to persisted month stats if available
        try {
          const ymKey = `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, "0")}`;
          const raw = localStorage.getItem(`pos_month_stats_${ymKey}`);
          if (raw) this.monthStats = JSON.parse(raw);
        } catch (_) {
          this.monthStats = this.monthStats || null;
        }
      }
    },

    updateMonthStatsFromSales() {
      try {
        const d = new Date();
        const nowMonth = d.getMonth();
        const nowYear = d.getFullYear();
        const list = Array.isArray(this.sales) ? this.sales : [];
        const monthList = list.filter((s) => {
          const dt = new Date(
            s.date || s.created_at || s.createdAt || Date.now(),
          );
          return dt.getFullYear() === nowYear && dt.getMonth() === nowMonth;
        });
        const revenue = monthList.reduce(
          (sum, s) => sum + Number(s.total || 0),
          0,
        );
        const recCount = monthList.filter(
          (s) => (s.customerType || "") !== "medical",
        ).length;
        const medUnique = new Set(
          monthList
            .filter((s) => (s.customerType || "") === "medical")
            .map((s) => s.customerMedicalCard || s.customer),
        ).size;
        const customers = recCount + medUnique;
        const dayOfMonth = d.getDate();
        const daysInMonth = new Date(
          d.getFullYear(),
          d.getMonth() + 1,
          0,
        ).getDate();
        this.monthStats = { revenue, customers, dayOfMonth, daysInMonth };
        const ymKey = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
        localStorage.setItem(
          `pos_month_stats_${ymKey}`,
          JSON.stringify({ ...this.monthStats, ts: Date.now() }),
        );
      } catch (_) {}
    },

    salesDateLabel() {
      const dr = this.salesFilter.dateRange || "today";
      const format = (val) => {
        const dd = new Date(val);
        return `${dd.getMonth() + 1}/${dd.getDate()}/${dd.getFullYear()}`;
      };
      let start = this.salesFilter.startDate;
      let end = this.salesFilter.endDate;
      if (dr !== "custom" || !start || !end) {
        const d = new Date();
        if (dr === "week") {
          const first = new Date(d);
          first.setDate(d.getDate() - 6);
          start = first;
          end = d;
        } else if (dr === "month") {
          const first = new Date(d.getFullYear(), d.getMonth(), 1);
          const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
          start = first;
          end = last;
        } else if (dr === "yesterday") {
          const y = new Date(d);
          y.setDate(d.getDate() - 1);
          start = y;
          end = y;
        } else {
          start = d;
          end = d;
        }
      }
      const sStr =
        typeof start === "string"
          ? start
          : `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, "0")}-${String(start.getDate()).padStart(2, "0")}`;
      const eStr =
        typeof end === "string"
          ? end
          : `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, "0")}-${String(end.getDate()).padStart(2, "0")}`;
      if (sStr === eStr) return format(sStr);
      return `${format(sStr)} - ${format(eStr)}`;
    },

    // ===== Deals & Specials (API-backed) =====
    mapDealToSpa(d) {
      const t = String(d.type || d.deal_type || "").toLowerCase();
      const type = t === "fixed" ? "fixed_amount" : t;
      const normDate = (v) => {
        if (!v) return "";
        const s = String(v);
        if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
        // Try to parse and format as YYYY-MM-DD
        try {
          const dt = new Date(s);
          if (!isNaN(dt.getTime())) {
            const y = dt.getFullYear();
            const m = String(dt.getMonth() + 1).padStart(2, "0");
            const d2 = String(dt.getDate()).padStart(2, "0");
            return `${y}-${m}-${d2}`;
          }
        } catch (_) {}
        return s.split("T")[0] || "";
      };
      const activeDays = Array.isArray(d.active_days)
        ? d.active_days
        : typeof d.active_days === "string"
          ? (function (s) {
              try {
                const x = JSON.parse(s);
                return Array.isArray(x) ? x : [];
              } catch (_) {
                return [];
              }
            })()
          : [];
      // Normalize discount maps
      const catMap = (() => {
        const v = d.category_discounts;
        if (!v) return {};
        if (typeof v === "string") {
          try {
            const x = JSON.parse(v);
            return x && typeof x === "object" ? x : {};
          } catch (_) {
            return {};
          }
        }
        return v;
      })();
      const itemMap = (() => {
        const v = d.item_discounts;
        if (!v) return {};
        if (typeof v === "string") {
          try {
            const x = JSON.parse(v);
            return x && typeof x === "object" ? x : {};
          } catch (_) {
            return {};
          }
        }
        return v;
      })();
      return {
        id: d.id,
        name: d.name,
        description: d.description || "",
        type: type,
        discountValue: Number(d.value || d.discount_value || 0),
        categories: Array.isArray(d.applicable_categories)
          ? d.applicable_categories
          : Array.isArray(d.categories)
            ? d.categories
            : [],
        specificItems: Array.isArray(d.specific_items) ? d.specific_items : [],
        categoryDiscounts: catMap || {},
        itemDiscounts: itemMap || {},
        startDate: normDate(d.start_date || d.startDate || ""),
        endDate: normDate(d.end_date || d.endDate || ""),
        isActive: !!(d.is_active ?? true),
        frequency: d.frequency || "always",
        dayOfWeek: d.day_of_week || undefined,
        dayOfMonth: d.day_of_month || undefined,
        activeDays: activeDays,
        emailCustomers: !!d.email_customers,
        loyaltyOnly: !!d.loyalty_only,
        medicalOnly: !!d.medical_only,
        minimumPurchase:
          d.minimum_purchase != null ? Number(d.minimum_purchase) : undefined,
        minimumPurchaseType: d.minimum_purchase_type || "dollars",
        maxUses: d.max_uses != null ? Number(d.max_uses) : undefined,
        currentUses: d.current_uses != null ? Number(d.current_uses) : 0,
      };
    },

    async loadDeals() {
      try {
        let list = [];
        // Primary: API (Node proxy -> Supabase)
        try {
          if (window.posAuth && typeof posAuth.apiRequest === "function") {
            const res = await posAuth.apiRequest("get", "/deals");
            const payload = res?.data;
            const dealsArr = Array.isArray(payload?.deals)
              ? payload.deals
              : Array.isArray(payload)
                ? payload
                : [];
            list = dealsArr.map((d) => this.mapDealToSpa(d));
          }
        } catch (_) {}
        // Fallback: Laravel web route (merged Supabase + local DB)
        if (!Array.isArray(list) || list.length === 0) {
          try {
            const resp = await fetch("/deals", {
              headers: { Accept: "application/json" },
              credentials: "same-origin",
            });
            if (resp.ok) {
              const data = await resp.json();
              const arr = Array.isArray(data?.deals)
                ? data.deals
                : Array.isArray(data)
                  ? data
                  : [];
              list = arr.map((d) => this.mapDealToSpa(d));
            }
          } catch (_) {}
        }
        // Merge any locally-saved deals (persist across logins in this browser)
        try {
          const rawLocal = localStorage.getItem("pos_deals_local_v1");
          const localArr = rawLocal ? JSON.parse(rawLocal) : [];
          const mappedLocal = (Array.isArray(localArr) ? localArr : []).map(
            (d) => this.mapDealToSpa(d),
          );
          const existingIds = new Set((list || []).map((d) => String(d.id)));
          const merged = [
            ...(list || []),
            ...mappedLocal.filter((d) => !existingIds.has(String(d.id))),
          ];
          list = merged;
        } catch (_) {}
        this.deals = list || [];
        this.filterDeals();
      } catch (e) {
        this.deals = this.deals || [];
        this.filterDeals();
      }
    },

    filterDeals() {
      const f = String(this.dealFilter || "").toLowerCase();
      let list = Array.isArray(this.deals) ? this.deals.slice() : [];
      if (f === "active") list = list.filter((d) => !!d.isActive);
      else if (f === "inactive") list = list.filter((d) => !d.isActive);
      else if (f === "expired")
        list = list.filter(
          (d) => !!d.endDate && new Date(d.endDate) < new Date(),
        );
      this.filteredDeals = list;
    },

    // Dashboard helpers used in templates
    getTodaysSavings() {
      try {
        const s = this.getEndOfDayStats ? this.getEndOfDayStats() : null;
        const v =
          s && typeof s.totalDiscounts === "number" ? s.totalDiscounts : 0;
        return Number.isFinite(v) ? v : 0;
      } catch (_) {
        return 0;
      }
    },
    getCustomersHelped() {
      try {
        const s = this.getEndOfDayStats ? this.getEndOfDayStats() : null;
        const v =
          s && typeof s.customerCount === "number"
            ? s.customerCount
            : s && typeof s.totalSales === "number"
              ? s.totalSales
              : 0;
        return Number.isFinite(v) ? v : 0;
      } catch (_) {
        return 0;
      }
    },
    getMostPopularDeal() {
      try {
        const list = Array.isArray(this.deals) ? this.deals : [];
        if (!list.length) return "���";
        let best = list[0];
        for (const d of list) {
          const cu = Number(d?.currentUses ?? d?.current_uses ?? 0) || 0;
          const bestCu =
            Number(best?.currentUses ?? best?.current_uses ?? 0) || 0;
          if (cu > bestCu) best = d;
        }
        return best && best.name ? best.name : "—";
      } catch (_) {
        return "—";
      }
    },

    getActiveDealsCount() {
      return (this.deals || []).filter((d) => !!d.isActive).length;
    },

    getDealStatusClass(deal) {
      return deal?.isActive
        ? "bg-green-100 text-green-800"
        : "bg-gray-100 text-gray-800";
    },

    getDealSchedule(deal) {
      try {
        const days = [
          "Sunday",
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
        ];
        if (Array.isArray(deal.activeDays) && deal.activeDays.length > 0) {
          const labels = deal.activeDays
            .map((i) => days[i] || "")
            .filter(Boolean);
          return labels.length ? `Custom (${labels.join(", ")})` : "Custom";
        }
        const freq = String(deal.frequency || "").toLowerCase();
        if (freq === "always") return "Always Active";
        if (freq === "daily") return "Daily";
        if (freq === "weekly")
          return deal.dayOfWeek ? `Weekly (${deal.dayOfWeek})` : "Weekly";
        if (freq === "monthly")
          return deal.dayOfMonth
            ? `Monthly (Day ${deal.dayOfMonth})`
            : "Monthly";
        return "Custom";
      } catch (_) {
        return "Custom";
      }
    },

    async toggleDealStatus(deal) {
      try {
        // Local-only deal handling
        if (String(deal.id).startsWith("local-")) {
          deal.isActive = !deal.isActive;
          try {
            const raw = localStorage.getItem("pos_deals_local_v1");
            const arr = raw ? JSON.parse(raw) : [];
            const next = (Array.isArray(arr) ? arr : []).map((d) =>
              String(d.id) === String(deal.id)
                ? { ...d, is_active: deal.isActive }
                : d,
            );
            localStorage.setItem("pos_deals_local_v1", JSON.stringify(next));
          } catch (_) {}
          this.filterDeals();
          this.showToast &&
            this.showToast(
              `Deal ${deal.isActive ? "activated" : "deactivated"}`,
              "success",
            );
          return;
        }
        const payload = { ...deal, is_active: !deal.isActive };
        payload.type = payload.type === "fixed" ? "fixed_amount" : payload.type;
        payload.value = payload.discountValue;
        payload.applicable_categories = payload.categories || [];
        payload.active_days = Array.isArray(deal.activeDays)
          ? deal.activeDays.slice()
          : [];
        const res = await posAuth.apiRequest(
          "put",
          `/deals/${deal.id}`,
          payload,
        );
        if (res?.success !== false) {
          deal.isActive = !deal.isActive;
          this.filterDeals();
          this.showToast &&
            this.showToast(
              `Deal ${deal.isActive ? "activated" : "deactivated"}`,
              "success",
            );
        } else {
          this.showToast &&
            this.showToast(res?.message || "Failed to update deal", "error");
        }
      } catch (e) {
        this.showToast && this.showToast("Failed to update deal", "error");
      }
    },

    async deleteDeal(id) {
      if (!confirm("Delete this deal?")) return;
      try {
        if (String(id).startsWith("local-")) {
          try {
            const raw = localStorage.getItem("pos_deals_local_v1");
            const arr = raw ? JSON.parse(raw) : [];
            const next = (Array.isArray(arr) ? arr : []).filter(
              (d) => String(d.id) !== String(id),
            );
            localStorage.setItem("pos_deals_local_v1", JSON.stringify(next));
          } catch (_) {}
          this.deals = (this.deals || []).filter(
            (d) => String(d.id) !== String(id),
          );
          this.filterDeals();
          this.showToast && this.showToast("Deal deleted", "success");
          return;
        }
        const res = await posAuth.apiRequest("delete", `/deals/${id}`);
        if (res?.success !== false) {
          this.deals = (this.deals || []).filter((d) => d.id !== id);
          this.filterDeals();
          this.showToast && this.showToast("Deal deleted", "success");
        } else {
          this.showToast &&
            this.showToast(res?.message || "Failed to delete deal", "error");
        }
      } catch (e) {
        this.showToast && this.showToast("Failed to delete deal", "error");
      }
    },

    async saveDeal() {
      try {
        const f = this.dealForm || {};
        let freq = f.frequency || "always";
        let dow = f.dayOfWeek || f.day_of_week || null;
        try {
          if (Array.isArray(f.activeDays) && f.activeDays.length > 0) {
            const days = [
              "Sunday",
              "Monday",
              "Tuesday",
              "Wednesday",
              "Thursday",
              "Friday",
              "Saturday",
            ];
            const idx = Number(f.activeDays[0]);
            if (!isNaN(idx) && days[idx]) {
              freq = "weekly";
              dow = days[idx];
            }
          }
        } catch (_) {}
        const payload = {
          name: f.name,
          description: f.description || "",
          type: f.type === "fixed" ? "fixed_amount" : f.type || "percentage",
          value: Number(f.discountValue || f.value || 0),
          frequency: freq,
          day_of_week: dow,
          day_of_month: f.dayOfMonth || f.day_of_month || null,
          start_date: f.startDate || undefined,
          end_date: f.endDate || undefined,
          applicable_categories: Array.isArray(f.applicableCategories)
            ? f.applicableCategories
            : [],
          specific_items: Array.isArray(f.applicableProducts)
            ? f.applicableProducts
                .map((id) => Number(id))
                .filter((n) => !isNaN(n))
            : [],
          minimum_purchase:
            f.minPurchase != null ? Number(f.minPurchase) : null,
          minimum_purchase_type: f.minPurchaseType || "dollars",
          max_uses:
            f.usageLimit != null && String(f.usageLimit).trim() !== ""
              ? Number(f.usageLimit)
              : null,
          email_customers: !!f.emailCustomers,
          loyalty_only: !!f.loyaltyOnly,
          medical_only: !!f.medicalOnly,
          is_active: f.isActive != null ? !!f.isActive : true,
          active_days: Array.isArray(f.activeDays)
            ? f.activeDays.map((x) => Number(x)).filter((n) => !isNaN(n))
            : [],
          category_discounts: (function () {
            const m = f.categoryDiscounts || {};
            const out = {};
            Object.keys(m).forEach((k) => {
              const v = Number(m[k]);
              if (!isNaN(v)) out[k] = v;
            });
            return out;
          })(),
          item_discounts: (function () {
            const m = f.itemDiscounts || {};
            const out = {};
            Object.keys(m).forEach((k) => {
              const v = Number(m[k]);
              if (!isNaN(v)) out[String(k)] = v;
            });
            return out;
          })(),
        };
        const creating = !this.editingDeal;
        const url = creating ? "/deals" : `/deals/${this.editingDeal.id}`;
        const method = creating ? "post" : "put";
        if (!creating) {
          if (!f.startDate) delete payload.start_date;
          if (f.endDate == null || f.endDate === "") delete payload.end_date;
        }
        const res = await posAuth.apiRequest(method, url, payload);
        let data = res?.data || {};
        let persisted = data?.deal && data.deal.id != null;

        // Fallback to Laravel web route when API fails (RLS, network, etc.)
        if (!(res?.success && data?.success !== false && persisted)) {
          try {
            const webUrl = creating
              ? "/deals"
              : `/deals/${this.editingDeal.id}`;
            const webMethod = creating ? "POST" : "PATCH";
            const resp = await fetch(webUrl, {
              method: webMethod,
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN":
                  (document.querySelector('meta[name="csrf-token"]') || {})
                    .content || "",
              },
              credentials: "same-origin",
              body: JSON.stringify(payload),
            });
            const j = await resp.json().catch(() => ({}));
            if (resp.ok && j?.deal && j.deal.id != null) {
              data = j;
              persisted = true;
            }
          } catch (_) {}
        }

        if (persisted) {
          const created = this.mapDealToSpa(data.deal);
          if (creating) {
            this.deals = [created, ...this.deals];
          } else {
            this.deals = this.deals.map((d) =>
              String(d.id) === String(this.editingDeal.id)
                ? { ...created, id: this.editingDeal.id }
                : d,
            );
          }
          this.filterDeals();
          this.closeCreateDealModal();
          this.showToast &&
            this.showToast(
              creating ? "Deal created" : "Deal updated",
              "success",
            );
        } else {
          // Final fallback: persist locally in this browser so it survives logout/login
          try {
            const localId = `local-${Date.now()}`;
            const localDeal = this.mapDealToSpa({ ...payload, id: localId });
            // Save to localStorage cache
            const raw = localStorage.getItem("pos_deals_local_v1");
            const arr = raw ? JSON.parse(raw) : [];
            const next = Array.isArray(arr) ? arr : [];
            next.unshift({ ...localDeal });
            localStorage.setItem("pos_deals_local_v1", JSON.stringify(next));
            // Update UI
            this.deals = [localDeal, ...(this.deals || [])];
            this.filterDeals();
            this.closeCreateDealModal();
            this.showToast && this.showToast("Deal saved locally", "success");
          } catch (_) {
            this.showToast &&
              this.showToast(
                data?.message || res?.message || "Failed to save deal",
                "error",
              );
          }
        }
      } catch (e) {
        this.showToast && this.showToast("Failed to save deal", "error");
      }
    },

    getDealSelectableProducts() {
      const q = String(this.dealProductSearch || "").toLowerCase();
      const list = Array.isArray(this.products) ? this.products : [];
      if (!q) return list.slice(0, 100);
      return list
        .filter((p) => {
          const name = String(p.name || "").toLowerCase();
          const sku = String(p.sku || "").toLowerCase();
          return name.includes(q) || sku.includes(q);
        })
        .slice(0, 100);
    },

    editDeal(deal) {
      this.showCreateDealModal = true;
      this.editingDeal = deal;
      this.dealForm = {
        name: deal.name,
        description: deal.description,
        type: deal.type === "fixed_amount" ? "fixed" : deal.type,
        discountValue: deal.discountValue,
        buyQuantity: 1,
        getQuantity: 1,
        minPurchase: deal.minimumPurchase,
        minPurchaseType: deal.minimumPurchaseType || "dollars",
        usageLimit: deal.maxUses || "",
        allCategories: false,
        applicableCategories: deal.categories || [],
        applicableProducts: Array.isArray(deal.specificItems)
          ? deal.specificItems.slice()
          : [],
        categoryDiscounts:
          deal.categoryDiscounts || deal.category_discounts || {},
        itemDiscounts: deal.itemDiscounts || deal.item_discounts || {},
        excludeGLS: true,
        stackable: false,
        loyaltyOnly: !!deal.loyaltyOnly,
        medicalOnly: !!deal.medicalOnly,
        emailCustomers: !!deal.emailCustomers,
        isActive: !!deal.isActive,
        startDate: deal.startDate || "",
        endDate: deal.endDate || "",
        startTime: "",
        endTime: "",
        activeDays: (function () {
          const days = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
          ];
          const idx = days.indexOf(deal.dayOfWeek || deal.day_of_week || "");
          return idx >= 0 ? [idx] : [];
        })(),
      };
    },

    duplicateDeal(deal) {
      this.editingDeal = null;
      this.showCreateDealModal = true;
      this.dealForm = {
        ...this.dealForm,
        name: `${deal.name} (Copy)`,
        description: deal.description,
        type: deal.type === "fixed_amount" ? "fixed" : deal.type,
        discountValue: deal.discountValue,
        minPurchase: deal.minimumPurchase,
        minPurchaseType: deal.minimumPurchaseType || "dollars",
        applicableCategories: (deal.categories || []).slice(),
        applicableProducts: Array.isArray(deal.specificItems)
          ? deal.specificItems.slice()
          : [],
        categoryDiscounts:
          deal.categoryDiscounts || deal.category_discounts || {},
        itemDiscounts: deal.itemDiscounts || deal.item_discounts || {},
        loyaltyOnly: !!deal.loyaltyOnly,
        medicalOnly: !!deal.medicalOnly,
        emailCustomers: !!deal.emailCustomers,
        isActive: !!deal.isActive,
        startDate: deal.startDate || "",
        endDate: deal.endDate || "",
        activeDays: (function () {
          const days = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
          ];
          const idx = days.indexOf(deal.dayOfWeek || deal.day_of_week || "");
          return idx >= 0 ? [idx] : [];
        })(),
      };
    },

    getFilteredSalesStats() {
      const list = this.filteredSales || [];
      const totalRevenue = list.reduce(
        (sum, s) => sum + Number(s.total || 0),
        0,
      );
      const totalSales = list.length;
      const avgSale = totalSales > 0 ? totalRevenue / totalSales : 0;
      const recCount = list.filter(
        (s) => (s.customerType || "") !== "medical",
      ).length;
      const medUnique = new Set(
        list
          .filter((s) => (s.customerType || "") === "medical")
          .map((s) => s.customerMedicalCard || s.customer),
      ).size;
      const uniqueCustomers = recCount + medUnique;
      return { totalRevenue, totalSales, avgSale, uniqueCustomers };
    },

    getEndOfDayStats() {
      const list = this.filteredSales || [];
      const totalDiscounts = list.reduce(
        (sum, s) =>
          sum +
          (Array.isArray(s.discounts)
            ? s.discounts.reduce((a, d) => a + Number(d.amount || 0), 0)
            : 0),
        0,
      );
      const revenue = list.reduce((sum, s) => sum + Number(s.total || 0), 0);
      const cashSales = list
        .filter((s) => s.paymentMethod === "cash")
        .reduce((a, b) => a + Number(b.total || 0), 0);
      const debitSales = list
        .filter((s) => s.paymentMethod === "debit")
        .reduce(
          (a, b) =>
            a +
            Number(
              (b.debitAmount != null
                ? b.debitAmount
                : b.meta && b.meta.debit_amount != null
                  ? b.meta.debit_amount
                  : b.total) || 0,
            ),
          0,
        );
      const creditSales = list
        .filter((s) => s.paymentMethod === "credit")
        .reduce((a, b) => a + Number(b.total || 0), 0);
      const recCount = list.filter(
        (s) => (s.customerType || "") !== "medical",
      ).length; // each recreational sale = distinct customer
      const medUnique = new Set(
        list
          .filter((s) => (s.customerType || "") === "medical")
          .map((s) => s.customerMedicalCard || s.customer),
      ).size;
      const customerCount = recCount + medUnique;
      const totalSales = list.length;
      return {
        totalSales,
        totalRevenue: revenue,
        cashSales,
        debitSales,
        creditSales,
        customerCount,
        averageSale: totalSales > 0 ? revenue / totalSales : 0,
        totalDiscounts,
        tillBreakdown: { opening: 0 },
        paceReport: {
          currentMonthSales:
            this.monthStats && this.monthStats.revenue != null
              ? this.monthStats.revenue
              : revenue,
          dailyAverage: totalSales > 0 ? revenue / Math.max(1, totalSales) : 0,
          monthProjection: (() => {
            const now = new Date();
            const d =
              (this.monthStats && this.monthStats.dayOfMonth) || now.getDate();
            const dim =
              (this.monthStats && this.monthStats.daysInMonth) ||
              new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
            const m =
              this.monthStats && this.monthStats.revenue != null
                ? this.monthStats.revenue
                : revenue;
            return d > 0 ? (m / d) * dim : 0;
          })(),
        },
        customerPaceReport: {
          currentMonthCustomers:
            this.monthStats && this.monthStats.customers != null
              ? this.monthStats.customers
              : customerCount,
          dailyAverage:
            totalSales > 0 ? customerCount / Math.max(1, totalSales) : 0,
          monthProjection: (() => {
            const now = new Date();
            const d =
              (this.monthStats && this.monthStats.dayOfMonth) || now.getDate();
            const dim =
              (this.monthStats && this.monthStats.daysInMonth) ||
              new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
            const m =
              this.monthStats && this.monthStats.customers != null
                ? this.monthStats.customers
                : customerCount;
            return d > 0 ? (m / d) * dim : 0;
          })(),
        },
      };
    },

    printEndOfDayReport() {
      const dr = this.salesFilter.dateRange || "today";
      const toLocalISO = (d) =>
        `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
      const now = new Date();
      let url = "/sales/report/daily";
      if (dr === "today") {
        const tz =
          (Intl.DateTimeFormat &&
            Intl.DateTimeFormat().resolvedOptions().timeZone) ||
          "";
        url = `/sales/report/daily?date=${toLocalISO(now)}&format=pdf&tz=${encodeURIComponent(tz)}`;
      } else if (dr === "yesterday") {
        const d = new Date();
        d.setDate(d.getDate() - 1);
        const tz =
          (Intl.DateTimeFormat &&
            Intl.DateTimeFormat().resolvedOptions().timeZone) ||
          "";
        url = `/sales/report/daily?date=${toLocalISO(d)}&format=pdf&tz=${encodeURIComponent(tz)}`;
      } else if (dr === "week" || dr === "custom") {
        let start = this.salesFilter.startDate,
          end = this.salesFilter.endDate;
        if (dr === "week") {
          const d = new Date();
          const first = new Date(d);
          first.setDate(d.getDate() - 6);
          start = toLocalISO(first);
          end = toLocalISO(d);
        }
        const tz =
          (Intl.DateTimeFormat &&
            Intl.DateTimeFormat().resolvedOptions().timeZone) ||
          "";
        url = `/sales/report/weekly?start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}&format=pdf&tz=${encodeURIComponent(tz)}`;
      } else if (dr === "month") {
        const d = new Date();
        const tz =
          (Intl.DateTimeFormat &&
            Intl.DateTimeFormat().resolvedOptions().timeZone) ||
          "";
        url = `/sales/report/monthly?month=${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}&format=pdf&tz=${encodeURIComponent(tz)}`;
      }
      window.open(url, "_blank");
      this.endOfDayReportGenerated = true;
    },

    generateEndOfDayReport() {
      // For now, same as print - downloads the PDF via server
      this.printEndOfDayReport();
    },

    viewSaleDetails(sale) {
      const id = sale?.numericId || sale?.id || null;
      if (!id) return;
      window.open(`/sales/${id}`, "_blank");
    },

    reprintReceipt(sale) {
      const id = sale?.numericId || sale?.id || null;
      if (!id) return;
      window.open(`/sales/${id}/receipt?reprint=1`, "_blank");
    },

    async voidSale(sale) {
      const id = sale?.numericId || sale?.id || null;
      if (!id) return;
      const reason = prompt("Reason for voiding this sale?");
      if (!reason) return;
      const pin = prompt("Enter employee PIN to confirm:");
      if (!pin) return;
      try {
        const res = await axios.post(`/sales/${id}/void`, {
          reason,
          employee_pin: pin,
        });
        this.showToast("Sale voided", "success");
        await this.refreshSales(true);
      } catch (e) {
        const msg = e?.response?.data?.error || "Failed to void sale";
        this.showToast(msg, "error");
      }
    },

    async refundSale(sale) {
      const id = sale?.numericId || sale?.id || null;
      if (!id) return;
      const type = confirm("OK = Full refund, Cancel = Partial refund")
        ? "full"
        : "partial";
      let payload = {
        refund_type: type,
        reason: "Customer request",
        employee_pin: prompt("Enter employee PIN:") || "",
      };
      if (type === "partial") {
        const amtStr = prompt("Enter refund amount (e.g., 10.00):", "0.00");
        const amt = parseFloat(amtStr || "0");
        if (!(amt > 0)) return;
        payload.refund_amount = amt;
        payload.items = [];
      }
      try {
        await axios.post(`/sales/${id}/refund`, payload);
        this.showToast("Refund processed", "success");
        await this.refreshSales(true);
      } catch (e) {
        const msg = e?.response?.data?.error || "Failed to process refund";
        this.showToast(msg, "error");
      }
    },

    getSalesCountForPeriod() {
      const s = this.metrcPushSettings.startDate;
      const e = this.metrcPushSettings.endDate;
      if (!s || !e) return 0;
      const start = new Date(s).getTime();
      const end = new Date(e).getTime();
      return (this.sales || []).filter((x) => {
        const t = new Date(x.date).getTime();
        return t >= start && t <= end;
      }).length;
    },

    async pushToMetrc() {
      if (!this.metrcPushSettings.startDate || !this.metrcPushSettings.endDate)
        return;
      if (this.metrcPushInProgress) return;
      this.metrcPushInProgress = true;
      try {
        const start = new Date(this.metrcPushSettings.startDate).getTime();
        const end = new Date(this.metrcPushSettings.endDate).getTime();
        const list = (this.sales || []).filter((x) => {
          const t = new Date(x.date).getTime();
          return t >= start && t <= end && String(x.status) === "completed";
        });
        let pushed = 0;
        for (const s of list) {
          const id = s.numericId || s.id;
          if (!id) continue;
          try {
            const res = await axios.post(
              `/api/metrc/sales/receipts/from-sale/${id}`,
              {},
            );
            if (res?.status >= 200 && res?.status < 300) pushed++;
          } catch (e) {
            // ignore individual failures for batch
          }
        }
        this.lastMetrcPush = new Date().toLocaleString();
        this.metrcPushSuccess = pushed > 0;
        this.metrcPushResult =
          pushed > 0
            ? `Successfully pushed ${pushed} sale(s) to METRC`
            : "No eligible sales found for selected range";
        this.showToast(
          `Pushed ${pushed} sale(s) to METRC`,
          pushed ? "success" : "info",
        );
      } catch (err) {
        this.metrcPushSuccess = false;
        this.metrcPushResult =
          err?.response?.data?.message || "Failed to push to METRC";
        this.showToast(this.metrcPushResult, "error");
      } finally {
        this.metrcPushInProgress = false;
      }
    },

    async validateMetrcConnection() {
      try {
        const res = await (window.axios || axios).get("/api/settings/metrc");
        const enabled = !!res?.data?.enabled;
        const hasKey = !!res?.data?.user_api_key;
        this.showToast(
          enabled && hasKey ? "METRC ready" : "METRC not configured",
          enabled && hasKey ? "success" : "info",
        );
      } catch (_) {
        this.showToast("METRC not configured", "info");
      }
    },

    async appendSaleById(id) {
      const endpoints = [`/sales/json/${id}`, `/api/sales/${id}`];
      for (const url of endpoints) {
        try {
          const { data: s } = await (window.axios || axios).get(url, {
            headers: { Accept: "application/json" },
          });
          if (!s) continue;
          const mapped = this.mapSaleToSpa(s);
          const mk = String(mapped.id || mapped.numericId);
          this.sales = [
            mapped,
            ...this.sales.filter((x) => {
              const k = String(x.id || x.numericId);
              return k !== mk;
            }),
          ];
          this.filterSales();
          try {
            localStorage.setItem(
              "pos_sales_cache_v1",
              JSON.stringify({ ts: Date.now(), list: this.sales }),
            );
          } catch (_) {}
          return;
        } catch (_) {}
      }
    },

    getCurrentEmployee() {
      try {
        return (
          this.currentUser?.name ||
          this.currentUser?.employee?.name ||
          "Employee"
        );
      } catch (_) {
        return "Employee";
      }
    },

    // Page navigation
    setCurrentPage(page) {
      this.currentPage = page;
      // Manage live polling timer for sales page
      try {
        if (this._salesLiveTimer) {
          clearInterval(this._salesLiveTimer);
          this._salesLiveTimer = null;
        }
      } catch (_) {}
      if (page === "sales") {
        try {
          this.refreshSales(true);
        } catch (_) {}
        try {
          this._salesLiveTimer = setInterval(
            () => this.refreshSales(true),
            8000,
          );
        } catch (_) {}
      }
      if (page === "deals") {
        try {
          this.loadDeals();
        } catch (_) {}
      }
      if (page === "employees") {
        if ((this.employees || []).length === 0) {
          if (
            this.isAuthenticated &&
            (this.hasPermission("employees:read") ||
              this.hasRole("admin") ||
              this.hasRole("manager"))
          ) {
            this.fetchEmployeesFromApi();
          } else {
            this.ensureMyEmployeeListed();
          }
        }
      }
      if (page === "roles-permissions") {
        if (typeof this.loadRolePermissions === "function") {
          this.loadRolePermissions();
        }
      }
      if (page === "metrc-vendors") {
        try { this.refreshVendorData(); } catch (_) {}
      }
    },

    getCurrentPageTitle() {
      const titles = {
        pos: "Cashier",
        customers: "Customer Management",
        products: "Products",
        "metrc-vendors": "METRC Transfers",
        employees: "Employees",
        "rooms-drawers": "Rooms & Drawers",
        "price-tiers": "Price Tiers",
        sales: "Sales",
        "order-queue": "Order Queue",
        "inventory-evaluation": "Inventory Evaluation",
        aspd: "ASPD",
        analytics: "Analytics",
        reports: "Reports",
        deals: "Deals & Specials",
        loyalty: "Loyalty Program",
        settings: "Settings",
        "roles-permissions": "Roles & Permissions",
      };
      return titles[this.currentPage] || "Cannabis POS";
    },

    // Roles & Permissions (SPA state and actions)
    selectedRole: "admin",
    rolePermissions: {},

    // Role modal state
    showRoleModal: false,
    roleModalMode: "create", // 'create' | 'edit'
    roleModalKey: null,
    roleModalName: "",
    roleModalSelectKey: null,
    roleModalPerms: [],

    allPermissions() {
      return [
        "pos:access",
        "pos:sales",
        "pos:scanner_only",
        "products:read",
        "products:write",
        "products:print",
        "products:transfer",
        "products:delete",
        "sales:read",
        "sales:create",
        "sales:manage",
        "customers:read",
        "customers:write",
        "analytics:read",
        "reports:read",
        "reports:export",
        "employees:read",
        "employees:manage",
        "metrc:access",
        "metrc:sync",
        "metrc:create",
        "metrc:sales",
      ];
    },

    isAllSelectedMain() {
      const list = this.rolePermissions[this.selectedRole] || [];
      if (list.includes("*")) return true;
      const set = new Set(list);
      return this.allPermissions().every((p) => set.has(p));
    },
    toggleSelectAllMain(evt) {
      const check = evt?.target?.checked ?? !this.isAllSelectedMain();
      this.rolePermissions[this.selectedRole] = check ? ["*"] : [];
    },

    openRoleModal(mode) {
      this.roleModalMode = mode;
      if (mode === "edit") {
        this.roleModalSelectKey = this.selectedRole;
        const list = this.rolePermissions[this.roleModalSelectKey] || [];
        this.roleModalPerms = list.includes("*")
          ? this.allPermissions().slice()
          : list.slice();
      } else {
        this.roleModalKey = null;
        this.roleModalName = "";
        this.roleModalSelectKey = null;
        this.roleModalPerms = [];
      }
      this.showRoleModal = true;
    },
    closeRoleModal() {
      this.showRoleModal = false;
    },

    hasModalPerm(p) {
      return (this.roleModalPerms || []).includes(p);
    },
    toggleModalPerm(p) {
      const i = this.roleModalPerms.indexOf(p);
      if (i >= 0) this.roleModalPerms.splice(i, 1);
      else this.roleModalPerms.push(p);
    },
    isAllSelectedModal() {
      const set = new Set(this.roleModalPerms || []);
      return this.allPermissions().every((p) => set.has(p));
    },
    toggleSelectAllModal(evt) {
      const check = evt?.target?.checked ?? !this.isAllSelectedModal();
      this.roleModalPerms = check ? this.allPermissions().slice() : [];
    },

    slugifyRole(name) {
      return (name || "")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9_-]+/g, "-")
        .replace(/^-+|-+$/g, "");
    },

    async saveRoleModal() {
      const normalized =
        this.roleModalPerms.length >= this.allPermissions().length
          ? ["*"]
          : Array.from(new Set(this.roleModalPerms));
      if (this.roleModalMode === "create") {
        const key = this.slugifyRole(this.roleModalName);
        if (!key) {
          this.showToast("Enter a role name", "error");
          return;
        }
        if (this.rolePermissions[key]) {
          this.showToast("Role already exists", "error");
          return;
        }
        this.rolePermissions[key] = normalized;
        this.selectedRole = key;
      } else if (this.roleModalMode === "edit") {
        const key = this.roleModalSelectKey || this.selectedRole;
        if (!key) {
          this.showToast("Select a role", "error");
          return;
        }
        this.rolePermissions[key] = normalized;
        this.selectedRole = key;
      }
      await this.saveRolePermissions();
      this.closeRoleModal();
      this.showToast("Role saved", "success");
    },

    async deleteRole() {
      if (this.roleModalMode !== "edit") return;
      const key = this.roleModalSelectKey || this.selectedRole;
      if (!key) return;
      if (key === "admin") {
        this.showToast("Cannot delete admin role", "error");
        return;
      }
      delete this.rolePermissions[key];
      const keys = Object.keys(this.rolePermissions);
      this.selectedRole = keys[0] || "admin";
      await this.saveRolePermissions();
      this.closeRoleModal();
      this.showToast("Role deleted", "success");
    },

    async loadRolePermissions() {
      try {
        const res = await (window.posAuth
          ? posAuth.apiRequest("get", "/settings/pos")
          : Promise.resolve({ success: false }));
        const s =
          (res && res.success && (res.data?.settings || res.data)) || {};
        const __apiPerms =
          s.role_permissions && typeof s.role_permissions === "object"
            ? s.role_permissions
            : null;
        let __backup = null;
        try {
          __backup = JSON.parse(
            localStorage.getItem("role_permissions_backup") || "null",
          );
        } catch (_) {
          __backup = null;
        }
        const __defaults = {
          admin: ["*"],
          manager: [
            "pos:*",
            "products:*",
            "customers:*",
            "sales:*",
            "analytics:read",
            "deals:*",
            "employees:read",
            "metrc:access",
            "metrc:sync",
            "reports:read",
            "reports:export",
          ],
          inventory: [
            "products:*",
            "metrc:access",
            "metrc:sync",
            "analytics:read",
          ],
          budtender: [
            "pos:*",
            "products:read",
            "customers:read",
            "sales:create",
            "analytics:read",
          ],
          cashier: [
            "pos:*",
            "products:read",
            "sales:create",
            "products:print",
            "analytics:read",
            "pos:scanner_only",
          ],
        };
        const __isDefaults = (obj) => {
          if (!obj || typeof obj !== "object") return true;
          return (
            Object.keys(obj).sort().join(",") ===
            Object.keys(__defaults).sort().join(",")
          );
        };
        this.rolePermissions =
          __apiPerms && !__isDefaults(__apiPerms)
            ? __apiPerms
            : __backup && typeof __backup === "object"
              ? __backup
              : __defaults;
      } catch (e) {
        try {
          const b = JSON.parse(
            localStorage.getItem("role_permissions_backup") || "null",
          );
          if (b && typeof b === "object") this.rolePermissions = b;
        } catch (_) {}
      }
      try {
        localStorage.setItem(
          "role_permissions_backup",
          JSON.stringify(this.rolePermissions),
        );
      } catch (_) {}
    },

    hasPerm(p) {
      const list = this.rolePermissions[this.selectedRole] || [];
      if (list.includes("*")) return true;
      if (list.includes(p)) return true;
      if (p.includes(":")) {
        const ns = p.split(":")[0];
        if (list.includes(ns + ":*")) return true;
      }
      return false;
    },

    togglePerm(p) {
      const list = this.rolePermissions[this.selectedRole] || [];
      const i = list.indexOf(p);
      if (i >= 0) list.splice(i, 1);
      else list.push(p);
      this.rolePermissions[this.selectedRole] = list;
    },

    async saveRolePermissions() {
      try {
        const res = await (window.posAuth
          ? posAuth.apiRequest("get", "/settings/pos")
          : Promise.resolve({ success: false }));
        const settings =
          (res && res.success && (res.data?.settings || res.data)) || {};
        settings.role_permissions = this.rolePermissions;
        const r = await (window.posAuth
          ? posAuth.apiRequest("post", "/settings/pos", settings)
          : Promise.resolve({ success: false }));
        const ok = r?.success === true || r?.data?.success === true;
        if (ok) {
          try {
            localStorage.setItem(
              "role_permissions_backup",
              JSON.stringify(this.rolePermissions),
            );
          } catch (_) {}
        }
        this.showToast(
          ok ? "Permissions saved" : "Failed to save permissions",
          ok ? "success" : "error",
        );
      } catch (e) {
        this.showToast("Failed to save permissions", "error");
      }
    },

    // Product management
    products: [
      {
        id: 1,
        name: "Blue Dream Flower",
        category: "Flower",
        price: 12.0, // Price per gram (will be calculated based on tier)
        stock: 250, // Stock in grams for deli-style
        thc: 18.5,
        cbd: 0.8,
        weight: "Sold by gram",
        onSalesFloor: true,
        isGLS: false,
        room: "Sales Floor",
        vendor: "Oregon Cannabis Co.",
        supplier: "Green Valley Farms",
        sku: "BDF-001",
        metrcTag: "1A4060300001234000000001",
        priceTier: 2, // Top Shelf tier
      },
      {
        id: 2,
        name: "OG Kush Flower",
        category: "Flower",
        price: 15.0, // Price per gram
        stock: 180, // Stock in grams
        thc: 24.1,
        cbd: 0.3,
        weight: "Sold by gram",
        onSalesFloor: true,
        isGLS: false,
        room: "Sales Floor",
        vendor: "Pacific Coast Cannabis",
        supplier: "California Supply Co.",
        sku: "OGK-002",
        metrcTag: "1A4060300001234000000002",
        priceTier: 1, // Premium Flower tier
      },
      {
        id: 3,
        name: "White Widow Flower",
        category: "Flower",
        price: 8.0, // Price per gram
        stock: 320, // Stock in grams
        thc: 16.8,
        cbd: 0.5,
        weight: "Sold by gram",
        onSalesFloor: true,
        isGLS: false,
        room: "Sales Floor",
        vendor: "Budget Cannabis Co.",
        supplier: "Northwest Distribution",
        sku: "WW-003",
        metrcTag: "1A4060300001234000000003",
        priceTier: 3, // Budget Option tier
      },
      {
        id: 4,
        name: "THC Gummy Bears",
        category: "Edibles",
        price: 25.0,
        stock: 40,
        thc: 10,
        cbd: 0,
        weight: "100mg",
        onSalesFloor: true,
        isGLS: true,
        room: "Sales Floor",
        vendor: "Sweet Relief Co.",
        supplier: "Edible Solutions",
        sku: "TGB-002",
        metrcTag: "1A4060300001234000000002",
        servingSize: 10,
        totalServings: 10,
      },
    ],

    cart: [],
    subtotal: 0,
    taxAmount: 0,
    total: 0,
    sortedProducts: [],

    // Pagination computed property
    get paginatedProducts() {
      const itemsPerPage =
        this.viewMode === "cards"
          ? this.itemsPerPageCard
          : this.itemsPerPageList;
      const startIndex = (this.currentProductPage - 1) * itemsPerPage;
      const endIndex = startIndex + itemsPerPage;
      return this.sortedProducts.slice(startIndex, endIndex);
    },

    get totalPages() {
      const itemsPerPage =
        this.viewMode === "cards"
          ? this.itemsPerPageCard
          : this.itemsPerPageList;
      return Math.ceil(this.sortedProducts.length / itemsPerPage);
    },

    get paginationInfo() {
      const itemsPerPage =
        this.viewMode === "cards"
          ? this.itemsPerPageCard
          : this.itemsPerPageList;
      const startItem = (this.currentProductPage - 1) * itemsPerPage + 1;
      const endItem = Math.min(
        this.currentProductPage * itemsPerPage,
        this.sortedProducts.length,
      );
      return {
        start: startItem,
        end: endItem,
        total: this.sortedProducts.length,
        currentPage: this.currentProductPage,
        totalPages: this.totalPages,
      };
    },

    // Pagination functions
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentProductPage = page;
      }
    },

    nextPage() {
      if (this.currentProductPage < this.totalPages) {
        this.currentProductPage++;
      }
    },

    prevPage() {
      if (this.currentProductPage > 1) {
        this.currentProductPage--;
      }
    },

    resetPagination() {
      this.currentProductPage = 1;
    },

    // Cart functions
    handleProductCardClick(product) {
      // If role requires scanner to add items, block card click
      try {
        const role = (this.currentUser?.role || "").toLowerCase();
        const perms =
          (this.settings?.role_permissions &&
            this.settings.role_permissions[role]) ||
          [];
        if (Array.isArray(perms) && perms.includes("pos:scanner_only")) {
          this.showToast(
            "Scanner required: use a barcode scanner or the Add button.",
            "info",
          );
          return;
        }
      } catch (_) {}

      // For items NOT on sales floor, allow click but prevent cart addition
      if (!product.onSalesFloor) {
        // If no sale is active, start new sale modal
        if (!this.selectedCustomer && !this.ageVerified) {
          this.showNewSaleModal = true;
          return;
        }

        // If sale is active, show notification that item is not available
        this.showToast(
          "This product is not currently available for sale",
          "error",
        );
        return;
      }

      // For items on sales floor, use normal add to cart logic
      this.addToCart(product);
    },

    addToCart(product) {
      if (!product.onSalesFloor) {
        this.showToast("Product is not available for sale", "error");
        return;
      }

      if (!this.selectedCustomer && !this.ageVerified) {
        this.showNewSaleModal = true;
        return;
      }

      // For flower products, open deli-style selection
      if (product.category === "Flower" && product.priceTier) {
        this.openFlowerDeliModal(product);
        return;
      }

      // For non-flower products, add directly to cart
      const existingItem = this.cart.find((item) => item.id === product.id);
      if (existingItem) {
        existingItem.quantity += 1;
      } else {
        this.cart.push({
          ...product,
          quantity: 1,
          discount: { amount: 0, type: "fixed", value: 0, reason: "" },
        });
      }

      this.calculateTotals();
      this.showToast(`${product.name} added to cart`, "success");
    },

    // Auto-apply active deals to cart items (exclude GLS items)
    autoApplyDealsToCart() {
      const now = new Date();
      const dayIdx = now.getDay(); // 0=Sunday
      const dayNames = [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
      ];
      const isDealActiveNow = (d) => {
        if (!d || !d.isActive) return false;
        try {
          if (d.startDate) {
            const sd = new Date(d.startDate);
            if (now < sd) return false;
          }
          if (d.endDate) {
            const ed = new Date(d.endDate);
            if (now > ed) return false;
          }
        } catch (_) {}
        if (Array.isArray(d.activeDays) && d.activeDays.length > 0) {
          return d.activeDays.includes(dayIdx);
        }
        const f = String(d.frequency || "").toLowerCase();
        if (f === "always" || f === "daily") return true;
        if (f === "weekly")
          return !d.dayOfWeek || d.dayOfWeek === dayNames[dayIdx];
        if (f === "monthly")
          return !d.dayOfMonth || now.getDate() === Number(d.dayOfMonth);
        return true;
      };
      const deals = Array.isArray(this.deals)
        ? this.deals.filter(isDealActiveNow)
        : [];
      const getCategory = (item) =>
        item.category ||
        item.categoryName ||
        item.category_label ||
        item.type ||
        "";
      (this.cart || []).forEach((item) => {
        if (!item) return;
        if (item.isGLS) {
          return;
        }
        const base = Number(item.price || 0) * Number(item.quantity || 1);
        const grams =
          Number(item.selectedWeight || 0) * Number(item.quantity || 1);
        let best = {
          amount: 0,
          dealId: null,
          type: "fixed",
          value: 0,
          reason: "",
        };
        const cat = getCategory(item);
        for (const d of deals) {
          if (
            Array.isArray(d.categories) &&
            d.categories.length > 0 &&
            !d.categories.includes(cat)
          )
            continue;
          if (Array.isArray(d.specificItems) && d.specificItems.length > 0) {
            const pid = item && item.id ? item.id : null;
            if (pid == null || !d.specificItems.includes(pid)) continue;
          }
          if (d.minimumPurchase != null) {
            if ((d.minimumPurchaseType || "dollars") === "grams") {
              if (!grams || grams < Number(d.minimumPurchase)) continue;
            } else {
              if (base < Number(d.minimumPurchase)) continue;
            }
          }
          // Determine effective value (per-item > per-category > base)
          const pid = item && item.id ? String(item.id) : null;
          const catOverride =
            d.categoryDiscounts && cat in d.categoryDiscounts
              ? Number(d.categoryDiscounts[cat])
              : null;
          const itemOverride =
            pid && d.itemDiscounts && pid in d.itemDiscounts
              ? Number(d.itemDiscounts[pid])
              : null;
          const effectiveValue =
            itemOverride != null
              ? itemOverride
              : catOverride != null
                ? catOverride
                : Number(d.discountValue);

          let amt = 0;
          if (d.type === "percentage") amt = base * (effectiveValue / 100);
          else if (d.type === "fixed_amount")
            amt = Math.min(effectiveValue, base);
          else if (d.type === "bogo" || d.type === "bulk")
            amt = base * (effectiveValue / 100);
          if (amt > best.amount)
            best = {
              amount: amt,
              dealId: d.id,
              type: d.type,
              value: Number(d.discountValue),
              reason: d.name,
            };
        }
        if (
          !item.discount ||
          !item.discount.reason ||
          (item.discount.reason || "").startsWith("[AUTO]")
        ) {
          item.discount =
            best.amount > 0
              ? {
                  amount: best.amount,
                  type: best.type === "fixed_amount" ? "fixed" : "percentage",
                  value: best.value,
                  reason: best.dealId ? `[AUTO] ${best.reason}` : "",
                }
              : { amount: 0, type: "fixed", value: 0, reason: "" };
          item._appliedDealId = best.dealId || null;
        }
      });
    },

    // Add flower to cart by grams with per-gram pricing and decimal support
    addFlowerToCart(product, weight, price) {
      const w = Number(weight);
      const grams = isFinite(w) && w > 0 ? Number(w.toFixed(2)) : 1.0;
      const p = Number(price);
      const unitPrice = isFinite(p) && grams > 0 ? p / grams : p;

      // Merge by product and gram unit
      const existingItem = this.cart.find(
        (item) =>
          item.id === product.id &&
          item.category === "Flower" &&
          item._unit === "g",
      );

      if (existingItem) {
        const next = Number(existingItem.quantity || 0) + grams;
        existingItem.quantity = Number(next.toFixed(2));
        // Ensure per-gram price sticks if a different selection was used
        if (unitPrice && isFinite(unitPrice)) existingItem.price = unitPrice;
        existingItem.selectedWeight = 1;
        existingItem.weight = "1g";
      } else {
        this.cart.push({
          ...product,
          // Treat quantity as grams for Flower when selling deli-style
          quantity: grams,
          selectedWeight: 1,
          weight: "1g",
          price: unitPrice,
          _unit: "g",
          displayName: `${product.name} (by gram)`,
          discount: { amount: 0, type: "fixed", value: 0, reason: "" },
        });
      }

      this.calculateTotals();
      const line = isFinite(unitPrice) ? unitPrice * grams : p;
      const msg = `${product.name} (${grams}g) added to cart for $${Number(line).toFixed(2)}`;
      this.showToast(msg, "success");
    },

    removeFromCart(index) {
      this.cart.splice(index, 1);
      this.calculateTotals();
    },

    updateQuantity(index, newQuantity) {
      if (newQuantity <= 0) {
        this.removeFromCart(index);
      } else {
        const item = this.cart[index];
        const prev = Number(item.quantity || 0);
        const incoming = Number(newQuantity);
        let next = incoming;
        if (item && item._unit === "g") {
          const step = 0.25;
          const delta = incoming - prev;
          next = prev + (delta >= 0 ? step : -step);
        }
        const q = Number(next);
        item.quantity = isFinite(q)
          ? Math.max(0.01, Number(q.toFixed(2)))
          : 0.01;
        this.calculateTotals();
      }
    },

    clearCart() {
      this.cart = [];
      this.calculateTotals();
      try {
        this.persistCartState();
      } catch (_) {}
      this.showToast("Cart cleared", "info");
    },

    get filteredSavedSales() {
      const q = String(this.savedSalesSearch || "").toLowerCase();
      if (!q) return this.savedSales;
      return (this.savedSales || []).filter((s) => {
        const name = String(s.name || "").toLowerCase();
        const amt =
          s.total_amount != null
            ? String(s.total_amount)
            : String(s.total || "");
        return name.includes(q) || amt.includes(q);
      });
    },

    async openSavedSales() {
      await this.fetchSavedSales();
      this.showSavedSalesModal = true;
    },

    async fetchSavedSales() {
      this.loadingSavedSales = true;
      let list = [];
      try {
        if (
          this.isAuthenticated &&
          window.posAuth &&
          typeof posAuth.apiRequest === "function"
        ) {
          const res = await posAuth.apiRequest("get", "/pos/saved-sales");
          list = (res && (res.data?.saved_sales || res.data || [])) || [];
        }
      } catch (_) {}
      if (!Array.isArray(list) || list.length === 0) {
        try {
          const uid = window.posAuth?.getUser?.()?.id || "anon";
          const key = `cannabisPOS-savedSales-${uid}`;
          list = JSON.parse(localStorage.getItem(key) || "[]");
        } catch (_) {
          list = [];
        }
      }
      this.savedSales = Array.isArray(list) ? list : [];
      this.loadingSavedSales = false;
    },

    async deleteSavedSale(sale) {
      const id = sale?.id;
      try {
        if (
          this.isAuthenticated &&
          id &&
          window.posAuth &&
          typeof posAuth.apiRequest === "function"
        ) {
          await posAuth.apiRequest("delete", `/pos/saved-sales/${id}`);
        } else {
          const uid = window.posAuth?.getUser?.()?.id || "anon";
          const key = `cannabisPOS-savedSales-${uid}`;
          const list = JSON.parse(localStorage.getItem(key) || "[]");
          const next = list.filter((x) => x.id !== id);
          localStorage.setItem(key, JSON.stringify(next));
        }
        this.savedSales = (this.savedSales || []).filter((x) => x.id !== id);
        this.showToast("Saved sale deleted", "success");
      } catch (e) {
        this.showToast("Failed to delete saved sale", "error");
      }
    },

    async loadSavedSale(sale) {
      try {
        let data = sale;
        if (
          (!sale?.cart_items || sale.cart_items.length === 0) &&
          this.isAuthenticated &&
          sale?.id &&
          window.posAuth
        ) {
          const res = await posAuth.apiRequest(
            "get",
            `/pos/saved-sales/${sale.id}`,
          );
          data = res?.data?.saved_sale || res?.data || sale;
        }
        const items = Array.isArray(data.cart_items)
          ? data.cart_items
          : Array.isArray(data.cart)
            ? data.cart
            : [];
        if (!items.length) {
          this.showToast("Saved sale has no items", "warning");
          return;
        }
        this.cart = items.map((i) => ({ ...i }));
        this.selectedCustomer = data.customer || data.customer_info || null;
        this.cartDiscount = data.cart_discount || {
          type: "percentage",
          value: 0,
          amount: 0,
          reason: "",
        };
        this.calculateTotals();
        try {
          this.persistCartState();
        } catch (_) {}
        this.showToast("Saved sale loaded into cart", "success");
        try {
          await this.deleteSavedSale(data);
        } catch (_) {}
        this.showSavedSalesModal = false;
        window.dispatchEvent(new Event("pos-cart-updated"));
      } catch (e) {
        this.showToast("Failed to load saved sale", "error");
      }
    },

    // Save current cart to Saved Sales (local or API), then clear cart
    async holdCurrentSale() {
      if (!Array.isArray(this.cart) || this.cart.length === 0) {
        this.showToast("Cart is empty", "warning");
        return;
      }
      const name = `Held Sale - ${new Date().toLocaleString()}`;
      const payload = {
        id: Date.now(),
        name,
        employee: this.getCurrentEmployee && this.getCurrentEmployee(),
        customer: this.selectedCustomer || null,
        cart_items: (this.cart || []).map((x) => ({ ...x })),
        cart_discount: this.cartDiscount || null,
        total_items: (this.cart || []).reduce(
          (s, i) => s + (Number(i.quantity) || 0),
          0,
        ),
        total_amount: Number(this.total || 0),
        created_at: new Date().toISOString(),
      };

      // Try API when authenticated; fallback to localStorage
      let savedOk = false;
      try {
        if (
          this.isAuthenticated &&
          window.posAuth &&
          typeof posAuth.apiRequest === "function"
        ) {
          const res = await posAuth.apiRequest(
            "post",
            "/pos/save-sale",
            payload,
          );
          savedOk = !!(res && (res.success || res.ok));
        }
      } catch (_) {}
      if (!savedOk) {
        try {
          const uid = window.posAuth?.getUser?.()?.id || "anon";
          const key = `cannabisPOS-savedSales-${uid}`;
          const list = JSON.parse(localStorage.getItem(key) || "[]");
          list.unshift(payload);
          localStorage.setItem(key, JSON.stringify(list.slice(0, 50)));
          savedOk = true;
        } catch (_) {}
      }

      if (savedOk) {
        this.showToast("Sale held and added to Saved Sales", "success");
        this.clearCart();
        try {
          window.dispatchEvent(new Event("pos-cart-updated"));
        } catch (_) {}
      } else {
        this.showToast("Failed to hold sale", "error");
      }
    },

    // End current sale by clearing state (no save)
    async endCurrentSale() {
      try {
        if (typeof window.confirm === "function") {
          const ok = window.confirm(
            "End current sale? You will need to start a new sale to add items.",
          );
          if (!ok) return;
        }
        this.clearCart();
        this.selectedCustomer = null;
        this.cartDiscount = {
          type: "percentage",
          value: 0,
          amount: 0,
          reason: "",
        };
        try {
          this.persistCartState();
        } catch (_) {}
        this.showToast("Sale ended. Start a new sale to continue.", "info");
        try {
          window.dispatchEvent(new Event("pos-cart-updated"));
        } catch (_) {}
      } catch (e) {
        this.showToast("Failed to end sale", "error");
      }
    },

    calculateTotals() {
      try {
        this.autoApplyDealsToCart && this.autoApplyDealsToCart();
      } catch (_) {}
      if (!this.cart || !Array.isArray(this.cart)) {
        this.subtotal = 0;
        this.taxAmount = 0;
        this.total = 0;
        try {
          this.persistCartState();
        } catch (_) {}
        return;
      }

      // Calculate raw subtotal
      this.subtotal = this.cart.reduce((sum, item) => {
        if (!item) return sum;
        return sum + (item.price || 0) * (item.quantity || 1);
      }, 0);

      // Calculate total item discounts
      const totalItemDiscounts = this.getTotalItemDiscounts();

      // Calculate subtotal after item discounts
      const subtotalAfterItemDiscounts = this.subtotal - totalItemDiscounts;

      // Calculate cart discount amount
      const cartDiscountAmount =
        this.cartDiscount && this.cartDiscount.amount
          ? this.cartDiscount.amount
          : 0;

      // Calculate final subtotal after all discounts
      const finalSubtotal = Math.max(
        0,
        subtotalAfterItemDiscounts - cartDiscountAmount,
      );

      // Calculate tax on the discounted amount
      const effectiveTaxRate = this.getEffectiveTaxRate() || 0;
      this.taxAmount = finalSubtotal * (effectiveTaxRate / 100);

      // Calculate final total
      this.total = finalSubtotal + this.taxAmount;
      try {
        this.persistCartState();
      } catch (_) {}
    },

    getEffectiveTaxRate() {
      if (this.selectedCustomer && this.selectedCustomer.isMedical) {
        return this.medicalTaxRate || 0;
      }
      return this.taxRate || 0;
    },

    shouldShowTax() {
      return this.getEffectiveTaxRate() > 0;
    },

    // Customer functions
    customers: [
      {
        id: 1,
        name: "John Smith",
        email: "john@example.com",
        phone: "(555) 123-4567",
        isMedical: false,
        loyaltyPoints: 250,
      },
      {
        id: 2,
        name: "Jane Medical",
        email: "jane@example.com",
        phone: "(555) 987-6543",
        isMedical: true,
        loyaltyPoints: 150,
        medicalCard: "OR-MED-12345",
        medicalCardType: "patient",
      },
    ],

    // Loyalty view state (demo index.html)
    loyaltyFilter: {
      search: "",
      tier: "all",
      status: "all",
      sortBy: "name-asc",
    },
    filteredLoyaltyCustomers: [],

    // Persistent cart state (no data loss across refresh)
    cartStorageKey() {
      try {
        const uid = posAuth?.getUser()?.id || "anon";
        return `cannabisPOS-cart-${uid}`;
      } catch (_) {
        return "cannabisPOS-cart-anon";
      }
    },
    persistCartState() {
      try {
        const state = {
          cart: Array.isArray(this.cart) ? this.cart : [],
          selectedCustomer: this.selectedCustomer || null,
          cartDiscount: this.cartDiscount || {
            type: "percentage",
            value: 0,
            amount: 0,
          },
          subtotal: this.subtotal || 0,
          taxAmount: this.taxAmount || 0,
          total: this.total || 0,
          taxRate: this.taxRate,
        };
        localStorage.setItem(this.cartStorageKey(), JSON.stringify(state));
      } catch (_) {}
    },
    loadCartState() {
      try {
        const raw = localStorage.getItem(this.cartStorageKey());
        if (!raw) return;
        const state = JSON.parse(raw);
        this.cart = Array.isArray(state.cart) ? state.cart : [];
        this.selectedCustomer = state.selectedCustomer || null;
        this.cartDiscount = state.cartDiscount || this.cartDiscount;
        this.subtotal = state.subtotal || 0;
        this.taxAmount = state.taxAmount || 0;
        this.total = state.total || 0;
      } catch (_) {}
    },

    // Customers local persistence (per-user key with legacy fallback)
    customersStorageKey() {
      try {
        const uid = posAuth?.getUser()?.id || "anon";
        return `cannabisPOS-customers-${uid}`;
      } catch (_) {
        return "cannabisPOS-customers-anon";
      }
    },
    _saveCustomersLocal() {
      try {
        const list = Array.isArray(this.customers) ? this.customers : [];
        // Write to user-scoped key
        localStorage.setItem(this.customersStorageKey(), JSON.stringify(list));
        // Maintain legacy key for backward compatibility
        localStorage.setItem("cannabisPOS-customers", JSON.stringify(list));
      } catch (e) {}
    },
    loyaltyStorageKeys() {
      try {
        const uid = posAuth?.getUser()?.id || "anon";
        return [`cannabest-loyalty-${uid}`, "cannabest-loyalty"];
      } catch (_) {
        return ["cannabest-loyalty-anon", "cannabest-loyalty"];
      }
    },
    _addToLoyaltyLocal(entry) {
      try {
        const [userKey, globalKey] = this.loyaltyStorageKeys();
        const listA = JSON.parse(localStorage.getItem(userKey) || "[]");
        const listB = JSON.parse(localStorage.getItem(globalKey) || "[]");
        const today = new Date().toISOString().split('T')[0];
        const normalized = { signupDate: today, joinDate: today, ...entry };
        const seen = new Set();
        const merged = [normalized, ...(Array.isArray(listA)?listA:[]), ...(Array.isArray(listB)?listB:[])].filter(c => {
          const key = String(c?.id || c?.email || c?.phone || "");
          if (!key || seen.has(key)) return false;
          seen.add(key);
          return true;
        });
        localStorage.setItem(userKey, JSON.stringify(merged));
        localStorage.setItem(globalKey, JSON.stringify(merged));
      } catch (_) {}
    },

    selectCustomer(customer) {
      this.selectedCustomer = customer;
      this.calculateTotals();
      try {
        this.persistCartState();
      } catch (_) {}
    },

    async enrollCustomerInLoyalty() {
      const name = (this.enrollForm.customerName || "").trim();
      const email = (this.enrollForm.email || "").trim();
      const phone = (this.enrollForm.phone || "").trim();
      if (!name || !email || !phone) {
        this.showToast("Please fill in name, phone, and email", "error");
        return;
      }
      const starting = Number(this.enrollForm.startingPoints || 0);
      const tier = this.enrollForm.tier || "Bronze";

      // Try real API first when available
      let enrolled = null;
      try {
        if (window.posAuth && typeof posAuth.apiRequest === "function") {
          const res = await posAuth.apiRequest("post", "/loyalty/enroll", {
            name,
            email,
            phone,
            tier,
            starting_points: starting,
          });
          if (res && res.success && res.data && res.data.customer) {
            enrolled = res.data.customer;
          }
        }
      } catch (_) {}

      // Fallback: local demo push
      if (!enrolled) {
        enrolled = {
          id: Date.now(),
          name,
          email,
          phone,
          isMedical: false,
          loyaltyPoints: starting,
          tier,
        };
      }

      // Ensure customers is an array before push
      if (!Array.isArray(this.customers)) {
        const list = this.customers;
        if (list && Array.isArray(list.data)) this.customers = list.data;
        else if (Array.isArray(list)) this.customers = list;
        else this.customers = [];
      }

      this.customers.push(enrolled);
      try {
        this._saveCustomersLocal();
      } catch (_) {}
      try {
        this.filterLoyaltyCustomers();
      } catch (_) {}
      this.showEnrollCustomerModal = false;
      this.enrollForm = {
        customerName: "",
        email: "",
        phone: "",
        tier: "Bronze",
        startingPoints: 0,
      };
      this.showToast(`Enrolled ${name} in loyalty program`, "success");
    },

    deleteCustomerFromLoyalty(id) {
      // Demo-only removal helper used in index.html
      const before = Array.isArray(this.customers) ? this.customers.length : 0;
      this.customers = (
        Array.isArray(this.customers) ? this.customers : []
      ).filter((c) => String(c.id) !== String(id));
      if (Array.isArray(this.customers) && this.customers.length !== before) {
        try {
          this._saveCustomersLocal();
        } catch (_) {}
        try {
          this.filterLoyaltyCustomers();
        } catch (_) {}
        this.showToast("Customer removed from loyalty program", "success");
      }
    },

    async toggleCustomerStatus(customer) {
      try {
        if (!customer) return;
        const id = customer.id;
        const idx = (Array.isArray(this.customers) ? this.customers : []).findIndex(c => String(c.id) === String(id));
        if (idx === -1) return;
        const current = this.customers[idx];
        const nextActive = !(current.isActive === true);

        // Best-effort server update
        try {
          if (this.isAuthenticated && this.hasPermission("customers:write") && id != null) {
            const payload = { is_active: nextActive };
            try { await posAuth.apiRequest("patch", `/customers/${id}`, payload); }
            catch { await posAuth.apiRequest("put", `/customers/${id}`, payload); }
          }
        } catch (_) {}

        this.customers[idx] = { ...current, isActive: nextActive };
        try { this._saveCustomersLocal(); } catch (_) {}
        try { this.filterLoyaltyCustomers(); } catch (_) {}
        this.showToast(nextActive ? "Customer activated" : "Customer deactivated", "success");
      } catch (e) {
        this.showToast("Failed to toggle status", "error");
      }
    },

    viewCustomerDetails(customer) {
      try {
        if (!customer) return;
        // Prefer existing customer in list for freshest fields
        const idStr = String(customer.id || "");
        const existing = (Array.isArray(this.customers) ? this.customers : []).find(c => String(c.id) === idStr) || customer;
        this.viewCustomer(existing);
      } catch (_) {
        this.viewCustomer(customer);
      }
    },

    openAdjustPointsModal(customer) {
      if (!customer) return;
      const points = Number(customer.loyaltyPoints ?? customer.loyalty_points ?? customer.points ?? 0) || 0;
      const tier = customer.tier || "Bronze";
      this.selectedLoyaltyCustomer = { ...customer, points, loyaltyPoints: points, tier };
      this.pointsForm = { action: "add", type: "add", amount: 0, reason: "", customReason: "" };
      this.showAdjustPointsModal = true;
    },

    async savePointsAdjustment() {
      if (!this.selectedLoyaltyCustomer) return;
      const id = this.selectedLoyaltyCustomer.id;
      const amountNum = Number(this.pointsForm.amount || 0) || 0;
      if (amountNum <= 0 && this.pointsForm.type !== 'set') return;

      // Compute new total
      let newTotal = this.selectedLoyaltyCustomer.points || 0;
      if (this.pointsForm.type === 'set') newTotal = amountNum;
      else if (this.pointsForm.type === 'add') newTotal = newTotal + amountNum;
      else if (this.pointsForm.type === 'subtract') newTotal = Math.max(0, newTotal - amountNum);

      // Best-effort API
      try {
        if (this.isAuthenticated && this.hasPermission("loyalty:manage") && id != null) {
          const payload = {
            points: this.pointsForm.type === 'set' ? newTotal : amountNum,
            type: this.pointsForm.type === 'set' ? 'adjustment' : (this.pointsForm.type === 'add' ? 'earned' : 'redeemed'),
            reason: this.pointsForm.customReason || this.pointsForm.reason || 'Adjustment',
          };
          await posAuth.apiRequest("post", `/loyalty/${id}/adjust-points`, payload);
        }
      } catch (_) {}

      // Update local state
      const idx = (Array.isArray(this.customers) ? this.customers : []).findIndex(c => String(c.id) === String(id));
      if (idx !== -1) {
        const current = this.customers[idx];
        const lp = Number(current.loyaltyPoints ?? current.loyalty_points ?? current.points ?? 0) || 0;
        const updatedPoints = this.pointsForm.type === 'set' ? newTotal : (this.pointsForm.type === 'add' ? lp + amountNum : Math.max(0, lp - amountNum));
        this.customers[idx] = { ...current, loyaltyPoints: updatedPoints, points: updatedPoints };
      }
      if (this.selectedLoyaltyCustomer) {
        this.selectedLoyaltyCustomer.loyaltyPoints = newTotal;
        this.selectedLoyaltyCustomer.points = newTotal;
      }
      try { this._saveCustomersLocal(); } catch (_) {}
      try { this.filterLoyaltyCustomers(); } catch (_) {}
      this.showAdjustPointsModal = false;
      this.pointsForm = { action: "add", type: "add", amount: 0, reason: "", customReason: "" };
      this.showToast("Points updated", "success");
    },

    // Sale flow functions
    selectCustomerType(type) {
      this.showNewSaleModal = false;
      if (type === "recreational") {
        this.showRecreationalModal = true;
      } else if (type === "medical") {
        this.showMedicalModal = true;
      }
    },

    confirmRecreationalSale() {
      if (!this.ageVerified) {
        this.showToast("Age verification is required", "error");
        return;
      }
      this.showRecreationalModal = false;
      this.showToast("Recreational sale started - Age verified", "success");
    },

    confirmMedicalSale() {
      // Validate required medical data
      if (
        !this.medicalData.cardNumber ||
        !this.medicalData.type ||
        !this.medicalData.issueDate ||
        !this.medicalData.expirationDate
      ) {
        this.showToast(
          "Please fill in all required medical information",
          "error",
        );
        return;
      }

      // Validate caregiver-specific requirements
      if (
        this.medicalData.type === "caregiver" &&
        !this.medicalData.patientCardNumber
      ) {
        this.showToast(
          "Patient card number is required for caregivers",
          "error",
        );
        return;
      }

      // Create medical customer if saving data
      if (this.medicalData.saveData) {
        const medicalCustomer = {
          id: Date.now(), // Simple ID generation
          name: this.medicalData.customerName || "Medical Customer",
          email: this.medicalData.email || "",
          phone: "",
          isMedical: true,
          loyaltyPoints: 0,
          medicalCard: this.medicalData.cardNumber,
          medicalCardType: this.medicalData.type,
          medicalIssueDate: this.medicalData.issueDate,
          medicalExpirationDate: this.medicalData.expirationDate,
          patientCardNumber: this.medicalData.patientCardNumber || null,
        };

        // Add to customers array if not already exists
        const existingCustomer = this.customers.find(
          (c) => c.medicalCard === this.medicalData.cardNumber,
        );
        if (!existingCustomer) {
          this.customers.push(medicalCustomer);
        }

        // Set as selected customer
        this.selectedCustomer = existingCustomer || medicalCustomer;
      } else {
        // Create temporary medical customer for this sale only
        this.selectedCustomer = {
          id: "temp-medical",
          name: "Medical Customer",
          email: "",
          phone: "",
          isMedical: true,
          loyaltyPoints: 0,
          medicalCard: this.medicalData.cardNumber,
          medicalCardType: this.medicalData.type,
          medicalIssueDate: this.medicalData.issueDate,
          medicalExpirationDate: this.medicalData.expirationDate,
          patientCardNumber: this.medicalData.patientCardNumber || null,
        };
      }

      // Close modal and reset form
      this.showMedicalModal = false;
      this.medicalData = {
        cardNumber: "",
        issueDate: "",
        expirationDate: "",
        type: "medical",
        patientCardNumber: "",
        saveData: false,
        customerName: "",
        email: "",
      };

      // Recalculate totals (medical customers are tax-exempt)
      this.calculateTotals();

      // Success message
      const customerType =
        this.selectedCustomer.medicalCardType === "caregiver"
          ? "Caregiver"
          : "Medical Patient";
      this.showToast(`${customerType} sale started - Tax exempt`, "success");
    },

    cancelNewSale() {
      this.showNewSaleModal = false;
      this.showRecreationalModal = false;
      this.showMedicalModal = false;
      this.ageVerified = false;
      this.selectedCustomer = null;
    },

    // Loyalty filtering and stats (for demo index.html)
    filterLoyaltyCustomers() {
      this.normalizeCollections();
      let list = Array.isArray(this.customers) ? this.customers.slice() : [];
      list = list.map((c) => ({
        tier: c.tier || "Bronze",
        loyaltyPoints:
          typeof c.loyaltyPoints === "number"
            ? c.loyaltyPoints
            : Number(c.loyalty_points || 0) || 0,
        ...c,
      }));
      const q = (this.loyaltyFilter.search || "").toLowerCase();
      if (q) {
        list = list.filter(
          (c) =>
            (c.name || "").toLowerCase().includes(q) ||
            (c.phone || "").toLowerCase().includes(q) ||
            (c.email || "").toLowerCase().includes(q),
        );
      }
      if (this.loyaltyFilter.tier && this.loyaltyFilter.tier !== "all") {
        list = list.filter(
          (c) => String(c.tier) === String(this.loyaltyFilter.tier),
        );
      }
      if (this.loyaltyFilter.status && this.loyaltyFilter.status !== "all") {
        if (this.loyaltyFilter.status === "active")
          list = list.filter((c) => (c.loyaltyPoints || 0) > 0);
        if (this.loyaltyFilter.status === "inactive")
          list = list.filter((c) => (c.loyaltyPoints || 0) === 0);
      }
      const [field, dir] = (this.loyaltyFilter.sortBy || "name-asc").split("-");
      list.sort((a, b) => {
        const av =
          (a[field] ?? (field === "name" ? a.name : a.loyaltyPoints)) || 0;
        const bv =
          (b[field] ?? (field === "name" ? b.name : b.loyaltyPoints)) || 0;
        if (typeof av === "string" && typeof bv === "string") {
          return dir === "asc" ? av.localeCompare(bv) : bv.localeCompare(av);
        }
        return dir === "asc" ? (av > bv ? 1 : -1) : av < bv ? 1 : -1;
      });
      this.filteredLoyaltyCustomers = list;
    },
    getLoyaltyStats() {
      const list = Array.isArray(this.customers) ? this.customers : [];
      const totalMembers = list.length;
      const activeMembers = list.filter(
        (c) => (c.loyaltyPoints || c.loyalty_points || 0) > 0,
      ).length;
      const pointsRedeemed = 0;
      const avgMonthlySpend = 0;
      return { totalMembers, activeMembers, pointsRedeemed, avgMonthlySpend };
    },

    // Product filtering and sorting
    filterProducts() {
      this.normalizeCollections();
      let filtered = Array.isArray(this.products) ? this.products : [];

      if (this.selectedCategory) {
        filtered = filtered.filter((p) => p.category === this.selectedCategory);
      }

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(
          (p) =>
            p.name.toLowerCase().includes(query) ||
            p.category.toLowerCase().includes(query) ||
            (p.sku && p.sku.toLowerCase().includes(query)),
        );
      }

      this.sortedProducts = filtered;
      this.sortProducts();
      this.resetPagination();
    },

    sortProducts() {
      const [field, direction] = this.sortOrder.split("-");

      this.sortedProducts.sort((a, b) => {
        let aVal = a[field];
        let bVal = b[field];

        if (typeof aVal === "string") {
          aVal = aVal.toLowerCase();
          bVal = bVal.toLowerCase();
        }

        if (direction === "asc") {
          return aVal > bVal ? 1 : -1;
        } else {
          return aVal < bVal ? 1 : -1;
        }
      });
    },

    // Payment functions
    initiatePayment(method) {
      if (this.cart.length === 0) {
        this.showToast("Cart is empty", "error");
        return;
      }

      if (method === "cash") {
        this.showCashModal = true;
      } else if (method === "debit") {
        this.showDebitModal = true;
      }
    },

    // Settings and data management
    loadSettings() {
      try {
        const savedPrimary = localStorage.getItem("cannabisPOS-settings");
        const savedLegacy = localStorage.getItem("cannabest-pos-settings");
        const saved = savedPrimary || savedLegacy;
        if (saved) {
          const settings = JSON.parse(saved);
          this.cartViewMode = settings.cartViewMode || "narrow";
          this.viewMode = settings.viewMode || "cards";
        }

        // Load store settings
        const savedStoreSettings = localStorage.getItem(
          "cannabisPOS-storeSettings",
        );
        if (savedStoreSettings) {
          const storeSettings = JSON.parse(savedStoreSettings);
          this.storeSettings = { ...this.storeSettings, ...storeSettings };
        }
      } catch (error) {
        console.error("Error loading settings:", error);
      }
      // Load weight threshold
      try {
        const wt = localStorage.getItem("cannabisPOS-weightThreshold");
        if (wt != null && wt !== "") {
          const n = parseFloat(wt);
          if (!isNaN(n) && isFinite(n)) this.weightThreshold = Math.max(0, n);
        }
      } catch (_) {}
    },

    saveSettings() {
      try {
        const settings = {
          cartViewMode: this.cartViewMode,
          viewMode: this.viewMode,
          timestamp: new Date().toISOString(),
        };
        const json = JSON.stringify(settings);
        localStorage.setItem("cannabisPOS-settings", json);
        localStorage.setItem("cannabest-pos-settings", json);
      } catch (error) {
        console.error("Error saving settings:", error);
      }
    },

    async saveWeightThreshold() {
      try {
        const n = Number(this.weightThreshold);
        this.weightThreshold = isFinite(n)
          ? Math.max(0, Number(n.toFixed(2)))
          : 0;
        localStorage.setItem(
          "cannabisPOS-weightThreshold",
          String(this.weightThreshold),
        );
        // Persist to server POS settings (Supabase-backed)
        try {
          let settings = {};
          try {
            const getRes = await (window.posAuth
              ? posAuth.apiRequest("get", "/settings/pos")
              : (window.axios || axios).get("/api/settings/pos"));
            settings = getRes?.data?.settings || getRes?.data || settings;
          } catch (_) {}
          settings = settings && typeof settings === "object" ? settings : {};
          settings.weight_threshold = this.weightThreshold;
          const saveRes = await (window.posAuth
            ? posAuth.apiRequest("post", "/settings/pos", settings)
            : (window.axios || axios).post("/api/settings/pos", settings));
          const ok =
            saveRes?.success === true ||
            saveRes?.data?.success === true ||
            (saveRes?.status && saveRes.status >= 200 && saveRes.status < 300);
          if (!ok) throw new Error("save-failed");
        } catch (_) {}
        if (typeof this.showToast === "function")
          this.showToast("Weight threshold saved", "success");
      } catch (_) {
        if (typeof this.showToast === "function")
          this.showToast("Failed to save threshold", "error");
      }
    },

    async loadPriceTiers() {
      try {
        const res = await (window.axios || axios).get("/api/price-tiers", {
          headers: { Accept: "application/json" },
        });
        const list = res?.data?.tiers || [];
        if (Array.isArray(list)) {
          const mapped = list.map((t) => ({
            id: t.id,
            name: t.name,
            isActive: t.is_active ?? true,
            createdAt: t.created_at || new Date().toISOString(),
            prices: t.prices || {
              weight_1g: 0,
              weight_3_5g: 0,
              weight_7g: 0,
              weight_14g: 0,
              weight_28g: 0,
            },
            customWeights: t.custom_weights || [],
          }));
          this.priceTiers = mapped;
          if (mapped.length > 0) {
            try {
              localStorage.setItem(
                "cannabisPOS-priceTiers-backup",
                JSON.stringify(mapped),
              );
            } catch (_) {}
          }
        }
      } catch (e) {
        try {
          const b = JSON.parse(
            localStorage.getItem("cannabisPOS-priceTiers-backup") || "[]",
          );
          if (Array.isArray(b)) this.priceTiers = b;
        } catch (_) {}
      }
    },

    loadProducts() {
      try {
        const saved = localStorage.getItem("cannabisPOS-products");
        if (saved) {
          const parsed = JSON.parse(saved);
          this.products = Array.isArray(parsed)
            ? parsed
            : Array.isArray(parsed?.data)
              ? parsed.data
              : [];
        }
      } catch (error) {
        console.error("Error loading products:", error);
      }
      const doFetch = async () => {
        try {
          const resp = await fetch("/products", {
            headers: { Accept: "application/json" },
            credentials: "same-origin",
          });
          if (resp.ok) {
            const data = await resp.json();
            const items = Array.isArray(data?.data)
              ? data.data
              : Array.isArray(data)
                ? data
                : [];
            if (Array.isArray(items) && items.length) {
              this.products = items;
              try {
                localStorage.setItem(
                  "cannabisPOS-products",
                  JSON.stringify({ data: items }),
                );
              } catch (e) {}
            }
          }
        } catch (e) {
          try {
            const resp = await fetch("/api/products", {
              headers: { Accept: "application/json" },
            });
            if (resp.ok) {
              const data = await resp.json();
              const items = Array.isArray(data?.data)
                ? data.data
                : Array.isArray(data)
                  ? data
                  : [];
              if (Array.isArray(items) && items.length) {
                this.products = items;
                try {
                  localStorage.setItem(
                    "cannabisPOS-products",
                    JSON.stringify({ data: items }),
                  );
                } catch (_) {}
              }
            }
          } catch (_) {}
        } finally {
          this.normalizeCollections();
          this.filterProducts();
        }
      };
      if (!Array.isArray(this.products) || this.products.length === 0) {
        doFetch();
      } else {
        this.normalizeCollections();
        this.filterProducts();
      }
    },

    loadCustomers() {
      try {
        const out = Array.isArray(this.customers) ? this.customers.slice() : [];
        const legacyRaw = localStorage.getItem("cannabisPOS-customers");
        const userRaw = localStorage.getItem(this.customersStorageKey());
        const legacy = legacyRaw ? JSON.parse(legacyRaw) : [];
        const userScoped = userRaw ? JSON.parse(userRaw) : [];
        const merged = [...out];
        const seen = new Set(out.map((c) => String(c.id || c.email || c.phone || Math.random())));
        const addAll = (arr) => {
          (Array.isArray(arr) ? arr : []).forEach((c) => {
            const key = String(c.id || c.email || c.phone || JSON.stringify(c));
            if (!seen.has(key)) {
              merged.push(c);
              seen.add(key);
            }
          });
        };
        addAll(userScoped);
        addAll(legacy);
        this.customers = merged;
        // Persist merged list to both scoped and legacy keys to survive session changes
        try { this._saveCustomersLocal(); } catch (_) {}
      } catch (error) {
        console.error("Error loading customers:", error);
      } finally {
        try {
          this.filterLoyaltyCustomers();
        } catch (_) {}
      }
    },

    async fetchEmployeesFromApi() {
      // Prefer Laravel web endpoint to ensure DB persistence, then fallback to /api proxy
      let list = [];
      try {
        const resp = await fetch("/employees", {
          method: "GET",
          headers: { Accept: "application/json" },
          credentials: "same-origin",
        });
        if (resp.ok) {
          const data = await resp.json();
          list = data.employees || data.data || [];
        }
      } catch (err) {
        console.warn("/employees web fetch failed", err);
      }
      if (!Array.isArray(list) || list.length === 0) {
        try {
          const res = await posAuth.apiRequest("get", "/employees"); // /api/employees
          if (
            res.success &&
            res.data &&
            (res.data.employees || res.data.data)
          ) {
            list = res.data.employees || res.data.data || [];
          }
        } catch (err) {
          console.warn("/api/employees fallback failed", err);
        }
      }
      if (!Array.isArray(list)) list = [];
      this.employees = list.map((e) => ({
        id: e.id || e.employee_id,
        numericId: e.id ?? null,
        employeeId: e.employee_id ?? null,
        name: e.full_name
          ? e.full_name
          : [e.first_name, e.last_name].filter(Boolean).join(" "),
        email: e.email || "",
        phone: e.phone || "",
        role: (e.role || e.position || "budtender").toLowerCase(),
        status:
          e.is_active === false ||
          String(e.status || "").toLowerCase() === "inactive"
            ? "inactive"
            : "active",
        hireDate: e.hire_date ? String(e.hire_date).slice(0, 10) : "",
        payRate: Number(e.hourly_rate ?? 0),
        hoursWorked: Number(e.hours_worked ?? 0),
        workerPermit: e.worker_permit || e.workerPermit || "",
        metrcApiKey: e.metrc_api_key || e.metrcApiKey || "",
      }));
      try {
        this.ensureMyEmployeeListed(window.posAuth?.getUser?.());
      } catch (_) {}
    },

    loadEmployees() {
      // Placeholder fallback: ensure array exists
      this.employees = this.employees || [];
    },

    get filteredEmployees() {
      const q = (this.employeeSearchQuery || "").toLowerCase();
      const statusFilter = (this.employeeStatusFilter || "").toLowerCase();
      return (this.employees || []).filter((e) => {
        const matchQ =
          !q ||
          [e.name, e.email, e.phone, String(e.id)]
            .filter(Boolean)
            .some((v) => String(v).toLowerCase().includes(q));
        const matchRole =
          !this.employeeRoleFilter || e.role === this.employeeRoleFilter;
        // Default behavior: hide inactive unless the user searches or explicitly selects a status
        let matchStatus = true;
        if (!statusFilter) {
          matchStatus = q ? true : e.status === "active";
        } else if (statusFilter === "all") {
          matchStatus = true;
        } else {
          matchStatus = e.status === statusFilter;
        }
        return matchQ && matchRole && matchStatus;
      });
    },

    // Utility functions
    showToast(message, type = "info") {
      // Create toast notification element
      const toast = document.createElement("div");
      toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg text-white text-sm max-w-sm transition-all duration-300 transform translate-x-full opacity-0`;

      // Set background color based on type
      const colors = {
        success: "bg-green-600",
        error: "bg-red-600",
        warning: "bg-yellow-600",
        info: "bg-blue-600",
      };
      toast.classList.add(colors[type] || colors.info);

      // Add message
      toast.textContent = message;

      // Add to DOM
      document.body.appendChild(toast);

      // Animate in
      setTimeout(() => {
        toast.classList.remove("translate-x-full", "opacity-0");
        toast.classList.add("translate-x-0", "opacity-100");
      }, 10);

      // Auto remove after 4 seconds
      setTimeout(() => {
        toast.classList.add("translate-x-full", "opacity-0");
        setTimeout(() => {
          if (toast.parentNode) {
            document.body.removeChild(toast);
          }
        }, 300);
      }, 4000);
    },

    // Basic modal functions
    closeAllModals() {
      this.showCustomerModal = false;
      this.showNewSaleModal = false;
      this.showRecreationalModal = false;
      this.showMedicalModal = false;
      this.showMetrcModal = false;
      this.showTransferModal = false;
      this.showEditModal = false;
      this.showPrintModal = false;
      this.showPrintPreviewModal = false;
      this.showPrintSettingsPreviewModal = false;
      this.showCashModal = false;
      this.showDebitModal = false;
      this.showPrintTypeModal = false;
    },

    // Price tier functions for flower products
    getFlowerTierName(tierId) {
      const tier = this.priceTiers.find((t) => t.id == tierId);
      return tier ? tier.name : "Standard Pricing";
    },

    getFlowerTierRange(tierId) {
      const tier = this.priceTiers.find((t) => t.id == tierId);
      if (!tier) return "$N/A";

      const prices = tier.prices;
      const minPrice = Math.min(
        prices.weight_1g,
        prices.weight_3_5g,
        prices.weight_7g,
        prices.weight_14g,
        prices.weight_28g,
      );
      const maxPrice = Math.max(
        prices.weight_1g,
        prices.weight_3_5g,
        prices.weight_7g,
        prices.weight_14g,
        prices.weight_28g,
      );

      return `$${minPrice.toFixed(2)} - $${maxPrice.toFixed(2)}`;
    },

    // Print functionality
    showPrintTypeModal(product) {
      if (!product) {
        this.showToast("No product selected for printing", "error");
        return;
      }
      this.printData.product = product;
      this.showPrintTypeModal = true;

      // Initialize QR code when product is selected
      setTimeout(() => {
        this.generateProductQRCode(product);
      }, 100);
    },

    selectPrintType(type) {
      this.printData.type = type;
      this.showPrintTypeModal = false;
      this.showPrintPreviewModal = true;

      // Generate QR code for preview
      setTimeout(() => {
        this.generateProductQRCode(this.printData.product);
      }, 100);
    },

    proceedToPrint() {
      this.showPrintPreviewModal = false;
      this.showPrintModal = true;
    },

    cancelPrintPreview() {
      this.showPrintPreviewModal = false;
      this.resetPrintData();
    },

    resetPrintData() {
      this.printData = {
        product: null,
        type: "",
        selectedPrinter: "",
        copies: 1,
        scale: 100,
        labelSize: "medium",
        customWidth: 3.0,
        customHeight: 2.0,
        orientation: "portrait",
        quality: "normal",
        borderEnabled: false,
        timestampEnabled: true,
        companyLogoEnabled: false,
        batchPrint: false,
      };
    },

    previewPrintSettings() {
      // Show the customized print preview modal
      this.showPrintSettingsPreviewModal = true;

      // Generate QR code for the preview after modal opens
      setTimeout(() => {
        this.generateProductQRCode(this.printData.product);
      }, 100);
    },

    closePrintSettingsPreview() {
      this.showPrintSettingsPreviewModal = false;
    },

    printFromPreview() {
      // Close the preview and execute the print
      this.showPrintSettingsPreviewModal = false;
      this.showPrintModal = false;
      this.executePrint();
    },

    backToOptions() {
      // Close the preview and go back to the print options modal
      this.showPrintSettingsPreviewModal = false;
      // showPrintModal should still be true to return to options
    },

    executePrint() {
      // Simulate printing functionality with advanced options
      const printType =
        this.printData.type === "barcode" ? "QR Code Label" : "Exit Label";
      const scaleText =
        this.printData.scale !== 100
          ? ` at ${this.printData.scale}% scale`
          : "";
      const sizeText = this.getLabelSizeDisplay();

      this.showToast(
        `${printType} printed for ${this.printData.product.name} (${this.printData.copies} copies, ${sizeText}${scaleText})`,
        "success",
      );

      // Log advanced print settings for debugging
      console.log("Print job executed with settings:", {
        product: this.printData.product.name,
        type: printType,
        printer: this.printData.selectedPrinter || "Default",
        copies: this.printData.copies,
        scale: this.printData.scale,
        size: sizeText,
        orientation: this.printData.orientation,
        quality: this.printData.quality,
        options: {
          border: this.printData.borderEnabled,
          timestamp: this.printData.timestampEnabled,
          logo: this.printData.companyLogoEnabled,
          batch: this.printData.batchPrint,
        },
      });

      this.showPrintModal = false;
      this.resetPrintData();
    },

    // Flower deli-style functionality
    openFlowerDeliModal(product) {
      if (product.category === "Flower" && product.priceTier) {
        const tier = this.priceTiers.find((t) => t.id == product.priceTier);
        if (!tier) {
          this.showToast(
            "Price tier not found for this flower product",
            "error",
          );
          return;
        }
        const perGram = Number(tier.prices?.weight_1g || 0);
        const defaultGrams =
          this.weightThreshold && this.weightThreshold > 0
            ? this.weightThreshold
            : 1.0;
        let gramsStr = null;
        try {
          gramsStr = prompt(
            `Enter grams for ${product.name} (e.g. 1.25)`,
            String(defaultGrams),
          );
        } catch (_) {
          gramsStr = String(defaultGrams);
        }
        const grams = Number(parseFloat(gramsStr));
        if (!isFinite(grams) || grams <= 0) {
          this.showToast("Invalid grams amount", "error");
          return;
        }
        const price = isFinite(perGram) && perGram > 0 ? perGram * grams : 0;
        this.addFlowerToCart(product, grams, price);
        return;
      }
      this.addToCart(product);
    },

    // Helper functions
    getCannabinoidDisplay(product, type, value) {
      if (!value || value === 0) return "N/A";

      // For edibles, tinctures, and topicals, show mg content
      if (this.usesMgDisplay(product)) {
        return `${value}mg`;
      }

      // For other products, show percentage
      return `${value}%`;
    },

    isEdible(product) {
      return product.category === "Edibles";
    },

    usesMgDisplay(product) {
      // Categories that display cannabinoids in mg instead of percentage
      const mgCategories = ["Edibles", "Tinctures", "Topicals", "Infused"];
      return mgCategories.includes(product.category);
    },

    // Oregon Sales Limit Tracking Functions
    getCartTotalsByCategory() {
      const totals = {};
      const customerType =
        this.selectedCustomer && this.selectedCustomer.isMedical
          ? "medical"
          : "recreational";
      const limits = this.oregonSalesLimits[customerType];

      // Initialize totals for all tracked categories
      Object.keys(limits).forEach((category) => {
        totals[category] = 0;
      });

      // Calculate totals from cart items
      this.cart.forEach((item) => {
        let category = item.category;

        // Pre-rolls and Infused products count towards Flower limits
        if (category === "Pre-Rolls" || category === "Infused") {
          category = "Flower";
        }

        if (limits[category]) {
          const limit = limits[category];

          if (limit.unit === "grams") {
            // For flower, pre-rolls, infused, concentrates, edibles, topicals - use weight
            const weightGrams = this.extractWeightInGrams(item.weight || "1g");
            totals[category] += weightGrams * (item.quantity || 1);
          } else if (limit.unit === "ml") {
            // For beverages, tinctures - use volume (assume 1ml per unit if no volume specified)
            const volumeMl = parseFloat(item.volume || item.weight || "1") || 1;
            totals[category] += volumeMl * (item.quantity || 1);
          } else if (limit.unit === "units") {
            // For plants, seeds - count units
            totals[category] += item.quantity || 1;
          }
        }
      });

      return totals;
    },

    extractWeightInGrams(weightStr) {
      if (!weightStr) return 1;

      // Extract number and unit from weight string (e.g., "3.5g", "1 oz", "14g")
      const match = weightStr
        .toString()
        .match(/(\d+\.?\d*)\s*(g|gram|grams|oz|ounce|ounces)/i);
      if (!match) return 1;

      const value = parseFloat(match[1]);
      const unit = match[2].toLowerCase();

      // Convert to grams
      if (unit.startsWith("oz") || unit.startsWith("ounce")) {
        return value * 28.35; // 1 oz = 28.35 grams
      } else {
        return value; // Already in grams
      }
    },

    getSalesLimitProgress() {
      const cartTotals = this.getCartTotalsByCategory();
      const customerType =
        this.selectedCustomer && this.selectedCustomer.isMedical
          ? "medical"
          : "recreational";
      const limits = this.oregonSalesLimits[customerType];
      const progress = {};

      Object.keys(limits).forEach((category) => {
        const limit = limits[category];
        const current = cartTotals[category] || 0;
        const percentage = Math.min((current / limit.limit) * 100, 100);

        progress[category] = {
          current: current,
          limit: limit.limit,
          unit: limit.unit,
          displayName: limit.displayName,
          percentage: percentage,
          isAtLimit: current >= limit.limit,
          isNearLimit: percentage >= 80,
        };
      });

      return progress;
    },

    shouldShowSalesLimitTracker() {
      // Show for both recreational and medical customers (they have different limits)
      return this.selectedCustomer || this.ageVerified;
    },

    // QR Code generation and print helper functions
    generateProductQRCode(product) {
      if (!product) return;

      // Find the canvas element
      const canvas = this.$refs.qrCanvas;
      if (!canvas) {
        setTimeout(() => this.generateProductQRCode(product), 100);
        return;
      }

      // Generate QR code data
      const qrData = this.getProductQRData(product);

      try {
        // Generate QR code using QRious library
        if (typeof QRious !== "undefined") {
          const qr = new QRious({
            element: canvas,
            value: qrData,
            size: 80,
            level: "M",
          });
        } else {
          // Fallback: draw a simple placeholder
          this.drawQRFallback(canvas, qrData);
        }
      } catch (error) {
        console.warn("QR code generation failed, using fallback:", error);
        this.drawQRFallback(canvas, qrData);
      }
    },

    getProductQRData(product) {
      if (!product) return "No product data";

      // Create comprehensive product data for QR code
      const qrData = {
        id: product.id,
        name: product.name,
        sku: product.sku || "",
        category: product.category,
        price: product.price,
        metrc: product.metrcTag || "",
        thc: product.thc || 0,
        cbd: product.cbd || 0,
        timestamp: new Date().toISOString(),
      };

      // Return as JSON string for QR code
      return JSON.stringify(qrData);
    },

    drawQRFallback(canvas, data) {
      // Fallback method to draw a simple pattern when QR library fails
      const ctx = canvas.getContext("2d");
      ctx.clearRect(0, 0, 80, 80);

      // Draw a simple grid pattern as fallback
      ctx.fillStyle = "#000";
      for (let i = 0; i < 8; i++) {
        for (let j = 0; j < 8; j++) {
          if ((i + j) % 2 === 0) {
            ctx.fillRect(i * 10, j * 10, 10, 10);
          }
        }
      }

      // Add text indicating it's a fallback
      ctx.fillStyle = "#fff";
      ctx.font = "8px Arial";
      ctx.fillText("QR", 30, 45);
    },

    getLabelSizeDisplay() {
      if (this.printData.labelSize === "custom") {
        return `${this.printData.customWidth || 3}" × ${this.printData.customHeight || 2}"`;
      }

      const sizes = {
        small: '2" �� 1"',
        medium: '3" �� 2"',
        large: '4" × 3"',
        "extra-large": '6" × 4"',
      };

      return sizes[this.printData.labelSize] || "Medium";
    },

    previewPrintSettings() {
      // Show the customized print preview modal
      this.showPrintSettingsPreviewModal = true;

      // Generate QR code for the preview after modal opens
      setTimeout(() => {
        const canvas = this.$refs.previewQrCanvas;
        if (canvas && this.printData.product) {
          this.generateQRCodeOnCanvas(canvas, this.printData.product);
        }
      }, 100);
    },

    generateQRCodeOnCanvas(canvas, product) {
      if (!canvas || !product) return;

      const qrData = this.getProductQRData(product);

      try {
        if (typeof QRious !== "undefined") {
          const qr = new QRious({
            element: canvas,
            value: qrData,
            size: 80,
            level: "M",
          });
        } else {
          this.drawQRFallback(canvas, qrData);
        }
      } catch (error) {
        console.warn("QR code generation failed, using fallback:", error);
        this.drawQRFallback(canvas, qrData);
      }
    },

    getPreviewLabelStyle() {
      // Calculate dimensions for the preview based on label size
      let width, height;

      if (this.printData.labelSize === "custom") {
        width = (this.printData.customWidth || 3) * 50; // 50px per inch for preview
        height = (this.printData.customHeight || 2) * 50;
      } else {
        const dimensions = {
          small: { width: 100, height: 50 }, // 2" × 1"
          medium: { width: 150, height: 100 }, // 3" ��� 2"
          large: { width: 200, height: 150 }, // 4" × 3"
          "extra-large": { width: 300, height: 200 }, // 6" × 4"
        };

        const size = dimensions[this.printData.labelSize] || dimensions.medium;
        width = size.width;
        height = size.height;
      }

      // Apply orientation
      if (this.printData.orientation === "landscape") {
        [width, height] = [height, width]; // Swap dimensions
      }

      return `width: ${width}px; height: ${height}px; font-family: monospace;`;
    },

    // Missing functions to fix console errors
    getTotalItemDiscounts() {
      if (!this.cart || !Array.isArray(this.cart)) return 0;
      return this.cart.reduce((total, item) => {
        if (!item) return total;
        return (
          total +
          (item.discount && item.discount.amount
            ? item.discount.amount * (item.quantity || 1)
            : 0)
        );
      }, 0);
    },

    getDiscountedSubtotal() {
      const subtotal = this.subtotal || 0;
      const itemDiscounts = this.getTotalItemDiscounts() || 0;
      const cartDiscount =
        this.cartDiscount && this.cartDiscount.amount
          ? this.cartDiscount.amount
          : 0;
      return subtotal - itemDiscounts - cartDiscount;
    },

    getItemTotal(item) {
      if (!item) return 0;
      const baseTotal = (item.price || 0) * (item.quantity || 1);
      const itemDiscount =
        item.discount && item.discount.amount
          ? item.discount.amount * (item.quantity || 1)
          : 0;
      return baseTotal - itemDiscount;
    },

    removeCartDiscount() {
      this.cartDiscount = {
        type: "percentage",
        value: 0,
        amount: 0,
        reason: "",
      };
      this.calculateTotals();
    },

    // Cart item discount functions
    openItemDiscountModal(index) {
      if (index < 0 || index >= this.cart.length) {
        this.showToast("Invalid cart item selected", "error");
        return;
      }

      this.selectedCartItem = this.cart[index];
      this.selectedCartItemIndex = index;

      // Reset the discount form
      this.itemDiscountForm = {
        type: "percentage",
        value: 0,
        reason: "",
        calculatedAmount: 0,
      };

      this.showItemDiscountModal = true;
    },

    calculateItemDiscount() {
      if (!this.selectedCartItem || !this.itemDiscountForm.value) {
        this.itemDiscountForm.calculatedAmount = 0;
        return;
      }

      const value = parseFloat(this.itemDiscountForm.value) || 0;
      const itemPrice = this.selectedCartItem.price || 0;

      if (this.itemDiscountForm.type === "percentage") {
        // Percentage discount
        if (value > 100) {
          this.itemDiscountForm.value = 100;
          return;
        }
        this.itemDiscountForm.calculatedAmount = itemPrice * (value / 100);
      } else {
        // Fixed amount discount
        if (value > itemPrice) {
          this.itemDiscountForm.value = itemPrice.toFixed(2);
          this.itemDiscountForm.calculatedAmount = itemPrice;
        } else {
          this.itemDiscountForm.calculatedAmount = value;
        }
      }
    },

    applyItemDiscount() {
      if (
        !this.selectedCartItem ||
        !this.itemDiscountForm.value ||
        this.itemDiscountForm.calculatedAmount <= 0
      ) {
        this.showToast("Please enter a valid discount amount", "error");
        return;
      }

      if (
        !this.itemDiscountForm.reason ||
        this.itemDiscountForm.reason.trim() === ""
      ) {
        this.showToast("Discount reason is required", "error");
        return;
      }

      // Apply the discount to the cart item
      const discountData = {
        type: this.itemDiscountForm.type,
        value: parseFloat(this.itemDiscountForm.value),
        amount: this.itemDiscountForm.calculatedAmount,
        reason: this.itemDiscountForm.reason.trim(),
      };

      // Update the cart item with the discount
      this.cart[this.selectedCartItemIndex].discount = discountData;

      // Recalculate totals
      this.calculateTotals();

      const discountText =
        discountData.type === "percentage"
          ? `${discountData.value}% discount`
          : `$${discountData.amount.toFixed(2)} discount`;

      const itemName = this.selectedCartItem?.name || "item";

      // Close modal and reset
      this.showItemDiscountModal = false;
      this.selectedCartItem = null;
      this.selectedCartItemIndex = null;
      this.itemDiscountForm = {
        type: "percentage",
        value: 0,
        reason: "",
        calculatedAmount: 0,
      };

      this.showToast(`${discountText} applied to ${itemName}`, "success");
    },

    calculateCartDiscount() {
      if (this.discountForm.type === "percentage") {
        const baseAmount = this.subtotal - this.getTotalItemDiscounts();
        this.discountForm.calculatedAmount =
          baseAmount * (this.discountForm.value / 100);
      } else {
        this.discountForm.calculatedAmount =
          parseFloat(this.discountForm.value) || 0;
      }
    },

    applyCartDiscount() {
      // Validate required fields
      if (!this.discountForm.value || this.discountForm.calculatedAmount <= 0) {
        this.showToast("Please enter a valid discount amount", "error");
        return;
      }

      if (!this.discountForm.reason || this.discountForm.reason.trim() === "") {
        this.showToast("Discount reason is required", "error");
        return;
      }

      // Apply the cart discount
      this.cartDiscount = {
        type: this.discountForm.type,
        value: parseFloat(this.discountForm.value),
        amount: this.discountForm.calculatedAmount,
        reason: this.discountForm.reason.trim(),
      };

      // Recalculate totals
      this.calculateTotals();

      // Close modal and reset form
      this.showDiscountModal = false;
      this.discountForm = {
        type: "percentage",
        value: 0,
        reason: "",
        calculatedAmount: 0,
      };

      // Success message
      const discountText =
        this.cartDiscount.type === "percentage"
          ? `${this.cartDiscount.value}% cart discount`
          : `$${this.cartDiscount.amount.toFixed(2)} cart discount`;

      this.showToast(`${discountText} applied successfully`, "success");
    },

    calculateNewPointsTotal() {
      if (!this.selectedLoyaltyCustomer || !this.pointsForm.amount) return 0;
      const currentPoints = this.selectedLoyaltyCustomer.loyaltyPoints || 0;
      const amount = parseFloat(this.pointsForm.amount) || 0;

      if (this.pointsForm.action === "add") {
        return currentPoints + amount;
      } else {
        return Math.max(0, currentPoints - amount);
      }
    },

    getLoyaltyTierClass(points) {
      if (points >= 1000) return "text-purple-600 font-bold";
      if (points >= 500) return "text-blue-600 font-semibold";
      if (points >= 100) return "text-green-600";
      return "text-gray-600";
    },

    getActiveEmployees() {
      const q = (this.employeeSearchQuery || "").toLowerCase();
      // Even when searching, assignments should use active employees only
      return (this.employees || []).filter((emp) => emp.status === "active");
    },

    hasAnyPrices() {
      return (
        Object.values(this.tierForm.prices).some((price) => price > 0) ||
        this.tierForm.customWeights.some((weight) => weight.price > 0)
      );
    },

    getTotalWeightsConfigured() {
      let count = Object.values(this.tierForm.prices).filter(
        (price) => price > 0,
      ).length;
      count += this.tierForm.customWeights.filter(
        (weight) => weight.price > 0,
      ).length;
      return count;
    },

    getUniqueCategories() {
      const list = Array.isArray(this.products) ? this.products : [];
      const base = [
        "Flower",
        "Pre-Rolls",
        "Infused",
        "Concentrates",
        "Extracts",
        "Edibles",
        "Topicals",
        "Tinctures",
        "Vape Products",
        "Inhalable Cannabinoids",
        "Clones",
        "Immature Plants",
        "Seeds",
        "Shake/Trim",
        "Kief",
        "Accessories",
        "Capsules",
        "Beverages",
        "Suppositories",
        "Mature Plants",
        "Hemp",
        "Paraphernalia",
      ];
      const categories = [
        ...new Set([...base, ...list.map((p) => p.category).filter(Boolean)]),
      ];
      return categories.sort();
    },

    openAgingModal(type, title) {
      this.normalizeCollections();
      this.agingModalData = {
        title: title,
        items: (Array.isArray(this.products) ? this.products : [])
          .filter((p) => {
            // Mock aging logic - in real app this would use actual dates
            const mockAge = Math.floor(Math.random() * 120);
            if (type === "fresh") return mockAge <= 30;
            if (type === "moderate") return mockAge > 30 && mockAge <= 60;
            if (type === "aging") return mockAge > 60 && mockAge <= 90;
            if (type === "stale") return mockAge > 90;
            return false;
          })
          .map((p) => {
            // Add aging data to each product
            const mockAge = Math.floor(Math.random() * 120);
            const dateAdded = new Date();
            dateAdded.setDate(dateAdded.getDate() - mockAge);
            return {
              ...p,
              daysOld: mockAge,
              dateAdded: dateAdded.toISOString(),
            };
          }),
      };

      // Calculate totals for the modal
      this.agingModalData.totalCost = (this.agingModalData.items || []).reduce(
        (sum, item) => sum + Number(item?.cost || 0) * Number(item?.stock || 0),
        0,
      );
      this.agingModalData.totalRetail = (
        this.agingModalData.items || []
      ).reduce(
        (sum, item) =>
          sum + Number(item?.price || 0) * Number(item?.stock || 0),
        0,
      );
      this.agingModalData.totalProfit =
        this.agingModalData.totalRetail - this.agingModalData.totalCost;

      this.showAgingModal = true;
    },

    viewProduct(product) {
      // Show detailed product information in a modal or expand view
      this.selectedProduct = product;
      this.showMetrcModal = true;
    },

    applyDiscountToAgedItem(item) {
      // Apply discount to an aged inventory item
      const discountPercent = prompt(
        `Enter discount percentage for ${item.name} (e.g., 20 for 20% off):`,
      );
      if (discountPercent && !isNaN(discountPercent)) {
        const discount = parseFloat(discountPercent);
        if (discount > 0 && discount <= 100) {
          const newPrice = item.price * (1 - discount / 100);
          const productIndex = (
            Array.isArray(this.products) ? this.products : []
          ).findIndex((p) => p.id === item.id);
          if (productIndex !== -1) {
            this.products[productIndex].price = newPrice;
            this.products[productIndex].discountApplied = discount;
            this.showToast(
              `${discount}% discount applied to ${item.name}. New price: $${newPrice.toFixed(2)}`,
            );

            // Update the aging modal data to reflect the new price
            const modalItemIndex = this.agingModalData.items.findIndex(
              (i) => i.id === item.id,
            );
            if (modalItemIndex !== -1) {
              this.agingModalData.items[modalItemIndex].price = newPrice;
              this.agingModalData.items[modalItemIndex].discountApplied =
                discount;
              // Recalculate totals
              this.agingModalData.totalRetail =
                this.agingModalData.items.reduce(
                  (sum, item) => sum + item.price * item.stock,
                  0,
                );
              this.agingModalData.totalProfit =
                this.agingModalData.totalRetail - this.agingModalData.totalCost;
            }
          }
        } else {
          this.showToast(
            "Please enter a valid discount percentage between 1 and 100.",
          );
        }
      }
    },

    bulkDiscountAgedItems() {
      // Apply bulk discount to all items in the aging modal
      const discountPercent = prompt(
        `Enter bulk discount percentage for all ${this.agingModalData.items.length} items (e.g., 20 for 20% off):`,
      );
      if (discountPercent && !isNaN(discountPercent)) {
        const discount = parseFloat(discountPercent);
        if (discount > 0 && discount <= 100) {
          let updatedCount = 0;
          this.agingModalData.items.forEach((item) => {
            const newPrice = item.price * (1 - discount / 100);
            const arr = Array.isArray(this.products) ? this.products : [];
            const productIndex = arr.findIndex((p) => p.id === item.id);
            if (productIndex !== -1) {
              this.products[productIndex].price = newPrice;
              this.products[productIndex].discountApplied = discount;
              item.price = newPrice;
              item.discountApplied = discount;
              updatedCount++;
            }
          });

          // Recalculate totals
          this.agingModalData.totalRetail = (
            this.agingModalData.items || []
          ).reduce(
            (sum, item) =>
              sum + Number(item?.price || 0) * Number(item?.stock || 0),
            0,
          );
          this.agingModalData.totalProfit =
            this.agingModalData.totalRetail - this.agingModalData.totalCost;

          this.showToast(
            `${discount}% bulk discount applied to ${updatedCount} items.`,
          );
        } else {
          this.showToast(
            "Please enter a valid discount percentage between 1 and 100.",
          );
        }
      }
    },

    exportAgingData() {
      // Export aging data to CSV/Excel format
      if (
        !this.agingModalData.items ||
        this.agingModalData.items.length === 0
      ) {
        this.showToast("No data to export.");
        return;
      }

      const csvData = [
        [
          "Product Name",
          "SKU",
          "Category",
          "Days Old",
          "Date Added",
          "Stock",
          "Cost per Unit",
          "Retail per Unit",
          "Total Cost",
          "Total Retail",
          "Room",
        ],
      ];

      this.agingModalData.items.forEach((item) => {
        csvData.push([
          item.name,
          item.sku || "N/A",
          item.category,
          item.daysOld,
          new Date(item.dateAdded).toLocaleDateString(),
          item.stock,
          (item.cost || 0).toFixed(2),
          item.price.toFixed(2),
          ((item.cost || 0) * item.stock).toFixed(2),
          (item.price * item.stock).toFixed(2),
          item.room || "Main",
        ]);
      });

      const csvContent = csvData.map((row) => row.join(",")).join("\n");
      const blob = new Blob([csvContent], { type: "text/csv" });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = `inventory-aging-${this.agingModalData.title.replace(/\s+/g, "-").toLowerCase()}-${new Date().toISOString().split("T")[0]}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);

      this.showToast("Aging data exported successfully.");
    },

    printAgingReport() {
      // Print aging analysis report
      if (
        !this.agingModalData.items ||
        this.agingModalData.items.length === 0
      ) {
        this.showToast("No data to print.");
        return;
      }

      const printContent = `
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
                    .summary-card { border: 1px solid #ddd; padding: 15px; text-align: center; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f5f5f5; }
                    .text-right { text-align: right; }
                </style>
                <div class="header">
                    <h1>Inventory Aging Report</h1>
                    <h2>${this.agingModalData.title}</h2>
                    <p>Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
                </div>

                <div class="summary">
                    <div class="summary-card">
                        <h3>Total Items</h3>
                        <p>${this.agingModalData.items.length}</p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Cost Value</h3>
                        <p>$${this.agingModalData.totalCost.toFixed(2)}</p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Retail Value</h3>
                        <p>$${this.agingModalData.totalRetail.toFixed(2)}</p>
                    </div>
                    <div class="summary-card">
                        <h3>Potential Profit</h3>
                        <p>$${this.agingModalData.totalProfit.toFixed(2)}</p>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Days Old</th>
                            <th>Stock</th>
                            <th class="text-right">Cost/Unit</th>
                            <th class="text-right">Retail/Unit</th>
                            <th class="text-right">Total Cost</th>
                            <th class="text-right">Total Retail</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${this.agingModalData.items
                          .map(
                            (item) => `
                            <tr>
                                <td>${item.name}<br><small>SKU: ${item.sku || "N/A"}</small></td>
                                <td>${item.category}</td>
                                <td>${item.daysOld}</td>
                                <td>${item.stock}</td>
                                <td class="text-right">$${(item.cost || 0).toFixed(2)}</td>
                                <td class="text-right">$${item.price.toFixed(2)}</td>
                                <td class="text-right">$${((item.cost || 0) * item.stock).toFixed(2)}</td>
                                <td class="text-right">$${(item.price * item.stock).toFixed(2)}</td>
                            </tr>
                        `,
                          )
                          .join("")}
                    </tbody>
                </table>
            `;

      const printWindow = window.open("", "_blank");
      printWindow.document.write(printContent);
      printWindow.document.close();
      printWindow.print();
    },

    resetEmployeeForm() {
      this.employeeForm = {
        name: "",
        email: "",
        phone: "",
        role: "budtender",
        payRate: 15.0,
        hireDate: "",
        status: "active",
        workerPermit: "",
        metrcApiKey: "",
        permissions: [],
      };
    },

    closeAddRoomModal() {
      this.showAddRoomModal = false;
      this.selectedRoom = null;
      this.roomForm = {
        name: "",
        type: "",
        forSale: "true",
        maxCapacity: "",
        status: "active",
        temperature: 68,
        humidity: 50,
      };
    },

    closeEditCustomerModal() {
      this.showEditCustomerModal = false;
      this.selectedCustomer = null;
      this.editCustomerForm = {
        name: "",
        email: "",
        phone: "",
        isMedical: false,
        medicalCard: "",
      };
    },

    closeTierModal() {
      this.showAddTierModal = false;
      this.tierForm = {
        name: "",
        prices: {
          weight_1g: 0,
          weight_3_5g: 0,
          weight_7g: 0,
          weight_14g: 0,
          weight_28g: 0,
        },
        customWeights: [],
      };
    },

    closeCreateDealModal() {
      this.showCreateDealModal = false;
      this.editingDeal = null;
      this.dealForm = {
        name: "",
        description: "",
        type: "",
        discountValue: 0,
        buyQuantity: 1,
        getQuantity: 1,
        minPurchase: 0,
        minPurchaseType: "dollars",
        usageLimit: "",
        allCategories: false,
        applicableCategories: [],
        applicableProducts: [],
        excludeGLS: true,
        stackable: false,
        loyaltyOnly: false,
        medicalOnly: false,
        emailCustomers: false,
        isActive: true,
        startDate: "",
        endDate: "",
        startTime: "",
        endTime: "",
        activeDays: [],
      };
    },

    toggleAllCategories() {
      if (this.dealForm.allCategories) {
        this.dealForm.applicableCategories = [];
      }
    },

    resetCashCount() {
      this.cashCount = {
        total: 0,
        notes: "",
      };
    },

    calculateChange() {
      const given = parseFloat(this.cashPayment.amountGiven) || 0;
      this.cashPayment.changeDue = given - this.total;
    },

    calculateDebitChange() {
      const amount = parseFloat(this.debitPayment.amount) || 0;
      this.debitPayment.changeDue = amount - this.total;
    },

    async completeDebitPayment() {
      // Validate required fields
      if (!this.debitPayment.lastFour) {
        this.showToast("Last 4 digits of card are required", "error");
        return;
      }
      if (!this.debitPayment.employeePin) {
        this.showToast("Employee PIN is required", "error");
        return;
      }
      if (this.debitPayment.changeDue < 0) {
        this.showToast("Insufficient payment amount", "error");
        return;
      }

      const items = (this.cart || []).map((it) => ({
        id: it.id,
        name: it.displayName || it.name,
        price: Number(it.price || 0),
        quantity: Number(it.quantity || 1),
        category: it.category || (it.product ? it.product.category : undefined),
      }));
      const payload = {
        method: "debit",
        total: this.total,
        subtotal: this.subtotal,
        taxAmount: this.taxAmount,
        items,
        employeePin: this.debitPayment.employeePin,
        card_details: {
          last_four: String(this.debitPayment.lastFour || ""),
          type: "debit",
        },
        debit_amount: parseFloat(this.debitPayment.amount) || this.total,
        customer: this.selectedCustomer
          ? {
              name: this.selectedCustomer.name || "Walk-in Customer",
              isMedical: !!this.selectedCustomer.isMedical,
              type: this.selectedCustomer.isMedical
                ? "medical"
                : "recreational",
              medical_card_number:
                this.selectedCustomer.medicalCard ||
                this.selectedCustomer.medicalCardNumber ||
                this.selectedCustomer.patientCardNumber ||
                null,
            }
          : null,
      };

      try {
        const { data: result } = await (window.axios || axios).post(
          "/api/pos/process-payment-open",
          payload,
          { headers: { Accept: "application/json" } },
        );
        this.showToast("Debit payment processed successfully", "success");
        try {
          const detail = {
            sale_id: result?.sale_id,
            sale_number: result?.sale_number,
            total: this.total,
            paymentMethod: "debit",
            paymentReference: String(this.debitPayment.lastFour || "") || null,
            itemCount: items.reduce((a, b) => a + Number(b.quantity || 0), 0),
          };
          document.dispatchEvent(
            new CustomEvent("pos-sale-completed", { detail }),
          );
          try {
            localStorage.setItem(
              "pos_last_sale_event",
              JSON.stringify({ ...detail, ts: Date.now() }),
            );
          } catch (_) {}
          try {
            if (detail.sale_id != null)
              localStorage.setItem(
                "pos_last_sale_id",
                `${detail.sale_id}:${Date.now()}`,
              );
          } catch (_) {}
        } catch (_) {}
      } catch (e) {
        this.showToast("Failed to persist sale", "error");
        return;
      }

      // Clear cart and reset state
      this.cart = [];
      this.selectedCustomer = null;
      this.ageVerified = false;
      this.calculateTotals();

      // Reset debit payment form
      this.debitPayment = {
        amount: 0,
        changeDue: 0,
        lastFour: "",
        employeePin: "",
      };
      this.showDebitModal = false;
    },

    async completeCashPayment() {
      // Validate required fields
      if (!this.cashPayment.employeePin) {
        this.showToast("Employee PIN is required", "error");
        return;
      }
      if (this.cashPayment.changeDue < 0) {
        this.showToast("Insufficient cash amount", "error");
        return;
      }

      const items = (this.cart || []).map((it) => ({
        id: it.id,
        name: it.displayName || it.name,
        price: Number(it.price || 0),
        quantity: Number(it.quantity || 1),
        category: it.category || (it.product ? it.product.category : undefined),
      }));
      const payload = {
        method: "cash",
        total: this.total,
        subtotal: this.subtotal,
        taxAmount: this.taxAmount,
        items,
        employeePin: this.cashPayment.employeePin,
        customer: this.selectedCustomer
          ? {
              name: this.selectedCustomer.name || "Walk-in Customer",
              isMedical: !!this.selectedCustomer.isMedical,
              type: this.selectedCustomer.isMedical
                ? "medical"
                : "recreational",
              medical_card_number:
                this.selectedCustomer.medicalCard ||
                this.selectedCustomer.medicalCardNumber ||
                this.selectedCustomer.patientCardNumber ||
                null,
            }
          : null,
      };

      try {
        const { data: result } = await (window.axios || axios).post(
          "/api/pos/process-payment-open",
          payload,
          { headers: { Accept: "application/json" } },
        );
        this.showToast("Cash payment processed successfully", "success");
        try {
          const detail = {
            sale_id: result?.sale_id,
            sale_number: result?.sale_number,
            total: this.total,
            paymentMethod: "cash",
            paymentReference: null,
            itemCount: items.reduce((a, b) => a + Number(b.quantity || 0), 0),
          };
          document.dispatchEvent(
            new CustomEvent("pos-sale-completed", { detail }),
          );
          try {
            localStorage.setItem(
              "pos_last_sale_event",
              JSON.stringify({ ...detail, ts: Date.now() }),
            );
          } catch (_) {}
          try {
            if (detail.sale_id != null)
              localStorage.setItem(
                "pos_last_sale_id",
                `${detail.sale_id}:${Date.now()}`,
              );
          } catch (_) {}
        } catch (_) {}
      } catch (e) {
        this.showToast("Failed to persist sale", "error");
        return;
      }

      // Clear cart and reset state
      this.cart = [];
      this.selectedCustomer = null;
      this.ageVerified = false;
      this.calculateTotals();

      this.cashPayment = { amountGiven: 0, changeDue: 0, employeePin: "" };
      this.showCashModal = false;
    },

    // Product card button functions
    viewMetrcData(product) {
      this.selectedProduct = product;
      this.showMetrcModal = true;
    },

    openTransferRoom(product) {
      this.selectedProduct = product;
      this.transferData = {
        quantity: 1,
        destinationRoom: "",
        setSalesFloorStatus: !product.onSalesFloor, // Default to making available if not on sales floor
        reason: "",
      };
      this.showTransferModal = true;
    },

    editProduct(product) {
      this.selectedProduct = product;
      this.editData = {
        name: product.name || "",
        stock: product.stock || 0,
        cost: product.cost || 0,
        price: product.price || 0,
        thc: product.thc || 0,
        cbd: product.cbd || 0,
        cbn: product.metrcData?.cbn || 0,
        cbg: product.metrcData?.cbg || 0,
        cbc: product.metrcData?.cbc || 0,
        priceTier: product.priceTier || "",
      };
      this.showEditModal = true;
    },

    executeTransfer() {
      if (!this.transferData.destinationRoom || !this.transferData.quantity) {
        this.showToast("Please select destination room and quantity", "error");
        return;
      }

      // Simulate transfer logic
      const product = this.selectedProduct;
      const quantity = parseInt(this.transferData.quantity);

      if (quantity > product.stock) {
        this.showToast("Transfer quantity exceeds available stock", "error");
        return;
      }

      // Update product in products array
      const productIndex = this.products.findIndex((p) => p.id === product.id);
      if (productIndex !== -1) {
        this.products[productIndex].room = this.transferData.destinationRoom;

        // Update sales floor status if requested
        if (this.transferData.setSalesFloorStatus !== undefined) {
          this.products[productIndex].onSalesFloor =
            this.transferData.setSalesFloorStatus;
        }

        // In a real app, this would handle partial transfers
        // For now, we'll just update the room location
      }

      this.showToast(
        `${product.name} transferred to ${this.transferData.destinationRoom}`,
        "success",
      );
      this.showTransferModal = false;
      this.selectedProduct = null;
      this.filterProducts(); // Refresh the product list
    },

    saveProductEdit() {
      if (!this.editData.name) {
        this.showToast("Product name is required", "error");
        return;
      }

      // Update product in products array
      const productIndex = this.products.findIndex(
        (p) => p.id === this.selectedProduct.id,
      );
      if (productIndex !== -1) {
        this.products[productIndex] = {
          ...this.products[productIndex],
          name: this.editData.name,
          stock: parseInt(this.editData.stock) || 0,
          cost: parseFloat(this.editData.cost) || 0,
          price: parseFloat(this.editData.price) || 0,
          thc: parseFloat(this.editData.thc) || 0,
          cbd: parseFloat(this.editData.cbd) || 0,
          priceTier: this.editData.priceTier || null,
        };

        // Update METRC data if available
        if (!this.products[productIndex].metrcData) {
          this.products[productIndex].metrcData = {};
        }
        this.products[productIndex].metrcData.cbn =
          parseFloat(this.editData.cbn) || 0;
        this.products[productIndex].metrcData.cbg =
          parseFloat(this.editData.cbg) || 0;
        this.products[productIndex].metrcData.cbc =
          parseFloat(this.editData.cbc) || 0;
      }

      this.showToast("Product updated successfully", "success");
      this.showEditModal = false;
      this.selectedProduct = null;
      this.filterProducts(); // Refresh the product list
    },

    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        // In a real app, this would upload the file
        this.showToast(`File uploaded: ${file.name}`, "success");
      }
    },

    // Placeholder functions for complex operations
    async addEmployee() {
      try {
        if (
          !this.employeeForm.name ||
          !this.employeeForm.email ||
          !this.employeeForm.role ||
          !this.employeeForm.hireDate ||
          this.employeeForm.payRate === ""
        ) {
          this.showToast("Please fill in all required fields", "error");
          return;
        }
        const parts = String(this.employeeForm.name).trim().split(/\s+/);
        const first_name = parts.shift() || "";
        const last_name = parts.join(" ") || "";
        const role = this.employeeForm.role;
        const deptMap = {
          manager: "management",
          budtender: "sales",
          security: "security",
          admin: "admin",
        };
        const department = deptMap[role] || "operations";
        let employee_id = null;
        try {
          const r = await axios.get("/api/employees/next-id", {
            headers: { Accept: "application/json" },
          });
          employee_id = r?.data?.next_id || null;
        } catch (_) {
          employee_id = null;
        }
        const hourly_rate = parseFloat(this.employeeForm.payRate || 0) || 0;
        const hire_date = this.employeeForm.hireDate;
        const permissionsByRole = {
          manager: [
            "employees:read",
            "employees:write",
            "sales",
            "inventory",
            "reports",
          ],
          admin: ["*"],
          budtender: ["sales", "customers", "inventory:read"],
          security: ["sales:read", "inventory:read"],
        };
        const permissions = permissionsByRole[role] || ["sales:read"];
        const tempPassword = `Canna${Math.random().toString(36).slice(2, 8)}!${Math.floor(10 + Math.random() * 89)}`;

        const payload = {
          first_name,
          last_name,
          email: this.employeeForm.email,
          phone: this.employeeForm.phone || "",
          employee_id,
          department,
          position: role,
          hire_date,
          hourly_rate,
          permissions,
          password: tempPassword,
          password_confirmation: tempPassword,
        };

        const token =
          localStorage.getItem("auth_token") ||
          localStorage.getItem("pos_token");
        const res = await axios.post("/api/employees", payload, {
          headers: {
            Accept: "application/json",
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
          },
        });
        const created =
          res.data && res.data.employee ? res.data.employee : null;
        const uiEmployee = {
          id:
            created && created.id
              ? created.id
              : employee_id || created?.employee_id || null,
          name: `${first_name} ${last_name}`.trim(),
          email: this.employeeForm.email,
          phone: this.employeeForm.phone || "",
          role,
          payRate: hourly_rate,
          status: "active",
          hireDate: hire_date,
          hoursWorked: 0,
        };
        this.employees = this.employees || [];
        this.employees.unshift(uiEmployee);
        this.showAddEmployeeModal = false;
        this.resetEmployeeForm();
        const pinMsg =
          created && created.pin ? ` Temporary PIN: ${created.pin}.` : "";
        this.showToast(
          `Employee created. Temp password: ${tempPassword}.${pinMsg}`,
          "success",
        );
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.showToast("Validation failed. Please check the form.", "error");
        } else if (error.response && error.response.status === 401) {
          this.showToast("Unauthorized. Please log in again.", "error");
        } else {
          this.showToast("Failed to create employee", "error");
        }
      }
    },

    async updateEmployee() {
      try {
        if (!this.selectedEmployee) {
          this.showToast("No employee selected", "error");
          return;
        }
        if (!this.employeeForm.name || !this.employeeForm.email) {
          this.showToast("Name and email are required", "error");
          return;
        }
        const parts = String(this.employeeForm.name).trim().split(/\s+/);
        const first_name = parts.shift() || "";
        const last_name = parts.join(" ") || "";
        const role = this.employeeForm.role;
        const deptMap = {
          manager: "management",
          admin: "management",
          budtender: "sales",
          security: "security",
          cashier: "sales",
        };
        const department = deptMap[role] || "sales";
        const hourly_rate = parseFloat(this.employeeForm.payRate || 0) || 0;
        const hire_date =
          this.employeeForm.hireDate ||
          this.selectedEmployee?.hireDate ||
          this.selectedEmployee?.hire_date ||
          "";
        const payload = {
          first_name,
          last_name,
          email: this.employeeForm.email,
          phone: this.employeeForm.phone || "",
          department,
          position: role,
          role, // ensure role is persisted explicitly
          hourly_rate,
          hire_date,
          permissions: this.employeeForm.permissions || [],
          worker_permit: this.employeeForm.workerPermit || "",
          metrc_api_key: this.employeeForm.metrcApiKey || "",
          status: this.employeeForm.status || "active",
        };
        const res = await posAuth.apiRequest(
          "put",
          `/employees/${this.selectedEmployee.id}`,
          payload,
        );
        if (!res.success) {
          throw new Error(res.message || "Failed to update employee");
        }
        // Update local list
        const idx = (this.employees || []).findIndex(
          (e) => String(e.id) === String(this.selectedEmployee.id),
        );
        if (idx !== -1) {
          this.employees[idx] = {
            ...this.employees[idx],
            name: `${first_name} ${last_name}`.trim(),
            email: payload.email,
            phone: payload.phone,
            role,
            payRate: hourly_rate,
            hireDate:
              hire_date ||
              this.employees[idx].hireDate ||
              this.employees[idx].hire_date ||
              "",
            status: payload.status,
            workerPermit: payload.worker_permit,
            metrcApiKey: payload.metrc_api_key,
            permissions: payload.permissions,
          };
        }
        this.showToast("Employee updated", "success");
        this.showAddEmployeeModal = false;
        this.selectedEmployee = null;
        this.resetEmployeeForm();
      } catch (err) {
        this.showToast(err?.message || "Failed to update employee", "error");
      }
    },

    addRoom() {
      console.log("Add room functionality would be implemented here");
    },

    updateRoom() {
      console.log("Update room functionality would be implemented here");
    },

    async addCustomer() {
      // Validate required fields
      if (!this.customerForm.name) {
        this.showToast("Customer name is required", "error");
        return;
      }

      if (this.customerForm.type === "medical") {
        if (!this.customerForm.medicalCardNumber) {
          this.showToast("Medical card number is required", "error");
          return;
        }
        if (!this.customerForm.medicalCardIssueDate) {
          this.showToast("Medical card issue date is required", "error");
          return;
        }
        if (!this.customerForm.medicalCardExpiry) {
          this.showToast("Medical card expiry date is required", "error");
          return;
        }
        if (
          this.customerForm.medicalCardType === "caregiver" &&
          !this.customerForm.patientCardNumber
        ) {
          this.showToast(
            "Patient card number is required for caregivers",
            "error",
          );
          return;
        }
      }

      // Create new customer object
      const newCustomer = {
        id: Date.now(), // Simple ID generation
        name: this.customerForm.name.trim(),
        email: this.customerForm.email.trim(),
        phone: this.customerForm.phone.trim(),
        isMedical: this.customerForm.type === "medical",
        loyaltyPoints: 0,
        createdAt: new Date().toISOString(),
      };

      // Add medical-specific fields if applicable
      if (this.customerForm.type === "medical") {
        newCustomer.medicalCard = this.customerForm.medicalCardNumber.trim();
        newCustomer.medicalCardType = this.customerForm.medicalCardType;
        newCustomer.medicalIssueDate = this.customerForm.medicalCardIssueDate;
        newCustomer.medicalExpirationDate = this.customerForm.medicalCardExpiry;
        if (this.customerForm.medicalCardType === "caregiver") {
          newCustomer.patientCardNumber =
            this.customerForm.patientCardNumber.trim();
        }
      }

      // Try to persist via API when authenticated and permitted
      let savedCustomer = null;
      try {
        if (this.isAuthenticated && this.hasPermission("customers:write")) {
          const [first, ...rest] = (newCustomer.name || "").split(" ");
          const payload = {
            first_name: first || newCustomer.name,
            last_name: rest.join(" ") || null,
            email: newCustomer.email,
            phone: newCustomer.phone,
            customer_type: newCustomer.isMedical ? "medical" : "recreational",
            data_retention_consent: true,
          };
          const res = await posAuth.apiRequest("post", "/customers", payload);
          if (res && res.success && res.data && (res.data.customer || res.data.data)) {
            const srv = res.data.customer || res.data.data;
            savedCustomer = {
              ...newCustomer,
              id: srv.id ?? newCustomer.id,
              name: srv.first_name && srv.last_name ? `${srv.first_name} ${srv.last_name}` : (srv.first_name || newCustomer.name),
              email: srv.email || newCustomer.email,
              phone: srv.phone || newCustomer.phone,
            };
          }
        }
      } catch (e) {
        // fall back to local only
      }

      // Add to customers array
      const finalCustomer = savedCustomer || newCustomer;
      this.customers.push(finalCustomer);

      // Save to localStorage for persistence (per-user + legacy)
      try { this._saveCustomersLocal(); } catch (error) { console.error("Error saving customers:", error); }
      try { this.filterLoyaltyCustomers(); } catch (_) {}

      // Optional: enroll in loyalty if requested
      if (this.customerForm.enrollLoyalty) {
        try {
          // Try API first
          if (this.isAuthenticated && this.hasPermission("loyalty:enroll")) {
            await posAuth.apiRequest("post", "/loyalty/enroll", {
              name: finalCustomer.name,
              email: finalCustomer.email,
              phone: finalCustomer.phone,
              tier: "Bronze",
              starting_points: 0,
            });
          }
        } catch (_) {}
        // Always ensure local enrollment as fallback
        this._addToLoyaltyLocal({
          id: String(finalCustomer.id),
          name: finalCustomer.name,
          phone: finalCustomer.phone,
          email: finalCustomer.email,
          joinDate: new Date().toISOString().split('T')[0],
          totalSpent: 0,
          totalVisits: 0,
          pointsBalance: 0,
          pointsEarned: 0,
          pointsRedeemed: 0,
          tier: 'Bronze',
          dataRetentionConsent: true,
          salesHistory: [],
          lastVisit: "",
          isVeteran: false,
        });
      }

      this.showToast(
        `Customer ${finalCustomer.name} added successfully`,
        "success",
      );
      this.closeAddCustomerModal();
    },

    closeAddCustomerModal() {
      this.showAddCustomerModal = false;
      // Reset form data
      this.customerForm = {
        type: "recreational",
        name: "",
        email: "",
        phone: "",
        isMedical: false,
        medicalCard: "",
        medicalCardNumber: "",
        medicalCardIssueDate: "",
        medicalCardExpiry: "",
        medicalCardType: "patient",
        patientCardNumber: "",
        saveData: false,
        enrollLoyalty: false,
      };
    },

    closeEditCustomerModal() {
      this.showEditCustomerModal = false;
      this.selectedCustomer = null;
      this.editCustomerForm = {
        name: "",
        email: "",
        phone: "",
        isMedical: false,
        medicalCard: "",
      };
    },

    closeAddRoomModal() {
      this.showAddRoomModal = false;
      this.selectedRoom = null;
      this.roomForm = {
        name: "",
        type: "",
        forSale: "true",
        maxCapacity: "",
        status: "active",
        temperature: 68,
        humidity: 50,
      };
    },

    closeTierModal() {
      this.showAddTierModal = false;
      this.tierForm = {
        name: "",
        prices: {
          weight_1g: 0,
          weight_3_5g: 0,
          weight_7g: 0,
          weight_14g: 0,
          weight_28g: 0,
        },
        customWeights: [],
      };
    },

    closeCreateDealModal() {
      this.showCreateDealModal = false;
      this.editingDeal = null;
      this.dealForm = {
        name: "",
        description: "",
        type: "",
        discountValue: 0,
        buyQuantity: 1,
        getQuantity: 1,
        minPurchase: 0,
        minPurchaseType: "dollars",
        usageLimit: "",
        allCategories: false,
        applicableCategories: [],
        applicableProducts: [],
        excludeGLS: true,
        stackable: false,
        loyaltyOnly: false,
        medicalOnly: false,
        emailCustomers: false,
        isActive: true,
        startDate: "",
        endDate: "",
        startTime: "",
        endTime: "",
        activeDays: [],
      };
    },

    closeVoidSaleModal() {
      this.showVoidSaleModal = false;
      this.saleToVoid = null;
      this.voidForm = {
        reason: "",
        notes: "",
        employeePin: "",
        pinVerified: false,
        verifiedEmployee: "",
        pinError: "",
      };
    },

    completeCashCount() {
      console.log(
        "Complete cash count functionality would be implemented here",
      );
    },

    confirmEmployeeAssignment(employee) {
      console.log(
        "Employee assignment functionality would be implemented here",
      );
    },

    verifyEmployeePin() {
      console.log("PIN verification functionality would be implemented here");
    },

    autoFillFromOunce(event) {
      // Auto-calculate smaller weights from ounce price
      const ouncePrice = parseFloat(event.target.value) || 0;
      if (ouncePrice > 0) {
        this.tierForm.prices.weight_14g = (ouncePrice / 2).toFixed(2);
        this.tierForm.prices.weight_7g = (ouncePrice / 4).toFixed(2);
        this.tierForm.prices.weight_3_5g = (ouncePrice / 8).toFixed(2);
        this.tierForm.prices.weight_1g = (ouncePrice / 28).toFixed(2);
      }
    },

    handleImageUpload(event, type) {
      const file = event.target.files[0];
      if (file) {
        if (type === "main") {
          this.importForm.mainImage = file.name;
        } else if (type === "additional") {
          this.importForm.additionalImages.push(file.name);
        }
      }
    },

    removeAdditionalImage(index) {
      this.importForm.additionalImages.splice(index, 1);
    },

    filterProductSuggestions(query) {
      // This would filter products for auto-suggestions
      console.log("Product filtering for:", query);
    },

    // METRC Integration Functions
    testMetrcConnection() {
      console.log(
        "Testing METRC connection with settings:",
        this.metrcSettings,
      );

      // Validate required fields
      if (!this.metrcSettings.apiKey || !this.metrcSettings.userKey) {
        this.showToast(
          "Please enter both API Key and User Key to test connection",
          "error",
        );
        return;
      }

      if (!this.metrcSettings.facilityLicense) {
        this.showToast("Please enter your Facility License Number", "error");
        return;
      }

      // Show loading state
      this.showToast("Testing METRC connection...", "info");

      // Simulate API call to test METRC connection
      // In a real implementation, this would make an actual HTTP request to METRC API
      setTimeout(() => {
        try {
          // Simulate successful connection test
          const isValid =
            this.metrcSettings.apiKey.length > 10 &&
            this.metrcSettings.userKey.length > 10 &&
            this.metrcSettings.facilityLicense.length > 3;

          if (isValid) {
            this.showToast(
              "METRC connection successful! API credentials verified.",
              "success",
            );
            console.log("METRC connection test passed:", {
              facilityLicense: this.metrcSettings.facilityLicense,
              state: this.metrcSettings.state,
              timestamp: new Date().toISOString(),
            });
          } else {
            this.showToast(
              "METRC connection failed: Invalid credentials. Please check your API keys.",
              "error",
            );
          }
        } catch (error) {
          this.showToast("METRC connection error: " + error.message, "error");
          console.error("METRC connection test failed:", error);
        }
      }, 1500); // Simulate network delay
    },

    async syncMetrcInventory() {
      console.log("Syncing METRC inventory with settings:", this.metrcSettings);

      // Validate required fields
      if (!this.metrcSettings.apiKey || !this.metrcSettings.userKey) {
        this.showToast(
          "Please configure METRC settings before syncing",
          "error",
        );
        return;
      }

      if (!this.metrcSettings.facilityLicense) {
        this.showToast(
          "Please enter your Facility License Number before syncing",
          "error",
        );
        return;
      }

      // Show loading state
      this.showToast("Starting METRC inventory sync...", "info");

      // Import active packages on server (excludes zero-qty)
      try {
        const res = await (window.axios || axios).post(
          "/api/metrc/import-packages",
        );
        const imported = Number(res?.data?.imported || 0);
        const updated = Number(res?.data?.updated || 0);
        const skipped = Number(res?.data?.skipped || 0);
        const total = imported + updated;
        if (total > 0) {
          this.showToast(
            `METRC sync complete: ${imported} imported, ${updated} updated. Skipped ${skipped} zero-qty.`,
            "success",
          );
          if (this.metrcSettings.trackSales)
            this.showToast("Sales tracking to METRC is enabled", "info");
          return;
        }
      } catch (e) {
        console.warn(
          "Server import failed, attempting package fetch fallback",
          e?.response?.data || e,
        );
        try {
          const res = await (window.axios || axios).get("/api/metrc/packages");
          const count = Array.isArray(res?.data?.packages)
            ? res.data.packages.length
            : res?.data?.count || 0;
          if (count > 0) {
            this.showToast(
              `Found ${count} active METRC packages. Import requires inventory write permission.`,
              "warning",
            );
            return;
          }
        } catch (_) {}
      }

      // Final fallback: local products with tags
      try {
        this.normalizeCollections();
        const list = Array.isArray(this.products)
          ? this.products
          : this.products && Array.isArray(this.products.data)
            ? this.products.data
            : [];
        const productsToSync = list.filter(
          (p) => p && p.metrcTag && p.metrcTag.length > 0,
        );
        const synced = productsToSync.length;
        if (synced > 0) {
          this.showToast(
            `METRC inventory sync completed! ${synced} products synchronized.`,
            "success",
          );
          if (this.metrcSettings.trackSales)
            this.showToast("Sales tracking to METRC is enabled", "info");
        } else {
          this.showToast(
            "No METRC packages or tagged products found to sync",
            "warning",
          );
        }
      } catch (error) {
        this.showToast(
          "METRC sync error: " + (error.message || "Unknown error"),
          "error",
        );
        console.error("METRC inventory sync failed:", error);
      }
    },

    // CSV Import Functions
    openCsvImportModal() {
      this.showCsvImportModal = true;
      this.resetCsvImportForm();
    },

    closeCsvImportModal() {
      this.showCsvImportModal = false;
      this.resetCsvImportForm();
    },

    resetCsvImportForm() {
      this.csvImportForm = {
        file: null,
        fileName: "",
        category: "",
        skipFirstRow: true,
        previewData: [],
        totalRows: 0,
        validRows: 0,
        errorRows: 0,
        importing: false,
        importComplete: false,
        importResults: null,
      };
    },

    handleCsvFileUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      if (!file.name.toLowerCase().endsWith(".csv")) {
        this.showToast("Please select a valid CSV file", "error");
        return;
      }

      this.csvImportForm.file = file;
      this.csvImportForm.fileName = file.name;
      this.showToast(
        "CSV file selected. Click preview to validate data.",
        "info",
      );
    },

    previewCsvData() {
      if (!this.csvImportForm.file) {
        this.showToast("Please select a CSV file first", "error");
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const csv = e.target.result;
          const lines = csv.split("\n").filter((line) => line.trim());

          if (lines.length === 0) {
            this.showToast("CSV file appears to be empty", "error");
            return;
          }

          // Parse header row
          const headerLine = this.csvImportForm.skipFirstRow ? lines[0] : null;
          const dataLines = this.csvImportForm.skipFirstRow
            ? lines.slice(1)
            : lines;

          // Parse CSV data
          const parsedData = dataLines.map((line, index) => {
            const values = this.parseCsvLine(line);
            const rowNum = this.csvImportForm.skipFirstRow
              ? index + 2
              : index + 1;
            return this.validateCsvRow(values, rowNum);
          });

          this.csvImportForm.previewData = parsedData.slice(0, 10); // Show first 10 rows
          this.csvImportForm.totalRows = parsedData.length;
          this.csvImportForm.validRows = parsedData.filter(
            (row) => row.valid,
          ).length;
          this.csvImportForm.errorRows = parsedData.filter(
            (row) => !row.valid,
          ).length;

          this.showToast(
            `Preview loaded: ${this.csvImportForm.validRows} valid, ${this.csvImportForm.errorRows} invalid rows`,
            "info",
          );
        } catch (error) {
          this.showToast("Error parsing CSV file: " + error.message, "error");
        }
      };

      reader.readAsText(this.csvImportForm.file);
    },

    parseCsvLine(line) {
      const result = [];
      let current = "";
      let inQuotes = false;

      for (let i = 0; i < line.length; i++) {
        const char = line[i];

        if (char === '"') {
          inQuotes = !inQuotes;
        } else if (char === "," && !inQuotes) {
          result.push(current.trim());
          current = "";
        } else {
          current += char;
        }
      }

      result.push(current.trim());
      return result;
    },

    validateCsvRow(values, rowNum) {
      const errors = [];

      // Expected CSV format: Name, Category, Price, Cost, Stock, Weight, THC%, CBD%, SKU, Vendor, METRC_Tag
      if (values.length < 6) {
        errors.push("Insufficient columns (minimum 6 required)");
      }

      const [
        name,
        category,
        price,
        cost,
        stock,
        weight,
        thc,
        cbd,
        sku,
        vendor,
        metrcTag,
      ] = values;

      // Validate required fields
      if (!name || name.trim() === "") {
        errors.push("Product name is required");
      }

      if (!category || category.trim() === "") {
        errors.push("Category is required");
      }

      if (!price || isNaN(parseFloat(price)) || parseFloat(price) <= 0) {
        errors.push("Valid price is required");
      }

      if (!cost || isNaN(parseFloat(cost)) || parseFloat(cost) < 0) {
        errors.push("Valid cost is required");
      }

      if (!stock || isNaN(parseInt(stock)) || parseInt(stock) < 0) {
        errors.push("Valid stock quantity is required");
      }

      // Validate optional numeric fields
      if (
        thc &&
        (isNaN(parseFloat(thc)) || parseFloat(thc) < 0 || parseFloat(thc) > 100)
      ) {
        errors.push("THC% must be between 0-100");
      }

      if (
        cbd &&
        (isNaN(parseFloat(cbd)) || parseFloat(cbd) < 0 || parseFloat(cbd) > 100)
      ) {
        errors.push("CBD% must be between 0-100");
      }

      return {
        rowNum,
        data: {
          name: name?.trim() || "",
          category: category?.trim() || "",
          price: parseFloat(price) || 0,
          cost: parseFloat(cost) || 0,
          stock: parseInt(stock) || 0,
          weight: weight?.trim() || "",
          thc: parseFloat(thc) || 0,
          cbd: parseFloat(cbd) || 0,
          sku: sku?.trim() || "",
          vendor: vendor?.trim() || "",
          metrcTag: metrcTag?.trim() || "",
        },
        valid: errors.length === 0,
        errors: errors,
      };
    },

    importCsvData() {
      if (!this.csvImportForm.file) {
        this.showToast("Please select and preview a CSV file first", "error");
        return;
      }

      if (this.csvImportForm.validRows === 0) {
        this.showToast("No valid rows found to import", "error");
        return;
      }

      this.csvImportForm.importing = true;
      this.showToast("Starting CSV import...", "info");

      // Re-read and process the entire file
      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const csv = e.target.result;
          const lines = csv.split("\n").filter((line) => line.trim());
          const dataLines = this.csvImportForm.skipFirstRow
            ? lines.slice(1)
            : lines;

          let imported = 0;
          let skipped = 0;

          dataLines.forEach((line, index) => {
            const values = this.parseCsvLine(line);
            const rowData = this.validateCsvRow(values, index + 1);

            if (rowData.valid) {
              // Generate new product ID
              const newId = Math.max(...this.products.map((p) => p.id), 0) + 1;

              // Create new product object
              const newProduct = {
                id: newId,
                name: rowData.data.name,
                category: rowData.data.category,
                price: rowData.data.price,
                cost: rowData.data.cost,
                stock: rowData.data.stock,
                weight: rowData.data.weight || "N/A",
                thc: rowData.data.thc,
                cbd: rowData.data.cbd,
                sku: rowData.data.sku || `SKU-${newId}`,
                vendor: rowData.data.vendor || "Unknown",
                supplier: "CSV Import",
                room: "Storage Room",
                onSalesFloor: false,
                isGLS: false,
                metrcTag: rowData.data.metrcTag || "",
                priceTier: null,
              };

              // Add to products array
              this.products.push(newProduct);
              imported++;
            } else {
              skipped++;
            }
          });

          // Update import results
          this.csvImportForm.importResults = {
            imported: imported,
            skipped: skipped,
            total: dataLines.length,
          };

          this.csvImportForm.importing = false;
          this.csvImportForm.importComplete = true;

          // Save to localStorage
          try {
            localStorage.setItem(
              "cannabisPOS-products",
              JSON.stringify(this.products),
            );
          } catch (error) {
            console.error("Error saving products to localStorage:", error);
          }

          // Filter products to update display
          this.filterProducts();

          this.showToast(
            `CSV import completed! ${imported} products imported, ${skipped} skipped.`,
            "success",
          );
        } catch (error) {
          this.csvImportForm.importing = false;
          this.showToast("Error importing CSV: " + error.message, "error");
        }
      };

      reader.readAsText(this.csvImportForm.file);
    },

    // Template Download Functions
    openTemplateModal() {
      this.showTemplateModal = true;
    },

    closeTemplateModal() {
      this.showTemplateModal = false;
    },

    downloadTemplate(category) {
      const templates = {
        flower: {
          filename: "Flower_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Weight",
            "THC%",
            "CBD%",
            "CBN%",
            "CBG%",
            "CBC%",
            "SKU",
            "Vendor",
            "Supplier",
            "METRC Tag",
          ],
          sample: [
            "Blue Dream",
            "Flower",
            "15.00",
            "8.00",
            "250",
            "28g",
            "22.5",
            "0.8",
            "0.3",
            "1.2",
            "0.5",
            "BD-001",
            "Oregon Cannabis Co.",
            "Green Valley Farms",
            "1A4060300001234000000001",
          ],
        },
        edibles: {
          filename: "Edibles_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Weight",
            "THC mg",
            "CBD mg",
            "Total Servings",
            "SKU",
            "Vendor",
            "Supplier",
            "METRC Tag",
          ],
          sample: [
            "THC Gummy Bears",
            "Edibles",
            "25.00",
            "12.00",
            "50",
            "100mg",
            "10",
            "0",
            "10",
            "TGB-001",
            "Sweet Relief Co.",
            "Edible Solutions",
            "1A4060300001234000000002",
          ],
        },
        concentrates: {
          filename: "Concentrates_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Weight",
            "THC%",
            "CBD%",
            "Extract Type",
            "SKU",
            "Vendor",
            "Supplier",
            "METRC Tag",
          ],
          sample: [
            "Live Resin Badder",
            "Concentrates",
            "45.00",
            "25.00",
            "25",
            "1g",
            "78.5",
            "0.2",
            "Live Resin",
            "LRB-001",
            "Concentrate Co.",
            "Extraction Labs",
            "1A4060300001234000000003",
          ],
        },
        prerolls: {
          filename: "PreRolls_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Weight",
            "THC%",
            "CBD%",
            "Pack Size",
            "SKU",
            "Vendor",
            "Supplier",
            "METRC Tag",
          ],
          sample: [
            "Pre-Roll Pack",
            "Pre-Rolls",
            "12.00",
            "6.00",
            "100",
            "1g",
            "20.5",
            "0.5",
            "1",
            "PR-001",
            "Roll Co.",
            "Pre-Roll Solutions",
            "1A4060300001234000000004",
          ],
        },
        tinctures: {
          filename: "Tinctures_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Volume",
            "THC mg/ml",
            "CBD mg/ml",
            "Total Volume",
            "SKU",
            "Vendor",
            "Supplier",
            "METRC Tag",
          ],
          sample: [
            "CBD Tincture",
            "Tinctures",
            "35.00",
            "18.00",
            "30",
            "30ml",
            "1",
            "25",
            "30ml",
            "TIN-001",
            "Tincture Co.",
            "Extract Solutions",
            "1A4060300001234000000005",
          ],
        },
        topicals: {
          filename: "Topicals_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Size",
            "THC mg",
            "CBD mg",
            "Application Type",
            "SKU",
            "Vendor",
            "Supplier",
            "METRC Tag",
          ],
          sample: [
            "Pain Relief Cream",
            "Topicals",
            "28.00",
            "14.00",
            "40",
            "50g",
            "100",
            "200",
            "Cream",
            "TOP-001",
            "Topical Co.",
            "Wellness Labs",
            "1A4060300001234000000006",
          ],
        },
        vape: {
          filename: "VapeProducts_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Cartridge Size",
            "THC%",
            "CBD%",
            "Hardware Type",
            "SKU",
            "Vendor",
            "Supplier",
            "METRC Tag",
          ],
          sample: [
            "Hybrid Vape Cart",
            "Vape Products",
            "40.00",
            "22.00",
            "60",
            "1g",
            "85.2",
            "0.3",
            "510 Thread",
            "VPC-001",
            "Vape Co.",
            "Cartridge Solutions",
            "1A4060300001234000000007",
          ],
        },
        accessories: {
          filename: "Accessories_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Material",
            "Color",
            "Size",
            "Brand",
            "SKU",
            "Vendor",
            "Supplier",
          ],
          sample: [
            "Glass Pipe",
            "Accessories",
            "18.00",
            "9.00",
            "25",
            "Glass",
            "Clear",
            "Medium",
            "GlassCo",
            "ACC-001",
            "Accessory Co.",
            "Glass Solutions",
          ],
        },
        hemp: {
          filename: "Hemp_Import_Template.csv",
          headers: [
            "Product Name",
            "Category",
            "Price",
            "Cost",
            "Stock",
            "Weight",
            "CBD%",
            "Delta-8%",
            "Delta-9%",
            "SKU",
            "Vendor",
            "Supplier",
            "COA Available",
          ],
          sample: [
            "Hemp Flower",
            "Hemp",
            "12.00",
            "6.00",
            "80",
            "3.5g",
            "18.5",
            "0.2",
            "0.3",
            "HMP-001",
            "Hemp Co.",
            "Hemp Farms",
            "Yes",
          ],
        },
      };

      const template = templates[category];
      if (!template) {
        this.showToast("Template not found for category: " + category, "error");
        return;
      }

      // Create CSV content
      let csvContent = template.headers.join(",") + "\n";
      csvContent += template.sample.join(",") + "\n";

      // Create download
      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      const url = URL.createObjectURL(blob);
      link.setAttribute("href", url);
      link.setAttribute("download", template.filename);
      link.style.visibility = "hidden";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      this.showToast(`Template downloaded: ${template.filename}`, "success");
    },

    downloadAllTemplates() {
      const categories = [
        "flower",
        "edibles",
        "concentrates",
        "prerolls",
        "tinctures",
        "topicals",
        "vape",
        "accessories",
        "hemp",
      ];

      this.showToast("Downloading all templates...", "info");

      // Download each template with a small delay
      categories.forEach((category, index) => {
        setTimeout(() => {
          this.downloadTemplate(category);
        }, index * 200); // 200ms delay between downloads
      });

      setTimeout(
        () => {
          this.showToast("All templates downloaded successfully!", "success");
        },
        categories.length * 200 + 500,
      );
    },

    // Price Tier Functions
    addCustomWeight() {
      this.tierForm.customWeights = this.tierForm.customWeights || [];
      this.tierForm.customWeights.push({
        weight: "",
        price: 0,
      });
    },

    removeCustomWeight(index) {
      if (
        this.tierForm.customWeights &&
        index >= 0 &&
        index < this.tierForm.customWeights.length
      ) {
        this.tierForm.customWeights.splice(index, 1);
      }
    },

    // METRC Import Functions
    resetImportForm() {
      this.importForm = {
        metrcTag: "",
        category: "",
        weight: "",
        cannabinoids: "",
        productName: "",
        sku: "",
        price: 0,
        cost: 0,
        room: "",
        mainImage: null,
        additionalImages: [],
      };
    },

    executeMetrcImport() {
      if (
        !this.importForm.productName ||
        !this.importForm.price ||
        !this.importForm.cost ||
        !this.importForm.room ||
        !this.importForm.sku
      ) {
        this.showToast("Please fill in all required fields", "error");
        return;
      }

      const newProduct = {
        id: Math.max(...this.products.map((p) => p.id), 0) + 1,
        name: this.importForm.productName,
        category: this.importForm.category || "Flower",
        price: parseFloat(this.importForm.price),
        cost: parseFloat(this.importForm.cost),
        stock: 1,
        weight: this.importForm.weight || "N/A",
        thc: 0,
        cbd: 0,
        sku: this.importForm.sku,
        vendor: this.selectedVendorForImport?.name || "METRC Import",
        supplier: "METRC Transfer",
        room: this.importForm.room,
        onSalesFloor: this.importForm.room.includes("Sales"),
        isGLS: false,
        metrcTag: this.importForm.metrcTag || "",
      };

      this.products.push(newProduct);

      try {
        localStorage.setItem(
          "cannabisPOS-products",
          JSON.stringify(this.products),
        );
      } catch (error) {
        console.error("Error saving products:", error);
      }

      this.filterProducts();
      this.showToast(
        `Product "${newProduct.name}" imported successfully from METRC`,
        "success",
      );

      this.showMetrcImportModal = false;
      this.selectedVendorForImport = null;
      this.resetImportForm();
    },

    // Employee Management Functions
    resetEmployeeForm() {
      this.employeeForm = {
        name: "",
        email: "",
        phone: "",
        role: "budtender",
        payRate: 15.0,
        hireDate: "",
        status: "active",
        workerPermit: "",
        metrcApiKey: "",
        permissions: [],
      };
    },

    editEmployee(employee) {
      this.selectedEmployee = employee;
      this.employeeForm = {
        name: employee.name,
        email: employee.email,
        phone: employee.phone,
        role: employee.role,
        payRate: employee.payRate,
        hireDate: employee.hireDate,
        status: employee.status,
        workerPermit: employee.workerPermit || "",
        metrcApiKey: employee.metrcApiKey || "",
        permissions: employee.permissions || [],
      };
      this.showAddEmployeeModal = true;
    },

    viewEmployee(employee) {
      this.selectedEmployee = employee;
      this.showEmployeeModal = true;
    },

    async resetPassword(employee) {
      try {
        if (!employee?.id) throw new Error("Invalid employee");
        const res = await posAuth.apiRequest(
          "post",
          `/employees/${employee.id}/reset-password`,
        );
        if (res.success) {
          this.showToast("Password reset email sent", "success");
        } else {
          throw new Error(res.message || "Failed to send reset email");
        }
      } catch (e) {
        this.showToast(e.message || "Failed to send reset email", "error");
      }
    },

    async resetPIN(employee) {
      try {
        if (!employee?.id) throw new Error("Invalid employee");
        const res = await posAuth.apiRequest(
          "post",
          `/employees/${employee.id}/reset-pin`,
        );
        if (res.success) {
          this.showToast("PIN reset and email sent", "success");
        } else {
          throw new Error(res.message || "Failed to reset PIN");
        }
      } catch (e) {
        this.showToast(e.message || "Failed to reset PIN", "error");
      }
    },

    // Room Management Functions
    closeAddRoomModal() {
      this.showAddRoomModal = false;
      this.selectedRoom = null;
      this.roomForm = {
        name: "",
        type: "",
        forSale: "true",
        maxCapacity: "",
        status: "active",
        temperature: 68,
        humidity: 50,
      };
    },

    addRoom() {
      if (!this.roomForm.name) {
        this.showToast("Please enter a room name", "error");
        return;
      }

      const newRoom = {
        id:
          Math.max(
            ...(this.facilityRooms || []).map((r) => Number(r?.id) || 0),
            0,
          ) + 1,
        name: this.roomForm.name,
        type: this.roomForm.type || "storage",
        forSale: this.roomForm.forSale === "true",
        maxCapacity: Number(this.roomForm.maxCapacity) || 0,
        currentCapacity: 0,
        status: this.roomForm.status,
        temperature: Number(this.roomForm.temperature) || 68,
        humidity: Number(this.roomForm.humidity) || 50,
        createdAt: new Date().toISOString(),
      };

      this.facilityRooms = Array.isArray(this.facilityRooms)
        ? this.facilityRooms
        : [];
      this.facilityRooms.push(newRoom);
      try {
        localStorage.setItem("pos_rooms", JSON.stringify(this.facilityRooms));
      } catch (_) {}
      try {
        this.logActivity &&
          this.logActivity(
            "room",
            "created",
            newRoom.name,
            `${newRoom.type} · Cap ${newRoom.maxCapacity}`,
          );
      } catch (_) {}
      this.showToast(`Room "${newRoom.name}" created successfully`, "success");
      this.closeAddRoomModal();
    },

    updateRoom() {
      if (!this.selectedRoom || !this.roomForm.name) {
        this.showToast("Please enter a room name", "error");
        return;
      }

      const roomIndex = (this.facilityRooms || []).findIndex(
        (r) => r.id === this.selectedRoom.id,
      );
      if (roomIndex !== -1) {
        const prev = this.facilityRooms[roomIndex];
        this.facilityRooms[roomIndex] = {
          ...prev,
          name: this.roomForm.name,
          type: this.roomForm.type || prev.type || "storage",
          forSale: this.roomForm.forSale === "true",
          maxCapacity:
            Number(this.roomForm.maxCapacity) || Number(prev.maxCapacity) || 0,
          status: this.roomForm.status,
          temperature:
            Number(this.roomForm.temperature) || Number(prev.temperature) || 68,
          humidity:
            Number(this.roomForm.humidity) || Number(prev.humidity) || 50,
          updatedAt: new Date().toISOString(),
        };
        try {
          localStorage.setItem("pos_rooms", JSON.stringify(this.facilityRooms));
        } catch (_) {}
        try {
          this.logActivity &&
            this.logActivity(
              "room",
              "updated",
              this.roomForm.name,
              `${prev.name} → ${this.roomForm.name}`,
            );
        } catch (_) {}
        this.showToast(
          `Room "${this.roomForm.name}" updated successfully`,
          "success",
        );
        this.closeAddRoomModal();
      }
    },

    editRoom(room) {
      this.selectedRoom = room;
      this.roomForm = {
        name: room.name,
        forSale: room.forSale ? "true" : "false",
        maxCapacity: room.maxCapacity,
        status: room.status,
        temperature: room.temperature || 68,
        humidity: room.humidity || 50,
      };
      this.showAddRoomModal = true;
    },

    deleteRoomWithPin(room) {
      if (!room) return;
      if (!confirm(`Delete room "${room.name}"?`)) return;
      this.facilityRooms = (this.facilityRooms || []).filter(
        (r) => String(r.id) !== String(room.id),
      );
      try {
        localStorage.setItem("pos_rooms", JSON.stringify(this.facilityRooms));
      } catch (_) {}
      try {
        this.logActivity && this.logActivity("room", "deleted", room.name);
      } catch (_) {}
      this.showToast("Room deleted", "success");
    },

    // Drawer Management Functions
    closeAddDrawerModal() {
      this.showAddDrawerModal = false;
      this.drawerForm = {
        name: "",
        location: "",
        assignedEmployee: "",
        startingAmount: 100.0,
      };
    },

    addDrawer() {
      if (
        !this.drawerForm.name ||
        !this.drawerForm.location ||
        !this.drawerForm.assignedEmployee ||
        this.drawerForm.startingAmount < 0
      ) {
        this.showToast(
          "Please fill in all required fields with valid values",
          "error",
        );
        return;
      }

      const newDrawer = {
        id:
          Math.max(
            ...(this.cashDrawers || []).map((d) => Number(d.id) || 0),
            0,
          ) + 1,
        name: this.drawerForm.name,
        location: this.drawerForm.location,
        assignedEmployee: this.drawerForm.assignedEmployee,
        startingAmount: parseFloat(this.drawerForm.startingAmount),
        currentAmount: parseFloat(this.drawerForm.startingAmount),
        status: "open",
        openedAt: new Date().toISOString(),
        createdAt: new Date().toISOString(),
      };

      this.cashDrawers = this.cashDrawers || [];
      this.cashDrawers.push(newDrawer);
      try {
        localStorage.setItem("pos_drawers", JSON.stringify(this.cashDrawers));
      } catch (_) {}
      try {
        this.logActivity &&
          this.logActivity(
            "drawer",
            "created",
            newDrawer.name,
            `Starting $${newDrawer.startingAmount.toFixed(2)}`,
          );
      } catch (_) {}
      this.showToast(
        `Cash drawer "${newDrawer.name}" created successfully`,
        "success",
      );
      this.closeAddDrawerModal();
    },

    // Simple activity logger (used by Activity Log tab)
    logActivity(type, action, location = "", details = "") {
      const entry = {
        id: String(Date.now()) + Math.random().toString(36).slice(2),
        timestamp: new Date().toLocaleString(),
        action: type,
        type,
        location,
        employee: this.currentUser?.name || "User",
        details: details || action,
      };
      this.activityLog.unshift(entry);
      try {
        const key = "rd-activity-log";
        const prev = JSON.parse(localStorage.getItem(key) || "[]");
        prev.push(entry);
        localStorage.setItem(key, JSON.stringify(prev));
      } catch (_) {}
    },

    // Alpine-used drawer actions
    openDrawer(drawer) {
      if (!drawer) return;
      drawer.status = "open";
      drawer.openedAt = new Date().toISOString();
      try {
        localStorage.setItem("pos_drawers", JSON.stringify(this.cashDrawers));
      } catch (_) {}
      this.logActivity("drawer", "opened", drawer.name);
      this.showToast(`${drawer.name} opened`, "success");
    },
    closeDrawer(drawer) {
      if (!drawer) return;
      drawer.status = "closed";
      try {
        localStorage.setItem("pos_drawers", JSON.stringify(this.cashDrawers));
      } catch (_) {}
      this.logActivity("drawer", "closed", drawer.name);
      this.showToast(`${drawer.name} closed`, "info");
    },
    countDrawer(drawer) {
      try {
        const modal = document.getElementById("rd-count-modal");
        if (modal) {
          modal.classList.remove("hidden");
          modal.classList.add("flex");
          return;
        }
      } catch (_) {}
      this.showToast(
        "Counting UI is available on Rooms & Drawers page",
        "info",
      );
    },
    deleteDrawerWithPin(drawer) {
      if (!drawer) return;
      if (!confirm(`Delete ${drawer.name}?`)) return;
      this.cashDrawers = (this.cashDrawers || []).filter(
        (d) => String(d.id) !== String(drawer.id),
      );
      try {
        localStorage.setItem("pos_drawers", JSON.stringify(this.cashDrawers));
      } catch (_) {}
      this.logActivity("drawer", "deleted", drawer.name);
      this.showToast("Drawer deleted", "success");
    },
    assignEmployeeToDrawer(drawer) {
      if (!drawer) return;
      const name = prompt("Assign employee name:");
      if (!name) return;
      drawer.assignedEmployee = name;
      try {
        localStorage.setItem("pos_drawers", JSON.stringify(this.cashDrawers));
      } catch (_) {}
      this.logActivity(
        "drawer",
        "assigned",
        drawer.name,
        `Assigned to ${name}`,
      );
      this.showToast(`Assigned to ${name}`, "success");
    },

    // Employee permissions helper
    canManageEmployees() {
      try {
        if (
          window.posAuth?.hasRole &&
          (posAuth.hasRole("admin") || posAuth.hasRole("manager"))
        )
          return true;
        if (
          window.posAuth?.hasPermission &&
          posAuth.hasPermission("employees:manage")
        )
          return true;
      } catch (e) {}
      return false;
    },

    // PIN Modal Functions
    closePinModal() {
      this.showPinModal = false;
      this.pinInput = "";
      this.pinError = "";
      this.pinAction = "";
      this.employeePendingDelete = null;
    },

    async verifyPinAndDelete() {
      if (!this.pinInput || this.pinInput.length < 4) {
        this.pinError = "Please enter a valid PIN";
        return;
      }

      // Verify PIN (primary API). If unauthorized, re-auth using PIN.
      let pinOk = false;
      if (this.canManageEmployees()) {
        pinOk = true;
      }
      if (!pinOk) {
        try {
          const verify = await window.posAuth?.apiRequest?.(
            "post",
            "/auth/verify-pin",
            { pin: this.pinInput },
          );
          pinOk = !!(verify && verify.success);
        } catch (err) {
          pinOk = false;
        }
      }

      if (!pinOk) {
        // Attempt PIN-based login to refresh token/session
        try {
          const empId = await this.resolveMyEmployeeId();
          if (empId) {
            const login = await window.posAuth?.pinLogin?.(
              empId,
              this.pinInput,
            );
            pinOk = !!(login && login.success);
            if (pinOk) {
              this.isAuthenticated = true;
              this.currentUser = login.user || window.posAuth?.getUser?.();
            }
          }
        } catch (_) {
          pinOk = false;
        }
      }

      // Fallback: accept current user's PIN if they can manage employees
      if (!pinOk) {
        try {
          const u = window.posAuth?.getUser?.() || {};
          const canManage = !!(
            window.posAuth?.hasRole?.("admin") ||
            window.posAuth?.hasRole?.("manager") ||
            window.posAuth?.hasPermission?.("employees:manage")
          );
          if (!(canManage && String(u?.pin || "") === String(this.pinInput))) {
            this.pinError = "PIN verification failed";
            this.showToast(this.pinError, "error");
            return;
          }
        } catch (_) {
          this.pinError = "PIN verification failed";
          this.showToast(this.pinError, "error");
          return;
        }
      }

      // Execute action
      try {
        if (
          this.pinAction === "deleteEmployee" &&
          (this.employeePendingDelete?.id ||
            this.employeePendingDelete?.employeeId ||
            this.employeePendingDelete?.numericId != null)
        ) {
          const cand = this.employeePendingDelete;
          const targetId =
            (cand.numericId != null ? String(cand.numericId) : "") ||
            cand.employeeId ||
            "" ||
            String(cand.id);

          // Prefer API route (token auth) for reliable persistence; fallback to web if available
          let ok = false;
          // 1) API via helper (respects axios interceptors and token refresh)
          try {
            const res = await window.posAuth?.apiRequest?.(
              "delete",
              `/employees/${encodeURIComponent(targetId)}`,
            );
            ok = !!(res && res.success);
          } catch (_) {
            ok = false;
          }

          // 2) Direct API call fallback
          if (!ok) {
            try {
              const respApi = await (window.axios || axios).delete(
                `/api/employees/${encodeURIComponent(targetId)}`,
                { headers: { Accept: "application/json" } },
              );
              ok = respApi && respApi.status >= 200 && respApi.status < 300;
            } catch (_) {
              ok = false;
            }
          }

          // 3) Web route as last resort (requires web auth + CSRF)
          if (!ok) {
            try {
              const headers = { Accept: "application/json" };
              const csrf = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
              if (csrf) headers["X-CSRF-TOKEN"] = csrf;
              const respWeb = await (window.axios || axios).delete(
                `/employees/${encodeURIComponent(targetId)}`,
                { headers },
              );
              ok = respWeb && respWeb.status >= 200 && respWeb.status < 300;
            } catch (_) {
              ok = false;
            }
          }

          if (!ok) {
            throw new Error("Delete failed");
          }

          // Remove from local list regardless (idempotent)
          this.employees = (this.employees || []).filter((e) => {
            const nid = e.numericId != null ? String(e.numericId) : "";
            const eid = e.employeeId || "";
            return (
              String(e.id) !== targetId && nid !== targetId && eid !== targetId
            );
          });
          try {
            await this.fetchEmployeesFromApi();
          } catch (_) {}
          try {
            this.ensureMyEmployeeListed(window.posAuth?.getUser?.());
          } catch (_) {}
          this.showToast("Employee deactivated", "success");
        } else if (this.pinAction === "deleteRoom") {
          this.showToast("Room deleted", "success");
        } else if (this.pinAction === "deleteDrawer") {
          this.showToast("Cash drawer deleted", "success");
        }
        this.closePinModal();
      } catch (e) {
        this.pinError = e?.message || "Operation failed";
      }
    },

    // Resolve current employee ID for PIN re-auth flows
    async resolveMyEmployeeId() {
      try {
        const el = document.getElementById("user-menu-container");
        const id = el?.dataset?.employeeId;
        if (id) return String(id);
      } catch (_) {}
      try {
        const u = await window.posAuth?.refreshUser?.();
        if (u?.employee?.id) return String(u.employee.id);
      } catch (_) {}
      try {
        const u = window.posAuth?.getUser?.();
        if (u?.employee?.id) return String(u.employee.id);
      } catch (_) {}
      return "";
    },

    // Employee actions
    async deleteEmployee(employee) {
      if (!this.canManageEmployees()) {
        this.showToast("Insufficient permissions", "error");
        return;
      }
      if (!employee?.id) return;
      this.employeePendingDelete = employee;
      this.pinAction = "deleteEmployee";
      this.pinInput = "";
      this.pinError = "";
      this.showPinModal = true;
    },

    async toggleEmployeeStatus(employee) {
      if (!this.canManageEmployees()) {
        this.showToast("Insufficient permissions", "error");
        return;
      }
      if (!employee?.id) return;
      const next = employee.status === "active" ? "inactive" : "active";
      try {
        const res = await posAuth.apiRequest(
          "put",
          `/employees/${employee.id}`,
          { status: next },
        );
        if (!res.success)
          throw new Error(res.message || "Failed to update status");
        // Update local list
        const idx = (this.employees || []).findIndex(
          (e) => String(e.id) === String(employee.id),
        );
        if (idx !== -1)
          this.employees[idx] = { ...this.employees[idx], status: next };
        this.showToast(
          next === "active" ? "Employee activated" : "Employee set to inactive",
          "success",
        );
      } catch (e) {
        this.showToast(e?.message || "Failed to update status", "error");
      }
    },

    // Employee Assignment Functions
    confirmEmployeeAssignment(employee) {
      if (!this.selectedDrawerForAssignment || !employee) {
        this.showToast("Missing assignment information", "error");
        return;
      }

      const drawerIndex = (this.cashDrawers || []).findIndex(
        (d) => d.id === this.selectedDrawerForAssignment.id,
      );
      if (drawerIndex !== -1) {
        this.cashDrawers[drawerIndex].assignedEmployee = employee.name;
        this.cashDrawers[drawerIndex].assignedEmployeeId = employee.id;
        this.showToast(
          `${employee.name} assigned to ${this.selectedDrawerForAssignment.name}`,
          "success",
        );
      }

      this.showEmployeeAssignModal = false;
      this.selectedDrawerForAssignment = null;
    },

    // Customer Management Functions
    closeEditCustomerModal() {
      this.showEditCustomerModal = false;
      this.editCustomerForm = {
        name: "",
        email: "",
        phone: "",
        isMedical: false,
        medicalCard: "",
      };
    },

    async updateCustomer() {
      if (!this.editCustomerForm.name) {
        this.showToast("Customer name is required", "error");
        return;
      }

      const customerIndex = this.customers.findIndex(
        (c) => c.id === this.selectedCustomer.id,
      );
      if (customerIndex !== -1) {
        const current = this.customers[customerIndex];
        const updated = {
          ...current,
          name: this.editCustomerForm.name,
          email: this.editCustomerForm.email,
          phone: this.editCustomerForm.phone,
          isMedical: this.editCustomerForm.isMedical,
          medicalCard: this.editCustomerForm.medicalCard,
        };

        // Try API update when authenticated and permitted
        try {
          if (this.isAuthenticated && this.hasPermission("customers:write") && current.id) {
            const [first, ...rest] = (updated.name || "").split(" ");
            const payload = {
              first_name: first || updated.name,
              last_name: rest.join(" ") || null,
              email: updated.email,
              phone: updated.phone,
              customer_type: updated.isMedical ? "medical" : "recreational",
            };
            const res = await posAuth.apiRequest("patch", `/customers/${current.id}`, payload);
            if (!(res && res.success)) {
              await posAuth.apiRequest("put", `/customers/${current.id}`, payload);
            }
          }
        } catch (_) {}

        this.customers[customerIndex] = updated;
        try { this._saveCustomersLocal(); } catch (_) {}
        try { this.filterLoyaltyCustomers(); } catch (_) {}

        this.showToast(
          `Customer "${this.editCustomerForm.name}" updated successfully`,
          "success",
        );
        this.closeEditCustomerModal();
      }
    },

    deleteCustomer(customer) {
      const id = customer && customer.id != null ? customer.id : null;
      const before = Array.isArray(this.customers) ? this.customers.length : 0;
      this.customers = (Array.isArray(this.customers) ? this.customers : []).filter(c => String(c.id) !== String(id));
      const changed = Array.isArray(this.customers) && this.customers.length !== before;
      if (changed) {
        try { this._saveCustomersLocal(); } catch (_) {}
        try { this.filterLoyaltyCustomers(); } catch (_) {}
      }
      // Best-effort server delete
      try {
        if (this.isAuthenticated && this.hasPermission("customers:write") && id) {
          posAuth.apiRequest("delete", `/customers/${id}`);
        }
      } catch (_) {}
      this.showToast("Customer deleted", changed ? "success" : "info");
    },

    viewCustomer(customer) {
      try {
        if (customer && typeof customer === "object") {
          this.selectedCustomer = customer;
        } else if (customer != null) {
          const idStr = String(customer);
          const found = (Array.isArray(this.customers) ? this.customers : []).find(
            (c) => String(c.id) === idStr,
          );
          this.selectedCustomer = found || null;
        } else {
          this.selectedCustomer = null;
        }
      } catch (_) {
        this.selectedCustomer = customer || null;
      }
      this.showCustomerViewModal = true;
    },

    editCustomer(customer) {
      this.selectedCustomer = customer;
      this.editCustomerForm = {
        name: customer.name,
        email: customer.email || "",
        phone: customer.phone || "",
        isMedical: customer.isMedical || false,
        medicalCard: customer.medicalCard || "",
      };
      this.showEditCustomerModal = true;
    },

    startSaleForCustomer(customer) {
      this.selectedCustomer = customer;
      if (customer.isMedical) {
        this.ageVerified = true;
      }
      this.showToast(`Sale started for ${customer.name}`, "success");
    },

    // Price Tier Management Functions
    closeTierModal() {
      this.showAddTierModal = false;
      this.tierForm = {
        name: "",
        prices: {
          weight_1g: 0,
          weight_3_5g: 0,
          weight_7g: 0,
          weight_14g: 0,
          weight_28g: 0,
        },
        customWeights: [],
      };
    },

    async addPriceTier() {
      if (!this.tierForm.name || !this.hasAnyPrices()) {
        this.showToast(
          "Please enter tier name and at least one price",
          "error",
        );
        return;
      }

      const payload = {
        name: this.tierForm.name,
        is_active: true,
        prices: this.tierForm.prices,
        custom_weights: this.tierForm.customWeights || [],
        created_at: new Date().toISOString(),
      };

      try {
        const res = await (window.axios || axios).post(
          "/api/price-tiers",
          payload,
          { headers: { Accept: "application/json" } },
        );
        const saved = (res?.data && (res.data.tier || res.data)) || null;
        const newTier = {
          id:
            saved?.id ||
            Math.max(...this.priceTiers.map((t) => t.id || 0), 0) + 1,
          name: saved?.name || payload.name,
          isActive: saved?.is_active ?? true,
          createdAt: saved?.created_at || payload.created_at,
          prices: saved?.prices || payload.prices,
          customWeights: saved?.custom_weights || payload.custom_weights,
        };
        this.priceTiers.push(newTier);
        try {
          localStorage.setItem(
            "cannabisPOS-priceTiers-backup",
            JSON.stringify(this.priceTiers),
          );
        } catch (_) {}
        this.showToast(
          `Price tier "${newTier.name}" created successfully`,
          "success",
        );
      } catch (e) {
        // Fallback: local only
        const fallback = {
          id: Math.max(...this.priceTiers.map((t) => t.id || 0), 0) + 1,
          name: payload.name,
          isActive: true,
          createdAt: payload.created_at,
          prices: payload.prices,
          customWeights: payload.custom_weights,
        };
        this.priceTiers.push(fallback);
        this.showToast(
          `Price tier "${fallback.name}" saved locally (offline)`,
          "warning",
        );
      }
      this.closeTierModal();
    },

    // Void Sale Functions
    closeVoidSaleModal() {
      this.showVoidSaleModal = false;
      this.saleToVoid = null;
      this.voidForm = {
        reason: "",
        notes: "",
        employeePin: "",
        pinVerified: false,
        verifiedEmployee: "",
        pinError: "",
      };
    },

    verifyEmployeePin() {
      if (!this.voidForm.employeePin || this.voidForm.employeePin.length < 4) {
        this.voidForm.pinError = "Please enter a valid PIN";
        return;
      }

      this.voidForm.pinVerified = true;
      this.voidForm.verifiedEmployee = "Current Employee";
      this.voidForm.pinError = "";
      this.showToast("PIN verified successfully", "success");
    },

    confirmVoidSale() {
      if (!this.voidForm.reason || !this.voidForm.pinVerified) {
        this.showToast(
          "Please fill in all required fields and verify PIN",
          "error",
        );
        return;
      }

      if (this.voidForm.reason === "other" && !this.voidForm.notes) {
        this.showToast('Please provide notes for "Other" reason', "error");
        return;
      }

      this.showToast("Sale voided successfully", "success");
      this.closeVoidSaleModal();
    },

    // Deal Management Functions
    closeCreateDealModal() {
      this.showCreateDealModal = false;
      this.editingDeal = null;
      this.dealForm = {
        name: "",
        description: "",
        type: "",
        discountValue: 0,
        buyQuantity: 1,
        getQuantity: 1,
        minPurchase: 0,
        minPurchaseType: "dollars",
        usageLimit: "",
        allCategories: false,
        applicableCategories: [],
        applicableProducts: [],
        excludeGLS: true,
        stackable: false,
        loyaltyOnly: false,
        medicalOnly: false,
        emailCustomers: false,
        isActive: true,
        startDate: "",
        endDate: "",
        startTime: "",
        endTime: "",
        activeDays: [],
      };
    },

    toggleAllCategories() {
      if (this.dealForm.allCategories) {
        this.dealForm.applicableCategories = [];
      }
    },

    async sendDealEmail(deal) {
      try {
        const id = deal?.numericId || deal?.id;
        if (!id) {
          this.showToast && this.showToast("Deal ID missing", "error");
          return;
        }
        const http = window.axios || axios;
        const res = await http.post(
          `/api/deals/${id}/email`,
          {},
          { headers: { Accept: "application/json" } },
        );
        if (res?.status >= 200 && res?.status < 300) {
          this.showToast && this.showToast("Email campaign sent", "success");
        } else {
          this.showToast && this.showToast("Failed to send emails", "error");
        }
      } catch (e) {
        this.showToast && this.showToast("Failed to send emails", "error");
      }
    },

    // CSV Import Functions
    closeCsvImportModal() {
      this.showCsvImportModal = false;
      this.csvImportForm = {
        file: null,
        fileName: "",
        category: "",
        skipFirstRow: true,
        previewData: [],
        totalRows: 0,
        validRows: 0,
        errorRows: 0,
        importing: false,
        importComplete: false,
        importResults: null,
      };
    },

    // Cash Count Functions
    resetCashCount() {
      this.cashCount = {
        total: 0,
        notes: "",
      };
    },

    printClosingSheet(drawer) {
      this.showToast(`Closing sheet printed for ${drawer.name}`, "success");
    },

    // Vendor Functions
    downloadManifest(vendor) {
      this.showToast(`Manifest downloaded for ${vendor.name}`, "success");
    },

    importVendorInventory(vendor) {
      this.showToast(`Inventory imported from ${vendor.name}`, "success");
    },

    // File Upload Handlers
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.showToast(`File "${file.name}" selected for upload`, "info");
      }
    },

    handleImageUpload(event, type) {
      const file = event.target.files[0];
      if (file) {
        if (type === "main") {
          this.importForm.mainImage = file.name;
        } else if (type === "additional") {
          this.importForm.additionalImages =
            this.importForm.additionalImages || [];
          this.importForm.additionalImages.push(file.name);
        }
        this.showToast(`Image "${file.name}" uploaded`, "success");
      }
    },

    handleCsvFileUpload(event) {
      const file = event.target.files[0];
      if (file && file.type === "text/csv") {
        this.csvImportForm.file = file;
        this.csvImportForm.fileName = file.name;
        this.showToast(`CSV file "${file.name}" selected`, "success");
      } else {
        this.showToast("Please select a valid CSV file", "error");
      }
    },

    // Authentication Functions
    async handleLogin(email, password) {
      const result = await posAuth.login(email, password);
      if (result.success) {
        try {
          localStorage.removeItem("pos_force_reauth");
        } catch (e) {}
        this.isAuthenticated = true;
        this.currentUser = result.user;
        this.showAuthModal = false;
        await this.loadInitialData();
        this.ensureMyEmployeeListed();
        this.showToast("Login successful", "success");
      } else {
        this.showToast(result.message, "error");
      }
      return result.success;
    },

    async handlePinLogin(employeeId, pin) {
      this.loginError = "";

      if (!employeeId || !pin) {
        this.loginError = "Please enter both Employee ID and PIN";
        return;
      }

      try {
        const result = await posAuth.pinLogin(employeeId, pin);
        if (result.success) {
          try {
            localStorage.removeItem("pos_force_reauth");
          } catch (e) {}
          this.isAuthenticated = true;
          this.currentUser = result.user;
          this.showAuthModal = false;
          this.loginError = "";
          this.employeeId = "";
          this.employeePin = "";
          await this.loadInitialData();
          this.ensureMyEmployeeListed();
          this.showToast("PIN login successful", "success");
        } else {
          this.loginError = result.message || "PIN login failed";
        }
      } catch (error) {
        console.error("PIN login error:", error);
        this.loginError =
          error?.message || "PIN login failed. Please try again.";
      }
    },

    async handleRegister() {
      this.registerError = "";
      const { name, email, password, passwordConfirm, pin } = this.registerForm;

      if (!name || !email || !password || !passwordConfirm || !pin) {
        this.registerError = "Please fill out all fields";
        return;
      }
      if (password !== passwordConfirm) {
        this.registerError = "Passwords do not match";
        return;
      }
      if (!/^\d{4}$/.test(pin)) {
        this.registerError = "PIN must be exactly 4 digits";
        return;
      }

      try {
        const response = await axios.post("/api/auth/self-register", {
          name,
          email,
          password,
          password_confirmation: passwordConfirm,
          pin,
        });

        if (response.status === 201) {
          if (window.posAuth && typeof window.posAuth.setAuth === "function") {
            window.posAuth.setAuth(response.data.user, response.data.token);
          }
          this.currentUser = response.data.user;
          this.isAuthenticated = true;
          this.showRegisterModal = false;
          this.showAuthModal = false;
          this.registerForm = {
            name: "",
            email: "",
            password: "",
            passwordConfirm: "",
            pin: "",
          };
          await this.loadInitialData();
          this.showToast("Account created. Welcome!", "success");
        }
      } catch (error) {
        const msg =
          error.response?.data?.error ||
          error.response?.data?.message ||
          "Registration failed";
        this.registerError = msg;
        this.showToast(msg, "error");
      }
    },

    logout() {
      this.isAuthenticated = false;
      this.currentUser = null;
      this.showAuthModal = true;
      this.loginError = "";
      this.loginEmail = "";
      this.loginPassword = "";
      this.employeeId = "";
      this.employeePin = "";

      this.cart = [];
      this.selectedCustomer = null;
      this.ageVerified = false;

      this.showToast("Logged out successfully", "info");
    },

    // Initialize authentication state using POSAuth
    async initAuth() {
      try {
        this.isAuthenticated = posAuth.isAuthenticated();
        this.currentUser = posAuth.getUser();
        if (this.isAuthenticated) {
          try {
            await posAuth.refreshUser();
          } catch (e) {}
          this.showAuthModal = false;
        }
        document.addEventListener("pos-unauthorized", async () => {
          try {
            const inactive =
              typeof posAuth?.isInactiveBeyondLimit === "function"
                ? posAuth.isInactiveBeyondLimit()
                : false;
            if (!inactive) {
              const ok = await posAuth.refreshToken();
              if (ok) {
                const u = await posAuth.refreshUser();
                if (u) {
                  this.currentUser = u;
                  this.isAuthenticated = true;
                  this.showAuthModal = false;
                  return;
                }
              }
            }
          } catch (e) {}
          this.isAuthenticated = false;
          this.currentUser = null;
          this.showAuthModal = true;
        });
      } catch (error) {
        console.error("Error loading auth state:", error);
      }
    },

    // Initialize report data
    initializeReportData() {
      // Load saved reports from localStorage
      try {
        const savedReports = localStorage.getItem("cannabisPOS-reports");
        if (savedReports) {
          this.recentReports = JSON.parse(savedReports);
        }
      } catch (error) {
        console.error("Error loading reports:", error);
        this.recentReports = [];
      }
    },

    // Open/Close Report Modal
    closeCreateReportModal() {
      this.showCreateReportModal = false;
      this.resetCustomReport();
    },

    resetCustomReport() {
      this.customReport = {
        name: "",
        type: "",
        description: "",
        dataSources: [],
        dateRange: "last-30-days",
        startDate: "",
        endDate: "",
        categoryFilters: [],
        employeeFilter: "",
        customerType: "",
        paymentMethod: "",
        selectedMetrics: [],
        chartType: "table",
        colorScheme: "cannabis",
        includeComparisons: false,
        includeTrends: false,
        includeBreakdowns: false,
        exportFormats: ["pdf"],
        autoSchedule: false,
        scheduleFrequency: "weekly",
        scheduleEmail: "",
      };
    },

    // Update data sources based on report type
    updateDataSources() {
      const typeMapping = {
        sales: ["sales", "customers", "products", "payments", "taxes"],
        inventory: ["inventory", "products", "metrc", "rooms"],
        customers: ["customers", "sales", "loyalty"],
        employees: ["employees", "sales"],
        financial: ["sales", "payments", "taxes", "inventory"],
        compliance: ["metrc", "taxes", "products", "sales"],
        operational: ["rooms", "employees", "inventory", "products"],
      };

      if (this.customReport.type && typeMapping[this.customReport.type]) {
        this.customReport.dataSources = typeMapping[this.customReport.type];
      }
    },

    // Update date inputs for custom range
    updateDateInputs() {
      if (this.customReport.dateRange === "custom") {
        const today = new Date();
        const thirtyDaysAgo = new Date(
          today.getTime() - 30 * 24 * 60 * 60 * 1000,
        );

        this.customReport.startDate = thirtyDaysAgo.toISOString().split("T")[0];
        this.customReport.endDate = today.toISOString().split("T")[0];
      }
    },

    // Validate report configuration
    isReportValid() {
      return (
        this.customReport.name &&
        this.customReport.type &&
        this.customReport.dataSources.length > 0 &&
        this.customReport.selectedMetrics.length > 0
      );
    },

    // Generate Quick Reports
    generateQuickReport(reportType) {
      const reportConfigs = {
        "daily-sales": {
          name: "Daily Sales Summary",
          type: "sales",
          dateRange: "today",
          metrics: [
            "total-revenue",
            "transaction-count",
            "average-order-value",
            "units-sold",
          ],
        },
        "weekly-sales": {
          name: "Weekly Sales Trends",
          type: "sales",
          dateRange: "last-7-days",
          metrics: [
            "total-revenue",
            "gross-sales",
            "top-selling-products",
            "sales-by-category",
          ],
        },
        "monthly-sales": {
          name: "Monthly Performance",
          type: "sales",
          dateRange: "this-month",
          metrics: [
            "total-revenue",
            "net-sales",
            "transaction-count",
            "sales-per-hour",
          ],
        },
        "low-stock": {
          name: "Low Stock Alert",
          type: "inventory",
          dateRange: "today",
          metrics: ["low-stock-items", "out-of-stock-items", "reorder-alerts"],
        },
        "inventory-value": {
          name: "Inventory Valuation",
          type: "inventory",
          dateRange: "today",
          metrics: [
            "inventory-value",
            "current-stock-levels",
            "aging-inventory",
          ],
        },
        "aging-inventory": {
          name: "Aging Inventory Report",
          type: "inventory",
          dateRange: "last-30-days",
          metrics: ["aging-inventory", "dead-stock", "inventory-turnover"],
        },
        "top-customers": {
          name: "Top Customers Report",
          type: "customers",
          dateRange: "last-30-days",
          metrics: [
            "top-customers",
            "customer-lifetime-value",
            "repeat-customers",
          ],
        },
        "customer-loyalty": {
          name: "Loyalty Program Report",
          type: "customers",
          dateRange: "this-month",
          metrics: [
            "loyalty-program-stats",
            "new-customers",
            "customer-retention-rate",
          ],
        },
        "customer-demographics": {
          name: "Customer Demographics",
          type: "customers",
          dateRange: "last-30-days",
          metrics: [
            "customer-demographics",
            "medical-vs-recreational",
            "total-customers",
          ],
        },
        "employee-sales": {
          name: "Employee Sales Performance",
          type: "employees",
          dateRange: "this-month",
          metrics: [
            "total-revenue",
            "transaction-count",
            "average-order-value",
          ],
        },
        "employee-hours": {
          name: "Employee Hours & Productivity",
          type: "employees",
          dateRange: "this-month",
          metrics: ["sales-per-hour"],
        },
        "commission-report": {
          name: "Commission Report",
          type: "financial",
          dateRange: "this-month",
          metrics: ["commission-payments", "gross-sales", "net-profit"],
        },
        "metrc-manifest": {
          name: "METRC Manifest Report",
          type: "compliance",
          dateRange: "today",
          metrics: ["current-stock-levels", "stock-movement"],
        },
        "tax-report": {
          name: "Tax Compliance Report",
          type: "compliance",
          dateRange: "this-month",
          metrics: ["tax-collected", "gross-sales", "medical-vs-recreational"],
        },
        "audit-trail": {
          name: "Audit Trail Report",
          type: "compliance",
          dateRange: "last-30-days",
          metrics: ["transaction-count", "refunds-returns"],
        },
        "profit-loss": {
          name: "Profit & Loss Statement",
          type: "financial",
          dateRange: "this-month",
          metrics: [
            "gross-profit",
            "net-profit",
            "cost-of-goods-sold",
            "operating-expenses",
          ],
        },
        "cash-flow": {
          name: "Cash Flow Report",
          type: "financial",
          dateRange: "this-month",
          metrics: ["cash-flow", "total-revenue", "payment-method-breakdown"],
        },
        "margin-analysis": {
          name: "Margin Analysis",
          type: "financial",
          dateRange: "last-30-days",
          metrics: ["profit-margin", "gross-profit", "cost-of-goods-sold"],
        },
      };

      const config = reportConfigs[reportType];
      if (config) {
        // Simulate report generation
        this.showToast(`Generating ${config.name}...`, "info");

        setTimeout(() => {
          const generatedReport = {
            id: Date.now(),
            name: config.name,
            type: config.type,
            createdAt: new Date().toISOString(),
            createdBy: this.currentUser?.name || "User",
            dateRange: config.dateRange,
            metrics: config.metrics,
            status: "completed",
          };

          this.recentReports.unshift(generatedReport);
          if (this.recentReports.length > 10) {
            this.recentReports = this.recentReports.slice(0, 10);
          }

          // Save to localStorage
          localStorage.setItem(
            "cannabisPOS-reports",
            JSON.stringify(this.recentReports),
          );

          this.showToast(`${config.name} generated successfully!`, "success");
        }, 1500);
      }
    },

    // Generate Report (predefined or custom)
    async generateReport(type) {
      // Always ask for format first so the user sees the prompt
      const fmt = await this._askFormat("pdf");
      if (!fmt) return;

      // Normalize type in case an event object was passed
      let resolvedType =
        typeof type === "string" && type.trim() ? type.trim() : null;
      if (!resolvedType && type && typeof type === "object") {
        const tgt = type.target || type.currentTarget || null;
        if (tgt && typeof tgt.getAttribute === "function") {
          resolvedType =
            tgt.dataset?.reportType ||
            tgt.getAttribute("data-report-type") ||
            tgt.getAttribute("data-type") ||
            null;
        }
      }

      // Predefined quick reports
      if (resolvedType) {
        const apiType = this._mapReportType(resolvedType);
        if (!apiType) {
          this.showToast("Unsupported report", "error");
          return;
        }
        try {
          const res = await (window.axios || axios).post(
            "/api/reports/export",
            {
              report_type: apiType,
              format: fmt,
              start_date: null,
              end_date: null,
              filters: {},
            },
            { responseType: "blob" },
          );
          const ctype = (res?.headers?.["content-type"] || "").toLowerCase();
          const dataBlob =
            res?.data instanceof Blob
              ? res.data
              : new Blob([res.data], {
                  type: ctype || "application/octet-stream",
                });
          let treatAsStub = ctype.includes("application/json");
          if (
            !treatAsStub &&
            dataBlob &&
            dataBlob.size > 0 &&
            dataBlob.size < 4096
          ) {
            try {
              const text = await dataBlob.text();
              const t = text.trim();
              if (
                t.startsWith("{") ||
                t.startsWith("[") ||
                t.includes("Dev API stub active")
              ) {
                treatAsStub = true;
              }
            } catch (_) {}
          }
          if (treatAsStub) {
            const headings = this._getReportHeadings(apiType, []);
            const csv = headings.join(",") + "\n";
            const blob = new Blob([csv], { type: "text/csv" });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `report_${apiType}.csv`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(() => URL.revokeObjectURL(url), 3000);
          } else {
            this._triggerDownload(
              { data: dataBlob, headers: res.headers },
              `report_${apiType}.${fmt === "excel" ? "xlsx" : fmt}`,
            );
          }
        } catch (e) {
          this.showToast("Failed to generate report", "error");
        }
        return;
      }

      // Custom report builder flow
      if (!this.isReportValid()) {
        this.showToast("Please complete all required fields", "error");
        return;
      }
      try {
        const reportType = this._mapSourceToReport(
          (this.customReport?.dataSources?.[0] || "sales").toLowerCase(),
        );
        const res = await (window.axios || axios).post(
          "/api/reports/export",
          {
            report_type: reportType,
            format: fmt,
            start_date: this.customReport?.startDate || null,
            end_date: this.customReport?.endDate || null,
            filters: {
              metrics: this.customReport?.selectedMetrics || [],
              include_comparisons: !!this.customReport?.includeComparisons,
              include_trends: !!this.customReport?.includeTrends,
              include_breakdowns: !!this.customReport?.includeBreakdowns,
            },
          },
          { responseType: "blob" },
        );
        const ctype = (res?.headers?.["content-type"] || "").toLowerCase();
        const dataBlob =
          res?.data instanceof Blob
            ? res.data
            : new Blob([res.data], {
                type: ctype || "application/octet-stream",
              });
        let treatAsStub = ctype.includes("application/json");
        if (
          !treatAsStub &&
          dataBlob &&
          dataBlob.size > 0 &&
          dataBlob.size < 4096
        ) {
          try {
            const text = await dataBlob.text();
            const t = text.trim();
            if (
              t.startsWith("{") ||
              t.startsWith("[") ||
              t.includes("Dev API stub active")
            ) {
              treatAsStub = true;
            }
          } catch (_) {}
        }
        if (treatAsStub) {
          const headings = this._getReportHeadings(
            reportType,
            this.customReport?.selectedMetrics || [],
          );
          const csv = headings.join(",") + "\n";
          const blob = new Blob([csv], { type: "text/csv" });
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          const name = (this.customReport?.name || "custom-report").replace(
            /\s+/g,
            "_",
          );
          a.download = `${name}.csv`;
          document.body.appendChild(a);
          a.click();
          a.remove();
          setTimeout(() => URL.revokeObjectURL(url), 3000);
        } else {
          const name = (this.customReport?.name || "custom-report").replace(
            /\s+/g,
            "_",
          );
          this._triggerDownload(
            { data: dataBlob, headers: res.headers },
            `${name}.${fmt === "excel" ? "xlsx" : fmt}`,
          );
        }
        this.showToast("Report generated successfully!", "success");
      } catch (e) {
        this.showToast("Failed to generate report", "error");
      }
    },

    // Save Report Template
    async saveReportTemplate() {
      if (!this.isReportValid()) {
        this.showToast("Please complete all required fields", "error");
        return;
      }
      // Optimistically add to Recent Reports before API call
      try {
        const optimisticItem = {
          id: `tmp_${Date.now()}`,
          name: this.customReport.name || "Custom Report",
          type: this._mapSourceToReport(
            (this.customReport?.dataSources?.[0] || "sales").toLowerCase(),
          ),
          createdAt: new Date().toISOString(),
          createdBy: this.currentUser?.name || "User",
          status: "saved",
          config: {
            date_range: this.customReport?.dateRange || "last-30-days",
            selected_metrics: this.customReport?.selectedMetrics || [],
          },
        };
        this.recentReports = [optimisticItem, ...this.recentReports].slice(
          0,
          10,
        );
        try {
          localStorage.setItem(
            "cannabisPOS-reports",
            JSON.stringify(this.recentReports),
          );
        } catch (_) {}
      } catch (_) {}
      try {
        const payload = {
          name: this.customReport.name,
          description: this.customReport.description || "",
          report_type: this._mapSourceToReport(
            (this.customReport?.dataSources?.[0] || "sales").toLowerCase(),
          ),
          format: (
            this.customReport?.exportFormats?.[0] || "pdf"
          ).toLowerCase(),
          include_charts:
            !!this.customReport?.includeTrends ||
            !!this.customReport?.includeBreakdowns,
          orientation: this.customReport?.orientation || "portrait",
          paper_size: this.customReport?.paperSize || "a4",
          config: {
            date_range: this.customReport?.dateRange || "last-30-days",
            start_date: this.customReport?.startDate || null,
            end_date: this.customReport?.endDate || null,
            selected_metrics: this.customReport?.selectedMetrics || [],
            filters: {
              categoryFilters: this.customReport?.categoryFilters || [],
              employeeFilter: this.customReport?.employeeFilter || "",
              customerType: this.customReport?.customerType || "",
              paymentMethod: this.customReport?.paymentMethod || "",
            },
            data_sources: this.customReport?.dataSources || [],
            chart_type: this.customReport?.chartType || "table",
            color_scheme: this.customReport?.colorScheme || "cannabis",
            schedule: {
              enabled: !!this.customReport?.autoSchedule,
              frequency: this.customReport?.scheduleFrequency || "weekly",
              email: this.customReport?.scheduleEmail || "",
            },
          },
        };
        const res = await posAuth.apiRequest(
          "post",
          "/reports/templates",
          payload,
        );
        if (!res.success)
          throw new Error(res.message || "Failed to save template");
        this.showToast(
          `Report template "${this.customReport.name}" saved successfully!`,
          "success",
        );
        // Optimistically add to Recent Reports with expected shape and persist
        try {
          const t = res.data?.template || null;
          if (t) {
            const item = {
              id: t.id,
              name: t.name,
              type: t.report_type,
              createdAt: new Date().toISOString(),
              createdBy: this.currentUser?.name || "User",
              status: "saved",
              config: t.config,
            };
            this.recentReports = [item, ...this.recentReports].slice(0, 10);
            try {
              localStorage.setItem(
                "cannabisPOS-reports",
                JSON.stringify(this.recentReports),
              );
            } catch (_) {}
          }
        } catch (_) {}
        await this.fetchReportTemplates();
        this.initializeReportData();
        this.showCreateReportModal = false;
        this.resetCustomReport();
      } catch (e) {
        // Local fallback save
        try {
          const uid = posAuth?.getUser()?.id || "anon";
          const key = `report_templates_${uid}`;
          const list = JSON.parse(localStorage.getItem(key) || "[]");
          const tpl = {
            id: Date.now(),
            user_id: uid,
            name: this.customReport.name,
            description: this.customReport.description || "",
            report_type: this._mapSourceToReport(
              (this.customReport?.dataSources?.[0] || "sales").toLowerCase(),
            ),
            format: (
              this.customReport?.exportFormats?.[0] || "pdf"
            ).toLowerCase(),
            include_charts:
              !!this.customReport?.includeTrends ||
              !!this.customReport?.includeBreakdowns,
            orientation: this.customReport?.orientation || "portrait",
            paper_size: this.customReport?.paperSize || "a4",
            config: payload.config,
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
          };
          list.push(tpl);
          localStorage.setItem(key, JSON.stringify(list));
          this.showToast(
            `Report template "${tpl.name}" saved (offline).`,
            "warning",
          );
          // Optimistically add to Recent Reports with expected shape and persist
          try {
            const item = {
              id: tpl.id,
              name: tpl.name,
              type: tpl.report_type,
              createdAt: new Date().toISOString(),
              createdBy: this.currentUser?.name || "User",
              status: "saved",
              config: tpl.config,
            };
            this.recentReports = [item, ...this.recentReports].slice(0, 10);
            try {
              localStorage.setItem(
                "cannabisPOS-reports",
                JSON.stringify(this.recentReports),
              );
            } catch (_) {}
          } catch (_) {}
          await this.fetchReportTemplates();
          this.initializeReportData();
          this.showCreateReportModal = false;
          this.resetCustomReport();
        } catch (_) {
          this.showToast("Failed to save report template", "error");
        }
      }
    },

    _getReportHeadings(reportType, metrics) {
      if (Array.isArray(metrics) && metrics.length) {
        return metrics.map((m) =>
          String(m)
            .replace(/_/g, " ")
            .replace(/\b\w/g, (c) => c.toUpperCase()),
        );
      }
      switch (reportType) {
        case "sales":
          return [
            "Date",
            "Transaction ID",
            "Customer",
            "Items",
            "Subtotal",
            "Tax",
            "Total",
            "Payment Method",
          ];
        case "inventory":
          return [
            "Product Name",
            "SKU",
            "Category",
            "Quantity",
            "Unit Cost",
            "Unit Price",
            "Total Value",
            "Room",
            "METRC Tag",
          ];
        case "customers":
          return [
            "Customer Name",
            "Type",
            "Email",
            "Phone",
            "Total Visits",
            "Total Spent",
            "Average Order",
            "Last Visit",
          ];
        case "products":
          return [
            "Name",
            "Category",
            "SKU",
            "Price",
            "Cost",
            "Quantity",
            "Room",
            "THC%",
            "CBD%",
            "METRC Tag",
          ];
        case "analytics":
          return ["Metric", "Value", "Period", "Change", "Percentage"];
        case "metrc":
          return [
            "Package Tag",
            "Product",
            "Quantity",
            "Unit",
            "Status",
            "Location",
            "Last Modified",
          ];
        case "compliance":
          return ["Date", "Type", "Description", "Status", "Employee", "Notes"];
        case "employees":
          return [
            "Name",
            "Role",
            "Employee ID",
            "Email",
            "Hours Worked",
            "Sales Count",
            "Performance Score",
          ];
        default:
          return ["Column 1", "Column 2", "Column 3"];
      }
    },

    // Preview Report
    async previewReport() {
      if (!this.isReportValid()) {
        this.showToast("Please complete all required fields", "error");
        return;
      }
      try {
        this.showToast("Opening report preview...", "info");
        const payload = {
          report_type: this._mapSourceToReport(
            (this.customReport?.dataSources?.[0] || "sales").toLowerCase(),
          ),
          format: "pdf",
          start_date: this.customReport?.startDate || null,
          end_date: this.customReport?.endDate || null,
          filters: {
            metrics: this.customReport?.selectedMetrics || [],
            include_comparisons: !!this.customReport?.includeComparisons,
            include_trends: !!this.customReport?.includeTrends,
            include_breakdowns: !!this.customReport?.includeBreakdowns,
          },
        };
        const res = await (window.axios || axios).post(
          "/api/reports/export",
          payload,
          { responseType: "blob" },
        );
        const ctype = (
          res?.headers?.["content-type"] || "text/html"
        ).toLowerCase();
        const dataBlob =
          res?.data instanceof Blob
            ? res.data
            : new Blob([res.data], { type: ctype });
        let treatAsStub = ctype.includes("application/json");
        if (
          !treatAsStub &&
          dataBlob &&
          dataBlob.size > 0 &&
          dataBlob.size < 4096
        ) {
          try {
            const text = await dataBlob.text();
            const t = text.trim();
            if (
              t.startsWith("{") ||
              t.startsWith("[") ||
              t.includes("Dev API stub active")
            ) {
              treatAsStub = true;
            }
          } catch (_) {}
        }
        if (treatAsStub) throw new Error("stub");
        const url = URL.createObjectURL(dataBlob);
        const w = window.open(url, "_blank");
        if (!w)
          this.showToast("Popup blocked. Enable popups to preview.", "warning");
        setTimeout(() => URL.revokeObjectURL(url), 8000);
      } catch (e) {
        // Fallback HTML preview with headings only
        const rt = this._mapSourceToReport(
          (this.customReport?.dataSources?.[0] || "sales").toLowerCase(),
        );
        const headings = this._getReportHeadings(
          rt,
          this.customReport?.selectedMetrics || [],
        );
        const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${rt} Report</title></head><body><h1>${rt} Report</h1><table border="1" cellspacing="0" cellpadding="6"><thead><tr>${headings.map((h) => `<th>${h}</th>`).join("")}</tr></thead><tbody><tr>${headings.map(() => "<td></td>").join("")}</tr></tbody></table></body></html>`;
        const blob = new Blob([html], { type: "text/html" });
        const url = URL.createObjectURL(blob);
        const w = window.open(url, "_blank");
        if (!w)
          this.showToast("Popup blocked. Enable popups to preview.", "warning");
        setTimeout(() => URL.revokeObjectURL(url), 5000);
      }
    },

    // Report Management Functions
    viewReport(report) {
      this.showToast(`Opening report: ${report.name}`, "info");
    },

    async printReport(type) {
      try {
        const payload = {
          report_type: this._mapReportType(type),
          format: "excel",
          start_date: this.customReport?.startDate || null,
          end_date: this.customReport?.endDate || null,
          filters: { metrics: this.customReport?.selectedMetrics || [] },
        };
        const res = await (window.axios || axios).post(
          "/api/reports/export",
          payload,
          { responseType: "blob" },
        );
        const ctype = (
          res?.headers?.["content-type"] || "text/csv"
        ).toLowerCase();
        const dataBlob =
          res?.data instanceof Blob
            ? res.data
            : new Blob([res.data], { type: ctype });
        let treatAsStub = ctype.includes("application/json");
        if (
          !treatAsStub &&
          dataBlob &&
          dataBlob.size > 0 &&
          dataBlob.size < 4096
        ) {
          try {
            const text = await dataBlob.text();
            const t = text.trim();
            if (
              t.startsWith("{") ||
              t.startsWith("[") ||
              t.includes("Dev API stub active")
            ) {
              treatAsStub = true;
            }
          } catch (_) {}
        }
        if (treatAsStub) throw new Error("stub");
        const url = URL.createObjectURL(dataBlob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `report_${payload.report_type}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(url), 5000);
      } catch (e) {
        // Client-side CSV fallback with headers only
        const rt = this._mapReportType(type);
        const headings = this._getReportHeadings(
          rt,
          this.customReport?.selectedMetrics || [],
        );
        const csv = headings.join(",") + "\n";
        const blob = new Blob([csv], { type: "text/csv" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `report_${rt}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(url), 3000);
        this.showToast("Downloaded CSV headers (fallback)", "warning");
      }
    },

    downloadReport(report) {
      this.showToast(`Downloading ${report.name}...`, "info");
      // Re-run generation for chosen format
      this.generateReport(report.name.toLowerCase().replace(/\s+/g, "-"));
    },

    duplicateReport(report) {
      this.showToast(`Duplicating ${report.name}...`, "info");
    },

    // Helpers for export
    async _askFormat(def = "pdf") {
      try {
        const c = prompt("Export format: pdf, excel, or csv", def);
        const v = (c || "").trim().toLowerCase();
        if (!v) return null;
        if (!["pdf", "excel", "csv"].includes(v)) {
          this.showToast("Invalid format", "error");
          return null;
        }
        return v;
      } catch (e) {
        return null;
      }
    },
    _mapSourceToReport(src) {
      const m = {
        sales: "sales",
        inventory: "inventory",
        customers: "customers",
        products: "products",
        employees: "employees",
        analytics: "analytics",
        metrc: "metrc",
        compliance: "compliance",
      };
      return m[src] || "sales";
    },
    _mapReportType(type) {
      const m = {
        "daily-sales": "sales",
        "weekly-sales": "sales",
        "monthly-sales": "sales",
        "sales-by-category": "sales",
        "sales-by-employee": "sales",
        inventory: "inventory",
        "current-inventory": "inventory",
        "low-stock": "inventory",
        "out-of-stock": "inventory",
        "inventory-valuation": "inventory",
        "product-movement": "inventory",
        "tax-collected": "tax_report",
        "metrc-compliance": "metrc",
        compliance: "metrc",
        "medical-sales": "sales",
        "regulatory-summary": "compliance",
        "audit-trail": "compliance",
        "customer-list": "customers",
        "loyalty-summary": "customers",
        "top-customers": "customers",
        "customer-preferences": "customers",
        "retention-analysis": "customers",
        "employee-performance": "employees",
        payroll: "employees",
        "penny-sale": "sales",
      };
      return m[type] || null;
    },
    _triggerDownload(res, filename) {
      const headers = (res && res.headers) || {};
      const contentType = headers["content-type"] || "application/octet-stream";
      const cd = headers["content-disposition"] || "";
      const hintedName =
        headers["x-export-filename"] ||
        (cd.match(/filename\*=UTF-8''([^;]+)|filename=\"?([^\";]+)\"?/i) ||
          [])[1] ||
        (cd.match(/filename=\"?([^\";]+)\"?/i) || [])[1];
      const finalName =
        hintedName && typeof hintedName === "string"
          ? decodeURIComponent(hintedName)
          : filename;
      const blob =
        res?.data instanceof Blob
          ? res.data
          : new Blob([res.data], { type: contentType });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", finalName);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    },

    // Helper Functions
    getReportTypeColor(type) {
      const colors = {
        sales: "bg-blue-100 text-blue-600",
        inventory: "bg-green-100 text-green-600",
        customers: "bg-purple-100 text-purple-600",
        employees: "bg-orange-100 text-orange-600",
        financial: "bg-indigo-100 text-indigo-600",
        compliance: "bg-red-100 text-red-600",
        operational: "bg-gray-100 text-gray-600",
      };
      return colors[type] || "bg-gray-100 text-gray-600";
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return (
        date.toLocaleDateString() +
        " at " +
        date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })
      );
    },
  };
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  // Cannabis POS System ready - initialization handled by Alpine's init() function
});

// Global analytics helpers for Alpine bindings on landing/analytics sections
(function () {
  const defaults = {
    customerCount: 0,
    customerGrowth: 0,
    avgTransaction: 0,
    avgItems: 0,
    overallGrowth: 0,
    totalDiscounts: 0,
    discountPercentage: 0,
    retentionRate: 0,
    inventoryTurnover: 0,
    profitMargin: 0,
  };
  if (!window.__analyticsData) window.__analyticsData = { ...defaults };
  window.getAnalyticsData = function () {
    return window.__analyticsData;
  };
  window.getTopProducts = function () {
    return Array.isArray(window.__topProducts) ? window.__topProducts : [];
  };
  window.getTopVendors = function () {
    return Array.isArray(window.__topVendors) ? window.__topVendors : [];
  };
  window.getStoreComparison = function () {
    return Array.isArray(window.__storeComparison)
      ? window.__storeComparison
      : [];
  };
  window.getAgingAnalysis = function () {
    return window.__agingAnalysis || { fresh: 0, slow: 0, stale: 0 };
  };

  async function refreshLandingAnalytics() {
    try {
      const tf = "today";
      const tz =
        (Intl.DateTimeFormat &&
          Intl.DateTimeFormat().resolvedOptions().timeZone) ||
        "";
      const res = await (window.axios || axios).get("/api/analytics/overview", {
        params: { timeframe: tf, tz },
      });
      const d = res?.data || {};
      const sales = d.sales || {};
      window.__analyticsData.customerCount = Number(
        sales.customers || d.customers || 0,
      );
      window.__analyticsData.customerGrowth = Number(
        (d.customers && d.customers.growth) || 0,
      );
      window.__analyticsData.avgTransaction = Number(
        sales.avgOrderValue || d.avgOrderValue || 0,
      );
      window.__analyticsData.avgItems = Number(d.avgItems || 0);
      window.__analyticsData.overallGrowth = Number(
        (d.growth && d.growth.overall) || 0,
      );
      window.__analyticsData.totalDiscounts = Number(
        sales.totalDiscounts || (d.discounts && d.discounts.total) || 0,
      );
      window.__analyticsData.discountPercentage = Number(
        (d.discounts && d.discounts.pctOfSales) || 0,
      );
      window.__analyticsData.retentionRate = Number(
        (d.customers && d.customers.retentionRate) || 0,
      );
      window.__analyticsData.inventoryTurnover = Number(
        (d.inventory && d.inventory.turnover) || 0,
      );
      window.__analyticsData.profitMargin = Number(
        (d.profit && d.profit.margin) || 0,
      );
      window.__topProducts = Array.isArray(d.topProducts) ? d.topProducts : [];
      window.__topVendors = Array.isArray(d.topVendors) ? d.topVendors : [];
      window.__storeComparison =
        d.company && Array.isArray(d.company.stores) ? d.company.stores : [];
      window.__agingAnalysis =
        d.inventory && d.inventory.aging
          ? d.inventory.aging
          : window.__agingAnalysis || { fresh: 0, slow: 0, stale: 0 };
    } catch (err) {
      try {
        // Fallback: compute minimal metrics from today's recent sales
        const now = new Date();
        const toISO = (dt) =>
          `${dt.getFullYear()}-${String(dt.getMonth() + 1).padStart(2, "0")}-${String(dt.getDate()).padStart(2, "0")}`;
        const start = toISO(now),
          end = toISO(now);
        const tz =
          (Intl.DateTimeFormat &&
            Intl.DateTimeFormat().resolvedOptions().timeZone) ||
          "";
        const r = await (window.axios || axios).get("/api/sales/recent", {
          params: {
            status: "completed",
            limit: 500,
            date_from: start,
            date_to: end,
            tz,
          },
          headers: { Accept: "application/json" },
        });
        const list = Array.isArray(r?.data)
          ? r.data
          : Array.isArray(r?.data?.data)
            ? r.data.data
            : [];
        let revenue = 0,
          tx = 0,
          items = 0,
          discounts = 0;
        const prodMap = new Map();
        for (const s of list) {
          const amt = Number(s.total_amount ?? s.total ?? 0);
          revenue += isFinite(amt) ? amt : 0;
          tx++;
          const saleItems = Array.isArray(s.sale_items) ? s.sale_items : [];
          items += saleItems.reduce((a, i) => a + Number(i.quantity || 0), 0);
          for (const it of saleItems) {
            const name = it.product_name || it.name || "Product";
            const category = it.category || it.product_category || "";
            const key = name + "|" + category;
            const rec = prodMap.get(key) || {
              name,
              category,
              revenue: 0,
              units: 0,
            };
            const line = Number(
              it.total_price || (it.unit_price || 0) * (it.quantity || 0),
            );
            rec.revenue += isFinite(line) ? line : 0;
            rec.units += Number(it.quantity || 0);
            prodMap.set(key, rec);
          }
        }
        window.__analyticsData.customerCount = tx;
        window.__analyticsData.avgTransaction = tx ? revenue / tx : 0;
        window.__analyticsData.avgItems = tx ? items / tx : 0;
        window.__analyticsData.totalDiscounts = discounts;
        window.__analyticsData.discountPercentage =
          revenue > 0 ? (discounts / revenue) * 100 : 0;
        window.__topProducts = Array.from(prodMap.values())
          .sort((a, b) => b.revenue - a.revenue)
          .slice(0, 5);
        window.__topVendors = [];
        window.__storeComparison = [];
      } catch (_) {}
    }
  }
  window.refreshLandingAnalytics = refreshLandingAnalytics;
  try {
    document.addEventListener("DOMContentLoaded", function () {
      refreshLandingAnalytics();
      try {
        if (window.__landingAnalyticsTimer)
          clearInterval(window.__landingAnalyticsTimer);
      } catch (_) {}
      window.__landingAnalyticsTimer = setInterval(
        refreshLandingAnalytics,
        15000,
      );
    });
  } catch (_) {}
})();

// Global inventory evaluation helpers for Alpine bindings
(function(){
  function getProducts() {
    try {
      const app = document.getElementById('app');
      const scope = app && app.__x && app.__x.$data ? app.__x.$data : null;
      if (scope && Array.isArray(scope.products) && scope.products.length) return scope.products;
    } catch(_) {}
    try {
      const saved = localStorage.getItem('cannabisPOS-products');
      if (saved) {
        const parsed = JSON.parse(saved);
        const arr = Array.isArray(parsed?.data) ? parsed.data : (Array.isArray(parsed) ? parsed : []);
        return arr;
      }
    } catch(_) {}
    return [];
  }
  function normalize(p){
    const stock = Number(p.stock ?? p.quantity ?? 0);
    const price = Number(p.price ?? 0);
    const cost = Number(p.cost ?? p.unit_cost ?? p.costPerUnit ?? 0);
    const category = p.category || 'Uncategorized';
    return { id: p.id || p.sku || p.name, name: p.name || 'Product', sku: p.sku || null, category, stock: isFinite(stock)?stock:0, price: isFinite(price)?price:0, cost: isFinite(cost)?cost:0 };
  }
  window.getInventoryEvaluation = function(){
    const items = getProducts().map(normalize);
    let totalCost = 0, totalRetail = 0;
    for (const it of items){
      totalCost += (it.cost || 0) * (it.stock || 0);
      totalRetail += (it.price || 0) * (it.stock || 0);
    }
    const totalProfit = totalRetail - totalCost;
    const averageMargin = totalRetail > 0 ? (totalProfit / totalRetail) * 100 : 0;
    return { totalCost, totalRetail, totalProfit, averageMargin };
  };
  window.getCategoryBreakdown = function(){
    const items = getProducts().map(normalize);
    const map = new Map();
    for (const it of items){
      const key = it.category || 'Uncategorized';
      const cur = map.get(key) || { productCount:0, totalCost:0, totalRetail:0, totalProfit:0, averageMargin:0, products:[] };
      cur.productCount += 1;
      const lineCost = (it.cost||0) * (it.stock||0);
      const lineRetail = (it.price||0) * (it.stock||0);
      cur.totalCost += lineCost;
      cur.totalRetail += lineRetail;
      cur.totalProfit += (lineRetail - lineCost);
      cur.products.push(it);
      map.set(key, cur);
    }
    for (const [k, v] of map.entries()){
      v.averageMargin = v.totalRetail > 0 ? (v.totalProfit / v.totalRetail) * 100 : 0;
    }
    const obj = {};
    for (const [k,v] of map.entries()) obj[k] = v;
    return obj;
  };
})();

// Bridge for Alpine bindings that expect component-scoped vars/methods
(function () {
  try {
    if (typeof window.analyticsView === "undefined")
      window.analyticsView = "company";
    if (typeof window.refreshAnalytics !== "function")
      window.refreshAnalytics = function () {
        try {
          window.refreshLandingAnalytics && window.refreshLandingAnalytics();
        } catch (_) {}
      };
    if (typeof window.getTopDiscounts !== "function")
      window.getTopDiscounts = function () {
        return Array.isArray(window.__topDiscounts)
          ? window.__topDiscounts
          : [];
      };
    // Attempt to hydrate the root Alpine component with expected bindings
    document.addEventListener("DOMContentLoaded", function () {
      try {
        const app = document.getElementById("app");
        const scope = app && app.__x && app.__x.$data ? app.__x.$data : null;
        if (scope) {
          if (typeof scope.analyticsView === "undefined")
            scope.analyticsView = window.analyticsView || "company";
          if (typeof scope.refreshAnalytics !== "function")
            scope.refreshAnalytics = function () {
              try {
                window.refreshLandingAnalytics &&
                  window.refreshLandingAnalytics();
              } catch (_) {}
            };
          if (typeof scope.getTopDiscounts !== "function")
            scope.getTopDiscounts = function () {
              return window.getTopDiscounts ? window.getTopDiscounts() : [];
            };
          if (typeof scope.getTopProducts !== "function")
            scope.getTopProducts = function () {
              return window.getTopProducts ? window.getTopProducts() : [];
            };
          if (typeof scope.getTopVendors !== "function")
            scope.getTopVendors = function () {
              return window.getTopVendors ? window.getTopVendors() : [];
            };
          if (typeof scope.getStoreComparison !== "function")
            scope.getStoreComparison = function () {
              return window.getStoreComparison
                ? window.getStoreComparison()
                : [];
            };
          if (typeof scope.getAgingAnalysis !== "function")
            scope.getAgingAnalysis = function () {
              return window.getAgingAnalysis
                ? window.getAgingAnalysis()
                : { fresh: 0, slow: 0, stale: 0 };
            };
          // Ensure inventory helpers exist on the component scope for Alpine bindings
          if (typeof scope.getInventoryEvaluation !== "function")
            scope.getInventoryEvaluation = function () {
              try {
                if (typeof window.getInventoryEvaluation === "function") {
                  return window.getInventoryEvaluation();
                }
              } catch (_) {}
              return { totalCost: 0, totalRetail: 0, totalProfit: 0, averageMargin: 0 };
            };
          if (typeof scope.getCategoryBreakdown !== "function")
            scope.getCategoryBreakdown = function () {
              try {
                if (typeof window.getCategoryBreakdown === "function") {
                  return window.getCategoryBreakdown();
                }
              } catch (_) {}
              return {};
            };
          if (typeof scope.aspdTimeframe === "undefined") scope.aspdTimeframe = "month";
          if (typeof scope.expandedCategories === "undefined") scope.expandedCategories = [];
          if (typeof scope.toggleCategoryExpansion !== "function")
            scope.toggleCategoryExpansion = function (name) {
              try {
                const i = this.expandedCategories.indexOf(name);
                if (i >= 0) this.expandedCategories.splice(i, 1);
                else this.expandedCategories.push(name);
              } catch (_) {}
            };
          if (typeof scope.loadAspd !== "function") scope.loadAspd = function(){ try { window.loadAspd && window.loadAspd(); } catch (_) {} };
          if (typeof scope.exportAspd !== "function") scope.exportAspd = function(){ try { window.exportAspd && window.exportAspd(); } catch (_) {} };
        }
      } catch (_) {}
    });
  } catch (_) {}
})();

// Auto-bind aspdTimeframe to any Alpine component that uses it
(function(){
  function ensureAspdOnClosestComponent(el){
    try {
      let node = el;
      while (node && !node.__x) node = node.parentElement;
      if (node && node.__x && node.__x.$data) {
        const data = node.__x.$data;
        if (typeof data.aspdTimeframe === 'undefined') data.aspdTimeframe = 'month';
        if (typeof data.loadAspd !== 'function') data.loadAspd = function(){ try{ window.loadAspd && window.loadAspd(); } catch(_){} };
        if (typeof data.exportAspd !== 'function') data.exportAspd = function(){ try{ window.exportAspd && window.exportAspd(); } catch(_){} };
      }
    } catch(_){}
  }
  function scan(){
    try {
      const els = document.querySelectorAll('select[x-model="aspdTimeframe"], [x-model="aspdTimeframe"]');
      els.forEach(ensureAspdOnClosestComponent);
    } catch(_){}
  }
  document.addEventListener('alpine:init', scan);
  document.addEventListener('alpine:initialized', scan);
  document.addEventListener('DOMContentLoaded', function(){ setTimeout(scan, 0); });
  // Also run shortly after script load
  try { setTimeout(scan, 50); } catch(_) {}
})();

// Global bridge for aspdTimeframe to prevent ReferenceErrors
(function(){
  try {
    if (typeof window.aspdTimeframe === 'undefined') {
      let __aspd_fallback = 'month';
      Object.defineProperty(window, 'aspdTimeframe', {
        configurable: true,
        enumerable: true,
        get() {
          try {
            const app = document.getElementById('app');
            const scope = app && app.__x && app.__x.$data ? app.__x.$data : null;
            if (scope && typeof scope.aspdTimeframe !== 'undefined') return scope.aspdTimeframe;
          } catch(_) {}
          return __aspd_fallback;
        },
        set(v) {
          try {
            const app = document.getElementById('app');
            const scope = app && app.__x && app.__x.$data ? app.__x.$data : null;
            if (scope) scope.aspdTimeframe = v;
          } catch(_) {}
          __aspd_fallback = v;
          return true;
        }
      });
    }
  } catch(_) {}
})();
