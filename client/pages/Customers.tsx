import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Users,
  Plus,
  Search,
  Edit,
  Trash2,
  Eye,
  Phone,
  Mail,
  Calendar,
  MapPin,
  CreditCard,
  History,
  Star,
  Filter,
  Download,
  UserPlus,
  ShoppingCart,
  Package,
  Printer,
} from "lucide-react";
import { useNavigate } from "react-router-dom";

interface PurchaseItem {
  id: string;
  name: string;
  category: string;
  quantity: number;
  unitPrice: number;
  total: number;
  metrcTag?: string;
}

interface Purchase {
  id: string;
  date: string;
  items: PurchaseItem[];
  subtotal: number;
  tax: number;
  total: number;
  paymentMethod: string;
  loyaltyPointsEarned?: number;
  employee: string;
}

interface Customer {
  id: string;
  firstName: string;
  lastName?: string;
  email: string;
  phone: string;
  dateOfBirth: string;
  address: {
    street: string;
    city: string;
    state: string;
    zipCode: string;
  };
  customerType: "recreational" | "medical";
  medicalCard?: {
    number: string;
    issueDate: string;
    expirationDate: string;
    isPatient: boolean; // true for patient, false for caregiver
    physicianName?: string;
    notes?: string;
  };
  loyaltyProgram?: {
    memberId: string;
    joinDate: string;
    pointsBalance: number;
    tier: "Bronze" | "Silver" | "Gold" | "Platinum";
    isVeteran: boolean;
  };
  isActive: boolean;
  lastVisit?: string;
  totalSpent: number;
  totalVisits: number;
  preferredProducts: string[];
  notes: string;
  createdDate: string;
  dataRetentionConsent: boolean;
  purchaseHistory: Purchase[];
}

