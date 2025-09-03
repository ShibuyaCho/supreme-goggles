import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Checkbox } from "@/components/ui/checkbox";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import {
  Settings as SettingsIcon,
  Clock,
  Globe,
  DollarSign,
  Tag,
  Trash2,
  Store,
  Building2,
  Plus,
  Edit,
  Save,
  X,
  Monitor,
  Grid,
  List,
  Eye
} from "lucide-react";

interface StoreHours {
  day: string;
  isOpen: boolean;
  openTime: string;
  closeTime: string;
}

interface StoreSettings {
  storeName: string;
  website: string;
  taxRate: number;
  autoDeleteZeroQuantity: boolean;
  autoDeleteZeroDays: number;
  exitLabelCategories: string[];
  hours: StoreHours[];
  minimumPriceEnabled: boolean;
  minimumPriceCategories: string[];
  minimumPriceAmount: number;
  inventoryViewMode: 'cards' | 'list';
  expandableCart: boolean;
}

interface Store {
  id: string;
  name: string;
  address: string;
  phone: string;
  status: 'active' | 'inactive';
  settings: StoreSettings;
}

const defaultHours: StoreHours[] = [
  { day: "Monday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Tuesday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Wednesday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Thursday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Friday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Saturday", isOpen: true, openTime: "10:00", closeTime: "20:00" },
  { day: "Sunday", isOpen: true, openTime: "11:00", closeTime: "19:00" }
];

const availableCategories = [
  "Flower", "Pre-Rolls", "Concentrates", "Edibles", "Topicals", 
  "Tinctures", "Accessories", "Hemp", "Paraphernalia", "Clones"
];

