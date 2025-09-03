import { useState } from "react";
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
  Star
} from "lucide-react";

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
  medicalOnly: boolean;
  minimumPurchase?: number;
  minimumPurchaseType?: 'dollars' | 'grams';
  maxUses?: number;
  currentUses: number;
}

const availableCategories = [
  "Flower", "Pre-Rolls", "Concentrates", "Extracts", "Edibles", "Topicals",
  "Tinctures", "Vapes", "Inhalable Cannabinoids", "Clones", "Hemp", "Paraphernalia", "Accessories"
];

const sampleProducts = [
  { id: "1", name: "Blue Dream", category: "Flower" },
  { id: "2", name: "OG Kush", category: "Flower" },
  { id: "3", name: "Strawberry Gummies", category: "Edibles" },
  { id: "4", name: "Sour Diesel Pre-Roll", category: "Pre-Rolls" },
  { id: "5", name: "Live Resin Cart", category: "Concentrates" }
];

const mockDeals: Deal[] = [
  {
    id: "1",
    name: "Happy Hour Special",
    description: "20% off all flower products during weekday happy hours",
    type: "percentage",
    discountValue: 20,
    categories: ["Flower"],
    specificItems: [],
    startDate: "2024-01-15",
    endDate: "2024-12-31",
    isActive: true,
    frequency: "daily",
    emailCustomers: true,
    loyaltyOnly: false,
    medicalOnly: false,
    minimumPurchase: 25,
    minimumPurchaseType: 'dollars',
    currentUses: 156
  },
  {
    id: "2",
    name: "BOGO Edibles Friday",
    description: "Buy one get one 50% off on all edibles every Friday",
    type: "bogo",
    discountValue: 50,
    categories: ["Edibles"],
    specificItems: [],
    startDate: "2024-01-01",
    endDate: "2024-12-31",
    isActive: true,
    frequency: "weekly",
    dayOfWeek: "Friday",
    emailCustomers: true,
    loyaltyOnly: true,
    medicalOnly: false,
    minimumPurchaseType: 'dollars',
    currentUses: 89
  },
  {
    id: "3",
    name: "Monthly Member Special",
    description: "$10 off purchases over $100 for loyalty members",
    type: "fixed",
    discountValue: 10,
    categories: [],
    specificItems: [],
    startDate: "2024-01-01",
    endDate: "2024-12-31",
    isActive: true,
    frequency: "always",
    emailCustomers: false,
    loyaltyOnly: true,
    medicalOnly: false,
    minimumPurchase: 100,
    minimumPurchaseType: 'dollars',
    maxUses: 1000,
    currentUses: 234
  }
];

