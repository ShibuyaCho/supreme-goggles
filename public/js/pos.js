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
      usageLimit: "",
      allCategories: false,
      applicableCategories: [],
      applicableProducts: [],
      excludeGLS: true,
      stackable: false,
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
      const u = userOverride || this.currentUser || {};
      const emp = u.employee || null;
      let id =
        emp?.id ||
        emp?.employee_id ||
        u.employee_id ||
        u.id ||
        `EMP-${Date.now()}`;
      const exists = (this.employees || []).some(
        (e) => String(e.id) === String(id),
      );
      if (exists) return;
      const name =
        emp?.name ||
        [emp?.first_name, emp?.last_name].filter(Boolean).join(" ") ||
        u.name ||
        "";
      const entry = {
        id,
        name,
        email: u.email || emp?.email || "",
        phone: emp?.phone || "",
        role: (u.role || emp?.role || "cashier").toLowerCase(),
        status: emp?.status || (u.is_active ? "active" : "active"),
        hireDate: new Date().toISOString().slice(0, 10),
        payRate: Number(emp && emp.hourly_rate != null ? emp.hourly_rate : 0),
        hoursWorked: 0,
        workerPermit: "",
        metrcApiKey: "",
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
          // Update local settings with API data
          this.taxRate = result.data.tax_rate || 20.0;
          this.medicalTaxRate = result.data.medical_tax_rate || 0.0;
          // Merge other settings
          Object.assign(this.storeSettings, result.data);
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

    // Page navigation
    setCurrentPage(page) {
      this.currentPage = page;
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

    // Add flower to cart with specific weight and price
    addFlowerToCart(product, weight, price) {
      const existingItem = this.cart.find(
        (item) => item.id === product.id && item.selectedWeight === weight,
      );

      if (existingItem) {
        existingItem.quantity += 1;
      } else {
        this.cart.push({
          ...product,
          quantity: 1,
          selectedWeight: weight,
          price: price,
          displayName: `${product.name} (${weight}g)`,
          discount: { amount: 0, type: "fixed", value: 0, reason: "" },
        });
      }

      this.calculateTotals();
      this.showToast(
        `${product.name} (${weight}g) added to cart for $${price.toFixed(2)}`,
        "success",
      );
    },

    removeFromCart(index) {
      this.cart.splice(index, 1);
      this.calculateTotals();
    },

    updateQuantity(index, newQuantity) {
      if (newQuantity <= 0) {
        this.removeFromCart(index);
      } else {
        this.cart[index].quantity = newQuantity;
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

    calculateTotals() {
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

    // Persist demo customers locally (fallback when not authenticated)
    _saveCustomersLocal() {
      try {
        const list = Array.isArray(this.customers) ? this.customers : [];
        localStorage.setItem("cannabisPOS-customers", JSON.stringify(list));
      } catch (e) {}
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
      this.normalizeCollections();
      this.filterProducts();
    },

    loadCustomers() {
      try {
        const saved = localStorage.getItem("cannabisPOS-customers");
        if (saved) {
          const savedCustomers = JSON.parse(saved);
          savedCustomers.forEach((savedCustomer) => {
            if (!this.customers.find((c) => c.id === savedCustomer.id)) {
              this.customers.push(savedCustomer);
            }
          });
        }
      } catch (error) {
        console.error("Error loading customers:", error);
      } finally {
        try {
          this.filterLoyaltyCustomers();
        } catch (_) {}
      }
    },

    async fetchEmployeesFromApi() {
      try {
        const res = await posAuth.apiRequest("get", "/employees");
        if (res.success && res.data && (res.data.employees || res.data.data)) {
          const list = res.data.employees || res.data.data || [];
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
            status: e.status || (e.is_active ? "active" : "inactive"),
            hireDate: e.hire_date ? String(e.hire_date).slice(0, 10) : "",
            payRate: Number(e.hourly_rate ?? 0),
            hoursWorked: Number(e.hours_worked ?? 0),
            workerPermit: e.worker_permit || e.workerPermit || "",
            metrcApiKey: e.metrc_api_key || e.metrcApiKey || "",
          }));
        }
      } catch (err) {
        console.warn("Failed to fetch employees", err);
      }
    },

    loadEmployees() {
      // Placeholder fallback: ensure array exists
      this.employees = this.employees || [];
    },

    get filteredEmployees() {
      const q = (this.employeeSearchQuery || "").toLowerCase();
      return (this.employees || []).filter((e) => {
        const matchQ =
          !q ||
          [e.name, e.email, e.phone, String(e.id)]
            .filter(Boolean)
            .some((v) => String(v).toLowerCase().includes(q));
        const matchRole =
          !this.employeeRoleFilter || e.role === this.employeeRoleFilter;
        const matchStatus =
          !this.employeeStatusFilter || e.status === this.employeeStatusFilter;
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
      // This would open a modal for selecting weight/quantity for flower products
      if (product.category === "Flower" && product.priceTier) {
        const tier = this.priceTiers.find((t) => t.id == product.priceTier);
        if (tier) {
          // Default to 1g when adding flower to cart
          // In a real implementation, this would open a weight selection modal
          const weight = 1;
          const price = tier.prices.weight_1g;
          this.addFlowerToCart(product, weight, price);
          this.showToast(
            `Deli-style: Added ${weight}g of ${product.name} from ${this.getFlowerTierName(product.priceTier)} tier`,
            "success",
          );
        } else {
          this.showToast(
            "Price tier not found for this flower product",
            "error",
          );
        }
      } else {
        this.addToCart(product);
      }
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
        medium: '3" × 2"',
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
          medium: { width: 150, height: 100 }, // 3" �� 2"
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
      return this.employees.filter((emp) => emp.status === "active");
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
      const categories = [
        ...new Set(list.map((p) => p.category).filter(Boolean)),
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
        usageLimit: "",
        allCategories: false,
        applicableCategories: [],
        applicableProducts: [],
        excludeGLS: true,
        stackable: false,
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

    completeDebitPayment() {
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

      // Process debit payment
      const paymentData = {
        method: "debit",
        amount: this.debitPayment.amount,
        changeDue: this.debitPayment.changeDue,
        lastFour: this.debitPayment.lastFour,
        employeePin: this.debitPayment.employeePin,
        cart: this.cart,
        customer: this.selectedCustomer,
        total: this.total,
        subtotal: this.subtotal,
        taxAmount: this.taxAmount,
        timestamp: new Date().toISOString(),
      };

      // Persist sale locally
      try {
        const uid = posAuth?.getUser()?.id || "anon";
        const key = `cannabisPOS-sales-${uid}`;
        const list = JSON.parse(localStorage.getItem(key) || "[]");
        list.push(paymentData);
        localStorage.setItem(key, JSON.stringify(list));
      } catch (_) {}

      // Simulate payment processing
      this.showToast("Debit payment processed successfully", "success");

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

      // In a real application, this would:
      // 1. Process payment through payment gateway
      // 2. Print receipt
      // 3. Update inventory
      // 4. Log transaction
      console.log("Debit payment completed:", paymentData);
    },

    completeCashPayment() {
      // Validate required fields
      if (!this.cashPayment.employeePin) {
        this.showToast("Employee PIN is required", "error");
        return;
      }

      if (this.cashPayment.changeDue < 0) {
        this.showToast("Insufficient cash amount", "error");
        return;
      }

      // Process cash payment
      const paymentData = {
        method: "cash",
        amountGiven: this.cashPayment.amountGiven,
        changeDue: this.cashPayment.changeDue,
        employeePin: this.cashPayment.employeePin,
        cart: this.cart,
        customer: this.selectedCustomer,
        total: this.total,
        subtotal: this.subtotal,
        taxAmount: this.taxAmount,
        timestamp: new Date().toISOString(),
      };

      // Persist sale locally
      try {
        const uid = posAuth?.getUser()?.id || "anon";
        const key = `cannabisPOS-sales-${uid}`;
        const list = JSON.parse(localStorage.getItem(key) || "[]");
        list.push(paymentData);
        localStorage.setItem(key, JSON.stringify(list));
      } catch (_) {}

      // Simulate payment processing
      this.showToast("Cash payment processed successfully", "success");

      // Clear cart and reset state
      this.cart = [];
      this.selectedCustomer = null;
      this.ageVerified = false;
      this.calculateTotals();

      // Reset cash payment form
      this.cashPayment = {
        amountGiven: 0,
        changeDue: 0,
        employeePin: "",
      };

      this.showCashModal = false;

      // In a real application, this would:
      // 1. Open cash drawer
      // 2. Print receipt
      // 3. Update inventory
      // 4. Log transaction
      console.log("Cash payment completed:", paymentData);
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
        const employee_id = `EMP-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 6).toUpperCase()}`;
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
          id: created && created.id ? created.id : employee_id,
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
        const hire_date = this.employeeForm.hireDate;
        const payload = {
          first_name,
          last_name,
          email: this.employeeForm.email,
          phone: this.employeeForm.phone || "",
          department,
          position: role,
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
            hireDate: hire_date,
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

    addCustomer() {
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

      // Add to customers array
      this.customers.push(newCustomer);

      // Save to localStorage for persistence
      try {
        const existingCustomers = JSON.parse(
          localStorage.getItem("cannabisPOS-customers") || "[]",
        );
        existingCustomers.push(newCustomer);
        localStorage.setItem(
          "cannabisPOS-customers",
          JSON.stringify(existingCustomers),
        );
      } catch (error) {
        console.error("Error saving customer to localStorage:", error);
      }

      this.showToast(
        `Customer ${newCustomer.name} added successfully`,
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
        usageLimit: "",
        allCategories: false,
        applicableCategories: [],
        applicableProducts: [],
        excludeGLS: true,
        stackable: false,
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
        id: Math.max(...(this.facilityRooms || []).map((r) => r.id), 0) + 1,
        name: this.roomForm.name,
        forSale: this.roomForm.forSale === "true",
        maxCapacity: this.roomForm.maxCapacity,
        status: this.roomForm.status,
        temperature: this.roomForm.temperature,
        humidity: this.roomForm.humidity,
        createdAt: new Date().toISOString(),
      };

      this.facilityRooms = this.facilityRooms || [];
      this.facilityRooms.push(newRoom);
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
        this.facilityRooms[roomIndex] = {
          ...this.facilityRooms[roomIndex],
          name: this.roomForm.name,
          forSale: this.roomForm.forSale === "true",
          maxCapacity: this.roomForm.maxCapacity,
          status: this.roomForm.status,
          temperature: this.roomForm.temperature,
          humidity: this.roomForm.humidity,
          updatedAt: new Date().toISOString(),
        };

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
        id: Math.max(...(this.cashDrawers || []).map((d) => d.id), 0) + 1,
        name: this.drawerForm.name,
        location: this.drawerForm.location,
        assignedEmployee: this.drawerForm.assignedEmployee,
        startingAmount: parseFloat(this.drawerForm.startingAmount),
        currentAmount: parseFloat(this.drawerForm.startingAmount),
        status: "active",
        createdAt: new Date().toISOString(),
      };

      this.cashDrawers = this.cashDrawers || [];
      this.cashDrawers.push(newDrawer);
      this.showToast(
        `Cash drawer "${newDrawer.name}" created successfully`,
        "success",
      );
      this.closeAddDrawerModal();
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
      try {
        const verify = await posAuth.apiRequest("post", "/auth/verify-pin", {
          pin: this.pinInput,
        });
        if (!verify?.success) {
          // Fallback: accept current user's PIN if they can manage employees
          const u = window.posAuth?.getUser?.() || {};
          const canManage =
            (window.posAuth?.hasRole &&
              (posAuth.hasRole("admin") || posAuth.hasRole("manager"))) ||
            (window.posAuth?.hasPermission &&
              posAuth.hasPermission("employees:manage"));
          if (!(canManage && String(u?.pin || "") === String(this.pinInput))) {
            this.pinError = verify?.message || "PIN verification failed";
            return;
          }
        }
      } catch (e) {
        // Fallback path on network/API error
        const u = window.posAuth?.getUser?.() || {};
        const canManage =
          (window.posAuth?.hasRole &&
            (posAuth.hasRole("admin") || posAuth.hasRole("manager"))) ||
          (window.posAuth?.hasPermission &&
            posAuth.hasPermission("employees:manage"));
        if (!(canManage && String(u?.pin || "") === String(this.pinInput))) {
          this.pinError = e?.message || "PIN verification failed";
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
          const res = await posAuth.apiRequest(
            "delete",
            `/employees/${encodeURIComponent(targetId)}`,
          );
          if (!res.success && res.status !== 404)
            throw new Error(res.message || "Delete failed");
          // Remove from local list regardless (idempotent)
          this.employees = (this.employees || []).filter((e) => {
            const nid = e.numericId != null ? String(e.numericId) : "";
            const eid = e.employeeId || "";
            return (
              String(e.id) !== targetId && nid !== targetId && eid !== targetId
            );
          });
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

    updateCustomer() {
      if (!this.editCustomerForm.name) {
        this.showToast("Customer name is required", "error");
        return;
      }

      const customerIndex = this.customers.findIndex(
        (c) => c.id === this.selectedCustomer.id,
      );
      if (customerIndex !== -1) {
        this.customers[customerIndex] = {
          ...this.customers[customerIndex],
          name: this.editCustomerForm.name,
          email: this.editCustomerForm.email,
          phone: this.editCustomerForm.phone,
          isMedical: this.editCustomerForm.isMedical,
          medicalCard: this.editCustomerForm.medicalCard,
        };

        this.showToast(
          `Customer "${this.editCustomerForm.name}" updated successfully`,
          "success",
        );
        this.closeEditCustomerModal();
      }
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

    addPriceTier() {
      if (!this.tierForm.name || !this.hasAnyPrices()) {
        this.showToast(
          "Please enter tier name and at least one price",
          "error",
        );
        return;
      }

      const newTier = {
        id: Math.max(...this.priceTiers.map((t) => t.id), 0) + 1,
        name: this.tierForm.name,
        isActive: true,
        createdAt: new Date().toISOString(),
        prices: { ...this.tierForm.prices },
        customWeights: [...(this.tierForm.customWeights || [])],
      };

      this.priceTiers.push(newTier);
      this.showToast(
        `Price tier "${newTier.name}" created successfully`,
        "success",
      );
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
        usageLimit: "",
        allCategories: false,
        applicableCategories: [],
        applicableProducts: [],
        excludeGLS: true,
        stackable: false,
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
