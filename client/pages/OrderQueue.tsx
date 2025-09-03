import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import {
  Phone,
  Globe,
  Clock,
  CheckCircle,
  XCircle,
  User,
  MapPin,
  DollarSign,
  Eye,
  Edit,
  Plus,
  Filter,
  Search,
  Calendar,
  ShoppingCart
} from "lucide-react";

interface Order {
  id: string;
  orderNumber: string;
  customerName: string;
  customerPhone: string;
  type: 'phone' | 'online';
  status: 'pending' | 'preparing' | 'ready' | 'completed' | 'cancelled';
  items: Array<{
    name: string;
    quantity: number;
    price: number;
  }>;
  total: number;
  orderTime: string;
  estimatedReady: string;
  notes?: string;
  address?: string;
  medicalCard?: string;
  caregiverCard?: string;
}

const mockOrders: Order[] = [
  {
    id: "1",
    orderNumber: "ORD-001",
    customerName: "Sarah Johnson",
    customerPhone: "(555) 123-4567",
    type: "online",
    status: "pending",
    items: [
      { name: "Blue Dream", quantity: 1, price: 45.00 },
      { name: "Gummy Bears", quantity: 2, price: 25.00 }
    ],
    total: 114.00,
    orderTime: "2:30 PM",
    estimatedReady: "3:00 PM",
    notes: "Customer prefers indica strains",
    medicalCard: "MMJ123456"
  },
  {
    id: "2",
    orderNumber: "ORD-002",
    customerName: "Mike Chen",
    customerPhone: "(555) 987-6543",
    type: "phone",
    status: "preparing",
    items: [
      { name: "OG Kush", quantity: 2, price: 50.00 },
      { name: "Vape Cartridge", quantity: 1, price: 55.00 }
    ],
    total: 186.00,
    orderTime: "2:15 PM",
    estimatedReady: "2:45 PM",
    caregiverCard: "CG789012"
  },
  {
    id: "3",
    orderNumber: "ORD-003",
    customerName: "Emma Rodriguez",
    customerPhone: "(555) 456-7890",
    type: "online",
    status: "ready",
    items: [
      { name: "CBD Tincture", quantity: 1, price: 65.00 }
    ],
    total: 78.00,
    orderTime: "1:45 PM",
    estimatedReady: "2:30 PM",
    address: "123 Main St, City, State",
    medicalCard: "MMJ654321"
  }
];

const statusColors = {
  pending: "bg-yellow-100 text-yellow-800",
  preparing: "bg-blue-100 text-blue-800",
  ready: "bg-green-100 text-green-800",
  completed: "bg-gray-100 text-gray-800",
  cancelled: "bg-red-100 text-red-800"
};