export default function Customers() {
  const navigate = useNavigate();
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [searchQuery, setSearchQuery] = useState("");
  const [filterType, setFilterType] = useState<
    "all" | "recreational" | "medical"
  >("all");
  const [filterActive, setFilterActive] = useState<
    "all" | "active" | "inactive"
  >("all");
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [showViewDialog, setShowViewDialog] = useState(false);
  const [showPurchaseHistoryDialog, setShowPurchaseHistoryDialog] =
    useState(false);
  const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(
    null,
  );

  const [newCustomer, setNewCustomer] = useState<Partial<Customer>>({
    firstName: "",
    lastName: "",
    email: "",
    phone: "",
    dateOfBirth: "",
    address: {
      street: "",
      city: "",
      state: "OR",
      zipCode: "",
    },
    customerType: "recreational",
    isActive: true,
    notes: "",
    dataRetentionConsent: false,
  });
  const [enrollInLoyalty, setEnrollInLoyalty] = useState(false);

  // Local persistence helpers (per-user scope + POS-compatible mirror)
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
  const customersFullKey = () => `cannabest-customers-${getUserId()}`;
  const posCustomersKey = () => `cannabisPOS-customers-${getUserId()}`;
  const loyaltyKey = () => `cannabest-loyalty-${getUserId()}`;

  const saveCustomersLocal = (list: Customer[]) => {
    try {
      localStorage.setItem(customersFullKey(), JSON.stringify(list));
      localStorage.setItem("cannabest-customers", JSON.stringify(list));
    } catch (_) {}
    try {
      const simplified = list.map((c) => ({
        id: c.id,
        name: `${c.firstName} ${c.lastName || ""}`.trim(),
        email: c.email,
        phone: c.phone,
        isMedical: c.customerType === "medical",
        loyaltyPoints: c.loyaltyProgram?.pointsBalance || 0,
      }));
      localStorage.setItem(posCustomersKey(), JSON.stringify(simplified));
      localStorage.setItem("cannabisPOS-customers", JSON.stringify(simplified));
    } catch (_) {}
  };

  // Load from local storage on mount and merge with any POS-saved customers
  useEffect(() => {
    // Clear any demo/legacy local storage so UI reflects database only
    try {
      localStorage.removeItem(customersFullKey());
      localStorage.removeItem("cannabest-customers");
      localStorage.removeItem(posCustomersKey());
      localStorage.removeItem("cannabisPOS-customers");
    } catch (_) {}
  }, []);

  // Persist whenever the customers list changes
  useEffect(() => {
    saveCustomersLocal(customers);
  }, [customers]);

  // Map server customer to local Customer shape
  const mapServerToCustomer = (srv: any): Customer => {
    const addressObj = (() => {
      try {
        if (!srv?.address)
          return { street: "", city: "", state: "OR", zipCode: "" };
        const a =
          typeof srv.address === "string"
            ? JSON.parse(srv.address || "{}")
            : srv.address;
        return {
          street: a?.street || "",
          city: a?.city || "",
          state: a?.state || "OR",
          zipCode: a?.zip_code || a?.zipCode || "",
        };
      } catch (_) {
        return { street: "", city: "", state: "OR", zipCode: "" };
      }
    })();
    const loyaltyPoints = Number(srv?.loyalty_points || 0) || 0;
    const memberId =
      srv?.loyalty_member_id ||
      (loyaltyPoints > 0 ? String(srv?.id || "") : "");
    const tier = srv?.loyalty_tier || "Bronze";
    return {
      id: String(srv?.id ?? Date.now().toString()),
      firstName: srv?.first_name || "",
      lastName: srv?.last_name || "",
      email: srv?.email || "",
      phone: srv?.phone || "",
      dateOfBirth: srv?.date_of_birth || "",
      address: addressObj,
      customerType:
        srv?.customer_type === "medical" ? "medical" : "recreational",
      medicalCard: undefined,
      loyaltyProgram: memberId
        ? {
            memberId,
            joinDate: (
              srv?.loyalty_join_date || new Date().toISOString().split("T")[0]
            ).toString(),
            pointsBalance: loyaltyPoints,
            tier: tier,
            isVeteran: !!srv?.is_veteran,
          }
        : undefined,
      isActive: srv?.is_active !== false,
      lastVisit: srv?.last_visit || undefined,
      totalSpent: Number(srv?.total_spent || 0) || 0,
      totalVisits: Number(srv?.total_visits || 0) || 0,
      preferredProducts: [],
      notes: srv?.notes || "",
      createdDate: (srv?.created_at || new Date().toISOString()).toString(),
      dataRetentionConsent: !!srv?.data_retention_consent,
      purchaseHistory: [],
    };
  };

  const fetchServerCustomers = async (): Promise<Customer[]> => {
    const results: Customer[] = [];
    try {
      const res = await fetch("/customers", {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });
      if (res.ok) {
        const data = await res.json();
        const list = Array.isArray(data?.data)
          ? data.data
          : Array.isArray(data)
            ? data
            : [];
        results.push(...list.map(mapServerToCustomer));
      }
    } catch (_) {}
    try {
      const token = localStorage.getItem("auth_token");
      const res = await fetch("/api/customers", {
        headers: {
          Accept: "application/json",
          Authorization: token ? `Bearer ${token}` : "",
        },
      });
      if (res.ok) {
        const data = await res.json();
        const list = Array.isArray(data?.data)
          ? data.data
          : Array.isArray(data?.customers)
            ? data.customers
            : [];
        results.push(...list.map(mapServerToCustomer));
      }
    } catch (_) {}
    const seen = new Set<string>();
    return results.filter((c) => {
      const key = String(c.id || c.email || c.phone || "");
      if (!key || seen.has(key)) return false;
      seen.add(key);
      return true;
    });
  };

  useEffect(() => {
    (async () => {
      const serverList = await fetchServerCustomers();
      if (serverList.length) {
        setCustomers(serverList);
        return;
      }
    })();
  }, []);

  const filteredCustomers = customers.filter((customer) => {
    const matchesSearch =
      customer.firstName.toLowerCase().includes(searchQuery.toLowerCase()) ||
      customer.lastName.toLowerCase().includes(searchQuery.toLowerCase()) ||
      customer.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
      customer.phone.includes(searchQuery) ||
      customer.loyaltyProgram?.memberId
        .toLowerCase()
        .includes(searchQuery.toLowerCase());

    const matchesType =
      filterType === "all" || customer.customerType === filterType;
    const matchesActive =
      filterActive === "all" ||
      (filterActive === "active" && customer.isActive) ||
      (filterActive === "inactive" && !customer.isActive);

    return matchesSearch && matchesType && matchesActive;
  });

  const addCustomer = async () => {
    if (!newCustomer.firstName || !newCustomer.email || !newCustomer.phone) {
      alert("Please fill in all required fields (First Name, Email, Phone)");
      return;
    }

    if (!newCustomer.dataRetentionConsent) {
      alert("Data retention consent is required");
      return;
    }

    const payload = {
      first_name: newCustomer.firstName!,
      last_name: newCustomer.lastName || null,
      email: newCustomer.email!,
      phone: newCustomer.phone!,
      date_of_birth: newCustomer.dateOfBirth || null,
      customer_type: newCustomer.customerType || "recreational",
      address: newCustomer.address || {
        street: "",
        city: "",
        state: "OR",
        zipCode: "",
      },
      is_veteran: !!newCustomer.loyaltyProgram?.isVeteran,
      notes: newCustomer.notes || "",
      data_retention_consent: true,
    } as any;

    // Try persisting to server so all users can look them up
    let createdFromServer: Customer | null = null;
    try {
      const token = localStorage.getItem("auth_token");
      const res = await fetch("/api/customers", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: token ? `Bearer ${token}` : "",
        },
        body: JSON.stringify(payload),
      });
      if (res.ok) {
        const data = await res.json();
        const srv = data?.customer || data?.data || null;
        if (srv) createdFromServer = mapServerToCustomer(srv);
      }
    } catch (_) {}

    const customer: Customer = createdFromServer || {
      id: Date.now().toString(),
      firstName: newCustomer.firstName!,
      lastName: newCustomer.lastName!,
      email: newCustomer.email!,
      phone: newCustomer.phone!,
      dateOfBirth: newCustomer.dateOfBirth || "",
      address: newCustomer.address || {
        street: "",
        city: "",
        state: "OR",
        zipCode: "",
      },
      customerType: newCustomer.customerType || "recreational",
      isActive: true,
      totalSpent: 0,
      totalVisits: 0,
      preferredProducts: [],
      notes: newCustomer.notes || "",
      createdDate: new Date().toISOString().split("T")[0],
      dataRetentionConsent: newCustomer.dataRetentionConsent,
    };

    setCustomers((prev) => [...prev, customer]);

    if (enrollInLoyalty) {
      try {
        const token = localStorage.getItem("auth_token");
        const fullName =
          `${customer.firstName} ${customer.lastName || ""}`.trim();
        let ok = false;
        let res = await fetch("/api/loyalty-members", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: token ? `Bearer ${token}` : "",
          },
          body: JSON.stringify({
            customer_id: createdFromServer
              ? Number(createdFromServer.id)
              : undefined,
            name: fullName,
            email: customer.email,
            phone: customer.phone,
            starting_points: 0,
            tier: "Bronze",
            is_veteran: !!newCustomer.loyaltyProgram?.isVeteran,
          }),
        });
        ok = res.ok;
        if (!ok) {
          res = await fetch("/node/loyalty-members", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: token ? `Bearer ${token}` : "",
            },
            body: JSON.stringify({
              customer_id: createdFromServer
                ? Number(createdFromServer.id)
                : undefined,
              name: fullName,
              email: customer.email,
              phone: customer.phone,
              starting_points: 0,
              tier: "Bronze",
              is_veteran: !!newCustomer.loyaltyProgram?.isVeteran,
            }),
          });
          ok = res.ok;
        }
      } catch (_) {}
    }

    setShowAddDialog(false);
    setNewCustomer({
      firstName: "",
      lastName: "",
      email: "",
      phone: "",
      dateOfBirth: "",
      address: { street: "", city: "", state: "OR", zipCode: "" },
      customerType: "recreational",
      isActive: true,
      notes: "",
      dataRetentionConsent: false,
    });
    setEnrollInLoyalty(false);
  };

  const editCustomer = async () => {
    if (
      !selectedCustomer ||
      !newCustomer.firstName ||
      !newCustomer.email ||
      !newCustomer.phone
    ) {
      alert("Please fill in all required fields (First Name, Email, Phone)");
      return;
    }

    // Attempt server update first
    try {
      const token = localStorage.getItem("auth_token");
      if (token && selectedCustomer) {
        const payload = {
          first_name: newCustomer.firstName!,
          last_name: newCustomer.lastName || null,
          email: newCustomer.email!,
          phone: newCustomer.phone!,
          date_of_birth: newCustomer.dateOfBirth || null,
          customer_type: newCustomer.customerType || "recreational",
          address: newCustomer.address || {
            street: "",
            city: "",
            state: "OR",
            zipCode: "",
          },
          is_veteran: !!newCustomer.loyaltyProgram?.isVeteran,
          notes: newCustomer.notes || "",
        } as any;
        await fetch(`/api/customers/${selectedCustomer.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify(payload),
        });
      }
    } catch (_) {}

    setCustomers((prev) =>
      prev.map((customer) =>
        customer.id === selectedCustomer!.id
          ? { ...customer, ...(newCustomer as Customer) }
          : customer,
      ),
    );

    setShowEditDialog(false);
    setSelectedCustomer(null);
    setNewCustomer({});
  };

  const deleteCustomer = async (customerId: string) => {
    if (
      confirm(
        "Are you sure you want to delete this customer? This action cannot be undone.",
      )
    ) {
      try {
        const token = localStorage.getItem("auth_token");
        if (token) {
          await fetch(`/api/customers/${customerId}`, {
            method: "DELETE",
            headers: {
              Accept: "application/json",
              Authorization: `Bearer ${token}`,
            },
          });
        }
      } catch (_) {}
      setCustomers((prev) =>
        prev.filter((customer) => customer.id !== customerId),
      );
    }
  };

  const deactivateCustomer = (customerId: string) => {
    setCustomers((prev) =>
      prev.map((customer) =>
        customer.id === customerId
          ? { ...customer, isActive: false }
          : customer,
      ),
    );
  };

  const activateCustomer = (customerId: string) => {
    setCustomers((prev) =>
      prev.map((customer) =>
        customer.id === customerId ? { ...customer, isActive: true } : customer,
      ),
    );
  };

  const startSaleForCustomer = (customer: Customer) => {
    // Store customer information for the POS system
    const customerData = {
      id: customer.id,
      name: `${customer.firstName} ${customer.lastName || ""}`.trim(),
      phone: customer.phone,
      email: customer.email,
      customerType: customer.customerType,
      medicalCard: customer.medicalCard?.number || "",
      loyaltyProgram: customer.loyaltyProgram,
      isVeteran: customer.loyaltyProgram?.isVeteran || false,
      dataRetentionConsent: customer.dataRetentionConsent,
    };

    // Store in localStorage for the POS system to pick up
    localStorage.setItem(
      "selectedCustomerForSale",
      JSON.stringify(customerData),
    );

    // Navigate to POS system
    navigate("/");
  };

  const exportCustomers = () => {
    const csvContent = [
      "First Name,Last Name,Email,Phone,Customer Type,Total Spent,Total Visits,Last Visit,Created Date",
      ...filteredCustomers.map(
        (customer) =>
          `${customer.firstName},${customer.lastName || ""},${customer.email},${customer.phone},${customer.customerType},${customer.totalSpent},${customer.totalVisits},${customer.lastVisit || "Never"},${customer.createdDate}`,
      ),
    ].join("\n");

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute(
      "download",
      `customers_${new Date().toISOString().split("T")[0]}.csv`,
    );
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const stats = {
    total: customers.length,
    active: customers.filter((c) => c.isActive).length,
    inactive: customers.filter((c) => !c.isActive).length,
    recreational: customers.filter((c) => c.customerType === "recreational")
      .length,
    medical: customers.filter((c) => c.customerType === "medical").length,
    loyaltyMembers: customers.filter((c) => c.loyaltyProgram).length,
    veterans: customers.filter((c) => c.loyaltyProgram?.isVeteran).length,
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Customer Management</h1>
            <p className="text-sm opacity-80">
              Manage customer profiles and information
            </p>
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              className="header-button-visible"
              onClick={exportCustomers}
            >
              <Download className="w-4 h-4 mr-2" />
              Export CSV
            </Button>
            <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
              <DialogTrigger asChild>
                <Button>
                  <Plus className="w-4 h-4 mr-2" />
                  Add Customer
                </Button>
              </DialogTrigger>
              <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                  <DialogTitle>Add New Customer</DialogTitle>
                </DialogHeader>
                <div className="space-y-6">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <Label htmlFor="firstName">First Name *</Label>
                      <Input
                        id="firstName"
                        value={newCustomer.firstName || ""}
                        onChange={(e) =>
                          setNewCustomer((prev) => ({
                            ...prev,
                            firstName: e.target.value,
                          }))
                        }
                        placeholder="Enter first name"
                      />
                    </div>
                    <div>
                      <Label htmlFor="lastName">Last Name (Optional)</Label>
                      <Input
                        id="lastName"
                        value={newCustomer.lastName || ""}
                        onChange={(e) =>
                          setNewCustomer((prev) => ({
                            ...prev,
                            lastName: e.target.value,
                          }))
                        }
                        placeholder="Enter last name (optional)"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <Label htmlFor="email">Email *</Label>
                      <Input
                        id="email"
                        type="email"
                        value={newCustomer.email || ""}
                        onChange={(e) =>
                          setNewCustomer((prev) => ({
                            ...prev,
                            email: e.target.value,
                          }))
                        }
                        placeholder="customer@email.com"
                      />
                    </div>
                    <div>
                      <Label htmlFor="phone">Phone *</Label>
                      <Input
                        id="phone"
                        value={newCustomer.phone || ""}
                        onChange={(e) =>
                          setNewCustomer((prev) => ({
                            ...prev,
                            phone: e.target.value,
                          }))
                        }
                        placeholder="(555) 123-4567"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <Label htmlFor="dateOfBirth">Date of Birth</Label>
                      <Input
                        id="dateOfBirth"
                        type="date"
                        value={newCustomer.dateOfBirth || ""}
                        onChange={(e) =>
                          setNewCustomer((prev) => ({
                            ...prev,
                            dateOfBirth: e.target.value,
                          }))
                        }
                      />
                    </div>
                    <div>
                      <Label htmlFor="customerType">Customer Type *</Label>
                      <Select
                        value={newCustomer.customerType || "recreational"}
                        onValueChange={(value) =>
                          setNewCustomer((prev) => ({
                            ...prev,
                            customerType: value as "recreational" | "medical",
                          }))
                        }
                      >
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="recreational">
                            Recreational
                          </SelectItem>
                          <SelectItem value="medical">Medical</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>

                  {/* Veteran Status */}
                  <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div className="flex items-center space-x-3">
                      <Checkbox
                        id="veteranStatus"
                        checked={(newCustomer as any).isVeteran || false}
                        onCheckedChange={(checked) =>
                          setNewCustomer((prev) => ({
                            ...prev,
                            ...((checked
                              ? { isVeteran: true }
                              : { isVeteran: false }) as any),
                          }))
                        }
                      />
                      <div>
                        <Label
                          htmlFor="veteranStatus"
                          className="text-sm font-medium"
                        >
                          U.S. Military Veteran
                        </Label>
                        <p className="text-xs text-gray-600">
                          Eligible for 10% veteran discount on all purchases
                          (including GLS items)
                        </p>
                      </div>
                    </div>
                  </div>

                  <div className="space-y-3">
                    <Label className="text-base font-medium">Address</Label>
                    <div>
                      <Label htmlFor="street">Street Address</Label>
                      <Input
                        id="street"
                        value={newCustomer.address?.street || ""}
                        onChange={(e) =>
                          setNewCustomer((prev) => ({
                            ...prev,
                            address: {
                              ...prev.address!,
                              street: e.target.value,
                            },
                          }))
                        }
                        placeholder="123 Main St"
                      />
                    </div>
                    <div className="grid grid-cols-3 gap-3">
                      <div>
                        <Label htmlFor="city">City</Label>
                        <Input
                          id="city"
                          value={newCustomer.address?.city || ""}
                          onChange={(e) =>
                            setNewCustomer((prev) => ({
                              ...prev,
                              address: {
                                ...prev.address!,
                                city: e.target.value,
                              },
                            }))
                          }
                          placeholder="Portland"
                        />
                      </div>
                      <div>
                        <Label htmlFor="state">State</Label>
                        <Select
                          value={newCustomer.address?.state || "OR"}
                          onValueChange={(value) =>
                            setNewCustomer((prev) => ({
                              ...prev,
                              address: { ...prev.address!, state: value },
                            }))
                          }
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="OR">Oregon</SelectItem>
                            <SelectItem value="WA">Washington</SelectItem>
                            <SelectItem value="CA">California</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label htmlFor="zipCode">ZIP Code</Label>
                        <Input
                          id="zipCode"
                          value={newCustomer.address?.zipCode || ""}
                          onChange={(e) =>
                            setNewCustomer((prev) => ({
                              ...prev,
                              address: {
                                ...prev.address!,
                                zipCode: e.target.value,
                              },
                            }))
                          }
                          placeholder="97201"
                        />
                      </div>
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="notes">Notes</Label>
                    <textarea
                      id="notes"
                      value={newCustomer.notes || ""}
                      onChange={(e) =>
                        setNewCustomer((prev) => ({
                          ...prev,
                          notes: e.target.value,
                        }))
                      }
                      placeholder="Add any notes about this customer..."
                      className="w-full p-2 border rounded-md text-sm"
                      rows={3}
                    />
                  </div>

                  <div className="flex items-start space-x-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <Checkbox
                      id="dataConsent"
                      checked={newCustomer.dataRetentionConsent || false}
                      onCheckedChange={(checked) =>
                        setNewCustomer((prev) => ({
                          ...prev,
                          dataRetentionConsent: checked as boolean,
                        }))
                      }
                    />
                    <div className="space-y-2">
                      <Label
                        htmlFor="dataConsent"
                        className="text-sm font-medium"
                      >
                        Data Retention Consent *
                      </Label>
                      <p className="text-xs text-gray-600">
                        Customer consents to storing personal information and
                        tracking sales history for compliance and future visits
                        as required by Oregon state law.
                      </p>
                    </div>
                  </div>

                  <div className="flex items-start space-x-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <Checkbox
                      id="enrollLoyalty"
                      checked={enrollInLoyalty}
                      onCheckedChange={(checked) =>
                        setEnrollInLoyalty(!!checked)
                      }
                    />
                    <div className="space-y-2">
                      <Label
                        htmlFor="enrollLoyalty"
                        className="text-sm font-medium"
                      >
                        Enroll in Loyalty Program
                      </Label>
                      <p className="text-xs text-gray-600">
                        Adds this customer to the Loyalty page so you can track
                        points and rewards.
                      </p>
                    </div>
                  </div>

                  <div className="flex gap-2">
                    <Button
                      onClick={addCustomer}
                      className="flex-1"
                      disabled={
                        !newCustomer.firstName ||
                        !newCustomer.email ||
                        !newCustomer.phone ||
                        !newCustomer.dataRetentionConsent
                      }
                    >
                      Add Customer
                    </Button>
                    <Button
                      variant="outline"
                      onClick={() => setShowAddDialog(false)}
                      className="flex-1"
                    >
                      Cancel
                    </Button>
                  </div>
                </div>
              </DialogContent>
            </Dialog>
          </div>
        </div>
      </header>

      <div className="container mx-auto p-6">
        <Tabs defaultValue="customers" className="space-y-6">
          <TabsList>
            <TabsTrigger value="customers">All Customers</TabsTrigger>
            <TabsTrigger value="analytics">Analytics</TabsTrigger>
          </TabsList>

          <TabsContent value="customers" className="space-y-6">
            {/* Filters and Search */}
            <div className="flex gap-4 items-center">
              <div className="flex-1 relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input
                  placeholder="Search by name, email, phone, or loyalty ID..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Select
                value={filterType}
                onValueChange={(value) => setFilterType(value as any)}
              >
                <SelectTrigger className="w-48">
                  <Filter className="w-4 h-4 mr-2" />
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Types</SelectItem>
                  <SelectItem value="recreational">Recreational</SelectItem>
                  <SelectItem value="medical">Medical</SelectItem>
                </SelectContent>
              </Select>
              <Select
                value={filterActive}
                onValueChange={(value) => setFilterActive(value as any)}
              >
                <SelectTrigger className="w-48">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="active">Active Only</SelectItem>
                  <SelectItem value="inactive">Inactive Only</SelectItem>
                </SelectContent>
              </Select>
            </div>

            {/* Customer Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
              {filteredCustomers.map((customer) => (
                <Card
                  key={customer.id}
                  className={`hover:shadow-md transition-shadow ${!customer.isActive ? "opacity-60" : ""}`}
                >
                  <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                      <div>
                        <h3 className="font-semibold">
                          {customer.firstName} {customer.lastName || ""}
                        </h3>
                        <p className="text-sm text-muted-foreground">
                          {customer.email}
                        </p>
                      </div>
                      <div className="flex flex-col gap-1">
                        <Badge
                          variant={
                            customer.customerType === "medical"
                              ? "default"
                              : "secondary"
                          }
                        >
                          {customer.customerType}
                        </Badge>
                        {!customer.isActive && (
                          <Badge variant="destructive">Inactive</Badge>
                        )}
                        {customer.loyaltyProgram?.isVeteran && (
                          <Badge variant="outline" className="text-xs">
                            Veteran
                          </Badge>
                        )}
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    <div className="grid grid-cols-1 gap-2 text-sm">
                      <div className="flex items-center gap-2">
                        <Phone className="w-4 h-4 text-muted-foreground" />
                        <span>{customer.phone}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <MapPin className="w-4 h-4 text-muted-foreground" />
                        <span>
                          {customer.address.city}, {customer.address.state}
                        </span>
                      </div>
                      {customer.loyaltyProgram && (
                        <div className="flex items-center gap-2">
                          <Star className="w-4 h-4 text-muted-foreground" />
                          <span>
                            {customer.loyaltyProgram.tier} -{" "}
                            {customer.loyaltyProgram.pointsBalance} pts
                          </span>
                        </div>
                      )}
                      <div className="flex items-center gap-2">
                        <CreditCard className="w-4 h-4 text-muted-foreground" />
                        <span>${customer.totalSpent.toFixed(2)} spent</span>
                      </div>
                    </div>

                    <div className="flex gap-1 pt-2 flex-wrap">
                      {/* Start Sale Button - Primary Action */}
                      {customer.isActive && (
                        <Button
                          size="sm"
                          onClick={() => startSaleForCustomer(customer)}
                          className="bg-green-600 hover:bg-green-700 text-white"
                        >
                          <ShoppingCart className="w-3 h-3 mr-1" />
                          Start Sale
                        </Button>
                      )}

                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedCustomer(customer);
                          setShowViewDialog(true);
                        }}
                      >
                        <Eye className="w-3 h-3 mr-1" />
                        View
                      </Button>
                      {customer.loyaltyProgram && (
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => {
                            setSelectedCustomer(customer);
                            setShowPurchaseHistoryDialog(true);
                          }}
                        >
                          <History className="w-3 h-3 mr-1" />
                          History
                        </Button>
                      )}
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedCustomer(customer);
                          setNewCustomer(customer);
                          setShowEditDialog(true);
                        }}
                      >
                        <Edit className="w-3 h-3 mr-1" />
                        Edit
                      </Button>
                      {customer.isActive ? (
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => deactivateCustomer(customer.id)}
                        >
                          Deactivate
                        </Button>
                      ) : (
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => activateCustomer(customer.id)}
                        >
                          Activate
                        </Button>
                      )}
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => deleteCustomer(customer.id)}
                        className="text-red-600 hover:text-red-700"
                      >
                        <Trash2 className="w-3 h-3" />
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>

            {filteredCustomers.length === 0 && (
              <div className="text-center py-12">
                <Users className="mx-auto h-12 w-12 text-gray-400" />
                <h3 className="mt-2 text-sm font-medium text-gray-900">
                  No customers found
                </h3>
                <p className="mt-1 text-sm text-gray-500">
                  Try adjusting your search or filter criteria.
                </p>
              </div>
            )}
          </TabsContent>

          <TabsContent value="analytics" className="space-y-6">
            {/* Customer Stats */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-blue-600">
                    {stats.total}
                  </div>
                  <div className="text-sm text-muted-foreground">
                    Total Customers
                  </div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-green-600">
                    {stats.active}
                  </div>
                  <div className="text-sm text-muted-foreground">
                    Active Customers
                  </div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-purple-600">
                    {stats.medical}
                  </div>
                  <div className="text-sm text-muted-foreground">
                    Medical Patients
                  </div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-orange-600">
                    {stats.loyaltyMembers}
                  </div>
                  <div className="text-sm text-muted-foreground">
                    Loyalty Members
                  </div>
                </CardContent>
              </Card>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Customer Type Distribution</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <div className="flex justify-between items-center">
                      <span>Recreational</span>
                      <span className="font-medium">
                        {stats.recreational} (
                        {((stats.recreational / stats.total) * 100).toFixed(1)}
                        %)
                      </span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span>Medical</span>
                      <span className="font-medium">
                        {stats.medical} (
                        {((stats.medical / stats.total) * 100).toFixed(1)}%)
                      </span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Special Categories</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <div className="flex justify-between items-center">
                      <span>Veterans</span>
                      <span className="font-medium">{stats.veterans}</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span>Inactive Customers</span>
                      <span className="font-medium">{stats.inactive}</span>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>
        </Tabs>

        {/* View Customer Dialog */}
        <Dialog open={showViewDialog} onOpenChange={setShowViewDialog}>
          <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Customer Details</DialogTitle>
            </DialogHeader>
            {selectedCustomer && (
              <div className="space-y-6">
                <div className="flex items-center justify-between">
                  <div>
                    <h2 className="text-xl font-semibold">
                      {selectedCustomer.firstName}{" "}
                      {selectedCustomer.lastName || ""}
                    </h2>
                    <p className="text-muted-foreground">
                      {selectedCustomer.email}
                    </p>
                  </div>
                  <div className="flex flex-col gap-2">
                    <Badge
                      variant={
                        selectedCustomer.customerType === "medical"
                          ? "default"
                          : "secondary"
                      }
                    >
                      {selectedCustomer.customerType}
                    </Badge>
                    {selectedCustomer.loyaltyProgram?.isVeteran && (
                      <Badge variant="outline">Veteran</Badge>
                    )}
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-4">
                    <div>
                      <Label className="text-base font-medium">
                        Contact Information
                      </Label>
                      <div className="space-y-2 mt-2 text-sm">
                        <div className="flex items-center gap-2">
                          <Phone className="w-4 h-4 text-muted-foreground" />
                          <span>{selectedCustomer.phone}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          <Mail className="w-4 h-4 text-muted-foreground" />
                          <span>{selectedCustomer.email}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          <MapPin className="w-4 h-4 text-muted-foreground" />
                          <span>
                            {selectedCustomer.address.street}
                            <br />
                            {selectedCustomer.address.city},{" "}
                            {selectedCustomer.address.state}{" "}
                            {selectedCustomer.address.zipCode}
                          </span>
                        </div>
                      </div>
                    </div>

                    {selectedCustomer.medicalCard && (
                      <div>
                        <Label className="text-base font-medium">
                          Medical Information
                        </Label>
                        <div className="space-y-2 mt-2 text-sm">
                          <div>
                            Card Number: {selectedCustomer.medicalCard.number}
                          </div>
                          <div>
                            Issue Date:{" "}
                            {new Date(
                              selectedCustomer.medicalCard.issueDate,
                            ).toLocaleDateString()}
                          </div>
                          <div>
                            Expiration:{" "}
                            {new Date(
                              selectedCustomer.medicalCard.expirationDate,
                            ).toLocaleDateString()}
                          </div>
                          <div>
                            Type:{" "}
                            {selectedCustomer.medicalCard.isPatient
                              ? "Patient"
                              : "Caregiver"}
                          </div>
                          {selectedCustomer.medicalCard.physicianName && (
                            <div>
                              Physician:{" "}
                              {selectedCustomer.medicalCard.physicianName}
                            </div>
                          )}
                        </div>
                      </div>
                    )}
                  </div>

                  <div className="space-y-4">
                    <div>
                      <Label className="text-base font-medium">
                        Purchase History
                      </Label>
                      <div className="space-y-2 mt-2 text-sm">
                        <div>
                          Total Spent: ${selectedCustomer.totalSpent.toFixed(2)}
                        </div>
                        <div>Total Visits: {selectedCustomer.totalVisits}</div>
                        <div>
                          Last Visit:{" "}
                          {selectedCustomer.lastVisit
                            ? new Date(
                                selectedCustomer.lastVisit,
                              ).toLocaleDateString()
                            : "Never"}
                        </div>
                        <div>
                          Customer Since:{" "}
                          {new Date(
                            selectedCustomer.createdDate,
                          ).toLocaleDateString()}
                        </div>
                      </div>
                    </div>

                    {selectedCustomer.loyaltyProgram && (
                      <div>
                        <Label className="text-base font-medium">
                          Loyalty Program
                        </Label>
                        <div className="bg-green-50 p-4 rounded-lg mt-2">
                          <div className="text-lg font-bold text-green-800">
                            {selectedCustomer.loyaltyProgram.tier}
                          </div>
                          <div className="text-sm text-green-700">
                            Member ID:{" "}
                            {selectedCustomer.loyaltyProgram.memberId}
                          </div>
                          <div className="text-sm text-green-700">
                            Points:{" "}
                            {selectedCustomer.loyaltyProgram.pointsBalance}
                          </div>
                          <div className="text-xs text-green-600 mt-1">
                            Joined:{" "}
                            {new Date(
                              selectedCustomer.loyaltyProgram.joinDate,
                            ).toLocaleDateString()}
                          </div>
                        </div>
                      </div>
                    )}
                  </div>
                </div>

                {selectedCustomer.notes && (
                  <div>
                    <Label className="text-base font-medium">Notes</Label>
                    <div className="mt-2 p-3 bg-gray-50 rounded-lg text-sm">
                      {selectedCustomer.notes}
                    </div>
                  </div>
                )}

                {selectedCustomer.preferredProducts.length > 0 && (
                  <div>
                    <Label className="text-base font-medium">
                      Preferred Products
                    </Label>
                    <div className="flex flex-wrap gap-2 mt-2">
                      {selectedCustomer.preferredProducts.map(
                        (product, index) => (
                          <Badge key={index} variant="outline">
                            {product}
                          </Badge>
                        ),
                      )}
                    </div>
                  </div>
                )}
              </div>
            )}
          </DialogContent>
        </Dialog>

        {/* Edit Customer Dialog */}
        <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
          <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Edit Customer</DialogTitle>
            </DialogHeader>
            <div className="space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="editFirstName">First Name *</Label>
                  <Input
                    id="editFirstName"
                    value={newCustomer.firstName || ""}
                    onChange={(e) =>
                      setNewCustomer((prev) => ({
                        ...prev,
                        firstName: e.target.value,
                      }))
                    }
                    placeholder="Enter first name"
                  />
                </div>
                <div>
                  <Label htmlFor="editLastName">Last Name (Optional)</Label>
                  <Input
                    id="editLastName"
                    value={newCustomer.lastName || ""}
                    onChange={(e) =>
                      setNewCustomer((prev) => ({
                        ...prev,
                        lastName: e.target.value,
                      }))
                    }
                    placeholder="Enter last name (optional)"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="editEmail">Email *</Label>
                  <Input
                    id="editEmail"
                    type="email"
                    value={newCustomer.email || ""}
                    onChange={(e) =>
                      setNewCustomer((prev) => ({
                        ...prev,
                        email: e.target.value,
                      }))
                    }
                    placeholder="customer@email.com"
                  />
                </div>
                <div>
                  <Label htmlFor="editPhone">Phone *</Label>
                  <Input
                    id="editPhone"
                    value={newCustomer.phone || ""}
                    onChange={(e) =>
                      setNewCustomer((prev) => ({
                        ...prev,
                        phone: e.target.value,
                      }))
                    }
                    placeholder="(555) 123-4567"
                  />
                </div>
              </div>

              <div>
                <Label htmlFor="editNotes">Notes</Label>
                <textarea
                  id="editNotes"
                  value={newCustomer.notes || ""}
                  onChange={(e) =>
                    setNewCustomer((prev) => ({
                      ...prev,
                      notes: e.target.value,
                    }))
                  }
                  placeholder="Add any notes about this customer..."
                  className="w-full p-2 border rounded-md text-sm"
                  rows={3}
                />
              </div>

              <div className="flex gap-2">
                <Button
                  onClick={editCustomer}
                  className="flex-1"
                  disabled={
                    !newCustomer.firstName ||
                    !newCustomer.email ||
                    !newCustomer.phone
                  }
                >
                  Save Changes
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowEditDialog(false);
                    setSelectedCustomer(null);
                    setNewCustomer({});
                  }}
                  className="flex-1"
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Purchase History Dialog */}
        <Dialog
          open={showPurchaseHistoryDialog}
          onOpenChange={setShowPurchaseHistoryDialog}
        >
          <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>
                Purchase History - {selectedCustomer?.firstName}{" "}
                {selectedCustomer?.lastName}
                {selectedCustomer?.loyaltyProgram && (
                  <Badge className="ml-2" variant="outline">
                    {selectedCustomer.loyaltyProgram.tier} Member
                  </Badge>
                )}
              </DialogTitle>
            </DialogHeader>

            {selectedCustomer && (
              <div className="space-y-4">
                {/* Summary Stats */}
                <div className="grid grid-cols-3 gap-4 mb-6">
                  <div className="text-center p-4 bg-blue-50 rounded-lg">
                    <div className="text-2xl font-bold text-blue-600">
                      {selectedCustomer.totalVisits}
                    </div>
                    <div className="text-sm text-blue-700">Total Visits</div>
                  </div>
                  <div className="text-center p-4 bg-green-50 rounded-lg">
                    <div className="text-2xl font-bold text-green-600">
                      ${selectedCustomer.totalSpent.toFixed(2)}
                    </div>
                    <div className="text-sm text-green-700">Total Spent</div>
                  </div>
                  <div className="text-center p-4 bg-purple-50 rounded-lg">
                    <div className="text-2xl font-bold text-purple-600">
                      {selectedCustomer.loyaltyProgram?.pointsBalance || 0}
                    </div>
                    <div className="text-sm text-purple-700">
                      Loyalty Points
                    </div>
                  </div>
                </div>

                {/* Recent Purchases (Last 10 visits) */}
                <div>
                  <h3 className="text-lg font-semibold mb-3 flex items-center gap-2">
                    <ShoppingCart className="w-5 h-5" />
                    {selectedCustomer.customerType === "medical"
                      ? "Complete Purchase History (All Transactions)"
                      : "Recent Purchases (Last 10 Visits)"}
                  </h3>

                  {selectedCustomer.purchaseHistory &&
                  selectedCustomer.purchaseHistory.length > 0 ? (
                    <div className="space-y-3">
                      {selectedCustomer.purchaseHistory
                        .sort(
                          (a, b) =>
                            new Date(b.date).getTime() -
                            new Date(a.date).getTime(),
                        )
                        .slice(
                          0,
                          selectedCustomer.customerType === "medical"
                            ? undefined
                            : 10,
                        )
                        .map((purchase) => (
                          <div
                            key={purchase.id}
                            className="border rounded-lg p-4 hover:bg-gray-50"
                          >
                            <div className="flex justify-between items-start mb-3">
                              <div>
                                <div className="font-medium">{purchase.id}</div>
                                <div className="text-sm text-gray-600">
                                  {new Date(purchase.date).toLocaleDateString()}{" "}
                                  at{" "}
                                  {new Date(purchase.date).toLocaleTimeString()}
                                </div>
                                <div className="text-sm text-gray-600">
                                  Cashier: {purchase.employee}
                                </div>
                              </div>
                              <div className="text-right">
                                <div className="text-lg font-bold">
                                  ${purchase.total.toFixed(2)}
                                </div>
                                <div className="text-sm text-gray-600">
                                  {purchase.paymentMethod}
                                </div>
                                {purchase.loyaltyPointsEarned && (
                                  <div className="text-sm text-green-600">
                                    +{purchase.loyaltyPointsEarned} pts
                                  </div>
                                )}
                                <Button
                                  size="sm"
                                  variant="outline"
                                  className="mt-2"
                                  onClick={() => {
                                    // Generate and print receipt
                                    const receiptContent = `
CANNABEST DISPENSARY
Receipt Re-Print
Transaction: ${purchase.id}
Date: ${new Date(purchase.date).toLocaleString()}
Cashier: ${purchase.employee}

ITEMS:
${purchase.items.map((item) => `${item.quantity}x ${item.name} - $${item.total.toFixed(2)}`).join("\n")}

Subtotal: $${purchase.subtotal.toFixed(2)}
Tax: $${purchase.tax.toFixed(2)}
TOTAL: $${purchase.total.toFixed(2)}

Payment: ${purchase.paymentMethod}
${purchase.loyaltyPointsEarned ? `Loyalty Points Earned: ${purchase.loyaltyPointsEarned}` : ""}

Thank you for shopping with us!
                                  `.trim();

                                    const printWindow = window.open(
                                      "",
                                      "_blank",
                                    );
                                    if (printWindow) {
                                      printWindow.document.write(`
                                      <html>
                                        <head>
                                          <title>Receipt - ${purchase.id}</title>
                                          <style>
                                            body { font-family: monospace; padding: 20px; }
                                            pre { white-space: pre-wrap; }
                                          </style>
                                        </head>
                                        <body>
                                          <pre>${receiptContent}</pre>
                                          <script>window.print(); window.close();</script>
                                        </body>
                                      </html>
                                    `);
                                      printWindow.document.close();
                                    }
                                  }}
                                >
                                  <Printer className="w-3 h-3 mr-1" />
                                  Re-print Receipt
                                </Button>
                              </div>
                            </div>

                            <div className="space-y-2">
                              <div className="text-sm font-medium text-gray-700">
                                Items Purchased:
                              </div>
                              {purchase.items.map((item) => (
                                <div
                                  key={item.id}
                                  className="flex justify-between items-center text-sm bg-gray-50 p-2 rounded"
                                >
                                  <div className="flex items-center gap-2">
                                    <Package className="w-4 h-4 text-gray-500" />
                                    <div>
                                      <span className="font-medium">
                                        {item.name}
                                      </span>
                                      <span className="text-gray-600 ml-2">
                                        ({item.category})
                                      </span>
                                      {item.metrcTag && (
                                        <span className="text-xs text-gray-500 ml-2">
                                          [...{item.metrcTag.slice(-5)}]
                                        </span>
                                      )}
                                    </div>
                                  </div>
                                  <div className="text-right">
                                    <div>
                                      {item.quantity}x $
                                      {item.unitPrice.toFixed(2)}
                                    </div>
                                    <div className="font-medium">
                                      ${item.total.toFixed(2)}
                                    </div>
                                  </div>
                                </div>
                              ))}
                            </div>

                            <div className="flex justify-between text-sm mt-3 pt-3 border-t">
                              <span>
                                Subtotal: ${purchase.subtotal.toFixed(2)}
                              </span>
                              <span>Tax: ${purchase.tax.toFixed(2)}</span>
                              <span className="font-medium">
                                Total: ${purchase.total.toFixed(2)}
                              </span>
                            </div>
                          </div>
                        ))}
                    </div>
                  ) : (
                    <div className="text-center py-8 text-gray-500">
                      <ShoppingCart className="w-12 h-12 text-gray-300 mx-auto mb-3" />
                      <p>No purchase history available for this customer</p>
                    </div>
                  )}
                </div>

                <div className="flex justify-end pt-4">
                  <Button
                    variant="outline"
                    onClick={() => setShowPurchaseHistoryDialog(false)}
                  >
                    Close
                  </Button>
                </div>
              </div>
            )}
          </DialogContent>
        </Dialog>
      </div>
    </div>
  );
}
