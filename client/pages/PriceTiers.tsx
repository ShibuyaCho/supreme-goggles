import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import {
  Plus,
  Edit,
  Trash2,
  DollarSign,
  Package,
  Save,
  Copy,
  BarChart3
} from "lucide-react";

interface PriceTier {
  id: string;
  name: string;
  description: string;
  category: string;
  prices: {
    "1g": number;
    "3.5g": number;
    "7g": number;
    "14g": number;
    "28g": number;
    "56g": number;
  };
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

const sampleTiers: PriceTier[] = [
  {
    id: "1",
    name: "Premium Indoor",
    description: "Top-shelf indoor grown cannabis",
    category: "Flower",
    prices: {
      "1g": 12.00,
      "3.5g": 40.00,
      "7g": 75.00,
      "14g": 140.00,
      "28g": 260.00,
      "56g": 480.00
    },
    isActive: true,
    createdAt: "2024-01-10",
    updatedAt: "2024-01-15"
  },
  {
    id: "2",
    name: "Budget Outdoor",
    description: "Quality outdoor grown cannabis",
    category: "Flower",
    prices: {
      "1g": 6.00,
      "3.5g": 20.00,
      "7g": 38.00,
      "14g": 70.00,
      "28g": 130.00,
      "56g": 240.00
    },
    isActive: true,
    createdAt: "2024-01-10",
    updatedAt: "2024-01-12"
  },
  {
    id: "3",
    name: "House Special",
    description: "Mid-tier quality at great prices",
    category: "Flower",
    prices: {
      "1g": 8.00,
      "3.5g": 28.00,
      "7g": 52.00,
      "14g": 98.00,
      "28g": 180.00,
      "56g": 340.00
    },
    isActive: true,
    createdAt: "2024-01-08",
    updatedAt: "2024-01-14"
  }
];

const weightOptions = ["1g", "3.5g", "7g", "14g", "28g", "56g"] as const;

export default function PriceTiers() {
  const [tiers, setTiers] = useState<PriceTier[]>(sampleTiers);
  const [showCreateDialog, setShowCreateDialog] = useState(false);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [selectedTier, setSelectedTier] = useState<PriceTier | null>(null);
  const [newTier, setNewTier] = useState<Partial<PriceTier>>({
    name: "",
    description: "",
    category: "Flower",
    prices: {
      "1g": 0,
      "3.5g": 0,
      "7g": 0,
      "14g": 0,
      "28g": 0,
      "56g": 0
    },
    isActive: true
  });

  const handleCreateTier = () => {
    if (!newTier.name || !newTier.description) return;

    const tier: PriceTier = {
      id: Date.now().toString(),
      name: newTier.name,
      description: newTier.description || "",
      category: newTier.category || "Flower",
      prices: newTier.prices || {
        "1g": 0,
        "3.5g": 0,
        "7g": 0,
        "14g": 0,
        "28g": 0,
        "56g": 0
      },
      isActive: newTier.isActive || true,
      createdAt: new Date().toISOString().split('T')[0],
      updatedAt: new Date().toISOString().split('T')[0]
    };

    setTiers(prev => [...prev, tier]);
    setNewTier({
      name: "",
      description: "",
      category: "Flower",
      prices: {
        "1g": 0,
        "3.5g": 0,
        "7g": 0,
        "14g": 0,
        "28g": 0,
        "56g": 0
      },
      isActive: true
    });
    setShowCreateDialog(false);
  };

  const handleEditTier = () => {
    if (!selectedTier) return;

    setTiers(prev => prev.map(tier => 
      tier.id === selectedTier.id 
        ? { ...selectedTier, updatedAt: new Date().toISOString().split('T')[0] }
        : tier
    ));
    setShowEditDialog(false);
    setSelectedTier(null);
  };

  const handleDeleteTier = (tierId: string) => {
    setTiers(prev => prev.filter(tier => tier.id !== tierId));
  };

  const toggleTierStatus = (tierId: string) => {
    setTiers(prev => prev.map(tier => 
      tier.id === tierId 
        ? { ...tier, isActive: !tier.isActive, updatedAt: new Date().toISOString().split('T')[0] }
        : tier
    ));
  };

  const duplicateTier = (tier: PriceTier) => {
    const duplicatedTier: PriceTier = {
      ...tier,
      id: Date.now().toString(),
      name: `${tier.name} (Copy)`,
      createdAt: new Date().toISOString().split('T')[0],
      updatedAt: new Date().toISOString().split('T')[0]
    };
    setTiers(prev => [...prev, duplicatedTier]);
  };

  const updateNewTierPrice = (weight: keyof PriceTier['prices'], value: number) => {
    setNewTier(prev => ({
      ...prev,
      prices: {
        ...prev.prices,
        [weight]: value
      }
    }));
  };

  const updateSelectedTierPrice = (weight: keyof PriceTier['prices'], value: number) => {
    if (!selectedTier) return;
    setSelectedTier(prev => prev ? ({
      ...prev,
      prices: {
        ...prev.prices,
        [weight]: value
      }
    }) : null);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Price Tiers Management</h1>
            <p className="text-sm opacity-80">Create and manage pricing tiers for different product categories</p>
          </div>
          <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="w-4 h-4 mr-2" />
                Create Tier
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-4xl">
              <DialogHeader>
                <DialogTitle>Create New Price Tier</DialogTitle>
              </DialogHeader>
              <div className="space-y-6">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="tier-name">Tier Name</Label>
                    <Input
                      id="tier-name"
                      placeholder="e.g., Premium Indoor"
                      value={newTier.name}
                      onChange={(e) => setNewTier(prev => ({ ...prev, name: e.target.value }))}
                    />
                  </div>
                  <div>
                    <Label htmlFor="tier-category">Category</Label>
                    <Select value={newTier.category} onValueChange={(value) => setNewTier(prev => ({ ...prev, category: value }))}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Flower">Flower</SelectItem>
                        <SelectItem value="Concentrates">Concentrates</SelectItem>
                        <SelectItem value="Pre-Rolls">Pre-Rolls</SelectItem>
                        <SelectItem value="Edibles">Edibles</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                <div>
                  <Label htmlFor="tier-description">Description</Label>
                  <Input
                    id="tier-description"
                    placeholder="Brief description of this tier"
                    value={newTier.description}
                    onChange={(e) => setNewTier(prev => ({ ...prev, description: e.target.value }))}
                  />
                </div>
                <div>
                  <Label className="text-base font-medium">Pricing Structure</Label>
                  <div className="grid grid-cols-3 gap-4 mt-3">
                    {weightOptions.map(weight => (
                      <div key={weight}>
                        <Label htmlFor={`new-${weight}`}>{weight}</Label>
                        <div className="relative">
                          <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                          <Input
                            id={`new-${weight}`}
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            value={newTier.prices?.[weight] || 0}
                            onChange={(e) => updateNewTierPrice(weight, parseFloat(e.target.value) || 0)}
                            className="pl-10"
                          />
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
                <Button onClick={handleCreateTier} className="w-full">
                  Create Price Tier
                </Button>
              </div>
            </DialogContent>
          </Dialog>
        </div>
      </header>

      <div className="container mx-auto p-6">
        {/* Overview Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-blue-600">{tiers.length}</div>
              <div className="text-sm text-muted-foreground">Total Tiers</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-green-600">
                {tiers.filter(t => t.isActive).length}
              </div>
              <div className="text-sm text-muted-foreground">Active Tiers</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-purple-600">
                ${Math.max(...tiers.map(t => t.prices["1g"])).toFixed(2)}
              </div>
              <div className="text-sm text-muted-foreground">Highest 1g Price</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-orange-600">
                ${Math.min(...tiers.map(t => t.prices["1g"])).toFixed(2)}
              </div>
              <div className="text-sm text-muted-foreground">Lowest 1g Price</div>
            </CardContent>
          </Card>
        </div>

        {/* Price Tiers List */}
        <div className="space-y-4">
          {tiers.map(tier => (
            <Card key={tier.id} className="hover:shadow-md transition-shadow">
              <CardHeader className="pb-3">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <Package className="w-6 h-6 text-primary" />
                    <div>
                      <h3 className="font-semibold flex items-center gap-2">
                        {tier.name}
                        <Badge variant={tier.isActive ? "default" : "secondary"}>
                          {tier.isActive ? "Active" : "Inactive"}
                        </Badge>
                      </h3>
                      <p className="text-sm text-muted-foreground">{tier.description}</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => duplicateTier(tier)}
                    >
                      <Copy className="w-4 h-4" />
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => {
                        setSelectedTier(tier);
                        setShowEditDialog(true);
                      }}
                    >
                      <Edit className="w-4 h-4" />
                    </Button>
                    <Button
                      variant={tier.isActive ? "secondary" : "default"}
                      size="sm"
                      onClick={() => toggleTierStatus(tier.id)}
                    >
                      {tier.isActive ? "Deactivate" : "Activate"}
                    </Button>
                    <Button
                      variant="destructive"
                      size="sm"
                      onClick={() => handleDeleteTier(tier.id)}
                    >
                      <Trash2 className="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-3 lg:grid-cols-6 gap-4">
                  {weightOptions.map(weight => (
                    <div key={weight} className="text-center p-3 bg-gray-50 rounded-lg">
                      <div className="text-sm font-medium text-muted-foreground">{weight}</div>
                      <div className="text-lg font-bold">${tier.prices[weight].toFixed(2)}</div>
                    </div>
                  ))}
                </div>
                <div className="flex justify-between items-center mt-4 pt-4 border-t text-sm text-muted-foreground">
                  <span>Category: {tier.category}</span>
                  <span>Updated: {tier.updatedAt}</span>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Edit Tier Dialog */}
        <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
          <DialogContent className="max-w-4xl">
            <DialogHeader>
              <DialogTitle>Edit Price Tier - {selectedTier?.name}</DialogTitle>
            </DialogHeader>
            {selectedTier && (
              <div className="space-y-6">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="edit-tier-name">Tier Name</Label>
                    <Input
                      id="edit-tier-name"
                      value={selectedTier.name}
                      onChange={(e) => setSelectedTier(prev => prev ? ({ ...prev, name: e.target.value }) : null)}
                    />
                  </div>
                  <div>
                    <Label htmlFor="edit-tier-category">Category</Label>
                    <Select 
                      value={selectedTier.category} 
                      onValueChange={(value) => setSelectedTier(prev => prev ? ({ ...prev, category: value }) : null)}
                    >
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Flower">Flower</SelectItem>
                        <SelectItem value="Concentrates">Concentrates</SelectItem>
                        <SelectItem value="Pre-Rolls">Pre-Rolls</SelectItem>
                        <SelectItem value="Edibles">Edibles</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                <div>
                  <Label htmlFor="edit-tier-description">Description</Label>
                  <Input
                    id="edit-tier-description"
                    value={selectedTier.description}
                    onChange={(e) => setSelectedTier(prev => prev ? ({ ...prev, description: e.target.value }) : null)}
                  />
                </div>
                <div>
                  <Label className="text-base font-medium">Pricing Structure</Label>
                  <div className="grid grid-cols-3 gap-4 mt-3">
                    {weightOptions.map(weight => (
                      <div key={weight}>
                        <Label htmlFor={`edit-${weight}`}>{weight}</Label>
                        <div className="relative">
                          <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                          <Input
                            id={`edit-${weight}`}
                            type="number"
                            step="0.01"
                            value={selectedTier.prices[weight]}
                            onChange={(e) => updateSelectedTierPrice(weight, parseFloat(e.target.value) || 0)}
                            className="pl-10"
                          />
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
                <Button onClick={handleEditTier} className="w-full">
                  <Save className="w-4 h-4 mr-2" />
                  Save Changes
                </Button>
              </div>
            )}
          </DialogContent>
        </Dialog>
      </div>
    </div>
  );
}
