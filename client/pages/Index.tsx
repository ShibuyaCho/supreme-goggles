import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { Progress } from "@/components/ui/progress";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import {
  ShoppingCart,
  Search,
  CreditCard,
  DollarSign,
  Package,
  Users,
  BarChart3,
  Settings,
  LogOut,
  Plus,
  Minus,
  Trash2,
  Percent,
  Tag,
  Leaf,
  Clock,
  FileText,
  Home,
  Database,
  Edit3,
  Printer,
  QrCode,
  FileOutput,
  Lock,
  X,
  ArrowRightLeft,
  Building,
  ArrowUpDown,
  ChevronDown,
  Grid3X3,
  List
} from "lucide-react";

interface Product {
  id: string;
  name: string;
  price: number;
  category: string;
  image: string;
  stock: number;
  sku?: string;
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
  strain?: string;
  weight?: string;
  metrcTag?: string;
  batchId?: string;
  harvestDate?: string;
  sourceHarvest?: string;
  supplier?: string;
  supplierUID?: string;
  grower?: string;
  vendor?: string;
  farm?: string;
  administrativeHold?: boolean;
  testResults?: {
    tested: boolean;
    labName?: string;
    testDate?: string;
    cannabinoids?: { thc: number; cbd: number; cbg?: number; cbn?: number; cbc?: number; };
    contaminants?: { passed: boolean; };
  };
  packagedDate?: string;
  expirationDate?: string;
  isUntaxed?: boolean;
  minimumPrice?: number;
  weightThreshold?: number; // for flower products
  isGLS?: boolean; // Green Leaf Special - no discounts allowed
  room?: string; // Storage room location
}

// Edit Product Form Component
const EditProductForm = ({ product, onSave, onCancel }: {
  product: Product;
  onSave: (product: Product) => void;
  onCancel: () => void;
}) => {
  const [editedProduct, setEditedProduct] = useState<Product>({ ...product });

  const categories = [
    "Flower", "Pre-Rolls", "Concentrates", "Extracts", "Edibles", "Topicals",
    "Tinctures", "Vapes", "Inhalable Cannabinoids", "Clones", "Hemp", "Paraphernalia", "Accessories"
  ];

  const strains = [
    "Sativa", "Indica", "Hybrid", "CBD-Dominant", "1:1 THC:CBD", "High-CBD", "Mixed"
  ];

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-2 gap-4">
        <div>
          <Label htmlFor="edit-name">Product Name *</Label>
          <Input
            id="edit-name"
            value={editedProduct.name}
            onChange={(e) => setEditedProduct(prev => ({...prev, name: e.target.value}))}
            placeholder="Enter product name"
          />
        </div>
        <div>
          <Label htmlFor="edit-category">Category *</Label>
          <Select value={editedProduct.category} onValueChange={(value) => setEditedProduct(prev => ({...prev, category: value}))}>
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
          <Label htmlFor="edit-price">Price ($) *</Label>
          <Input
            id="edit-price"
            type="number"
            step="0.01"
            value={editedProduct.price}
            onChange={(e) => setEditedProduct(prev => ({...prev, price: parseFloat(e.target.value) || 0}))}
            placeholder="0.00"
          />
        </div>
        <div>
          <Label htmlFor="edit-weight">Weight/Size *</Label>
          <Input
            id="edit-weight"
            value={editedProduct.weight || ""}
            onChange={(e) => setEditedProduct(prev => ({...prev, weight: e.target.value}))}
            placeholder="e.g., 1g, 100mg, 30ml"
          />
        </div>
        <div>
          <Label htmlFor="edit-stock">Stock *</Label>
          <Input
            id="edit-stock"
            type="number"
            value={editedProduct.stock}
            onChange={(e) => setEditedProduct(prev => ({...prev, stock: parseInt(e.target.value) || 0}))}
            placeholder="0"
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div>
          <Label htmlFor="edit-sku">SKU</Label>
          <Input
            id="edit-sku"
            value={editedProduct.sku || ""}
            onChange={(e) => setEditedProduct(prev => ({...prev, sku: e.target.value}))}
            placeholder="Product SKU"
          />
        </div>
        <div>
          <Label htmlFor="edit-strain">Strain</Label>
          <Select value={editedProduct.strain || ""} onValueChange={(value) => setEditedProduct(prev => ({...prev, strain: value}))}>
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

      {/* Product Image Upload/Edit */}
      <div>
        <Label htmlFor="edit-product-image">Product Image</Label>
        <div className="border-2 border-dashed border-gray-300 rounded-lg p-6">
          <div className="text-center">
            {editedProduct.image ? (
              <div className="space-y-4">
                <img
                  src={editedProduct.image}
                  alt="Product preview"
                  className="mx-auto h-32 w-32 object-cover rounded-lg"
                />
                <div className="flex gap-2 justify-center">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => setEditedProduct(prev => ({...prev, image: ""}))}
                  >
                    Remove Image
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => document.getElementById('edit-file-upload')?.click()}
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
                    onClick={() => document.getElementById('edit-file-upload')?.click()}
                  >
                    Choose Image
                  </Button>
                  <p className="text-sm mt-2">or drag and drop</p>
                </div>
                <p className="text-xs text-gray-500">PNG, JPG up to 10MB</p>
              </div>
            )}
            <input
              id="edit-file-upload"
              type="file"
              accept="image/*"
              className="hidden"
              onChange={(e) => {
                const file = e.target.files?.[0];
                if (file) {
                  const reader = new FileReader();
                  reader.onload = (event) => {
                    setEditedProduct(prev => ({...prev, image: event.target?.result as string}));
                  };
                  reader.readAsDataURL(file);
                }
              }}
            />
          </div>
        </div>
      </div>

      <div className="flex gap-2">
        <Button onClick={() => onSave(editedProduct)} className="flex-1">
          Update Product
        </Button>
        <Button variant="outline" onClick={onCancel} className="flex-1">
          Cancel
        </Button>
      </div>
    </div>
  );
};

// Marijuana Leaf Symbol Component for GLS items
const MarijuanaLeaf = ({ size = "24" }: { size?: string }) => (
  <div className={`w-${size} h-${size} flex items-center justify-center`}>
    <svg viewBox="0 0 100 100" className="w-full h-full">
      {/* Marijuana 7-leaflet cannabis leaf shape */}
      <g className="fill-green-600">
        {/* Center leaflet */}
        <path d="M50 10 C52 10, 54 15, 54 25 L54 70 C54 75, 52 80, 50 80 C48 80, 46 75, 46 70 L46 25 C46 15, 48 10, 50 10 Z" />

        {/* Left leaflets */}
        <path d="M42 20 C44 18, 46 20, 47 25 L48 55 C47 60, 45 62, 42 60 C38 58, 35 50, 33 40 C32 30, 35 22, 42 20 Z" />
        <path d="M32 30 C35 28, 38 30, 40 35 L42 50 C41 55, 39 57, 35 55 C30 53, 25 45, 22 38 C20 30, 25 28, 32 30 Z" />
        <path d="M22 40 C25 38, 28 40, 30 45 L32 55 C31 58, 29 60, 25 58 C20 56, 15 50, 12 45 C10 40, 15 38, 22 40 Z" />

        {/* Right leaflets */}
        <path d="M58 20 C56 18, 54 20, 53 25 L52 55 C53 60, 55 62, 58 60 C62 58, 65 50, 67 40 C68 30, 65 22, 58 20 Z" />
        <path d="M68 30 C65 28, 62 30, 60 35 L58 50 C59 55, 61 57, 65 55 C70 53, 75 45, 78 38 C80 30, 75 28, 68 30 Z" />
        <path d="M78 40 C75 38, 72 40, 70 45 L68 55 C69 58, 71 60, 75 58 C80 56, 85 50, 88 45 C90 40, 85 38, 78 40 Z" />
      </g>

      {/* Central stem and veins */}
      <g className="stroke-green-800 fill-none" strokeWidth="0.8">
        <path d="M50 20 L50 75" />
        <path d="M50 30 L42 35" />
        <path d="M50 35 L32 40" />
        <path d="M50 40 L22 45" />
        <path d="M50 30 L58 35" />
        <path d="M50 35 L68 40" />
        <path d="M50 40 L78 45" />
      </g>

      {/* Serrated edges detail */}
      <g className="stroke-green-700 fill-none" strokeWidth="0.3">
        <path d="M46 25 L44 23 L46 27 L44 25 L46 29" />
        <path d="M54 25 L56 23 L54 27 L56 25 L54 29" />
        <path d="M40 35 L38 33 L40 37 L38 35" />
        <path d="M60 35 L62 33 L60 37 L62 35" />
      </g>
    </svg>
  </div>
);

// Navigation items for dropdown
const navigationItems = [
  { icon: BarChart3, label: "Analytics", path: "/analytics" },
  { icon: Users, label: "Customers", path: "/customers" },
  { icon: FileText, label: "Custom Reports", path: "/reports" },
  { icon: BarChart3, label: "Inventory Evaluation", path: "/inventory-report" },
  { icon: Tag, label: "Deals & Specials", path: "/deals" },
  { icon: Users, label: "Employees", path: "/employees" },
  { icon: Package, label: "Inventory", path: "/products?tab=inventory" },
  { icon: Users, label: "Loyalty Program", path: "/loyalty" },
  { icon: Clock, label: "Order Queue", path: "/queue" },
  { icon: ShoppingCart, label: "Point of Sale", path: "/" },
  { icon: DollarSign, label: "Price Tiers", path: "/price-tiers" },
  { icon: Plus, label: "Product Creation", path: "/products" },
  { icon: Home, label: "Rooms & Drawers", path: "/rooms" },
  { icon: Database, label: "Sales Management", path: "/sales" },
  { icon: Settings, label: "Settings", path: "/settings" }
];

// Oregon Logo Component
const OregonLogo = () => (
  <div className="w-10 h-10 relative">
    <svg viewBox="0 0 100 100" className="w-full h-full">
      {/* Oregon state outline */}
      <path
        d="M20,30 L80,30 L80,70 L60,75 L40,75 L20,70 Z"
        fill="url(#flag-pattern)"
        stroke="#333"
        strokeWidth="1"
      />
      {/* Cash register icon inside */}
      <rect x="35" y="45" width="30" height="20" fill="#333" rx="2"/>
      <rect x="40" y="50" width="20" height="8" fill="#fff" rx="1"/>
      <circle cx="42" cy="62" r="1" fill="#fff"/>
      <circle cx="48" cy="62" r="1" fill="#fff"/>
      <circle cx="54" cy="62" r="1" fill="#fff"/>
      <circle cx="58" cy="62" r="1" fill="#fff"/>

      {/* American flag pattern */}
      <defs>
        <pattern id="flag-pattern" patternUnits="userSpaceOnUse" width="100" height="100">
          <rect width="100" height="100" fill="#B22234"/>
          <rect y="0" width="100" height="7" fill="#fff"/>
          <rect y="14" width="100" height="7" fill="#fff"/>
          <rect y="28" width="100" height="7" fill="#fff"/>
          <rect y="42" width="100" height="7" fill="#fff"/>
          <rect y="56" width="100" height="7" fill="#fff"/>
          <rect y="70" width="100" height="7" fill="#fff"/>
          <rect y="84" width="100" height="7" fill="#fff"/>
          <rect x="0" y="0" width="40" height="50" fill="#3C3B6E"/>
          <g fill="white">
            <text x="20" y="25" textAnchor="middle" fontSize="20">★</text>
          </g>
        </pattern>
      </defs>
    </svg>
  </div>
);

interface CartItem extends Product {
  quantity: number;
  discount: number; // Percentage discount (0-100)
  discountType: 'percentage' | 'fixed';
  discountReasonCode?: string;
  autoAppliedDeal?: string;
}

interface CartDiscount {
  type: 'percentage' | 'fixed';
  value: number;
  label: string;
  reasonCode?: string;
}

interface MedicalCustomer {
  name: string;
  phone: string;
  medicalCardNumber: string;
  issueDate: string;
  expirationDate: string;
  isPatient: boolean; // true for patient, false for caregiver
  notes: string;
  salesHistory: any[];
}

interface SalePin {
  employeeId: string;
  pin: string;
}

interface DebitTransaction {
  lastFourDigits: string;
}

interface Room {
  id: string;
  name: string;
  type: 'production' | 'storage' | 'processing' | 'sales';
  isActive: boolean;
  maxCapacity?: number;
  currentStock?: number;
}

interface RoomTransfer {
  id: string;
  productId: string;
  productName: string;
  fromRoom: string;
  toRoom: string;
  quantity: number;
  transferDate: string;
  employeeId: string;
  metrcTransferId?: string;
  status: 'pending' | 'completed' | 'cancelled';
  reason: string;
}

interface Deal {
  id: string;
  name: string;
  description: string;
  type: 'percentage' | 'fixed' | 'bogo' | 'bulk';
  discountValue: number;
  categories: string[];
  specificItems: string[];
  startDate: string;
  endDate: string;
  isActive: boolean;
  frequency: 'always' | 'daily' | 'weekly' | 'monthly' | 'custom';
  dayOfWeek?: string;
  dayOfMonth?: number;
  emailCustomers: boolean;
  loyaltyOnly: boolean;
  minimumPurchase?: number;
  maxUses?: number;
  currentUses: number;
}

interface SavedSale {
  id: string;
  name: string;
  saveDate: string;
  employeeId: string;
  employeeName: string;
  customerType: 'rec' | 'medical';
  customerInfo: any;
  cart: CartItem[];
  cartDiscount: CartDiscount | null;
  selectedLoyaltyCustomer: any;
  totalItems: number;
  totalAmount: number;
  notes?: string;
}

// Loyalty customers data
const loyaltyCustomers = [
  {
    id: "1",
    memberId: "LOY001",
    name: "John Doe",
    phone: "(555) 123-4567",
    email: "john.doe@email.com",
    joinDate: "2024-01-15",
    totalSpent: 1250.75,
    totalVisits: 18,
    pointsBalance: 45,
    pointsEarned: 125,
    pointsRedeemed: 80,
    tier: "Silver",
    dataRetentionConsent: true,
    lastVisit: "2024-01-14",
    salesHistory: [
      { id: "p1", date: "2024-01-14", total: 85.50, pointsEarned: 8, items: ["Blue Dream", "Edible Gummies"] },
      { id: "p2", date: "2024-01-10", total: 120.25, pointsEarned: 12, items: ["OG Kush", "Pre-Rolls"] }
    ]
  },
  {
    id: "2",
    memberId: "LOY002",
    name: "Jane Smith",
    phone: "(555) 987-6543",
    email: "jane.smith@email.com",
    joinDate: "2023-11-20",
    totalSpent: 2850.40,
    totalVisits: 42,
    pointsBalance: 156,
    pointsEarned: 285,
    pointsRedeemed: 129,
    tier: "Gold",
    dataRetentionConsent: true,
    lastVisit: "2024-01-13",
    salesHistory: [
      { id: "p3", date: "2024-01-13", total: 95.00, pointsEarned: 9, items: ["Live Resin Cart", "Flower"] },
      { id: "p4", date: "2024-01-08", total: 150.75, pointsEarned: 15, items: ["Premium Flower", "Concentrates"] }
    ]
  },
  {
    id: "3",
    memberId: "LOY003",
    name: "Mike Johnson",
    phone: "(555) 456-7890",
    email: "mike.johnson@email.com",
    joinDate: "2023-08-10",
    totalSpent: 4200.90,
    totalVisits: 68,
    pointsBalance: 328,
    pointsEarned: 420,
    pointsRedeemed: 92,
    tier: "Platinum",
    dataRetentionConsent: true,
    lastVisit: "2024-01-15",
    salesHistory: [
      { id: "p5", date: "2024-01-15", total: 200.50, pointsEarned: 20, items: ["Premium Products", "Accessories"] }
    ]
  }
];

// Sample deals/specials data
const currentDeals: Deal[] = [
  {
    id: "1",
    name: "Daily Flower Special",
    description: "15% off all flower products",
    type: "percentage",
    discountValue: 15,
    categories: ["Flower"],
    specificItems: [],
    startDate: new Date().toISOString().split('T')[0], // Today
    endDate: new Date().toISOString().split('T')[0], // Today
    isActive: true,
    frequency: "daily",
    emailCustomers: true,
    loyaltyOnly: false,
    currentUses: 25
  },
  {
    id: "2",
    name: "Pre-Roll BOGO",
    description: "Buy one pre-roll, get second 50% off",
    type: "bogo",
    discountValue: 50,
    categories: ["Pre-Rolls"],
    specificItems: [],
    startDate: new Date().toISOString().split('T')[0], // Today
    endDate: new Date().toISOString().split('T')[0], // Today
    isActive: true,
    frequency: "daily",
    emailCustomers: false,
    loyaltyOnly: false,
    currentUses: 12
  },
  {
    id: "3",
    name: "Blue Dream Special",
    description: "$2 off Blue Dream today only",
    type: "fixed",
    discountValue: 2,
    categories: [],
    specificItems: ["4"], // Blue Dream product ID
    startDate: new Date().toISOString().split('T')[0], // Today
    endDate: new Date().toISOString().split('T')[0], // Today
    isActive: true,
    frequency: "daily",
    emailCustomers: true,
    loyaltyOnly: false,
    currentUses: 8
  }
];

