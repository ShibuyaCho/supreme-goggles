import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Switch } from "@/components/ui/switch";
import { Separator } from "@/components/ui/separator";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Progress } from "@/components/ui/progress";
import {
  Plus,
  Upload,
  Download,
  Package,
  Leaf,
  Database,
  FileText,
  CheckCircle,
  AlertCircle,
  Clock,
  RefreshCw,
  Search,
  Filter,
  MoreHorizontal,
  Eye,
  Edit,
  Trash2,
  ArrowRightLeft,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  Grid3X3,
  List
} from "lucide-react";

interface NewProduct {
  name: string;
  category: string;
  price: number;
  cost: number;
  sku: string;
  weight: string;
  room?: string;
  strain?: string;
  thc?: number;
  cbd?: number;
  cbg?: number;
  cbn?: number;
  cbc?: number;
  thcMg?: number;
  cbdMg?: number;
  cbgMg?: number;
  cbnMg?: number;
  cbcMg?: number;
  supplier?: string;
  grower?: string;
  farm?: string;
  vendor?: string;
  packagedDate?: string;
  expirationDate?: string;
  isUntaxed?: boolean;
  isGLS?: boolean;
  description?: string;
  batchNotes?: string;
  image?: string;
}

interface MetrcImport {
  id: string;
  fileName: string;
  uploadDate: string;
  status: 'pending' | 'processing' | 'completed' | 'failed';
  recordsTotal: number;
  recordsProcessed: number;
  recordsSuccessful: number;
  recordsFailed: number;
  errors?: string[];
  vendorName?: string;
  vendorLicense?: string;
  facilityName?: string;
  facilityLicense?: string;
  importType?: 'packages' | 'inventory' | 'harvest' | 'plants' | 'transfers';
  metrcApiResponse?: string;
  items?: ImportItem[];
}

interface ImportItem {
  id: string;
  name: string;
  category: string;
  price: number;
  cost: number;
  sku?: string;
  weight: string;
  room?: string;
  strain?: string;
  thc?: number;
  cbd?: number;
  cbg?: number;
  cbn?: number;
  cbc?: number;
  supplier?: string;
  grower?: string;
  farm?: string;
  vendor?: string;
  packagedDate?: string;
  expirationDate?: string;
  isUntaxed?: boolean;
  isGLS?: boolean;
  description?: string;
  metrcTag?: string;
  batchId?: string;
}

interface ProductCatalogueItem {
  id: string;
  name: string;
  category: string;
  basePrice: number;
  baseCost: number;
  defaultWeight: string;
  strain?: string;
  description: string;
  isTemplate: boolean;
  locationId: string;
  locationName: string;
  createdDate: string;
  lastModified: string;
  isActive: boolean;
  defaultRoom?: string;
  supplier?: string;
  vendor?: string;
  grower?: string;
  farm?: string;
  defaultSkuPattern?: string;
  cannabinoidProfile?: {
    thc?: number;
    cbd?: number;
    cbg?: number;
    cbn?: number;
    cbc?: number;
  };
}

interface MetrcImportMapping {
  importItemId: string;
  catalogueItemId: string;
  priceOverride?: number;
  costOverride?: number;
  weightOverride?: string;
  roomAssignment?: string;
  notes?: string;
}

const categories = [
  "Flower", "Pre-Rolls", "Concentrates", "Extracts", "Edibles", "Topicals",
  "Tinctures", "Vapes", "Inhalable Cannabinoids", "Clones", "Hemp", "Paraphernalia", "Accessories"
];

const strains = [
  "Sativa", "Indica", "Hybrid", "CBD-Dominant", "1:1 THC:CBD", "High-CBD", "Mixed"
];

const availableRooms = [
  { id: "main-storage", name: "Main Storage Vault" },
  { id: "premium-storage", name: "Premium Storage" },
  { id: "secure-vault", name: "Secure Vault" },
  { id: "exotic-storage", name: "Exotic Storage" },
  { id: "clone-room", name: "Clone Room" },
  { id: "edibles-storage", name: "Edibles Storage" },
  { id: "sales-floor", name: "Sales Floor" },
  { id: "processing-lab", name: "Processing Lab" },
  { id: "quarantine", name: "Quarantine Room" },
  { id: "back-room", name: "Back Room" }
];

// Sample inventory products (not on sales floor)
const inventoryProducts = [
  {
    id: "inv1",
    name: "OG Kush Reserve",
    category: "Flower",
    price: 12.00,
    cost: 6.00,
    stock: 89,
    room: "Secure Vault",
    sku: "OGK-RES-1G",
    weight: "1g",
    thc: 24.5,
    cbd: 0.3,
    strain: "Indica",
    metrcTag: "1A4000000000022000000201",
    batchId: "OGK240120",
    packagedDate: "2024-01-20",
    expirationDate: "2025-01-20",
    supplier: "Premium Cannabis Co",
    vendor: "Pacific Coast Cannabis"
  },
  {
    id: "inv2",
    name: "Bulk Shake Mix",
    category: "Flower",
    price: 2.50,
    cost: 1.25,
    stock: 2500,
    room: "Main Storage Vault",
    sku: "BULK-SHAKE-1G",
    weight: "1g",
    thc: 15.2,
    cbd: 0.5,
    strain: "Mixed",
    metrcTag: "1A4000000000022000000202",
    batchId: "BULK240118",
    packagedDate: "2024-01-18",
    expirationDate: "2025-01-18",
    supplier: "Wholesale Cannabis Supply",
    vendor: "Budget Cannabis Distributors"
  },
  {
    id: "inv3",
    name: "Premium Live Resin Cart",
    category: "Vapes",
    price: 85.00,
    cost: 42.50,
    stock: 45,
    room: "Secure Vault",
    sku: "LR-CART-1G",
    weight: "1g",
    thc: 78.5,
    cbd: 0.1,
    strain: "Hybrid",
    metrcTag: "1A4000000000022000000203",
    batchId: "LR240119",
    packagedDate: "2024-01-19",
    expirationDate: "2025-01-19",
    supplier: "Extract Artisans",
    vendor: "Elite Vape Solutions"
  },
  {
    id: "inv4",
    name: "Edible Bulk Gummies",
    category: "Edibles",
    price: 18.00,
    cost: 9.00,
    stock: 350,
    room: "Edibles Storage",
    sku: "BULK-GUM-10MG",
    weight: "10mg",
    thc: 10,
    cbd: 0,
    metrcTag: "1A4000000000022000000204",
    batchId: "GUM240117",
    packagedDate: "2024-01-17",
    expirationDate: "2025-07-17",
    supplier: "Edible Creations Co",
    vendor: "Sweet Treats Distribution"
  },
  {
    id: "inv5",
    name: "Hash - Premium Bubble",
    category: "Concentrates",
    price: 95.00,
    cost: 47.50,
    stock: 12,
    room: "Secure Vault",
    sku: "HASH-BUBBLE-1G",
    weight: "1g",
    thc: 65.8,
    cbd: 1.2,
    metrcTag: "1A4000000000022000000205",
    batchId: "HASH240115",
    packagedDate: "2024-01-15",
    expirationDate: "2025-07-15",
    supplier: "Artisan Hash Collective",
    vendor: "Concentrate Kings"
  },
  {
    id: "inv6",
    name: "CBD Topical Balm Bulk",
    category: "Topicals",
    price: 35.00,
    cost: 17.50,
    stock: 78,
    room: "Back Room",
    sku: "CBD-BALM-50ML",
    weight: "50ml",
    thc: 0,
    cbd: 250,
    metrcTag: "1A4000000000022000000206",
    batchId: "BALM240116",
    packagedDate: "2024-01-16",
    expirationDate: "2025-07-16",
    supplier: "Wellness Products Inc",
    vendor: "Therapeutic Solutions"
  }
];

// Sample product catalogue for current location
const sampleCatalogueItems: ProductCatalogueItem[] = [
  {
    id: "cat-001",
    name: "Premium Indoor Flower Template",
    category: "Flower",
    basePrice: 12.00,
    baseCost: 6.00,
    defaultWeight: "1g",
    strain: "Varies",
    description: "High-quality indoor grown flower with premium genetics",
    isTemplate: true,
    locationId: "location-001",
    locationName: "Main Dispensary",
    createdDate: "2024-01-01",
    lastModified: "2024-01-15",
    isActive: true,
    defaultRoom: "Secure Vault",
    supplier: "Premium Cannabis Co",
    vendor: "Elite Distributors",
    grower: "Craft Cultivation",
    farm: "Emerald Fields",
    defaultSkuPattern: "PREM-{STRAIN}-1G",
    cannabinoidProfile: {
      thc: 20,
      cbd: 0.5,
      cbg: 0.3,
      cbn: 0.2,
      cbc: 0.1
    }
  },
  {
    id: "cat-002",
    name: "Budget Flower Template",
    category: "Flower",
    basePrice: 4.00,
    baseCost: 2.00,
    defaultWeight: "1g",
    strain: "Mixed",
    description: "Value-priced flower options for budget-conscious customers",
    isTemplate: true,
    locationId: "location-001",
    locationName: "Main Dispensary",
    createdDate: "2024-01-01",
    lastModified: "2024-01-10",
    isActive: true,
    defaultRoom: "Main Storage Vault",
    supplier: "Budget Cannabis Supply",
    vendor: "Value Distributors",
    grower: "Wholesale Farms",
    farm: "Budget Buds",
    defaultSkuPattern: "BUD-{STRAIN}-1G"
  },
  {
    id: "cat-003",
    name: "Live Resin Cartridge Template",
    category: "Vapes",
    basePrice: 65.00,
    baseCost: 32.50,
    defaultWeight: "1g",
    description: "Premium live resin vape cartridges with full-spectrum effects",
    isTemplate: true,
    locationId: "location-001",
    locationName: "Main Dispensary",
    createdDate: "2024-01-01",
    lastModified: "2024-01-12",
    isActive: true,
    defaultRoom: "Secure Vault",
    supplier: "Extract Artisans",
    vendor: "Vape Solutions",
    grower: "Premium Extracts Co",
    farm: "Elite Gardens",
    defaultSkuPattern: "LR-{STRAIN}-1G",
    cannabinoidProfile: {
      thc: 80,
      cbd: 0.1
    }
  },
  {
    id: "cat-004",
    name: "Gummy Edibles Template",
    category: "Edibles",
    basePrice: 25.00,
    baseCost: 12.50,
    defaultWeight: "100mg",
    description: "Fruit-flavored cannabis gummies in various dosages",
    isTemplate: true,
    locationId: "location-001",
    locationName: "Main Dispensary",
    createdDate: "2024-01-01",
    lastModified: "2024-01-08",
    isActive: true,
    defaultRoom: "Edibles Storage",
    supplier: "Edible Creations Co",
    vendor: "Sweet Distribution",
    grower: "Source Cannabis",
    farm: "Edible Source Farm",
    defaultSkuPattern: "GUM-{FLAVOR}-{MG}MG",
    cannabinoidProfile: {
      thc: 10,
      cbd: 0
    }
  }
];