export default function Deals() {
  const [deals, setDeals] = useState<Deal[]>(mockDeals);
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [selectedDeal, setSelectedDeal] = useState<Deal | null>(null);
  const [showEditDialog, setShowEditDialog] = useState(false);

  const [newDeal, setNewDeal] = useState<Partial<Deal>>({
    name: "",
    description: "",
    type: "percentage",
    discountValue: 0,
    categories: [],
    specificItems: [],
    startDate: new Date().toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0],
    isActive: true,
    frequency: "always",
    emailCustomers: false,
    loyaltyOnly: false,
    medicalOnly: false,
    minimumPurchaseType: 'dollars',
    currentUses: 0
  });

  const createDeal = () => {
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
      currentUses: 0
    };

    setDeals(prev => [...prev, deal]);
    setShowCreateDialog(false);
    resetForm();
  };

  const editDeal = () => {
    if (!selectedDeal || !newDeal.name || !newDeal.description || !newDeal.discountValue) {
      alert("Please fill in all required fields");
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
      minimumPurchaseType: newDeal.minimumPurchaseType || 'dollars',
      maxUses: newDeal.maxUses
    };

    setDeals(prev => prev.map(deal =>
      deal.id === selectedDeal.id ? updatedDeal : deal
    ));

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
      startDate: new Date().toISOString().split('T')[0],
      endDate: new Date().toISOString().split('T')[0],
      isActive: true,
      frequency: "always",
      emailCustomers: false,
      loyaltyOnly: false,
      medicalOnly: false,
      currentUses: 0
    });
  };

  const toggleDealStatus = (dealId: string) => {
    setDeals(prev => prev.map(deal => 
      deal.id === dealId ? { ...deal, isActive: !deal.isActive } : deal
    ));
  };

  const deleteDeal = (dealId: string) => {
    if (confirm("Are you sure you want to delete this deal?")) {
      setDeals(prev => prev.filter(deal => deal.id !== dealId));
    }
  };

  const sendDealEmail = (deal: Deal) => {
    console.log(`Sending deal email for: ${deal.name}`);
    alert(`Email campaign for "${deal.name}" has been sent to loyalty program members!`);
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

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Deals & Specials</h1>
            <p className="text-sm opacity-80">Manage sales, discounts, and promotions</p>
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
                      onChange={(e) => setNewDeal(prev => ({...prev, name: e.target.value}))}
                      placeholder="Enter deal name"
                    />
                  </div>
                  <div>
                    <Label htmlFor="deal-type">Discount Type</Label>
                    <Select value={newDeal.type} onValueChange={(value: Deal['type']) => setNewDeal(prev => ({...prev, type: value}))}>
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
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={newDeal.description}
                    onChange={(e) => setNewDeal(prev => ({...prev, description: e.target.value}))}
                    placeholder="Describe the deal..."
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="discount-value">
                      {newDeal.type === 'percentage' ? 'Percentage (%)' : 
                       newDeal.type === 'fixed' ? 'Amount ($)' : 'Discount (%)'}
                    </Label>
                    <Input
                      id="discount-value"
                      type="number"
                      value={newDeal.discountValue}
                      onChange={(e) => setNewDeal(prev => ({...prev, discountValue: parseFloat(e.target.value) || 0}))}
                      placeholder="0"
                    />
                  </div>
                  <div>
                    <Label htmlFor="frequency">Frequency</Label>
                    <Select value={newDeal.frequency} onValueChange={(value: Deal['frequency']) => setNewDeal(prev => ({...prev, frequency: value}))}>
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

                {newDeal.frequency === 'weekly' && (
                  <div>
                    <Label htmlFor="day-of-week">Day of Week</Label>
                    <Select value={newDeal.dayOfWeek} onValueChange={(value) => setNewDeal(prev => ({...prev, dayOfWeek: value}))}>
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

                {newDeal.frequency === 'monthly' && (
                  <div>
                    <Label htmlFor="day-of-month">Day of Month</Label>
                    <Input
                      id="day-of-month"
                      type="number"
                      min="1"
                      max="31"
                      value={newDeal.dayOfMonth || ""}
                      onChange={(e) => setNewDeal(prev => ({...prev, dayOfMonth: parseInt(e.target.value) || undefined}))}
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
                      onChange={(e) => setNewDeal(prev => ({...prev, startDate: e.target.value}))}
                    />
                  </div>
                  <div>
                    <Label htmlFor="end-date">End Date (Optional)</Label>
                    <Input
                      id="end-date"
                      type="date"
                      value={newDeal.endDate}
                      onChange={(e) => setNewDeal(prev => ({...prev, endDate: e.target.value}))}
                    />
                  </div>
                </div>

                <div>
                  <Label>Categories</Label>
                  <div className="grid grid-cols-3 gap-2 mt-2">
                    {availableCategories.map(category => (
                      <div key={category} className="flex items-center space-x-2">
                        <Checkbox
                          id={`category-${category}`}
                          checked={newDeal.categories?.includes(category)}
                          onCheckedChange={(checked) => {
                            if (checked) {
                              setNewDeal(prev => ({...prev, categories: [...(prev.categories || []), category]}));
                            } else {
                              setNewDeal(prev => ({...prev, categories: prev.categories?.filter(c => c !== category) || []}));
                            }
                          }}
                        />
                        <Label htmlFor={`category-${category}`} className="text-sm">{category}</Label>
                      </div>
                    ))}
                  </div>
                </div>

                <div>
                  <Label>Minimum Purchase</Label>
                  <div className="grid grid-cols-3 gap-2 mt-2">
                    <div>
                      <Select value={newDeal.minimumPurchaseType || 'dollars'} onValueChange={(value: 'dollars' | 'grams') => setNewDeal(prev => ({...prev, minimumPurchaseType: value}))}>
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
                        onChange={(e) => setNewDeal(prev => ({...prev, minimumPurchase: parseFloat(e.target.value) || undefined}))}
                        placeholder={`Minimum ${newDeal.minimumPurchaseType === 'grams' ? 'grams' : 'dollars'}`}
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
                    onChange={(e) => setNewDeal(prev => ({...prev, maxUses: parseInt(e.target.value) || undefined}))}
                    placeholder="Unlimited"
                  />
                </div>

                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <div>
                      <Label>Email Customers</Label>
                      <p className="text-sm text-gray-600">Send email notification to loyalty program members</p>
                    </div>
                    <Switch
                      checked={newDeal.emailCustomers}
                      onCheckedChange={(checked) => setNewDeal(prev => ({...prev, emailCustomers: checked}))}
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div>
                      <Label>Loyalty Members Only</Label>
                      <p className="text-sm text-gray-600">Restrict deal to loyalty program members</p>
                    </div>
                    <Switch
                      checked={newDeal.loyaltyOnly}
                      onCheckedChange={(checked) => setNewDeal(prev => ({...prev, loyaltyOnly: checked}))}
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div>
                      <Label>Medical/Caregiver Only</Label>
                      <p className="text-sm text-gray-600">Only available to medical patients and caregivers</p>
                    </div>
                    <Switch
                      checked={newDeal.medicalOnly}
                      onCheckedChange={(checked) => setNewDeal(prev => ({...prev, medicalOnly: checked}))}
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div>
                      <Label>Active</Label>
                      <p className="text-sm text-gray-600">Make deal active immediately</p>
                    </div>
                    <Switch
                      checked={newDeal.isActive}
                      onCheckedChange={(checked) => setNewDeal(prev => ({...prev, isActive: checked}))}
                    />
                  </div>
                </div>

                <div className="flex gap-2">
                  <Button onClick={createDeal} className="flex-1">
                    Create Deal
                  </Button>
                  <Button variant="outline" onClick={() => setShowCreateDialog(false)} className="flex-1">
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
              <div className="text-2xl font-bold text-green-600">{deals.filter(d => d.isActive).length}</div>
              <div className="text-sm text-muted-foreground">Active Deals</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-blue-600">{deals.reduce((sum, d) => sum + d.currentUses, 0)}</div>
              <div className="text-sm text-muted-foreground">Total Uses</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-purple-600">{deals.filter(d => d.loyaltyOnly).length}</div>
              <div className="text-sm text-muted-foreground">Loyalty Deals</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-green-600">{deals.filter(d => d.medicalOnly).length}</div>
              <div className="text-sm text-muted-foreground">Medical Deals</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-orange-600">{deals.filter(d => d.emailCustomers).length}</div>
              <div className="text-sm text-muted-foreground">Email Campaigns</div>
            </CardContent>
          </Card>
        </div>

        {/* Deals List */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
          {deals.map(deal => (
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
                      <Badge variant="outline" className="bg-green-50 text-green-700 border-green-300">
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
                      {deal.type === 'percentage' ? `${deal.discountValue}%` : 
                       deal.type === 'fixed' ? `$${deal.discountValue}` : 
                       deal.type === 'bogo' ? `BOGO ${deal.discountValue}%` : 
                       `${deal.discountValue}% Bulk`}
                    </div>
                  </div>
                  <div>
                    <span className="font-medium">Usage:</span>
                    <div className="text-lg font-bold">
                      {deal.currentUses}{deal.maxUses ? `/${deal.maxUses}` : ''}
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
                        {deal.categories.slice(0, 2).map(category => (
                          <Badge key={category} variant="outline" className="text-xs">{category}</Badge>
                        ))}
                        {deal.categories.length > 2 && (
                          <span className="text-xs text-muted-foreground">+{deal.categories.length - 2} more</span>
                        )}
                      </div>
                    </div>
                  )}
                  {deal.minimumPurchase && (
                    <div className="flex items-center gap-2">
                      <DollarSign className="w-4 h-4 text-muted-foreground" />
                      <span>
                        Min. {deal.minimumPurchaseType === 'grams' ? `${deal.minimumPurchase}g` : `$${deal.minimumPurchase}`}
                      </span>
                    </div>
                  )}
                </div>

                <div className="flex gap-2 pt-2">
                  <Button size="sm" variant="outline" onClick={() => toggleDealStatus(deal.id)}>
                    {deal.isActive ? "Deactivate" : "Activate"}
                  </Button>
                  {deal.emailCustomers && (
                    <Button size="sm" variant="outline" onClick={() => sendDealEmail(deal)}>
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
                        maxUses: deal.maxUses
                      });
                      setShowEditDialog(true);
                    }}
                  >
                    <Edit className="w-3 h-3 mr-1" />
                    Edit
                  </Button>
                  <Button size="sm" variant="destructive" onClick={() => deleteDeal(deal.id)}>
                    <Trash2 className="w-3 h-3" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
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
                  onChange={(e) => setNewDeal(prev => ({...prev, name: e.target.value}))}
                  placeholder="Enter deal name"
                />
              </div>
              <div>
                <Label htmlFor="edit-deal-type">Discount Type</Label>
                <Select value={newDeal.type} onValueChange={(value: Deal['type']) => setNewDeal(prev => ({...prev, type: value}))}>
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
                onChange={(e) => setNewDeal(prev => ({...prev, description: e.target.value}))}
                placeholder="Describe the deal..."
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="edit-discount-value">
                  {newDeal.type === 'percentage' ? 'Percentage (%)' :
                   newDeal.type === 'fixed' ? 'Amount ($)' : 'Discount (%)'}
                </Label>
                <Input
                  id="edit-discount-value"
                  type="number"
                  value={newDeal.discountValue}
                  onChange={(e) => setNewDeal(prev => ({...prev, discountValue: parseFloat(e.target.value) || 0}))}
                  placeholder="0"
                />
              </div>
              <div>
                <Label htmlFor="edit-frequency">Frequency</Label>
                <Select value={newDeal.frequency} onValueChange={(value: Deal['frequency']) => setNewDeal(prev => ({...prev, frequency: value}))}>
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

            <div className="flex gap-2">
              <Button onClick={editDeal} className="flex-1">
                Update Deal
              </Button>
              <Button variant="outline" onClick={() => setShowEditDialog(false)} className="flex-1">
                Cancel
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