const sampleProducts: Product[] = [
  // $30/oz Special Flower (approx $1.07/g) - GLS Product
  {
    id: "1", name: "GLS Shake Special", price: 1.07, category: "Flower", image: "/placeholder.svg", stock: 500,
    sku: "SHAKE-30OZ-1G", thc: 12, cbd: 0.1, cbg: 0.3, cbn: 0.1, cbc: 0.2, strain: "Mixed", weight: "1g",
    metrcTag: "1A4000000000022000000123", batchId: "SK240115", harvestDate: "2024-01-10",
    sourceHarvest: "Oregon Budget 2024", supplier: "Value Cannabis Supply", supplierUID: "1A4000000000022000000001", grower: "Budget Buds Farm", vendor: "Discount Cannabis Co", farm: "Budget Buds Farm", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-12", cannabinoids: { thc: 12, cbd: 0.1, cbg: 0.3, cbn: 0.1, cbc: 0.2 }, contaminants: { passed: true } },
    packagedDate: "2024-01-14", expirationDate: "2025-01-14",
    minimumPrice: 0.01, weightThreshold: 0.2, isGLS: true, room: "Sales Floor"
  },
  // $50/oz Special Flower (approx $1.79/g) - GLS Product
  {
    id: "2", name: "Green Leaf Special Outdoor", price: 1.79, category: "Flower", image: "/placeholder.svg", stock: 400,
    sku: "OUT-50OZ-1G", thc: 16, cbd: 0.2, cbg: 0.4, cbn: 0.2, cbc: 0.3, strain: "Sativa", weight: "1g",
    metrcTag: "1A4000000000022000000124", batchId: "OUT240115", harvestDate: "2024-01-08",
    sourceHarvest: "Oregon Outdoor 2024", supplier: "Outdoor Cannabis Supply", supplierUID: "1A4000000000022000000002", grower: "Sunny Fields Farm", vendor: "Outdoor Specialists", farm: "Sunny Fields Farm", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-11", cannabinoids: { thc: 16, cbd: 0.2, cbg: 0.4, cbn: 0.2, cbc: 0.3 }, contaminants: { passed: true } },
    packagedDate: "2024-01-13", expirationDate: "2025-01-13",
    minimumPrice: 0.01, weightThreshold: 0.2, isGLS: true, room: "Sales Floor"
  },
  // $4/g Tier
  {
    id: "3", name: "House Blend", price: 4.00, category: "Flower", image: "/placeholder.svg", stock: 200,
    sku: "HB-4G-1G", thc: 18, cbd: 0.3, cbg: 0.5, cbn: 0.1, cbc: 0.4, strain: "Hybrid", weight: "1g",
    metrcTag: "1A4000000000022000000125", batchId: "HB240115", harvestDate: "2024-01-09",
    sourceHarvest: "Indoor House 2024", supplier: "House Cannabis Supply", grower: "House Cultivation", vendor: "House Brand Co", farm: "House Cultivation", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-12", cannabinoids: { thc: 18, cbd: 0.3, cbg: 0.5, cbn: 0.1, cbc: 0.4 }, contaminants: { passed: true } },
    packagedDate: "2024-01-14", expirationDate: "2025-01-14", room: "Sales Floor"
  },
  // $7/g Tier
  {
    id: "4", name: "Blue Dream", price: 7.00, category: "Flower", image: "/placeholder.svg", stock: 150,
    sku: "BD-7G-1G", thc: 20, cbd: 0.1, cbg: 0.6, cbn: 0.2, cbc: 0.3, strain: "Hybrid", weight: "1g",
    metrcTag: "1A4000000000022000000126", batchId: "BD240115", harvestDate: "2024-01-10",
    sourceHarvest: "Premium Indoor 2024", supplier: "Green Valley Supply", grower: "Emerald Fields Farm", vendor: "Premium Cannabis Co", farm: "Emerald Fields Farm", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-12", cannabinoids: { thc: 20, cbd: 0.1, cbg: 0.6, cbn: 0.2, cbc: 0.3 }, contaminants: { passed: true } },
    packagedDate: "2024-01-14", expirationDate: "2025-01-14", room: "Sales Floor"
  },
  // $12/g Tier
  {
    id: "5", name: "OG Kush", price: 12.00, category: "Flower", image: "/placeholder.svg", stock: 100,
    sku: "OG-12G-1G", thc: 24, cbd: 0.2, cbg: 0.4, cbn: 0.4, cbc: 0.2, strain: "Indica", weight: "1g",
    metrcTag: "1A4000000000022000000127", batchId: "OG240115", harvestDate: "2024-01-08",
    sourceHarvest: "Top Shelf Indoor 2024", supplier: "Pacific Coast Cannabis", grower: "High Grade Gardens", vendor: "West Coast Distributors", farm: "High Grade Gardens", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-11", cannabinoids: { thc: 24, cbd: 0.2, cbg: 0.4, cbn: 0.4, cbc: 0.2 }, contaminants: { passed: true } },
    packagedDate: "2024-01-13", expirationDate: "2025-01-13", room: "Secure Vault"
  },
  // $14/g Tier
  {
    id: "6", name: "Gelato", price: 14.00, category: "Flower", image: "/placeholder.svg", stock: 75,
    sku: "GEL-14G-1G", thc: 26, cbd: 0.1, cbg: 0.7, cbn: 0.3, cbc: 0.4, strain: "Hybrid", weight: "1g",
    metrcTag: "1A4000000000022000000128", batchId: "GEL240115", harvestDate: "2024-01-09",
    sourceHarvest: "Craft Indoor 2024", supplier: "Artisan Cannabis Supply", grower: "Craft Cultivation", vendor: "Artisan Brands", farm: "Craft Cultivation", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-12", cannabinoids: { thc: 26, cbd: 0.1, cbg: 0.7, cbn: 0.3, cbc: 0.4 }, contaminants: { passed: true } },
    packagedDate: "2024-01-14", expirationDate: "2025-01-14", room: "Secure Vault"
  },
  // $16/g Tier
  {
    id: "7", name: "Exotic Zkittlez", price: 16.00, category: "Flower", image: "/placeholder.svg", stock: 50,
    sku: "ZK-16G-1G", thc: 28, cbd: 0.1, cbg: 0.8, cbn: 0.5, cbc: 0.3, strain: "Indica", weight: "1g",
    metrcTag: "1A4000000000022000000129", batchId: "ZK240115", harvestDate: "2024-01-07",
    sourceHarvest: "Exotic Premium 2024", supplier: "Exotic Cannabis Co", grower: "Elite Gardens", vendor: "Exotic Strains Inc", farm: "Elite Gardens", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-10", cannabinoids: { thc: 28, cbd: 0.1, cbg: 0.8, cbn: 0.5, cbc: 0.3 }, contaminants: { passed: true } },
    packagedDate: "2024-01-12", expirationDate: "2025-01-12"
  },
  // Clone Products
  {
    id: "8", name: "Blue Dream Clone", price: 15.00, category: "Clones", image: "/placeholder.svg", stock: 25,
    sku: "BD-CLONE-1CT", thc: 0, cbd: 0, strain: "Hybrid", weight: "1 clone",
    metrcTag: "1A4000000000022000000130", batchId: "BDC240115", harvestDate: "2024-01-15",
    sourceHarvest: "Mother Plant BD-001", supplier: "Clone Masters", grower: "Clone Cultivation Co", vendor: "Clone Supply Inc", farm: "Clone Cultivation Co", administrativeHold: false,
    testResults: { tested: false },
    packagedDate: "2024-01-15", expirationDate: "2024-02-15", room: "Sales Floor"
  },
  {
    id: "9", name: "OG Kush Clone", price: 18.00, category: "Clones", image: "/placeholder.svg", stock: 20,
    sku: "OG-CLONE-1CT", thc: 0, cbd: 0, strain: "Indica", weight: "1 clone",
    metrcTag: "1A4000000000022000000131", batchId: "OGC240115", harvestDate: "2024-01-15",
    sourceHarvest: "Mother Plant OG-002", supplier: "Clone Masters", grower: "Clone Cultivation Co", vendor: "Clone Supply Inc", farm: "Clone Cultivation Co", administrativeHold: false,
    testResults: { tested: false },
    packagedDate: "2024-01-15", expirationDate: "2024-02-15"
  },
  {
    id: "10", name: "Gelato Clone", price: 20.00, category: "Clones", image: "/placeholder.svg", stock: 15,
    sku: "GEL-CLONE-1CT", thc: 0, cbd: 0, strain: "Hybrid", weight: "1 clone",
    metrcTag: "1A4000000000022000000132", batchId: "GLC240115", harvestDate: "2024-01-15",
    sourceHarvest: "Mother Plant GEL-001", supplier: "Clone Masters", grower: "Clone Cultivation Co", vendor: "Clone Supply Inc", farm: "Clone Cultivation Co", administrativeHold: false,
    testResults: { tested: false },
    packagedDate: "2024-01-15", expirationDate: "2024-02-15"
  },
  {
    id: "11", name: "Gummy Bears", price: 25.00, category: "Edibles", image: "/placeholder.svg", stock: 100,
    sku: "GB-001-100MG", thc: 10, cbd: 0, cbg: 0.5, cbn: 0.2, cbc: 0.3,
    thcMg: 100, cbdMg: 0, cbgMg: 5, cbnMg: 2, cbcMg: 3, weight: "100mg",
    metrcTag: "1A4000000000022000000133", batchId: "GB240115", harvestDate: "2024-01-05",
    sourceHarvest: "Extraction Batch A", supplier: "Edible Creations Co", grower: "Source Cannabis Farm", vendor: "Sweet Treats Inc", farm: "Green Valley Farm", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-10", cannabinoids: { thc: 10, cbd: 0, cbg: 0.5, cbn: 0.2, cbc: 0.3 }, contaminants: { passed: true } },
    packagedDate: "2024-01-12", expirationDate: "2025-07-12", room: "Sales Floor"
  },
  {
    id: "12", name: "CBD Tincture", price: 65.00, category: "Tinctures", image: "/placeholder.svg", stock: 50,
    sku: "CT-001-30ML", thc: 2, cbd: 25, weight: "30ml",
    metrcTag: "1A4000000000022000000134", batchId: "CT240115", harvestDate: "2024-01-01",
    sourceHarvest: "CBD Rich Harvest", supplier: "Wellness Products Inc", grower: "Therapeutic Gardens", vendor: "Wellness Co", farm: "Therapeutic Gardens", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-08", cannabinoids: { thc: 2, cbd: 25 }, contaminants: { passed: true } },
    packagedDate: "2024-01-10", expirationDate: "2026-01-10", room: "Sales Floor"
  },
  {
    id: "13", name: "Vape Cartridge", price: 55.00, category: "Vapes", image: "/placeholder.svg", stock: 40,
    sku: "VC-001-1G", thc: 85, cbd: 0, strain: "Hybrid", weight: "1g",
    metrcTag: "1A4000000000022000000135", batchId: "VC240115", harvestDate: "2024-01-03",
    sourceHarvest: "Premium Extract Line", supplier: "Vapor Tech Solutions", grower: "Elite Extraction Co", vendor: "Vape World", farm: "Elite Extraction Co", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-09", cannabinoids: { thc: 85, cbd: 0 }, contaminants: { passed: true } },
    packagedDate: "2024-01-11", expirationDate: "2025-01-11", room: "Sales Floor"
  },
  {
    id: "14", name: "Hash", price: 80.00, category: "Concentrates", image: "/placeholder.svg", stock: 15,
    sku: "HS-001-1G", thc: 60, cbd: 1, weight: "1g",
    metrcTag: "1A4000000000022000000136", batchId: "HS240115", harvestDate: "2024-01-02",
    sourceHarvest: "Artisan Hash Collection", supplier: "Craft Concentrates", grower: "Mountain View Farms", vendor: "Hash Masters", farm: "Mountain View Farms", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-07", cannabinoids: { thc: 60, cbd: 1 }, contaminants: { passed: true } },
    packagedDate: "2024-01-09", expirationDate: "2025-07-09"
  },
  {
    id: "15", name: "Pre-Roll Pack", price: 35.00, category: "Pre-Rolls", image: "/placeholder.svg", stock: 60,
    sku: "PR-001-5PC", thc: 18, cbd: 0.2, strain: "Indica", weight: "5x0.5g",
    metrcTag: "1A4000000000022000000137", batchId: "PR240115", harvestDate: "2024-01-06",
    sourceHarvest: "Premium Indoor Batch", supplier: "Roll Masters", grower: "Indoor Excellence", vendor: "Roll Co", farm: "Indoor Excellence", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-11", cannabinoids: { thc: 18, cbd: 0.2 }, contaminants: { passed: true } },
    packagedDate: "2024-01-13", expirationDate: "2025-01-13", room: "Sales Floor"
  },
  {
    id: "16", name: "Chocolate Bar", price: 30.00, category: "Edibles", image: "/placeholder.svg", stock: 80,
    sku: "CB-001-100MG", thc: 10, cbd: 0, cbg: 0.3, cbn: 0.1, cbc: 0.2,
    thcMg: 100, cbdMg: 0, cbgMg: 3, cbnMg: 1, cbcMg: 2, weight: "100mg",
    metrcTag: "1A4000000000022000000138", batchId: "CB240115", harvestDate: "2024-01-04",
    sourceHarvest: "Cacao Infusion Series", supplier: "Sweet Relief Co", grower: "Organic Source Farm", vendor: "Chocolate Works", farm: "Organic Source Farm", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-09", cannabinoids: { thc: 10, cbd: 0, cbg: 0.3, cbn: 0.1, cbc: 0.2 }, contaminants: { passed: true } },
    packagedDate: "2024-01-11", expirationDate: "2025-07-11"
  },
  {
    id: "17", name: "Rosin", price: 95.00, category: "Concentrates", image: "/placeholder.svg", stock: 12,
    sku: "RS-001-1G", thc: 75, cbd: 2, weight: "1g",
    metrcTag: "1A4000000000022000000139", batchId: "RS240115", harvestDate: "2024-01-01",
    sourceHarvest: "Solventless Premium", supplier: "Pure Extracts LLC", grower: "Artisan Cannabis Co", vendor: "Rosin Kings", farm: "Artisan Cannabis Co", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-06", cannabinoids: { thc: 75, cbd: 2 }, contaminants: { passed: true } },
    packagedDate: "2024-01-08", expirationDate: "2025-07-08"
  },
  {
    id: "18", name: "Infused Blunt", price: 42.00, category: "Infused Pre-Rolls", image: "/placeholder.svg", stock: 35,
    sku: "IB-001-1G", thc: 28, cbd: 0.5, strain: "Hybrid", weight: "1g",
    metrcTag: "1A4000000000022000000140", batchId: "IB240115", harvestDate: "2024-01-05",
    sourceHarvest: "Infused Premium Line", supplier: "Blunt Masters Inc", grower: "Enhanced Cannabis Co", vendor: "Infused Products Co", farm: "Enhanced Cannabis Co", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-10", cannabinoids: { thc: 28, cbd: 0.5 }, contaminants: { passed: true } },
    packagedDate: "2024-01-12", expirationDate: "2025-01-12"
  },
  {
    id: "19", name: "Delta-8 Disposable", price: 38.00, category: "Inhalable Cannabinoids", image: "/placeholder.svg", stock: 25,
    sku: "D8-001-0.5G", thc: 8, cbd: 2, weight: "0.5g",
    metrcTag: "1A4000000000022000000141", batchId: "D8240115", harvestDate: "2024-01-03",
    sourceHarvest: "Cannabinoid Isolation Batch", supplier: "Alternative Cannabinoids", grower: "Research Cultivation", vendor: "Alt Cannabinoids Inc", farm: "Research Cultivation", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-08", cannabinoids: { thc: 8, cbd: 2 }, contaminants: { passed: true } },
    packagedDate: "2024-01-10", expirationDate: "2025-01-10"
  },
  {
    id: "20", name: "CBD Topical Balm", price: 45.00, category: "Topicals", image: "/placeholder.svg", stock: 40,
    sku: "TB-001-50ML", thc: 0, cbd: 200, weight: "50ml",
    metrcTag: "1A4000000000022000000142", batchId: "TB240115", harvestDate: "2024-01-02",
    sourceHarvest: "CBD Topical Series", supplier: "Skin Care Solutions", grower: "Therapeutic Hemp Farm", vendor: "Topical Solutions Inc", farm: "Therapeutic Hemp Farm", administrativeHold: false,
    testResults: { tested: true, labName: "Oregon Cannabis Lab", testDate: "2024-01-07", cannabinoids: { thc: 0, cbd: 200 }, contaminants: { passed: true } },
    packagedDate: "2024-01-09", expirationDate: "2025-07-09"
  },
  {
    id: "21", name: "Hemp Rolling Papers", price: 8.00, category: "Hemp", image: "/placeholder.svg", stock: 200,
    sku: "HP-001-32CT", weight: "32 papers", isUntaxed: true,
    metrcTag: "1A4000000000022000000143", batchId: "RP240115",
    sourceHarvest: "Industrial Hemp", supplier: "Paper Products Co", grower: "Hemp Industrial Farm", vendor: "Hemp Supplies Inc", farm: "Hemp Industrial Farm", administrativeHold: false,
    testResults: { tested: false },
    packagedDate: "2024-01-01", expirationDate: "2026-01-01"
  },
  {
    id: "22", name: "Glass Pipe", price: 25.00, category: "Paraphernalia", image: "/placeholder.svg", stock: 50,
    sku: "GP-001-SM", weight: "3oz", isUntaxed: true,
    metrcTag: "1A4000000000022000000144", batchId: "GP240115",
    sourceHarvest: "N/A", supplier: "Glass Art Accessories", grower: "N/A", vendor: "Smoke Shop Supply", farm: "N/A", administrativeHold: false,
    testResults: { tested: false },
    packagedDate: "2024-01-01", expirationDate: "2030-01-01"
  },
  {
    id: "23", name: "Grinder", price: 15.00, category: "Accessories", image: "/placeholder.svg", stock: 75,
    sku: "GR-001-4PC", weight: "2oz", isUntaxed: true,
    metrcTag: "1A4000000000022000000145", batchId: "GR240115",
    sourceHarvest: "N/A", supplier: "Metal Works Co", grower: "N/A", vendor: "Accessory World", farm: "N/A", administrativeHold: false,
    testResults: { tested: false },
    packagedDate: "2024-01-01", expirationDate: "2030-01-01"
  },
  {
    id: "24", name: "Hemp Seed Oil", price: 12.00, category: "Hemp", image: "/placeholder.svg", stock: 100,
    sku: "HSO-001-1OZ", weight: "1oz", isUntaxed: true,
    metrcTag: "1A4000000000022000000146", batchId: "HSO240115",
    sourceHarvest: "Organic Hemp Seeds", supplier: "Natural Hemp Co", grower: "Organic Hemp Farm", vendor: "Health Products Inc", farm: "Organic Hemp Farm", administrativeHold: false,
    testResults: { tested: false },
    packagedDate: "2024-01-01", expirationDate: "2025-12-01", room: "Sales Floor"
  },
];

const categories = ["All", "Flower", "Clones", "Edibles", "Vapes", "Concentrates", "Pre-Rolls", "Infused Pre-Rolls", "Tinctures", "Inhalable Cannabinoids", "Topicals", "Hemp", "Paraphernalia", "Accessories"];

// Sample rooms data
const availableRooms: Room[] = [
  { id: "sales-floor", name: "Sales Floor", type: "sales", isActive: true, maxCapacity: 1000, currentStock: 750 },
  { id: "storage-main", name: "Main Storage", type: "storage", isActive: true, maxCapacity: 5000, currentStock: 3200 },
  { id: "production-1", name: "Production Room 1", type: "production", isActive: true, maxCapacity: 2000, currentStock: 850 },
  { id: "processing-lab", name: "Processing Lab", type: "processing", isActive: true, maxCapacity: 500, currentStock: 200 },
  { id: "vault-secure", name: "Secure Vault", type: "storage", isActive: true, maxCapacity: 1000, currentStock: 450 },
  { id: "quarantine", name: "Quarantine Room", type: "storage", isActive: true, maxCapacity: 200, currentStock: 15 }
];

