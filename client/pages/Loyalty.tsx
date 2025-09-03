import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { Checkbox } from "@/components/ui/checkbox";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Users,
  Plus,
  Search,
  Star,
  Gift,
  TrendingUp,
  Calendar,
  Phone,
  Mail,
  DollarSign,
  Award,
  Eye,
  History,
  Edit,
  Trash2
} from "lucide-react";

interface LoyaltyCustomer {
  id: string;
  name: string;
  phone: string;
  email: string;
  joinDate: string;
  totalSpent: number;
  totalVisits: number;
  pointsBalance: number;
  pointsEarned: number;
  pointsRedeemed: number;
  tier: 'Bronze' | 'Silver' | 'Gold' | 'Platinum';
  dataRetentionConsent: boolean;
  salesHistory: Purchase[];
  lastVisit: string;
  isVeteran: boolean;
}

interface Purchase {
  id: string;
  date: string;
  total: number;
  pointsEarned: number;
  items: string[];
}

interface TierConfig {
  name: string;
  threshold: number;
  pointsMultiplier: number;
  benefits: string[];
}

const defaultTierThresholds = {
  Bronze: 0,
  Silver: 500,
  Gold: 1500,
  Platinum: 3000
};

const tierColors = {
  Bronze: "bg-amber-100 text-amber-800",
  Silver: "bg-gray-100 text-gray-800",
  Gold: "bg-yellow-100 text-yellow-800",
  Platinum: "bg-purple-100 text-purple-800"
};

const mockCustomers: LoyaltyCustomer[] = [
  {
    id: "1",
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
    isVeteran: false,
    salesHistory: [
      { id: "p1", date: "2024-01-14", total: 85.50, pointsEarned: 8, items: ["Blue Dream", "Edible Gummies"] },
      { id: "p2", date: "2024-01-10", total: 120.25, pointsEarned: 12, items: ["OG Kush", "Pre-Rolls"] }
    ]
  },
  {
    id: "2",
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
    isVeteran: true,
    salesHistory: [
      { id: "p3", date: "2024-01-13", total: 95.00, pointsEarned: 9, items: ["Live Resin Cart", "Flower"] },
      { id: "p4", date: "2024-01-08", total: 150.75, pointsEarned: 15, items: ["Premium Flower", "Concentrates"] }
    ]
  },
  {
    id: "3",
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
    isVeteran: true,
    salesHistory: [
      { id: "p5", date: "2024-01-15", total: 200.50, pointsEarned: 20, items: ["Premium Products", "Accessories"] }
    ]
  }
];