export default function OrderQueue() {
  const [orders, setOrders] = useState<Order[]>(mockOrders);
  const [selectedStatus, setSelectedStatus] = useState<string>("all");
  const [selectedType, setSelectedType] = useState<string>("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [showNewOrderDialog, setShowNewOrderDialog] = useState(false);
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [showOrderDetails, setShowOrderDetails] = useState(false);

  const filteredOrders = orders.filter(order => {
    const matchesStatus = selectedStatus === "all" || order.status === selectedStatus;
    const matchesType = selectedType === "all" || order.type === selectedType;
    const matchesSearch = order.customerName.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         order.orderNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         order.customerPhone.includes(searchQuery);
    return matchesStatus && matchesType && matchesSearch;
  });

  const updateOrderStatus = (orderId: string, newStatus: Order['status']) => {
    setOrders(prev => prev.map(order =>
      order.id === orderId ? { ...order, status: newStatus } : order
    ));
  };

  const bringToCart = (order: Order) => {
    // Navigate to POS with order data
    if (confirm(`Bring ${order.orderNumber} to cart for ${order.customerName}?`)) {
      // Store order data in localStorage to pass to POS
      localStorage.setItem('queueOrder', JSON.stringify(order));
      // Navigate to POS
      window.location.href = '/';
      // Show success message
      alert(`Order ${order.orderNumber} has been brought to cart. Processing for ${order.customerName}.`);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4">
          <h1 className="text-xl font-semibold">Order Queue</h1>
          <p className="text-sm opacity-80">Manage phone and online orders</p>
        </div>
      </header>

      <div className="container mx-auto p-6">
        {/* Controls */}
        <div className="flex flex-col sm:flex-row gap-4 mb-6">
          <div className="flex-1">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                placeholder="Search orders, customers, or phone numbers..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>
          <Select value={selectedStatus} onValueChange={setSelectedStatus}>
            <SelectTrigger className="w-40">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Status</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="preparing">Preparing</SelectItem>
              <SelectItem value="ready">Ready</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
            </SelectContent>
          </Select>
          <Select value={selectedType} onValueChange={setSelectedType}>
            <SelectTrigger className="w-40">
              <SelectValue placeholder="Type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Types</SelectItem>
              <SelectItem value="phone">Phone Orders</SelectItem>
              <SelectItem value="online">Online Orders</SelectItem>
            </SelectContent>
          </Select>
          <Dialog open={showNewOrderDialog} onOpenChange={setShowNewOrderDialog}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="w-4 h-4 mr-2" />
                New Order
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-md">
              <DialogHeader>
                <DialogTitle>Create New Order</DialogTitle>
              </DialogHeader>
              <div className="space-y-4">
                <div>
                  <Label htmlFor="customer-name">Customer Name</Label>
                  <Input id="customer-name" placeholder="Enter customer name" />
                </div>
                <div>
                  <Label htmlFor="customer-phone">Phone Number</Label>
                  <Input id="customer-phone" placeholder="(555) 123-4567" />
                </div>
                <div>
                  <Label htmlFor="order-type">Order Type</Label>
                  <Select>
                    <SelectTrigger>
                      <SelectValue placeholder="Select type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="phone">Phone Order</SelectItem>
                      <SelectItem value="online">Online Order</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label htmlFor="medical-card">Medical Card # (Optional)</Label>
                  <Input id="medical-card" placeholder="MMJ123456" />
                </div>
                <div>
                  <Label htmlFor="caregiver-card">Caregiver Card # (Optional)</Label>
                  <Input id="caregiver-card" placeholder="CG789012" />
                </div>
                <Button className="w-full">Create Order</Button>
              </div>
            </DialogContent>
          </Dialog>
        </div>

        {/* Order Stats */}
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-yellow-600">
                {orders.filter(o => o.status === 'pending').length}
              </div>
              <div className="text-sm text-muted-foreground">Pending</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-blue-600">
                {orders.filter(o => o.status === 'preparing').length}
              </div>
              <div className="text-sm text-muted-foreground">Preparing</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-green-600">
                {orders.filter(o => o.status === 'ready').length}
              </div>
              <div className="text-sm text-muted-foreground">Ready</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-gray-600">
                {orders.filter(o => o.status === 'completed').length}
              </div>
              <div className="text-sm text-muted-foreground">Completed</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 text-center">
              <div className="text-2xl font-bold text-red-600">
                {orders.filter(o => o.status === 'cancelled').length}
              </div>
              <div className="text-sm text-muted-foreground">Cancelled</div>
            </CardContent>
          </Card>
        </div>

        {/* Orders Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
          {filteredOrders.map(order => (
            <Card key={order.id} className="hover:shadow-md transition-shadow">
              <CardHeader className="pb-3">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    {order.type === 'phone' ? 
                      <Phone className="w-4 h-4 text-blue-600" /> : 
                      <Globe className="w-4 h-4 text-green-600" />
                    }
                    <span className="font-semibold">{order.orderNumber}</span>
                  </div>
                  <Badge className={statusColors[order.status]}>
                    {order.status}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex items-center gap-2">
                  <User className="w-4 h-4 text-muted-foreground" />
                  <span className="font-medium">{order.customerName}</span>
                </div>
                <div className="flex items-center gap-2">
                  <Phone className="w-4 h-4 text-muted-foreground" />
                  <span className="text-sm">{order.customerPhone}</span>
                </div>
                {order.medicalCard && (
                  <div className="flex items-center gap-2">
                    <Badge variant="outline" className="text-xs">
                      Medical: {order.medicalCard}
                    </Badge>
                  </div>
                )}
                {order.caregiverCard && (
                  <div className="flex items-center gap-2">
                    <Badge variant="outline" className="text-xs">
                      Caregiver: {order.caregiverCard}
                    </Badge>
                  </div>
                )}
                <div className="flex items-center justify-between text-sm">
                  <div className="flex items-center gap-1">
                    <Clock className="w-4 h-4 text-muted-foreground" />
                    <span>Ready: {order.estimatedReady}</span>
                  </div>
                  <div className="flex items-center gap-1">
                    <DollarSign className="w-4 h-4 text-muted-foreground" />
                    <span className="font-semibold">${order.total.toFixed(2)}</span>
                  </div>
                </div>
                
                <Separator />
                
                <div className="flex gap-2">
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => {
                      setSelectedOrder(order);
                      setShowOrderDetails(true);
                    }}
                  >
                    <Eye className="w-3 h-3 mr-1" />
                    View
                  </Button>
                  
                  {order.status === 'pending' && (
                    <Button
                      size="sm"
                      onClick={() => updateOrderStatus(order.id, 'preparing')}
                    >
                      Start Prep
                    </Button>
                  )}
                  
                  {order.status === 'preparing' && (
                    <Button
                      size="sm"
                      onClick={() => updateOrderStatus(order.id, 'ready')}
                      className="bg-green-600 hover:bg-green-700"
                    >
                      Mark Ready
                    </Button>
                  )}
                  
                  {order.status === 'ready' && (
                    <>
                      <Button
                        size="sm"
                        onClick={() => bringToCart(order)}
                        className="bg-blue-600 hover:bg-blue-700"
                      >
                        <ShoppingCart className="w-3 h-3 mr-1" />
                        Bring to Cart
                      </Button>
                      <Button
                        size="sm"
                        onClick={() => updateOrderStatus(order.id, 'completed')}
                        variant="outline"
                      >
                        Complete
                      </Button>
                    </>
                  )}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Order Details Dialog */}
        <Dialog open={showOrderDetails} onOpenChange={setShowOrderDetails}>
          <DialogContent className="max-w-lg">
            <DialogHeader>
              <DialogTitle>Order Details - {selectedOrder?.orderNumber}</DialogTitle>
            </DialogHeader>
            {selectedOrder && (
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label>Customer</Label>
                    <p className="font-medium">{selectedOrder.customerName}</p>
                    <p className="text-sm text-muted-foreground">{selectedOrder.customerPhone}</p>
                  </div>
                  <div>
                    <Label>Order Type</Label>
                    <div className="flex items-center gap-2 mt-1">
                      {selectedOrder.type === 'phone' ? 
                        <Phone className="w-4 h-4" /> : 
                        <Globe className="w-4 h-4" />
                      }
                      <span className="capitalize">{selectedOrder.type}</span>
                    </div>
                  </div>
                </div>
                
                {(selectedOrder.medicalCard || selectedOrder.caregiverCard) && (
                  <div>
                    <Label>Cards</Label>
                    <div className="flex gap-2 mt-1">
                      {selectedOrder.medicalCard && (
                        <Badge variant="outline">Medical: {selectedOrder.medicalCard}</Badge>
                      )}
                      {selectedOrder.caregiverCard && (
                        <Badge variant="outline">Caregiver: {selectedOrder.caregiverCard}</Badge>
                      )}
                    </div>
                  </div>
                )}

                <div>
                  <Label>Items</Label>
                  <div className="space-y-2 mt-1">
                    {selectedOrder.items.map((item, index) => (
                      <div key={index} className="flex justify-between p-2 bg-gray-50 rounded">
                        <span>{item.name} x{item.quantity}</span>
                        <span>${(item.price * item.quantity).toFixed(2)}</span>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="flex justify-between font-semibold">
                  <span>Total</span>
                  <span>${selectedOrder.total.toFixed(2)}</span>
                </div>

                {selectedOrder.notes && (
                  <div>
                    <Label>Notes</Label>
                    <p className="text-sm bg-gray-50 p-2 rounded mt-1">{selectedOrder.notes}</p>
                  </div>
                )}

                {selectedOrder.address && (
                  <div>
                    <Label>Address</Label>
                    <p className="text-sm bg-gray-50 p-2 rounded mt-1">{selectedOrder.address}</p>
                  </div>
                )}
              </div>
            )}
          </DialogContent>
        </Dialog>
      </div>
    </div>
  );
}