export default function Index() {
  const navigate = useNavigate();
  const [cart, setCart] = useState<CartItem[]>([]);
  const [selectedCategory, setSelectedCategory] = useState("All");
  const [searchQuery, setSearchQuery] = useState("");
  const [sortBy, setSortBy] = useState<'name' | 'price' | 'category' | 'thc' | 'room'>('name');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');
  const [cartDiscount, setCartDiscount] = useState<CartDiscount | null>(null);
  const [showDiscountDialog, setShowDiscountDialog] = useState(false);
  const [showCartDiscountDialog, setShowCartDiscountDialog] = useState(false);
  const [selectedItemForDiscount, setSelectedItemForDiscount] = useState<string | null>(null);
  const [discountValue, setDiscountValue] = useState("");
  const [discountType, setDiscountType] = useState<'percentage' | 'fixed'>('percentage');
  const [showCustomerDialog, setShowCustomerDialog] = useState(false);
  const [showMetrcDialog, setShowMetrcDialog] = useState(false);
  const [selectedProductForMetrc, setSelectedProductForMetrc] = useState<Product | null>(null);
  const [showEnhancedMetrcDialog, setShowEnhancedMetrcDialog] = useState(false);
  const [selectedProductForEnhancedMetrc, setSelectedProductForEnhancedMetrc] = useState<Product | null>(null);
  const [editingQuantity, setEditingQuantity] = useState<string | null>(null);
  const [quantityInput, setQuantityInput] = useState("");
  const [discountReasonCode, setDiscountReasonCode] = useState("");
  const [customerType, setCustomerType] = useState<'rec' | 'medical'>('rec');
  const [medicalCustomer, setMedicalCustomer] = useState<MedicalCustomer>({
    name: "",
    phone: "",
    medicalCardNumber: "",
    issueDate: "",
    expirationDate: "",
    isPatient: true,
    notes: "",
    salesHistory: []
  });
  const [showMedicalDialog, setShowMedicalDialog] = useState(false);
  const [showPinDialog, setShowPinDialog] = useState(false);
  const [employeePin, setEmployeePin] = useState("");
  const [showDebitDialog, setShowDebitDialog] = useState(false);
  const [debitLastFour, setDebitLastFour] = useState("");
  const [showBarcodeDialog, setShowBarcodeDialog] = useState(false);
  const [showExitLabelDialog, setShowExitLabelDialog] = useState(false);
  const [selectedProductForPrint, setSelectedProductForPrint] = useState<Product | null>(null);
  const [showPaymentAmountDialog, setShowPaymentAmountDialog] = useState(false);
  const [paymentAmount, setPaymentAmount] = useState("");
  const [paymentMethod, setPaymentMethod] = useState<'cash' | 'debit'>('cash');
  const [showReceiptDialog, setShowReceiptDialog] = useState(false);
  const [lastTransaction, setLastTransaction] = useState<any>(null);
  const [showCustomerLookup, setShowCustomerLookup] = useState(false);
  const [customerSearchQuery, setCustomerSearchQuery] = useState("");
  const [selectedLoyaltyCustomer, setSelectedLoyaltyCustomer] = useState<any>(null);

  // New Sale Dialog State
  const [showNewSaleDialog, setShowNewSaleDialog] = useState(false);
  const [newSaleCustomerType, setNewSaleCustomerType] = useState<"recreational" | "medical" | "">("");
  const [medicalCardInfo, setMedicalCardInfo] = useState({
    number: "",
    issueDate: "",
    expirationDate: ""
  });
  const [caregiverCardInfo, setCaregiverCardInfo] = useState({
    number: "",
    issueDate: "",
    expirationDate: "",
    patientName: ""
  });
  const [dataRetentionConsent, setDataRetentionConsent] = useState(false);

  // Sale workflow state - require New Sale button to be clicked before adding items
  const [saleStarted, setSaleStarted] = useState(false);
  const [queueOrder, setQueueOrder] = useState<any>(null);
  const [showQueueOrderDialog, setShowQueueOrderDialog] = useState(false);
  const [showRoomTransferDialog, setShowRoomTransferDialog] = useState(false);
  const [selectedProductForTransfer, setSelectedProductForTransfer] = useState<Product | null>(null);
  const [selectedProductForEdit, setSelectedProductForEdit] = useState<Product | null>(null);
  const [showEditProductDialog, setShowEditProductDialog] = useState(false);
  const [transferQuantity, setTransferQuantity] = useState("");
  const [selectedFromRoom, setSelectedFromRoom] = useState("");
  const [selectedToRoom, setSelectedToRoom] = useState("");
  const [transferReason, setTransferReason] = useState("");

  // Saved Sales functionality
  const [savedSales, setSavedSales] = useState<SavedSale[]>([]);
  const [showSavedSalesDialog, setShowSavedSalesDialog] = useState(false);
  const [showSaveSaleDialog, setShowSaveSaleDialog] = useState(false);
  const [saleNameToSave, setSaleNameToSave] = useState("");
  const [saleNotesToSave, setSaleNotesToSave] = useState("");
  const [showInventoryTab, setShowInventoryTab] = useState(false);
  const [showNavigationDropdown, setShowNavigationDropdown] = useState(false);

  // Cashier view mode - connected to Settings page
  const [cashierViewMode, setCashierViewMode] = useState<'cards' | 'list'>(() => {
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

  // Listen for changes to the settings for view mode synchronization
  useEffect(() => {
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === 'cannabest-store-settings' && e.newValue) {
        try {
          const settings = JSON.parse(e.newValue);
          console.log('Index: Storage change detected, updating cashier view mode to:', settings.inventoryViewMode);
          setCashierViewMode(settings.inventoryViewMode || 'cards');
        } catch (error) {
          console.warn('Could not parse settings from localStorage:', error);
        }
      }
    };

    // Listen for custom event for same-page updates
    const handleSettingsUpdate = (e: CustomEvent) => {
      console.log('Index: Settings update event received:', e.detail);
      if (e.detail?.inventoryViewMode) {
        setCashierViewMode(e.detail.inventoryViewMode);
      }
    };

    const handleInventoryViewChange = (e: CustomEvent) => {
      console.log('Index: Inventory view change event received:', e.detail);
      if (e.detail?.viewMode) {
        setCashierViewMode(e.detail.viewMode);
      }
    };

    window.addEventListener('storage', handleStorageChange);
    window.addEventListener('settings-updated', handleSettingsUpdate as EventListener);
    window.addEventListener('inventory-view-changed', handleInventoryViewChange as EventListener);

    // Check localStorage periodically for any missed updates
    const checkSettingsInterval = setInterval(() => {
      try {
        const savedSettings = localStorage.getItem('cannabest-store-settings');
        if (savedSettings) {
          const settings = JSON.parse(savedSettings);
          const currentViewMode = settings.inventoryViewMode || 'cards';
          if (currentViewMode !== cashierViewMode) {
            console.log('Index: Periodic check found view mode change:', currentViewMode);
            setCashierViewMode(currentViewMode);
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
  }, [cashierViewMode]);

  // Customizable tax rate (default 20% for Oregon)
  const [taxRate, setTaxRate] = useState(0.20);
  const [showTaxRateDialog, setShowTaxRateDialog] = useState(false);
  const [customerInfo, setCustomerInfo] = useState({
    name: "",
    phone: "",
    medicalCard: "",
    caregiverCard: "",
    isVerified: false,
    isOregonResident: true,
    dailyPurchases: {
      flower: 0, // grams
      concentrates: 0, // grams
      edibles: 0, // grams
      tinctures: 0, // ounces
      inhalableCannabinoidsExtracts: 0, // grams
      topicals: 0, // grams
      infusedPreRolls: 0, // grams
      clones: 0 // units
    }
  });

  // Oregon daily possession limits
  const oregonLimits = {
    flower: 56700, // 56.7g (in mg for consistency)
    concentrates: 10000, // 10g (in mg)
    edibles: 454000, // 454g (in mg)
    tinctures: 72000, // 72oz (in ml for consistency, assuming 1oz = 1000ml)
    inhalableCannabinoidsExtracts: 10000, // 10g (in mg)
    topicals: 454000, // 454g (in mg) - no specific limit, using general product limit
    clones: 4 // 4 clones max per day for recreational customers
  };

  const filteredProducts = sampleProducts
    .filter(product => {
      const searchLower = searchQuery.toLowerCase();
      const matchesSearch = product.name.toLowerCase().includes(searchLower) ||
                           product.farm?.toLowerCase().includes(searchLower) ||
                           product.supplier?.toLowerCase().includes(searchLower) ||
                           product.vendor?.toLowerCase().includes(searchLower) ||
                           product.grower?.toLowerCase().includes(searchLower) ||
                           product.metrcTag?.toLowerCase().includes(searchLower) ||
                           product.sku?.toLowerCase().includes(searchLower) ||
                           product.batchId?.toLowerCase().includes(searchLower);

      if (showInventoryTab) {
        // Show items NOT on sales floor when inventory tab is active
        const isNotOnSalesFloor = product.room !== "Sales Floor" && product.room;
        const matchesCategory = selectedCategory === "All" || product.category === selectedCategory;
        return matchesSearch && isNotOnSalesFloor && (selectedCategory === "All" || matchesCategory);
      } else {
        // Show ALL items in cashier mode (both sales floor and non-sales floor)
        const matchesCategory = selectedCategory === "All" || product.category === selectedCategory;
        return matchesCategory && matchesSearch;
      }
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
        case 'category':
          valueA = a.category.toLowerCase();
          valueB = b.category.toLowerCase();
          break;
        case 'thc':
          valueA = a.thc || 0;
          valueB = b.thc || 0;
          break;
        case 'room':
          valueA = (a.room || '').toLowerCase();
          valueB = (b.room || '').toLowerCase();
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

  // Deal Application Functions
  const checkApplicableDeals = (productId: string): Deal[] => {
    const product = sampleProducts.find(p => p.id === productId);
    if (!product) return [];

    const today = new Date().toISOString().split('T')[0];
    const dayOfWeek = new Date().toLocaleDateString('en-US', { weekday: 'long' });

    return currentDeals.filter(deal => {
      // Must be active
      if (!deal.isActive) return false;

      // Skip if GLS product and this is an automatic deal
      if (product.isGLS) return false;

      // Check date range
      if (deal.startDate > today || deal.endDate < today) return false;

      // Check frequency
      if (deal.frequency === 'weekly' && deal.dayOfWeek !== dayOfWeek) return false;
      if (deal.frequency === 'monthly' && deal.dayOfMonth !== new Date().getDate()) return false;

      // Check loyalty requirement
      if (deal.loyaltyOnly && !selectedLoyaltyCustomer) return false;

      // Check if item matches deal criteria
      const matchesCategory = deal.categories.length === 0 || deal.categories.includes(product.category);
      const matchesSpecificItem = deal.specificItems.length === 0 || deal.specificItems.includes(product.id);

      return matchesCategory || matchesSpecificItem;
    });
  };

  const applyAutomaticDeal = (item: CartItem, deals: Deal[]): CartItem => {
    if (deals.length === 0) return item;

    // Apply the best deal (highest discount value)
    const bestDeal = deals.reduce((best, current) => {
      const bestValue = best.type === 'percentage' ? best.discountValue : (best.discountValue / item.price) * 100;
      const currentValue = current.type === 'percentage' ? current.discountValue : (current.discountValue / item.price) * 100;
      return currentValue > bestValue ? current : best;
    });

    return {
      ...item,
      discount: bestDeal.discountValue,
      discountType: bestDeal.type === 'percentage' ? 'percentage' : 'fixed',
      discountReasonCode: `AUTO-${bestDeal.id}`,
      autoAppliedDeal: bestDeal.name
    };
  };

  const createRoomTransfer = () => {
    if (!selectedProductForTransfer || !transferQuantity || !selectedFromRoom || !selectedToRoom) {
      alert("Please fill in all required fields for the room transfer.");
      return;
    }

    const quantity = parseFloat(transferQuantity);
    if (quantity <= 0) {
      alert("Transfer quantity must be greater than 0.");
      return;
    }

    if (selectedFromRoom === selectedToRoom) {
      alert("Source and destination rooms must be different.");
      return;
    }

    const transfer: RoomTransfer = {
      id: Date.now().toString(),
      productId: selectedProductForTransfer.id,
      productName: selectedProductForTransfer.name,
      fromRoom: selectedFromRoom,
      toRoom: selectedToRoom,
      quantity: quantity,
      transferDate: new Date().toISOString(),
      employeeId: "current-employee", // In real app, get from auth
      metrcTransferId: `MTR${Date.now()}`,
      status: "completed",
      reason: transferReason
    };

    // Here you would normally send to your backend/API
    console.log("Room transfer created:", transfer);

    alert(`Room transfer completed successfully!

Product: ${selectedProductForTransfer.name}
Quantity: ${quantity}
From: ${availableRooms.find(r => r.id === selectedFromRoom)?.name}
To: ${availableRooms.find(r => r.id === selectedToRoom)?.name}
Metrc Transfer ID: ${transfer.metrcTransferId}`);

    // Reset form
    setShowRoomTransferDialog(false);
    setSelectedProductForTransfer(null);
    setTransferQuantity("");
    setSelectedFromRoom("");
    setSelectedToRoom("");
    setTransferReason("");
  };

  const updateProduct = (updatedProduct: Product) => {
    setProducts(prev => prev.map(product =>
      product.id === updatedProduct.id ? updatedProduct : product
    ));
    setShowEditProductDialog(false);
    setSelectedProductForEdit(null);
    alert(`Product "${updatedProduct.name}" has been updated successfully!`);
  };

  // New Sale Dialog Functions
  const handleNewSale = () => {
    // Clear current cart and customer info
    setCart([]);
    setCustomerInfo({
      name: "",
      phone: "",
      medicalCard: "",
      caregiverCard: "",
      isVerified: false,
      isOregonResident: true,
      dailyPurchases: {
        flower: 0,
        concentrates: 0,
        edibles: 0,
        tinctures: 0,
        inhalableCannabinoidsExtracts: 0,
        topicals: 0,
        infusedPreRolls: 0,
        clones: 0
      }
    });
    setSelectedLoyaltyCustomer(null);
    // Reset sale started state
    setSaleStarted(false);
    setShowNewSaleDialog(true);
  };

  const handleCustomerTypeSubmit = () => {
    if (newSaleCustomerType === "recreational") {
      if (!customerInfo.isVerified) {
        alert("Customer ID verification is required for recreational sales");
        return;
      }
    }

    if (newSaleCustomerType === "medical") {
      if (!medicalCardInfo.number || !medicalCardInfo.issueDate || !medicalCardInfo.expirationDate) {
        alert("Please fill in all medical card information");
        return;
      }
      if (!dataRetentionConsent) {
        alert("Data retention consent is required for medical patients");
        return;
      }
    }

    // Set customer info based on type
    if (newSaleCustomerType === "medical") {
      // Check if medical customer exists in loyalty program
      const existingLoyaltyCustomer = loyaltyCustomers.find(customer =>
        customer.phone === customerInfo.phone ||
        customer.email === customerInfo.email ||
        customer.name.toLowerCase().includes(medicalCardInfo.number.toLowerCase())
      );

      if (existingLoyaltyCustomer) {
        setSelectedLoyaltyCustomer(existingLoyaltyCustomer);
        alert(`Medical patient found in loyalty program! ${existingLoyaltyCustomer.name} - ${existingLoyaltyCustomer.tier} tier with ${existingLoyaltyCustomer.pointsBalance} points.`);
      }

      setCustomerInfo(prev => ({
        ...prev,
        medicalCard: medicalCardInfo.number,
        isVerified: true // Assume verified for medical patients
      }));
    }

    // Reset dialog state and mark sale as started
    setNewSaleCustomerType("");
    setMedicalCardInfo({ number: "", issueDate: "", expirationDate: "" });
    setCaregiverCardInfo({ number: "", issueDate: "", expirationDate: "", patientName: "" });
    setDataRetentionConsent(false);
    setShowNewSaleDialog(false);
    setSaleStarted(true);
  };

  // Saved Sales Functions
  const saveSaleForLater = () => {
    if (cart.length === 0) {
      alert("Cannot save an empty sale. Please add items to the cart first.");
      return;
    }
    setShowSaveSaleDialog(true);
  };

  const confirmSaveSale = () => {
    if (!saleNameToSave.trim()) {
      alert("Please enter a name for this saved sale.");
      return;
    }

    const totalAmount = cart.reduce((sum, item) => {
      const itemTotal = item.price * item.quantity;
      const discountAmount = item.discountType === 'percentage'
        ? itemTotal * (item.discount / 100)
        : item.discount;
      return sum + itemTotal - discountAmount;
    }, 0);

    const savedSale: SavedSale = {
      id: Date.now().toString(),
      name: saleNameToSave.trim(),
      saveDate: new Date().toISOString(),
      employeeId: "current-employee", // In real app, get from auth
      employeeName: "Current Employee", // In real app, get from auth
      customerType: customerType,
      customerInfo: { ...customerInfo },
      cart: [...cart],
      cartDiscount: cartDiscount,
      selectedLoyaltyCustomer: selectedLoyaltyCustomer,
      totalItems: cart.reduce((sum, item) => sum + item.quantity, 0),
      totalAmount: totalAmount,
      notes: saleNotesToSave.trim()
    };

    setSavedSales(prev => [...prev, savedSale]);

    // Clear current sale
    setCart([]);
    setCustomerInfo({
      name: "",
      phone: "",
      medicalCard: "",
      caregiverCard: "",
      isVerified: false,
      isOregonResident: true,
      dailyPurchases: {
        flower: 0,
        concentrates: 0,
        edibles: 0,
        tinctures: 0,
        inhalableCannabinoidsExtracts: 0,
        topicals: 0,
        infusedPreRolls: 0,
        clones: 0
      }
    });
    setSelectedLoyaltyCustomer(null);
    setCartDiscount(null);
    setSaleStarted(false);

    // Reset dialog
    setShowSaveSaleDialog(false);
    setSaleNameToSave("");
    setSaleNotesToSave("");

    alert(`Sale "${savedSale.name}" has been saved for later!`);
  };

  const loadSavedSale = (savedSale: SavedSale) => {
    // Clear current sale first
    setCart([]);
    setCustomerInfo({
      name: "",
      phone: "",
      medicalCard: "",
      caregiverCard: "",
      isVerified: false,
      isOregonResident: true,
      dailyPurchases: {
        flower: 0,
        concentrates: 0,
        edibles: 0,
        tinctures: 0,
        inhalableCannabinoidsExtracts: 0,
        topicals: 0,
        infusedPreRolls: 0,
        clones: 0
      }
    });
    setSelectedLoyaltyCustomer(null);
    setCartDiscount(null);

    // Load saved sale data
    setCustomerType(savedSale.customerType);
    setCustomerInfo(savedSale.customerInfo);
    setCart(savedSale.cart);
    setCartDiscount(savedSale.cartDiscount);
    setSelectedLoyaltyCustomer(savedSale.selectedLoyaltyCustomer);
    setSaleStarted(true);

    setShowSavedSalesDialog(false);
    alert(`Loaded saved sale: "${savedSale.name}"`);
  };

  const deleteSavedSale = (saleId: string) => {
    const sale = savedSales.find(s => s.id === saleId);
    if (sale && confirm(`Are you sure you want to delete the saved sale "${sale.name}"?`)) {
      setSavedSales(prev => prev.filter(s => s.id !== saleId));
      alert(`Saved sale "${sale.name}" has been deleted.`);
    }
  };

  const addToCart = (product: Product) => {
    // Require New Sale button to be clicked before adding items
    if (!saleStarted) {
      alert("Please click 'New Sale' to start a transaction before adding items to cart.");
      return;
    }

    // Check if item is on sales floor - restrict cart addition for non-sales floor items
    if (product.room && product.room !== "Sales Floor") {
      if (showInventoryTab) {
        // If adding from inventory tab, show room transfer warning
        if (!confirm(`This item is currently in ${product.room}. Adding it to the cart will require a room transfer to Sales Floor. Continue?`)) {
          return;
        }
      } else {
        // In regular cashier mode, don't allow adding non-sales floor items
        alert(`This item cannot be added to cart as it is currently stored in ${product.room}. Only items on the Sales Floor are available for sale. Transfer this item to Sales Floor first or use the Inventory tab to transfer and add simultaneously.`);
        return;
      }
    }

    // Check limits if customer is verified
    if (customerInfo.isVerified) {
      const violations = wouldExceedLimits(product);
      if (Object.values(violations).some(v => v)) {
        alert("Adding this item would exceed Oregon possession limits.");
        return;
      }
    }

    // Add directly to cart without confirmation dialog
    confirmAddToCart(product);
  };

  const confirmAddToCart = (product: Product) => {
    setCart(prev => {
      const existingItem = prev.find(item => item.id === product.id);
      if (existingItem) {
        // For existing items, check if BOGO deals apply with increased quantity
        const applicableDeals = checkApplicableDeals(product.id);
        const bogoDeals = applicableDeals.filter(deal => deal.type === 'bogo');

        const updatedItem = {
          ...existingItem,
          quantity: existingItem.quantity + 1
        };

        // Apply BOGO if quantity >= 2 and not already applied
        if (bogoDeals.length > 0 && updatedItem.quantity >= 2 && !existingItem.autoAppliedDeal) {
          const bestBogo = bogoDeals[0]; // Take first BOGO deal
          return prev.map(item =>
            item.id === product.id
              ? {
                  ...updatedItem,
                  discount: bestBogo.discountValue,
                  discountType: 'percentage',
                  discountReasonCode: `AUTO-${bestBogo.id}`,
                  autoAppliedDeal: bestBogo.name
                }
              : item
          );
        }

        return prev.map(item =>
          item.id === product.id ? updatedItem : item
        );
      }

      // Check for applicable deals and auto-apply them
      const applicableDeals = checkApplicableDeals(product.id);
      const newItem: CartItem = {
        ...product,
        quantity: 1,
        discount: 0,
        discountType: 'percentage'
      };

      // Auto-apply the best deal if any are found (but not BOGO for single items)
      const nonBogoDeals = applicableDeals.filter(deal => deal.type !== 'bogo');
      const itemWithDeal = nonBogoDeals.length > 0
        ? applyAutomaticDeal(newItem, nonBogoDeals)
        : newItem;

      // Show notification if deal was applied
      if (itemWithDeal.autoAppliedDeal) {
        setTimeout(() => {
          // Using alert for now - could be replaced with toast notification
          console.log(`🎉 Auto-applied deal: ${itemWithDeal.autoAppliedDeal} to ${product.name}`);
        }, 100);
      }

      return [...prev, itemWithDeal];
    });
    setShowMetrcDialog(false);
    setSelectedProductForMetrc(null);
  };

  const updateQuantity = (id: string, change: number) => {
    setCart(prev => {
      return prev.map(item => {
        if (item.id === id) {
          let stepSize = 1;
          let minQuantity = 1;

          if (item.category === "Flower") {
            stepSize = 0.01; // 0.01g increments for flower
            minQuantity = 0.01;
          }

          const newQuantity = Math.round((item.quantity + (change * stepSize)) * 100) / 100;
          return newQuantity >= minQuantity ? { ...item, quantity: newQuantity } : item;
        }
        return item;
      }).filter(item => item.quantity >= (item.category === "Flower" ? 0.01 : 1));
    });
  };

  const setDirectQuantity = (id: string, quantity: number) => {
    const cartItem = cart.find(item => item.id === id);
    const minQuantity = cartItem?.category === "Flower" ? 0.01 : 1;

    if (quantity < minQuantity) {
      removeFromCart(id);
      return;
    }

    setCart(prev => {
      return prev.map(item => {
        if (item.id === id) {
          return { ...item, quantity };
        }
        return item;
      });
    });
  };

  const handleQuantityEdit = (item: CartItem) => {
    setEditingQuantity(item.id);
    setQuantityInput(item.category === "Flower"
      ? item.quantity.toFixed(2)
      : item.quantity.toString()
    );
  };

  const saveQuantityEdit = (id: string) => {
    const cartItem = cart.find(item => item.id === id);
    let newQuantity: number;

    if (cartItem?.category === "Flower") {
      // Allow decimal values for flower products, round to hundredth
      newQuantity = Math.round(parseFloat(quantityInput) * 100) / 100 || 0.01;
      newQuantity = Math.max(0.01, newQuantity); // Minimum of 0.01g
    } else {
      // Integer values for other products
      newQuantity = parseInt(quantityInput) || 1;
    }

    setDirectQuantity(id, newQuantity);
    setEditingQuantity(null);
    setQuantityInput("");
  };

  const removeFromCart = (id: string) => {
    setCart(prev => prev.filter(item => item.id !== id));
  };

  const applyItemDiscount = (itemId: string, discount: number, type: 'percentage' | 'fixed', reasonCode: string) => {
    setCart(prev => prev.map(item =>
      item.id === itemId
        ? { ...item, discount, discountType: type, discountReasonCode: reasonCode }
        : item
    ));
  };

  const removeItemDiscount = (itemId: string) => {
    setCart(prev => prev.map(item =>
      item.id === itemId
        ? { ...item, discount: 0, discountType: 'percentage', discountReasonCode: undefined, autoAppliedDeal: undefined }
        : item
    ));
  };

  const applyCartDiscount = () => {
    const value = parseFloat(discountValue);
    if (value > 0 && discountReasonCode.trim()) {
      setCartDiscount({
        type: discountType,
        value: value,
        label: discountType === 'percentage' ? `${value}% off` : `$${value.toFixed(2)} off`,
        reasonCode: discountReasonCode
      });
      setShowCartDiscountDialog(false);
      setDiscountValue("");
      setDiscountReasonCode("");
    }
  };

  const removeCartDiscount = () => {
    setCartDiscount(null);
  };

  const printBarcode = (product: Product) => {
    const barcodeWindow = window.open('', '_blank');
    if (barcodeWindow) {
      const metrcLast5 = product.metrcTag ? product.metrcTag.slice(-5) : 'N/A';
      const qrData = `${product.name}|${metrcLast5}|$${product.price.toFixed(2)}`;

      barcodeWindow.document.write(`
        <html>
          <head>
            <title>QR Code - ${product.name}</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 0; padding: 10px; }
              .barcode-container {
                width: 1.5in;
                height: 0.5in;
                border: 1px solid black;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                page-break-after: always;
                padding: 2px;
              }
              .qr-code {
                width: 30px;
                height: 30px;
                background: #000;
                margin: 2px auto;
                position: relative;
              }
              .qr-code::before {
                content: '';
                position: absolute;
                width: 100%;
                height: 100%;
                background-image:
                  linear-gradient(45deg, transparent 49%, #fff 49%, #fff 51%, transparent 51%),
                  linear-gradient(-45deg, transparent 49%, #fff 49%, #fff 51%, transparent 51%);
                background-size: 4px 4px;
              }
              .product-name { font-size: 8px; font-weight: bold; margin: 1px 0; }
              .metrc-info { font-size: 6px; margin: 1px 0; }
              .price { font-size: 8px; font-weight: bold; margin: 1px 0; }
            </style>
            <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
          </head>
          <body>
            <div class="barcode-container">
              <div class="product-name">${product.name}</div>
              <canvas id="qrcode" width="30" height="30"></canvas>
              <div class="metrc-info">METRC: ${metrcLast5}</div>
              <div class="price">$${product.price.toFixed(2)}</div>
            </div>
            <script>
              const canvas = document.getElementById('qrcode');
              QRCode.toCanvas(canvas, '${qrData}', { width: 30, margin: 1 }, function (error) {
                if (error) {
                  console.error(error);
                  // Fallback to simple pattern if QR code library fails
                  const ctx = canvas.getContext('2d');
                  ctx.fillStyle = '#000';
                  for(let i = 0; i < 30; i += 3) {
                    for(let j = 0; j < 30; j += 3) {
                      if((i + j) % 6 === 0) ctx.fillRect(i, j, 2, 2);
                    }
                  }
                }
                window.print();
              });
            </script>
          </body>
        </html>
      `);
      barcodeWindow.document.close();
    }
  };

  const printExitLabel = (product: Product) => {
    const exitLabelWindow = window.open('', '_blank');
    if (exitLabelWindow) {
      exitLabelWindow.document.write(`
        <html>
          <head>
            <title>Oregon OLCC Exit Label - ${product.name}</title>
            <style>
              body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 5px;
                font-size: 12px;
              }
              .exit-label {
                border: 2px solid black;
                padding: 4px;
                width: 0.5in;
                height: 0.4in;
                page-break-after: always;
                font-size: 12px;
                line-height: 1.1;
              }
              .header {
                font-weight: bold;
                text-align: center;
                font-size: 10px;
                margin-bottom: 2px;
              }
              .field { margin: 1px 0; font-size: 8px; }
              .field-label { font-weight: bold; }
              .warning {
                background: yellow;
                border: 1px solid red;
                padding: 1px;
                margin: 2px 0;
                text-align: center;
                font-weight: bold;
                font-size: 6px;
                line-height: 1;
              }
              .metrc { font-family: monospace; font-size: 8px; }
              .marijuana-symbol {
                width: 0.1in;
                height: 0.1in;
                background: #000;
                border-radius: 50%;
                display: inline-block;
                margin: 1px;
                position: relative;
              }
              .marijuana-symbol::before {
                content: '����';
                position: absolute;
                top: -2px;
                left: -1px;
                font-size: 8px;
                color: #fff;
              }
            </style>
          </head>
          <body>
            <div class="exit-label">
              <div class="header">
                <span class="marijuana-symbol"></span>
                OREGON OLCC EXIT LABEL
              </div>

              <div class="field">
                <span class="field-label">Product:</span> ${product.name}
              </div>

              <div class="field">
                <span class="field-label">Category:</span> ${product.category}
              </div>

              <div class="field">
                <span class="field-label">Weight:</span> ${product.weight}
              </div>

              ${product.thc ? `<div class="field"><span class="field-label">THC:</span> ${product.thc}%</div>` : ''}
              ${product.cbd ? `<div class="field"><span class="field-label">CBD:</span> ${product.cbd}%</div>` : ''}

              <div class="field">
                <span class="field-label">Harvest:</span> ${product.harvestDate}
              </div>

              <div class="field">
                <span class="field-label">Lab:</span> ${product.testResults?.labName || 'N/A'}
              </div>

              <div class="field">
                <span class="field-label">Tested:</span> ${product.testResults?.testDate || 'N/A'}
              </div>

              <div class="field">
                <span class="field-label">Packaged:</span> ${product.packagedDate}
              </div>

              <div class="field">
                <span class="field-label">Supplier:</span> ${product.supplier || 'N/A'}
              </div>

              <div class="field">
                <span class="field-label">Supplier UID:</span> ${product.supplierUID || 'N/A'}
              </div>

              <div class="field metrc">
                <span class="field-label">METRC:</span> ${product.metrcTag}
              </div>

              <div class="warning">
                DO NOT EAT. For use by adults 21 and older. Keep out of reach of children. It is illegal to drive a motor vehicle while under the influence of marijuana.
              </div>
            </div>
          </body>
        </html>
      `);
      exitLabelWindow.document.close();
      exitLabelWindow.print();
      exitLabelWindow.close();
    }
  };

  // Calculate current usage from cart
  const calculateCurrentUsage = () => {
    return cart.reduce((usage, item) => {
      const weightInMg = parseWeight(item.weight);

      if (item.category === "Flower" || item.category === "Pre-Rolls") {
        usage.flower += weightInMg * item.quantity;
      } else if (item.category === "Concentrates") {
        usage.concentrates += weightInMg * item.quantity;
      } else if (item.category === "Vapes") {
        usage.inhalableCannabinoidsExtracts += weightInMg * item.quantity;
      } else if (item.category === "Edibles") {
        usage.edibles += weightInMg * item.quantity;
      } else if (item.category === "Tinctures") {
        const volumeInMl = parseTinctureVolume(item.weight);
        usage.tinctures += volumeInMl * item.quantity;
      } else if (item.category === "Topicals") {
        usage.topicals += weightInMg * item.quantity;
      } else if (item.category === "Infused Pre-Rolls") {
        usage.infusedPreRolls += weightInMg * item.quantity;
      } else if (item.category === "Clones") {
        usage.clones += item.quantity; // Count clones as units, not weight
      }

      return usage;
    }, { flower: 0, concentrates: 0, edibles: 0, tinctures: 0, inhalableCannabinoidsExtracts: 0, topicals: 0, infusedPreRolls: 0, clones: 0 });
  };

  // Parse weight string to milligrams
  const parseWeight = (weight?: string): number => {
    if (!weight) return 0;
    const num = parseFloat(weight.replace(/[^\d.]/g, ''));
    if (weight.includes('g') && !weight.includes('mg')) {
      return num * 1000; // convert grams to mg
    }
    return num; // assume mg
  };

  // Parse tincture volume string to milliliters
  const parseTinctureVolume = (weight?: string): number => {
    if (!weight) return 0;
    const num = parseFloat(weight.replace(/[^\d.]/g, ''));
    if (weight.includes('ml')) {
      return num;
    }
    if (weight.includes('oz')) {
      return num * 29.5735; // convert oz to ml
    }
    return num; // assume ml
  };

  // Check if adding item would exceed limits
  const wouldExceedLimits = (product: Product, quantity: number = 1) => {
    const currentUsage = calculateCurrentUsage();
    const weightInMg = parseWeight(product.weight);

    let newUsage = { ...currentUsage };

    if (product.category === "Flower" || product.category === "Pre-Rolls") {
      newUsage.flower += weightInMg * quantity;
    } else if (product.category === "Concentrates") {
      newUsage.concentrates += weightInMg * quantity;
    } else if (product.category === "Vapes") {
      newUsage.inhalableCannabinoidsExtracts += weightInMg * quantity;
    } else if (product.category === "Edibles") {
      newUsage.edibles += weightInMg * quantity;
    } else if (product.category === "Tinctures") {
      const volumeInMl = parseTinctureVolume(product.weight);
      newUsage.tinctures += volumeInMl * quantity;
    } else if (product.category === "Topicals") {
      newUsage.topicals += weightInMg * quantity;
    } else if (product.category === "Infused Pre-Rolls") {
      newUsage.infusedPreRolls += weightInMg * quantity;
    } else if (product.category === "Clones") {
      newUsage.clones += quantity;
    }

    return {
      flower: newUsage.flower > oregonLimits.flower,
      concentrates: newUsage.concentrates > oregonLimits.concentrates,
      edibles: newUsage.edibles > oregonLimits.edibles,
      tinctures: newUsage.tinctures > oregonLimits.tinctures,
      inhalableCannabinoidsExtracts: newUsage.inhalableCannabinoidsExtracts > oregonLimits.inhalableCannabinoidsExtracts,
      topicals: newUsage.topicals > oregonLimits.topicals,
      infusedPreRolls: newUsage.infusedPreRolls > oregonLimits.infusedPreRolls,
      clones: newUsage.clones > oregonLimits.clones
    };
  };

  const getItemTotal = (item: CartItem) => {
    let baseTotal = item.price * item.quantity;

    // Apply veteran discount if customer is a veteran (10% off all items including GLS)
    if (selectedLoyaltyCustomer?.isVeteran) {
      baseTotal = baseTotal * 0.9; // 10% discount
    }

    if (item.discount === 0) return baseTotal;

    if (item.discountType === 'percentage') {
      return baseTotal * (1 - item.discount / 100);
    } else {
      return Math.max(0, baseTotal - item.discount);
    }
  };

  const subtotal = cart.reduce((sum, item) => sum + getItemTotal(item), 0);
  const taxableSubtotal = cart.reduce((sum, item) => {
    return item.isUntaxed ? sum : sum + getItemTotal(item);
  }, 0);
  const untaxedSubtotal = cart.reduce((sum, item) => {
    return item.isUntaxed ? sum + getItemTotal(item) : sum;
  }, 0);

  const getCartDiscountAmount = () => {
    if (!cartDiscount) return 0;

    // Calculate subtotal of non-GLS items only (GLS products cannot have discounts)
    const nonGLSSubtotal = cart.reduce((sum, item) => {
      const product = sampleProducts.find(p => p.id === item.id);
      return product?.isGLS ? sum : sum + getItemTotal(item);
    }, 0);

    if (cartDiscount.type === 'percentage') {
      return nonGLSSubtotal * (cartDiscount.value / 100);
    }
    return Math.min(cartDiscount.value, nonGLSSubtotal);
  };

  const discountedSubtotal = subtotal - getCartDiscountAmount();
  const discountedTaxableSubtotal = taxableSubtotal - (getCartDiscountAmount() * (taxableSubtotal / subtotal));

  // Check if customer is medical patient/caregiver - medical customers are tax exempt
  const isMedicalCustomer = customerInfo.medicalCard && customerInfo.medicalCard.trim() !== "";
  const tax = isMedicalCustomer ? 0 : discountedTaxableSubtotal * taxRate;
  const total = discountedSubtotal + tax;

  // Check for queue orders and customer data on component mount
  useEffect(() => {
    const queueOrderData = localStorage.getItem('queueOrder');
    if (queueOrderData) {
      const orderData = JSON.parse(queueOrderData);
      setQueueOrder(orderData);
      setShowQueueOrderDialog(true);
      // Clear the stored order so it doesn't show again
      localStorage.removeItem('queueOrder');
    }

    // Check for customer data from customer management
    const customerData = localStorage.getItem('selectedCustomerForSale');
    if (customerData) {
      try {
        const customer = JSON.parse(customerData);

        // Set up customer information
        setCustomerInfo({
          name: customer.name,
          phone: customer.phone,
          medicalCard: customer.medicalCard || '',
          caregiverCard: '',
          isVerified: true, // Customer from management is pre-verified
          isOregonResident: true,
          dailyPurchases: {
            flower: 0,
            concentrates: 0,
            edibles: 0,
            tinctures: 0,
            inhalableCannabinoidsExtracts: 0,
            topicals: 0,
            infusedPreRolls: 0,
            clones: 0
          }
        });

        // Set loyalty customer if available
        if (customer.loyaltyProgram) {
          const loyaltyCustomer = {
            id: customer.id,
            memberId: customer.loyaltyProgram.memberId,
            name: customer.name,
            phone: customer.phone,
            email: customer.email,
            tier: customer.loyaltyProgram.tier,
            pointsBalance: customer.loyaltyProgram.pointsBalance,
            isVeteran: customer.isVeteran
          };
          setSelectedLoyaltyCustomer(loyaltyCustomer);
        }

        // Set customer type for medical vs recreational
        setCustomerType(customer.customerType);

        // Start the sale automatically
        setSaleStarted(true);

        // Clear the stored customer data
        localStorage.removeItem('selectedCustomerForSale');

        // Show notification
        setTimeout(() => {
          alert(`Sale started for ${customer.name}${customer.loyaltyProgram ? ` (${customer.loyaltyProgram.tier} member)` : ''}`);
        }, 500);

      } catch (error) {
        console.warn('Could not parse customer data from localStorage:', error);
        localStorage.removeItem('selectedCustomerForSale');
      }
    }
  }, []);

  const loadQueueOrder = () => {
    if (queueOrder) {
      // Set customer info based on queue order
      setCustomerInfo({
        name: queueOrder.customerName,
        phone: queueOrder.customerPhone,
        medicalCard: queueOrder.medicalCard || "",
        caregiverCard: queueOrder.caregiverCard || "",
        isVerified: true, // Assume queue orders are verified
        isOregonResident: true,
        dailyPurchases: {
        flower: 0,
        concentrates: 0,
        edibles: 0,
        tinctures: 0,
        inhalableCannabinoidsExtracts: 0,
        topicals: 0,
        infusedPreRolls: 0,
        clones: 0
      }
      });

      // Note: In a real implementation, you would map order items to products and add to cart
      alert(`Queue order ${queueOrder.orderNumber} loaded for ${queueOrder.customerName}. Please manually add items to cart.`);
      setSaleStarted(true);
      setShowQueueOrderDialog(false);
      setQueueOrder(null);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <div className="flex items-center space-x-2 cursor-pointer hover:bg-white/10 p-2 rounded-lg transition-colors">
                  <OregonLogo />
                  <h1 className="text-xl font-semibold">Cannabest POS</h1>
                  <ChevronDown className="w-4 h-4 ml-2" />
                </div>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="start" className="w-64">
                {navigationItems.map((item, index) => {
                  const IconComponent = item.icon;
                  return (
                    <DropdownMenuItem
                      key={index}
                      onClick={() => navigate(item.path)}
                      className="flex items-center gap-3 py-3"
                    >
                      <IconComponent className="w-4 h-4" />
                      {item.label}
                    </DropdownMenuItem>
                  );
                })}
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
          <div className="flex items-center space-x-4">
            <Dialog open={showTaxRateDialog} onOpenChange={setShowTaxRateDialog}>
              <DialogTrigger asChild>
                <Button variant="outline" size="sm" className="header-button-visible">
                  <Settings className="w-4 h-4 mr-2" />
                  Tax: {(taxRate * 100).toFixed(1)}%
                </Button>
              </DialogTrigger>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Configure Tax Rate</DialogTitle>
                </DialogHeader>
                <div className="space-y-4">
                  <div>
                    <Label htmlFor="tax-rate">Tax Rate (%)</Label>
                    <Input
                      id="tax-rate"
                      type="number"
                      step="0.1"
                      min="0"
                      max="100"
                      value={(taxRate * 100).toFixed(1)}
                      onChange={(e) => setTaxRate(parseFloat(e.target.value) / 100 || 0)}
                      placeholder="20.0"
                    />
                    <p className="text-sm text-muted-foreground mt-1">
                      Current rate: {(taxRate * 100).toFixed(1)}% (Oregon default: 20%)
                    </p>
                  </div>
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Quick Presets:</Label>
                    <div className="flex gap-2 flex-wrap">
                      <Button variant="outline" size="sm" onClick={() => setTaxRate(0.20)}>
                        Oregon (20%)
                      </Button>
                      <Button variant="outline" size="sm" onClick={() => setTaxRate(0.0875)}>
                        Washington (8.75%)
                      </Button>
                      <Button variant="outline" size="sm" onClick={() => setTaxRate(0.15)}>
                        California (15%)
                      </Button>
                      <Button variant="outline" size="sm" onClick={() => setTaxRate(0.08)}>
                        Colorado (8%)
                      </Button>
                    </div>
                  </div>
                </div>
                <Button onClick={() => setShowTaxRateDialog(false)} className="w-full">
                  Save Tax Rate
                </Button>
              </DialogContent>
            </Dialog>
            <Dialog open={showCustomerDialog} onOpenChange={setShowCustomerDialog}>
              <DialogTrigger asChild>
                <Button variant="outline" size="sm" className="header-button-visible">
                  <Users className="w-4 h-4 mr-2" />
                  Customer Info
                </Button>
              </DialogTrigger>
              <DialogContent className="max-w-md">
                <DialogHeader>
                  <DialogTitle>Customer Information</DialogTitle>
                </DialogHeader>
                <div className="space-y-4">
                  <div>
                    <Label>Customer Type</Label>
                    <div className="flex gap-2 mt-1">
                      <Button
                        variant={customerType === 'rec' ? 'default' : 'outline'}
                        onClick={() => setCustomerType('rec')}
                        size="sm"
                      >
                        Recreational
                      </Button>
                      <Button
                        variant={customerType === 'medical' ? 'default' : 'outline'}
                        onClick={() => setCustomerType('medical')}
                        size="sm"
                      >
                        Medical Patient/Caregiver
                      </Button>
                    </div>
                  </div>

                  {customerType === 'rec' ? (
                    <>
                      <div>
                        <Label htmlFor="customer-name">Customer Name</Label>
                        <Input
                          id="customer-name"
                          value={customerInfo.name}
                          onChange={(e) => setCustomerInfo(prev => ({...prev, name: e.target.value}))}
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                              setShowCustomerDialog(false);
                            }
                          }}
                          placeholder="Enter customer name"
                        />
                      </div>
                      <div>
                        <Label htmlFor="customer-phone">Phone Number</Label>
                        <Input
                          id="customer-phone"
                          value={customerInfo.phone}
                          onChange={(e) => setCustomerInfo(prev => ({...prev, phone: e.target.value}))}
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                              setShowCustomerDialog(false);
                            }
                          }}
                          placeholder="(555) 123-4567"
                        />
                      </div>
                      <div className="flex items-center space-x-2">
                        <input
                          type="checkbox"
                          id="verified"
                          checked={customerInfo.isVerified}
                          onChange={(e) => setCustomerInfo(prev => ({...prev, isVerified: e.target.checked}))}
                          required
                        />
                        <Label htmlFor="verified" className="text-red-600 font-medium">Customer ID Verified *</Label>
                      </div>
                      {!customerInfo.isVerified && (
                        <div className="text-sm text-red-600 bg-red-50 p-2 rounded">
                          Customer ID verification is required before completing the sale.
                        </div>
                      )}
                    </>
                  ) : (
                    <>
                      <div>
                        <Label htmlFor="med-name">Name</Label>
                        <Input
                          id="med-name"
                          value={medicalCustomer.name}
                          onChange={(e) => setMedicalCustomer(prev => ({...prev, name: e.target.value}))}
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                              setShowCustomerDialog(false);
                            }
                          }}
                          placeholder="Enter patient/caregiver name"
                          required
                        />
                      </div>
                      <div>
                        <Label htmlFor="med-phone">Phone Number</Label>
                        <Input
                          id="med-phone"
                          value={medicalCustomer.phone}
                          onChange={(e) => setMedicalCustomer(prev => ({...prev, phone: e.target.value}))}
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                              setShowCustomerDialog(false);
                            }
                          }}
                          placeholder="(555) 123-4567"
                        />
                      </div>
                      <div>
                        <Label htmlFor="med-card">Medical Card Number</Label>
                        <Input
                          id="med-card"
                          value={medicalCustomer.medicalCardNumber}
                          onChange={(e) => setMedicalCustomer(prev => ({...prev, medicalCardNumber: e.target.value}))}
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                              setShowCustomerDialog(false);
                            }
                          }}
                          placeholder="MMJ123456"
                          required
                        />
                      </div>
                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <Label htmlFor="issue-date">Issue Date</Label>
                          <Input
                            id="issue-date"
                            type="date"
                            value={medicalCustomer.issueDate}
                            onChange={(e) => setMedicalCustomer(prev => ({...prev, issueDate: e.target.value}))}
                            required
                          />
                        </div>
                        <div>
                          <Label htmlFor="exp-date">Expiration Date</Label>
                          <Input
                            id="exp-date"
                            type="date"
                            value={medicalCustomer.expirationDate}
                            onChange={(e) => setMedicalCustomer(prev => ({...prev, expirationDate: e.target.value}))}
                            required
                          />
                        </div>
                      </div>
                      <div className="flex items-center space-x-2">
                        <input
                          type="checkbox"
                          id="is-patient"
                          checked={medicalCustomer.isPatient}
                          onChange={(e) => setMedicalCustomer(prev => ({...prev, isPatient: e.target.checked}))}
                        />
                        <Label htmlFor="is-patient">Patient (uncheck for caregiver)</Label>
                      </div>
                      <div>
                        <Label htmlFor="notes">Personal Notes</Label>
                        <textarea
                          id="notes"
                          value={medicalCustomer.notes}
                          onChange={(e) => setMedicalCustomer(prev => ({...prev, notes: e.target.value}))}
                          placeholder="Add personal notes about this customer..."
                          className="w-full p-2 border rounded-md text-sm"
                          rows={3}
                        />
                      </div>
                    </>
                  )}

                  <Button onClick={() => setShowCustomerDialog(false)} className="w-full">
                    Save Customer Info
                  </Button>
                </div>
              </DialogContent>
            </Dialog>
            <Button variant="outline" size="sm" className="header-button-visible" onClick={() => navigate("/queue")}>
              <Clock className="w-4 h-4 mr-2" />
              Order Queue
            </Button>
            <span className="text-sm">Cashier: John Doe</span>
            <Button variant="ghost" size="sm">
              <LogOut className="w-4 h-4" />
            </Button>
          </div>
        </div>
      </header>

      <div className="flex h-[calc(100vh-80px)]">
        {/* Sidebar - Hidden, replaced by header dropdown */}
        <aside className="w-64 pos-sidebar hidden">
          <nav className="p-4 space-y-2">
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/analytics")}>
              <BarChart3 className="w-4 h-4 mr-3" />
              Analytics
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/customers")}>
              <Users className="w-4 h-4 mr-3" />
              Customers
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/reports")}>
              <FileText className="w-4 h-4 mr-3" />
              Custom Reports
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/inventory-report")}>
              <BarChart3 className="w-4 h-4 mr-3" />
              Inventory Evaluation
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/deals")}>
              <Tag className="w-4 h-4 mr-3" />
              Deals & Specials
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/employees")}>
              <Users className="w-4 h-4 mr-3" />
              Employees
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/products?tab=inventory")}>
              <Package className="w-4 h-4 mr-3" />
              Inventory
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/loyalty")}>
              <Users className="w-4 h-4 mr-3" />
              Loyalty Program
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/queue")}>
              <Clock className="w-4 h-4 mr-3" />
              Order Queue
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button">
              <ShoppingCart className="w-4 h-4 mr-3" />
              Point of Sale
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/price-tiers")}>
              <DollarSign className="w-4 h-4 mr-3" />
              Price Tiers
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/products")}>
              <Plus className="w-4 h-4 mr-3" />
              Product Creation
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/rooms")}>
              <Home className="w-4 h-4 mr-3" />
              Rooms & Drawers
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/sales")}>
              <Database className="w-4 h-4 mr-3" />
              Sales Management
            </Button>
            <Button variant="ghost" className="w-full justify-start pos-sidebar-button" onClick={() => navigate("/settings")}>
              <Settings className="w-4 h-4 mr-3" />
              Settings
            </Button>
          </nav>
        </aside>

        {/* Main Content */}
        <main className="flex-1 flex">
          {/* Product Catalog */}
          <div className={`${saleStarted ? 'flex-[1_1_0]' : 'flex-1'} p-6 transition-all duration-300`}>
            <div className="mb-6">
              <div className="flex items-center space-x-4 mb-4">
                <div className="relative flex-1 max-w-md">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <Input
                    placeholder="Search by name, Metrc tag, SKU, vendor, farm, supplier..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>
              
              {/* Category Filters */}
              <div className="flex space-x-2 flex-wrap">
                {categories.map(category => (
                  <Button
                    key={category}
                    variant={selectedCategory === category && !showInventoryTab ? "default" : "outline"}
                    size="sm"
                    onClick={() => {
                      setSelectedCategory(category);
                      setShowInventoryTab(false);
                    }}
                  >
                    {category}
                  </Button>
                ))}
                {saleStarted && (
                  <Button
                    variant={showInventoryTab ? "default" : "outline"}
                    size="sm"
                    onClick={() => setShowInventoryTab(true)}
                    className="bg-purple-600 hover:bg-purple-700 text-white"
                  >
                    <Package className="w-4 h-4 mr-2" />
                    Inventory (Non-Sales Floor)
                  </Button>
                )}
              </div>

              {/* Sorting Controls */}
              <div className="flex items-center gap-4 mt-4">
                <div className="flex items-center gap-2">
                  <ArrowUpDown className="w-4 h-4 text-muted-foreground" />
                  <span className="text-sm font-medium">Sort by:</span>
                </div>
                <Select value={`${sortBy}-${sortOrder}`} onValueChange={(value) => {
                  const [field, order] = value.split('-') as [typeof sortBy, typeof sortOrder];
                  setSortBy(field);
                  setSortOrder(order);
                }}>
                  <SelectTrigger className="w-48">
                    <SelectValue placeholder="Sort products" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="name-asc">Name (A-Z)</SelectItem>
                    <SelectItem value="name-desc">Name (Z-A)</SelectItem>
                    <SelectItem value="price-asc">Price (Low-High)</SelectItem>
                    <SelectItem value="price-desc">Price (High-Low)</SelectItem>
                    <SelectItem value="category-asc">Category (A-Z)</SelectItem>
                    <SelectItem value="category-desc">Category (Z-A)</SelectItem>
                    <SelectItem value="thc-asc">THC (Low-High)</SelectItem>
                    <SelectItem value="thc-desc">THC (High-Low)</SelectItem>
                    <SelectItem value="room-asc">Room (A-Z)</SelectItem>
                    <SelectItem value="room-desc">Room (Z-A)</SelectItem>
                  </SelectContent>
                </Select>

                {/* View Mode Toggle */}
                <div className="flex items-center gap-2 ml-auto">
                  <span className="text-sm font-medium text-muted-foreground">View:</span>
                  <div className="flex items-center gap-1 border rounded-lg p-1">
                    <Button
                      variant={cashierViewMode === 'cards' ? 'default' : 'ghost'}
                      size="sm"
                      onClick={() => {
                        setCashierViewMode('cards');
                        // Update localStorage to keep settings in sync
                        try {
                          const savedSettings = localStorage.getItem('cannabest-store-settings');
                          const settings = savedSettings ? JSON.parse(savedSettings) : {};
                          const newSettings = { ...settings, inventoryViewMode: 'cards' };
                          localStorage.setItem('cannabest-store-settings', JSON.stringify(newSettings));
                          console.log('Index: Updated localStorage with cards view');

                          // Dispatch events to notify other components
                          window.dispatchEvent(new CustomEvent('settings-updated', {
                            detail: newSettings
                          }));
                          window.dispatchEvent(new CustomEvent('inventory-view-changed', {
                            detail: { viewMode: 'cards' }
                          }));
                        } catch (error) {
                          console.warn('Could not update localStorage:', error);
                        }
                      }}
                      className="px-3"
                    >
                      <Grid3X3 className="w-4 h-4" />
                    </Button>
                    <Button
                      variant={cashierViewMode === 'list' ? 'default' : 'ghost'}
                      size="sm"
                      onClick={() => {
                        setCashierViewMode('list');
                        // Update localStorage to keep settings in sync
                        try {
                          const savedSettings = localStorage.getItem('cannabest-store-settings');
                          const settings = savedSettings ? JSON.parse(savedSettings) : {};
                          const newSettings = { ...settings, inventoryViewMode: 'list' };
                          localStorage.setItem('cannabest-store-settings', JSON.stringify(newSettings));
                          console.log('Index: Updated localStorage with list view');

                          // Dispatch events to notify other components
                          window.dispatchEvent(new CustomEvent('settings-updated', {
                            detail: newSettings
                          }));
                          window.dispatchEvent(new CustomEvent('inventory-view-changed', {
                            detail: { viewMode: 'list' }
                          }));
                        } catch (error) {
                          console.warn('Could not update localStorage:', error);
                        }
                      }}
                      className="px-3"
                    >
                      <List className="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            {/* Product Display - Conditional rendering based on view mode */}
            <div className={`${cashierViewMode === 'list' ? 'space-y-2' : `grid gap-4 ${saleStarted ? 'grid-cols-1' : 'grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'}`} transition-all duration-300`}>
              {filteredProducts.map(product => (
                <Card key={product.id} className="hover:shadow-md transition-shadow">
                  <CardContent className={cashierViewMode === 'list' ? 'p-3' : 'p-4'}>
                    {/* Only show images in card view, not in list view */}
                    {cashierViewMode === 'cards' && (
                      <div className="aspect-square bg-gray-100 rounded-lg mb-3 overflow-hidden">
                        <img
                          src={product.image}
                          alt={product.name}
                          className="w-full h-full object-cover"
                          onError={(e) => {
                            e.currentTarget.style.display = 'none';
                            e.currentTarget.nextElementSibling?.classList.remove('hidden');
                          }}
                        />
                        <div className="hidden w-full h-full flex items-center justify-center">
                          <Leaf className="w-8 h-8 text-success" />
                        </div>
                      </div>
                    )}
                    {/* Product Info - Layout differs between card and list view */}
                    {cashierViewMode === 'list' ? (
                      /* List View Layout - Compact horizontal layout with small product image */
                      <div className="flex items-center justify-between gap-3">
                        <div className="flex gap-3 flex-1 min-w-0">
                          {/* Small product image on the left side */}
                          <div className="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                            <img
                              src={product.image}
                              alt={product.name}
                              className="w-full h-full object-cover"
                              onError={(e) => {
                                e.currentTarget.style.display = 'none';
                                e.currentTarget.nextElementSibling?.classList.remove('hidden');
                              }}
                            />
                            <div className="hidden w-full h-full flex items-center justify-center">
                              <Leaf className="w-6 h-6 text-success" />
                            </div>
                          </div>

                          <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 mb-1">
                              <h3 className="font-medium text-sm truncate">{product.name}</h3>
                              {product.isGLS && <MarijuanaLeaf size="4" />}
                              {product.isUntaxed && (
                                <Badge variant="outline" className="text-xs bg-yellow-50 text-yellow-700 flex-shrink-0">Untaxed</Badge>
                              )}
                            </div>

                            <div className="flex items-center gap-2 text-xs text-muted-foreground">
                              <span>{product.category}</span>
                              <span>•</span>
                              <span>{product.weight}</span>
                              {product.metrcTag && (
                                <>
                                  <span>•</span>
                                  <span className="font-mono">...{product.metrcTag.slice(-5)}</span>
                                </>
                              )}
                            </div>

                            {/* Room status in compact form */}
                            {product.room && (
                              <div className={`inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-xs ${
                                product.room === "Sales Floor"
                                  ? "bg-green-50 text-green-600"
                                  : "bg-orange-50 text-orange-600"
                              }`}>
                                <Building className="w-3 h-3" />
                                <span>{product.room === "Sales Floor" ? "On Sales Floor" : product.room}</span>
                              </div>
                            )}

                            {/* Cannabinoids in compact form */}
                            {(product.thc || product.cbd) && (
                              <div className="flex gap-1 mt-1">
                                {product.thc && <Badge variant="secondary" className="text-xs px-1 py-0">THC: {product.thc}%</Badge>}
                                {product.cbd && <Badge variant="outline" className="text-xs px-1 py-0">CBD: {product.cbd}%</Badge>}
                              </div>
                            )}
                          </div>
                        </div>

                        {/* Action buttons and Price/Add button */}
                        <div className="flex items-center gap-2 flex-shrink-0">
                          {/* Action Buttons Row - Compact for list view */}
                          <div className="flex gap-1">
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => printBarcode(product)}
                              className="h-8 w-8 p-0"
                              title="Print Barcode"
                            >
                              <QrCode className="w-3 h-3" />
                            </Button>
                            {product.metrcTag && (
                              <>
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => printExitLabel(product)}
                                  className="h-8 w-8 p-0"
                                  title="Print Exit Label"
                                >
                                  <FileOutput className="w-3 h-3" />
                                </Button>
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  onClick={() => {
                                    setSelectedProductForEnhancedMetrc(product);
                                    setShowEnhancedMetrcDialog(true);
                                  }}
                                  className="h-8 w-8 p-0"
                                  title="View Metrc Details"
                                >
                                  <Database className="w-3 h-3" />
                                </Button>
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  onClick={() => {
                                    setSelectedProductForTransfer(product);
                                    setShowRoomTransferDialog(true);
                                  }}
                                  className="h-8 w-8 p-0"
                                  title="Transfer to Room"
                                >
                                  <ArrowRightLeft className="w-3 h-3" />
                                </Button>
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  onClick={() => {
                                    setSelectedProductForEdit(product);
                                    setShowEditProductDialog(true);
                                  }}
                                  className="h-8 w-8 p-0"
                                  title="Edit Product"
                                >
                                  <Edit3 className="w-3 h-3" />
                                </Button>
                              </>
                            )}
                          </div>

                          {/* Price and Add button */}
                          <div className="flex items-center gap-2">
                            <div className="text-right">
                              <div className="font-semibold text-sm">
                                {product.category === 'Flower' ? (() => {
                                  if (product.price === 1.07) return '$30/oz Special';
                                  if (product.price === 1.79) return '$50/oz Special';
                                  if (product.price === 4.00) return '$4/g';
                                  if (product.price === 7.00) return '$7/g';
                                  if (product.price === 12.00) return '$12/g';
                                  if (product.price === 14.00) return '$14/g';
                                  if (product.price === 16.00) return '$16/g';
                                  return `$${product.price.toFixed(2)}`;
                                })() : `$${product.price.toFixed(2)}`}
                              </div>
                              {product.isGLS && (
                                <div className="text-xs text-orange-600">Manual Discount Only</div>
                              )}
                            </div>

                            {(() => {
                              const violations = wouldExceedLimits(product);
                              const hasViolation = Object.values(violations).some(v => v);

                              if (!saleStarted) {
                                return (
                                  <Button size="sm" disabled className="bg-gray-100 text-gray-500">
                                    Start Sale First
                                  </Button>
                                );
                              }

                              const isNotOnSalesFloor = product.room && product.room !== "Sales Floor";

                              if (isNotOnSalesFloor && !showInventoryTab) {
                                return (
                                  <Button size="sm" disabled className="bg-orange-100 text-orange-700">
                                    <Lock className="w-4 h-4 mr-1" />
                                    Not on Sales Floor
                                  </Button>
                                );
                              }

                              return hasViolation && customerInfo.isVerified ? (
                                <Button size="sm" disabled className="bg-red-100 text-red-700">
                                  Limit Exceeded
                                </Button>
                              ) : (
                                <Button size="sm" onClick={() => addToCart(product)}>
                                  <Plus className="w-4 h-4" />
                                </Button>
                              );
                            })()}
                          </div>
                        </div>
                      </div>
                    ) : (
                      /* Card View Layout - Original vertical layout */
                      <div className="flex items-start justify-between mb-1">
                        <div className="flex items-center gap-2">
                          <div className="flex flex-col">
                            <h3 className="font-medium">{product.name}</h3>
                            {product.metrcTag && (
                              <div className="text-xs text-gray-500 font-mono">
                                METRC: ...{product.metrcTag.slice(-5)}
                              </div>
                            )}
                          </div>
                          {product.isGLS && (
                            <MarijuanaLeaf size="6" />
                          )}
                        </div>
                        {product.isUntaxed && (
                          <Badge variant="outline" className="text-xs bg-yellow-50 text-yellow-700">Untaxed</Badge>
                        )}
                      </div>
                    )}
                    {/* Card View Content - Only shown in card mode */}
                    {cashierViewMode === 'cards' && (
                      <>
                        <p className="text-sm text-muted-foreground mb-1">{product.category} • {product.weight}</p>
                        {product.room && (
                          <div className={`flex items-center gap-1 mb-1 p-1 rounded ${
                            product.room === "Sales Floor"
                              ? "bg-green-50"
                              : "bg-orange-50 border border-orange-200"
                          }`}>
                            <Building className={`w-3 h-3 ${
                              product.room === "Sales Floor" ? "text-green-600" : "text-orange-600"
                            }`} />
                            <span className={`text-xs font-medium ${
                              product.room === "Sales Floor" ? "text-green-600" : "text-orange-600"
                            }`}>
                              {product.room === "Sales Floor" ? "✓ On Sales Floor" : `📦 ${product.room}`}
                            </span>
                          </div>
                        )}
                        {(product.thc || product.cbd) && (
                          <div className="flex gap-2 mb-2">
                            {product.thc && <Badge variant="secondary" className="text-xs">THC: {product.thc}%</Badge>}
                            {product.cbd && <Badge variant="outline" className="text-xs">CBD: {product.cbd}%</Badge>}
                          </div>
                        )}
                        {product.strain && (
                          <p className="text-xs text-muted-foreground mb-1">{product.strain}</p>
                        )}

                        {/* Print Buttons Row */}
                        <div className="flex gap-1 mb-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => printBarcode(product)}
                            className="h-6 px-2 text-xs"
                            title="Print Barcode"
                          >
                            <QrCode className="w-3 h-3" />
                          </Button>
                          {product.metrcTag && (
                            <>
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => printExitLabel(product)}
                                className="h-6 px-2 text-xs"
                                title="Print Exit Label"
                              >
                                <FileOutput className="w-3 h-3" />
                              </Button>
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => {
                                  setSelectedProductForEnhancedMetrc(product);
                                  setShowEnhancedMetrcDialog(true);
                                }}
                                className="h-6 px-2 text-xs"
                                title="View Metrc Details"
                              >
                                <Database className="w-3 h-3" />
                              </Button>
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => {
                                  setSelectedProductForTransfer(product);
                                  setShowRoomTransferDialog(true);
                                }}
                                className="h-6 px-2 text-xs"
                                title="Transfer to Room"
                              >
                                <ArrowRightLeft className="w-3 h-3" />
                              </Button>
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => {
                                  setSelectedProductForEdit(product);
                                  setShowEditProductDialog(true);
                                }}
                                className="h-6 px-2 text-xs"
                                title="Edit Product"
                              >
                                <Edit3 className="w-3 h-3" />
                              </Button>
                            </>
                          )}
                        </div>

                        <div className="flex items-center gap-1 mb-2 flex-wrap">
                          {product.testResults?.tested && (
                            <Badge variant="outline" className="text-xs bg-green-50 text-green-700">Lab Tested</Badge>
                          )}
                          {product.isGLS && (
                            <Badge variant="outline" className="text-xs bg-orange-50 text-orange-700 border-orange-300">
                              GLS - Manual Discount Only
                            </Badge>
                          )}
                        </div>
                        <div className="flex items-center justify-between">
                          <span className="font-semibold">
                            {product.category === 'Flower' ? (() => {
                              if (product.price === 1.07) return '$30/oz Special';
                              if (product.price === 1.79) return '$50/oz Special';
                              if (product.price === 4.00) return '$4/g';
                              if (product.price === 7.00) return '$7/g';
                              if (product.price === 12.00) return '$12/g';
                              if (product.price === 14.00) return '$14/g';
                              if (product.price === 16.00) return '$16/g';
                              return `$${product.price.toFixed(2)}`;
                            })() : `$${product.price.toFixed(2)}`}
                          </span>
                          {(() => {
                            const violations = wouldExceedLimits(product);
                            const hasViolation = Object.values(violations).some(v => v);

                            if (!saleStarted) {
                              return (
                                <Button size="sm" disabled className="bg-gray-100 text-gray-500">
                                  Start Sale First
                                </Button>
                              );
                            }

                            // Check if item is not on sales floor
                            const isNotOnSalesFloor = product.room && product.room !== "Sales Floor";

                            if (isNotOnSalesFloor && !showInventoryTab) {
                              return (
                                <Button size="sm" disabled className="bg-orange-100 text-orange-700">
                                  <Lock className="w-4 h-4 mr-1" />
                                  Not on Sales Floor
                                </Button>
                              );
                            }

                            return hasViolation && customerInfo.isVerified ? (
                              <Button size="sm" disabled className="bg-red-100 text-red-700">
                                Limit Exceeded
                              </Button>
                            ) : (
                              <Button size="sm" onClick={() => addToCart(product)}>
                                <Plus className="w-4 h-4" />
                              </Button>
                            );
                          })()}
                        </div>
                      </>
                    )}
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>

          {/* Shopping Cart */}
          <div className={`${saleStarted ? 'flex-[3_1_0] min-w-[600px]' : 'w-96'} bg-white border-l border-border p-6 sticky top-0 h-screen overflow-y-auto transition-all duration-300`}>
            {/* New Sale Button */}
            <div className="mb-4">
              <Button
                className="w-full"
                size="lg"
                variant={saleStarted ? "outline" : "default"}
                onClick={handleNewSale}
              >
                <Plus className="w-4 h-4 mr-2" />
                {saleStarted ? "Start New Sale" : "New Sale"}
              </Button>
              {saleStarted && (
                <div className="text-xs text-green-600 text-center mt-1">
                  ✓ Sale in progress - items can be added to cart
                </div>
              )}
              {!saleStarted && (
                <div className="text-xs text-orange-600 text-center mt-1">
                  Click to start a new transaction
                </div>
              )}
            </div>
            {/* Saved Sales Actions */}
            {saleStarted && cart.length > 0 && (
              <div className="mb-4">
                <Button
                  variant="outline"
                  className="w-full mb-2"
                  size="sm"
                  onClick={saveSaleForLater}
                >
                  <Database className="w-4 h-4 mr-2" />
                  Save Sale for Later
                </Button>
              </div>
            )}

            <div className="mb-4">
              <Dialog open={showSavedSalesDialog} onOpenChange={setShowSavedSalesDialog}>
                <DialogTrigger asChild>
                  <Button variant="outline" className="w-full" size="sm">
                    <FileText className="w-4 h-4 mr-2" />
                    Saved Sales ({savedSales.length})
                  </Button>
                </DialogTrigger>
              </Dialog>
            </div>

            {/* Customer Lookup */}
            <div className="mb-4">
              <Dialog open={showCustomerLookup} onOpenChange={setShowCustomerLookup}>
                <DialogTrigger asChild>
                  <Button variant="outline" className="w-full" size="sm">
                    <Search className="w-4 h-4 mr-2" />
                    Loyalty Customer Lookup
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Loyalty Customer Lookup</DialogTitle>
                  </DialogHeader>
                  <div className="space-y-4">
                    <Input
                      placeholder="Search by name, phone, email, or loyalty member ID..."
                      value={customerSearchQuery}
                      onChange={(e) => setCustomerSearchQuery(e.target.value)}
                    />
                    <div className="max-h-60 overflow-y-auto space-y-2">
                      {loyaltyCustomers
                        .filter(customer =>
                          customer.name.toLowerCase().includes(customerSearchQuery.toLowerCase()) ||
                          customer.phone.includes(customerSearchQuery) ||
                          customer.email.toLowerCase().includes(customerSearchQuery.toLowerCase()) ||
                          customer.memberId.toLowerCase().includes(customerSearchQuery.toLowerCase())
                        )
                        .map(customer => (
                          <div
                            key={customer.id}
                            className="p-3 border rounded cursor-pointer hover:bg-gray-50"
                            onClick={() => {
                              setSelectedLoyaltyCustomer(customer);
                              setCustomerInfo({
                                name: customer.name,
                                phone: customer.phone,
                                medicalCard: "",
                                caregiverCard: "",
                                isVerified: false,
                                isOregonResident: true,
                                dailyPurchases: {
        flower: 0,
        concentrates: 0,
        edibles: 0,
        tinctures: 0,
        inhalableCannabinoidsExtracts: 0,
        topicals: 0,
        infusedPreRolls: 0,
        clones: 0
      }
                              });
                              setShowCustomerLookup(false);
                              setCustomerSearchQuery("");

                              // Show notification if customer has sales history
                              if (customer.salesHistory.length > 0) {
                                alert(`${customer.name} found! Last visit: ${customer.lastVisit ? new Date(customer.lastVisit).toLocaleDateString() : 'Never'}. Total spent: $${customer.totalSpent.toFixed(2)}`);
                              }
                            }}
                          >
                            <div className="flex items-center justify-between">
                              <div>
                                <div className="font-medium">{customer.name}</div>
                                <div className="text-sm text-gray-600">{customer.phone}</div>
                                <div className="text-sm text-gray-600">{customer.email}</div>
                                <div className="text-xs text-blue-600">Member ID: {customer.memberId}</div>
                              </div>
                              <div className="text-right">
                                <Badge variant="outline" className="text-xs">{customer.tier}</Badge>
                                <div className="text-sm font-medium">{customer.pointsBalance} pts</div>
                                <div className="text-xs text-gray-500">${customer.totalSpent.toFixed(2)} spent</div>
                              </div>
                            </div>
                          </div>
                        ))}
                    </div>
                  </div>
                </DialogContent>
              </Dialog>
            </div>

            {/* Customer Info Display */}
            {customerInfo.name && (
              <div className="mb-4 p-3 bg-green-50 rounded-lg border border-green-200">
                <div className="font-medium text-green-800">{customerInfo.name}</div>
                <div className="text-sm text-green-600">{customerInfo.phone}</div>
                {customerInfo.medicalCard && (
                  <div className="text-xs text-green-600">Medical: {customerInfo.medicalCard}</div>
                )}
                {customerInfo.caregiverCard && (
                  <div className="text-xs text-green-600">Caregiver: {customerInfo.caregiverCard}</div>
                )}
                {customerInfo.isVerified && (
                  <Badge className="mt-1" variant="default">ID Verified</Badge>
                )}
              </div>
            )}

            {/* Loyalty Points Redemption */}
            {selectedLoyaltyCustomer && selectedLoyaltyCustomer.pointsBalance > 0 && (
              <div className="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                <div className="flex items-center justify-between mb-3">
                  <div>
                    <div className="font-medium text-purple-800">Loyalty Points</div>
                    <div className="text-sm text-purple-600">Available: {selectedLoyaltyCustomer.pointsBalance} points</div>
                  </div>
                  <Badge variant="outline" className="text-purple-700">{selectedLoyaltyCustomer.tier}</Badge>
                </div>
                <div className="space-y-2">
                  <div className="text-xs text-purple-600">
                    100 points = $1.00 discount
                  </div>
                  {cart.some(item => item.autoAppliedDeal) && (
                    <div className="text-xs text-orange-600 bg-orange-50 p-2 rounded border border-orange-200">
                      ⚠️ Cannot use points while automatic deals are active. Remove auto-deals to redeem points.
                    </div>
                  )}
                  <Button
                    variant="outline"
                    size="sm"
                    className="w-full border-purple-300 text-purple-700 hover:bg-purple-100"
                    onClick={() => {
                      // Check if there are any automatic discounts already applied to cart items
                      const hasAutoDiscounts = cart.some(item => item.autoAppliedDeal);

                      if (hasAutoDiscounts) {
                        alert("Loyalty points cannot be redeemed when automatic deals are already applied. Remove automatic discounts first to use points.");
                        return;
                      }

                      if (!cartDiscount && selectedLoyaltyCustomer.pointsBalance >= 100) {
                        // Calculate non-GLS subtotal for redemption (can't redeem against GLS products)
                        const nonGLSSubtotal = cart.reduce((sum, item) => {
                          const product = sampleProducts.find(p => p.id === item.id);
                          return product?.isGLS ? sum : sum + getItemTotal(item);
                        }, 0);

                        const maxRedeemablePoints = Math.min(
                          selectedLoyaltyCustomer.pointsBalance,
                          Math.floor(nonGLSSubtotal * 100) // Can't redeem more than non-GLS subtotal
                        );
                        const redeemPoints = Math.floor(maxRedeemablePoints / 100) * 100; // Round down to nearest 100
                        const discountAmount = redeemPoints / 100;

                        setCartDiscount({
                          type: 'fixed',
                          value: discountAmount,
                          label: `${redeemPoints} Points Redeemed`,
                          reasonCode: 'LOYALTY_POINTS'
                        });

                        // Update customer points (this would be saved to database in real app)
                        setSelectedLoyaltyCustomer(prev => prev ? {
                          ...prev,
                          pointsBalance: prev.pointsBalance - redeemPoints
                        } : null);
                      }
                    }}
                    disabled={
                      cartDiscount !== null ||
                      selectedLoyaltyCustomer.pointsBalance < 100 ||
                      cart.some(item => item.autoAppliedDeal)
                    }
                  >
                    {(() => {
                      const hasAutoDiscounts = cart.some(item => item.autoAppliedDeal);
                      if (hasAutoDiscounts) return "Auto-deals active";
                      if (cartDiscount) return "Discount Applied";
                      if (selectedLoyaltyCustomer.pointsBalance < 100) return "Need 100+ Points";
                      return "Redeem Points";
                    })()}
                  </Button>
                </div>
              </div>
            )}

            {/* Sales Limits Tracking - Always Show for All Customers */}
            <div className="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
              <div className="flex items-center justify-between mb-3">
                <h3 className="font-medium text-blue-800">Sales Limits Tracker</h3>
                <Badge variant={customerInfo.isVerified ? "default" : "secondary"}>
                  {customerInfo.isVerified ? "Verified" : "Unverified"}
                </Badge>
              </div>
              <div className="space-y-3 text-sm">
                {(() => {
                  const currentUsage = calculateCurrentUsage();
                  const isNearLimit = (current: number, limit: number) => (current / limit) > 0.8;
                  const isOverLimit = (current: number, limit: number) => current >= limit;

                  return (
                    <>
                      <div>
                        <div className="flex justify-between mb-1">
                          <span className="flex items-center gap-1">
                            Flower/Pre-Rolls
                            {isNearLimit(currentUsage.flower, oregonLimits.flower) && (
                              <Badge variant="secondary" className="text-xs px-1 py-0">Near Limit</Badge>
                            )}
                          </span>
                          <span className={isOverLimit(currentUsage.flower, oregonLimits.flower) ? "text-red-600 font-bold" : ""}>
                            {(currentUsage.flower / 1000).toFixed(1)}g / {(oregonLimits.flower / 1000).toFixed(1)}g
                          </span>
                        </div>
                        <Progress
                          value={Math.min((currentUsage.flower / oregonLimits.flower) * 100, 100)}
                          className={`h-3 ${isOverLimit(currentUsage.flower, oregonLimits.flower) ? 'bg-red-100' : isNearLimit(currentUsage.flower, oregonLimits.flower) ? 'bg-yellow-100' : ''}`}
                        />
                      </div>

                      <div>
                        <div className="flex justify-between mb-1">
                          <span className="flex items-center gap-1">
                            Concentrates/Extracts
                            {isNearLimit(currentUsage.concentrates, oregonLimits.concentrates) && (
                              <Badge variant="secondary" className="text-xs px-1 py-0">Near Limit</Badge>
                            )}
                          </span>
                          <span className={isOverLimit(currentUsage.concentrates, oregonLimits.concentrates) ? "text-red-600 font-bold" : ""}>
                            {(currentUsage.concentrates / 1000).toFixed(1)}g / {(oregonLimits.concentrates / 1000).toFixed(0)}g
                          </span>
                        </div>
                        <Progress
                          value={Math.min((currentUsage.concentrates / oregonLimits.concentrates) * 100, 100)}
                          className={`h-3 ${isOverLimit(currentUsage.concentrates, oregonLimits.concentrates) ? 'bg-red-100' : isNearLimit(currentUsage.concentrates, oregonLimits.concentrates) ? 'bg-yellow-100' : ''}`}
                        />
                      </div>

                      <div>
                        <div className="flex justify-between mb-1">
                          <span className="flex items-center gap-1">
                            Edibles
                            {isNearLimit(currentUsage.edibles, oregonLimits.edibles) && (
                              <Badge variant="secondary" className="text-xs px-1 py-0">Near Limit</Badge>
                            )}
                          </span>
                          <span className={isOverLimit(currentUsage.edibles, oregonLimits.edibles) ? "text-red-600 font-bold" : ""}>
                            {(currentUsage.edibles / 1000).toFixed(1)}g / {(oregonLimits.edibles / 1000).toFixed(0)}g
                          </span>
                        </div>
                        <Progress
                          value={Math.min((currentUsage.edibles / oregonLimits.edibles) * 100, 100)}
                          className={`h-3 ${isOverLimit(currentUsage.edibles, oregonLimits.edibles) ? 'bg-red-100' : isNearLimit(currentUsage.edibles, oregonLimits.edibles) ? 'bg-yellow-100' : ''}`}
                        />
                      </div>

                      <div>
                        <div className="flex justify-between mb-1">
                          <span className="flex items-center gap-1">
                            Tinctures
                            {isNearLimit(currentUsage.tinctures, oregonLimits.tinctures) && (
                              <Badge variant="secondary" className="text-xs px-1 py-0">Near Limit</Badge>
                            )}
                          </span>
                          <span className={isOverLimit(currentUsage.tinctures, oregonLimits.tinctures) ? "text-red-600 font-bold" : ""}>
                            {(currentUsage.tinctures / 29.5735).toFixed(1)}oz / {(oregonLimits.tinctures / 1000).toFixed(0)}oz
                          </span>
                        </div>
                        <Progress
                          value={Math.min((currentUsage.tinctures / oregonLimits.tinctures) * 100, 100)}
                          className={`h-3 ${isOverLimit(currentUsage.tinctures, oregonLimits.tinctures) ? 'bg-red-100' : isNearLimit(currentUsage.tinctures, oregonLimits.tinctures) ? 'bg-yellow-100' : ''}`}
                        />
                      </div>

                      <div>
                        <div className="flex justify-between mb-1">
                          <span className="flex items-center gap-1">
                            Inhalable Cannabinoids
                            {isNearLimit(currentUsage.inhalableCannabinoidsExtracts, oregonLimits.inhalableCannabinoidsExtracts) && (
                              <Badge variant="secondary" className="text-xs px-1 py-0">Near Limit</Badge>
                            )}
                          </span>
                          <span className={isOverLimit(currentUsage.inhalableCannabinoidsExtracts, oregonLimits.inhalableCannabinoidsExtracts) ? "text-red-600 font-bold" : ""}>
                            {(currentUsage.inhalableCannabinoidsExtracts / 1000).toFixed(1)}g / {(oregonLimits.inhalableCannabinoidsExtracts / 1000).toFixed(0)}g
                          </span>
                        </div>
                        <Progress
                          value={Math.min((currentUsage.inhalableCannabinoidsExtracts / oregonLimits.inhalableCannabinoidsExtracts) * 100, 100)}
                          className={`h-3 ${isOverLimit(currentUsage.inhalableCannabinoidsExtracts, oregonLimits.inhalableCannabinoidsExtracts) ? 'bg-red-100' : isNearLimit(currentUsage.inhalableCannabinoidsExtracts, oregonLimits.inhalableCannabinoidsExtracts) ? 'bg-yellow-100' : ''}`}
                        />
                      </div>

                      {/* Compliance Status */}
                      <div className="pt-2 border-t border-blue-200">
                        <div className="flex items-center justify-between">
                          <span className="text-xs text-blue-700">Compliance Status:</span>
                          <Badge variant={
                            Object.values([
                              isOverLimit(currentUsage.flower, oregonLimits.flower),
                              isOverLimit(currentUsage.concentrates, oregonLimits.concentrates),
                              isOverLimit(currentUsage.edibles, oregonLimits.edibles),
                              isOverLimit(currentUsage.tinctures, oregonLimits.tinctures),
                              isOverLimit(currentUsage.inhalableCannabinoidsExtracts, oregonLimits.inhalableCannabinoidsExtracts)
                            ]).some(Boolean) ? "destructive" : "default"
                          }>
                            {Object.values([
                              isOverLimit(currentUsage.flower, oregonLimits.flower),
                              isOverLimit(currentUsage.concentrates, oregonLimits.concentrates),
                              isOverLimit(currentUsage.edibles, oregonLimits.edibles),
                              isOverLimit(currentUsage.tinctures, oregonLimits.tinctures),
                              isOverLimit(currentUsage.inhalableCannabinoidsExtracts, oregonLimits.inhalableCannabinoidsExtracts)
                            ]).some(Boolean) ? "OVER LIMIT" : "COMPLIANT"}
                          </Badge>
                        </div>
                      </div>
                    </>
                  );
                })()}
              </div>
            </div>

            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-semibold">Current Order</h2>
              <div className="flex items-center gap-2">
                <Badge variant="secondary">{cart.length} items</Badge>
                {cart.length > 0 && (
                  <Dialog open={showCartDiscountDialog} onOpenChange={setShowCartDiscountDialog}>
                    <DialogTrigger asChild>
                      <Button size="sm" variant="outline">
                        <Percent className="w-3 h-3 mr-1" />
                        Cart Discount
                      </Button>
                    </DialogTrigger>
                    <DialogContent>
                      <DialogHeader>
                        <DialogTitle>Apply Cart Discount</DialogTitle>
                      </DialogHeader>
                      <div className="space-y-4">
                        <div className="flex gap-2">
                          <Button
                            variant={discountType === 'percentage' ? 'default' : 'outline'}
                            onClick={() => setDiscountType('percentage')}
                          >
                            Percentage
                          </Button>
                          <Button
                            variant={discountType === 'fixed' ? 'default' : 'outline'}
                            onClick={() => setDiscountType('fixed')}
                          >
                            Fixed Amount
                          </Button>
                        </div>
                        <div>
                          <Label htmlFor="discount-value">
                            {discountType === 'percentage' ? 'Percentage (%)' : 'Amount ($)'}
                          </Label>
                          <Input
                            id="discount-value"
                            type="number"
                            value={discountValue}
                            onChange={(e) => setDiscountValue(e.target.value)}
                            onKeyDown={(e) => {
                              if (e.key === 'Enter' && discountReasonCode.trim()) {
                                applyCartDiscount();
                              }
                            }}
                            placeholder={discountType === 'percentage' ? '10' : '5.00'}
                          />
                        </div>
                        <div>
                          <Label htmlFor="reason-code">Reason Code *</Label>
                          <Input
                            id="reason-code"
                            value={discountReasonCode}
                            onChange={(e) => setDiscountReasonCode(e.target.value)}
                            onKeyDown={(e) => {
                              if (e.key === 'Enter' && discountReasonCode.trim()) {
                                applyCartDiscount();
                              }
                            }}
                            placeholder="e.g., Employee Discount, Senior Discount, Manager Override"
                            required
                          />
                        </div>
                        <Button
                          onClick={applyCartDiscount}
                          className="w-full"
                          disabled={!discountReasonCode.trim()}
                        >
                          Apply Discount
                        </Button>
                      </div>
                    </DialogContent>
                  </Dialog>
                )}
              </div>
            </div>

            {cart.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-12 text-center">
                <ShoppingCart className="w-12 h-12 text-muted-foreground mb-3" />
                <p className="text-muted-foreground">No items in cart</p>
              </div>
            ) : (
              <>
                <div className="space-y-3 mb-6 max-h-64 overflow-y-auto">
                  {cart.map(item => (
                    <div key={item.id} className="p-3 bg-gray-50 rounded-lg">
                      <div className="flex items-start justify-between mb-2">
                        <div className="flex-1">
                          <div className="flex items-center gap-2">
                            <h4 className="font-medium">{item.name}</h4>
                            {(() => {
                              const product = sampleProducts.find(p => p.id === item.id);
                              return product?.isGLS && <MarijuanaLeaf size="5" />;
                            })()}
                            {item.isUntaxed && (
                              <Badge variant="outline" className="text-xs bg-yellow-50 text-yellow-700">Untaxed</Badge>
                            )}
                            {(() => {
                              const product = sampleProducts.find(p => p.id === item.id);
                              return product?.isGLS && (
                                <Badge variant="outline" className="text-xs bg-orange-50 text-orange-700 border-orange-300">
                                  GLS
                                </Badge>
                              );
                            })()}
                          </div>
                          <p className="text-sm text-muted-foreground">
                            ${item.price.toFixed(2)} each • {item.weight}
                          </p>
                          {item.discount > 0 && (
                            <div className="flex items-center gap-1 mt-1">
                              <Tag className="w-3 h-3 text-success" />
                              <span className="text-xs text-success">
                                {item.discountType === 'percentage'
                                  ? `${item.discount}% off`
                                  : `$${item.discount.toFixed(2)} off`}
                                {item.autoAppliedDeal && (
                                  <span className="font-medium"> - {item.autoAppliedDeal}</span>
                                )}
                                {item.discountReasonCode && !item.autoAppliedDeal && ` (${item.discountReasonCode})`}
                              </span>
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => removeItemDiscount(item.id)}
                                className="h-4 w-4 p-0 ml-1"
                                title={item.autoAppliedDeal ? "Remove automatic deal" : "Remove discount"}
                              >
                                <X className="w-2 h-2" />
                              </Button>
                            </div>
                          )}
                          {selectedLoyaltyCustomer?.isVeteran && (
                            <div className="flex items-center gap-1 mt-1">
                              <Star className="w-3 h-3 text-blue-600" />
                              <span className="text-xs text-blue-600 font-medium">
                                10% Veteran Discount Applied
                              </span>
                            </div>
                          )}
                          {item.autoAppliedDeal && (
                            <div className="text-xs text-blue-600 mt-1">
                              🎉 Auto-applied: {item.autoAppliedDeal}
                            </div>
                          )}
                        </div>
                        <div className="text-right">
                          <div className="font-medium">${getItemTotal(item).toFixed(2)}</div>
                          {item.discount > 0 && (
                            <div className="text-xs text-muted-foreground line-through">
                              ${(item.price * item.quantity).toFixed(2)}
                            </div>
                          )}
                        </div>
                      </div>
                      <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => updateQuantity(item.id, -1)}
                          >
                            <Minus className="w-3 h-3" />
                          </Button>
                          {editingQuantity === item.id ? (
                            <Input
                              type="number"
                              value={quantityInput}
                              onChange={(e) => setQuantityInput(e.target.value)}
                              onBlur={() => saveQuantityEdit(item.id)}
                              onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                  saveQuantityEdit(item.id);
                                } else if (e.key === 'Escape') {
                                  setEditingQuantity(null);
                                  setQuantityInput("");
                                }
                              }}
                              className="w-16 h-8 text-center text-sm"
                              min={item.category === "Flower" ? "0.01" : "1"}
                              step={item.category === "Flower" ? "0.01" : "1"}
                              autoFocus
                            />
                          ) : (
                            <button
                              onClick={() => handleQuantityEdit(item)}
                              className="w-8 h-8 text-center hover:bg-gray-100 rounded flex items-center justify-center"
                              title="Click to edit quantity"
                            >
                              <span className="text-sm">
                                {item.category === "Flower"
                                  ? Number(item.quantity).toFixed(2)
                                  : item.quantity}
                              </span>
                              <Edit3 className="w-2 h-2 ml-1 text-gray-400" />
                            </button>
                          )}
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => updateQuantity(item.id, 1)}
                          >
                            <Plus className="w-3 h-3" />
                          </Button>
                        </div>
                        <div className="flex items-center space-x-1">
                          <Dialog open={showDiscountDialog && selectedItemForDiscount === item.id}
                                 onOpenChange={(open) => {
                                   setShowDiscountDialog(open);
                                   if (!open) setSelectedItemForDiscount(null);
                                 }}>
                            <DialogTrigger asChild>
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => {
                                  setSelectedItemForDiscount(item.id);
                                  setDiscountValue(item.discount.toString());
                                  setDiscountType(item.discountType);
                                }}
                                title={(() => {
                                  const product = sampleProducts.find(p => p.id === item.id);
                                  return product?.isGLS ? "Apply manual discount to Green Leaf Special product" : "Apply discount";
                                })()}
                              >
                                <Percent className="w-3 h-3" />
                              </Button>
                            </DialogTrigger>
                            <DialogContent>
                              <DialogHeader>
                                <DialogTitle>Apply Item Discount - {item.name}</DialogTitle>
                              </DialogHeader>
                              <div className="space-y-4">
                                <div className="flex gap-2">
                                  <Button
                                    variant={discountType === 'percentage' ? 'default' : 'outline'}
                                    onClick={() => setDiscountType('percentage')}
                                  >
                                    Percentage
                                  </Button>
                                  <Button
                                    variant={discountType === 'fixed' ? 'default' : 'outline'}
                                    onClick={() => setDiscountType('fixed')}
                                  >
                                    Fixed Amount
                                  </Button>
                                </div>
                                <div>
                                  <Label htmlFor="item-discount-value">
                                    {discountType === 'percentage' ? 'Percentage (%)' : 'Amount ($)'}
                                  </Label>
                                  <Input
                                    id="item-discount-value"
                                    type="number"
                                    value={discountValue}
                                    onChange={(e) => setDiscountValue(e.target.value)}
                                    onKeyDown={(e) => {
                                      if (e.key === 'Enter' && discountReasonCode.trim()) {
                                        const value = parseFloat(discountValue) || 0;
                                        applyItemDiscount(item.id, value, discountType, discountReasonCode);
                                        setShowDiscountDialog(false);
                                        setSelectedItemForDiscount(null);
                                        setDiscountValue("");
                                        setDiscountReasonCode("");
                                      }
                                    }}
                                    placeholder={discountType === 'percentage' ? '10' : '5.00'}
                                  />
                                </div>
                                <div>
                                  <Label htmlFor="item-reason-code">Reason Code *</Label>
                                  <Input
                                    id="item-reason-code"
                                    value={discountReasonCode}
                                    onChange={(e) => setDiscountReasonCode(e.target.value)}
                                    onKeyDown={(e) => {
                                      if (e.key === 'Enter' && discountReasonCode.trim()) {
                                        const value = parseFloat(discountValue) || 0;
                                        applyItemDiscount(item.id, value, discountType, discountReasonCode);
                                        setShowDiscountDialog(false);
                                        setSelectedItemForDiscount(null);
                                        setDiscountValue("");
                                        setDiscountReasonCode("");
                                      }
                                    }}
                                    placeholder="e.g., Damaged Package, Loyalty Discount"
                                    required
                                  />
                                </div>
                                <div className="flex gap-2">
                                  <Button onClick={() => {
                                    const value = parseFloat(discountValue) || 0;
                                    if (discountReasonCode.trim()) {
                                      applyItemDiscount(item.id, value, discountType, discountReasonCode);
                                      setShowDiscountDialog(false);
                                      setSelectedItemForDiscount(null);
                                      setDiscountValue("");
                                      setDiscountReasonCode("");
                                    }
                                  }} className="flex-1" disabled={!discountReasonCode.trim()}>
                                    Apply Discount
                                  </Button>
                                  <Button variant="outline" onClick={() => {
                                    removeItemDiscount(item.id);
                                    setShowDiscountDialog(false);
                                    setSelectedItemForDiscount(null);
                                  }}>
                                    Remove
                                  </Button>
                                </div>
                              </div>
                            </DialogContent>
                          </Dialog>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => removeFromCart(item.id)}
                          >
                            <Trash2 className="w-3 h-3" />
                          </Button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>

                <Separator className="mb-4" />

                <div className="space-y-2 mb-6">
                  <div className="flex justify-between">
                    <span>Subtotal</span>
                    <span>${subtotal.toFixed(2)}</span>
                  </div>
                  {cartDiscount && (
                    <div className="flex justify-between text-success">
                      <div className="flex items-center gap-1">
                        <span>Cart Discount ({cartDiscount.label})</span>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={removeCartDiscount}
                          className="h-4 w-4 p-0"
                        >
                          <Trash2 className="w-3 h-3" />
                        </Button>
                      </div>
                      <span>-${getCartDiscountAmount().toFixed(2)}</span>
                    </div>
                  )}
                  {untaxedSubtotal > 0 && (
                    <div className="text-xs text-muted-foreground">
                      <div className="flex justify-between">
                        <span>Taxable Items:</span>
                        <span>${(taxableSubtotal - (getCartDiscountAmount() * (taxableSubtotal / subtotal))).toFixed(2)}</span>
                      </div>
                      <div className="flex justify-between">
                        <span>Untaxed Items:</span>
                        <span>${(untaxedSubtotal - (getCartDiscountAmount() * (untaxedSubtotal / subtotal))).toFixed(2)}</span>
                      </div>
                    </div>
                  )}
                  <div className="flex justify-between">
                    <span>Tax (20%)</span>
                    <span>${tax.toFixed(2)}</span>
                  </div>
                  <Separator />
                  <div className="flex justify-between font-semibold text-lg">
                    <span>Total</span>
                    <span>${total.toFixed(2)}</span>
                  </div>
                </div>

                <div className="space-y-3">
                  <div className="grid grid-cols-2 gap-2">
                    <Button
                      className="w-full"
                      size="lg"
                      onClick={() => {
                        setPaymentMethod('cash');
                        setShowPaymentAmountDialog(true);
                      }}
                      disabled={cart.length === 0 || !saleStarted}
                    >
                      <CreditCard className="w-4 h-4 mr-2" />
                      Cash
                    </Button>
                    <Button
                      className="w-full"
                      size="lg"
                      onClick={() => {
                        setPaymentMethod('debit');
                        setShowDebitDialog(true);
                      }}
                      disabled={cart.length === 0 || !saleStarted}
                    >
                      <CreditCard className="w-4 h-4 mr-2" />
                      Debit Card
                    </Button>
                  </div>
                  <Button variant="outline" className="w-full">
                    Save Order
                  </Button>
                </div>
              </>
            )}
          </div>
        </main>

        {/* Metrc Compliance Dialog */}
        <Dialog open={showMetrcDialog} onOpenChange={setShowMetrcDialog}>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Metrc Compliance Check</DialogTitle>
            </DialogHeader>
            {selectedProductForMetrc && (
              <div className="space-y-4">
                <div className="p-4 bg-gray-50 rounded-lg">
                  <h3 className="font-semibold">{selectedProductForMetrc.name}</h3>
                  <p className="text-sm text-muted-foreground">{selectedProductForMetrc.category} • {selectedProductForMetrc.weight}</p>
                </div>

                <div className="space-y-3 text-sm">
                  <div className="flex justify-between">
                    <span>METRC Tag:</span>
                    <span className="font-mono">{selectedProductForMetrc.metrcTag}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Batch ID:</span>
                    <span>{selectedProductForMetrc.batchId}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Test Status:</span>
                    <Badge variant={selectedProductForMetrc.testResults?.tested ? "default" : "destructive"}>
                      {selectedProductForMetrc.testResults?.tested ? "Lab Tested" : "Not Tested"}
                    </Badge>
                  </div>
                  {selectedProductForMetrc.testResults?.tested && (
                    <>
                      <div className="flex justify-between">
                        <span>Lab:</span>
                        <span>{selectedProductForMetrc.testResults.labName}</span>
                      </div>
                      <div className="flex justify-between">
                        <span>Test Date:</span>
                        <span>{selectedProductForMetrc.testResults.testDate}</span>
                      </div>
                      <div className="flex justify-between">
                        <span>Contaminants:</span>
                        <Badge variant={selectedProductForMetrc.testResults.contaminants?.passed ? "default" : "destructive"}>
                          {selectedProductForMetrc.testResults.contaminants?.passed ? "Passed" : "Failed"}
                        </Badge>
                      </div>
                    </>
                  )}
                  <div className="flex justify-between">
                    <span>Packaged:</span>
                    <span>{selectedProductForMetrc.packagedDate}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Expires:</span>
                    <span>{selectedProductForMetrc.expirationDate}</span>
                  </div>
                </div>

                <div className="p-3 bg-blue-50 rounded text-sm">
                  <p className="font-medium text-blue-800">Oregon Compliance Check</p>
                  <p className="text-blue-700">This product is compliant with Oregon OLCC regulations and tracked in Metrc.</p>
                </div>

                <div className="flex gap-2">
                  <Button
                    onClick={() => confirmAddToCart(selectedProductForMetrc)}
                    className="flex-1"
                  >
                    Add to Cart
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => setShowMetrcDialog(false)}
                    className="flex-1"
                  >
                    Cancel
                  </Button>
                </div>
              </div>
            )}
          </DialogContent>
        </Dialog>

        {/* Enhanced Metrc Information Dialog */}
        <Dialog open={showEnhancedMetrcDialog} onOpenChange={setShowEnhancedMetrcDialog}>
          <DialogContent className="max-w-2xl" id="metrc-dialog-content">
            <DialogHeader>
              <DialogTitle className="flex items-center justify-between">
                Enhanced Metrc System Information
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => {
                    const printContent = document.getElementById('metrc-dialog-content');
                    if (printContent) {
                      const printWindow = window.open('', '_blank');
                      if (printWindow) {
                        printWindow.document.write(`
                          <html>
                            <head>
                              <title>Metrc Information - ${selectedProductForEnhancedMetrc?.name}</title>
                              <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 20px; }
                                .section { margin-bottom: 15px; }
                                .field { margin: 5px 0; }
                                .label { font-weight: bold; }
                                table { width: 100%; border-collapse: collapse; }
                                td { padding: 5px; border-bottom: 1px solid #ddd; }
                                .status { text-align: center; padding: 10px; margin: 5px; border: 1px solid #ccc; }
                              </style>
                            </head>
                            <body>
                              ${printContent.innerHTML}
                            </body>
                          </html>
                        `);
                        printWindow.document.close();
                        printWindow.print();
                        printWindow.close();
                      }
                    }
                  }}
                >
                  <Printer className="w-4 h-4 mr-2" />
                  Print
                </Button>
              </DialogTitle>
            </DialogHeader>
            {selectedProductForEnhancedMetrc && (
              <div className="space-y-6">
                <div className="p-4 bg-gray-50 rounded-lg">
                  <h3 className="font-semibold text-lg">{selectedProductForEnhancedMetrc.name}</h3>
                  <p className="text-sm text-muted-foreground">{selectedProductForEnhancedMetrc.category} • {selectedProductForEnhancedMetrc.weight}</p>
                </div>

                <div className="grid grid-cols-2 gap-6">
                  {/* Basic Information */}
                  <div className="space-y-3">
                    <h4 className="font-medium text-sm text-gray-700 uppercase tracking-wide">Basic Information</h4>
                    <div className="space-y-2 text-sm">
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">METRC Tag:</span>
                        <span className="font-mono text-xs">{selectedProductForEnhancedMetrc.metrcTag}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Batch ID:</span>
                        <span>{selectedProductForEnhancedMetrc.batchId}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Source Harvest:</span>
                        <span className="text-right">{selectedProductForEnhancedMetrc.sourceHarvest}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Supplier:</span>
                        <span className="text-right">{selectedProductForEnhancedMetrc.supplier}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Grower:</span>
                        <span className="text-right">{selectedProductForEnhancedMetrc.grower}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Harvest Date:</span>
                        <span>{selectedProductForEnhancedMetrc.harvestDate}</span>
                      </div>
                    </div>
                  </div>

                  {/* Test Results & Cannabinoids */}
                  <div className="space-y-3">
                    <h4 className="font-medium text-sm text-gray-700 uppercase tracking-wide">Test Results</h4>
                    <div className="space-y-2 text-sm">
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Test Date:</span>
                        <span>{selectedProductForEnhancedMetrc.testResults?.testDate || 'N/A'}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Lab:</span>
                        <span className="text-right">{selectedProductForEnhancedMetrc.testResults?.labName || 'N/A'}</span>
                      </div>
                      <div className="space-y-1">
                        <span className="text-muted-foreground text-xs">Cannabinoids:</span>
                        <div className="grid grid-cols-2 gap-1 text-xs">
                          <div className="flex justify-between">
                            <span>THC:</span>
                            <span>
                              {selectedProductForEnhancedMetrc.category === 'Edibles'
                                ? `${selectedProductForEnhancedMetrc.thcMg || 0}mg`
                                : `${selectedProductForEnhancedMetrc.thc || 0}%`}
                            </span>
                          </div>
                          <div className="flex justify-between">
                            <span>CBD:</span>
                            <span>
                              {selectedProductForEnhancedMetrc.category === 'Edibles'
                                ? `${selectedProductForEnhancedMetrc.cbdMg || 0}mg`
                                : `${selectedProductForEnhancedMetrc.cbd || 0}%`}
                            </span>
                          </div>
                          <div className="flex justify-between">
                            <span>CBG:</span>
                            <span>
                              {selectedProductForEnhancedMetrc.category === 'Edibles'
                                ? `${selectedProductForEnhancedMetrc.cbgMg || 0}mg`
                                : `${selectedProductForEnhancedMetrc.cbg || 0}%`}
                            </span>
                          </div>
                          <div className="flex justify-between">
                            <span>CBN:</span>
                            <span>
                              {selectedProductForEnhancedMetrc.category === 'Edibles'
                                ? `${selectedProductForEnhancedMetrc.cbnMg || 0}mg`
                                : `${selectedProductForEnhancedMetrc.cbn || 0}%`}
                            </span>
                          </div>
                          <div className="flex justify-between">
                            <span>CBC:</span>
                            <span>
                              {selectedProductForEnhancedMetrc.category === 'Edibles'
                                ? `${selectedProductForEnhancedMetrc.cbcMg || 0}mg`
                                : `${selectedProductForEnhancedMetrc.cbc || 0}%`}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Status Information */}
                <div className="space-y-3">
                  <h4 className="font-medium text-sm text-gray-700 uppercase tracking-wide">Status & Compliance</h4>
                  <div className="grid grid-cols-3 gap-4">
                    <div className="text-center p-3 bg-gray-50 rounded">
                      <p className="text-xs text-muted-foreground">Test Status</p>
                      <Badge variant={selectedProductForEnhancedMetrc.testResults?.tested ? "default" : "destructive"} className="mt-1">
                        {selectedProductForEnhancedMetrc.testResults?.tested ? "Lab Tested" : "Not Tested"}
                      </Badge>
                    </div>
                    <div className="text-center p-3 bg-gray-50 rounded">
                      <p className="text-xs text-muted-foreground">Contaminants</p>
                      <Badge variant={selectedProductForEnhancedMetrc.testResults?.contaminants?.passed ? "default" : "destructive"} className="mt-1">
                        {selectedProductForEnhancedMetrc.testResults?.contaminants?.passed ? "Passed" : "Failed"}
                      </Badge>
                    </div>
                    <div className="text-center p-3 bg-gray-50 rounded">
                      <p className="text-xs text-muted-foreground">Administrative Hold</p>
                      <Badge variant={selectedProductForEnhancedMetrc.administrativeHold ? "destructive" : "default"} className="mt-1">
                        {selectedProductForEnhancedMetrc.administrativeHold ? "Yes" : "No"}
                      </Badge>
                    </div>
                  </div>
                </div>

                {/* Dates */}
                <div className="space-y-3">
                  <h4 className="font-medium text-sm text-gray-700 uppercase tracking-wide">Important Dates</h4>
                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">Packaged:</span>
                      <span>{selectedProductForEnhancedMetrc.packagedDate}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">Expires:</span>
                      <span>{selectedProductForEnhancedMetrc.expirationDate}</span>
                    </div>
                  </div>
                </div>

                <div className="p-3 bg-blue-50 rounded text-sm">
                  <p className="font-medium text-blue-800">Oregon OLCC Compliance</p>
                  <p className="text-blue-700">This product is fully tracked in the Oregon Metrc system and compliant with all state regulations.</p>
                </div>

                <div className="flex justify-end">
                  <Button onClick={() => setShowEnhancedMetrcDialog(false)}>
                    Close
                  </Button>
                </div>
              </div>
            )}
          </DialogContent>
        </Dialog>

        {/* Employee PIN Dialog */}
        <Dialog open={showPinDialog} onOpenChange={setShowPinDialog}>
          <DialogContent className="max-w-sm">
            <DialogHeader>
              <DialogTitle>Employee PIN Required</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="employee-pin">Enter your 4-digit PIN</Label>
                <Input
                  id="employee-pin"
                  type="password"
                  value={employeePin}
                  onChange={(e) => setEmployeePin(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' && employeePin.length === 4) {
                      // Complete transaction
                      setCart([]);
                      setCartDiscount(null);
                      setShowPinDialog(false);
                      setShowReceiptDialog(true);
                      setEmployeePin("");
                      setPaymentAmount("");
                    }
                  }}
                  placeholder="****"
                  maxLength={4}
                  className="text-center text-2xl tracking-widest"
                  autoFocus
                />
              </div>
              <div className="flex gap-2">
                <Button
                  onClick={() => {
                    if (employeePin.length === 4) {
                      // Complete transaction
                      setCart([]);
                      setCartDiscount(null);
                      setShowPinDialog(false);
                      setShowReceiptDialog(true);
                      setEmployeePin("");
                      setPaymentAmount("");
                    }
                  }}
                  className="flex-1"
                  disabled={employeePin.length !== 4}
                >
                  Complete Sale
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowPinDialog(false);
                    setEmployeePin("");
                  }}
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Debit Card Dialog */}
        <Dialog open={showDebitDialog} onOpenChange={setShowDebitDialog}>
          <DialogContent className="max-w-sm">
            <DialogHeader>
              <DialogTitle>Debit Card Payment</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="last-four">Last 4 digits of card</Label>
                <Input
                  id="last-four"
                  type="text"
                  value={debitLastFour}
                  onChange={(e) => setDebitLastFour(e.target.value.replace(/\D/g, '').slice(0, 4))}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' && debitLastFour.length === 4 && employeePin.length === 4) {
                      navigate("/payment");
                      setShowDebitDialog(false);
                      setDebitLastFour("");
                      setEmployeePin("");
                    }
                  }}
                  placeholder="1234"
                  maxLength={4}
                  className="text-center text-2xl tracking-widest"
                  autoFocus
                />
              </div>
              <div className="text-xs text-muted-foreground">
                <p>• No cardholder name required</p>
                <p>�� No CVV required</p>
                <p>• No expiration date required</p>
              </div>
              <div>
                <Label htmlFor="debit-pin">Employee PIN</Label>
                <Input
                  id="debit-pin"
                  type="password"
                  value={employeePin}
                  onChange={(e) => setEmployeePin(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' && debitLastFour.length === 4 && employeePin.length === 4) {
                      navigate("/payment");
                      setShowDebitDialog(false);
                      setDebitLastFour("");
                      setEmployeePin("");
                    }
                  }}
                  placeholder="****"
                  maxLength={4}
                  className="text-center text-lg tracking-widest"
                />
              </div>
              <div className="flex gap-2">
                <Button
                  onClick={() => {
                    if (debitLastFour.length === 4 && employeePin.length === 4) {
                      // Process debit payment
                      navigate("/payment");
                      setShowDebitDialog(false);
                      setDebitLastFour("");
                      setEmployeePin("");
                    }
                  }}
                  className="flex-1"
                  disabled={debitLastFour.length !== 4 || employeePin.length !== 4}
                >
                  Complete Sale
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowDebitDialog(false);
                    setDebitLastFour("");
                    setEmployeePin("");
                  }}
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Payment Amount Dialog */}
        <Dialog open={showPaymentAmountDialog} onOpenChange={setShowPaymentAmountDialog}>
          <DialogContent className="max-w-sm">
            <DialogHeader>
              <DialogTitle>Enter Payment Amount</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="p-4 bg-gray-50 rounded-lg">
                <div className="text-lg font-semibold">Total: ${total.toFixed(2)}</div>
                {untaxedSubtotal > 0 && (
                  <div className="text-sm text-muted-foreground">
                    <div>Taxable: ${(taxableSubtotal - (getCartDiscountAmount() * (taxableSubtotal / subtotal))).toFixed(2)}</div>
                    <div>Untaxed: ${(untaxedSubtotal - (getCartDiscountAmount() * (untaxedSubtotal / subtotal))).toFixed(2)}</div>
                  </div>
                )}
              </div>
              <div>
                <Label htmlFor="payment-amount">Amount Received</Label>
                <Input
                  id="payment-amount"
                  type="number"
                  step="0.01"
                  value={paymentAmount}
                  onChange={(e) => setPaymentAmount(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' && parseFloat(paymentAmount) >= total) {
                      const transaction = {
                        id: Date.now().toString(),
                        items: cart,
                        subtotal,
                        tax,
                        total,
                        paymentAmount: parseFloat(paymentAmount),
                        change: parseFloat(paymentAmount) - total,
                        paymentMethod,
                        discounts: {
                          cart: cartDiscount,
                          items: cart.filter(item => item.discount > 0)
                        },
                        date: new Date().toLocaleString(),
                        employeePin
                      };
                      setLastTransaction(transaction);
                      setShowPaymentAmountDialog(false);
                      setShowPinDialog(true);
                    }
                  }}
                  placeholder="0.00"
                  className="text-center text-lg"
                  autoFocus
                />
              </div>
              {paymentAmount && (
                <div className="p-3 bg-blue-50 rounded">
                  <div className="text-sm">
                    <div>Amount Received: ${parseFloat(paymentAmount || '0').toFixed(2)}</div>
                    <div className="font-semibold">
                      Change Due: ${Math.max(0, parseFloat(paymentAmount || '0') - total).toFixed(2)}
                    </div>
                  </div>
                </div>
              )}
              <div className="flex gap-2">
                <Button
                  onClick={() => {
                    if (parseFloat(paymentAmount) >= total) {
                      const transaction = {
                        id: Date.now().toString(),
                        items: cart,
                        subtotal,
                        tax,
                        total,
                        paymentAmount: parseFloat(paymentAmount),
                        change: parseFloat(paymentAmount) - total,
                        paymentMethod,
                        discounts: {
                          cart: cartDiscount,
                          items: cart.filter(item => item.discount > 0)
                        },
                        date: new Date().toLocaleString()
                      };
                      setLastTransaction(transaction);
                      setShowPaymentAmountDialog(false);
                      setShowPinDialog(true);
                    }
                  }}
                  className="flex-1"
                  disabled={!paymentAmount || parseFloat(paymentAmount) < total}
                >
                  Continue to PIN
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowPaymentAmountDialog(false);
                    setPaymentAmount("");
                  }}
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* New Sale Dialog */}
        <Dialog open={showNewSaleDialog} onOpenChange={setShowNewSaleDialog}>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Start New Sale</DialogTitle>
            </DialogHeader>
            <div className="space-y-6">
              <div>
                <Label className="text-base font-medium">Customer Type</Label>
                <div className="mt-3 space-y-3">
                  <div className="flex items-center space-x-2">
                    <input
                      type="radio"
                      id="recreational"
                      name="customerType"
                      value="recreational"
                      checked={newSaleCustomerType === "recreational"}
                      onChange={(e) => setNewSaleCustomerType(e.target.value as "recreational")}
                      className="w-4 h-4"
                    />
                    <Label htmlFor="recreational" className="font-normal">
                      Recreational Customer (21+)
                    </Label>
                  </div>
                  <div className="flex items-center space-x-2">
                    <input
                      type="radio"
                      id="medical"
                      name="customerType"
                      value="medical"
                      checked={newSaleCustomerType === "medical"}
                      onChange={(e) => setNewSaleCustomerType(e.target.value as "medical")}
                      className="w-4 h-4"
                    />
                    <Label htmlFor="medical" className="font-normal">
                      Medical Patient
                    </Label>
                  </div>
                </div>
              </div>

              {newSaleCustomerType === "recreational" && (
                <div className="space-y-4">
                  <div className="border-t pt-4">
                    <div className="flex items-start space-x-2">
                      <input
                        type="checkbox"
                        id="customerIdVerified"
                        checked={customerInfo.isVerified}
                        onChange={(e) => setCustomerInfo(prev => ({...prev, isVerified: e.target.checked}))}
                        className="mt-1 w-4 h-4"
                        required
                      />
                      <Label htmlFor="customerIdVerified" className="text-sm font-medium text-red-600">
                        Customer ID Verified (Required) *
                      </Label>
                    </div>
                    {!customerInfo.isVerified && (
                      <div className="text-sm text-red-600 bg-red-50 p-2 rounded mt-2">
                        You must verify the customer's ID before proceeding with the sale.
                      </div>
                    )}
                  </div>
                </div>
              )}

              {newSaleCustomerType === "medical" && (
                <div className="space-y-4">
                  <div className="border-t pt-4">
                    <Label className="text-base font-medium">Customer Information</Label>
                    <div className="mt-3 space-y-3">
                      <div className="grid grid-cols-2 gap-3">
                        <div>
                          <Label htmlFor="customerName">Customer Name</Label>
                          <Input
                            id="customerName"
                            placeholder="Enter customer name"
                            value={customerInfo.name}
                            onChange={(e) => setCustomerInfo(prev => ({ ...prev, name: e.target.value }))}
                          />
                        </div>
                        <div>
                          <Label htmlFor="customerPhone">Phone Number</Label>
                          <Input
                            id="customerPhone"
                            placeholder="(555) 123-4567"
                            value={customerInfo.phone}
                            onChange={(e) => {
                              const phone = e.target.value;
                              setCustomerInfo(prev => ({ ...prev, phone }));

                              // Auto-lookup loyalty customer by phone
                              if (phone.length >= 10) {
                                const loyaltyMatch = loyaltyCustomers.find(customer =>
                                  customer.phone.replace(/\D/g, '').includes(phone.replace(/\D/g, ''))
                                );
                                if (loyaltyMatch && !selectedLoyaltyCustomer) {
                                  setSelectedLoyaltyCustomer(loyaltyMatch);
                                  setCustomerInfo(prev => ({ ...prev, name: loyaltyMatch.name }));
                                }
                              }
                            }}
                          />
                        </div>
                      </div>
                    </div>

                    {/* Loyalty Status Indicator */}
                    {selectedLoyaltyCustomer && (
                      <div className="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div className="flex items-center gap-2">
                          <Star className="w-4 h-4 text-green-600" />
                          <span className="text-sm font-medium text-green-800">
                            Loyalty Member Found!
                          </span>
                        </div>
                        <div className="text-xs text-green-700 mt-1">
                          {selectedLoyaltyCustomer.name} - {selectedLoyaltyCustomer.tier} tier
                          ({selectedLoyaltyCustomer.pointsBalance} points)
                        </div>
                      </div>
                    )}
                  </div>

                  <div className="border-t pt-4">
                    <Label className="text-base font-medium">Medical Card Information</Label>
                    <div className="mt-3 space-y-3">
                      <div>
                        <Label htmlFor="medicalCardNumber">Medical Card Number</Label>
                        <Input
                          id="medicalCardNumber"
                          placeholder="Enter medical card number"
                          value={medicalCardInfo.number}
                          onChange={(e) => setMedicalCardInfo(prev => ({ ...prev, number: e.target.value }))}
                        />
                      </div>
                      <div className="grid grid-cols-2 gap-3">
                        <div>
                          <Label htmlFor="issueDate">Issue Date</Label>
                          <Input
                            id="issueDate"
                            type="date"
                            value={medicalCardInfo.issueDate}
                            onChange={(e) => setMedicalCardInfo(prev => ({ ...prev, issueDate: e.target.value }))}
                          />
                        </div>
                        <div>
                          <Label htmlFor="expirationDate">Expiration Date</Label>
                          <Input
                            id="expirationDate"
                            type="date"
                            value={medicalCardInfo.expirationDate}
                            onChange={(e) => setMedicalCardInfo(prev => ({ ...prev, expirationDate: e.target.value }))}
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  <div className="border-t pt-4">
                    <Label className="text-base font-medium">Caregiver Information (Optional)</Label>
                    <div className="mt-3 space-y-3">
                      <div>
                        <Label htmlFor="caregiverCardNumber">Caregiver Card Number</Label>
                        <Input
                          id="caregiverCardNumber"
                          placeholder="Enter caregiver card number (if applicable)"
                          value={caregiverCardInfo.number}
                          onChange={(e) => setCaregiverCardInfo(prev => ({ ...prev, number: e.target.value }))}
                        />
                      </div>
                      <div className="grid grid-cols-2 gap-3">
                        <div>
                          <Label htmlFor="caregiverIssueDate">Issue Date</Label>
                          <Input
                            id="caregiverIssueDate"
                            type="date"
                            value={caregiverCardInfo.issueDate}
                            onChange={(e) => setCaregiverCardInfo(prev => ({ ...prev, issueDate: e.target.value }))}
                          />
                        </div>
                        <div>
                          <Label htmlFor="caregiverExpirationDate">Expiration Date</Label>
                          <Input
                            id="caregiverExpirationDate"
                            type="date"
                            value={caregiverCardInfo.expirationDate}
                            onChange={(e) => setCaregiverCardInfo(prev => ({ ...prev, expirationDate: e.target.value }))}
                          />
                        </div>
                      </div>
                      <div>
                        <Label htmlFor="patientName">Patient Name (if caregiver)</Label>
                        <Input
                          id="patientName"
                          placeholder="Enter patient name"
                          value={caregiverCardInfo.patientName}
                          onChange={(e) => setCaregiverCardInfo(prev => ({ ...prev, patientName: e.target.value }))}
                        />
                      </div>
                    </div>
                  </div>

                  <div className="border-t pt-4">
                    <div className="flex items-start space-x-2">
                      <input
                        type="checkbox"
                        id="dataConsent"
                        checked={dataRetentionConsent}
                        onChange={(e) => setDataRetentionConsent(e.target.checked)}
                        className="mt-1 w-4 h-4"
                      />
                      <Label htmlFor="dataConsent" className="text-sm">
                        I consent to the retention of my data and sales history for compliance and future visits as required by Oregon state law and regulations.
                      </Label>
                    </div>
                  </div>
                </div>
              )}

              <div className="flex gap-3 pt-4">
                <Button
                  variant="outline"
                  className="flex-1"
                  onClick={() => {
                    setShowNewSaleDialog(false);
                    setNewSaleCustomerType("");
                    setMedicalCardInfo({ number: "", issueDate: "", expirationDate: "" });
                    setCaregiverCardInfo({ number: "", issueDate: "", expirationDate: "", patientName: "" });
                    setDataRetentionConsent(false);
                  }}
                >
                  Cancel
                </Button>
                <Button
                  className="flex-1"
                  onClick={handleCustomerTypeSubmit}
                  disabled={!newSaleCustomerType ||
                    (newSaleCustomerType === "recreational" && !customerInfo.isVerified) ||
                    (newSaleCustomerType === "medical" && (!medicalCardInfo.number || !medicalCardInfo.issueDate || !medicalCardInfo.expirationDate || !dataRetentionConsent))}
                >
                  Start Sale
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Receipt Dialog */}
        <Dialog open={showReceiptDialog} onOpenChange={setShowReceiptDialog}>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle className="flex items-center justify-between">
                Transaction Receipt
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => {
                    const printContent = document.getElementById('receipt-content');
                    if (printContent) {
                      const printWindow = window.open('', '_blank');
                      if (printWindow) {
                        printWindow.document.write(`
                          <html>
                            <head>
                              <title>Receipt - Transaction ${lastTransaction?.id}</title>
                              <style>
                                body { font-family: 'Courier New', monospace; margin: 20px; font-size: 12px; }
                                .receipt { width: 3in; margin: 0 auto; }
                                .header { text-align: center; margin-bottom: 20px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
                                .line-item { display: flex; justify-content: space-between; margin: 5px 0; }
                                .total-line { border-top: 1px dashed #000; padding-top: 10px; margin-top: 10px; font-weight: bold; }
                                .footer { text-align: center; margin-top: 20px; border-top: 1px dashed #000; padding-top: 10px; }
                              </style>
                            </head>
                            <body>
                              ${printContent.innerHTML}
                            </body>
                          </html>
                        `);
                        printWindow.document.close();
                        printWindow.print();
                        printWindow.close();
                      }
                    }
                  }}
                >
                  <Printer className="w-4 h-4 mr-2" />
                  Print
                </Button>
              </DialogTitle>
            </DialogHeader>
            {lastTransaction && (
              <div id="receipt-content" className="space-y-4">
                <div className="receipt">
                  <div className="header">
                    <div className="flex items-center justify-center mb-2">
                      <OregonLogo />
                    </div>
                    <h2 className="font-bold">CANNABEST POS</h2>
                    <div className="text-sm">Oregon Cannabis Retailer</div>
                    <div className="text-xs">{lastTransaction.date}</div>
                    <div className="text-xs">Transaction: {lastTransaction.id}</div>
                  </div>

                  {/* Medical Customer Information */}
                  {customerInfo.medicalCard && (
                    <div className="medical-info p-2 bg-blue-50 border border-blue-200 rounded text-xs">
                      <div className="font-semibold text-blue-800">MEDICAL PATIENT</div>
                      <div>Medical Card #: {customerInfo.medicalCard}</div>
                      <div>Tax Status: EXEMPT</div>
                    </div>
                  )}

                  <div className="space-y-2">
                    {lastTransaction.items.map((item: any) => (
                      <div key={item.id}>
                        <div className="line-item">
                          <span>{item.name}</span>
                          <span>${getItemTotal(item).toFixed(2)}</span>
                        </div>
                        <div className="text-xs text-gray-600 ml-2">
                          {item.quantity} × ${item.price.toFixed(2)} {item.weight}
                          {item.discount > 0 && (
                            <div>Discount: {item.discountType === 'percentage' ? `${item.discount}%` : `$${item.discount}`} ({item.discountReasonCode})</div>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>

                  <div className="space-y-1 mt-4">
                    <div className="line-item">
                      <span>Subtotal:</span>
                      <span>${lastTransaction.subtotal.toFixed(2)}</span>
                    </div>
                    {lastTransaction.discounts.cart && (
                      <div className="line-item text-sm">
                        <span>Cart Discount ({lastTransaction.discounts.cart.label}):</span>
                        <span>-${getCartDiscountAmount().toFixed(2)}</span>
                      </div>
                    )}
                    <div className="line-item">
                      <span>Tax ({(taxRate * 100).toFixed(1)}%):</span>
                      <span>${lastTransaction.tax.toFixed(2)}</span>
                    </div>
                    <div className="line-item total-line">
                      <span>TOTAL:</span>
                      <span>${lastTransaction.total.toFixed(2)}</span>
                    </div>
                    <div className="line-item">
                      <span>Payment ({lastTransaction.paymentMethod}):</span>
                      <span>${lastTransaction.paymentAmount.toFixed(2)}</span>
                    </div>
                    <div className="line-item">
                      <span>CHANGE:</span>
                      <span>${lastTransaction.change.toFixed(2)}</span>
                    </div>
                  </div>

                  <div className="footer">
                    <div className="text-xs">Thank you for shopping with us!</div>
                    <div className="text-xs">Oregon OLCC Licensed Retailer</div>
                  </div>
                </div>
              </div>
            )}
          </DialogContent>
        </Dialog>

        {/* Room Transfer Dialog */}
        <Dialog open={showRoomTransferDialog} onOpenChange={setShowRoomTransferDialog}>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                <Building className="w-5 h-5" />
                Room Transfer
              </DialogTitle>
            </DialogHeader>
            {selectedProductForTransfer && (
              <div className="space-y-4">
                <div className="p-4 bg-gray-50 rounded-lg">
                  <h3 className="font-semibold">{selectedProductForTransfer.name}</h3>
                  <p className="text-sm text-muted-foreground">
                    {selectedProductForTransfer.category} • {selectedProductForTransfer.weight}
                  </p>
                  <p className="text-xs text-muted-foreground mt-1">
                    METRC: {selectedProductForTransfer.metrcTag}
                  </p>
                </div>

                <div className="space-y-4">
                  <div>
                    <Label htmlFor="transfer-quantity">Transfer Quantity</Label>
                    <Input
                      id="transfer-quantity"
                      type="number"
                      step="0.01"
                      min="0.01"
                      value={transferQuantity}
                      onChange={(e) => setTransferQuantity(e.target.value)}
                      placeholder={selectedProductForTransfer.category === "Flower" ? "0.01" : "1"}
                    />
                  </div>

                  <div>
                    <Label htmlFor="from-room">From Room</Label>
                    <Select value={selectedFromRoom} onValueChange={setSelectedFromRoom}>
                      <SelectTrigger>
                        <SelectValue placeholder="Select source room" />
                      </SelectTrigger>
                      <SelectContent>
                        {availableRooms.filter(room => room.isActive).map(room => (
                          <SelectItem key={room.id} value={room.id}>
                            <div className="flex items-center justify-between w-full">
                              <span>{room.name}</span>
                              <span className="text-xs text-muted-foreground ml-2">
                                ({room.currentStock}/{room.maxCapacity})
                              </span>
                            </div>
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label htmlFor="to-room">To Room</Label>
                    <Select value={selectedToRoom} onValueChange={setSelectedToRoom}>
                      <SelectTrigger>
                        <SelectValue placeholder="Select destination room" />
                      </SelectTrigger>
                      <SelectContent>
                        {availableRooms.filter(room => room.isActive && room.id !== selectedFromRoom).map(room => (
                          <SelectItem key={room.id} value={room.id}>
                            <div className="flex items-center justify-between w-full">
                              <span>{room.name}</span>
                              <span className="text-xs text-muted-foreground ml-2">
                                ({room.currentStock}/{room.maxCapacity})
                              </span>
                            </div>
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label htmlFor="transfer-reason">Transfer Reason (Optional)</Label>
                    <Select value={transferReason} onValueChange={setTransferReason}>
                      <SelectTrigger>
                        <SelectValue placeholder="Select transfer reason" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="inventory-adjustment">Inventory Adjustment</SelectItem>
                        <SelectItem value="quality-control">Quality Control</SelectItem>
                        <SelectItem value="processing">Processing</SelectItem>
                        <SelectItem value="packaging">Packaging</SelectItem>
                        <SelectItem value="storage-optimization">Storage Optimization</SelectItem>
                        <SelectItem value="compliance-testing">Compliance Testing</SelectItem>
                        <SelectItem value="quarantine">Quarantine</SelectItem>
                        <SelectItem value="sales-floor-restock">Sales Floor Restock</SelectItem>
                        <SelectItem value="other">Other</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="p-3 bg-blue-50 rounded text-sm">
                  <p className="font-medium text-blue-800">Metrc Compliance</p>
                  <p className="text-blue-700 text-xs">
                    This transfer will be automatically reported to Metrc with tracking ID.
                  </p>
                </div>

                <div className="flex gap-2">
                  <Button
                    onClick={createRoomTransfer}
                    className="flex-1"
                    disabled={!transferQuantity || !selectedFromRoom || !selectedToRoom}
                  >
                    <ArrowRightLeft className="w-4 h-4 mr-2" />
                    Complete Transfer
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => {
                      setShowRoomTransferDialog(false);
                      setSelectedProductForTransfer(null);
                      setTransferQuantity("");
                      setSelectedFromRoom("");
                      setSelectedToRoom("");
                      setTransferReason("");
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

        {/* Queue Order Dialog */}
        <Dialog open={showQueueOrderDialog} onOpenChange={setShowQueueOrderDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Queue Order Ready</DialogTitle>
            </DialogHeader>
            {queueOrder && (
              <div className="space-y-4">
                <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                  <h3 className="font-semibold">Order #{queueOrder.orderNumber}</h3>
                  <p className="text-sm text-gray-600">Customer: {queueOrder.customerName}</p>
                  <p className="text-sm text-gray-600">Phone: {queueOrder.customerPhone}</p>
                  <p className="text-sm text-gray-600">Total: ${queueOrder.total.toFixed(2)}</p>
                </div>

                <div>
                  <p className="text-sm">
                    This order is ready to be processed. Would you like to load the customer information
                    and start the sale? You will need to manually add items to the cart.
                  </p>
                </div>

                <div className="flex gap-2">
                  <Button onClick={loadQueueOrder} className="flex-1">
                    <ShoppingCart className="w-4 h-4 mr-2" />
                    Load Order
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => {
                      setShowQueueOrderDialog(false);
                      setQueueOrder(null);
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

        {/* Edit Product Dialog */}
        <Dialog open={showEditProductDialog} onOpenChange={setShowEditProductDialog}>
          <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Edit Product - {selectedProductForEdit?.name}</DialogTitle>
            </DialogHeader>
            {selectedProductForEdit && (
              <EditProductForm
                product={selectedProductForEdit}
                onSave={updateProduct}
                onCancel={() => {
                  setShowEditProductDialog(false);
                  setSelectedProductForEdit(null);
                }}
              />
            )}
          </DialogContent>
        </Dialog>

        {/* Save Sale Dialog */}
        <Dialog open={showSaveSaleDialog} onOpenChange={setShowSaveSaleDialog}>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Save Sale for Later</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="sale-name">Sale Name *</Label>
                <Input
                  id="sale-name"
                  value={saleNameToSave}
                  onChange={(e) => setSaleNameToSave(e.target.value)}
                  placeholder="e.g., John's Order, Medical Patient #123"
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' && saleNameToSave.trim()) {
                      confirmSaveSale();
                    }
                  }}
                />
              </div>
              <div>
                <Label htmlFor="sale-notes">Notes (Optional)</Label>
                <Textarea
                  id="sale-notes"
                  value={saleNotesToSave}
                  onChange={(e) => setSaleNotesToSave(e.target.value)}
                  placeholder="Add any special instructions or notes about this sale..."
                  rows={3}
                />
              </div>
              <div className="p-3 bg-blue-50 rounded-lg">
                <div className="text-sm font-medium text-blue-800 mb-1">Sale Summary:</div>
                <div className="text-xs text-blue-700">
                  • Items: {cart.reduce((sum, item) => sum + item.quantity, 0)}
                  • Customer: {customerType === 'medical' ? 'Medical' : 'Recreational'}
                  {selectedLoyaltyCustomer && (
                    <div>• Loyalty: {selectedLoyaltyCustomer.name} ({selectedLoyaltyCustomer.tier})</div>
                  )}
                </div>
              </div>
              <div className="flex gap-2">
                <Button onClick={confirmSaveSale} className="flex-1" disabled={!saleNameToSave.trim()}>
                  <Database className="w-4 h-4 mr-2" />
                  Save Sale
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowSaveSaleDialog(false);
                    setSaleNameToSave("");
                    setSaleNotesToSave("");
                  }}
                  className="flex-1"
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Saved Sales Dialog */}
        <Dialog open={showSavedSalesDialog} onOpenChange={setShowSavedSalesDialog}>
          <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Saved Sales ({savedSales.length})</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              {savedSales.length === 0 ? (
                <div className="text-center py-8">
                  <Database className="w-12 h-12 text-muted-foreground mx-auto mb-3" />
                  <p className="text-muted-foreground">No saved sales found.</p>
                  <p className="text-sm text-muted-foreground">Start a sale and save it for later to see it here.</p>
                </div>
              ) : (
                <div className="space-y-3">
                  {savedSales.map((sale) => (
                    <Card key={sale.id} className="p-4">
                      <div className="flex items-center justify-between mb-3">
                        <div>
                          <h3 className="font-semibold">{sale.name}</h3>
                          <div className="text-sm text-muted-foreground">
                            Saved by {sale.employeeName} on {new Date(sale.saveDate).toLocaleDateString()} at {new Date(sale.saveDate).toLocaleTimeString()}
                          </div>
                        </div>
                        <div className="text-right">
                          <div className="font-semibold">${sale.totalAmount.toFixed(2)}</div>
                          <div className="text-sm text-muted-foreground">{sale.totalItems} items</div>
                        </div>
                      </div>

                      <div className="grid grid-cols-2 gap-4 text-sm mb-3">
                        <div>
                          <span className="font-medium">Customer:</span> {sale.customerType === 'medical' ? 'Medical' : 'Recreational'}
                        </div>
                        <div>
                          <span className="font-medium">Loyalty:</span> {sale.selectedLoyaltyCustomer ? `${sale.selectedLoyaltyCustomer.name} (${sale.selectedLoyaltyCustomer.tier})` : 'None'}
                        </div>
                      </div>

                      {sale.notes && (
                        <div className="p-2 bg-gray-50 rounded text-sm mb-3">
                          <span className="font-medium">Notes:</span> {sale.notes}
                        </div>
                      )}

                      <div className="border-t pt-3">
                        <div className="text-sm font-medium mb-2">Items in Sale:</div>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                          {sale.cart.slice(0, 6).map((item, index) => (
                            <div key={index} className="flex justify-between">
                              <span>{item.name} x{item.quantity}</span>
                              <span>${(item.price * item.quantity).toFixed(2)}</span>
                            </div>
                          ))}
                          {sale.cart.length > 6 && (
                            <div className="text-muted-foreground">+ {sale.cart.length - 6} more items...</div>
                          )}
                        </div>
                      </div>

                      <div className="flex gap-2 mt-4">
                        <Button
                          size="sm"
                          onClick={() => loadSavedSale(sale)}
                          className="flex-1"
                        >
                          <ShoppingCart className="w-4 h-4 mr-2" />
                          Load Sale
                        </Button>
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => deleteSavedSale(sale.id)}
                          className="text-red-600 hover:text-red-700"
                        >
                          <Trash2 className="w-4 h-4" />
                        </Button>
                      </div>
                    </Card>
                  ))}
                </div>
              )}
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  );
}