export default function Products() {
  // Check URL parameters to determine initial tab
  const urlParams = new URLSearchParams(window.location.search);
  const showInventory = urlParams.get('tab') === 'inventory';
  const [activeTab, setActiveTab] = useState(showInventory ? "inventory" : "create");
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [showImportDialog, setShowImportDialog] = useState(false);
  const [importFile, setImportFile] = useState<File | null>(null);
  const [importProgress, setImportProgress] = useState(0);
  const [isImporting, setIsImporting] = useState(false);
  const [showImportDetailsDialog, setShowImportDetailsDialog] = useState(false);
  const [selectedImportRecord, setSelectedImportRecord] = useState<MetrcImport | null>(null);
  const [importingToInventory, setImportingToInventory] = useState(false);
  const [showImportItemDialog, setShowImportItemDialog] = useState(false);
  const [currentImportItems, setCurrentImportItems] = useState<ImportItem[]>([]);
  const [currentImportIndex, setCurrentImportIndex] = useState(0);
  const [editingImportItem, setEditingImportItem] = useState<ImportItem | null>(null);
  const [processedItems, setProcessedItems] = useState<ImportItem[]>([]);

  // Inventory filtering state
  const [inventorySearchQuery, setInventorySearchQuery] = useState("");
  const [selectedRoom, setSelectedRoom] = useState("all");
  const [selectedCategory, setSelectedCategory] = useState("all");
  const [sortBy, setSortBy] = useState<'name' | 'price' | 'cost' | 'vendor' | 'quantity'>('name');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');

  // Inventory action states
  const [showTransferDialog, setShowTransferDialog] = useState(false);
  const [showEditInventoryDialog, setShowEditInventoryDialog] = useState(false);
  const [selectedInventoryItem, setSelectedInventoryItem] = useState<any>(null);
  const [transferQuantity, setTransferQuantity] = useState("");
  const [editingInventoryItem, setEditingInventoryItem] = useState<any>(null);
  const [inventoryItems, setInventoryItems] = useState(inventoryProducts);

  // Get view mode from settings - connected to Settings page
  const [viewMode, setViewMode] = useState<'cards' | 'list'>(() => {
    // Try to get from localStorage or default to 'cards'
    try {
      const savedSettings = localStorage.getItem('cannabest-store-settings');
      if (savedSettings) {
        const settings = JSON.parse(savedSettings);
        return settings.inventoryViewMode || 'cards';
      }
    } catch (error) {
      console.warn('Could not load settings from localStorage:', error);
    }
    return 'cards';
  });

  // Listen for changes to the settings
  useEffect(() => {
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === 'cannabest-store-settings' && e.newValue) {
        try {
          const settings = JSON.parse(e.newValue);
          console.log('Products: Storage change detected, updating view mode to:', settings.inventoryViewMode);
          setViewMode(settings.inventoryViewMode || 'cards');
        } catch (error) {
          console.warn('Could not parse settings from localStorage:', error);
        }
      }
    };

    // Listen for storage changes from other tabs/pages
    window.addEventListener('storage', handleStorageChange);

    // Also listen for custom event for same-page updates
    const handleSettingsUpdate = (e: CustomEvent) => {
      console.log('Products: Settings update event received:', e.detail);
      if (e.detail?.inventoryViewMode) {
        setViewMode(e.detail.inventoryViewMode);
      }
    };

    window.addEventListener('settings-updated', handleSettingsUpdate as EventListener);

    // Listen for specific inventory view change events
    const handleInventoryViewChange = (e: CustomEvent) => {
      console.log('Products: Inventory view change event received:', e.detail);
      if (e.detail?.viewMode) {
        setViewMode(e.detail.viewMode);
      }
    };

    window.addEventListener('inventory-view-changed', handleInventoryViewChange as EventListener);

    // Also check localStorage periodically in case we missed an update
    const checkSettingsInterval = setInterval(() => {
      try {
        const savedSettings = localStorage.getItem('cannabest-store-settings');
        if (savedSettings) {
          const settings = JSON.parse(savedSettings);
          const currentViewMode = settings.inventoryViewMode || 'cards';
          if (currentViewMode !== viewMode) {
            console.log('Products: Periodic check found view mode change:', currentViewMode);
            setViewMode(currentViewMode);
          }
        }
      } catch (error) {
        // Silent fail for periodic check
      }
    }, 1000);

    return () => {
      window.removeEventListener('storage', handleStorageChange);
      window.removeEventListener('settings-updated', handleSettingsUpdate as EventListener);
      window.removeEventListener('inventory-view-changed', handleInventoryViewChange as EventListener);
      clearInterval(checkSettingsInterval);
    };
  }, [viewMode]);

  // Product Catalogue states
  const [showCatalogueDialog, setShowCatalogueDialog] = useState(false);
  const [showCreateCatalogueItemDialog, setShowCreateCatalogueItemDialog] = useState(false);
  const [currentLocationId] = useState("location-001"); // In real app, get from auth/settings
  const [currentLocationName] = useState("Main Dispensary"); // In real app, get from auth/settings
  const [catalogueItems, setCatalogueItems] = useState<ProductCatalogueItem[]>([]);
  const [selectedCatalogueItem, setSelectedCatalogueItem] = useState<ProductCatalogueItem | null>(null);
  const [showImportMappingDialog, setShowImportMappingDialog] = useState(false);
  const [importMappings, setImportMappings] = useState<MetrcImportMapping[]>([]);
  const [selectedImportItem, setSelectedImportItem] = useState<ImportItem | null>(null);
  
  const [newProduct, setNewProduct] = useState<NewProduct>({
    name: "",
    category: "",
    price: 0,
    cost: 0,
    sku: "",
    weight: "",
    packagedDate: new Date().toISOString().split('T')[0],
    isUntaxed: false,
    isGLS: false
  });

  const [newCatalogueItem, setNewCatalogueItem] = useState<Partial<ProductCatalogueItem>>({
    name: "",
    category: "",
    basePrice: 0,
    baseCost: 0,
    defaultWeight: "",
    description: "",
    isTemplate: true,
    locationId: currentLocationId,
    locationName: currentLocationName,
    isActive: true
  });

  const [metrcImports] = useState<MetrcImport[]>([
    {
      id: "1",
      fileName: "metrc_packages_2024_01_15.csv",
      uploadDate: "2024-01-15T10:30:00",
      status: "completed",
      recordsTotal: 150,
      recordsProcessed: 150,
      recordsSuccessful: 148,
      recordsFailed: 2,
      errors: ["Row 45: Invalid THC percentage", "Row 97: Missing required field 'weight'"],
      vendorName: "Green Valley Supply Co",
      vendorLicense: "100000015",
      facilityName: "Green Valley Production Facility",
      facilityLicense: "100000015-PF001",
      importType: "packages",
      metrcApiResponse: "Success - 148 packages synchronized",
      items: [
        {
          id: "1",
          name: "Blue Dream Premium",
          category: "Flower",
          price: 7.00,
          cost: 3.50,
          sku: "BD-001",
          weight: "1g",
          room: "Main Storage Vault",
          strain: "Hybrid",
          thc: 20.5,
          cbd: 0.8,
          supplier: "Green Valley Supply Co",
          grower: "Emerald Fields Farm",
          packagedDate: "2024-01-15",
          expirationDate: "2025-01-15",
          isUntaxed: false,
          isGLS: false,
          metrcTag: "1A4000000000022000001234",
          batchId: "BD001-240115"
        },
        {
          id: "2",
          name: "OG Kush",
          category: "Flower",
          price: 12.00,
          cost: 6.00,
          sku: "OGK-002",
          weight: "1g",
          room: "Premium Storage",
          strain: "Indica",
          thc: 24.2,
          cbd: 0.2,
          supplier: "Green Valley Supply Co",
          grower: "High Grade Gardens",
          packagedDate: "2024-01-15",
          expirationDate: "2025-01-15",
          isUntaxed: false,
          isGLS: false,
          metrcTag: "1A4000000000022000001235",
          batchId: "OGK002-240115"
        },
        {
          id: "3",
          name: "Gummy Bears",
          category: "Edibles",
          price: 25.00,
          cost: 12.50,
          sku: "GB-003",
          weight: "100mg",
          room: "Edibles Storage",
          thc: 10,
          cbd: 0,
          supplier: "Green Valley Supply Co",
          grower: "Source Cannabis Farm",
          packagedDate: "2024-01-15",
          expirationDate: "2025-07-15",
          isUntaxed: false,
          isGLS: false,
          metrcTag: "1A4000000000022000001236",
          batchId: "GB003-240115"
        }
      ]
    },
    {
      id: "2",
      fileName: "metrc_inventory_update.csv",
      uploadDate: "2024-01-14T15:45:00",
      status: "completed",
      recordsTotal: 89,
      recordsProcessed: 89,
      recordsSuccessful: 89,
      recordsFailed: 0,
      vendorName: "Pacific Coast Cannabis",
      vendorLicense: "100000023",
      facilityName: "Pacific Coast Cultivation",
      facilityLicense: "100000023-CF002",
      importType: "inventory",
      metrcApiResponse: "Success - All inventory items updated",
      items: []
    },
    {
      id: "3",
      fileName: "metrc_harvest_batch_20240113.csv",
      uploadDate: "2024-01-13T09:15:00",
      status: "failed",
      recordsTotal: 25,
      recordsProcessed: 12,
      recordsSuccessful: 0,
      recordsFailed: 12,
      errors: ["Connection timeout to Metrc API", "Invalid authentication credentials"],
      vendorName: "Emerald Triangle Farms",
      vendorLicense: "100000007",
      facilityName: "Emerald Triangle Harvest Facility",
      facilityLicense: "100000007-HF001",
      importType: "harvest",
      metrcApiResponse: "Error - API authentication failed",
      items: []
    }
  ]);

  const createProduct = () => {
    if (!newProduct.name || !newProduct.category || !newProduct.price || !newProduct.cost || !newProduct.weight || !newProduct.room) {
      alert("Please fill in all required fields (Name, Category, Storage Room, Price, Cost, Weight)");
      return;
    }

    // Generate METRC tag (in real app, this would come from Metrc API)
    const metrcTag = `1A4000000000022000${Date.now().toString().slice(-6)}`;
    const batchId = `${newProduct.category.substring(0, 3).toUpperCase()}${Date.now().toString().slice(-6)}`;

    const product = {
      id: Date.now().toString(),
      ...newProduct,
      metrcTag,
      batchId,
      harvestDate: new Date().toISOString().split('T')[0],
      sourceHarvest: "New Product Creation",
      supplierUID: `1A4000000000022000000${Math.floor(Math.random() * 999).toString().padStart(3, '0')}`,
      administrativeHold: false,
      testResults: {
        tested: false,
        labName: undefined,
        testDate: undefined,
        cannabinoids: {
          thc: newProduct.thc || 0,
          cbd: newProduct.cbd || 0,
          cbg: newProduct.cbg || 0,
          cbn: newProduct.cbn || 0,
          cbc: newProduct.cbc || 0
        },
        contaminants: { passed: false }
      },
      stock: 0 // Initially 0, will be updated via inventory management
    };

    console.log("Product created:", product);
    
    alert(`Product created successfully!

Name: ${product.name}
Category: ${product.category}
Price: $${product.price.toFixed(2)}
Weight: ${product.weight}
METRC Tag: ${product.metrcTag}
Batch ID: ${product.batchId}

Product has been added to inventory and reported to Metrc system.`);

    // Reset form
    setShowCreateDialog(false);
    setNewProduct({
      name: "",
      category: "",
      price: 0,
      cost: 0,
      sku: "",
      weight: "",
      packagedDate: new Date().toISOString().split('T')[0],
      isUntaxed: false,
      isGLS: false
    });
  };

  const handleFileUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file) {
      if (!file.name.endsWith('.csv')) {
        alert("Please upload a CSV file only.");
        return;
      }
      setImportFile(file);
    }
  };

  const processImport = () => {
    if (!importFile) {
      alert("Please select a file to import.");
      return;
    }

    setIsImporting(true);
    setImportProgress(0);

    // Simulate import progress
    const interval = setInterval(() => {
      setImportProgress(prev => {
        if (prev >= 100) {
          clearInterval(interval);
          setIsImporting(false);
          
          // Simulate successful import
          alert(`Import completed successfully!

File: ${importFile.name}
Records processed: 125
Records successful: 123
Records failed: 2

2 records failed due to validation errors. Please check the import log for details.`);
          
          setShowImportDialog(false);
          setImportFile(null);
          setImportProgress(0);
          return 100;
        }
        return prev + Math.random() * 15;
      });
    }, 500);
  };

  const downloadTemplate = () => {
    const csvContent = `name,category,price,weight,strain,thc,cbd,supplier,grower,farm,packagedDate,expirationDate,isUntaxed,isGLS,description
Blue Dream,Flower,7.00,1g,Hybrid,20,0.1,Green Valley Supply,Emerald Fields Farm,Emerald Fields Farm,2024-01-15,2025-01-15,false,false,Premium indoor hybrid strain
OG Kush,Flower,12.00,1g,Indica,24,0.2,Pacific Coast Cannabis,High Grade Gardens,High Grade Gardens,2024-01-15,2025-01-15,false,false,Top shelf indica
Gummy Bears,Edibles,25.00,100mg,N/A,10,0,Edible Creations Co,Source Cannabis Farm,Green Valley Farm,2024-01-15,2025-07-15,false,false,100mg THC gummy bears
Glass Pipe,Paraphernalia,25.00,3oz,N/A,0,0,Glass Art Accessories,N/A,N/A,2024-01-01,2030-01-01,true,false,Hand-blown glass smoking pipe`;

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'product_import_template.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  };

  const importToInventory = async (importRecord: MetrcImport) => {
    if (!importRecord.items || importRecord.items.length === 0) {
      alert("No items found in this import record to process.");
      return;
    }

    // Start the item-by-item import process
    setCurrentImportItems(importRecord.items);
    setCurrentImportIndex(0);
    setProcessedItems([]);
    setEditingImportItem(importRecord.items[0]);
    setShowImportItemDialog(true);
  };

  const handleImportItemSave = () => {
    if (!editingImportItem) return;

    // Add current item to processed list
    setProcessedItems(prev => [...prev, editingImportItem]);

    const nextIndex = currentImportIndex + 1;

    if (nextIndex < currentImportItems.length) {
      // Move to next item
      setCurrentImportIndex(nextIndex);
      setEditingImportItem(currentImportItems[nextIndex]);
    } else {
      // All items processed
      setShowImportItemDialog(false);
      setImportingToInventory(true);

      // Simulate final import process
      setTimeout(() => {
        setImportingToInventory(false);
        alert(`Successfully imported ${processedItems.length + 1} items to inventory!

All items have been reviewed and added to your inventory with proper Metrc tracking tags.`);

        // Reset state
        setCurrentImportItems([]);
        setCurrentImportIndex(0);
        setProcessedItems([]);
        setEditingImportItem(null);
      }, 2000);
    }
  };

  const handleImportItemSkip = () => {
    const nextIndex = currentImportIndex + 1;

    if (nextIndex < currentImportItems.length) {
      // Move to next item
      setCurrentImportIndex(nextIndex);
      setEditingImportItem(currentImportItems[nextIndex]);
    } else {
      // All items processed
      setShowImportItemDialog(false);
      setImportingToInventory(true);

      setTimeout(() => {
        setImportingToInventory(false);
        alert(`Import completed! Processed ${processedItems.length} items and skipped ${currentImportItems.length - processedItems.length} items.`);

        // Reset state
        setCurrentImportItems([]);
        setCurrentImportIndex(0);
        setProcessedItems([]);
        setEditingImportItem(null);
      }, 1000);
    }
  };

  const handleImportItemCancel = () => {
    setShowImportItemDialog(false);
    setCurrentImportItems([]);
    setCurrentImportIndex(0);
    setProcessedItems([]);
    setEditingImportItem(null);
  };

  // Product Catalogue Functions
  const createCatalogueItem = () => {
    if (!newCatalogueItem.name || !newCatalogueItem.category || !newCatalogueItem.basePrice || !newCatalogueItem.defaultWeight) {
      alert("Please fill in all required fields (Name, Category, Base Price, Default Weight)");
      return;
    }

    const catalogueItem: ProductCatalogueItem = {
      id: `cat-${Date.now()}`,
      name: newCatalogueItem.name!,
      category: newCatalogueItem.category!,
      basePrice: newCatalogueItem.basePrice!,
      baseCost: newCatalogueItem.baseCost || 0,
      defaultWeight: newCatalogueItem.defaultWeight!,
      strain: newCatalogueItem.strain,
      description: newCatalogueItem.description || "",
      isTemplate: true,
      locationId: currentLocationId,
      locationName: currentLocationName,
      createdDate: new Date().toISOString(),
      lastModified: new Date().toISOString(),
      isActive: true,
      defaultRoom: newCatalogueItem.defaultRoom,
      supplier: newCatalogueItem.supplier,
      vendor: newCatalogueItem.vendor,
      grower: newCatalogueItem.grower,
      farm: newCatalogueItem.farm,
      defaultSkuPattern: newCatalogueItem.defaultSkuPattern,
      cannabinoidProfile: newCatalogueItem.cannabinoidProfile
    };

    setCatalogueItems(prev => [...prev, catalogueItem]);

    // Reset form
    setNewCatalogueItem({
      name: "",
      category: "",
      basePrice: 0,
      baseCost: 0,
      defaultWeight: "",
      description: "",
      isTemplate: true,
      locationId: currentLocationId,
      locationName: currentLocationName,
      isActive: true
    });

    setShowCreateCatalogueItemDialog(false);
    alert(`Product catalogue template "${catalogueItem.name}" created successfully!`);
  };

  const mapImportToCatalogue = (importItem: ImportItem, catalogueItem: ProductCatalogueItem) => {
    const mapping: MetrcImportMapping = {
      importItemId: importItem.id,
      catalogueItemId: catalogueItem.id,
      priceOverride: importItem.price !== catalogueItem.basePrice ? importItem.price : undefined,
      costOverride: importItem.cost !== catalogueItem.baseCost ? importItem.cost : undefined,
      weightOverride: importItem.weight !== catalogueItem.defaultWeight ? importItem.weight : undefined,
      roomAssignment: importItem.room,
      notes: `Mapped from METRC import on ${new Date().toLocaleDateString()}`
    };

    setImportMappings(prev => [...prev, mapping]);

    alert(`Import item "${importItem.name}" has been mapped to catalogue template "${catalogueItem.name}"!`);
    setShowImportMappingDialog(false);
    setSelectedImportItem(null);
    setSelectedCatalogueItem(null);
  };

  const deleteCatalogueItem = (itemId: string) => {
    const item = catalogueItems.find(c => c.id === itemId);
    if (item && confirm(`Are you sure you want to delete the catalogue template "${item.name}"?`)) {
      setCatalogueItems(prev => prev.filter(c => c.id !== itemId));
      alert(`Catalogue template "${item.name}" has been deleted.`);
    }
  };

  // Initialize catalogue items with sample data
  useEffect(() => {
    if (catalogueItems.length === 0) {
      setCatalogueItems(sampleCatalogueItems);
    }
  }, [catalogueItems.length]);

  // Inventory action functions
  const handleTransferToSalesFloor = (item: any) => {
    setSelectedInventoryItem(item);
    setTransferQuantity(item.stock.toString());
    setShowTransferDialog(true);
  };

  const confirmTransfer = () => {
    if (!selectedInventoryItem || !transferQuantity) {
      alert("Please enter a valid transfer quantity.");
      return;
    }

    const quantity = parseInt(transferQuantity);
    if (quantity <= 0 || quantity > selectedInventoryItem.stock) {
      alert(`Transfer quantity must be between 1 and ${selectedInventoryItem.stock}.`);
      return;
    }

    // Update inventory item stock
    setInventoryItems(prev => prev.map(item =>
      item.id === selectedInventoryItem.id
        ? { ...item, stock: item.stock - quantity }
        : item
    ).filter(item => item.stock > 0)); // Remove items with 0 stock

    // In a real app, you would also:
    // 1. Create a room transfer record
    // 2. Update the sales floor inventory
    // 3. Update METRC system
    // 4. Log the transaction

    alert(`Successfully transferred ${quantity} units of "${selectedInventoryItem.name}" to Sales Floor!

Transfer Details:
• Product: ${selectedInventoryItem.name}
• Quantity: ${quantity} units
• From: ${selectedInventoryItem.room}
• To: Sales Floor
• Remaining in ${selectedInventoryItem.room}: ${selectedInventoryItem.stock - quantity} units

METRC transfer notification has been sent.`);

    // Reset dialog
    setShowTransferDialog(false);
    setSelectedInventoryItem(null);
    setTransferQuantity("");
  };

  const handleEditInventoryItem = (item: any) => {
    setEditingInventoryItem({ ...item });
    setShowEditInventoryDialog(true);
  };

  const saveInventoryEdit = () => {
    if (!editingInventoryItem) return;

    // Validate required fields
    if (!editingInventoryItem.name || !editingInventoryItem.price || !editingInventoryItem.stock) {
      alert("Please fill in all required fields (Name, Price, Stock).");
      return;
    }

    // Update inventory item
    setInventoryItems(prev => prev.map(item =>
      item.id === editingInventoryItem.id ? editingInventoryItem : item
    ));

    alert(`Successfully updated "${editingInventoryItem.name}"!`);

    // Reset dialog
    setShowEditInventoryDialog(false);
    setEditingInventoryItem(null);
  };

  const viewImportDetails = (importRecord: MetrcImport) => {
    setSelectedImportRecord(importRecord);
    setShowImportDetailsDialog(true);
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'completed':
        return <CheckCircle className="w-4 h-4 text-green-600" />;
      case 'failed':
        return <AlertCircle className="w-4 h-4 text-red-600" />;
      case 'processing':
        return <Clock className="w-4 h-4 text-blue-600" />;
      default:
        return <Clock className="w-4 h-4 text-gray-400" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'failed':
        return 'bg-red-100 text-red-800';
      case 'processing':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Product Management</h1>
            <p className="text-sm opacity-80">Create products and manage Metrc imports</p>
          </div>
          <div className="flex gap-2">
            <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
              <DialogTrigger asChild>
                <Button className="header-button-visible">
                  <Plus className="w-4 h-4 mr-2" />
                  Create Product
                </Button>
              </DialogTrigger>
            </Dialog>
            <Dialog open={showImportDialog} onOpenChange={setShowImportDialog}>
              <DialogTrigger asChild>
                <Button variant="outline" className="header-button-visible">
                  <Upload className="w-4 h-4 mr-2" />
                  Import from Metrc
                </Button>
              </DialogTrigger>
            </Dialog>
          </div>
        </div>
      </header>

      <div className="container mx-auto p-6">
        <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
          <TabsList className="grid w-full grid-cols-3">
            <TabsTrigger value="create">Product Creation</TabsTrigger>
            <TabsTrigger value="imports">Metrc Imports</TabsTrigger>
            <TabsTrigger value="inventory">On-Hand Inventory</TabsTrigger>
          </TabsList>

          <TabsContent value="create" className="space-y-6">
            {/* Quick Stats */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-blue-600">247</div>
                  <div className="text-sm text-muted-foreground">Total Products</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-green-600">18</div>
                  <div className="text-sm text-muted-foreground">Created Today</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-orange-600">23</div>
                  <div className="text-sm text-muted-foreground">Pending Lab Tests</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-purple-600">156</div>
                  <div className="text-sm text-muted-foreground">Active SKUs</div>
                </CardContent>
              </Card>
            </div>

            {/* Product Creation Form */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Package className="w-5 h-5" />
                  Create New Product
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-muted-foreground mb-4">
                  Create a new product entry that will be automatically registered with the Metrc system.
                </p>
                <div className="text-center">
                  <Button onClick={() => setShowCreateDialog(true)}>
                    <Plus className="w-4 h-4 mr-2" />
                    Start Product Creation
                  </Button>
                </div>
              </CardContent>
            </Card>

            {/* Recent Products */}
            <Card>
              <CardHeader>
                <CardTitle>Recently Created Products</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {[
                    { name: "Blue Dream Premium", category: "Flower", price: "$7.00", created: "2 hours ago", status: "pending-test" },
                    { name: "Mixed Berry Gummies", category: "Edibles", price: "$25.00", created: "4 hours ago", status: "completed" },
                    { name: "Live Resin Cart", category: "Vapes", price: "$55.00", created: "1 day ago", status: "completed" },
                    { name: "CBD Tincture 30ml", category: "Tinctures", price: "$65.00", created: "2 days ago", status: "completed" }
                  ].map((product, index) => (
                    <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                      <div className="flex items-center gap-3">
                        <Package className="w-4 h-4 text-gray-500" />
                        <div>
                          <div className="font-medium">{product.name}</div>
                          <div className="text-sm text-gray-600">{product.category} • {product.price}</div>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <span className="text-sm text-gray-500">{product.created}</span>
                        <Badge variant={product.status === 'completed' ? 'default' : 'secondary'}>
                          {product.status === 'completed' ? 'Ready' : 'Pending Test'}
                        </Badge>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="imports" className="space-y-6">
            {/* Import Actions */}
            <div className="flex gap-4">
              <Button onClick={downloadTemplate} variant="outline">
                <Download className="w-4 h-4 mr-2" />
                Download Template
              </Button>
              <Button onClick={() => setShowImportDialog(true)}>
                <Upload className="w-4 h-4 mr-2" />
                Import CSV
              </Button>
            </div>

            {/* Import History */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Database className="w-5 h-5" />
                  Import History
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {metrcImports.map((importRecord) => (
                    <div key={importRecord.id} className="border rounded-lg p-4">
                      <div className="flex items-center justify-between mb-3">
                        <div className="flex items-center gap-3">
                          {getStatusIcon(importRecord.status)}
                          <div>
                            <div className="font-medium">{importRecord.fileName}</div>
                            <div className="text-sm text-gray-600">
                              {new Date(importRecord.uploadDate).toLocaleString()}
                            </div>
                          </div>
                        </div>
                        <div className="flex gap-2">
                          {importRecord.importType && (
                            <Badge variant="outline" className="text-xs">
                              {importRecord.importType.charAt(0).toUpperCase() + importRecord.importType.slice(1)}
                            </Badge>
                          )}
                          <Badge className={getStatusColor(importRecord.status)}>
                            {importRecord.status.charAt(0).toUpperCase() + importRecord.status.slice(1)}
                          </Badge>
                        </div>
                      </div>

                      {/* Vendor Information */}
                      {importRecord.vendorName && (
                        <div className="mb-3 p-3 bg-blue-50 rounded-lg">
                          <div className="text-sm font-medium text-blue-800 mb-2">Vendor Details</div>
                          <div className="grid grid-cols-2 gap-4 text-sm">
                            <div>
                              <span className="text-blue-600">Vendor:</span>
                              <div className="font-medium text-blue-800">{importRecord.vendorName}</div>
                              <div className="text-xs text-blue-600">License: {importRecord.vendorLicense}</div>
                            </div>
                            <div>
                              <span className="text-blue-600">Facility:</span>
                              <div className="font-medium text-blue-800">{importRecord.facilityName}</div>
                              <div className="text-xs text-blue-600">License: {importRecord.facilityLicense}</div>
                            </div>
                          </div>
                        </div>
                      )}

                      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                          <span className="text-gray-600">Total Records:</span>
                          <div className="font-medium">{importRecord.recordsTotal}</div>
                        </div>
                        <div>
                          <span className="text-gray-600">Processed:</span>
                          <div className="font-medium">{importRecord.recordsProcessed}</div>
                        </div>
                        <div>
                          <span className="text-gray-600">Successful:</span>
                          <div className="font-medium text-green-600">{importRecord.recordsSuccessful}</div>
                        </div>
                        <div>
                          <span className="text-gray-600">Failed:</span>
                          <div className="font-medium text-red-600">{importRecord.recordsFailed}</div>
                        </div>
                      </div>

                      {importRecord.recordsProcessed > 0 && (
                        <div className="mt-3">
                          <div className="flex justify-between text-sm mb-1">
                            <span>Progress</span>
                            <span>{Math.round((importRecord.recordsProcessed / importRecord.recordsTotal) * 100)}%</span>
                          </div>
                          <Progress 
                            value={(importRecord.recordsProcessed / importRecord.recordsTotal) * 100} 
                            className="h-2"
                          />
                        </div>
                      )}

                      {/* Metrc API Response */}
                      {importRecord.metrcApiResponse && (
                        <div className={`mt-3 p-2 rounded ${
                          importRecord.status === 'completed'
                            ? 'bg-green-50 border border-green-200'
                            : 'bg-red-50 border border-red-200'
                        }`}>
                          <div className={`text-sm font-medium mb-1 ${
                            importRecord.status === 'completed' ? 'text-green-800' : 'text-red-800'
                          }`}>
                            Metrc API Response:
                          </div>
                          <div className={`text-xs ${
                            importRecord.status === 'completed' ? 'text-green-700' : 'text-red-700'
                          }`}>
                            {importRecord.metrcApiResponse}
                          </div>
                        </div>
                      )}

                      {importRecord.errors && importRecord.errors.length > 0 && (
                        <div className="mt-3 p-2 bg-red-50 border border-red-200 rounded">
                          <div className="text-sm font-medium text-red-800 mb-1">Import Errors:</div>
                          <ul className="text-xs text-red-700 space-y-1">
                            {importRecord.errors.slice(0, 3).map((error, index) => (
                              <li key={index}>• {error}</li>
                            ))}
                            {importRecord.errors.length > 3 && (
                              <li>• ...and {importRecord.errors.length - 3} more</li>
                            )}
                          </ul>
                        </div>
                      )}

                      {/* Import Actions */}
                      {importRecord.status === 'completed' && importRecord.recordsSuccessful > 0 && (
                        <div className="mt-4 pt-3 border-t flex gap-2">
                          <Button
                            size="sm"
                            onClick={() => importToInventory(importRecord)}
                            className="bg-green-600 hover:bg-green-700"
                            disabled={importingToInventory}
                          >
                            {importingToInventory ? (
                              <>
                                <RefreshCw className="w-4 h-4 mr-2 animate-spin" />
                                Importing...
                              </>
                            ) : (
                              <>
                                <Package className="w-4 h-4 mr-2" />
                                Import {importRecord.recordsSuccessful} Items to Inventory
                              </>
                            )}
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => viewImportDetails(importRecord)}
                          >
                            <Eye className="w-4 h-4 mr-2" />
                            View Details
                          </Button>
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="inventory" className="space-y-6">
            {/* Inventory Filters */}
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <Input
                    placeholder="Search inventory by name, SKU, METRC tag..."
                    value={inventorySearchQuery}
                    onChange={(e) => setInventorySearchQuery(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>
              <Select value={selectedRoom} onValueChange={setSelectedRoom}>
                <SelectTrigger className="w-48">
                  <SelectValue placeholder="Filter by room" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Rooms</SelectItem>
                  {availableRooms.filter(room => room.name !== "Sales Floor").map(room => (
                    <SelectItem key={room.id} value={room.name}>{room.name}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Select value={selectedCategory} onValueChange={setSelectedCategory}>
                <SelectTrigger className="w-40">
                  <SelectValue placeholder="Category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Categories</SelectItem>
                  {categories.map(category => (
                    <SelectItem key={category} value={category}>{category}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Select value={`${sortBy}-${sortOrder}`} onValueChange={(value) => {
                const [field, order] = value.split('-') as [typeof sortBy, typeof sortOrder];
                setSortBy(field);
                setSortOrder(order);
              }}>
                <SelectTrigger className="w-48">
                  <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="name-asc">Name (A-Z)</SelectItem>
                  <SelectItem value="name-desc">Name (Z-A)</SelectItem>
                  <SelectItem value="price-asc">Price (Low-High)</SelectItem>
                  <SelectItem value="price-desc">Price (High-Low)</SelectItem>
                  <SelectItem value="cost-asc">Cost (Low-High)</SelectItem>
                  <SelectItem value="cost-desc">Cost (High-Low)</SelectItem>
                  <SelectItem value="vendor-asc">Vendor (A-Z)</SelectItem>
                  <SelectItem value="vendor-desc">Vendor (Z-A)</SelectItem>
                  <SelectItem value="quantity-asc">Quantity (Low-High)</SelectItem>
                  <SelectItem value="quantity-desc">Quantity (High-Low)</SelectItem>
                </SelectContent>
              </Select>
            </div>

            {/* Inventory Stats */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-purple-600">
                    {inventoryItems.filter(p =>
                      (selectedRoom === "all" || p.room === selectedRoom) &&
                      (selectedCategory === "all" || p.category === selectedCategory) &&
                      (inventorySearchQuery === "" ||
                        p.name.toLowerCase().includes(inventorySearchQuery.toLowerCase()) ||
                        p.sku?.toLowerCase().includes(inventorySearchQuery.toLowerCase()) ||
                        p.metrcTag?.toLowerCase().includes(inventorySearchQuery.toLowerCase())
                      )
                    ).length}
                  </div>
                  <div className="text-sm text-muted-foreground">Items in Storage</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-orange-600">
                    {inventoryItems.reduce((sum, p) => sum + p.stock, 0).toLocaleString()}
                  </div>
                  <div className="text-sm text-muted-foreground">Total Units</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-green-600">
                    ${inventoryItems.reduce((sum, p) => sum + (p.price * p.stock), 0).toLocaleString()}
                  </div>
                  <div className="text-sm text-muted-foreground">Total Value</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-blue-600">
                    {inventoryItems.filter(p => p.room === "Secure Vault").length}
                  </div>
                  <div className="text-sm text-muted-foreground">Secured Items</div>
                </CardContent>
              </Card>
            </div>

            {/* Inventory List */}
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div>
                    <CardTitle className="flex items-center gap-2">
                      <Package className="w-5 h-5" />
                      On-Hand Inventory (Non-Sales Floor)
                    </CardTitle>
                    <div className="text-sm text-muted-foreground">
                      Items currently stored in various rooms but not available on the sales floor
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Button
                      variant={viewMode === 'cards' ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => {
                        setViewMode('cards');
                        // Update localStorage to keep settings in sync
                        try {
                          const savedSettings = localStorage.getItem('cannabest-store-settings');
                          const settings = savedSettings ? JSON.parse(savedSettings) : {};
                          const newSettings = { ...settings, inventoryViewMode: 'cards' };
                          localStorage.setItem('cannabest-store-settings', JSON.stringify(newSettings));
                          console.log('Products: Updated localStorage with cards view');
                        } catch (error) {
                          console.warn('Could not update localStorage:', error);
                        }
                      }}
                    >
                      <Grid3X3 className="w-4 h-4" />
                    </Button>
                    <Button
                      variant={viewMode === 'list' ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => {
                        setViewMode('list');
                        // Update localStorage to keep settings in sync
                        try {
                          const savedSettings = localStorage.getItem('cannabest-store-settings');
                          const settings = savedSettings ? JSON.parse(savedSettings) : {};
                          const newSettings = { ...settings, inventoryViewMode: 'list' };
                          localStorage.setItem('cannabest-store-settings', JSON.stringify(newSettings));
                          console.log('Products: Updated localStorage with list view');
                        } catch (error) {
                          console.warn('Could not update localStorage:', error);
                        }
                      }}
                    >
                      <List className="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                {(() => {
                  const filteredItems = inventoryItems
                    .filter(product => {
                      const matchesRoom = selectedRoom === "all" || product.room === selectedRoom;
                      const matchesCategory = selectedCategory === "all" || product.category === selectedCategory;
                      const searchLower = inventorySearchQuery.toLowerCase();
                      const matchesSearch = inventorySearchQuery === "" ||
                        product.name.toLowerCase().includes(searchLower) ||
                        product.sku?.toLowerCase().includes(searchLower) ||
                        product.metrcTag?.toLowerCase().includes(searchLower) ||
                        product.supplier?.toLowerCase().includes(searchLower) ||
                        product.vendor?.toLowerCase().includes(searchLower);
                      return matchesRoom && matchesCategory && matchesSearch;
                    })
                    .sort((a, b) => {
                      let valueA: any, valueB: any;

                      switch (sortBy) {
                        case 'name':
                          valueA = a.name.toLowerCase();
                          valueB = b.name.toLowerCase();
                          break;
                        case 'price':
                          valueA = a.price;
                          valueB = b.price;
                          break;
                        case 'cost':
                          valueA = a.cost || 0;
                          valueB = b.cost || 0;
                          break;
                        case 'vendor':
                          valueA = (a.vendor || '').toLowerCase();
                          valueB = (b.vendor || '').toLowerCase();
                          break;
                        case 'quantity':
                          valueA = a.stock;
                          valueB = b.stock;
                          break;
                        default:
                          valueA = a.name.toLowerCase();
                          valueB = b.name.toLowerCase();
                      }

                      if (typeof valueA === 'string') {
                        const comparison = valueA.localeCompare(valueB);
                        return sortOrder === 'asc' ? comparison : -comparison;
                      } else {
                        const comparison = valueA - valueB;
                        return sortOrder === 'asc' ? comparison : -comparison;
                      }
                    });

                  if (filteredItems.length === 0) {
                    return (
                      <div className="text-center py-8">
                        <Package className="w-12 h-12 text-gray-400 mx-auto mb-3" />
                        <p className="text-gray-600">No inventory items found matching your filters.</p>
                        <p className="text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                      </div>
                    );
                  }

                  if (viewMode === 'list') {
                    return (
                      <div className="space-y-1">
                        {/* List View Header */}
                        <div className="grid grid-cols-12 gap-4 py-3 px-4 bg-gray-50 rounded-lg font-medium text-sm border-b">
                          <div className="col-span-3">Product</div>
                          <div className="col-span-1">Stock</div>
                          <div className="col-span-1">Price</div>
                          <div className="col-span-1">Cost</div>
                          <div className="col-span-2">Location</div>
                          <div className="col-span-2">Vendor</div>
                          <div className="col-span-1">THC%</div>
                          <div className="col-span-1">Actions</div>
                        </div>

                        {/* List View Items */}
                        {filteredItems.map((product) => (
                          <div key={product.id} className="grid grid-cols-12 gap-4 py-3 px-4 border rounded-lg hover:bg-gray-50 items-center">
                            <div className="col-span-3">
                              <div className="flex items-center gap-3">
                                <div className="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                                  <Package className="w-4 h-4 text-gray-500" />
                                </div>
                                <div>
                                  <div className="font-medium">{product.name}</div>
                                  <div className="text-xs text-gray-600">{product.category} • {product.sku}</div>
                                </div>
                              </div>
                            </div>
                            <div className="col-span-1">
                              <div className="font-bold">{product.stock}</div>
                              <div className="text-xs text-gray-600">units</div>
                            </div>
                            <div className="col-span-1">
                              <div className="font-medium">${product.price.toFixed(2)}</div>
                            </div>
                            <div className="col-span-1">
                              <div className="font-medium text-green-600">${(product.cost || 0).toFixed(2)}</div>
                            </div>
                            <div className="col-span-2">
                              <div className="text-sm">{product.room}</div>
                            </div>
                            <div className="col-span-2">
                              <div className="text-sm text-blue-600">{product.vendor}</div>
                            </div>
                            <div className="col-span-1">
                              <div className="text-sm">{product.thc || 0}%</div>
                            </div>
                            <div className="col-span-1">
                              <div className="flex gap-1">
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => handleTransferToSalesFloor(product)}
                                  className="px-2"
                                >
                                  <ArrowRightLeft className="w-3 h-3" />
                                </Button>
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => handleEditInventoryItem(product)}
                                  className="px-2"
                                >
                                  <Edit className="w-3 h-3" />
                                </Button>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    );
                  }

                  // Card View (default)
                  return (
                    <div className="space-y-4">
                      {filteredItems.map((product) => (
                        <div key={product.id} className="border rounded-lg p-4 hover:bg-gray-50">
                          <div className="flex items-center justify-between mb-3">
                            <div className="flex items-center gap-3">
                              <div className="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <Package className="w-6 h-6 text-gray-500" />
                              </div>
                              <div>
                                <h3 className="font-semibold">{product.name}</h3>
                                <div className="text-sm text-gray-600">
                                  {product.category} • {product.weight} • SKU: {product.sku}
                                </div>
                              </div>
                            </div>
                            <div className="text-right">
                              <div className="text-2xl font-bold">{product.stock}</div>
                              <div className="text-sm text-gray-600">units</div>
                            </div>
                          </div>

                          <div className="grid grid-cols-2 md:grid-cols-6 gap-4 text-sm mb-3">
                            <div>
                              <span className="text-gray-600">Location:</span>
                              <div className="font-medium">{product.room}</div>
                            </div>
                            <div>
                              <span className="text-gray-600">Unit Price:</span>
                              <div className="font-medium">${product.price.toFixed(2)}</div>
                            </div>
                            <div>
                              <span className="text-gray-600">Unit Cost:</span>
                              <div className="font-medium text-green-600">${(product.cost || 0).toFixed(2)}</div>
                            </div>
                            <div>
                              <span className="text-gray-600">Total Value:</span>
                              <div className="font-medium">${(product.price * product.stock).toLocaleString()}</div>
                            </div>
                            <div>
                              <span className="text-gray-600">Supplier:</span>
                              <div className="font-medium">{product.supplier}</div>
                            </div>
                            <div>
                              <span className="text-gray-600">Vendor:</span>
                              <div className="font-medium text-blue-600">{product.vendor}</div>
                            </div>
                          </div>

                          {product.thc !== undefined && (
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
                              <div>
                                <span className="text-gray-600">THC:</span>
                                <div className="font-medium">{product.thc}%</div>
                              </div>
                              <div>
                                <span className="text-gray-600">CBD:</span>
                                <div className="font-medium">{product.cbd}%</div>
                              </div>
                              <div>
                                <span className="text-gray-600">Strain:</span>
                                <div className="font-medium">{product.strain}</div>
                              </div>
                              <div>
                                <span className="text-gray-600">Batch:</span>
                                <div className="font-medium font-mono text-xs">{product.batchId}</div>
                              </div>
                            </div>
                          )}

                          <div className="flex justify-between items-center pt-3 border-t">
                            <div className="text-xs text-gray-500">
                              METRC: {product.metrcTag} | Packaged: {product.packagedDate}
                            </div>
                            <div className="flex gap-2">
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => handleTransferToSalesFloor(product)}
                              >
                                <ArrowRightLeft className="w-4 h-4 mr-2" />
                                Transfer to Sales Floor
                              </Button>
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => handleEditInventoryItem(product)}
                              >
                                <Edit className="w-4 h-4 mr-2" />
                                Edit
                              </Button>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  );
                })()}
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="catalogue" className="space-y-6">
            {/* Catalogue Header */}
            <div className="flex items-center justify-between">
              <div>
                <h2 className="text-2xl font-bold">Product Catalogue</h2>
                <p className="text-muted-foreground">Manage product templates for METRC imports - Location: {currentLocationName}</p>
              </div>
              <Dialog open={showCreateCatalogueItemDialog} onOpenChange={setShowCreateCatalogueItemDialog}>
                <DialogTrigger asChild>
                  <Button>
                    <Plus className="w-4 h-4 mr-2" />
                    Create Template
                  </Button>
                </DialogTrigger>
              </Dialog>
            </div>

            {/* Catalogue Stats */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-blue-600">{catalogueItems.filter(c => c.isActive).length}</div>
                  <div className="text-sm text-muted-foreground">Active Templates</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-green-600">{catalogueItems.filter(c => c.category === 'Flower').length}</div>
                  <div className="text-sm text-muted-foreground">Flower Templates</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-purple-600">{importMappings.length}</div>
                  <div className="text-sm text-muted-foreground">METRC Mappings</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-orange-600">{catalogueItems.filter(c => c.locationId === currentLocationId).length}</div>
                  <div className="text-sm text-muted-foreground">Location-Specific</div>
                </CardContent>
              </Card>
            </div>

            {/* Catalogue Items */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Database className="w-5 h-5" />
                  Product Templates
                </CardTitle>
                <div className="text-sm text-muted-foreground">
                  Pre-configured product templates for consistent METRC imports. Each location maintains its own catalogue.
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {catalogueItems
                    .filter(item => item.locationId === currentLocationId)
                    .map((item) => (
                    <div key={item.id} className="border rounded-lg p-4 hover:bg-gray-50">
                      <div className="flex items-center justify-between mb-3">
                        <div className="flex items-center gap-3">
                          <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <Package className="w-6 h-6 text-blue-600" />
                          </div>
                          <div>
                            <h3 className="font-semibold">{item.name}</h3>
                            <div className="text-sm text-gray-600">
                              {item.category} • {item.defaultWeight}
                            </div>
                          </div>
                        </div>
                        <div className="flex items-center gap-2">
                          <Badge variant={item.isActive ? "default" : "secondary"}>
                            {item.isActive ? "Active" : "Inactive"}
                          </Badge>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => {
                              setSelectedCatalogueItem(item);
                              setShowImportMappingDialog(true);
                            }}
                          >
                            <ArrowRightLeft className="w-4 h-4 mr-2" />
                            Map Import
                          </Button>
                          <Button
                            size="sm"
                            variant="ghost"
                            onClick={() => deleteCatalogueItem(item.id)}
                            className="text-red-600 hover:text-red-700"
                          >
                            <Trash2 className="w-4 h-4" />
                          </Button>
                        </div>
                      </div>

                      <div className="grid grid-cols-2 md:grid-cols-6 gap-4 text-sm mb-3">
                        <div>
                          <span className="text-gray-600">Base Price:</span>
                          <div className="font-medium">${item.basePrice.toFixed(2)}</div>
                        </div>
                        <div>
                          <span className="text-gray-600">Base Cost:</span>
                          <div className="font-medium text-green-600">${item.baseCost.toFixed(2)}</div>
                        </div>
                        <div>
                          <span className="text-gray-600">Default Room:</span>
                          <div className="font-medium">{item.defaultRoom || 'Not Set'}</div>
                        </div>
                        <div>
                          <span className="text-gray-600">Supplier:</span>
                          <div className="font-medium">{item.supplier || 'Not Set'}</div>
                        </div>
                        <div>
                          <span className="text-gray-600">Vendor:</span>
                          <div className="font-medium text-blue-600">{item.vendor || 'Not Set'}</div>
                        </div>
                        <div>
                          <span className="text-gray-600">SKU Pattern:</span>
                          <div className="font-medium font-mono text-xs">{item.defaultSkuPattern || 'Not Set'}</div>
                        </div>
                      </div>

                      {item.cannabinoidProfile && (
                        <div className="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm mb-3">
                          {item.cannabinoidProfile.thc && (
                            <div>
                              <span className="text-gray-600">THC:</span>
                              <div className="font-medium">{item.cannabinoidProfile.thc}%</div>
                            </div>
                          )}
                          {item.cannabinoidProfile.cbd && (
                            <div>
                              <span className="text-gray-600">CBD:</span>
                              <div className="font-medium">{item.cannabinoidProfile.cbd}%</div>
                            </div>
                          )}
                          {item.cannabinoidProfile.cbg && (
                            <div>
                              <span className="text-gray-600">CBG:</span>
                              <div className="font-medium">{item.cannabinoidProfile.cbg}%</div>
                            </div>
                          )}
                          {item.cannabinoidProfile.cbn && (
                            <div>
                              <span className="text-gray-600">CBN:</span>
                              <div className="font-medium">{item.cannabinoidProfile.cbn}%</div>
                            </div>
                          )}
                          {item.cannabinoidProfile.cbc && (
                            <div>
                              <span className="text-gray-600">CBC:</span>
                              <div className="font-medium">{item.cannabinoidProfile.cbc}%</div>
                            </div>
                          )}
                        </div>
                      )}

                      <div className="flex justify-between items-center pt-3 border-t">
                        <div className="text-xs text-gray-500">
                          Created: {new Date(item.createdDate).toLocaleDateString()} | Modified: {new Date(item.lastModified).toLocaleDateString()}
                        </div>
                        <div className="text-xs text-blue-600">
                          Location: {item.locationName}
                        </div>
                      </div>

                      {item.description && (
                        <div className="mt-2 p-2 bg-gray-50 rounded text-sm">
                          <span className="font-medium">Description:</span> {item.description}
                        </div>
                      )}
                    </div>
                  ))}

                  {catalogueItems.filter(item => item.locationId === currentLocationId).length === 0 && (
                    <div className="text-center py-8">
                      <Database className="w-12 h-12 text-gray-400 mx-auto mb-3" />
                      <p className="text-gray-600">No product templates found for this location.</p>
                      <p className="text-sm text-gray-500">Create templates to streamline METRC imports.</p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>

      {/* Create Product Dialog */}
      <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Create New Product</DialogTitle>
        </DialogHeader>
        <div className="space-y-6">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="product-name">Product Name *</Label>
              <Input
                id="product-name"
                value={newProduct.name}
                onChange={(e) => setNewProduct(prev => ({...prev, name: e.target.value}))}
                placeholder="Enter product name"
              />
            </div>
            <div>
              <Label htmlFor="product-category">Category *</Label>
              <Select value={newProduct.category} onValueChange={(value) => setNewProduct(prev => ({...prev, category: value}))}>
                <SelectTrigger>
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  {categories.map(category => (
                    <SelectItem key={category} value={category}>{category}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>

          <div>
            <Label htmlFor="product-room">Storage Room *</Label>
            <Select value={newProduct.room || ""} onValueChange={(value) => setNewProduct(prev => ({...prev, room: value}))}>
              <SelectTrigger>
                <SelectValue placeholder="Select room to store this product" />
              </SelectTrigger>
              <SelectContent>
                {availableRooms.map(room => (
                  <SelectItem key={room.id} value={room.name}>{room.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {/* Product Image Upload */}
          <div>
            <Label htmlFor="product-image">Product Image</Label>
            <div className="border-2 border-dashed border-gray-300 rounded-lg p-6">
              <div className="text-center">
                {newProduct.image ? (
                  <div className="space-y-4">
                    <img
                      src={newProduct.image}
                      alt="Product preview"
                      className="mx-auto h-32 w-32 object-cover rounded-lg"
                    />
                    <div className="flex gap-2 justify-center">
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => setNewProduct(prev => ({...prev, image: ""}))}
                      >
                        Remove Image
                      </Button>
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => document.getElementById('file-upload')?.click()}
                      >
                        Change Image
                      </Button>
                    </div>
                  </div>
                ) : (
                  <div className="space-y-2">
                    <Upload className="mx-auto h-12 w-12 text-gray-400" />
                    <div className="text-gray-600">
                      <Button
                        type="button"
                        variant="outline"
                        onClick={() => document.getElementById('file-upload')?.click()}
                      >
                        Choose Image
                      </Button>
                      <p className="text-sm mt-2">or drag and drop</p>
                    </div>
                    <p className="text-xs text-gray-500">PNG, JPG up to 10MB</p>
                  </div>
                )}
                <input
                  id="file-upload"
                  type="file"
                  accept="image/*"
                  className="hidden"
                  onChange={(e) => {
                    const file = e.target.files?.[0];
                    if (file) {
                      const reader = new FileReader();
                      reader.onload = (event) => {
                        setNewProduct(prev => ({...prev, image: event.target?.result as string}));
                      };
                      reader.readAsDataURL(file);
                    }
                  }}
                />
              </div>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="product-sku">SKU (max 50 characters)</Label>
              <Input
                id="product-sku"
                value={newProduct.sku}
                onChange={(e) => {
                  const value = e.target.value.slice(0, 50); // Limit to 50 characters
                  setNewProduct(prev => ({...prev, sku: value}));
                }}
                placeholder="Product SKU (up to 50 characters)"
                maxLength={50}
                className="font-mono"
              />
              <div className="text-xs text-gray-500 mt-1">
                {newProduct.sku.length}/50 characters
              </div>
            </div>
            <div>
              <Label htmlFor="product-weight">Weight/Size *</Label>
              <Input
                id="product-weight"
                value={newProduct.weight}
                onChange={(e) => setNewProduct(prev => ({...prev, weight: e.target.value}))}
                placeholder="e.g., 1g, 100mg, 30ml"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="product-cost">Cost ($) *</Label>
              <Input
                id="product-cost"
                type="number"
                step="0.01"
                value={newProduct.cost}
                onChange={(e) => setNewProduct(prev => ({...prev, cost: parseFloat(e.target.value) || 0}))}
                placeholder="0.00"
              />
            </div>
            <div>
              <Label htmlFor="product-price">Sale Price ($) *</Label>
              <Input
                id="product-price"
                type="number"
                step="0.01"
                value={newProduct.price}
                onChange={(e) => setNewProduct(prev => ({...prev, price: parseFloat(e.target.value) || 0}))}
                placeholder="0.00"
              />
            </div>
            <div>
              <Label htmlFor="product-strain">Strain</Label>
              <Select value={newProduct.strain || ""} onValueChange={(value) => setNewProduct(prev => ({...prev, strain: value}))}>
                <SelectTrigger>
                  <SelectValue placeholder="Select strain type" />
                </SelectTrigger>
                <SelectContent>
                  {strains.map(strain => (
                    <SelectItem key={strain} value={strain}>{strain}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>

          {/* Cannabinoid Content */}
          <div>
            <Label className="text-base font-medium">Cannabinoid Content</Label>
            <div className="grid grid-cols-5 gap-4 mt-2">
              <div>
                <Label htmlFor="thc">THC (%)</Label>
                <Input
                  id="thc"
                  type="number"
                  step="0.1"
                  value={newProduct.thc || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, thc: parseFloat(e.target.value) || undefined}))}
                  placeholder="0.0"
                />
              </div>
              <div>
                <Label htmlFor="cbd">CBD (%)</Label>
                <Input
                  id="cbd"
                  type="number"
                  step="0.1"
                  value={newProduct.cbd || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, cbd: parseFloat(e.target.value) || undefined}))}
                  placeholder="0.0"
                />
              </div>
              <div>
                <Label htmlFor="cbg">CBG (%)</Label>
                <Input
                  id="cbg"
                  type="number"
                  step="0.1"
                  value={newProduct.cbg || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, cbg: parseFloat(e.target.value) || undefined}))}
                  placeholder="0.0"
                />
              </div>
              <div>
                <Label htmlFor="cbn">CBN (%)</Label>
                <Input
                  id="cbn"
                  type="number"
                  step="0.1"
                  value={newProduct.cbn || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, cbn: parseFloat(e.target.value) || undefined}))}
                  placeholder="0.0"
                />
              </div>
              <div>
                <Label htmlFor="cbc">CBC (%)</Label>
                <Input
                  id="cbc"
                  type="number"
                  step="0.1"
                  value={newProduct.cbc || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, cbc: parseFloat(e.target.value) || undefined}))}
                  placeholder="0.0"
                />
              </div>
            </div>
          </div>

          {/* For edibles - show mg content */}
          {newProduct.category === "Edibles" && (
            <div>
              <Label className="text-base font-medium">Cannabinoid Content (mg)</Label>
              <div className="grid grid-cols-5 gap-4 mt-2">
                <div>
                  <Label htmlFor="thc-mg">THC (mg)</Label>
                  <Input
                    id="thc-mg"
                    type="number"
                    step="0.1"
                    value={newProduct.thcMg || ""}
                    onChange={(e) => setNewProduct(prev => ({...prev, thcMg: parseFloat(e.target.value) || undefined}))}
                    placeholder="0.0"
                  />
                </div>
                <div>
                  <Label htmlFor="cbd-mg">CBD (mg)</Label>
                  <Input
                    id="cbd-mg"
                    type="number"
                    step="0.1"
                    value={newProduct.cbdMg || ""}
                    onChange={(e) => setNewProduct(prev => ({...prev, cbdMg: parseFloat(e.target.value) || undefined}))}
                    placeholder="0.0"
                  />
                </div>
                <div>
                  <Label htmlFor="cbg-mg">CBG (mg)</Label>
                  <Input
                    id="cbg-mg"
                    type="number"
                    step="0.1"
                    value={newProduct.cbgMg || ""}
                    onChange={(e) => setNewProduct(prev => ({...prev, cbgMg: parseFloat(e.target.value) || undefined}))}
                    placeholder="0.0"
                  />
                </div>
                <div>
                  <Label htmlFor="cbn-mg">CBN (mg)</Label>
                  <Input
                    id="cbn-mg"
                    type="number"
                    step="0.1"
                    value={newProduct.cbnMg || ""}
                    onChange={(e) => setNewProduct(prev => ({...prev, cbnMg: parseFloat(e.target.value) || undefined}))}
                    placeholder="0.0"
                  />
                </div>
                <div>
                  <Label htmlFor="cbc-mg">CBC (mg)</Label>
                  <Input
                    id="cbc-mg"
                    type="number"
                    step="0.1"
                    value={newProduct.cbcMg || ""}
                    onChange={(e) => setNewProduct(prev => ({...prev, cbcMg: parseFloat(e.target.value) || undefined}))}
                    placeholder="0.0"
                  />
                </div>
              </div>
            </div>
          )}

          {/* Supplier Information */}
          <div>
            <Label className="text-base font-medium">Supplier Information</Label>
            <div className="grid grid-cols-2 gap-4 mt-2">
              <div>
                <Label htmlFor="supplier">Supplier</Label>
                <Input
                  id="supplier"
                  value={newProduct.supplier || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, supplier: e.target.value}))}
                  placeholder="Supplier company name"
                />
              </div>
              <div>
                <Label htmlFor="grower">Grower</Label>
                <Input
                  id="grower"
                  value={newProduct.grower || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, grower: e.target.value}))}
                  placeholder="Grower name"
                />
              </div>
              <div>
                <Label htmlFor="farm">Farm</Label>
                <Input
                  id="farm"
                  value={newProduct.farm || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, farm: e.target.value}))}
                  placeholder="Farm name"
                />
              </div>
              <div>
                <Label htmlFor="vendor">Vendor</Label>
                <Input
                  id="vendor"
                  value={newProduct.vendor || ""}
                  onChange={(e) => setNewProduct(prev => ({...prev, vendor: e.target.value}))}
                  placeholder="Vendor company name"
                />
              </div>
            </div>
          </div>

          {/* Dates */}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="packaged-date">Packaged Date</Label>
              <Input
                id="packaged-date"
                type="date"
                value={newProduct.packagedDate || ""}
                onChange={(e) => setNewProduct(prev => ({...prev, packagedDate: e.target.value}))}
              />
            </div>
            <div>
              <Label htmlFor="expiration-date">Expiration Date</Label>
              <Input
                id="expiration-date"
                type="date"
                value={newProduct.expirationDate || ""}
                onChange={(e) => setNewProduct(prev => ({...prev, expirationDate: e.target.value}))}
              />
            </div>
          </div>

          {/* Additional Options */}
          <div className="space-y-3">
            <div className="flex items-center gap-4">
              <Switch
                checked={newProduct.isUntaxed}
                onCheckedChange={(checked) => setNewProduct(prev => ({...prev, isUntaxed: checked}))}
              />
              <div>
                <Label>Tax Exempt</Label>
                <p className="text-sm text-gray-600">Product is not subject to cannabis tax</p>
              </div>
            </div>
            <div className="flex items-center gap-4">
              <Switch
                checked={newProduct.isGLS}
                onCheckedChange={(checked) => setNewProduct(prev => ({...prev, isGLS: checked}))}
              />
              <div>
                <Label>Green Leaf Special (GLS)</Label>
                <p className="text-sm text-gray-600">Special pricing product - manual discounts only</p>
              </div>
            </div>
          </div>


          <div>
            <Label htmlFor="description">Description</Label>
            <Textarea
              id="description"
              value={newProduct.description || ""}
              onChange={(e) => setNewProduct(prev => ({...prev, description: e.target.value}))}
              placeholder="Product description..."
              rows={3}
            />
          </div>

          <div>
            <Label htmlFor="batch-notes">Batch Notes</Label>
            <Textarea
              id="batch-notes"
              value={newProduct.batchNotes || ""}
              onChange={(e) => setNewProduct(prev => ({...prev, batchNotes: e.target.value}))}
              placeholder="Internal batch notes..."
              rows={2}
            />
          </div>

          <div className="p-3 bg-blue-50 rounded text-sm">
            <p className="font-medium text-blue-800">Metrc Integration</p>
            <p className="text-blue-700">
              This product will be automatically registered in the Oregon Metrc system with a unique tracking tag.
            </p>
          </div>

          <div className="flex gap-2">
            <Button onClick={createProduct} className="flex-1">
              Create Product
            </Button>
            <Button variant="outline" onClick={() => setShowCreateDialog(false)} className="flex-1">
              Cancel
            </Button>
          </div>
        </div>
        </DialogContent>
      </Dialog>

      {/* Import Dialog */}
      <Dialog open={showImportDialog} onOpenChange={setShowImportDialog}>
        <DialogContent className="max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <Upload className="w-5 h-5" />
            Import Products from Metrc
          </DialogTitle>
        </DialogHeader>
        <div className="space-y-4">
          <div>
            <Label htmlFor="csv-file">Select CSV File</Label>
            <Input
              id="csv-file"
              type="file"
              accept=".csv"
              onChange={handleFileUpload}
            />
            {importFile && (
              <p className="text-sm text-green-600 mt-1">
                Selected: {importFile.name} ({(importFile.size / 1024).toFixed(1)} KB)
              </p>
            )}
          </div>

          <div className="p-3 bg-gray-50 rounded text-sm">
            <p className="font-medium mb-2">CSV Format Requirements:</p>
            <ul className="text-xs space-y-1 text-gray-600">
              <li>• Required fields: name, category, price, weight</li>
              <li>• Optional fields: strain, thc, cbd, supplier, grower, farm</li>
              <li>• Use provided template for best results</li>
              <li>• Maximum file size: 10MB</li>
            </ul>
          </div>

          {isImporting && (
            <div>
              <div className="flex justify-between text-sm mb-2">
                <span>Processing...</span>
                <span>{Math.round(importProgress)}%</span>
              </div>
              <Progress value={importProgress} className="h-2" />
            </div>
          )}

          <div className="flex gap-2">
            <Button 
              onClick={processImport} 
              className="flex-1"
              disabled={!importFile || isImporting}
            >
              {isImporting ? (
                <>
                  <RefreshCw className="w-4 h-4 mr-2 animate-spin" />
                  Processing...
                </>
              ) : (
                <>
                  <Upload className="w-4 h-4 mr-2" />
                  Import Products
                </>
              )}
            </Button>
            <Button 
              variant="outline" 
              onClick={() => {
                setShowImportDialog(false);
                setImportFile(null);
                setImportProgress(0);
              }}
              disabled={isImporting}
            >
              Cancel
            </Button>
          </div>
        </div>
        </DialogContent>
      </Dialog>

      {/* Import Details Dialog */}
      <Dialog open={showImportDetailsDialog} onOpenChange={setShowImportDetailsDialog}>
        <DialogContent className="max-w-3xl">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <FileText className="w-5 h-5" />
              Import Details - {selectedImportRecord?.fileName}
            </DialogTitle>
          </DialogHeader>
          {selectedImportRecord && (
            <div className="space-y-6">
              {/* Import Summary */}
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div className="text-center p-3 bg-gray-50 rounded-lg">
                  <div className="text-2xl font-bold text-gray-900">{selectedImportRecord.recordsTotal}</div>
                  <div className="text-sm text-gray-600">Total Records</div>
                </div>
                <div className="text-center p-3 bg-green-50 rounded-lg">
                  <div className="text-2xl font-bold text-green-600">{selectedImportRecord.recordsSuccessful}</div>
                  <div className="text-sm text-green-600">Successful</div>
                </div>
                <div className="text-center p-3 bg-red-50 rounded-lg">
                  <div className="text-2xl font-bold text-red-600">{selectedImportRecord.recordsFailed}</div>
                  <div className="text-sm text-red-600">Failed</div>
                </div>
                <div className="text-center p-3 bg-blue-50 rounded-lg">
                  <div className="text-2xl font-bold text-blue-600">
                    {Math.round((selectedImportRecord.recordsSuccessful / selectedImportRecord.recordsTotal) * 100)}%
                  </div>
                  <div className="text-sm text-blue-600">Success Rate</div>
                </div>
              </div>

              {/* Vendor Information */}
              {selectedImportRecord.vendorName && (
                <div className="p-4 bg-blue-50 rounded-lg">
                  <h4 className="font-medium text-blue-800 mb-3">Vendor Information</h4>
                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <span className="text-blue-600 font-medium">Vendor:</span>
                      <div className="text-blue-800">{selectedImportRecord.vendorName}</div>
                      <div className="text-xs text-blue-600">License: {selectedImportRecord.vendorLicense}</div>
                    </div>
                    <div>
                      <span className="text-blue-600 font-medium">Facility:</span>
                      <div className="text-blue-800">{selectedImportRecord.facilityName}</div>
                      <div className="text-xs text-blue-600">License: {selectedImportRecord.facilityLicense}</div>
                    </div>
                  </div>
                </div>
              )}

              {/* Sample Data Preview */}
              <div>
                <h4 className="font-medium mb-3">Sample Import Data</h4>
                <div className="border rounded-lg overflow-hidden">
                  <table className="w-full text-sm">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="p-3 text-left font-medium">Product Name</th>
                        <th className="p-3 text-left font-medium">Category</th>
                        <th className="p-3 text-left font-medium">Weight</th>
                        <th className="p-3 text-left font-medium">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr className="border-t">
                        <td className="p-3">Blue Dream Premium</td>
                        <td className="p-3">Flower</td>
                        <td className="p-3">1g</td>
                        <td className="p-3">
                          <Badge className="bg-green-100 text-green-800">Success</Badge>
                        </td>
                      </tr>
                      <tr className="border-t">
                        <td className="p-3">OG Kush</td>
                        <td className="p-3">Flower</td>
                        <td className="p-3">1g</td>
                        <td className="p-3">
                          <Badge className="bg-green-100 text-green-800">Success</Badge>
                        </td>
                      </tr>
                      <tr className="border-t">
                        <td className="p-3">Gummy Bears</td>
                        <td className="p-3">Edibles</td>
                        <td className="p-3">100mg</td>
                        <td className="p-3">
                          <Badge className="bg-green-100 text-green-800">Success</Badge>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              {/* Error Details */}
              {selectedImportRecord.errors && selectedImportRecord.errors.length > 0 && (
                <div>
                  <h4 className="font-medium mb-3 text-red-800">Import Errors</h4>
                  <div className="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <ul className="text-sm text-red-700 space-y-1">
                      {selectedImportRecord.errors.map((error, index) => (
                        <li key={index}>• {error}</li>
                      ))}
                    </ul>
                  </div>
                </div>
              )}

              {/* Metrc API Response */}
              {selectedImportRecord.metrcApiResponse && (
                <div>
                  <h4 className="font-medium mb-3">Metrc API Response</h4>
                  <div className={`p-3 rounded-lg text-sm ${
                    selectedImportRecord.status === 'completed'
                      ? 'bg-green-50 border border-green-200 text-green-700'
                      : 'bg-red-50 border border-red-200 text-red-700'
                  }`}>
                    {selectedImportRecord.metrcApiResponse}
                  </div>
                </div>
              )}

              <div className="flex gap-2">
                {selectedImportRecord.status === 'completed' && selectedImportRecord.recordsSuccessful > 0 && (
                  <Button
                    onClick={() => {
                      setShowImportDetailsDialog(false);
                      importToInventory(selectedImportRecord);
                    }}
                    className="bg-green-600 hover:bg-green-700"
                    disabled={importingToInventory}
                  >
                    <Package className="w-4 h-4 mr-2" />
                    Import to Inventory
                  </Button>
                )}
                <Button variant="outline" onClick={() => setShowImportDetailsDialog(false)}>
                  Close
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Individual Import Item Edit Dialog */}
      <Dialog open={showImportItemDialog} onOpenChange={setShowImportItemDialog}>
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>
              Edit Import Item ({currentImportIndex + 1} of {currentImportItems.length})
            </DialogTitle>
            <p className="text-sm text-muted-foreground">
              Review and edit this item before importing to inventory. You can modify any field or skip this item.
            </p>
          </DialogHeader>
          {editingImportItem && (
            <div className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="import-item-name">Product Name *</Label>
                  <Input
                    id="import-item-name"
                    value={editingImportItem.name}
                    onChange={(e) => setEditingImportItem(prev => prev ? {...prev, name: e.target.value} : null)}
                    placeholder="Enter product name"
                  />
                </div>
                <div>
                  <Label htmlFor="import-item-category">Category *</Label>
                  <Select
                    value={editingImportItem.category}
                    onValueChange={(value) => setEditingImportItem(prev => prev ? {...prev, category: value} : null)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select category" />
                    </SelectTrigger>
                    <SelectContent>
                      {categories.map(category => (
                        <SelectItem key={category} value={category}>{category}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div>
                <Label htmlFor="import-item-room">Storage Room *</Label>
                <Select
                  value={editingImportItem.room || ""}
                  onValueChange={(value) => setEditingImportItem(prev => prev ? {...prev, room: value} : null)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select room to store this product" />
                  </SelectTrigger>
                  <SelectContent>
                    {availableRooms.map(room => (
                      <SelectItem key={room.id} value={room.name}>{room.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="import-item-sku">SKU (max 50 characters)</Label>
                  <Input
                    id="import-item-sku"
                    value={editingImportItem.sku || ""}
                    onChange={(e) => {
                      const value = e.target.value.slice(0, 50);
                      setEditingImportItem(prev => prev ? {...prev, sku: value} : null);
                    }}
                    placeholder="Product SKU (up to 50 characters)"
                    maxLength={50}
                    className="font-mono"
                  />
                  <div className="text-xs text-gray-500 mt-1">
                    {(editingImportItem?.sku || '').length}/50 characters
                  </div>
                </div>
                <div>
                  <Label htmlFor="import-item-weight">Weight/Size *</Label>
                  <Input
                    id="import-item-weight"
                    value={editingImportItem.weight}
                    onChange={(e) => setEditingImportItem(prev => prev ? {...prev, weight: e.target.value} : null)}
                    placeholder="e.g., 1g, 100mg, 30ml"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="import-item-cost">Cost ($) *</Label>
                  <Input
                    id="import-item-cost"
                    type="number"
                    step="0.01"
                    value={editingImportItem.cost}
                    onChange={(e) => setEditingImportItem(prev => prev ? {...prev, cost: parseFloat(e.target.value) || 0} : null)}
                    placeholder="0.00"
                  />
                </div>
                <div>
                  <Label htmlFor="import-item-price">Sale Price ($) *</Label>
                  <Input
                    id="import-item-price"
                    type="number"
                    step="0.01"
                    value={editingImportItem.price}
                    onChange={(e) => setEditingImportItem(prev => prev ? {...prev, price: parseFloat(e.target.value) || 0} : null)}
                    placeholder="0.00"
                  />
                </div>
              </div>

              <div>
                <Label htmlFor="import-item-strain">Strain</Label>
                <Select
                  value={editingImportItem.strain || ""}
                  onValueChange={(value) => setEditingImportItem(prev => prev ? {...prev, strain: value} : null)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select strain type" />
                  </SelectTrigger>
                  <SelectContent>
                    {strains.map(strain => (
                      <SelectItem key={strain} value={strain}>{strain}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              {/* Cannabinoid Content */}
              <div>
                <Label className="text-base font-medium">Cannabinoid Content</Label>
                <div className="grid grid-cols-5 gap-4 mt-2">
                  <div>
                    <Label htmlFor="import-thc">THC (%)</Label>
                    <Input
                      id="import-thc"
                      type="number"
                      step="0.1"
                      value={editingImportItem.thc || ""}
                      onChange={(e) => setEditingImportItem(prev => prev ? {...prev, thc: parseFloat(e.target.value) || undefined} : null)}
                      placeholder="0.0"
                    />
                  </div>
                  <div>
                    <Label htmlFor="import-cbd">CBD (%)</Label>
                    <Input
                      id="import-cbd"
                      type="number"
                      step="0.1"
                      value={editingImportItem.cbd || ""}
                      onChange={(e) => setEditingImportItem(prev => prev ? {...prev, cbd: parseFloat(e.target.value) || undefined} : null)}
                      placeholder="0.0"
                    />
                  </div>
                  <div>
                    <Label htmlFor="import-cbg">CBG (%)</Label>
                    <Input
                      id="import-cbg"
                      type="number"
                      step="0.1"
                      value={editingImportItem.cbg || ""}
                      onChange={(e) => setEditingImportItem(prev => prev ? {...prev, cbg: parseFloat(e.target.value) || undefined} : null)}
                      placeholder="0.0"
                    />
                  </div>
                  <div>
                    <Label htmlFor="import-cbn">CBN (%)</Label>
                    <Input
                      id="import-cbn"
                      type="number"
                      step="0.1"
                      value={editingImportItem.cbn || ""}
                      onChange={(e) => setEditingImportItem(prev => prev ? {...prev, cbn: parseFloat(e.target.value) || undefined} : null)}
                      placeholder="0.0"
                    />
                  </div>
                  <div>
                    <Label htmlFor="import-cbc">CBC (%)</Label>
                    <Input
                      id="import-cbc"
                      type="number"
                      step="0.1"
                      value={editingImportItem.cbc || ""}
                      onChange={(e) => setEditingImportItem(prev => prev ? {...prev, cbc: parseFloat(e.target.value) || undefined} : null)}
                      placeholder="0.0"
                    />
                  </div>
                </div>
              </div>

              {/* Supplier Information */}
              <div>
                <Label className="text-base font-medium">Supplier Information</Label>
                <div className="grid grid-cols-2 gap-4 mt-2">
                  <div>
                    <Label htmlFor="import-supplier">Supplier</Label>
                    <Input
                      id="import-supplier"
                      value={editingImportItem.supplier || ""}
                      onChange={(e) => setEditingImportItem(prev => prev ? {...prev, supplier: e.target.value} : null)}
                      placeholder="Supplier company name"
                    />
                  </div>
                  <div>
                    <Label htmlFor="import-grower">Grower</Label>
                    <Input
                      id="import-grower"
                      value={editingImportItem.grower || ""}
                      onChange={(e) => setEditingImportItem(prev => prev ? {...prev, grower: e.target.value} : null)}
                      placeholder="Grower name"
                    />
                  </div>
                </div>
              </div>

              {/* Dates */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="import-packaged-date">Packaged Date</Label>
                  <Input
                    id="import-packaged-date"
                    type="date"
                    value={editingImportItem.packagedDate || ""}
                    onChange={(e) => setEditingImportItem(prev => prev ? {...prev, packagedDate: e.target.value} : null)}
                  />
                </div>
                <div>
                  <Label htmlFor="import-expiration-date">Expiration Date</Label>
                  <Input
                    id="import-expiration-date"
                    type="date"
                    value={editingImportItem.expirationDate || ""}
                    onChange={(e) => setEditingImportItem(prev => prev ? {...prev, expirationDate: e.target.value} : null)}
                  />
                </div>
              </div>

              {/* Metrc Information */}
              <div className="p-4 bg-blue-50 rounded-lg">
                <Label className="text-base font-medium text-blue-800">Metrc Information</Label>
                <div className="grid grid-cols-2 gap-4 mt-2">
                  <div>
                    <Label className="text-sm text-blue-700">Metrc Tag</Label>
                    <div className="font-mono text-sm p-2 bg-white rounded border">
                      {editingImportItem.metrcTag || "Auto-generated"}
                    </div>
                  </div>
                  <div>
                    <Label className="text-sm text-blue-700">Batch ID</Label>
                    <div className="font-mono text-sm p-2 bg-white rounded border">
                      {editingImportItem.batchId || "Auto-generated"}
                    </div>
                  </div>
                </div>
              </div>

              <div className="flex justify-between gap-2 pt-4 border-t">
                <div className="flex gap-2">
                  <Button onClick={handleImportItemSave} className="bg-green-600 hover:bg-green-700">
                    <Package className="w-4 h-4 mr-2" />
                    Import This Item
                  </Button>
                  <Button variant="outline" onClick={handleImportItemSkip}>
                    Skip Item
                  </Button>
                </div>
                <Button variant="outline" onClick={handleImportItemCancel}>
                  Cancel Import
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Transfer to Sales Floor Dialog */}
      <Dialog open={showTransferDialog} onOpenChange={setShowTransferDialog}>
        <DialogContent className="max-w-md">
          <DialogHeader>
            <DialogTitle>Transfer to Sales Floor</DialogTitle>
          </DialogHeader>
          {selectedInventoryItem && (
            <div className="space-y-4">
              <div className="p-4 bg-gray-50 rounded-lg">
                <h3 className="font-semibold">{selectedInventoryItem.name}</h3>
                <p className="text-sm text-muted-foreground">
                  {selectedInventoryItem.category} • {selectedInventoryItem.weight}
                </p>
                <p className="text-xs text-muted-foreground mt-1">
                  Current Location: {selectedInventoryItem.room} • Available: {selectedInventoryItem.stock} units
                </p>
              </div>

              <div>
                <Label htmlFor="transfer-qty">Transfer Quantity</Label>
                <Input
                  id="transfer-qty"
                  type="number"
                  min="1"
                  max={selectedInventoryItem.stock}
                  value={transferQuantity}
                  onChange={(e) => setTransferQuantity(e.target.value)}
                  placeholder="Enter quantity to transfer"
                />
                <p className="text-xs text-muted-foreground mt-1">
                  Maximum: {selectedInventoryItem.stock} units
                </p>
              </div>

              <div className="p-3 bg-blue-50 rounded-lg">
                <div className="text-sm font-medium text-blue-800 mb-1">Transfer Details:</div>
                <div className="text-xs text-blue-700">
                  • From: {selectedInventoryItem.room}
                  • To: Sales Floor
                  • Quantity: {transferQuantity || 0} units
                  • Value: ${((selectedInventoryItem.price * (parseInt(transferQuantity) || 0))).toFixed(2)}
                </div>
              </div>

              <div className="flex gap-2">
                <Button onClick={confirmTransfer} className="flex-1" disabled={!transferQuantity || parseInt(transferQuantity) <= 0}>
                  <ArrowRightLeft className="w-4 h-4 mr-2" />
                  Confirm Transfer
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowTransferDialog(false);
                    setSelectedInventoryItem(null);
                    setTransferQuantity("");
                  }}
                  className="flex-1"
                >
                  Cancel
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Edit Inventory Item Dialog */}
      <Dialog open={showEditInventoryDialog} onOpenChange={setShowEditInventoryDialog}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Edit Inventory Item</DialogTitle>
          </DialogHeader>
          {editingInventoryItem && (
            <div className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="edit-name">Product Name *</Label>
                  <Input
                    id="edit-name"
                    value={editingInventoryItem.name}
                    onChange={(e) => setEditingInventoryItem(prev => ({...prev, name: e.target.value}))}
                    placeholder="Enter product name"
                  />
                </div>
                <div>
                  <Label htmlFor="edit-category">Category *</Label>
                  <Select
                    value={editingInventoryItem.category}
                    onValueChange={(value) => setEditingInventoryItem(prev => ({...prev, category: value}))}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select category" />
                    </SelectTrigger>
                    <SelectContent>
                      {categories.map(category => (
                        <SelectItem key={category} value={category}>{category}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div className="grid grid-cols-4 gap-4">
                <div>
                  <Label htmlFor="edit-price">Sell Price ($) *</Label>
                  <Input
                    id="edit-price"
                    type="number"
                    step="0.01"
                    value={editingInventoryItem.price}
                    onChange={(e) => setEditingInventoryItem(prev => ({...prev, price: parseFloat(e.target.value) || 0}))}
                    placeholder="0.00"
                    className="font-semibold text-green-700"
                  />
                </div>
                <div>
                  <Label htmlFor="edit-cost">Cost ($) *</Label>
                  <Input
                    id="edit-cost"
                    type="number"
                    step="0.01"
                    value={editingInventoryItem.cost || 0}
                    onChange={(e) => setEditingInventoryItem(prev => ({...prev, cost: parseFloat(e.target.value) || 0}))}
                    placeholder="0.00"
                    className="font-semibold text-red-700"
                  />
                </div>
                <div>
                  <Label htmlFor="edit-stock">Stock *</Label>
                  <Input
                    id="edit-stock"
                    type="number"
                    value={editingInventoryItem.stock}
                    onChange={(e) => setEditingInventoryItem(prev => ({...prev, stock: parseInt(e.target.value) || 0}))}
                    placeholder="0"
                  />
                </div>
                <div>
                  <Label htmlFor="edit-weight">Weight/Size</Label>
                  <Input
                    id="edit-weight"
                    value={editingInventoryItem.weight}
                    onChange={(e) => setEditingInventoryItem(prev => ({...prev, weight: e.target.value}))}
                    placeholder="e.g., 1g, 100mg"
                  />
                </div>
              </div>

              {/* Margin Calculation Display */}
              {editingInventoryItem.price && editingInventoryItem.cost && (
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <div className="text-sm font-medium text-blue-800 mb-2">Profit Analysis</div>
                  <div className="grid grid-cols-3 gap-4 text-sm">
                    <div>
                      <span className="text-blue-700">Margin per Unit:</span>
                      <div className="font-bold text-blue-900">
                        ${(editingInventoryItem.price - editingInventoryItem.cost).toFixed(2)}
                      </div>
                    </div>
                    <div>
                      <span className="text-blue-700">Margin %:</span>
                      <div className="font-bold text-blue-900">
                        {(((editingInventoryItem.price - editingInventoryItem.cost) / editingInventoryItem.cost) * 100).toFixed(1)}%
                      </div>
                    </div>
                    <div>
                      <span className="text-blue-700">Total Potential Profit:</span>
                      <div className="font-bold text-blue-900">
                        ${((editingInventoryItem.price - editingInventoryItem.cost) * editingInventoryItem.stock).toFixed(2)}
                      </div>
                    </div>
                  </div>
                </div>
              )}

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="edit-sku">SKU (max 50 characters)</Label>
                  <Input
                    id="edit-sku"
                    value={editingInventoryItem.sku || ""}
                    onChange={(e) => {
                      const value = e.target.value.slice(0, 50);
                      setEditingInventoryItem(prev => ({...prev, sku: value}));
                    }}
                    placeholder="Product SKU (up to 50 characters)"
                    maxLength={50}
                    className="font-mono"
                  />
                  <div className="text-xs text-gray-500 mt-1">
                    {(editingInventoryItem.sku || '').length}/50 characters
                  </div>
                </div>
                <div>
                  <Label htmlFor="edit-room">Storage Room</Label>
                  <Select
                    value={editingInventoryItem.room}
                    onValueChange={(value) => setEditingInventoryItem(prev => ({...prev, room: value}))}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select room" />
                    </SelectTrigger>
                    <SelectContent>
                      {availableRooms.filter(room => room.name !== "Sales Floor").map(room => (
                        <SelectItem key={room.id} value={room.name}>{room.name}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>

              {editingInventoryItem.thc !== undefined && (
                <div>
                  <Label className="text-base font-medium">Cannabinoid Content</Label>
                  <div className="grid grid-cols-3 gap-4 mt-2">
                    <div>
                      <Label htmlFor="edit-thc">THC (%)</Label>
                      <Input
                        id="edit-thc"
                        type="number"
                        step="0.1"
                        value={editingInventoryItem.thc || ""}
                        onChange={(e) => setEditingInventoryItem(prev => ({...prev, thc: parseFloat(e.target.value) || 0}))}
                        placeholder="0.0"
                      />
                    </div>
                    <div>
                      <Label htmlFor="edit-cbd">CBD (%)</Label>
                      <Input
                        id="edit-cbd"
                        type="number"
                        step="0.1"
                        value={editingInventoryItem.cbd || ""}
                        onChange={(e) => setEditingInventoryItem(prev => ({...prev, cbd: parseFloat(e.target.value) || 0}))}
                        placeholder="0.0"
                      />
                    </div>
                    <div>
                      <Label htmlFor="edit-strain">Strain</Label>
                      <Input
                        id="edit-strain"
                        value={editingInventoryItem.strain || ""}
                        onChange={(e) => setEditingInventoryItem(prev => ({...prev, strain: e.target.value}))}
                        placeholder="e.g., Hybrid, Indica"
                      />
                    </div>
                  </div>
                </div>
              )}

              <div>
                <Label htmlFor="edit-supplier">Supplier</Label>
                <Input
                  id="edit-supplier"
                  value={editingInventoryItem.supplier || ""}
                  onChange={(e) => setEditingInventoryItem(prev => ({...prev, supplier: e.target.value}))}
                  placeholder="Supplier name"
                />
              </div>

              <div className="p-3 bg-gray-50 rounded-lg">
                <div className="text-sm font-medium mb-2">METRC Information</div>
                <div className="grid grid-cols-2 gap-4 text-xs">
                  <div>
                    <span className="text-gray-600">METRC Tag:</span>
                    <div className="font-mono">{editingInventoryItem.metrcTag}</div>
                  </div>
                  <div>
                    <span className="text-gray-600">Batch ID:</span>
                    <div className="font-mono">{editingInventoryItem.batchId}</div>
                  </div>
                </div>
              </div>

              <div className="flex gap-2">
                <Button onClick={saveInventoryEdit} className="flex-1">
                  <Edit className="w-4 h-4 mr-2" />
                  Save Changes
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowEditInventoryDialog(false);
                    setEditingInventoryItem(null);
                  }}
                  className="flex-1"
                >
                  Cancel
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Create Catalogue Item Dialog */}
      <Dialog open={showCreateCatalogueItemDialog} onOpenChange={setShowCreateCatalogueItemDialog}>
        <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Create Product Catalogue Template</DialogTitle>
          </DialogHeader>
          <div className="space-y-6">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="cat-name">Template Name *</Label>
                <Input
                  id="cat-name"
                  value={newCatalogueItem.name || ""}
                  onChange={(e) => setNewCatalogueItem(prev => ({...prev, name: e.target.value}))}
                  placeholder="e.g., Premium Indoor Flower Template"
                />
              </div>
              <div>
                <Label htmlFor="cat-category">Category *</Label>
                <Select value={newCatalogueItem.category || ""} onValueChange={(value) => setNewCatalogueItem(prev => ({...prev, category: value}))}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select category" />
                  </SelectTrigger>
                  <SelectContent>
                    {categories.map(category => (
                      <SelectItem key={category} value={category}>{category}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="grid grid-cols-3 gap-4">
              <div>
                <Label htmlFor="cat-price">Base Price ($) *</Label>
                <Input
                  id="cat-price"
                  type="number"
                  step="0.01"
                  value={newCatalogueItem.basePrice || ""}
                  onChange={(e) => setNewCatalogueItem(prev => ({...prev, basePrice: parseFloat(e.target.value) || 0}))}
                  placeholder="0.00"
                />
              </div>
              <div>
                <Label htmlFor="cat-cost">Base Cost ($)</Label>
                <Input
                  id="cat-cost"
                  type="number"
                  step="0.01"
                  value={newCatalogueItem.baseCost || ""}
                  onChange={(e) => setNewCatalogueItem(prev => ({...prev, baseCost: parseFloat(e.target.value) || 0}))}
                  placeholder="0.00"
                />
              </div>
              <div>
                <Label htmlFor="cat-weight">Default Weight *</Label>
                <Input
                  id="cat-weight"
                  value={newCatalogueItem.defaultWeight || ""}
                  onChange={(e) => setNewCatalogueItem(prev => ({...prev, defaultWeight: e.target.value}))}
                  placeholder="e.g., 1g, 100mg, 30ml"
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="cat-room">Default Room</Label>
                <Select value={newCatalogueItem.defaultRoom || ""} onValueChange={(value) => setNewCatalogueItem(prev => ({...prev, defaultRoom: value}))}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select default room" />
                  </SelectTrigger>
                  <SelectContent>
                    {availableRooms.map(room => (
                      <SelectItem key={room.id} value={room.name}>{room.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label htmlFor="cat-strain">Strain Type</Label>
                <Select value={newCatalogueItem.strain || ""} onValueChange={(value) => setNewCatalogueItem(prev => ({...prev, strain: value}))}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select strain type" />
                  </SelectTrigger>
                  <SelectContent>
                    {strains.map(strain => (
                      <SelectItem key={strain} value={strain}>{strain}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="cat-supplier">Default Supplier</Label>
                <Input
                  id="cat-supplier"
                  value={newCatalogueItem.supplier || ""}
                  onChange={(e) => setNewCatalogueItem(prev => ({...prev, supplier: e.target.value}))}
                  placeholder="Supplier name"
                />
              </div>
              <div>
                <Label htmlFor="cat-vendor">Default Vendor</Label>
                <Input
                  id="cat-vendor"
                  value={newCatalogueItem.vendor || ""}
                  onChange={(e) => setNewCatalogueItem(prev => ({...prev, vendor: e.target.value}))}
                  placeholder="Vendor name"
                />
              </div>
            </div>

            <div>
              <Label htmlFor="cat-sku-pattern">SKU Pattern</Label>
              <Input
                id="cat-sku-pattern"
                value={newCatalogueItem.defaultSkuPattern || ""}
                onChange={(e) => setNewCatalogueItem(prev => ({...prev, defaultSkuPattern: e.target.value}))}
                placeholder="e.g., PREM-{STRAIN}-1G or EDIBLE-{FLAVOR}-{MG}MG"
              />
              <p className="text-xs text-muted-foreground mt-1">
                Use {`{STRAIN}, {FLAVOR}, {MG}`} etc. as placeholders that will be filled during import mapping
              </p>
            </div>

            <div>
              <Label htmlFor="cat-description">Description</Label>
              <Textarea
                id="cat-description"
                value={newCatalogueItem.description || ""}
                onChange={(e) => setNewCatalogueItem(prev => ({...prev, description: e.target.value}))}
                placeholder="Describe this product template..."
                rows={3}
              />
            </div>

            <div className="p-4 bg-blue-50 rounded-lg">
              <div className="text-sm font-medium text-blue-800 mb-2">Location Information</div>
              <div className="text-xs text-blue-700">
                This template will be created for <strong>{currentLocationName}</strong> (ID: {currentLocationId})
                <br />Templates are location-specific and cannot be shared between locations.
              </div>
            </div>

            <div className="flex gap-2">
              <Button onClick={createCatalogueItem} className="flex-1">
                <Plus className="w-4 h-4 mr-2" />
                Create Template
              </Button>
              <Button
                variant="outline"
                onClick={() => {
                  setShowCreateCatalogueItemDialog(false);
                  setNewCatalogueItem({
                    name: "",
                    category: "",
                    basePrice: 0,
                    baseCost: 0,
                    defaultWeight: "",
                    description: "",
                    isTemplate: true,
                    locationId: currentLocationId,
                    locationName: currentLocationName,
                    isActive: true
                  });
                }}
                className="flex-1"
              >
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      {/* Import Mapping Dialog */}
      <Dialog open={showImportMappingDialog} onOpenChange={setShowImportMappingDialog}>
        <DialogContent className="max-w-4xl">
          <DialogHeader>
            <DialogTitle>Map METRC Import to Catalogue Template</DialogTitle>
          </DialogHeader>
          <div className="space-y-6">
            <div className="grid grid-cols-2 gap-6">
              {/* Import Item Selection */}
              <div>
                <Label className="text-base font-medium">Select Import Item</Label>
                <div className="mt-2 max-h-60 overflow-y-auto border rounded-lg">
                  {metrcImports
                    .filter(imp => imp.status === 'completed' && imp.items && imp.items.length > 0)
                    .flatMap(imp => imp.items || [])
                    .map(item => (
                    <div
                      key={item.id}
                      className={`p-3 border-b cursor-pointer hover:bg-gray-50 ${
                        selectedImportItem?.id === item.id ? 'bg-blue-50 border-blue-200' : ''
                      }`}
                      onClick={() => setSelectedImportItem(item)}
                    >
                      <div className="font-medium">{item.name}</div>
                      <div className="text-sm text-gray-600">{item.category} • {item.weight} • ${item.price.toFixed(2)}</div>
                      <div className="text-xs text-gray-500">SKU: {item.sku} | Vendor: {item.vendor}</div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Catalogue Template Selection */}
              <div>
                <Label className="text-base font-medium">Select Catalogue Template</Label>
                <div className="mt-2 max-h-60 overflow-y-auto border rounded-lg">
                  {catalogueItems
                    .filter(item => item.locationId === currentLocationId && item.isActive)
                    .map(item => (
                    <div
                      key={item.id}
                      className={`p-3 border-b cursor-pointer hover:bg-gray-50 ${
                        selectedCatalogueItem?.id === item.id ? 'bg-green-50 border-green-200' : ''
                      }`}
                      onClick={() => setSelectedCatalogueItem(item)}
                    >
                      <div className="font-medium">{item.name}</div>
                      <div className="text-sm text-gray-600">{item.category} • {item.defaultWeight} • ${item.basePrice.toFixed(2)}</div>
                      <div className="text-xs text-gray-500">Room: {item.defaultRoom} | Supplier: {item.supplier}</div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Mapping Preview */}
            {selectedImportItem && selectedCatalogueItem && (
              <div className="p-4 bg-gray-50 rounded-lg">
                <div className="text-sm font-medium mb-3">Mapping Preview</div>
                <div className="grid grid-cols-2 gap-6 text-xs">
                  <div>
                    <div className="font-medium text-blue-800">Import Item: {selectedImportItem.name}</div>
                    <div className="mt-1 space-y-1">
                      <div>Category: {selectedImportItem.category}</div>
                      <div>Price: ${selectedImportItem.price.toFixed(2)}</div>
                      <div>Cost: ${selectedImportItem.cost.toFixed(2)}</div>
                      <div>Weight: {selectedImportItem.weight}</div>
                      <div>Room: {selectedImportItem.room}</div>
                    </div>
                  </div>
                  <div>
                    <div className="font-medium text-green-800">Template: {selectedCatalogueItem.name}</div>
                    <div className="mt-1 space-y-1">
                      <div>Category: {selectedCatalogueItem.category}</div>
                      <div>Base Price: ${selectedCatalogueItem.basePrice.toFixed(2)}</div>
                      <div>Base Cost: ${selectedCatalogueItem.baseCost.toFixed(2)}</div>
                      <div>Default Weight: {selectedCatalogueItem.defaultWeight}</div>
                      <div>Default Room: {selectedCatalogueItem.defaultRoom}</div>
                    </div>
                  </div>
                </div>
                <div className="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded">
                  <div className="text-xs text-yellow-800">
                    <strong>Differences will be noted:</strong>
                    {selectedImportItem.price !== selectedCatalogueItem.basePrice && (
                      <span> Price override (${selectedImportItem.price.toFixed(2)})</span>
                    )}
                    {selectedImportItem.cost !== selectedCatalogueItem.baseCost && (
                      <span> Cost override (${selectedImportItem.cost.toFixed(2)})</span>
                    )}
                    {selectedImportItem.weight !== selectedCatalogueItem.defaultWeight && (
                      <span> Weight override ({selectedImportItem.weight})</span>
                    )}
                  </div>
                </div>
              </div>
            )}

            <div className="flex gap-2">
              <Button
                onClick={() => selectedImportItem && selectedCatalogueItem && mapImportToCatalogue(selectedImportItem, selectedCatalogueItem)}
                disabled={!selectedImportItem || !selectedCatalogueItem}
                className="flex-1"
              >
                <ArrowRightLeft className="w-4 h-4 mr-2" />
                Create Mapping
              </Button>
              <Button
                variant="outline"
                onClick={() => {
                  setShowImportMappingDialog(false);
                  setSelectedImportItem(null);
                  setSelectedCatalogueItem(null);
                }}
                className="flex-1"
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