export default function Loyalty() {
  const [customers, setCustomers] = useState<LoyaltyCustomer[]>(mockCustomers);
  const [searchQuery, setSearchQuery] = useState("");
  const [showSignupDialog, setShowSignupDialog] = useState(false);
  const [selectedCustomer, setSelectedCustomer] = useState<LoyaltyCustomer | null>(null);
  const [showDetailsDialog, setShowDetailsDialog] = useState(false);
  const [showTierEditDialog, setShowTierEditDialog] = useState(false);
  const [tiers, setTiers] = useState<TierConfig[]>([
    { name: "Bronze", threshold: 0, pointsMultiplier: 1, benefits: ["1% back in points", "Birthday rewards"] },
    { name: "Silver", threshold: 500, pointsMultiplier: 2, benefits: ["2% back in points", "Birthday rewards", "Exclusive deals"] },
    { name: "Gold", threshold: 1500, pointsMultiplier: 3, benefits: ["3% back in points", "Birthday rewards", "Exclusive deals", "Early access to sales"] },
    { name: "Platinum", threshold: 3000, pointsMultiplier: 5, benefits: ["5% back in points", "Birthday rewards", "Exclusive deals", "Early access to sales", "VIP customer service"] }
  ]);
  const [editingTier, setEditingTier] = useState<TierConfig | null>(null);
  const [showPointsDialog, setShowPointsDialog] = useState(false);
  const [selectedCustomerForPoints, setSelectedCustomerForPoints] = useState<LoyaltyCustomer | null>(null);
  const [pointsToAdd, setPointsToAdd] = useState("");
  const [pointsReason, setPointsReason] = useState("");

  const [newCustomer, setNewCustomer] = useState({
    name: "",
    phone: "",
    email: "",
    dataRetentionConsent: false,
    isVeteran: false
  });

  const filteredCustomers = customers.filter(customer =>
    customer.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    customer.phone.includes(searchQuery) ||
    customer.email.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const getTierFromSpending = (totalSpent: number): LoyaltyCustomer['tier'] => {
    // Sort tiers by threshold descending to find the highest qualifying tier
    const sortedTiers = [...tiers].sort((a, b) => b.threshold - a.threshold);

    for (const tier of sortedTiers) {
      if (totalSpent >= tier.threshold) {
        return tier.name as LoyaltyCustomer['tier'];
      }
    }

    return 'Bronze'; // Default fallback
  };

  const calculatePoints = (amount: number, tierName: string = 'Bronze'): number => {
    const tier = tiers.find(t => t.name === tierName);
    const multiplier = tier ? tier.pointsMultiplier : 1;
    return Math.floor(amount * (multiplier / 100) * 100); // Points based on tier multiplier
  };

  const addTier = () => {
    const newTier: TierConfig = {
      name: "New Tier",
      threshold: 0,
      pointsMultiplier: 1,
      benefits: ["Basic benefits"]
    };
    setTiers(prev => [...prev, newTier]);
    setEditingTier(newTier);
    setShowTierEditDialog(true);
  };

  const updateTier = (updatedTier: TierConfig) => {
    setTiers(prev => prev.map(tier =>
      tier.name === editingTier?.name ? updatedTier : tier
    ));
    setShowTierEditDialog(false);
    setEditingTier(null);
  };

  const deleteTier = (tierName: string) => {
    if (tiers.length <= 1) {
      alert("You must have at least one tier");
      return;
    }

    if (confirm(`Are you sure you want to delete the ${tierName} tier?`)) {
      setTiers(prev => prev.filter(tier => tier.name !== tierName));
    }
  };

  const editTier = (tier: TierConfig) => {
    setEditingTier(tier);
    setShowTierEditDialog(true);
  };

  const addPointsManually = () => {
    if (!selectedCustomerForPoints || !pointsToAdd || !pointsReason.trim()) {
      alert("Please fill in points amount and reason");
      return;
    }

    const points = parseInt(pointsToAdd);
    if (points <= 0) {
      alert("Points must be greater than 0");
      return;
    }

    setCustomers(prev => prev.map(customer =>
      customer.id === selectedCustomerForPoints.id
        ? {
            ...customer,
            pointsBalance: customer.pointsBalance + points,
            pointsEarned: customer.pointsEarned + points
          }
        : customer
    ));

    alert(`Successfully added ${points} points to ${selectedCustomerForPoints.name} for: ${pointsReason}`);

    setShowPointsDialog(false);
    setSelectedCustomerForPoints(null);
    setPointsToAdd("");
    setPointsReason("");
  };

  const signupCustomer = () => {
    if (!newCustomer.name || !newCustomer.phone || !newCustomer.email || !newCustomer.dataRetentionConsent) {
      alert("Please fill all fields and consent to data retention");
      return;
    }

    const customer: LoyaltyCustomer = {
      id: Date.now().toString(),
      name: newCustomer.name,
      phone: newCustomer.phone,
      email: newCustomer.email,
      joinDate: new Date().toISOString().split('T')[0],
      totalSpent: 0,
      totalVisits: 0,
      pointsBalance: 0,
      pointsEarned: 0,
      pointsRedeemed: 0,
      tier: 'Bronze',
      dataRetentionConsent: newCustomer.dataRetentionConsent,
      salesHistory: [],
      lastVisit: "",
      isVeteran: newCustomer.isVeteran
    };

    setCustomers(prev => [...prev, customer]);
    setShowSignupDialog(false);
    setNewCustomer({ name: "", phone: "", email: "", dataRetentionConsent: false, isVeteran: false });
    alert(`Welcome ${customer.name}! You've been enrolled in our loyalty program.`);
  };

  const deleteCustomer = (customerId: string) => {
    const customer = customers.find(c => c.id === customerId);
    if (!customer) return;

    if (confirm(`Are you sure you want to delete ${customer.name} from the loyalty program? This action cannot be undone and will remove all their points and history.`)) {
      setCustomers(prev => prev.filter(c => c.id !== customerId));
      alert(`${customer.name} has been removed from the loyalty program.`);
    }
  };

  const totalCustomers = customers.length;
  const totalPointsAwarded = customers.reduce((sum, c) => sum + c.pointsEarned, 0);
  const totalPointsRedeemed = customers.reduce((sum, c) => sum + c.pointsRedeemed, 0);
  const averageSpending = customers.reduce((sum, c) => sum + c.totalSpent, 0) / customers.length;
  const veteranCount = customers.filter(c => c.isVeteran).length;
  const activePointsBalance = customers.reduce((sum, c) => sum + c.pointsBalance, 0);
  const redemptionRate = totalPointsAwarded > 0 ? ((totalPointsRedeemed / totalPointsAwarded) * 100) : 0;
  const tierDistribution = tiers.reduce((acc, tier) => {
    acc[tier.name] = customers.filter(c => c.tier === tier.name).length;
    return acc;
  }, {} as Record<string, number>);

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Loyalty Program</h1>
            <p className="text-sm opacity-80">Manage customer rewards and engagement</p>
          </div>
          <Dialog open={showSignupDialog} onOpenChange={setShowSignupDialog}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="w-4 h-4 mr-2" />
                Enroll Customer
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Loyalty Program Signup</DialogTitle>
              </DialogHeader>
              <div className="space-y-4">
                <div>
                  <Label htmlFor="customer-name">Full Name *</Label>
                  <Input
                    id="customer-name"
                    value={newCustomer.name}
                    onChange={(e) => setNewCustomer(prev => ({...prev, name: e.target.value}))}
                    placeholder="Enter full name"
                  />
                </div>
                <div>
                  <Label htmlFor="customer-phone">Phone Number *</Label>
                  <Input
                    id="customer-phone"
                    value={newCustomer.phone}
                    onChange={(e) => setNewCustomer(prev => ({...prev, phone: e.target.value}))}
                    placeholder="(555) 123-4567"
                  />
                </div>
                <div>
                  <Label htmlFor="customer-email">Email Address *</Label>
                  <Input
                    id="customer-email"
                    type="email"
                    value={newCustomer.email}
                    onChange={(e) => setNewCustomer(prev => ({...prev, email: e.target.value}))}
                    placeholder="customer@email.com"
                  />
                </div>
                
                <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                  <h3 className="font-medium mb-2">Loyalty Program Benefits</h3>
                  <div className="text-sm space-y-2">
                    <div className="font-medium">Tier-Based Rewards:</div>
                    <ul className="space-y-1 ml-2">
                      <li>• Bronze Tier (Starting): 1% back in points</li>
                      <li>• Silver Tier ($500+ spent): 2% back in points</li>
                      <li>• Gold Tier ($1,500+ spent): 3% back in points</li>
                      <li>• Platinum Tier ($3,000+ spent): 5% back in points</li>
                    </ul>
                    <div className="mt-2">
                      <li>• Exclusive deals and early access to sales</li>
                      <li>• Birthday rewards and special offers</li>
                      <li>• Track your purchase history</li>
                    </div>
                  </div>
                </div>

                <div className="space-y-4">
                  <div className="flex items-start space-x-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <Checkbox
                      id="veteran-status"
                      checked={newCustomer.isVeteran}
                      onCheckedChange={(checked) => setNewCustomer(prev => ({...prev, isVeteran: checked as boolean}))}
                    />
                    <div className="space-y-2">
                      <Label htmlFor="veteran-status" className="text-sm font-medium">
                        Veteran Status
                      </Label>
                      <p className="text-xs text-gray-600">
                        I am a U.S. military veteran and would like to receive the 10% veteran discount
                        on all purchases (including Green Leaf Special items).
                      </p>
                    </div>
                  </div>

                  <div className="flex items-start space-x-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <Checkbox
                      id="data-consent"
                      checked={newCustomer.dataRetentionConsent}
                      onCheckedChange={(checked) => setNewCustomer(prev => ({...prev, dataRetentionConsent: checked as boolean}))}
                    />
                    <div className="space-y-2">
                      <Label htmlFor="data-consent" className="text-sm font-medium">
                        Data Retention Consent *
                      </Label>
                      <p className="text-xs text-gray-600">
                        I consent to Cannabest storing my personal information and tracking my sales history
                        for the purpose of providing loyalty program benefits. This data will be kept secure
                        and used only for program administration and personalized offers.
                      </p>
                    </div>
                  </div>
                </div>

                <div className="flex gap-2">
                  <Button 
                    onClick={signupCustomer} 
                    className="flex-1"
                    disabled={!newCustomer.name || !newCustomer.phone || !newCustomer.email || !newCustomer.dataRetentionConsent}
                  >
                    Enroll Customer
                  </Button>
                  <Button variant="outline" onClick={() => setShowSignupDialog(false)} className="flex-1">
                    Cancel
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>
      </header>

      <div className="container mx-auto p-6">
        <Tabs defaultValue="customers" className="space-y-6">
          <TabsList>
            <TabsTrigger value="customers">Customers</TabsTrigger>
            <TabsTrigger value="analytics">Analytics</TabsTrigger>
            <TabsTrigger value="tiers">Tier System</TabsTrigger>
          </TabsList>

          <TabsContent value="customers" className="space-y-6">
            {/* Search */}
            <div className="flex gap-4">
              <div className="flex-1 relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input
                  placeholder="Search by name, phone, or email..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-10"
                />
              </div>
            </div>

            {/* Customer Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
              {filteredCustomers.map(customer => (
                <Card key={customer.id} className="hover:shadow-md transition-shadow">
                  <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                      <div>
                        <h3 className="font-semibold">{customer.name}</h3>
                        <p className="text-sm text-muted-foreground">{customer.email}</p>
                      </div>
                      <div className="flex flex-col gap-1">
                        <Badge className={tierColors[customer.tier]}>
                          {customer.tier}
                        </Badge>
                        {customer.isVeteran && (
                          <Badge variant="outline" className="text-xs bg-blue-50 text-blue-700 border-blue-200">
                            Veteran
                          </Badge>
                        )}
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    <div className="grid grid-cols-2 gap-4 text-sm">
                      <div className="flex items-center gap-2">
                        <Phone className="w-4 h-4 text-muted-foreground" />
                        <span>{customer.phone}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <Calendar className="w-4 h-4 text-muted-foreground" />
                        <span>Joined {new Date(customer.joinDate).toLocaleDateString()}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <DollarSign className="w-4 h-4 text-muted-foreground" />
                        <span>${customer.totalSpent.toFixed(2)}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <TrendingUp className="w-4 h-4 text-muted-foreground" />
                        <span>{customer.totalVisits} visits</span>
                      </div>
                    </div>

                    <div className="bg-green-50 p-3 rounded-lg">
                      <div className="flex items-center justify-between">
                        <span className="text-sm font-medium text-green-800">Points Balance</span>
                        <div className="flex items-center gap-1">
                          <Star className="w-4 h-4 text-green-600" />
                          <span className="font-bold text-green-800">{customer.pointsBalance}</span>
                        </div>
                      </div>
                      <div className="text-xs text-green-700 mt-1">
                        Earned: {customer.pointsEarned} • Redeemed: {customer.pointsRedeemed}
                      </div>
                    </div>

                    <div className="flex gap-2 pt-2">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedCustomer(customer);
                          setShowDetailsDialog(true);
                        }}
                      >
                        <Eye className="w-3 h-3 mr-1" />
                        View Details
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedCustomerForPoints(customer);
                          setShowPointsDialog(true);
                        }}
                        className="btn-success"
                      >
                        <Plus className="w-3 h-3 mr-1" />
                        Add Points
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => deleteCustomer(customer.id)}
                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                      >
                        <Trash2 className="w-3 h-3 mr-1" />
                        Delete
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>

          <TabsContent value="analytics" className="space-y-6">
            {/* Program Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-blue-600">{totalCustomers}</div>
                  <div className="text-sm text-muted-foreground">Total Members</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-green-600">{totalPointsAwarded.toLocaleString()}</div>
                  <div className="text-sm text-muted-foreground">Points Awarded</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-red-600">{totalPointsRedeemed.toLocaleString()}</div>
                  <div className="text-sm text-muted-foreground">Points Redeemed</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-purple-600">${averageSpending.toFixed(2)}</div>
                  <div className="text-sm text-muted-foreground">Avg. Customer Spend</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-orange-600">
                    {customers.filter(c => c.lastVisit &&
                      new Date(c.lastVisit) > new Date(Date.now() - 30*24*60*60*1000)
                    ).length}
                  </div>
                  <div className="text-sm text-muted-foreground">Active (30 days)</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-blue-600">{veteranCount}</div>
                  <div className="text-sm text-muted-foreground">Veterans (10% discount)</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-indigo-600">{activePointsBalance.toLocaleString()}</div>
                  <div className="text-sm text-muted-foreground">Active Points Balance</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-amber-600">{redemptionRate.toFixed(1)}%</div>
                  <div className="text-sm text-muted-foreground">Redemption Rate</div>
                </CardContent>
              </Card>
            </div>

            {/* Points Redeemed Analytics */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Gift className="w-5 h-5" />
                  Points Redeemed Analytics
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div className="space-y-4">
                    <h4 className="font-medium text-gray-700">Recent Redemptions</h4>
                    <div className="space-y-3">
                      {customers
                        .filter(c => c.pointsRedeemed > 0)
                        .sort((a, b) => (b.lastVisit || '').localeCompare(a.lastVisit || ''))
                        .slice(0, 5)
                        .map(customer => (
                          <div key={customer.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                              <div className="font-medium text-sm">{customer.name}</div>
                              <div className="text-xs text-gray-600">{customer.tier} Member</div>
                            </div>
                            <div className="text-right">
                              <div className="font-medium text-red-600">{customer.pointsRedeemed}</div>
                              <div className="text-xs text-gray-600">pts redeemed</div>
                            </div>
                          </div>
                        ))}
                    </div>
                  </div>

                  <div className="space-y-4">
                    <h4 className="font-medium text-gray-700">Redemption by Tier</h4>
                    <div className="space-y-3">
                      {['Platinum', 'Gold', 'Silver', 'Bronze'].map(tier => {
                        const tierCustomers = customers.filter(c => c.tier === tier);
                        const tierRedemptions = tierCustomers.reduce((sum, c) => sum + c.pointsRedeemed, 0);
                        const avgRedemption = tierCustomers.length > 0 ? tierRedemptions / tierCustomers.length : 0;

                        return (
                          <div key={tier} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div className="flex items-center gap-2">
                              <Badge className={tierColors[tier as keyof typeof tierColors]} variant="outline">
                                {tier}
                              </Badge>
                              <span className="text-sm">{tierCustomers.length} members</span>
                            </div>
                            <div className="text-right">
                              <div className="font-medium">{tierRedemptions.toLocaleString()}</div>
                              <div className="text-xs text-gray-600">{avgRedemption.toFixed(0)} avg/member</div>
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  </div>

                  <div className="space-y-4">
                    <h4 className="font-medium text-gray-700">Redemption Insights</h4>
                    <div className="space-y-3">
                      <div className="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div className="text-sm font-medium text-green-800">Top Redeemer</div>
                        <div className="text-xs text-green-700 mt-1">
                          {customers.reduce((top, current) =>
                            current.pointsRedeemed > top.pointsRedeemed ? current : top, customers[0]
                          )?.name} - {customers.reduce((top, current) =>
                            current.pointsRedeemed > top.pointsRedeemed ? current : top, customers[0]
                          )?.pointsRedeemed} points
                        </div>
                      </div>

                      <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div className="text-sm font-medium text-blue-800">Average Value</div>
                        <div className="text-xs text-blue-700 mt-1">
                          ${(totalPointsRedeemed / 100).toFixed(2)} in discounts given
                        </div>
                      </div>

                      <div className="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <div className="text-sm font-medium text-purple-800">Engagement</div>
                        <div className="text-xs text-purple-700 mt-1">
                          {customers.filter(c => c.pointsRedeemed > 0).length} of {totalCustomers} members have redeemed
                        </div>
                      </div>

                      <div className="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <div className="text-sm font-medium text-amber-800">Outstanding Liability</div>
                        <div className="text-xs text-amber-700 mt-1">
                          ${(activePointsBalance / 100).toFixed(2)} in unredeemed points
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Tier Distribution */}
            <Card>
              <CardHeader>
                <CardTitle>Tier Distribution</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-4 gap-4">
                  {Object.entries(tierDistribution).map(([tier, count]) => (
                    <div key={tier} className="text-center p-4 border rounded-lg">
                      <div className="text-2xl font-bold">{count}</div>
                      <Badge className={tierColors[tier as keyof typeof tierColors]} variant="outline">
                        {tier}
                      </Badge>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="tiers" className="space-y-6">
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle>Loyalty Tier System Management</CardTitle>
                  <Button onClick={addTier}>
                    <Plus className="w-4 h-4 mr-2" />
                    Add Tier
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {tiers.map((tier) => (
                    <div key={tier.name} className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex items-center gap-4">
                        <Badge className={tierColors[tier.name as keyof typeof tierColors] || "bg-gray-100 text-gray-800"} variant="outline">
                          {tier.name}
                        </Badge>
                        <div>
                          <h3 className="font-medium">{tier.name} Tier</h3>
                          <p className="text-sm text-muted-foreground">
                            {tier.threshold === 0 ? "Starting tier" : `Spend $${tier.threshold}+ to qualify`}
                          </p>
                          <p className="text-xs text-muted-foreground">
                            {tier.pointsMultiplier}% back in points
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-4">
                        <div className="text-right">
                          <div className="font-bold">{tierDistribution[tier.name] || 0}</div>
                          <div className="text-sm text-muted-foreground">members</div>
                        </div>
                        <div className="flex gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => editTier(tier)}
                          >
                            <Edit className="w-3 h-3 mr-1" />
                            Edit
                          </Button>
                          {tiers.length > 1 && (
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => deleteTier(tier.name)}
                              className="text-red-600 hover:text-red-700"
                            >
                              <Trash2 className="w-3 h-3" />
                            </Button>
                          )}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            {/* Tier Edit Dialog */}
            <Dialog open={showTierEditDialog} onOpenChange={setShowTierEditDialog}>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>{editingTier ? `Edit ${editingTier.name} Tier` : 'Add New Tier'}</DialogTitle>
                </DialogHeader>
                {editingTier && (
                  <div className="space-y-4">
                    <div>
                      <Label htmlFor="tierName">Tier Name</Label>
                      <Input
                        id="tierName"
                        value={editingTier.name}
                        onChange={(e) => setEditingTier(prev => prev ? {...prev, name: e.target.value} : null)}
                        placeholder="Enter tier name"
                      />
                    </div>

                    <div>
                      <Label htmlFor="tierThreshold">Spending Threshold ($)</Label>
                      <Input
                        id="tierThreshold"
                        type="number"
                        value={editingTier.threshold}
                        onChange={(e) => setEditingTier(prev => prev ? {...prev, threshold: parseInt(e.target.value) || 0} : null)}
                        placeholder="0"
                      />
                      <p className="text-xs text-muted-foreground mt-1">
                        Minimum amount customer must spend to reach this tier
                      </p>
                    </div>

                    <div>
                      <Label htmlFor="pointsMultiplier">Points Back (%)</Label>
                      <Input
                        id="pointsMultiplier"
                        type="number"
                        step="0.1"
                        value={editingTier.pointsMultiplier}
                        onChange={(e) => setEditingTier(prev => prev ? {...prev, pointsMultiplier: parseFloat(e.target.value) || 1} : null)}
                        placeholder="1.0"
                      />
                      <p className="text-xs text-muted-foreground mt-1">
                        Percentage of purchase amount returned as points
                      </p>
                    </div>

                    <div>
                      <Label htmlFor="benefits">Benefits (one per line)</Label>
                      <textarea
                        id="benefits"
                        value={editingTier.benefits.join('\n')}
                        onChange={(e) => setEditingTier(prev => prev ? {...prev, benefits: e.target.value.split('\n').filter(b => b.trim())} : null)}
                        placeholder="Enter benefits, one per line"
                        className="w-full p-2 border rounded-md text-sm"
                        rows={4}
                      />
                    </div>

                    <div className="flex gap-2">
                      <Button
                        onClick={() => updateTier(editingTier)}
                        className="flex-1"
                        disabled={!editingTier.name.trim()}
                      >
                        Save Tier
                      </Button>
                      <Button
                        variant="outline"
                        onClick={() => {
                          setShowTierEditDialog(false);
                          setEditingTier(null);
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
          </TabsContent>
        </Tabs>

        {/* Manual Points Addition Dialog */}
        <Dialog open={showPointsDialog} onOpenChange={setShowPointsDialog}>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                <Gift className="w-5 h-5" />
                Add Points Manually
              </DialogTitle>
            </DialogHeader>
            {selectedCustomerForPoints && (
              <div className="space-y-4">
                <div className="p-4 bg-gray-50 rounded-lg">
                  <h3 className="font-semibold">{selectedCustomerForPoints.name}</h3>
                  <p className="text-sm text-muted-foreground">{selectedCustomerForPoints.email}</p>
                  <div className="flex items-center gap-2 mt-2">
                    <Badge className={tierColors[selectedCustomerForPoints.tier]}>
                      {selectedCustomerForPoints.tier}
                    </Badge>
                    <span className="text-sm">Current: {selectedCustomerForPoints.pointsBalance} points</span>
                  </div>
                </div>

                <div className="space-y-4">
                  <div>
                    <Label htmlFor="points-amount">Points to Add *</Label>
                    <Input
                      id="points-amount"
                      type="number"
                      min="1"
                      value={pointsToAdd}
                      onChange={(e) => setPointsToAdd(e.target.value)}
                      placeholder="Enter points amount"
                    />
                  </div>

                  <div>
                    <Label htmlFor="points-reason">Reason *</Label>
                    <select
                      id="points-reason"
                      value={pointsReason}
                      onChange={(e) => setPointsReason(e.target.value)}
                      className="w-full p-2 border rounded-md"
                    >
                      <option value="">Select reason...</option>
                      <option value="Birthday Bonus">Birthday Bonus</option>
                      <option value="Referral Reward">Referral Reward</option>
                      <option value="Social Media Follow">Social Media Follow</option>
                      <option value="Survey Completion">Survey Completion</option>
                      <option value="Manager Discretion">Manager Discretion</option>
                      <option value="Customer Service Recovery">Customer Service Recovery</option>
                      <option value="Promotional Event">Promotional Event</option>
                      <option value="Loyalty Program Adjustment">Loyalty Program Adjustment</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>

                  {pointsReason === "Other" && (
                    <div>
                      <Label htmlFor="custom-reason">Custom Reason</Label>
                      <Input
                        id="custom-reason"
                        value={pointsReason}
                        onChange={(e) => setPointsReason(e.target.value)}
                        placeholder="Enter custom reason"
                      />
                    </div>
                  )}
                </div>

                <div className="p-3 bg-blue-50 rounded text-sm">
                  <p className="font-medium text-blue-800">Points Value</p>
                  <p className="text-blue-700 text-xs">
                    {pointsToAdd ? `${pointsToAdd} points = $${(parseInt(pointsToAdd || "0") / 100).toFixed(2)} value` : "100 points = $1.00 value"}
                  </p>
                </div>

                <div className="flex gap-2">
                  <Button
                    onClick={addPointsManually}
                    className="flex-1"
                    disabled={!pointsToAdd || !pointsReason}
                  >
                    <Gift className="w-4 h-4 mr-2" />
                    Add Points
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => {
                      setShowPointsDialog(false);
                      setSelectedCustomerForPoints(null);
                      setPointsToAdd("");
                      setPointsReason("");
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

        {/* Customer Details Dialog */}
        <Dialog open={showDetailsDialog} onOpenChange={setShowDetailsDialog}>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Customer Details</DialogTitle>
            </DialogHeader>
            {selectedCustomer && (
              <div className="space-y-6">
                <div className="flex items-center justify-between">
                  <div>
                    <h2 className="text-xl font-semibold">{selectedCustomer.name}</h2>
                    <p className="text-muted-foreground">{selectedCustomer.email}</p>
                  </div>
                  <Badge className={tierColors[selectedCustomer.tier]}>
                    {selectedCustomer.tier} Member
                  </Badge>
                </div>

                <div className="grid grid-cols-2 gap-6">
                  <div className="space-y-4">
                    <div>
                      <Label>Contact Information</Label>
                      <div className="space-y-2 mt-2">
                        <div className="flex items-center gap-2">
                          <Phone className="w-4 h-4 text-muted-foreground" />
                          <span>{selectedCustomer.phone}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          <Mail className="w-4 h-4 text-muted-foreground" />
                          <span>{selectedCustomer.email}</span>
                        </div>
                      </div>
                    </div>

                    <div>
                      <Label>Membership Details</Label>
                      <div className="space-y-2 mt-2 text-sm">
                        <div>Joined: {new Date(selectedCustomer.joinDate).toLocaleDateString()}</div>
                        <div>Total Visits: {selectedCustomer.totalVisits}</div>
                        <div>Total Spent: ${selectedCustomer.totalSpent.toFixed(2)}</div>
                        <div>Last Visit: {selectedCustomer.lastVisit ? new Date(selectedCustomer.lastVisit).toLocaleDateString() : 'Never'}</div>
                      </div>
                    </div>
                  </div>

                  <div className="space-y-4">
                    <div>
                      <Label>Points Summary</Label>
                      <div className="bg-green-50 p-4 rounded-lg mt-2">
                        <div className="text-2xl font-bold text-green-800">{selectedCustomer.pointsBalance}</div>
                        <div className="text-sm text-green-700">Available Points</div>
                        <div className="text-xs text-green-600 mt-2">
                          Earned: {selectedCustomer.pointsEarned} • Redeemed: {selectedCustomer.pointsRedeemed}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <Label>Recent Purchase History</Label>
                  <div className="space-y-2 mt-2 max-h-40 overflow-y-auto">
                    {selectedCustomer.salesHistory.length > 0 ? (
                      selectedCustomer.salesHistory.map(purchase => (
                        <div key={purchase.id} className="flex items-center justify-between p-3 bg-gray-50 rounded">
                          <div>
                            <div className="font-medium">{new Date(purchase.date).toLocaleDateString()}</div>
                            <div className="text-sm text-muted-foreground">
                              {purchase.items.join(', ')}
                            </div>
                          </div>
                          <div className="text-right">
                            <div className="font-medium">${purchase.total.toFixed(2)}</div>
                            <div className="text-sm text-green-600">+{purchase.pointsEarned} pts</div>
                          </div>
                        </div>
                      ))
                    ) : (
                      <p className="text-sm text-muted-foreground">No purchase history available</p>
                    )}
                  </div>
                </div>
              </div>
            )}
          </DialogContent>
        </Dialog>
      </div>
    </div>
  );
}
