import { useState, useEffect, useRef } from "react";
import axios from "axios";
// Use global SettingsClient when available
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
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
  Eye,
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
  inventoryViewMode: "cards" | "list";
  expandableCart: boolean;
}

interface Store {
  id: string;
  name: string;
  address: string;
  phone: string;
  status: "active" | "inactive";
  settings: StoreSettings;
}

const defaultHours: StoreHours[] = [
  { day: "Monday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Tuesday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Wednesday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Thursday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Friday", isOpen: true, openTime: "09:00", closeTime: "21:00" },
  { day: "Saturday", isOpen: true, openTime: "10:00", closeTime: "20:00" },
  { day: "Sunday", isOpen: true, openTime: "11:00", closeTime: "19:00" },
];

const availableCategories = [
  "Flower",
  "Pre-Rolls",
  "Concentrates",
  "Edibles",
  "Topicals",
  "Tinctures",
  "Accessories",
  "Hemp",
  "Paraphernalia",
  "Clones",
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
        inventoryViewMode: "cards" as const,
        expandableCart: true,
      },
    };

    try {
      const savedSettings = localStorage.getItem("cannabest-store-settings");
      if (savedSettings) {
        const settings = JSON.parse(savedSettings);
        return {
          ...defaultStore,
          settings: { ...defaultStore.settings, ...settings },
        };
      }
    } catch (error) {
      console.warn("Could not load settings from localStorage:", error);
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
        minimumPriceAmount: 1.0,
        inventoryViewMode: "list",
        expandableCart: false,
      },
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
        inventoryViewMode: "cards",
        expandableCart: true,
      },
    },
  ]);

  const [selectedTab, setSelectedTab] = useState("general");
  const [isEditing, setIsEditing] = useState(false);
  const saveTimer = useRef<number | null>(null);
  const loadedFromServer = useRef(false);

  const getStoreHeaders = () => {
    try {
      const raw = localStorage.getItem("pos_store");
      if (raw) {
        const store = JSON.parse(raw);
        if (store && store.id) {
          return {
            "X-Store-ID": String(store.id),
            Accept: "application/json",
          } as Record<string, string>;
        }
      }
    } catch {}
    return { Accept: "application/json" } as Record<string, string>;
  };

  const updateStoreSettings = (updates: Partial<StoreSettings>) => {
    const newSettings = { ...currentStore.settings, ...updates };
    console.log(
      "Settings: Updating store settings:",
      updates,
      "New settings:",
      newSettings,
    );

    setCurrentStore((prev) => ({
      ...prev,
      settings: newSettings,
    }));

    // Save to localStorage for persistence across pages
    try {
      localStorage.setItem(
        "cannabest-store-settings",
        JSON.stringify(newSettings),
      );
      console.log("Settings: Saved to localStorage:", newSettings);

      // Dispatch custom event to notify other components
      const event = new CustomEvent("settings-updated", {
        detail: newSettings,
      });
      window.dispatchEvent(event);
      console.log("Settings: Dispatched settings-updated event:", event.detail);

      // Also dispatch a specific inventory view mode event
      if (updates.inventoryViewMode) {
        const inventoryEvent = new CustomEvent("inventory-view-changed", {
          detail: { viewMode: updates.inventoryViewMode },
        });
        window.dispatchEvent(inventoryEvent);
        console.log(
          "Settings: Dispatched inventory-view-changed event:",
          inventoryEvent.detail,
        );
      }
    } catch (error) {
      console.warn("Could not save settings to localStorage:", error);
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
      ? current.filter((c) => c !== category)
      : [...current, category];
    updateStoreSettings({ exitLabelCategories: updated });
  };

  const toggleMinimumPriceCategory = (category: string) => {
    const current = currentStore.settings.minimumPriceCategories;
    const updated = current.includes(category)
      ? current.filter((c) => c !== category)
      : [...current, category];
    updateStoreSettings({ minimumPriceCategories: updated });
  };

  const saveToApi = async (settings: StoreSettings) => {
    // Base payload from this page's UI
    const basePayload: any = {
      // Store info
      store_name: settings.storeName,
      store_address: (settings as any).storeAddress ?? "",
      store_phone: (settings as any).storePhone ?? "",
      store_email: (settings as any).storeEmail ?? "",
      store_manager: (settings as any).storeManager ?? "",
      license_number: (settings as any).licenseNumber ?? "",
      website: settings.website,
      receipt_footer: (settings as any).receiptFooter ?? "",

      // Taxes (this page currently only edits the primary tax rate)
      sales_tax: Number(settings.taxRate) || 0,

      // Auto delete
      auto_delete_zero_quantity: !!settings.autoDeleteZeroQuantity,
      auto_delete_zero_days: Math.min(
        30,
        Math.max(1, Number(settings.autoDeleteZeroDays) || 1),
      ),

      // Exit labels & printing
      exit_label_categories: Array.isArray(settings.exitLabelCategories)
        ? settings.exitLabelCategories
        : [],
      receipt_autoprint: (settings as any).receiptAutoprint ?? false,
      receipt_categories_autoprint: Array.isArray(
        (settings as any).receiptCategoriesAutoprint,
      )
        ? (settings as any).receiptCategoriesAutoprint
        : [],
      receipt_paper_size: (settings as any).receiptPaperSize ?? "80mm",

      // Pricing
      minimum_price_enabled: !!settings.minimumPriceEnabled,
      minimum_price_categories: Array.isArray(settings.minimumPriceCategories)
        ? settings.minimumPriceCategories
        : [],
      minimum_price_amount: Number(settings.minimumPriceAmount) || 0,

      // Display & inventory
      inventory_view_mode: settings.inventoryViewMode,
      expandable_cart: !!settings.expandableCart,

      // Hours
      business_hours: settings.hours,
    };

    // Enrich with other sections (tax breakdowns, printing prefs, METRC, sales rules)
    let existing: any = {};
    try {
      const scAny: any = (window as any).SettingsClient;
      if (scAny) {
        const resp = await scAny.get(true);
        existing = (resp && resp.settings) || {};
      }
    } catch (_) {}

    // Pull additional details from local UI caches (if the modal/UI saved them previously)
    try {
      const taxRaw = localStorage.getItem("cannabisPOS-taxSettings") || "";
      if (taxRaw) {
        const t = JSON.parse(taxRaw);
        basePayload.cannabis_tax = Number(t.recreationalRate ?? t.cannabisRate ?? existing.cannabis_tax ?? 0) || 0;
        basePayload.medical_tax = Number(t.medicalRate ?? existing.medical_tax ?? 0) || 0;
        basePayload.excise_tax = Number(t.localRate ?? existing.excise_tax ?? 0) || 0;
        basePayload.sales_tax = Number(basePayload.sales_tax ?? t.stateRate ?? existing.sales_tax ?? 0) || 0;
        basePayload.tax_inclusive = !!(t.includeInPrice ?? existing.tax_inclusive);
      }
    } catch (_) {}
    try {
      const printRaw = localStorage.getItem("cannabisPOS-printSettings") || "";
      if (printRaw) {
        const p = JSON.parse(printRaw);
        basePayload.receipt_autoprint = !!(p.autoprint ?? existing.receipt_autoprint);
        basePayload.print_labels = !!(p.printLabels ?? existing.print_labels);
        basePayload.receipt_template = String(p.receiptTemplate ?? existing.receipt_template ?? "standard");
        basePayload.receipt_paper_size = String(p.paperSize ?? existing.receipt_paper_size ?? "80mm");
        if (Array.isArray(p.categoriesAutoprint)) basePayload.receipt_categories_autoprint = p.categoriesAutoprint;
      }
    } catch (_) {}
    try {
      const salesRaw = localStorage.getItem("cannabisPOS-salesSettings") || "";
      if (salesRaw) {
        const s = JSON.parse(salesRaw);
        basePayload.minimum_price_amount = Number(s.minimumSale ?? basePayload.minimum_price_amount ?? existing.minimum_price_amount ?? 0) || 0;
        basePayload.minimum_price_enabled = !!(s.enforceMinimumSale ?? basePayload.minimum_price_enabled ?? existing.minimum_price_enabled);
        basePayload.__ui_daily_limit = Number(s.dailyLimit ?? existing.__ui_daily_limit ?? existing.daily_limit ?? 0) || 0;
        basePayload.daily_limit = Number(s.dailyLimit ?? existing.daily_limit ?? 0) || 0;
        basePayload.require_customer = !!(s.requireCustomerInfo ?? existing.require_customer);
        basePayload.auto_delete_zero_quantity = !!(s.autoDeleteZeroQuantity ?? basePayload.auto_delete_zero_quantity ?? existing.auto_delete_zero_quantity);
        basePayload.auto_delete_zero_days = Math.min(30, Math.max(1, Number(s.autoDeleteZeroDays ?? basePayload.auto_delete_zero_days ?? existing.auto_delete_zero_days ?? 1) || 1));
      }
    } catch (_) {}

    // Preserve and include METRC integration fields and POS behavior/sales rules
    basePayload.metrc_enabled = existing.metrc_enabled ?? basePayload.metrc_enabled ?? false;
    basePayload.metrc_user_key = existing.metrc_user_key ?? basePayload.metrc_user_key ?? "";
    basePayload.metrc_vendor_key = existing.metrc_vendor_key ?? basePayload.metrc_vendor_key ?? "";
    basePayload.metrc_facility = existing.metrc_facility ?? basePayload.metrc_facility ?? "";
    basePayload.metrc_auto_push_sales = existing.metrc_auto_push_sales ?? basePayload.metrc_auto_push_sales ?? false;

    basePayload.require_customer = basePayload.require_customer ?? existing.require_customer ?? true;
    basePayload.age_verification = basePayload.age_verification ?? existing.age_verification ?? true;
    basePayload.limit_enforcement = basePayload.limit_enforcement ?? existing.limit_enforcement ?? true;
    basePayload.accept_cash = basePayload.accept_cash ?? existing.accept_cash ?? true;
    basePayload.accept_debit = basePayload.accept_debit ?? existing.accept_debit ?? true;
    basePayload.accept_check = basePayload.accept_check ?? existing.accept_check ?? false;
    basePayload.round_to_nearest = basePayload.round_to_nearest ?? existing.round_to_nearest ?? false;

    const fullPayload = { ...(existing || {}), ...basePayload };

    const sc: any = (window as any).SettingsClient;
    if (sc) {
      const res = await sc.save(fullPayload);
      if (!res || res.success !== true) throw new Error("save-failed");
    } else {
      await axios.post("/api/settings/pos", fullPayload, {
        headers: getStoreHeaders(),
      });
    }
  };

  const saveSettings = async () => {
    console.log("Saving settings:", currentStore.settings);
    await saveToApi(currentStore.settings);
    setIsEditing(false);
  };

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const sc: any = (window as any).SettingsClient;
        if (sc) {
          const resp = await sc.get(true);
          const data = resp?.settings || {};
          if (cancelled) return;
          // Map backend to UI
          const merged: Partial<StoreSettings> = {
            storeName: data.store_name ?? currentStore.settings.storeName,
            website: data.website ?? currentStore.settings.website,
            taxRate:
              Number(data.sales_tax ?? currentStore.settings.taxRate) || 0,
            autoDeleteZeroQuantity: !!data.auto_delete_zero_quantity,
            autoDeleteZeroDays: Math.min(
              30,
              Math.max(
                1,
                Number(
                  data.auto_delete_zero_days ||
                    currentStore.settings.autoDeleteZeroDays,
                ) || 1,
              ),
            ),
            exitLabelCategories: Array.isArray(data.exit_label_categories)
              ? data.exit_label_categories
              : currentStore.settings.exitLabelCategories,
            minimumPriceEnabled: !!data.minimum_price_enabled,
            minimumPriceCategories: Array.isArray(data.minimum_price_categories)
              ? data.minimum_price_categories
              : currentStore.settings.minimumPriceCategories,
            minimumPriceAmount:
              Number(
                data.minimum_price_amount ??
                  currentStore.settings.minimumPriceAmount,
              ) || currentStore.settings.minimumPriceAmount,
            inventoryViewMode:
              data.inventory_view_mode === "list" ||
              data.inventory_view_mode === "cards"
                ? data.inventory_view_mode
                : currentStore.settings.inventoryViewMode,
            expandableCart:
              data.expandable_cart ?? currentStore.settings.expandableCart,
            hours: Array.isArray(data.business_hours)
              ? data.business_hours
              : currentStore.settings.hours,
          };
          setCurrentStore((prev) => ({
            ...prev,
            settings: { ...prev.settings, ...merged },
          }));
          loadedFromServer.current = true;
        } else {
          const res = await axios.get("/api/settings/pos", {
            headers: getStoreHeaders(),
          });
          const data =
            res?.data?.settings && typeof res.data.settings === "object"
              ? res.data.settings
              : res.data || {};
          if (cancelled) return;
          const merged: Partial<StoreSettings> = {
            storeName: data.store_name ?? currentStore.settings.storeName,
            website: data.website ?? currentStore.settings.website,
            taxRate:
              Number(data.sales_tax ?? currentStore.settings.taxRate) || 0,
            autoDeleteZeroQuantity: !!data.auto_delete_zero_quantity,
            autoDeleteZeroDays: Math.min(
              30,
              Math.max(
                1,
                Number(
                  data.auto_delete_zero_days ||
                    currentStore.settings.autoDeleteZeroDays,
                ) || 1,
              ),
            ),
            exitLabelCategories: Array.isArray(data.exit_label_categories)
              ? data.exit_label_categories
              : currentStore.settings.exitLabelCategories,
            minimumPriceEnabled: !!data.minimum_price_enabled,
            minimumPriceCategories: Array.isArray(data.minimum_price_categories)
              ? data.minimum_price_categories
              : currentStore.settings.minimumPriceCategories,
            minimumPriceAmount:
              Number(
                data.minimum_price_amount ??
                  currentStore.settings.minimumPriceAmount,
              ) || currentStore.settings.minimumPriceAmount,
            inventoryViewMode:
              data.inventory_view_mode === "list" ||
              data.inventory_view_mode === "cards"
                ? data.inventory_view_mode
                : currentStore.settings.inventoryViewMode,
            expandableCart:
              data.expandable_cart ?? currentStore.settings.expandableCart,
            hours: Array.isArray(data.business_hours)
              ? data.business_hours
              : currentStore.settings.hours,
          };
          setCurrentStore((prev) => ({
            ...prev,
            settings: { ...prev.settings, ...merged },
          }));
          loadedFromServer.current = true;
        }
      } catch (_) {
        // ignore
      }
    })();
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    // Debounced autosave on settings change
    if (!loadedFromServer.current) return; // avoid autosaving defaults before load
    if (saveTimer.current) window.clearTimeout(saveTimer.current);
    saveTimer.current = window.setTimeout(() => {
      saveToApi(currentStore.settings).catch(() => {});
    }, 600);
    return () => {
      if (saveTimer.current) window.clearTimeout(saveTimer.current);
    };
  }, [currentStore.settings]);

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Settings</h1>
            <p className="text-sm opacity-80">
              Configure store operations and preferences
            </p>
          </div>
          <div className="flex items-center gap-4">
            <Select
              value={currentStore.id}
              onValueChange={(storeId) => {
                const store = stores.find((s) => s.id === storeId);
                if (store) setCurrentStore(store);
              }}
            >
              <SelectTrigger className="w-64">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {stores.map((store) => (
                  <SelectItem key={store.id} value={store.id}>
                    <div className="flex items-center gap-2">
                      <span>{store.name}</span>
                      <Badge
                        variant={
                          store.status === "active" ? "default" : "secondary"
                        }
                      >
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
                      onChange={(e) =>
                        updateStoreSettings({ storeName: e.target.value })
                      }
                      disabled={!isEditing}
                    />
                  </div>
                  <div>
                    <Label htmlFor="website">Website URL</Label>
                    <Input
                      id="website"
                      type="url"
                      value={currentStore.settings.website}
                      onChange={(e) =>
                        updateStoreSettings({ website: e.target.value })
                      }
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
                    <div
                      key={dayHours.day}
                      className="flex items-center gap-4 p-3 border rounded-lg"
                    >
                      <div className="w-24 font-medium">{dayHours.day}</div>
                      <Switch
                        checked={dayHours.isOpen}
                        onCheckedChange={(checked) =>
                          updateHours(index, { isOpen: checked })
                        }
                        disabled={!isEditing}
                      />
                      {dayHours.isOpen ? (
                        <div className="flex items-center gap-2">
                          <Input
                            type="time"
                            value={dayHours.openTime}
                            onChange={(e) =>
                              updateHours(index, { openTime: e.target.value })
                            }
                            className="w-32"
                            disabled={!isEditing}
                          />
                          <span>to</span>
                          <Input
                            type="time"
                            value={dayHours.closeTime}
                            onChange={(e) =>
                              updateHours(index, { closeTime: e.target.value })
                            }
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
                    onChange={(e) =>
                      updateStoreSettings({
                        taxRate: parseFloat(e.target.value) || 0,
                      })
                    }
                    disabled={!isEditing}
                  />
                  <p className="text-xs text-gray-600 mt-1">
                    Current rate: {currentStore.settings.taxRate}% (Oregon
                    standard rate is typically 17%)
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
                    Select which product categories should automatically print
                    exit labels
                  </p>
                  <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    {availableCategories.map((category) => (
                      <div key={category} className="flex items-center gap-2">
                        <Checkbox
                          id={category}
                          checked={currentStore.settings.exitLabelCategories.includes(
                            category,
                          )}
                          onCheckedChange={() =>
                            toggleExitLabelCategory(category)
                          }
                          disabled={!isEditing}
                        />
                        <Label htmlFor={category} className="text-sm">
                          {category}
                        </Label>
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
                      <h4 className="font-medium">
                        Enable Minimum Price Protection
                      </h4>
                      <p className="text-sm text-gray-600">
                        Prevent products from being sold below a specified
                        minimum price
                      </p>
                    </div>
                    <Switch
                      checked={currentStore.settings.minimumPriceEnabled}
                      onCheckedChange={(checked) =>
                        updateStoreSettings({ minimumPriceEnabled: checked })
                      }
                      disabled={!isEditing}
                    />
                  </div>

                  {currentStore.settings.minimumPriceEnabled && (
                    <>
                      <div>
                        <Label htmlFor="minimum-price-amount">
                          Minimum Price ($)
                        </Label>
                        <Input
                          id="minimum-price-amount"
                          type="number"
                          step="0.01"
                          min="0"
                          value={currentStore.settings.minimumPriceAmount}
                          onChange={(e) =>
                            updateStoreSettings({
                              minimumPriceAmount:
                                parseFloat(e.target.value) || 0.01,
                            })
                          }
                          disabled={!isEditing}
                        />
                        <p className="text-xs text-gray-600 mt-1">
                          This minimum will apply to selected categories below
                        </p>
                      </div>

                      <div>
                        <Label>Categories Subject to Minimum Price</Label>
                        <p className="text-sm text-gray-600 mb-4">
                          Select which product categories should have minimum
                          price protection
                        </p>
                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                          {availableCategories.map((category) => (
                            <div
                              key={category}
                              className="flex items-center gap-2"
                            >
                              <Checkbox
                                id={`min-price-${category}`}
                                checked={currentStore.settings.minimumPriceCategories.includes(
                                  category,
                                )}
                                onCheckedChange={() =>
                                  toggleMinimumPriceCategory(category)
                                }
                                disabled={!isEditing}
                              />
                              <Label
                                htmlFor={`min-price-${category}`}
                                className="text-sm"
                              >
                                {category}
                              </Label>
                            </div>
                          ))}
                        </div>
                      </div>

                      <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p className="text-sm text-blue-800">
                          <strong>Current Setting:</strong> Products in{" "}
                          {currentStore.settings.minimumPriceCategories.length}{" "}
                          selected{" "}
                          {currentStore.settings.minimumPriceCategories
                            .length === 1
                            ? "category"
                            : "categories"}{" "}
                          cannot be sold below{" "}
                          <strong>
                            $
                            {currentStore.settings.minimumPriceAmount.toFixed(
                              2,
                            )}
                          </strong>
                        </p>
                        {currentStore.settings.minimumPriceCategories.length >
                          0 && (
                          <p className="text-sm text-blue-700 mt-1">
                            Protected categories:{" "}
                            {currentStore.settings.minimumPriceCategories.join(
                              ", ",
                            )}
                          </p>
                        )}
                      </div>
                    </>
                  )}
                </div>

                <div className="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                  <p className="text-sm text-gray-700">
                    📝 <strong>Note:</strong> GLS (Green Leaf Special) products
                    are exempt from minimum price restrictions regardless of
                    category settings.
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
                      Choose how you want to view inventory items in the
                      Products page
                    </p>
                    <div className="grid grid-cols-2 gap-4">
                      <div
                        className={`p-4 border rounded-lg cursor-pointer transition-all ${
                          currentStore.settings.inventoryViewMode === "cards"
                            ? "border-blue-500 bg-blue-50"
                            : "border-gray-200 hover:border-gray-300"
                        }`}
                        onClick={() =>
                          isEditing &&
                          updateStoreSettings({ inventoryViewMode: "cards" })
                        }
                      >
                        <div className="flex items-center gap-3 mb-2">
                          <Grid className="w-5 h-5 text-blue-600" />
                          <span className="font-medium">Card View</span>
                          {currentStore.settings.inventoryViewMode ===
                            "cards" && (
                            <Eye className="w-4 h-4 text-blue-600" />
                          )}
                        </div>
                        <p className="text-sm text-gray-600">
                          Display inventory items as cards with visual product
                          information
                        </p>
                      </div>

                      <div
                        className={`p-4 border rounded-lg cursor-pointer transition-all ${
                          currentStore.settings.inventoryViewMode === "list"
                            ? "border-blue-500 bg-blue-50"
                            : "border-gray-200 hover:border-gray-300"
                        }`}
                        onClick={() =>
                          isEditing &&
                          updateStoreSettings({ inventoryViewMode: "list" })
                        }
                      >
                        <div className="flex items-center gap-3 mb-2">
                          <List className="w-5 h-5 text-blue-600" />
                          <span className="font-medium">List View</span>
                          {currentStore.settings.inventoryViewMode ===
                            "list" && <Eye className="w-4 h-4 text-blue-600" />}
                        </div>
                        <p className="text-sm text-gray-600">
                          Display inventory items as a compact list with
                          detailed information
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <p className="text-sm text-blue-800">
                    <strong>Current Setting:</strong>{" "}
                    {currentStore.settings.inventoryViewMode === "cards"
                      ? "Card View"
                      : "List View"}
                  </p>
                  <p className="text-sm text-blue-700 mt-1">
                    This setting will change how inventory items are displayed
                    in the Products page while keeping all the same information
                    visible.
                  </p>
                </div>

                <div className="p-4 border rounded-lg space-y-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h4 className="font-medium">Expandable Cart</h4>
                      <p className="text-sm text-gray-600">
                        Enable cart to expand automatically when items are added
                        during transactions
                      </p>
                    </div>
                    <Switch
                      checked={currentStore.settings.expandableCart}
                      onCheckedChange={(checked) =>
                        updateStoreSettings({ expandableCart: checked })
                      }
                      disabled={!isEditing}
                    />
                  </div>
                  <div className="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <p className="text-sm text-gray-700">
                      📝 <strong>Note:</strong> When enabled, the shopping cart
                      will automatically expand to show item details when
                      products are added. When disabled, the cart remains
                      compact until manually expanded.
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
                      <h4 className="font-medium">
                        Auto-delete Zero Quantity Items
                      </h4>
                      <p className="text-sm text-gray-600">
                        Automatically remove products from inventory when
                        quantity stays at zero
                      </p>
                    </div>
                    <Switch
                      checked={currentStore.settings.autoDeleteZeroQuantity}
                      onCheckedChange={(checked) =>
                        updateStoreSettings({ autoDeleteZeroQuantity: checked })
                      }
                      disabled={!isEditing}
                    />
                  </div>

                  {currentStore.settings.autoDeleteZeroQuantity && (
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <Label htmlFor="auto-delete-days">
                          Days at Zero Before Deletion
                        </Label>
                        <Input
                          id="auto-delete-days"
                          type="number"
                          min="1"
                          max="30"
                          value={currentStore.settings.autoDeleteZeroDays}
                          onChange={(e) =>
                            updateStoreSettings({
                              autoDeleteZeroDays: parseInt(e.target.value) || 1,
                            })
                          }
                          disabled={!isEditing}
                        />
                        <p className="text-xs text-gray-600 mt-1">
                          Default: 1 day (items deleted after being at zero for
                          this many days)
                        </p>
                      </div>
                      <div className="flex items-center">
                        <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                          <p className="text-sm text-blue-800">
                            <strong>Current Setting:</strong> Items will be
                            deleted after staying at zero quantity for{" "}
                            <strong>
                              {currentStore.settings.autoDeleteZeroDays} day
                              {currentStore.settings.autoDeleteZeroDays !== 1
                                ? "s"
                                : ""}
                            </strong>
                          </p>
                        </div>
                      </div>
                    </div>
                  )}
                </div>

                {currentStore.settings.autoDeleteZeroQuantity && (
                  <div className="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p className="text-sm text-yellow-800">
                      ⚠️ Warning: Items will be permanently removed from
                      inventory after staying at zero quantity for the specified
                      number of days. This action cannot be undone.
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
                      <p className="text-sm text-gray-600">
                        Manage multiple store locations
                      </p>
                    </div>
                    <Button variant="outline">
                      <Plus className="w-4 h-4 mr-2" />
                      Add Store
                    </Button>
                  </div>

                  <div className="space-y-3">
                    {stores.map((store) => (
                      <div
                        key={store.id}
                        className="flex items-center justify-between p-4 border rounded-lg"
                      >
                        <div className="flex-1">
                          <div className="flex items-center gap-3">
                            <h5 className="font-medium">{store.name}</h5>
                            <Badge
                              variant={
                                store.status === "active"
                                  ? "default"
                                  : "secondary"
                              }
                            >
                              {store.status}
                            </Badge>
                            {store.id === currentStore.id && (
                              <Badge variant="outline">Current</Badge>
                            )}
                          </div>
                          <p className="text-sm text-gray-600">
                            {store.address}
                          </p>
                          <p className="text-sm text-gray-600">{store.phone}</p>
                        </div>
                        <div className="flex items-center gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => setCurrentStore(store)}
                          >
                            {store.id === currentStore.id
                              ? "Current"
                              : "Switch To"}
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
