import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Switch } from "@/components/ui/switch";
import {
  Plus,
  Tag,
  Calendar,
  Percent,
  DollarSign,
  Mail,
  Edit,
  Trash2,
  Clock,
  Users,
  Package,
  Star,
} from "lucide-react";

interface ProductItem {
  id: number | string;
  name: string;
  sku?: string;
  category?: string;
}

interface Deal {
  categoryDiscounts?: Record<string, number>;
  itemDiscounts?: Record<string, number>;
  id: string;
  name: string;
  description: string;
  type: "percentage" | "fixed" | "bogo" | "bulk";
  discountValue: number;
  categories: string[];
  specificItems: string[];
  startDate: string;
  endDate: string;
  isActive: boolean;
  frequency: "always" | "daily" | "weekly" | "monthly" | "custom";
  dayOfWeek?: string;
  dayOfMonth?: number;
  emailCustomers: boolean;
  loyaltyOnly: boolean;
  medicalOnly: boolean;
  minimumPurchase?: number;
  minimumPurchaseType?: "dollars" | "grams";
  maxUses?: number;
  currentUses: number;
}

const availableCategories = [
  "Flower",
  "Pre-Rolls",
  "Infused",
  "Concentrates",
  "Extracts",
  "Edibles",
  "Topicals",
  "Tinctures",
  "Vapes",
  "Inhalable Cannabinoids",
  "Clones",
  "Hemp",
  "Paraphernalia",
  "Accessories",
];

const sampleProducts = [
  { id: "1", name: "Blue Dream", category: "Flower" },
  { id: "2", name: "OG Kush", category: "Flower" },
  { id: "3", name: "Strawberry Gummies", category: "Edibles" },
  { id: "4", name: "Sour Diesel Pre-Roll", category: "Pre-Rolls" },
  { id: "5", name: "Live Resin Cart", category: "Concentrates" },
];

const mockDeals: Deal[] = [];