export default function Settings() {
  const [currentStore, setCurrentStore] = useState<Store>(() => {
    // Try to load settings from localStorage
    const defaultStore = {
      id: "1",
      name: "Cannabest Dispensary - Main",
      address: "123 Cannabis St, Portland, OR 97201",
      phone: "(503) 555-0123",
      status: "active" as const,
      settings: {
        storeName: "Cannabest Dispensary - Main",
        website: "https://cannabest.com",
        taxRate: 17.0,
        autoDeleteZeroQuantity: false,
        autoDeleteZeroDays: 1,
        exitLabelCategories: ["Flower", "Pre-Rolls", "Concentrates", "Edibles"],
        hours: defaultHours,
        minimumPriceEnabled: false,
        minimumPriceCategories: [],
        minimumPriceAmount: 0.01,
        inventoryViewMode: 'cards' as const,
        expandableCart: true
      }
    };

    try {
      const savedSettings = localStorage.getItem('cannabest-store-settings');
      if (savedSettings) {
        const settings = JSON.parse(savedSettings);
        return {
          ...defaultStore,
          settings: { ...defaultStore.settings, ...settings }
        };
      }
    } catch (error) {
      console.warn('Could not load settings from localStorage:', error);
    }

    return defaultStore;
  });

  const [stores] = useState<Store[]>([
    currentStore,
    {
      id: "2",
      name: "Cannabest Dispensary - Downtown",
      address: "456 Main St, Portland, OR 97202",
      phone: "(503) 555-0124",
      status: "active",
      settings: {
        storeName: "Cannabest Dispensary - Downtown",
        website: "https://cannabest.com/downtown",
        taxRate: 17.0,
        autoDeleteZeroQuantity: true,
        autoDeleteZeroDays: 2,
        exitLabelCategories: ["Flower", "Concentrates", "Edibles"],
        hours: defaultHours,
        minimumPriceEnabled: true,
        minimumPriceCategories: ["Flower", "Concentrates"],
        minimumPriceAmount: 1.00,
        inventoryViewMode: 'list',
        expandableCart: false
      }
    },
    {
      id: "3",
      name: "Cannabest Dispensary - Eastside",
      address: "789 Division St, Portland, OR 97203",
      phone: "(503) 555-0125",
      status: "inactive",
      settings: {
        storeName: "Cannabest Dispensary - Eastside",
        website: "https://cannabest.com/eastside",
        taxRate: 17.0,
        autoDeleteZeroQuantity: false,
        autoDeleteZeroDays: 1,
        exitLabelCategories: ["Flower", "Pre-Rolls", "Edibles", "Topicals"],
        hours: defaultHours,
        minimumPriceEnabled: false,
        minimumPriceCategories: [],
        minimumPriceAmount: 0.01,
        inventoryViewMode: 'cards',
        expandableCart: true
      }
    }
  ]);

  const [selectedTab, setSelectedTab] = useState("general");
  const [isEditing, setIsEditing] = useState(false);

  const updateStoreSettings = (updates: Partial<StoreSettings>) => {
    const newSettings = { ...currentStore.settings, ...updates };
    console.log('Settings: Updating store settings:', updates, 'New settings:', newSettings);

    setCurrentStore(prev => ({
      ...prev,
      settings: newSettings
    }));

    // Save to localStorage for persistence across pages
    try {
      localStorage.setItem('cannabest-store-settings', JSON.stringify(newSettings));
      console.log('Settings: Saved to localStorage:', newSettings);

      // Dispatch custom event to notify other components
      const event = new CustomEvent('settings-updated', {
        detail: newSettings
      });
      window.dispatchEvent(event);
      console.log('Settings: Dispatched settings-updated event:', event.detail);

      // Also dispatch a specific inventory view mode event
      if (updates.inventoryViewMode) {
        const inventoryEvent = new CustomEvent('inventory-view-changed', {
          detail: { viewMode: updates.inventoryViewMode }
        });
        window.dispatchEvent(inventoryEvent);
        console.log('Settings: Dispatched inventory-view-changed event:', inventoryEvent.detail);
      }
    } catch (error) {
      console.warn('Could not save settings to localStorage:', error);
    }
  };

  const updateHours = (dayIndex: number, updates: Partial<StoreHours>) => {
    const newHours = [...currentStore.settings.hours];
    newHours[dayIndex] = { ...newHours[dayIndex], ...updates };
    updateStoreSettings({ hours: newHours });
  };

  const toggleExitLabelCategory = (category: string) => {
    const current = currentStore.settings.exitLabelCategories;
    const updated = current.includes(category)
      ? current.filter(c => c !== category)
      : [...current, category];
    updateStoreSettings({ exitLabelCategories: updated });
  };

  const toggleMinimumPriceCategory = (category: string) => {
    const current = currentStore.settings.minimumPriceCategories;
    const updated = current.includes(category)
      ? current.filter(c => c !== category)
      : [...current, category];
    updateStoreSettings({ minimumPriceCategories: updated });
  };

  const saveSettings = () => {
    console.log("Saving settings:", currentStore.settings);
    setIsEditing(false);
    // Here you would save to your backend
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Settings</h1>
            <p className="text-sm opacity-80">Configure store operations and preferences</p>
          </div>
          <div className="flex items-center gap-4">
            <Select value={currentStore.id} onValueChange={(storeId) => {
              const store = stores.find(s => s.id === storeId);
              if (store) setCurrentStore(store);
            }}>
              <SelectTrigger className="w-64">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {stores.map(store => (
                  <SelectItem key={store.id} value={store.id}>
                    <div className="flex items-center gap-2">
                      <span>{store.name}</span>
                      <Badge variant={store.status === 'active' ? 'default' : 'secondary'}>
                        {store.status}
                      </Badge>
                    </div>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            {isEditing ? (
              <div className="flex gap-2">
                <Button onClick={saveSettings}>
                  <Save className="w-4 h-4 mr-2" />
                  Save Changes
                </Button>
                <Button variant="outline" onClick={() => setIsEditing(false)}>
                  <X className="w-4 h-4 mr-2" />
                  Cancel
                </Button>
              </div>
            ) : (
              <Button onClick={() => setIsEditing(true)}>
                <Edit className="w-4 h-4 mr-2" />
                Edit Settings
              </Button>
            )}
          </div>
        </div>
      </header>

      <div className="container mx-auto p-6">
        <Tabs value={selectedTab} onValueChange={setSelectedTab}>
          <TabsList className="grid w-full grid-cols-7">
            <TabsTrigger value="general">General</TabsTrigger>
            <TabsTrigger value="hours">Hours</TabsTrigger>
            <TabsTrigger value="tax">Tax & Exit Label Categories</TabsTrigger>
            <TabsTrigger value="pricing">Minimum Price</TabsTrigger>
            <TabsTrigger value="inventory">Display</TabsTrigger>
            <TabsTrigger value="management">Management</TabsTrigger>
            <TabsTrigger value="stores">Multi-Store</TabsTrigger>
          </TabsList>

          {/* General Settings */}
          <TabsContent value="general" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Store className="w-5 h-5" />
                  Store Information
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="store-name">Store Name</Label>
                    <Input
                      id="store-name"
                      value={currentStore.settings.storeName}
                      onChange={(e) => updateStoreSettings({ storeName: e.target.value })}
                      disabled={!isEditing}
                    />
                  </div>
                  <div>
                    <Label htmlFor="website">Website URL</Label>
                    <Input
                      id="website"
                      type="url"
                      value={currentStore.settings.website}
                      onChange={(e) => updateStoreSettings({ website: e.target.value })}
                      placeholder="https://yourstore.com"
                      disabled={!isEditing}
                    />
                  </div>
                </div>
                <div>
                  <Label>Store Address</Label>
                  <div className="text-sm text-gray-600 p-2 bg-gray-50 rounded">
                    {currentStore.address}
                  </div>
                </div>
                <div>
                  <Label>Phone Number</Label>
                  <div className="text-sm text-gray-600 p-2 bg-gray-50 rounded">
                    {currentStore.phone}
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Hours of Operation */}
          <TabsContent value="hours" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Clock className="w-5 h-5" />
                  Hours of Operation
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {currentStore.settings.hours.map((dayHours, index) => (
                    <div key={dayHours.day} className="flex items-center gap-4 p-3 border rounded-lg">
                      <div className="w-24 font-medium">{dayHours.day}</div>
                      <Switch
                        checked={dayHours.isOpen}
                        onCheckedChange={(checked) => updateHours(index, { isOpen: checked })}
                        disabled={!isEditing}
                      />
                      {dayHours.isOpen ? (
                        <div className="flex items-center gap-2">
                          <Input
                            type="time"
                            value={dayHours.openTime}
                            onChange={(e) => updateHours(index, { openTime: e.target.value })}
                            className="w-32"
                            disabled={!isEditing}
                          />
                          <span>to</span>
                          <Input
                            type="time"
                            value={dayHours.closeTime}
                            onChange={(e) => updateHours(index, { closeTime: e.target.value })}
                            className="w-32"
                            disabled={!isEditing}
                          />
                        </div>
                      ) : (
                        <span className="text-gray-500">Closed</span>
                      )}
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Tax & Pricing */}
          <TabsContent value="tax" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <DollarSign className="w-5 h-5" />
                  Tax Configuration
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <Label htmlFor="tax-rate">Tax Rate (%)</Label>
                  <Input
                    id="tax-rate"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    value={currentStore.settings.taxRate}
                    onChange={(e) => updateStoreSettings({ taxRate: parseFloat(e.target.value) || 0 })}
                    disabled={!isEditing}
                  />
                  <p className="text-xs text-gray-600 mt-1">
                    Current rate: {currentStore.settings.taxRate}% (Oregon standard rate is typically 17%)
                  </p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Tag className="w-5 h-5" />
                  Exit Label Categories
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div>
                  <Label>Categories that require exit labels</Label>
                  <p className="text-sm text-gray-600 mb-4">
                    Select which product categories should automatically print exit labels
                  </p>
                  <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    {availableCategories.map(category => (
                      <div key={category} className="flex items-center gap-2">
                        <Checkbox
                          id={category}
                          checked={currentStore.settings.exitLabelCategories.includes(category)}
                          onCheckedChange={() => toggleExitLabelCategory(category)}
                          disabled={!isEditing}
                        />
                        <Label htmlFor={category} className="text-sm">{category}</Label>
                      </div>
                    ))}
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Minimum Price Settings */}
          <TabsContent value="pricing" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <DollarSign className="w-5 h-5" />
                  Minimum Price Protection
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="p-4 border rounded-lg space-y-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h4 className="font-medium">Enable Minimum Price Protection</h4>
                      <p className="text-sm text-gray-600">
                        Prevent products from being sold below a specified minimum price
                      </p>
                    </div>
                    <Switch
                      checked={currentStore.settings.minimumPriceEnabled}
                      onCheckedChange={(checked) => updateStoreSettings({ minimumPriceEnabled: checked })}
                      disabled={!isEditing}
                    />
                  </div>

                  {currentStore.settings.minimumPriceEnabled && (
                    <>
                      <div>
                        <Label htmlFor="minimum-price-amount">Minimum Price ($)</Label>
                        <Input
                          id="minimum-price-amount"
                          type="number"
                          step="0.01"
                          min="0"
                          value={currentStore.settings.minimumPriceAmount}
                          onChange={(e) => updateStoreSettings({ minimumPriceAmount: parseFloat(e.target.value) || 0.01 })}
                          disabled={!isEditing}
                        />
                        <p className="text-xs text-gray-600 mt-1">
                          This minimum will apply to selected categories below
                        </p>
                      </div>

                      <div>
                        <Label>Categories Subject to Minimum Price</Label>
                        <p className="text-sm text-gray-600 mb-4">
                          Select which product categories should have minimum price protection
                        </p>
                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                          {availableCategories.map(category => (
                            <div key={category} className="flex items-center gap-2">
                              <Checkbox
                                id={`min-price-${category}`}
                                checked={currentStore.settings.minimumPriceCategories.includes(category)}
                                onCheckedChange={() => toggleMinimumPriceCategory(category)}
                                disabled={!isEditing}
                              />
                              <Label htmlFor={`min-price-${category}`} className="text-sm">{category}</Label>
                            </div>
                          ))}
                        </div>
                      </div>

                      <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p className="text-sm text-blue-800">
                          <strong>Current Setting:</strong> Products in {currentStore.settings.minimumPriceCategories.length} selected {currentStore.settings.minimumPriceCategories.length === 1 ? 'category' : 'categories'} cannot be sold below <strong>${currentStore.settings.minimumPriceAmount.toFixed(2)}</strong>
                        </p>
                        {currentStore.settings.minimumPriceCategories.length > 0 && (
                          <p className="text-sm text-blue-700 mt-1">
                            Protected categories: {currentStore.settings.minimumPriceCategories.join(', ')}
                          </p>
                        )}
                      </div>
                    </>
                  )}
                </div>

                <div className="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                  <p className="text-sm text-gray-700">
                    📝 <strong>Note:</strong> GLS (Green Leaf Special) products are exempt from minimum price restrictions regardless of category settings.
                  </p>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Inventory Settings */}
          <TabsContent value="inventory" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Monitor className="w-5 h-5" />
                  Inventory Display Preferences
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="p-4 border rounded-lg space-y-4">
                  <div>
                    <h4 className="font-medium mb-3">Inventory View Mode</h4>
                    <p className="text-sm text-gray-600 mb-4">
                      Choose how you want to view inventory items in the Products page
                    </p>
                    <div className="grid grid-cols-2 gap-4">
                      <div
                        className={`p-4 border rounded-lg cursor-pointer transition-all ${
                          currentStore.settings.inventoryViewMode === 'cards'
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-200 hover:border-gray-300'
                        }`}
                        onClick={() => isEditing && updateStoreSettings({ inventoryViewMode: 'cards' })}
                      >
                        <div className="flex items-center gap-3 mb-2">
                          <Grid className="w-5 h-5 text-blue-600" />
                          <span className="font-medium">Card View</span>
                          {currentStore.settings.inventoryViewMode === 'cards' && (
                            <Eye className="w-4 h-4 text-blue-600" />
                          )}
                        </div>
                        <p className="text-sm text-gray-600">
                          Display inventory items as cards with visual product information
                        </p>
                      </div>

                      <div
                        className={`p-4 border rounded-lg cursor-pointer transition-all ${
                          currentStore.settings.inventoryViewMode === 'list'
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-200 hover:border-gray-300'
                        }`}
                        onClick={() => isEditing && updateStoreSettings({ inventoryViewMode: 'list' })}
                      >
                        <div className="flex items-center gap-3 mb-2">
                          <List className="w-5 h-5 text-blue-600" />
                          <span className="font-medium">List View</span>
                          {currentStore.settings.inventoryViewMode === 'list' && (
                            <Eye className="w-4 h-4 text-blue-600" />
                          )}
                        </div>
                        <p className="text-sm text-gray-600">
                          Display inventory items as a compact list with detailed information
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <p className="text-sm text-blue-800">
                    <strong>Current Setting:</strong> {currentStore.settings.inventoryViewMode === 'cards' ? 'Card View' : 'List View'}
                  </p>
                  <p className="text-sm text-blue-700 mt-1">
                    This setting will change how inventory items are displayed in the Products page while keeping all the same information visible.
                  </p>
                </div>

                <div className="p-4 border rounded-lg space-y-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h4 className="font-medium">Expandable Cart</h4>
                      <p className="text-sm text-gray-600">
                        Enable cart to expand automatically when items are added during transactions
                      </p>
                    </div>
                    <Switch
                      checked={currentStore.settings.expandableCart}
                      onCheckedChange={(checked) => updateStoreSettings({ expandableCart: checked })}
                      disabled={!isEditing}
                    />
                  </div>
                  <div className="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <p className="text-sm text-gray-700">
                      📝 <strong>Note:</strong> When enabled, the shopping cart will automatically expand to show item details when products are added. When disabled, the cart remains compact until manually expanded.
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Inventory Management Settings */}
          <TabsContent value="management" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Trash2 className="w-5 h-5" />
                  Inventory Management
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="p-4 border rounded-lg space-y-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h4 className="font-medium">Auto-delete Zero Quantity Items</h4>
                      <p className="text-sm text-gray-600">
                        Automatically remove products from inventory when quantity stays at zero
                      </p>
                    </div>
                    <Switch
                      checked={currentStore.settings.autoDeleteZeroQuantity}
                      onCheckedChange={(checked) => updateStoreSettings({ autoDeleteZeroQuantity: checked })}
                      disabled={!isEditing}
                    />
                  </div>

                  {currentStore.settings.autoDeleteZeroQuantity && (
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <Label htmlFor="auto-delete-days">Days at Zero Before Deletion</Label>
                        <Input
                          id="auto-delete-days"
                          type="number"
                          min="1"
                          max="30"
                          value={currentStore.settings.autoDeleteZeroDays}
                          onChange={(e) => updateStoreSettings({ autoDeleteZeroDays: parseInt(e.target.value) || 1 })}
                          disabled={!isEditing}
                        />
                        <p className="text-xs text-gray-600 mt-1">
                          Default: 1 day (items deleted after being at zero for this many days)
                        </p>
                      </div>
                      <div className="flex items-center">
                        <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                          <p className="text-sm text-blue-800">
                            <strong>Current Setting:</strong> Items will be deleted after staying at zero quantity for <strong>{currentStore.settings.autoDeleteZeroDays} day{currentStore.settings.autoDeleteZeroDays !== 1 ? 's' : ''}</strong>
                          </p>
                        </div>
                      </div>
                    </div>
                  )}
                </div>

                {currentStore.settings.autoDeleteZeroQuantity && (
                  <div className="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p className="text-sm text-yellow-800">
                      ⚠️ Warning: Items will be permanently removed from inventory after staying at zero quantity for the specified number of days. This action cannot be undone.
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          {/* Multi-Store Management */}
          <TabsContent value="stores" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Building2 className="w-5 h-5" />
                  Franchise Management
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h4 className="font-medium">Store Locations</h4>
                      <p className="text-sm text-gray-600">Manage multiple store locations</p>
                    </div>
                    <Button variant="outline">
                      <Plus className="w-4 h-4 mr-2" />
                      Add Store
                    </Button>
                  </div>
                  
                  <div className="space-y-3">
                    {stores.map(store => (
                      <div key={store.id} className="flex items-center justify-between p-4 border rounded-lg">
                        <div className="flex-1">
                          <div className="flex items-center gap-3">
                            <h5 className="font-medium">{store.name}</h5>
                            <Badge variant={store.status === 'active' ? 'default' : 'secondary'}>
                              {store.status}
                            </Badge>
                            {store.id === currentStore.id && (
                              <Badge variant="outline">Current</Badge>
                            )}
                          </div>
                          <p className="text-sm text-gray-600">{store.address}</p>
                          <p className="text-sm text-gray-600">{store.phone}</p>
                        </div>
                        <div className="flex items-center gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => setCurrentStore(store)}
                          >
                            {store.id === currentStore.id ? "Current" : "Switch To"}
                          </Button>
                          <Button size="sm" variant="outline">
                            <Edit className="w-3 h-3" />
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