export default function Deals() {
  const [deals, setDeals] = useState<Deal[]>([]);

  // Local shadow persistence (per-user + global) to retain fields not stored in Supabase
  const getUserId = () => {
    try {
      const u = JSON.parse(
        localStorage.getItem("pos_user") ||
          localStorage.getItem("user_data") ||
          "null",
      );
      return u?.id || "anon";
    } catch (_) {
      return "anon";
    }
  };
  const getStoreId = () => {
    try {
      const anyWin: any = window as any;
      let sid = typeof anyWin?.SettingsClient?.currentStoreId === 'function' ? anyWin.SettingsClient.currentStoreId() : '';
      if (!sid) {
        const raw = localStorage.getItem('pos_store');
        if (raw) sid = String(JSON.parse(raw)?.id || '');
      }
      return sid || 'default';
    } catch { return 'default'; }
  };
  const dealsUserKey = () => `cannabest-deals-${getUserId()}-${getStoreId()}`;
  const dealsGlobalKey = () => `cannabest-deals-${getStoreId()}`;

  const saveDealsLocal = (list: Deal[]) => {
    try {
      // Persist only for current store namespace to prevent cross-store bleeding
      localStorage.setItem(dealsUserKey(), JSON.stringify(list));
      localStorage.setItem(dealsGlobalKey(), JSON.stringify(list));
    } catch (_) {}
  };
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [selectedDeal, setSelectedDeal] = useState<Deal | null>(null);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [productSearch, setProductSearch] = useState("");
  const [productLoading, setProductLoading] = useState(false);
  const [productResults, setProductResults] = useState<ProductItem[]>([]);

  const [newDeal, setNewDeal] = useState<Partial<Deal>>({
    name: "",
    description: "",
    type: "percentage",
    discountValue: 0,
    categories: [],
    specificItems: [],
    startDate: new Date().toISOString().split("T")[0],
    endDate: "",
    isActive: true,
    frequency: "always",
    emailCustomers: false,
    loyaltyOnly: false,
    medicalOnly: false,
    minimumPurchaseType: "dollars",
    categoryDiscounts: {},
    itemDiscounts: {},
    currentUses: 0,
  });

  const loadProducts = async (q?: string) => {
    try {
      setProductLoading(true);
      const params = new URLSearchParams();
      params.set("status", "in_stock");
      const search = typeof q === "string" ? q : productSearch;
      if (search && search.trim() !== "") {
        params.set("search", search.trim());
      }
      const res = await fetch(`/api/products?${params.toString()}`, {
        headers: { Accept: "application/json" },
      });
      if (res.ok) {
        const data = await res.json();
        const items: ProductItem[] = Array.isArray(data?.data)
          ? data.data
          : Array.isArray(data)
            ? data
            : [];
        setProductResults(items);
      } else {
        setProductResults([]);
      }
    } catch (e) {
      setProductResults([]);
    } finally {
      setProductLoading(false);
    }
  };

  useEffect(() => {
    if (showCreateDialog || showEditDialog) {
      loadProducts();
    }
  }, [showCreateDialog, showEditDialog]);

  const mapApiDealToUi = (d: any): Deal => ({
    id: String(d.id),
    name: d.name || "",
    description: d.description || "",
    type: d.type === "fixed_amount" ? "fixed" : d.type || "percentage",
    discountValue: Number(d.value || 0),
    categories: Array.isArray(d.applicable_categories)
      ? d.applicable_categories
      : [],
    specificItems: Array.isArray(d.specific_items)
      ? d.specific_items.map((x: any) => String(x))
      : [],
    categoryDiscounts: (() => {
      const v = (d as any).category_discounts;
      if (!v) return {};
      if (typeof v === "string") {
        try {
          const x = JSON.parse(v);
          return x && typeof x === "object" ? x : {};
        } catch {
          return {};
        }
      }
      return v;
    })(),
    itemDiscounts: (() => {
      const v = (d as any).item_discounts;
      if (!v) return {};
      if (typeof v === "string") {
        try {
          const x = JSON.parse(v);
          return x && typeof x === "object" ? x : {};
        } catch {
          return {};
        }
      }
      return v;
    })(),
    startDate: d.start_date || "",
    endDate: d.end_date || "",
    isActive: !!d.is_active,
    frequency: d.frequency || "always",
    dayOfWeek: d.day_of_week || undefined,
    dayOfMonth: d.day_of_month || undefined,
    emailCustomers: !!d.email_customers,
    loyaltyOnly: !!d.loyalty_only,
    medicalOnly: !!d.medical_only,
    minimumPurchase: d.minimum_purchase ?? undefined,
    minimumPurchaseType: d.minimum_purchase_type || "dollars",
    maxUses: d.max_uses ?? undefined,
    currentUses: d.current_uses ?? 0,
  });

  const mapUiToApi = (f: Partial<Deal>) => ({
    name: f.name || "",
    description: f.description || "",
    type: f.type === "fixed" ? "fixed_amount" : f.type || "percentage",
    value: Number(f.discountValue || 0),
    frequency: f.frequency || "always",
    day_of_week: f.dayOfWeek || undefined,
    day_of_month: f.dayOfMonth || undefined,
    start_date: f.startDate || undefined,
    end_date: f.endDate || undefined,
    applicable_categories: f.categories || [],
    specific_items: (f.specificItems || [])
      .map((x) => Number(x))
      .filter((n) => !Number.isNaN(n)),
    category_discounts: f.categoryDiscounts || {},
    item_discounts: f.itemDiscounts || {},
    minimum_purchase: f.minimumPurchase ?? undefined,
    minimum_purchase_type: f.minimumPurchaseType || "dollars",
    max_uses: f.maxUses ?? undefined,
    email_customers: !!f.emailCustomers,
    loyalty_only: !!f.loyaltyOnly,
    medical_only: !!f.medicalOnly,
    is_active: !!f.isActive,
  });

  const storeHeaders = () => {
    try {
      const anyWin: any = window as any;
      let sid =
        typeof anyWin?.SettingsClient?.currentStoreId === "function"
          ? anyWin.SettingsClient.currentStoreId()
          : "";
      if (!sid) {
        try {
          const raw = localStorage.getItem("pos_store");
          if (raw) sid = String(JSON.parse(raw)?.id || "");
        } catch {}
      }
      if (!sid) sid = "default";
      let sname =
        typeof anyWin?.SettingsClient?.currentStoreName === "function"
          ? anyWin.SettingsClient.currentStoreName()
          : "";
      if (!sname) {
        try {
          const raw = localStorage.getItem("pos_store");
          if (raw) sname = String(JSON.parse(raw)?.name || "");
        } catch {}
      }
      const h: Record<string, string> = {
        Accept: "application/json",
        "X-Store-ID": String(sid),
      };
      if (sname) (h as any)["X-Store-Name"] = sname;
      return h;
    } catch {
      return { Accept: "application/json", "X-Store-ID": "default" } as any;
    }
  };

  useEffect(() => {
    const loadDeals = async () => {
      try {
        // Load shadow local for current store only
        let localA: Deal[] = [];
        let localB: Deal[] = [];
        try {
          const rawA = localStorage.getItem(dealsUserKey());
          const rawB = localStorage.getItem(dealsGlobalKey());
          localA = rawA ? JSON.parse(rawA) : [];
          localB = rawB ? JSON.parse(rawB) : [];
        } catch (_) {}
        const localMergedMap = new Map<string, Deal>();
        [
          ...(Array.isArray(localA) ? localA : []),
          ...(Array.isArray(localB) ? localB : []),
        ].forEach((d) => {
          if (!d) return;
          const id = String((d as any).id ?? "");
          if (!id) return;
          if (!localMergedMap.has(id)) localMergedMap.set(id, d as Deal);
        });

        const res = await fetch("/api/deals", {
          headers: { ...storeHeaders() },
        });
        const data = await res.json();
        const list = Array.isArray(data?.deals) ? data.deals : [];
        const fromApi = list.map(mapApiDealToUi);

        // Overlay API with any locally saved fields for the same id
        const overlayed = fromApi.map((d) => {
          const local = localMergedMap.get(String(d.id));
          if (!local) return d;
          return {
            ...d,
            description: local.description ?? d.description,
            categories: local.categories?.length
              ? local.categories
              : d.categories,
            specificItems: local.specificItems?.length
              ? local.specificItems
              : d.specificItems,
            categoryDiscounts: Object.keys(local.categoryDiscounts || {}).length
              ? local.categoryDiscounts
              : d.categoryDiscounts,
            itemDiscounts: Object.keys(local.itemDiscounts || {}).length
              ? local.itemDiscounts
              : d.itemDiscounts,
            startDate: local.startDate || d.startDate,
            endDate: local.endDate || d.endDate,
            frequency: local.frequency || d.frequency,
            dayOfWeek: local.dayOfWeek ?? d.dayOfWeek,
            dayOfMonth: local.dayOfMonth ?? d.dayOfMonth,
            minimumPurchase: local.minimumPurchase ?? d.minimumPurchase,
            minimumPurchaseType:
              local.minimumPurchaseType || d.minimumPurchaseType,
            maxUses: local.maxUses ?? d.maxUses,
          } as Deal;
        });

        // Do NOT include local deals not present in API across stores; trust server for visibility
        const mergedAll = [...overlayed];
        const seenAll = new Set<string>();
        setDeals(
          mergedAll.filter((d) => {
            const key = String(d.id);
            if (seenAll.has(key)) return false;
            seenAll.add(key);
            return true;
          }),
        );
      } catch {
        // Fallback to purely local
        try {
          const raw =
            localStorage.getItem(dealsUserKey()) ||
            localStorage.getItem(dealsGlobalKey()) ||
            "[]";
          const arr = JSON.parse(raw);
          setDeals(Array.isArray(arr) ? arr : []);
        } catch (_) {
          setDeals([]);
        }
      }
    };
    loadDeals();
  }, []);

  const createDeal = async () => {
    const payload = mapUiToApi(newDeal);
    try {
      const res = await fetch("/api/deals", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          ...storeHeaders(),
        },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (res.ok && data?.deal) {
        const base = mapApiDealToUi(data.deal);
        // Merge back fields that Supabase might not return
        const full: Deal = {
          ...base,
          description: newDeal.description ?? base.description,
          categories: newDeal.categories || base.categories,
          specificItems: newDeal.specificItems || base.specificItems,
          categoryDiscounts:
            newDeal.categoryDiscounts || base.categoryDiscounts,
          itemDiscounts: newDeal.itemDiscounts || base.itemDiscounts,
          startDate: newDeal.startDate || base.startDate,
          endDate: newDeal.endDate || base.endDate,
          frequency: newDeal.frequency || base.frequency,
          dayOfWeek: newDeal.dayOfWeek ?? base.dayOfWeek,
          dayOfMonth: newDeal.dayOfMonth ?? base.dayOfMonth,
          minimumPurchase: newDeal.minimumPurchase ?? base.minimumPurchase,
          minimumPurchaseType:
            newDeal.minimumPurchaseType || base.minimumPurchaseType,
          maxUses: newDeal.maxUses ?? base.maxUses,
        } as Deal;
        setDeals((prev) => {
          const next = [full, ...prev];
          saveDealsLocal(next);
          return next;
        });
        setShowCreateDialog(false);
        resetForm();
        return;
      }
      alert(data?.message || "Failed to create deal");
      return;
    } catch (e) {
      alert("Failed to create deal");
      return;
    }

    const deal: Deal = {
      id: Date.now().toString(),
      name: newDeal.name || "",
      description: newDeal.description || "",
      type: newDeal.type || "percentage",
      discountValue: newDeal.discountValue || 0,
      categories: newDeal.categories || [],
      specificItems: newDeal.specificItems || [],
      startDate: newDeal.startDate || "",
      endDate: newDeal.endDate || "",
      isActive: newDeal.isActive || false,
      frequency: newDeal.frequency || "always",
      dayOfWeek: newDeal.dayOfWeek,
      dayOfMonth: newDeal.dayOfMonth,
      emailCustomers: newDeal.emailCustomers || false,
      loyaltyOnly: newDeal.loyaltyOnly || false,
      medicalOnly: newDeal.medicalOnly || false,
      minimumPurchase: newDeal.minimumPurchase,
      maxUses: newDeal.maxUses,
      currentUses: 0,
    };

    setDeals((prev) => [...prev, deal]);
    setShowCreateDialog(false);
    resetForm();
  };

  const editDeal = async () => {
    if (
      !selectedDeal ||
      !newDeal.name ||
      !newDeal.description ||
      !newDeal.discountValue
    ) {
      alert("Please fill in all required fields");
      return;
    }

    const payload = mapUiToApi(newDeal);
    try {
      const res = await fetch(`/api/deals/${selectedDeal.id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          ...storeHeaders(),
        },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (res.ok && data?.deal) {
        const base = mapApiDealToUi(data.deal);
        const full: Deal = {
          ...base,
          description: newDeal.description ?? base.description,
          categories: newDeal.categories || base.categories,
          specificItems: newDeal.specificItems || base.specificItems,
          categoryDiscounts:
            newDeal.categoryDiscounts || base.categoryDiscounts,
          itemDiscounts: newDeal.itemDiscounts || base.itemDiscounts,
          startDate: newDeal.startDate || base.startDate,
          endDate: newDeal.endDate || base.endDate,
          frequency: newDeal.frequency || base.frequency,
          dayOfWeek: newDeal.dayOfWeek ?? base.dayOfWeek,
          dayOfMonth: newDeal.dayOfMonth ?? base.dayOfMonth,
          minimumPurchase: newDeal.minimumPurchase ?? base.minimumPurchase,
          minimumPurchaseType:
            newDeal.minimumPurchaseType || base.minimumPurchaseType,
          maxUses: newDeal.maxUses ?? base.maxUses,
        } as Deal;
        setDeals((prev) => {
          const next = prev.map((d) =>
            String(d.id) === String(full.id) ? full : d,
          );
          // de-duplicate by id
          const seen = new Set<string>();
          const deduped = next.filter((d) => {
            const k = String(d.id);
            if (seen.has(k)) return false;
            seen.add(k);
            return true;
          });
          saveDealsLocal(deduped);
          return deduped;
        });
        setShowEditDialog(false);
        setSelectedDeal(null);
        resetForm();
        return;
      }
      alert(data?.message || "Failed to update deal");
      return;
    } catch (e) {
      alert("Failed to update deal");
      return;
    }

    const updatedDeal: Deal = {
      ...selectedDeal,
      name: newDeal.name || "",
      description: newDeal.description || "",
      type: newDeal.type || "percentage",
      discountValue: newDeal.discountValue || 0,
      categories: newDeal.categories || [],
      specificItems: newDeal.specificItems || [],
      startDate: newDeal.startDate || "",
      endDate: newDeal.endDate || "",
      isActive: newDeal.isActive || false,
      frequency: newDeal.frequency || "always",
      dayOfWeek: newDeal.dayOfWeek,
      dayOfMonth: newDeal.dayOfMonth,
      emailCustomers: newDeal.emailCustomers || false,
      loyaltyOnly: newDeal.loyaltyOnly || false,
      medicalOnly: newDeal.medicalOnly || false,
      minimumPurchase: newDeal.minimumPurchase,
      minimumPurchaseType: newDeal.minimumPurchaseType || "dollars",
      maxUses: newDeal.maxUses,
    };

    setDeals((prev) =>
      prev.map((deal) => (deal.id === selectedDeal.id ? updatedDeal : deal)),
    );

    setShowEditDialog(false);
    setSelectedDeal(null);
    resetForm();

    alert(`Deal "${updatedDeal.name}" has been updated successfully!`);
  };

  const resetForm = () => {
    setNewDeal({
      name: "",
      description: "",
      type: "percentage",
      discountValue: 0,
      categories: [],
      specificItems: [],
      startDate: new Date().toISOString().split("T")[0],
      endDate: "",
      isActive: true,
      frequency: "always",
      emailCustomers: false,
      loyaltyOnly: false,
      medicalOnly: false,
      categoryDiscounts: {},
      itemDiscounts: {},
      currentUses: 0,
    });
  };

  const toggleDealStatus = async (dealId: string) => {
    const target = deals.find((d) => d.id === dealId);
    if (!target) return;
    try {
      const res = await fetch(`/api/deals/${dealId}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          ...storeHeaders(),
        },
        body: JSON.stringify({
          ...mapUiToApi(target),
          is_active: !target.isActive,
        }),
      });
      if (res.ok) {
        setDeals((prev) =>
          prev.map((deal) =>
            deal.id === dealId ? { ...deal, isActive: !deal.isActive } : deal,
          ),
        );
      }
    } catch {}
  };

  const deleteDeal = async (dealId: string) => {
    if (confirm("Are you sure you want to delete this deal?")) {
      try {
        const r = await fetch(`/api/deals/${dealId}`, {
          method: "DELETE",
          headers: { ...storeHeaders() },
        });
        // proceed even if server fails to avoid UI lock; server is source of truth on next load
      } catch (_) {}
      setDeals((prev) => {
        const next = prev.filter((deal) => deal.id !== dealId);
        saveDealsLocal(next);
        return next;
      });
    }
  };

  const sendDealEmail = (deal: Deal) => {
    console.log(`Sending deal email for: ${deal.name}`);
    alert(
      `Email campaign for "${deal.name}" has been sent to loyalty program members!`,
    );
  };

  const getFrequencyDisplay = (deal: Deal) => {
    switch (deal.frequency) {
      case "daily":
        return "Daily";
      case "weekly":
        return `Weekly (${deal.dayOfWeek})`;
      case "monthly":
        return `Monthly (Day ${deal.dayOfMonth})`;
      case "always":
        return "Always Active";
      default:
        return "Custom";
    }
  };

  // Persist on change
  useEffect(() => {
    saveDealsLocal(deals);
  }, [deals]);

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Deals & Specials</h1>
            <p className="text-sm opacity-80">
              Manage sales, discounts, and promotions
            </p>
          </div>
          <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
            <DialogTrigger asChild>
              <Button className="header-button-visible">
                <Plus className="w-4 h-4 mr-2" />
                Create Deal
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
              <DialogHeader>
                <DialogTitle>Create New Deal</DialogTitle>
              </DialogHeader>
              <div className="space-y-6">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="deal-name">Deal Name</Label>
                    <Input
                      id="deal-name"
                      value={newDeal.name}
                      onChange={(e) =>
                        setNewDeal((prev) => ({
                          ...prev,
                          name: e.target.value,
                        }))
                      }
                      placeholder="Enter deal name"
                    />
                  </div>
                  <div>
                    <Label htmlFor="deal-type">Discount Type</Label>
                    <Select
                      value={newDeal.type}
                      onValueChange={(value: Deal["type"]) =>
                        setNewDeal((prev) => ({ ...prev, type: value }))
                      }
                    >
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="percentage">
                          Percentage Off
                        </SelectItem>
                        <SelectItem value="fixed">Fixed Amount Off</SelectItem>
                        <SelectItem value="bogo">Buy One Get One</SelectItem>
                        <SelectItem value="bulk">Bulk Discount</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div>
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={newDeal.description}
                    onChange={(e) =>
                      setNewDeal((prev) => ({
                        ...prev,
                        description: e.target.value,
                      }))
                    }
                    placeholder="Describe the deal..."
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="discount-value">
                      {newDeal.type === "percentage"
                        ? "Percentage (%)"
                        : newDeal.type === "fixed"
                          ? "Amount ($)"
                          : "Discount (%)"}
                    </Label>
                    <Input
                      id="discount-value"
                      type="number"
                      value={newDeal.discountValue}
                      onChange={(e) =>
                        setNewDeal((prev) => ({
                          ...prev,
                          discountValue: parseFloat(e.target.value) || 0,
                        }))
                      }
                      placeholder="0"
                    />
                  </div>
                  <div>
                    <Label htmlFor="frequency">Frequency</Label>
                    <Select
                      value={newDeal.frequency}
                      onValueChange={(value: Deal["frequency"]) =>
                        setNewDeal((prev) => ({ ...prev, frequency: value }))
                      }
                    >
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="always">Always Active</SelectItem>
                        <SelectItem value="daily">Daily</SelectItem>
                        <SelectItem value="weekly">Weekly</SelectItem>
                        <SelectItem value="monthly">Monthly</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                {newDeal.frequency === "weekly" && (
                  <div>
                    <Label htmlFor="day-of-week">Day of Week</Label>
                    <Select
                      value={newDeal.dayOfWeek}
                      onValueChange={(value) =>
                        setNewDeal((prev) => ({ ...prev, dayOfWeek: value }))
                      }
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select day" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Monday">Monday</SelectItem>
                        <SelectItem value="Tuesday">Tuesday</SelectItem>
                        <SelectItem value="Wednesday">Wednesday</SelectItem>
                        <SelectItem value="Thursday">Thursday</SelectItem>
                        <SelectItem value="Friday">Friday</SelectItem>
                        <SelectItem value="Saturday">Saturday</SelectItem>
                        <SelectItem value="Sunday">Sunday</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                )}

                {newDeal.frequency === "monthly" && (
                  <div>
                    <Label htmlFor="day-of-month">Day of Month</Label>
                    <Input
                      id="day-of-month"
                      type="number"
                      min="1"
                      max="31"
                      value={newDeal.dayOfMonth || ""}
                      onChange={(e) =>
                        setNewDeal((prev) => ({
                          ...prev,
                          dayOfMonth: parseInt(e.target.value) || undefined,
                        }))
                      }
                      placeholder="1-31"
                    />
                  </div>
                )}

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="start-date">Start Date</Label>
                    <Input
                      id="start-date"
                      type="date"
                      value={newDeal.startDate}
                      onChange={(e) =>
                        setNewDeal((prev) => ({
                          ...prev,
                          startDate: e.target.value,
                        }))
                      }
                    />
                  </div>
                  <div>
                    <Label htmlFor="end-date">End Date (Optional)</Label>
                    <Input
                      id="end-date"
                      type="date"
                      value={newDeal.endDate}
                      onChange={(e) =>
                        setNewDeal((prev) => ({
                          ...prev,
                          endDate: e.target.value,
                        }))
                      }
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label>Categories</Label>
                    <div className="grid grid-cols-3 gap-2 mt-2">
                      {availableCategories.map((category) => (
                        <div
                          key={category}
                          className="flex items-center space-x-2"
                        >
                          <Checkbox
                            id={`category-${category}`}
                            checked={newDeal.categories?.includes(category)}
                            onCheckedChange={(checked) => {
                              if (checked) {
                                setNewDeal((prev) => ({
                                  ...prev,
                                  categories: [
                                    ...(prev.categories || []),
                                    category,
                                  ],
                                }));
                              } else {
                                setNewDeal((prev) => ({
                                  ...prev,
                                  categories:
                                    prev.categories?.filter(
                                      (c) => c !== category,
                                    ) || [],
                                }));
                              }
                            }}
                          />
                          <Label
                            htmlFor={`category-${category}`}
                            className="text-sm"
                          >
                            {category}
                          </Label>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div>
                    <Label>Specific Products (current inventory)</Label>
                    <div className="mt-2 flex gap-2">
                      <Input
                        placeholder="Search by name or SKU"
                        value={productSearch}
                        onChange={(e) => setProductSearch(e.target.value)}
                        onKeyDown={(e) => {
                          if (e.key === "Enter") {
                            e.preventDefault();
                            loadProducts();
                          }
                        }}
                      />
                      <Button
                        type="button"
                        variant="outline"
                        onClick={() => loadProducts()}
                      >
                        Search
                      </Button>
                      <Button
                        type="button"
                        variant="ghost"
                        onClick={() => {
                          setProductSearch("");
                          loadProducts("");
                        }}
                      >
                        Clear
                      </Button>
                    </div>
                    <div className="mt-2 border rounded-lg max-h-48 overflow-y-auto">
                      {productLoading && (
                        <div className="p-3 text-sm text-gray-500">
                          Loading...
                        </div>
                      )}
                      {!productLoading && productResults.length === 0 && (
                        <div className="p-3 text-sm text-gray-500">
                          No products found
                        </div>
                      )}
                      {!productLoading &&
                        productResults.map((p) => {
                          const idStr = String(p.id);
                          const checked = (
                            newDeal.specificItems || []
                          ).includes(idStr);
                          return (
                            <label
                              key={idStr}
                              className="flex items-center justify-between px-3 py-2 border-b last:border-b-0 cursor-pointer hover:bg-gray-50"
                            >
                              <div className="flex items-center space-x-3">
                                <Checkbox
                                  checked={checked}
                                  onCheckedChange={(c) => {
                                    setNewDeal((prev) => {
                                      const current = new Set(
                                        prev.specificItems || [],
                                      );
                                      if (c) current.add(idStr);
                                      else current.delete(idStr);
                                      return {
                                        ...prev,
                                        specificItems: Array.from(current),
                                      };
                                    });
                                  }}
                                />
                                <div>
                                  <div className="text-sm font-medium">
                                    {p.name}
                                  </div>
                                  <div className="text-xs text-muted-foreground">
                                    {[
                                      p.sku ? `SKU: ${p.sku}` : null,
                                      p.category,
                                    ]
                                      .filter(Boolean)
                                      .join(" • ")}
                                  </div>
                                </div>
                              </div>
                              <span className="text-xs text-muted-foreground">
                                #{idStr}
                              </span>
                            </label>
                          );
                        })}
                    </div>
                    <div className="mt-2 text-xs text-muted-foreground">
                      {(newDeal.specificItems || []).length} selected
                      {(newDeal.specificItems || []).length > 0 && (
                        <Button
                          type="button"
                          variant="link"
                          className="ml-2 h-auto p-0"
                          onClick={() =>
                            setNewDeal((prev) => ({
                              ...prev,
                              specificItems: [],
                            }))
                          }
                        >
                          Clear
                        </Button>
                      )}
                    </div>
                    <p className="text-xs text-gray-500 mt-1">
                      Only products currently in stock are shown and eligible.
                    </p>
                  </div>
                </div>

                <div>
                  <Label>Per-Category Discounts (optional)</Label>
                  <div className="mt-2 space-y-2">
                    {availableCategories.map((cat) => (
                      <div
                        key={cat}
                        className="grid grid-cols-2 gap-2 items-center"
                      >
                        <div className="text-sm">{cat}</div>
                        <div className="flex items-center gap-2">
                          <Input
                            type="number"
                            step="0.01"
                            value={String(
                              (newDeal.categoryDiscounts || {})[cat] ?? "",
                            )}
                            onChange={(e) =>
                              setNewDeal((prev) => ({
                                ...prev,
                                categoryDiscounts: {
                                  ...(prev.categoryDiscounts || {}),
                                  [cat]: parseFloat(e.target.value) || 0,
                                },
                              }))
                            }
                            placeholder="Discount value"
                          />
                          <Select
                            value={newDeal.type}
                            onValueChange={(value: Deal["type"]) =>
                              setNewDeal((prev) => ({ ...prev, type: value }))
                            }
                          >
                            <SelectTrigger className="w-[140px]">
                              <SelectValue placeholder="Deal type" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem value="percentage">
                                Percentage
                              </SelectItem>
                              <SelectItem value="fixed">
                                Fixed Amount
                              </SelectItem>
                            </SelectContent>
                          </Select>
                        </div>
                      </div>
                    ))}
                    <p className="text-xs text-muted-foreground">
                      If set, these override the main discount for that
                      category.
                    </p>
                  </div>
                </div>

                <div>
                  <Label>Per-Item Discounts (optional)</Label>
                  <div className="mt-2 space-y-2">
                    {(newDeal.specificItems || []).map((pid) => (
                      <div
                        key={pid}
                        className="grid grid-cols-2 gap-2 items-center"
                      >
                        <div className="text-sm">Item #{pid}</div>
                        <div className="flex items-center gap-2">
                          <Input
                            type="number"
                            step="0.01"
                            value={String(
                              (newDeal.itemDiscounts || {})[String(pid)] ?? "",
                            )}
                            onChange={(e) =>
                              setNewDeal((prev) => ({
                                ...prev,
                                itemDiscounts: {
                                  ...(prev.itemDiscounts || {}),
                                  [String(pid)]:
                                    parseFloat(e.target.value) || 0,
                                },
                              }))
                            }
                            placeholder="Discount value"
                          />
                          <Select
                            value={newDeal.type}
                            onValueChange={(value: Deal["type"]) =>
                              setNewDeal((prev) => ({ ...prev, type: value }))
                            }
                          >
                            <SelectTrigger className="w-[140px]">
                              <SelectValue placeholder="Deal type" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem value="percentage">
                                Percentage
                              </SelectItem>
                              <SelectItem value="fixed">
                                Fixed Amount
                              </SelectItem>
                            </SelectContent>
                          </Select>
                        </div>
                      </div>
                    ))}
                    {(newDeal.specificItems || []).length === 0 && (
                      <p className="text-xs text-muted-foreground">
                        Select products above to set per-item discounts.
                      </p>
                    )}
                  </div>
                </div>

                <div>
                  <Label>Minimum Purchase</Label>
                  <div className="grid grid-cols-3 gap-2 mt-2">
                    <div>
                      <Select
                        value={newDeal.minimumPurchaseType || "dollars"}
                        onValueChange={(value: "dollars" | "grams") =>
                          setNewDeal((prev) => ({
                            ...prev,
                            minimumPurchaseType: value,
                          }))
                        }
                      >
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="dollars">Dollars ($)</SelectItem>
                          <SelectItem value="grams">Grams (g)</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="col-span-2">
                      <Input
                        type="number"
                        step="0.01"
                        value={newDeal.minimumPurchase || ""}
                        onChange={(e) =>
                          setNewDeal((prev) => ({
                            ...prev,
                            minimumPurchase:
                              parseFloat(e.target.value) || undefined,
                          }))
                        }
                        placeholder={`Minimum ${newDeal.minimumPurchaseType === "grams" ? "grams" : "dollars"}`}
                      />
                    </div>
                  </div>
                </div>

                <div>
                  <Label htmlFor="max-uses">Maximum Uses</Label>
                  <Input
                    id="max-uses"
                    type="number"
                    value={newDeal.maxUses || ""}
                    onChange={(e) =>
                      setNewDeal((prev) => ({
                        ...prev,
                        maxUses: parseInt(e.target.value) || undefined,
                      }))
                    }
                    placeholder="Unlimited"
                  />
                </div>

                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <div>
                      <Label>Email Customers</Label>
                      <p className="text-sm text-gray-600">
                        Send email notification to loyalty program members
                      </p>
                    </div>
                    <Switch
                      checked={newDeal.emailCustomers}
                      onCheckedChange={(checked) =>
                        setNewDeal((prev) => ({
                          ...prev,
                          emailCustomers: checked,
                        }))
                      }
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div>
                      <Label>Loyalty Members Only</Label>
                      <p className="text-sm text-gray-600">
                        Restrict deal to loyalty program members
                      </p>
                    </div>
                    <Switch
                      checked={newDeal.loyaltyOnly}
                      onCheckedChange={(checked) =>
                        setNewDeal((prev) => ({
                          ...prev,
                          loyaltyOnly: checked,
                        }))
                      }
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div>
                      <Label>Medical/Caregiver Only</Label>
                      <p className="text-sm text-gray-600">
                        Only available to medical patients and caregivers
                      </p>
                    </div>
                    <Switch
                      checked={newDeal.medicalOnly}
                      onCheckedChange={(checked) =>
                        setNewDeal((prev) => ({
                          ...prev,
                          medicalOnly: checked,
                        }))
                      }
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div>
                      <Label>Active</Label>
                      <p className="text-sm text-gray-600">
                        Make deal active immediately
                      </p>
                    </div>
                    <Switch
                      checked={newDeal.isActive}
                      onCheckedChange={(checked) =>
                        setNewDeal((prev) => ({ ...prev, isActive: checked }))
                      }
                    />
                  </div>
                </div>

                <div className="flex gap-2">
                  <Button onClick={createDeal} className="flex-1">
                    Create Deal
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => setShowCreateDialog(false)}
                    className="flex-1"
                  >
                    Cancel
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>
      </header>

      <div className="container mx-auto p-6">
        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-green-600">
                {deals.filter((d) => d.isActive).length}
              </div>
              <div className="text-sm text-muted-foreground">Active Deals</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-blue-600">
                {deals.reduce((sum, d) => sum + d.currentUses, 0)}
              </div>
              <div className="text-sm text-muted-foreground">Total Uses</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-purple-600">
                {deals.filter((d) => d.loyaltyOnly).length}
              </div>
              <div className="text-sm text-muted-foreground">Loyalty Deals</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-green-600">
                {deals.filter((d) => d.medicalOnly).length}
              </div>
              <div className="text-sm text-muted-foreground">Medical Deals</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-orange-600">
                {deals.filter((d) => d.emailCustomers).length}
              </div>
              <div className="text-sm text-muted-foreground">
                Email Campaigns
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Deals List */}
        <div className="max-h-[70vh] overflow-y-auto pr-1">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {deals.map((deal) => (
              <Card key={deal.id} className="hover:shadow-md transition-shadow">
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <h3 className="font-semibold">{deal.name}</h3>
                    <div className="flex gap-2">
                      <Badge variant={deal.isActive ? "default" : "secondary"}>
                        {deal.isActive ? "Active" : "Inactive"}
                      </Badge>
                      {deal.loyaltyOnly && (
                        <Badge variant="outline">
                          <Star className="w-3 h-3 mr-1" />
                          Loyalty
                        </Badge>
                      )}
                      {deal.medicalOnly && (
                        <Badge
                          variant="outline"
                          className="bg-green-50 text-green-700 border-green-300"
                        >
                          <Plus className="w-3 h-3 mr-1" />
                          Medical
                        </Badge>
                      )}
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="space-y-4">
                  <p className="text-sm text-gray-600">{deal.description}</p>

                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <span className="font-medium">Discount:</span>
                      <div className="text-lg font-bold text-green-600">
                        {deal.type === "percentage"
                          ? `${deal.discountValue}%`
                          : deal.type === "fixed"
                            ? `$${deal.discountValue}`
                            : deal.type === "bogo"
                              ? `BOGO ${deal.discountValue}%`
                              : `${deal.discountValue}% Bulk`}
                      </div>
                    </div>
                    <div>
                      <span className="font-medium">Usage:</span>
                      <div className="text-lg font-bold">
                        {deal.currentUses}
                        {deal.maxUses ? `/${deal.maxUses}` : ""}
                      </div>
                    </div>
                  </div>

                  <div className="space-y-2 text-sm">
                    <div className="flex items-center gap-2">
                      <Clock className="w-4 h-4 text-muted-foreground" />
                      <span>{getFrequencyDisplay(deal)}</span>
                    </div>
                    {deal.categories.length > 0 && (
                      <div className="flex items-center gap-2">
                        <Package className="w-4 h-4 text-muted-foreground" />
                        <div className="flex flex-wrap gap-1">
                          {deal.categories.slice(0, 2).map((category) => (
                            <Badge
                              key={category}
                              variant="outline"
                              className="text-xs"
                            >
                              {category}
                            </Badge>
                          ))}
                          {deal.categories.length > 2 && (
                            <span className="text-xs text-muted-foreground">
                              +{deal.categories.length - 2} more
                            </span>
                          )}
                        </div>
                      </div>
                    )}
                    {deal.minimumPurchase && (
                      <div className="flex items-center gap-2">
                        <DollarSign className="w-4 h-4 text-muted-foreground" />
                        <span>
                          Min.{" "}
                          {deal.minimumPurchaseType === "grams"
                            ? `${deal.minimumPurchase}g`
                            : `$${deal.minimumPurchase}`}
                        </span>
                      </div>
                    )}
                  </div>

                  <div className="flex gap-2 pt-2">
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => toggleDealStatus(deal.id)}
                    >
                      {deal.isActive ? "Deactivate" : "Activate"}
                    </Button>
                    {deal.emailCustomers && (
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => sendDealEmail(deal)}
                      >
                        <Mail className="w-3 h-3 mr-1" />
                        Email
                      </Button>
                    )}
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => {
                        setSelectedDeal(deal);
                        setNewDeal({
                          name: deal.name,
                          description: deal.description,
                          type: deal.type,
                          discountValue: deal.discountValue,
                          categories: deal.categories,
                          specificItems: deal.specificItems,
                          categoryDiscounts: deal.categoryDiscounts || {},
                          itemDiscounts: deal.itemDiscounts || {},
                          startDate: deal.startDate,
                          endDate: deal.endDate,
                          isActive: deal.isActive,
                          frequency: deal.frequency,
                          dayOfWeek: deal.dayOfWeek,
                          dayOfMonth: deal.dayOfMonth,
                          emailCustomers: deal.emailCustomers,
                          loyaltyOnly: deal.loyaltyOnly,
                          medicalOnly: deal.medicalOnly,
                          minimumPurchase: deal.minimumPurchase,
                          minimumPurchaseType: deal.minimumPurchaseType,
                          maxUses: deal.maxUses,
                        });
                        setShowEditDialog(true);
                      }}
                    >
                      <Edit className="w-3 h-3 mr-1" />
                      Edit
                    </Button>
                    <Button
                      size="sm"
                      variant="destructive"
                      onClick={() => deleteDeal(deal.id)}
                    >
                      <Trash2 className="w-3 h-3" />
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </div>

      {/* Edit Deal Dialog */}
      <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Edit Deal - {selectedDeal?.name}</DialogTitle>
          </DialogHeader>
          <div className="space-y-6">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="edit-deal-name">Deal Name</Label>
                <Input
                  id="edit-deal-name"
                  value={newDeal.name}
                  onChange={(e) =>
                    setNewDeal((prev) => ({ ...prev, name: e.target.value }))
                  }
                  placeholder="Enter deal name"
                />
              </div>
              <div>
                <Label htmlFor="edit-deal-type">Discount Type</Label>
                <Select
                  value={newDeal.type}
                  onValueChange={(value: Deal["type"]) =>
                    setNewDeal((prev) => ({ ...prev, type: value }))
                  }
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="percentage">Percentage Off</SelectItem>
                    <SelectItem value="fixed">Fixed Amount Off</SelectItem>
                    <SelectItem value="bogo">Buy One Get One</SelectItem>
                    <SelectItem value="bulk">Bulk Discount</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div>
              <Label htmlFor="edit-description">Description</Label>
              <Textarea
                id="edit-description"
                value={newDeal.description}
                onChange={(e) =>
                  setNewDeal((prev) => ({
                    ...prev,
                    description: e.target.value,
                  }))
                }
                placeholder="Describe the deal..."
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="edit-discount-value">
                  {newDeal.type === "percentage"
                    ? "Percentage (%)"
                    : newDeal.type === "fixed"
                      ? "Amount ($)"
                      : "Discount (%)"}
                </Label>
                <Input
                  id="edit-discount-value"
                  type="number"
                  value={newDeal.discountValue}
                  onChange={(e) =>
                    setNewDeal((prev) => ({
                      ...prev,
                      discountValue: parseFloat(e.target.value) || 0,
                    }))
                  }
                  placeholder="0"
                />
              </div>
              <div>
                <Label htmlFor="edit-frequency">Frequency</Label>
                <Select
                  value={newDeal.frequency}
                  onValueChange={(value: Deal["frequency"]) =>
                    setNewDeal((prev) => ({ ...prev, frequency: value }))
                  }
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="always">Always Active</SelectItem>
                    <SelectItem value="daily">Daily</SelectItem>
                    <SelectItem value="weekly">Weekly</SelectItem>
                    <SelectItem value="monthly">Monthly</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div>
              <Label>Specific Products (current inventory)</Label>
              <div className="mt-2 flex gap-2">
                <Input
                  placeholder="Search by name or SKU"
                  value={productSearch}
                  onChange={(e) => setProductSearch(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === "Enter") {
                      e.preventDefault();
                      loadProducts();
                    }
                  }}
                />
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => loadProducts()}
                >
                  Search
                </Button>
                <Button
                  type="button"
                  variant="ghost"
                  onClick={() => {
                    setProductSearch("");
                    loadProducts("");
                  }}
                >
                  Clear
                </Button>
              </div>
              <div className="mt-2 border rounded-lg max-h-48 overflow-y-auto">
                {productLoading && (
                  <div className="p-3 text-sm text-gray-500">Loading...</div>
                )}
                {!productLoading && productResults.length === 0 && (
                  <div className="p-3 text-sm text-gray-500">
                    No products found
                  </div>
                )}
                {!productLoading &&
                  productResults.map((p) => {
                    const idStr = String(p.id);
                    const checked = (newDeal.specificItems || []).includes(
                      idStr,
                    );
                    return (
                      <label
                        key={idStr}
                        className="flex items-center justify-between px-3 py-2 border-b last:border-b-0 cursor-pointer hover:bg-gray-50"
                      >
                        <div className="flex items-center space-x-3">
                          <Checkbox
                            checked={checked}
                            onCheckedChange={(c) => {
                              setNewDeal((prev) => {
                                const current = new Set(
                                  prev.specificItems || [],
                                );
                                if (c) current.add(idStr);
                                else current.delete(idStr);
                                return {
                                  ...prev,
                                  specificItems: Array.from(current),
                                };
                              });
                            }}
                          />
                          <div>
                            <div className="text-sm font-medium">{p.name}</div>
                            <div className="text-xs text-muted-foreground">
                              {[p.sku ? `SKU: ${p.sku}` : null, p.category]
                                .filter(Boolean)
                                .join(" • ")}
                            </div>
                          </div>
                        </div>
                        <span className="text-xs text-muted-foreground">
                          #{idStr}
                        </span>
                      </label>
                    );
                  })}
              </div>
              <div className="mt-2 text-xs text-muted-foreground">
                {(newDeal.specificItems || []).length} selected
                {(newDeal.specificItems || []).length > 0 && (
                  <Button
                    type="button"
                    variant="link"
                    className="ml-2 h-auto p-0"
                    onClick={() =>
                      setNewDeal((prev) => ({ ...prev, specificItems: [] }))
                    }
                  >
                    Clear
                  </Button>
                )}
              </div>
              <p className="text-xs text-gray-500 mt-1">
                Only products currently in stock are shown and eligible.
              </p>
            </div>

            <div className="flex gap-2">
              <Button onClick={editDeal} className="flex-1">
                Update Deal
              </Button>
              <Button
                variant="outline"
                onClick={() => setShowEditDialog(false)}
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
