import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Textarea } from "@/components/ui/textarea";
import {
  Home,
  Plus,
  Lock,
  Unlock,
  DollarSign,
  Package,
  AlertTriangle,
  Settings,
  Eye,
  Edit,
  Trash2,
  MapPin,
  Shield,
  Camera,
  Thermometer,
  Zap,
  Users,
  Calculator,
  Printer,
  FileDown,
  Search,
  Filter,
  Calendar,
  Download
} from "lucide-react";

interface Room {
  id: string;
  name: string;
  type: 'storage' | 'cultivation' | 'processing' | 'for sale' | 'hold safe' | 'back room' | 'hold fix';
  status: 'active' | 'maintenance' | 'inactive';
  temperature: number;
  humidity: number;
  capacity?: number;
  currentInventory: number;
  securityLevel?: 'low' | 'medium' | 'high' | 'maximum';
  accessLevel: string[];
  lastInspection: string;
  notes?: string;
  drawers: Drawer[];
}

interface Drawer {
  id: string;
  name: string;
  location: string;
  type: 'cash/debit' | 'product' | 'supplies' | 'documents';
  isOpen: boolean;
  assignedEmployee?: string;
  accessCode?: string;
  currentValue?: number;
  expectedValue?: number;
  countedValue?: number;
  dropNotificationAmount?: number;
  lastAccessed: string;
  lastCounted?: string;
  dailySales?: number;
  openingAmount?: number;
  accessHistory: AccessLog[];
}

interface AccessLog {
  timestamp: string;
  employee: string;
  action: 'open' | 'close' | 'stock' | 'remove';
  notes?: string;
}

const mockRooms: Room[] = [
  {
    id: "1",
    name: "Main Storage Vault",
    type: "storage",
    status: "active",
    temperature: 68,
    humidity: 45,
    capacity: 1000,
    currentInventory: 750,
    securityLevel: "maximum",
    accessLevel: ["manager", "security"],
    lastInspection: "2024-01-14",
    notes: "Climate controlled storage for high-value inventory",
    drawers: [
      {
        id: "d1",
        name: "Premium Flower Drawer A",
        location: "Vault Section A",
        type: "product",
        isOpen: false,
        assignedEmployee: "Sarah Johnson",
        currentValue: 15000,
        lastAccessed: "2024-01-15 10:30",
        accessHistory: [
          { timestamp: "2024-01-15 10:30", employee: "Sarah Johnson", action: "stock", notes: "Added Blue Dream inventory" },
          { timestamp: "2024-01-15 08:45", employee: "Mike Chen", action: "remove", notes: "Prepared for retail display" }
        ]
      },
      {
        id: "d2",
        name: "Concentrates Secure Drawer",
        location: "Vault Section B",
        type: "product",
        isOpen: false,
        assignedEmployee: "Mike Chen",
        currentValue: 8500,
        lastAccessed: "2024-01-14 16:20",
        accessHistory: [
          { timestamp: "2024-01-14 16:20", employee: "Sarah Johnson", action: "stock" }
        ]
      }
    ]
  },
  {
    id: "2",
    name: "For Sale Floor",
    type: "for sale",
    status: "active",
    temperature: 72,
    humidity: 40,
    capacity: 200,
    currentInventory: 185,
    securityLevel: "medium",
    accessLevel: ["manager", "budtender", "cashier"],
    lastInspection: "2024-01-15",
    drawers: [
      {
        id: "d3",
        name: "Cash Register 1",
        location: "Front Counter",
        type: "cash/debit",
        isOpen: true,
        assignedEmployee: "Emma Rodriguez",
        currentValue: 850,
        expectedValue: 875,
        countedValue: 850,
        openingAmount: 300,
        dailySales: 1250,
        lastCounted: "2024-01-15 14:30",
        dropNotificationAmount: 500,
        lastAccessed: "2024-01-15 14:30",
        accessHistory: [
          { timestamp: "2024-01-15 14:30", employee: "Emma Rodriguez", action: "close" },
          { timestamp: "2024-01-15 09:00", employee: "Mike Chen", action: "open", notes: "Morning cash count: $300" }
        ]
      },
      {
        id: "d4",
        name: "Display Case Drawer",
        location: "Main Display",
        type: "product",
        isOpen: false,
        assignedEmployee: "Lisa Park",
        currentValue: 3200,
        lastAccessed: "2024-01-15 12:15",
        accessHistory: []
      }
    ]
  },
  {
    id: "3",
    name: "Processing Lab",
    type: "processing",
    status: "maintenance",
    temperature: 70,
    humidity: 35,
    capacity: 500,
    currentInventory: 0,
    securityLevel: "high",
    accessLevel: ["manager", "processor"],
    lastInspection: "2024-01-10",
    notes: "Scheduled maintenance until 1/20/2024",
    drawers: []
  }
];

const roomTypeColors = {
  storage: "bg-blue-100 text-blue-800",
  cultivation: "bg-green-100 text-green-800",
  processing: "bg-purple-100 text-purple-800",
  "for sale": "bg-orange-100 text-orange-800",
  office: "bg-gray-100 text-gray-800",
  security: "bg-red-100 text-red-800"
};

const statusColors = {
  active: "bg-green-100 text-green-800",
  maintenance: "bg-yellow-100 text-yellow-800",
  inactive: "bg-red-100 text-red-800"
};

const securityColors = {
  low: "bg-gray-100 text-gray-800",
  medium: "bg-yellow-100 text-yellow-800",
  high: "bg-orange-100 text-orange-800",
  maximum: "bg-red-100 text-red-800"
};

const availableEmployees = [
  { id: "emp1", name: "Sarah Johnson", role: "Manager" },
  { id: "emp2", name: "Mike Chen", role: "Budtender" },
  { id: "emp3", name: "Emma Rodriguez", role: "Cashier" },
  { id: "emp4", name: "David Kim", role: "Security" },
  { id: "emp5", name: "Lisa Park", role: "Budtender" },
  { id: "emp6", name: "John Smith", role: "Cashier" }
];

export default function RoomsDrawers() {
  const [rooms, setRooms] = useState<Room[]>(mockRooms);
  const [selectedRoom, setSelectedRoom] = useState<Room | null>(null);
  const [selectedDrawer, setSelectedDrawer] = useState<Drawer | null>(null);
  const [showRoomDialog, setShowRoomDialog] = useState(false);
  const [showDrawerDialog, setShowDrawerDialog] = useState(false);
  const [showAccessDialog, setShowAccessDialog] = useState(false);
  const [showAssignDialog, setShowAssignDialog] = useState(false);
  const [selectedDrawerForAssign, setSelectedDrawerForAssign] = useState<{roomId: string, drawerId: string} | null>(null);
  const [showCountDialog, setShowCountDialog] = useState(false);
  const [selectedDrawerForCount, setSelectedDrawerForCount] = useState<{roomId: string, drawerId: string} | null>(null);
  const [countAmount, setCountAmount] = useState("");
  const [denominations, setDenominations] = useState({
    hundreds: 0,
    fifties: 0,
    twenties: 0,
    tens: 0,
    fives: 0,
    ones: 0,
    quarters: 0,
    dimes: 0,
    nickels: 0,
    pennies: 0
  });
  const [cashSales, setCashSales] = useState("");
  const [debitSales, setDebitSales] = useState("");
  const [dailyDebitSales, setDailyDebitSales] = useState("");
  const [newRoomForm, setNewRoomForm] = useState({
    name: "",
    type: "storage" as Room['type'],
    capacity: "",
    securityLevel: "medium" as Room['securityLevel'],
    notes: ""
  });
  const [newDrawerForm, setNewDrawerForm] = useState({
    name: ""
  });
  const [selectedRoomForDrawer, setSelectedRoomForDrawer] = useState<string | null>(null);
  const [showEditRoomDialog, setShowEditRoomDialog] = useState(false);
  const [roomToEdit, setRoomToEdit] = useState<Room | null>(null);
  const [accessLogSearch, setAccessLogSearch] = useState("");
  const [accessLogFilter, setAccessLogFilter] = useState({
    employee: "all",
    action: "all",
    room: "all",
    dateRange: "all"
  });
  const [editRoomForm, setEditRoomForm] = useState({
    name: "",
    type: "",
    capacity: "",
    securityLevel: ""
  });
  const [showEditDrawerDialog, setShowEditDrawerDialog] = useState(false);
  const [drawerToEdit, setDrawerToEdit] = useState<{drawer: Drawer, roomId: string} | null>(null);
  const [editDrawerForm, setEditDrawerForm] = useState({
    name: "",
    type: "",
    assignedEmployee: "",
    dropNotificationAmount: ""
  });
  const [showBeginningBalanceDialog, setShowBeginningBalanceDialog] = useState(false);
  const [beginningBalance, setBeginningBalance] = useState("");
  const [drawerToOpen, setDrawerToOpen] = useState<{roomId: string, drawerId: string} | null>(null);

  // Function to calculate today's debit sales automatically
  const calculateTodaysDebitSales = () => {
    // In a real app, this would fetch from sales data/API
    // For demo purposes, we'll simulate calculating today's debit sales
    const today = new Date().toISOString().split('T')[0];

    // Mock calculation - in real app would query sales database
    // This would typically sum all debit transactions from today
    const mockDebitSales = Math.random() * 2000 + 500; // Random between $500-$2500

    return Math.round(mockDebitSales * 100) / 100; // Round to 2 decimal places
  };

  const toggleDrawerStatus = (roomId: string, drawerId: string) => {
    const room = rooms.find(r => r.id === roomId);
    const drawer = room?.drawers.find(d => d.id === drawerId);

    if (drawer && !drawer.isOpen && drawer.type === 'cash/debit') {
      // Ask for beginning balance when opening a cash drawer
      setDrawerToOpen({ roomId, drawerId });
      setBeginningBalance("");
      setShowBeginningBalanceDialog(true);
    } else {
      // Toggle drawer status directly for non-cash drawers or when closing
      setRooms(prev => prev.map(room =>
        room.id === roomId
          ? {
              ...room,
              drawers: room.drawers.map(drawer =>
                drawer.id === drawerId
                  ? { ...drawer, isOpen: !drawer.isOpen }
                  : drawer
              )
            }
          : room
      ));
    }
  };

  const openDrawerWithBalance = () => {
    if (!drawerToOpen || !beginningBalance) {
      alert("Please enter a beginning balance");
      return;
    }

    const balance = parseFloat(beginningBalance);
    if (isNaN(balance) || balance < 0) {
      alert("Please enter a valid beginning balance");
      return;
    }

    // Automatically calculate today's debit sales
    const todaysDebitSales = calculateTodaysDebitSales();

    setRooms(prev => prev.map(room =>
      room.id === drawerToOpen.roomId
        ? {
            ...room,
            drawers: room.drawers.map(drawer =>
              drawer.id === drawerToOpen.drawerId
                ? {
                    ...drawer,
                    isOpen: true,
                    openingAmount: balance,
                    expectedValue: balance,
                    dailySales: todaysDebitSales, // Automatically set today's debit sales
                    lastAccessed: new Date().toISOString().slice(0, 16).replace('T', ' ')
                  }
                : drawer
            )
          }
        : room
    ));

    // Auto-populate the daily debit sales field
    setDailyDebitSales(todaysDebitSales.toFixed(2));

    setShowBeginningBalanceDialog(false);
    setDrawerToOpen(null);
    setBeginningBalance("");
    alert(`Drawer opened with beginning balance of $${balance.toFixed(2)}\nToday's debit sales automatically calculated: $${todaysDebitSales.toFixed(2)}`);
  };

  const assignEmployeeToDrawer = (roomId: string, drawerId: string, employeeName: string) => {
    setRooms(prev => prev.map(room =>
      room.id === roomId
        ? {
            ...room,
            drawers: room.drawers.map(drawer =>
              drawer.id === drawerId
                ? { ...drawer, assignedEmployee: employeeName }
                : drawer
            )
          }
        : room
    ));
    setShowAssignDialog(false);
    setSelectedDrawerForAssign(null);
  };

  const calculateDenominationTotal = () => {
    return (
      denominations.hundreds * 100 +
      denominations.fifties * 50 +
      denominations.twenties * 20 +
      denominations.tens * 10 +
      denominations.fives * 5 +
      denominations.ones * 1 +
      denominations.quarters * 0.25 +
      denominations.dimes * 0.10 +
      denominations.nickels * 0.05 +
      denominations.pennies * 0.01
    );
  };

  const countDrawer = (roomId: string, drawerId: string, countedAmount: number) => {
    const room = rooms.find(r => r.id === roomId);
    const drawer = room?.drawers.find(d => d.id === drawerId);

    setRooms(prev => prev.map(room =>
      room.id === roomId
        ? {
            ...room,
            drawers: room.drawers.map(drawer =>
              drawer.id === drawerId
                ? {
                    ...drawer,
                    countedValue: countedAmount,
                    lastCounted: new Date().toISOString().slice(0, 16).replace('T', ' ')
                  }
                : drawer
            )
          }
        : room
    ));

    const debitSales = drawer?.dailySales || 0;

    setShowCountDialog(false);
    setSelectedDrawerForCount(null);
    setCountAmount("");
    resetCountingForm();

    // Show summary with auto-calculated debit sales
    alert(`Drawer count completed!

Cash Counted: $${countedAmount.toFixed(2)}
Debit Sales (Auto): $${debitSales.toFixed(2)}
Expected Value: $${drawer?.expectedValue?.toFixed(2) || '0.00'}
Variance: $${(countedAmount - (drawer?.expectedValue || 0)).toFixed(2)}`);
  };

  const resetCountingForm = () => {
    setDenominations({
      hundreds: 0,
      fifties: 0,
      twenties: 0,
      tens: 0,
      fives: 0,
      ones: 0,
      quarters: 0,
      dimes: 0,
      nickels: 0,
      pennies: 0
    });
    setCashSales("");
    setDebitSales("");
    setDailyDebitSales("");
  };

  const createRoom = () => {
    const newRoom: Room = {
      id: Date.now().toString(),
      name: newRoomForm.name,
      type: newRoomForm.type,
      status: "active",
      temperature: 70,
      humidity: 40,
      capacity: parseInt(newRoomForm.capacity) || 100,
      currentInventory: 0,
      securityLevel: newRoomForm.securityLevel,
      accessLevel: ["manager"],
      lastInspection: new Date().toISOString().split('T')[0],
      notes: newRoomForm.notes,
      drawers: []
    };

    setRooms(prev => [...prev, newRoom]);
    setShowRoomDialog(false);
    setNewRoomForm({
      name: "",
      type: "storage",
      capacity: "",
      securityLevel: "medium",
      notes: ""
    });
  };

  const editRoom = (room: Room) => {
    setRoomToEdit(room);
    setEditRoomForm({
      name: room.name,
      type: room.type,
      capacity: room.capacity?.toString() || "",
      securityLevel: room.securityLevel || "medium"
    });
    setShowEditRoomDialog(true);
  };

  const updateRoom = () => {
    if (!roomToEdit || !editRoomForm.name) {
      alert("Please fill in all required fields");
      return;
    }

    const updatedRoom: Room = {
      ...roomToEdit,
      name: editRoomForm.name,
      type: editRoomForm.type as Room['type'],
      capacity: parseInt(editRoomForm.capacity) || roomToEdit.capacity,
      securityLevel: editRoomForm.securityLevel as Room['securityLevel']
    };

    setRooms(prev => prev.map(room =>
      room.id === roomToEdit.id ? updatedRoom : room
    ));
    setShowEditRoomDialog(false);
    setRoomToEdit(null);
    setEditRoomForm({
      name: "",
      type: "",
      capacity: "",
      securityLevel: ""
    });
    alert(`Room "${updatedRoom.name}" has been updated successfully!`);
  };

  const deleteRoom = (roomId: string) => {
    if (confirm("Are you sure you want to delete this room? This will also delete all drawers in this room.")) {
      setRooms(prev => prev.filter(room => room.id !== roomId));
    }
  };

  const createDrawer = (roomId: string) => {
    const room = rooms.find(r => r.id === roomId);
    const newDrawer: Drawer = {
      id: Date.now().toString(),
      name: newDrawerForm.name,
      location: room?.name || "Unknown Location",
      type: "cash/debit",
      isOpen: false,
      dropNotificationAmount: 500,
      lastAccessed: new Date().toISOString().slice(0, 16).replace('T', ' '),
      accessHistory: []
    };

    setRooms(prev => prev.map(room =>
      room.id === roomId
        ? { ...room, drawers: [...room.drawers, newDrawer] }
        : room
    ));

    setShowDrawerDialog(false);
    setSelectedRoomForDrawer(null);
    setNewDrawerForm({
      name: ""
    });
  };

  const deleteDrawer = (roomId: string, drawerId: string) => {
    if (confirm("Are you sure you want to delete this drawer?")) {
      setRooms(prev => prev.map(room =>
        room.id === roomId
          ? { ...room, drawers: room.drawers.filter(drawer => drawer.id !== drawerId) }
          : room
      ));
    }
  };

  const editDrawer = (drawer: Drawer, roomId: string) => {
    setDrawerToEdit({ drawer, roomId });
    setEditDrawerForm({
      name: drawer.name,
      type: drawer.type,
      assignedEmployee: drawer.assignedEmployee || "",
      dropNotificationAmount: drawer.dropNotificationAmount?.toString() || ""
    });
    setShowEditDrawerDialog(true);
  };

  const updateDrawer = () => {
    if (!drawerToEdit || !editDrawerForm.name) {
      alert("Please fill in all required fields");
      return;
    }

    const updatedDrawer: Drawer = {
      ...drawerToEdit.drawer,
      name: editDrawerForm.name,
      type: editDrawerForm.type as Drawer['type'],
      assignedEmployee: editDrawerForm.assignedEmployee || undefined,
      dropNotificationAmount: parseFloat(editDrawerForm.dropNotificationAmount) || undefined
    };

    setRooms(prev => prev.map(room =>
      room.id === drawerToEdit.roomId
        ? {
            ...room,
            drawers: room.drawers.map(drawer =>
              drawer.id === drawerToEdit.drawer.id ? updatedDrawer : drawer
            )
          }
        : room
    ));

    setShowEditDrawerDialog(false);
    setDrawerToEdit(null);
    setEditDrawerForm({
      name: "",
      type: "",
      assignedEmployee: "",
      dropNotificationAmount: ""
    });
    alert(`Drawer "${updatedDrawer.name}" has been updated successfully!`);
  };

  const printDrawerSheet = (drawer: Drawer, room: Room) => {
    const variance = (drawer.countedValue || 0) - (drawer.expectedValue || 0);
    const totalDenominations = calculateDenominationTotal();

    const printContent = `
      DRAWER CLOSING SHEET
      ====================

      Room: ${room.name}
      Drawer: ${drawer.name}
      Location: ${drawer.location}
      Assigned Employee: ${drawer.assignedEmployee || 'Unassigned'}
      Date: ${new Date().toLocaleDateString()}
      Time: ${new Date().toLocaleTimeString()}

      CASH BREAKDOWN
      ==============
      Opening Amount: $${drawer.openingAmount?.toFixed(2) || '0.00'}
      Daily Sales: $${drawer.dailySales?.toFixed(2) || '0.00'}
      Expected Total: $${drawer.expectedValue?.toFixed(2) || '0.00'}
      Counted Amount: $${drawer.countedValue?.toFixed(2) || '0.00'}

      DENOMINATIONS COUNTED
      =====================
      $100 Bills:     ${denominations.hundreds.toString().padStart(3)} = $${(denominations.hundreds * 100).toFixed(2).padStart(8)}
      $50 Bills:      ${denominations.fifties.toString().padStart(3)} = $${(denominations.fifties * 50).toFixed(2).padStart(8)}
      $20 Bills:      ${denominations.twenties.toString().padStart(3)} = $${(denominations.twenties * 20).toFixed(2).padStart(8)}
      $10 Bills:      ${denominations.tens.toString().padStart(3)} = $${(denominations.tens * 10).toFixed(2).padStart(8)}
      $5 Bills:       ${denominations.fives.toString().padStart(3)} = $${(denominations.fives * 5).toFixed(2).padStart(8)}
      $1 Bills:       ${denominations.ones.toString().padStart(3)} = $${(denominations.ones * 1).toFixed(2).padStart(8)}
      Quarters:       ${denominations.quarters.toString().padStart(3)} = $${(denominations.quarters * 0.25).toFixed(2).padStart(8)}
      Dimes:          ${denominations.dimes.toString().padStart(3)} = $${(denominations.dimes * 0.10).toFixed(2).padStart(8)}
      Nickels:        ${denominations.nickels.toString().padStart(3)} = $${(denominations.nickels * 0.05).toFixed(2).padStart(8)}
      Pennies:        ${denominations.pennies.toString().padStart(3)} = $${(denominations.pennies * 0.01).toFixed(2).padStart(8)}
                                               ��─────────────���─
      DENOMINATION TOTAL:                          $${totalDenominations.toFixed(2).padStart(8)}

      SALES BREAKDOWN
      ===============
      Cash Sales:     $${cashSales || '0.00'}
      Debit Sales:    $${debitSales || '0.00'}

      VARIANCE: $${variance.toFixed(2)} ${variance > 0 ? '(OVER)' : variance < 0 ? '(UNDER)' : '(EXACT)'}

      Employee Signature: _________________
      Manager Signature: __________________

      Last Count: ${drawer.lastCounted || 'Never'}
    `;

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`<pre style="font-family: monospace; font-size: 12px;">${printContent}</pre>`);
      printWindow.document.close();
      printWindow.print();
    }
  };

  const totalValue = rooms.reduce((sum, room) => 
    sum + room.drawers.reduce((drawerSum, drawer) => 
      drawerSum + (drawer.currentValue || 0), 0
    ), 0
  );

  const alertRooms = rooms.filter(room => 
    room.status === 'maintenance' || 
    room.currentInventory / room.capacity > 0.9 ||
    room.temperature < 65 || room.temperature > 75
  );

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-semibold">Rooms & Drawers</h1>
            <p className="text-sm opacity-80">Facility and storage management</p>
          </div>
          <div className="flex items-center gap-4">
            <div className="text-right">
              <div className="text-lg font-semibold">${totalValue.toLocaleString()}</div>
              <div className="text-xs opacity-80">Total Stored Value</div>
            </div>
            <Dialog open={showRoomDialog} onOpenChange={setShowRoomDialog}>
              <DialogTrigger asChild>
                <Button className="header-button-visible">
                  <Plus className="w-4 h-4 mr-2" />
                  Add Room
                </Button>
              </DialogTrigger>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Add New Room</DialogTitle>
                </DialogHeader>
                <div className="space-y-4">
                  <div>
                    <Label htmlFor="room-name">Room Name</Label>
                    <Input
                      id="room-name"
                      placeholder="Enter room name"
                      value={newRoomForm.name}
                      onChange={(e) => setNewRoomForm(prev => ({...prev, name: e.target.value}))}
                    />
                  </div>
                  <div>
                    <Label htmlFor="room-type">Room Type</Label>
                    <Select value={newRoomForm.type} onValueChange={(value: Room['type']) => setNewRoomForm(prev => ({...prev, type: value}))}>
                      <SelectTrigger>
                        <SelectValue placeholder="Select type" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="storage">Storage</SelectItem>
                        <SelectItem value="cultivation">Cultivation</SelectItem>
                        <SelectItem value="processing">Processing</SelectItem>
                        <SelectItem value="for sale">For Sale</SelectItem>
                        <SelectItem value="hold safe">Hold Safe</SelectItem>
                        <SelectItem value="back room">Back Room</SelectItem>
                        <SelectItem value="hold fix">Hold Fix</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div>
                    <Label htmlFor="notes">Notes</Label>
                    <Textarea
                      id="notes"
                      placeholder="Additional information..."
                      value={newRoomForm.notes}
                      onChange={(e) => setNewRoomForm(prev => ({...prev, notes: e.target.value}))}
                    />
                  </div>
                  <Button
                    className="w-full"
                    onClick={createRoom}
                    disabled={!newRoomForm.name}
                  >
                    Add Room
                  </Button>
                </div>
              </DialogContent>
            </Dialog>
          </div>
        </div>
      </header>

      <div className="container mx-auto p-6">
        <Tabs defaultValue="overview" className="space-y-6">
          <TabsList>
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="rooms">Rooms</TabsTrigger>
            <TabsTrigger value="drawers">Drawers</TabsTrigger>
            <TabsTrigger value="access">Access Logs</TabsTrigger>
          </TabsList>

          <TabsContent value="overview" className="space-y-6">
            {/* Alert Section */}
            {alertRooms.length > 0 && (
              <Card className="border-orange-200 bg-orange-50">
                <CardHeader>
                  <CardTitle className="flex items-center gap-2 text-orange-800">
                    <AlertTriangle className="w-5 h-5" />
                    Room Alerts ({alertRooms.length})
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    {alertRooms.map(room => (
                      <div key={room.id} className="flex items-center justify-between p-2 bg-white rounded">
                        <span className="font-medium">{room.name}</span>
                        <div className="flex items-center gap-2">
                          {room.status === 'maintenance' && (
                            <Badge variant="secondary">Under Maintenance</Badge>
                          )}
                          {room.currentInventory / room.capacity > 0.9 && (
                            <Badge variant="secondary">Near Capacity</Badge>
                          )}
                          {(room.temperature < 65 || room.temperature > 75) && (
                            <Badge variant="secondary">Temperature Alert</Badge>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-blue-600">{rooms.length}</div>
                  <div className="text-sm text-muted-foreground">Total Rooms</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-green-600">
                    {rooms.reduce((sum, room) => sum + room.drawers.length, 0)}
                  </div>
                  <div className="text-sm text-muted-foreground">Total Drawers</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-purple-600">
                    ${totalValue.toLocaleString()}
                  </div>
                  <div className="text-sm text-muted-foreground">Total Value</div>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-4 text-center">
                  <div className="text-2xl font-bold text-red-600">
                    {rooms.filter(r => r.status === 'active').length}
                  </div>
                  <div className="text-sm text-muted-foreground">Active Rooms</div>
                </CardContent>
              </Card>
            </div>

            {/* Room Status Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
              {rooms.map(room => (
                <Card key={room.id} className="hover:shadow-md transition-shadow">
                  <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <Home className="w-5 h-5 text-muted-foreground" />
                        <h3 className="font-semibold">{room.name}</h3>
                      </div>
                      <div className="flex gap-1">
                        <Badge className={roomTypeColors[room.type]}>
                          {room.type}
                        </Badge>
                        <Badge className={statusColors[room.status]}>
                          {room.status}
                        </Badge>
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    <div className="grid grid-cols-2 gap-4 text-sm">
                      <div className="flex items-center gap-2">
                        <Thermometer className="w-4 h-4 text-muted-foreground" />
                        <span>{room.temperature}°F</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <Package className="w-4 h-4 text-muted-foreground" />
                        <span>{room.currentInventory}/{room.capacity}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <Shield className="w-4 h-4 text-muted-foreground" />
                        <Badge className={securityColors[room.securityLevel]} variant="outline">
                          {room.securityLevel}
                        </Badge>
                      </div>
                      <div className="flex items-center gap-2">
                        <DollarSign className="w-4 h-4 text-muted-foreground" />
                        <span>${room.drawers.reduce((sum, d) => sum + (d.currentValue || 0), 0).toLocaleString()}</span>
                      </div>
                    </div>

                    {room.drawers.length > 0 && (
                      <div>
                        <div className="text-sm font-medium mb-2">Drawers ({room.drawers.length})</div>
                        <div className="flex flex-wrap gap-1">
                          {room.drawers.slice(0, 3).map(drawer => (
                            <div key={drawer.id} className="flex items-center gap-1">
                              <div className={`w-3 h-3 rounded-full ${drawer.isOpen ? 'bg-green-500' : 'bg-red-500'}`} />
                              <span className="text-xs">{drawer.name.substring(0, 15)}...</span>
                            </div>
                          ))}
                          {room.drawers.length > 3 && (
                            <span className="text-xs text-muted-foreground">+{room.drawers.length - 3} more</span>
                          )}
                        </div>
                      </div>
                    )}

                    <div className="flex gap-2 pt-2">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedRoom(room);
                          setShowAccessDialog(true);
                        }}
                      >
                        <Eye className="w-3 h-3 mr-1" />
                        View
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => editRoom(room)}
                      >
                        <Edit className="w-3 h-3 mr-1" />
                        Edit
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedRoomForDrawer(room.id);
                          setShowDrawerDialog(true);
                        }}
                      >
                        <Plus className="w-3 h-3 mr-1" />
                        Add Drawer
                      </Button>
                      <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => deleteRoom(room.id)}
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

          <TabsContent value="rooms" className="space-y-6">
            <div className="grid grid-cols-1 gap-4">
              {rooms.map(room => (
                <Card key={room.id}>
                  <CardHeader>
                    <div className="flex items-center justify-between">
                      <div>
                        <h3 className="text-lg font-semibold">{room.name}</h3>
                        <p className="text-sm text-muted-foreground">
                          Last inspection: {new Date(room.lastInspection).toLocaleDateString()}
                        </p>
                      </div>
                      <div className="flex gap-2">
                        <Badge className={roomTypeColors[room.type]}>{room.type}</Badge>
                        <Badge className={statusColors[room.status]}>{room.status}</Badge>
                        <Badge className={securityColors[room.securityLevel]}>{room.securityLevel} security</Badge>
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <h4 className="font-medium mb-2">Capacity</h4>
                        <div className="space-y-1 text-sm">
                          <div>Current: {room.currentInventory}</div>
                          <div>Maximum: {room.capacity}</div>
                          <div>Usage: {((room.currentInventory / room.capacity) * 100).toFixed(1)}%</div>
                        </div>
                      </div>
                      <div>
                        <h4 className="font-medium mb-2">Access</h4>
                        <div className="space-y-1 text-sm">
                          <div>Authorized roles: {room.accessLevel.join(', ')}</div>
                          <div>Drawers: {room.drawers.length}</div>
                        </div>
                      </div>
                    </div>
                    {room.notes && (
                      <div className="mt-4 p-3 bg-gray-50 rounded text-sm">
                        <strong>Notes:</strong> {room.notes}
                      </div>
                    )}
                    <div className="flex gap-2 mt-4 pt-4 border-t">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => {
                          setSelectedRoomForDrawer(room.id);
                          setShowDrawerDialog(true);
                        }}
                      >
                        <Plus className="w-3 h-3 mr-1" />
                        Add Drawer
                      </Button>
                      <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => deleteRoom(room.id)}
                      >
                        <Trash2 className="w-3 h-3 mr-1" />
                        Delete Room
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>

          <TabsContent value="drawers" className="space-y-6">
            <div className="space-y-4">
              {rooms.map(room => 
                room.drawers.length > 0 && (
                  <Card key={room.id}>
                    <CardHeader>
                      <CardTitle>{room.name} - Drawers</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        {room.drawers.map(drawer => (
                          <div key={drawer.id} className="p-4 border rounded-lg">
                            <div className="flex items-center justify-between mb-3">
                              <h4 className="font-semibold">{drawer.name}</h4>
                              <div className="flex items-center gap-2">
                                <Badge variant="outline">{drawer.type}</Badge>
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => toggleDrawerStatus(room.id, drawer.id)}
                                >
                                  {drawer.isOpen ? <Unlock className="w-3 h-3" /> : <Lock className="w-3 h-3" />}
                                </Button>
                              </div>
                            </div>
                            <div className="space-y-2 text-sm">
                              <div className="flex justify-between">
                                <span>Location:</span>
                                <span>{drawer.location}</span>
                              </div>
                              <div className="flex justify-between">
                                <span>Assigned to:</span>
                                <span className="font-medium">
                                  {drawer.assignedEmployee || "Unassigned"}
                                </span>
                              </div>
                              {drawer.type === 'cash/debit' && (
                                <>
                                  <div className="flex justify-between">
                                    <span>Expected:</span>
                                    <span className="font-semibold">${drawer.expectedValue?.toFixed(2) || '0.00'}</span>
                                  </div>
                                  <div className="flex justify-between">
                                    <span>Counted:</span>
                                    <span className="font-semibold">${drawer.countedValue?.toFixed(2) || '0.00'}</span>
                                  </div>
                                  {drawer.expectedValue && drawer.countedValue && (
                                    <div className="flex justify-between">
                                      <span>Variance:</span>
                                      <span className={`font-semibold ${
                                        drawer.countedValue - drawer.expectedValue > 0 ? 'text-green-600' :
                                        drawer.countedValue - drawer.expectedValue < 0 ? 'text-red-600' : 'text-gray-600'
                                      }`}>
                                        ${(drawer.countedValue - drawer.expectedValue).toFixed(2)}
                                        {drawer.countedValue - drawer.expectedValue > 0 ? ' (OVER)' :
                                         drawer.countedValue - drawer.expectedValue < 0 ? ' (UNDER)' : ' (EXACT)'}
                                      </span>
                                    </div>
                                  )}
                                </>
                              )}
                              {drawer.currentValue && drawer.type !== 'cash' && (
                                <div className="flex justify-between">
                                  <span>Value:</span>
                                  <span className="font-semibold">${drawer.currentValue.toLocaleString()}</span>
                                </div>
                              )}
                              <div className="flex justify-between">
                                <span>Last Accessed:</span>
                                <span>{drawer.lastAccessed}</span>
                              </div>
                              <div className="flex justify-between">
                                <span>Status:</span>
                                <Badge variant={drawer.isOpen ? "default" : "destructive"}>
                                  {drawer.isOpen ? "Open" : "Closed"}
                                </Badge>
                              </div>
                            </div>
                            <div className="flex gap-1 pt-2 flex-wrap">
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => {
                                  setSelectedDrawerForAssign({roomId: room.id, drawerId: drawer.id});
                                  setShowAssignDialog(true);
                                }}
                              >
                                <Users className="w-3 h-3 mr-1" />
                                Assign
                              </Button>
                              {drawer.type === 'cash/debit' && (
                                <>
                                  <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={() => {
                                      setSelectedDrawerForCount({roomId: room.id, drawerId: drawer.id});
                                      setCountAmount(drawer.countedValue?.toString() || "");
                                      setShowCountDialog(true);
                                    }}
                                  >
                                    <Calculator className="w-3 h-3 mr-1" />
                                    Count
                                  </Button>
                                  <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={() => printDrawerSheet(drawer, room)}
                                  >
                                    <Printer className="w-3 h-3 mr-1" />
                                    Print
                                  </Button>
                                  <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={() => printDrawerSheet(drawer, room)}
                                  >
                                    <FileDown className="w-3 h-3 mr-1" />
                                    Reprint
                                  </Button>
                                </>
                              )}
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => editDrawer(drawer, room.id)}
                              >
                                <Edit className="w-3 h-3 mr-1" />
                                Edit
                              </Button>
                              <Button
                                size="sm"
                                variant="destructive"
                                onClick={() => deleteDrawer(room.id, drawer.id)}
                              >
                                <Trash2 className="w-3 h-3 mr-1" />
                                Delete
                              </Button>
                            </div>
                          </div>
                        ))}
                      </div>
                    </CardContent>
                  </Card>
                )
              )}
            </div>
          </TabsContent>

          <TabsContent value="access" className="space-y-6">
            {/* Search and Filter Controls */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Search className="w-5 h-5" />
                  Access Log Search & Filter
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {/* Search Bar */}
                <div className="flex gap-4">
                  <div className="flex-1 relative">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <Input
                      placeholder="Search by employee name, action, room, drawer, or notes..."
                      value={accessLogSearch}
                      onChange={(e) => setAccessLogSearch(e.target.value)}
                      className="pl-10"
                    />
                  </div>
                  <Button
                    variant="outline"
                    onClick={() => {
                      // Generate and download access log report
                      const allLogs = rooms.flatMap(room =>
                        room.drawers.flatMap(drawer =>
                          drawer.accessHistory.map(log => ({
                            ...log,
                            roomName: room.name,
                            drawerName: drawer.name
                          }))
                        )
                      ).sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime());

                      const reportContent = `ACCESS LOG REPORT\nGenerated: ${new Date().toLocaleString()}\n\n` +
                        allLogs.map(log =>
                          `${log.timestamp} | ${log.employee} | ${log.action.toUpperCase()} | ${log.roomName} - ${log.drawerName}${log.notes ? ' | Notes: ' + log.notes : ''}`
                        ).join('\n');

                      const blob = new Blob([reportContent], { type: 'text/plain' });
                      const url = window.URL.createObjectURL(blob);
                      const a = document.createElement('a');
                      a.href = url;
                      a.download = `access-log-report-${new Date().toISOString().split('T')[0]}.txt`;
                      document.body.appendChild(a);
                      a.click();
                      document.body.removeChild(a);
                      window.URL.revokeObjectURL(url);
                    }}
                  >
                    <Download className="w-4 h-4 mr-2" />
                    Export Logs
                  </Button>
                </div>

                {/* Filter Controls */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div>
                    <Label>Employee</Label>
                    <Select value={accessLogFilter.employee} onValueChange={(value) => setAccessLogFilter(prev => ({...prev, employee: value}))}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Employees</SelectItem>
                        {[...new Set(rooms.flatMap(room =>
                          room.drawers.flatMap(drawer =>
                            drawer.accessHistory.map(log => log.employee)
                          )
                        ))].sort().map(employee => (
                          <SelectItem key={employee} value={employee}>{employee}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label>Action</Label>
                    <Select value={accessLogFilter.action} onValueChange={(value) => setAccessLogFilter(prev => ({...prev, action: value}))}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Actions</SelectItem>
                        <SelectItem value="open">Open</SelectItem>
                        <SelectItem value="close">Close</SelectItem>
                        <SelectItem value="stock">Stock</SelectItem>
                        <SelectItem value="remove">Remove</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label>Room</Label>
                    <Select value={accessLogFilter.room} onValueChange={(value) => setAccessLogFilter(prev => ({...prev, room: value}))}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Rooms</SelectItem>
                        {rooms.map(room => (
                          <SelectItem key={room.id} value={room.name}>{room.name}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label>Date Range</Label>
                    <Select value={accessLogFilter.dateRange} onValueChange={(value) => setAccessLogFilter(prev => ({...prev, dateRange: value}))}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Time</SelectItem>
                        <SelectItem value="today">Today</SelectItem>
                        <SelectItem value="week">This Week</SelectItem>
                        <SelectItem value="month">This Month</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                {/* Clear Filters */}
                {(accessLogSearch || accessLogFilter.employee !== "all" || accessLogFilter.action !== "all" ||
                  accessLogFilter.room !== "all" || accessLogFilter.dateRange !== "all") && (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => {
                      setAccessLogSearch("");
                      setAccessLogFilter({ employee: "all", action: "all", room: "all", dateRange: "all" });
                    }}
                  >
                    Clear All Filters
                  </Button>
                )}
              </CardContent>
            </Card>

            {/* Access Log Results */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center justify-between">
                  <span>Access Activity</span>
                  <Badge variant="secondary">
                    {(() => {
                      const allLogs = rooms.flatMap(room =>
                        room.drawers.flatMap(drawer =>
                          drawer.accessHistory.map(log => ({
                            ...log,
                            roomName: room.name,
                            drawerName: drawer.name
                          }))
                        )
                      );

                      const filteredLogs = allLogs.filter(log => {
                        // Search filter
                        const searchMatch = !accessLogSearch ||
                          log.employee.toLowerCase().includes(accessLogSearch.toLowerCase()) ||
                          log.action.toLowerCase().includes(accessLogSearch.toLowerCase()) ||
                          log.roomName.toLowerCase().includes(accessLogSearch.toLowerCase()) ||
                          log.drawerName.toLowerCase().includes(accessLogSearch.toLowerCase()) ||
                          (log.notes && log.notes.toLowerCase().includes(accessLogSearch.toLowerCase()));

                        // Filter conditions
                        const employeeMatch = accessLogFilter.employee === "all" || log.employee === accessLogFilter.employee;
                        const actionMatch = accessLogFilter.action === "all" || log.action === accessLogFilter.action;
                        const roomMatch = accessLogFilter.room === "all" || log.roomName === accessLogFilter.room;

                        // Date filter
                        let dateMatch = true;
                        if (accessLogFilter.dateRange !== "all") {
                          const logDate = new Date(log.timestamp);
                          const now = new Date();

                          switch (accessLogFilter.dateRange) {
                            case "today":
                              dateMatch = logDate.toDateString() === now.toDateString();
                              break;
                            case "week":
                              const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                              dateMatch = logDate >= weekAgo;
                              break;
                            case "month":
                              const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                              dateMatch = logDate >= monthAgo;
                              break;
                          }
                        }

                        return searchMatch && employeeMatch && actionMatch && roomMatch && dateMatch;
                      });

                      return `${filteredLogs.length} entries`;
                    })()}
                  </Badge>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3 max-h-96 overflow-y-auto">
                  {(() => {
                    const allLogs = rooms.flatMap(room =>
                      room.drawers.flatMap(drawer =>
                        drawer.accessHistory.map(log => ({
                          ...log,
                          roomName: room.name,
                          drawerName: drawer.name
                        }))
                      )
                    );

                    const filteredLogs = allLogs.filter(log => {
                      // Search filter
                      const searchMatch = !accessLogSearch ||
                        log.employee.toLowerCase().includes(accessLogSearch.toLowerCase()) ||
                        log.action.toLowerCase().includes(accessLogSearch.toLowerCase()) ||
                        log.roomName.toLowerCase().includes(accessLogSearch.toLowerCase()) ||
                        log.drawerName.toLowerCase().includes(accessLogSearch.toLowerCase()) ||
                        (log.notes && log.notes.toLowerCase().includes(accessLogSearch.toLowerCase()));

                      // Filter conditions
                      const employeeMatch = accessLogFilter.employee === "all" || log.employee === accessLogFilter.employee;
                      const actionMatch = accessLogFilter.action === "all" || log.action === accessLogFilter.action;
                      const roomMatch = accessLogFilter.room === "all" || log.roomName === accessLogFilter.room;

                      // Date filter
                      let dateMatch = true;
                      if (accessLogFilter.dateRange !== "all") {
                        const logDate = new Date(log.timestamp);
                        const now = new Date();

                        switch (accessLogFilter.dateRange) {
                          case "today":
                            dateMatch = logDate.toDateString() === now.toDateString();
                            break;
                          case "week":
                            const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                            dateMatch = logDate >= weekAgo;
                            break;
                          case "month":
                            const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                            dateMatch = logDate >= monthAgo;
                            break;
                        }
                      }

                      return searchMatch && employeeMatch && actionMatch && roomMatch && dateMatch;
                    }).sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime());

                    if (filteredLogs.length === 0) {
                      return (
                        <div className="text-center py-8 text-gray-500">
                          <Search className="w-8 h-8 mx-auto mb-2 text-gray-400" />
                          <p>No access logs found matching your criteria</p>
                        </div>
                      );
                    }

                    return filteredLogs.map((log, index) => (
                      <div key={index} className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                        <div className="flex-1">
                          <div className="flex items-center gap-3 mb-2">
                            <div className="font-bold text-gray-900">{log.employee}</div>
                            <Badge
                              variant={log.action === 'open' ? 'default' : log.action === 'close' ? 'secondary' :
                                      log.action === 'stock' ? 'outline' : 'destructive'}
                              className="font-semibold"
                            >
                              {log.action.toUpperCase()}
                            </Badge>
                          </div>
                          <div className="text-sm text-gray-700 font-medium">
                            {log.drawerName} in {log.roomName}
                          </div>
                          {log.notes && (
                            <div className="text-sm text-gray-600 mt-1 italic">
                              📝 {log.notes}
                            </div>
                          )}
                        </div>
                        <div className="text-right">
                          <div className="text-sm font-semibold text-gray-900">
                            {new Date(log.timestamp).toLocaleDateString()}
                          </div>
                          <div className="text-sm text-gray-600">
                            {new Date(log.timestamp).toLocaleTimeString()}
                          </div>
                        </div>
                      </div>
                    ));
                  })()}
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>

        {/* Employee Assignment Dialog */}
        <Dialog open={showAssignDialog} onOpenChange={setShowAssignDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Assign Employee to Drawer</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label>Select Employee</Label>
                <div className="space-y-2 mt-2">
                  {availableEmployees.map(employee => (
                    <div
                      key={employee.id}
                      className="flex items-center justify-between p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                      onClick={() => {
                        if (selectedDrawerForAssign) {
                          assignEmployeeToDrawer(
                            selectedDrawerForAssign.roomId,
                            selectedDrawerForAssign.drawerId,
                            employee.name
                          );
                        }
                      }}
                    >
                      <div>
                        <div className="font-medium">{employee.name}</div>
                        <div className="text-sm text-muted-foreground">{employee.role}</div>
                      </div>
                      <Button size="sm">Assign</Button>
                    </div>
                  ))}
                </div>
              </div>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  className="flex-1"
                  onClick={() => {
                    if (selectedDrawerForAssign) {
                      assignEmployeeToDrawer(
                        selectedDrawerForAssign.roomId,
                        selectedDrawerForAssign.drawerId,
                        ""
                      );
                    }
                  }}
                >
                  Unassign
                </Button>
                <Button
                  variant="outline"
                  className="flex-1"
                  onClick={() => setShowAssignDialog(false)}
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Add Drawer Dialog */}
        <Dialog open={showDrawerDialog} onOpenChange={setShowDrawerDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add New Drawer</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              {selectedRoomForDrawer && (
                <div className="p-3 bg-blue-50 rounded-lg">
                  <Label>Adding drawer to:</Label>
                  <div className="font-semibold text-blue-800">
                    {rooms.find(r => r.id === selectedRoomForDrawer)?.name}
                  </div>
                </div>
              )}
              <div>
                <Label htmlFor="drawer-name">Drawer Name *</Label>
                <Input
                  id="drawer-name"
                  placeholder="Enter drawer name (e.g., Cash Register 1, Till #2)"
                  value={newDrawerForm.name}
                  onChange={(e) => setNewDrawerForm(prev => ({...prev, name: e.target.value}))}
                />
              </div>
              <div className="flex gap-2">
                <Button
                  onClick={() => {
                    if (selectedRoomForDrawer) {
                      createDrawer(selectedRoomForDrawer);
                    }
                  }}
                  className="flex-1"
                  disabled={!newDrawerForm.name}
                >
                  Add Drawer
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowDrawerDialog(false);
                    setSelectedRoomForDrawer(null);
                    setNewDrawerForm({ name: "" });
                  }}
                  className="flex-1"
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Drawer Count Dialog */}
        <Dialog open={showCountDialog} onOpenChange={setShowCountDialog}>
          <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Count Drawer - Denomination Breakdown</DialogTitle>
            </DialogHeader>
            <div className="space-y-6">
              {selectedDrawerForCount && (() => {
                const room = rooms.find(r => r.id === selectedDrawerForCount.roomId);
                const drawer = room?.drawers.find(d => d.id === selectedDrawerForCount.drawerId);
                const denominationTotal = calculateDenominationTotal();
                const cashTotal = parseFloat(cashSales) || 0;
                const debitTotal = parseFloat(debitSales) || 0;
                const totalCounted = denominationTotal;

                return drawer ? (
                  <>
                    <div className="p-4 bg-gray-50 rounded-lg">
                      <h3 className="font-semibold">{drawer.name}</h3>
                      <p className="text-sm text-muted-foreground">Location: {drawer.location}</p>
                      <p className="text-sm text-muted-foreground">Assigned to: {drawer.assignedEmployee}</p>
                    </div>

                    {/* Change Summary - Put at Top */}
                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                      <h4 className="font-semibold text-blue-800 mb-3">💰 Change Summary</h4>
                      <div className="grid grid-cols-3 gap-4 text-sm">
                        <div>
                          <Label className="text-blue-700">Opening Amount</Label>
                          <div className="font-bold text-blue-900">${drawer.openingAmount?.toFixed(2) || '0.00'}</div>
                        </div>
                        <div>
                          <Label className="text-blue-700">Expected Total</Label>
                          <div className="font-bold text-blue-900">${drawer.expectedValue?.toFixed(2) || '0.00'}</div>
                        </div>
                        <div>
                          <Label className="text-blue-700">Physical Count</Label>
                          <div className="font-bold text-lg text-blue-900">${denominationTotal.toFixed(2)}</div>
                        </div>
                      </div>
                      <div className="mt-3 pt-3 border-t border-blue-200">
                        <div className="flex justify-between items-center">
                          <span className="font-semibold text-blue-800">Variance:</span>
                          <span className={`font-bold text-lg ${
                            denominationTotal - (drawer.expectedValue || 0) > 0 ? 'text-green-600' :
                            denominationTotal - (drawer.expectedValue || 0) < 0 ? 'text-red-600' : 'text-blue-900'
                          }`}>
                            ${Math.abs(denominationTotal - (drawer.expectedValue || 0)).toFixed(2)}
                            {denominationTotal - (drawer.expectedValue || 0) > 0 ? ' (OVER)' :
                             denominationTotal - (drawer.expectedValue || 0) < 0 ? ' (UNDER)' : ' (EXACT)'}
                          </span>
                        </div>
                      </div>
                    </div>

                    {/* Daily Sales Tracking - Auto-calculated */}
                    <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                      <h4 className="font-semibold text-green-800 mb-3">📊 Daily Sales Tracking (Auto-Calculated)</h4>
                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <Label className="text-green-700">Total Debit Sales Today ($)</Label>
                          <div className="p-2 bg-white border border-green-300 rounded font-mono text-lg">
                            ${(drawer.dailySales || 0).toFixed(2)}
                          </div>
                          <div className="text-xs text-green-600 mt-1">
                            🔄 Automatically calculated from today's sales data
                          </div>
                        </div>
                        <div>
                          <Label className="text-green-700">Cash in Drawer Should Be</Label>
                          <div className="font-bold text-lg text-green-900">
                            ${((drawer.expectedValue || 0) - (drawer.dailySales || 0)).toFixed(2)}
                          </div>
                          <div className="text-xs text-green-600 mt-1">
                            Expected total minus debit sales
                          </div>
                        </div>
                      </div>
                      <div className="mt-3 pt-3 border-t border-green-200">
                        <div className="text-xs text-green-700">
                          💡 <strong>Note:</strong> Debit sales are automatically tracked from the POS system. Only count the physical cash in the drawer.
                        </div>
                      </div>
                    </div>

                    {/* Currency Count - Sorted Smallest to Largest */}
                    <div>
                      <h4 className="font-semibold mb-3 text-gray-800">💵 Currency Count (Smallest to Largest)</h4>

                      {/* Coins First - Smallest to Largest */}
                      <div className="mb-4">
                        <h5 className="font-medium text-sm text-gray-700 mb-2">Coins</h5>
                        <div className="grid grid-cols-4 gap-3">
                          <div>
                            <Label htmlFor="pennies">1¢ (Pennies)</Label>
                            <Input
                              id="pennies"
                              type="number"
                              min="0"
                              value={denominations.pennies}
                              onChange={(e) => setDenominations(prev => ({...prev, pennies: parseInt(e.target.value) || 0}))}
                              placeholder="0"
                            />
                            <div className="text-xs text-muted-foreground mt-1">
                              ${(denominations.pennies * 0.01).toFixed(2)}
                            </div>
                          </div>
                          <div>
                            <Label htmlFor="nickels">5¢ (Nickels)</Label>
                            <Input
                              id="nickels"
                              type="number"
                              min="0"
                              value={denominations.nickels}
                              onChange={(e) => setDenominations(prev => ({...prev, nickels: parseInt(e.target.value) || 0}))}
                              placeholder="0"
                            />
                            <div className="text-xs text-muted-foreground mt-1">
                              ${(denominations.nickels * 0.05).toFixed(2)}
                            </div>
                          </div>
                          <div>
                            <Label htmlFor="dimes">10¢ (Dimes)</Label>
                            <Input
                              id="dimes"
                              type="number"
                              min="0"
                              value={denominations.dimes}
                              onChange={(e) => setDenominations(prev => ({...prev, dimes: parseInt(e.target.value) || 0}))}
                              placeholder="0"
                            />
                            <div className="text-xs text-muted-foreground mt-1">
                              ${(denominations.dimes * 0.10).toFixed(2)}
                            </div>
                          </div>
                          <div>
                            <Label htmlFor="quarters">25¢ (Quarters)</Label>
                            <Input
                              id="quarters"
                              type="number"
                              min="0"
                              value={denominations.quarters}
                              onChange={(e) => setDenominations(prev => ({...prev, quarters: parseInt(e.target.value) || 0}))}
                              placeholder="0"
                            />
                            <div className="text-xs text-muted-foreground mt-1">
                              ${(denominations.quarters * 0.25).toFixed(2)}
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* Bills - Smallest to Largest */}
                      <div>
                        <h5 className="font-medium text-sm text-gray-700 mb-2">Bills</h5>
                        <div className="grid grid-cols-3 gap-3">
                          <div>
                            <Label htmlFor="ones">$1 Bills</Label>
                            <Input
                              id="ones"
                              type="number"
                              min="0"
                              value={denominations.ones}
                              onChange={(e) => setDenominations(prev => ({...prev, ones: parseInt(e.target.value) || 0}))}
                              placeholder="0"
                            />
                            <div className="text-xs text-muted-foreground mt-1">
                              ${(denominations.ones * 1).toFixed(2)}
                            </div>
                          </div>
                          <div>
                            <Label htmlFor="fives">$5 Bills</Label>
                            <Input
                              id="fives"
                              type="number"
                              min="0"
                              value={denominations.fives}
                            onChange={(e) => setDenominations(prev => ({...prev, fives: parseInt(e.target.value) || 0}))}
                            placeholder="0"
                          />
                          <div className="text-xs text-muted-foreground mt-1">
                            ${(denominations.fives * 5).toFixed(2)}
                          </div>
                        </div>
                        <div>
                          <Label htmlFor="tens">$10 Bills</Label>
                          <Input
                            id="tens"
                            type="number"
                            min="0"
                            value={denominations.tens}
                            onChange={(e) => setDenominations(prev => ({...prev, tens: parseInt(e.target.value) || 0}))}
                            placeholder="0"
                          />
                          <div className="text-xs text-muted-foreground mt-1">
                            ${(denominations.tens * 10).toFixed(2)}
                          </div>
                        </div>
                        <div>
                          <Label htmlFor="twenties">$20 Bills</Label>
                          <Input
                            id="twenties"
                            type="number"
                            min="0"
                            value={denominations.twenties}
                            onChange={(e) => setDenominations(prev => ({...prev, twenties: parseInt(e.target.value) || 0}))}
                            placeholder="0"
                          />
                          <div className="text-xs text-muted-foreground mt-1">
                            ${(denominations.twenties * 20).toFixed(2)}
                          </div>
                        </div>
                        <div>
                          <Label htmlFor="fifties">$50 Bills</Label>
                          <Input
                            id="fifties"
                            type="number"
                            min="0"
                            value={denominations.fifties}
                            onChange={(e) => setDenominations(prev => ({...prev, fifties: parseInt(e.target.value) || 0}))}
                            placeholder="0"
                          />
                          <div className="text-xs text-muted-foreground mt-1">
                            ${(denominations.fifties * 50).toFixed(2)}
                          </div>
                        </div>
                        <div>
                          <Label htmlFor="hundreds">$100 Bills</Label>
                          <Input
                            id="hundreds"
                            type="number"
                            min="0"
                            value={denominations.hundreds}
                            onChange={(e) => setDenominations(prev => ({...prev, hundreds: parseInt(e.target.value) || 0}))}
                            placeholder="0"
                          />
                          <div className="text-xs text-muted-foreground mt-1">
                            ${(denominations.hundreds * 100).toFixed(2)}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                    {/* Sales Breakdown */}
                    <div>
                      <h4 className="font-semibold mb-3">Sales Breakdown (Optional)</h4>
                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <Label htmlFor="cash-sales">Cash Sales</Label>
                          <Input
                            id="cash-sales"
                            type="number"
                            step="0.01"
                            value={cashSales}
                            onChange={(e) => setCashSales(e.target.value)}
                            placeholder="0.00"
                          />
                        </div>
                        <div>
                          <Label htmlFor="debit-sales">Debit Sales</Label>
                          <Input
                            id="debit-sales"
                            type="number"
                            step="0.01"
                            value={debitSales}
                            onChange={(e) => setDebitSales(e.target.value)}
                            placeholder="0.00"
                          />
                        </div>
                      </div>
                    </div>

                    {/* Totals Summary */}
                    <div className="p-4 bg-blue-50 rounded-lg">
                      <h4 className="font-semibold mb-3">Count Summary</h4>
                      <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                          <div className="flex justify-between">
                            <span>Physical Cash Count:</span>
                            <span className="font-semibold">${denominationTotal.toFixed(2)}</span>
                          </div>
                          {cashSales && (
                            <div className="flex justify-between">
                              <span>Cash Sales:</span>
                              <span className="font-semibold">${cashTotal.toFixed(2)}</span>
                            </div>
                          )}
                          {debitSales && (
                            <div className="flex justify-between">
                              <span>Debit Sales:</span>
                              <span className="font-semibold">${debitTotal.toFixed(2)}</span>
                            </div>
                          )}
                        </div>
                        <div>
                          <div className="flex justify-between">
                            <span>Expected:</span>
                            <span className="font-semibold">${drawer.expectedValue?.toFixed(2) || '0.00'}</span>
                          </div>
                          <div className="flex justify-between">
                            <span>Variance:</span>
                            <span className={`font-semibold ${
                              denominationTotal - (drawer.expectedValue || 0) > 0 ? 'text-green-600' :
                              denominationTotal - (drawer.expectedValue || 0) < 0 ? 'text-red-600' : 'text-gray-600'
                            }`}>
                              ${(denominationTotal - (drawer.expectedValue || 0)).toFixed(2)}
                              {denominationTotal - (drawer.expectedValue || 0) > 0 ? ' (OVER)' :
                               denominationTotal - (drawer.expectedValue || 0) < 0 ? ' (UNDER)' : ' (EXACT)'}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div className="flex gap-2">
                      <Button
                        onClick={() => {
                          countDrawer(selectedDrawerForCount.roomId, selectedDrawerForCount.drawerId, denominationTotal);
                        }}
                        className="flex-1"
                        disabled={denominationTotal === 0}
                      >
                        Save Count - ${denominationTotal.toFixed(2)}
                      </Button>
                      <Button
                        variant="outline"
                        onClick={() => {
                          setShowCountDialog(false);
                          resetCountingForm();
                        }}
                        className="flex-1"
                      >
                        Cancel
                      </Button>
                    </div>
                  </>
                ) : null;
              })()}
            </div>
          </DialogContent>
        </Dialog>

        {/* Beginning Balance Dialog */}
        <Dialog open={showBeginningBalanceDialog} onOpenChange={setShowBeginningBalanceDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Set Beginning Balance</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <p className="text-sm text-muted-foreground">
                Enter the beginning cash balance for this drawer to start your shift.
              </p>
              <div>
                <Label htmlFor="beginning-balance">Beginning Balance ($)</Label>
                <Input
                  id="beginning-balance"
                  type="number"
                  step="0.01"
                  min="0"
                  value={beginningBalance}
                  onChange={(e) => setBeginningBalance(e.target.value)}
                  placeholder="0.00"
                  autoFocus
                />
              </div>
              <div className="flex gap-2">
                <Button onClick={openDrawerWithBalance} className="flex-1">
                  Open Drawer
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowBeginningBalanceDialog(false);
                    setDrawerToOpen(null);
                    setBeginningBalance("");
                  }}
                  className="flex-1"
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Edit Drawer Dialog */}
        <Dialog open={showEditDrawerDialog} onOpenChange={setShowEditDrawerDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Drawer - {drawerToEdit?.drawer.name}</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="edit-drawer-name">Drawer Name</Label>
                <Input
                  id="edit-drawer-name"
                  value={editDrawerForm.name}
                  onChange={(e) => setEditDrawerForm(prev => ({...prev, name: e.target.value}))}
                  placeholder="Enter drawer name"
                />
              </div>
              <div>
                <Label htmlFor="edit-drawer-type">Drawer Type</Label>
                <Select
                  value={editDrawerForm.type}
                  onValueChange={(value) => setEditDrawerForm(prev => ({...prev, type: value}))}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select drawer type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="cash/debit">Cash/Debit</SelectItem>
                    <SelectItem value="product">Product</SelectItem>
                    <SelectItem value="supplies">Supplies</SelectItem>
                    <SelectItem value="documents">Documents</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label htmlFor="edit-assigned-employee">Assigned Employee</Label>
                <Input
                  id="edit-assigned-employee"
                  value={editDrawerForm.assignedEmployee}
                  onChange={(e) => setEditDrawerForm(prev => ({...prev, assignedEmployee: e.target.value}))}
                  placeholder="Employee name"
                />
              </div>
              {editDrawerForm.type === 'cash/debit' && (
                <div>
                  <Label htmlFor="edit-drop-notification">Drop Notification Amount ($)</Label>
                  <Input
                    id="edit-drop-notification"
                    type="number"
                    step="0.01"
                    value={editDrawerForm.dropNotificationAmount}
                    onChange={(e) => setEditDrawerForm(prev => ({...prev, dropNotificationAmount: e.target.value}))}
                    placeholder="Amount to trigger drop notification (e.g., 500.00)"
                  />
                  <p className="text-xs text-gray-500 mt-1">
                    Notification will be sent when cash exceeds this amount
                  </p>
                </div>
              )}
              <div className="flex gap-2">
                <Button onClick={updateDrawer} className="flex-1">
                  Update Drawer
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowEditDrawerDialog(false);
                    setDrawerToEdit(null);
                  }}
                  className="flex-1"
                >
                  Cancel
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        {/* Edit Room Dialog */}
        <Dialog open={showEditRoomDialog} onOpenChange={setShowEditRoomDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Room - {roomToEdit?.name}</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="edit-room-name">Room Name</Label>
                <Input
                  id="edit-room-name"
                  value={editRoomForm.name}
                  onChange={(e) => setEditRoomForm(prev => ({...prev, name: e.target.value}))}
                  placeholder="Enter room name"
                />
              </div>
              <div>
                <Label htmlFor="edit-room-type">Room Type</Label>
                <Select
                  value={editRoomForm.type}
                  onValueChange={(value) => setEditRoomForm(prev => ({...prev, type: value}))}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select room type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="storage">Storage</SelectItem>
                    <SelectItem value="cultivation">Cultivation</SelectItem>
                    <SelectItem value="processing">Processing</SelectItem>
                    <SelectItem value="for sale">For Sale</SelectItem>
                    <SelectItem value="hold safe">Hold Safe</SelectItem>
                    <SelectItem value="back room">Back Room</SelectItem>
                    <SelectItem value="hold fix">Hold Fix</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label htmlFor="edit-capacity">Capacity</Label>
                <Input
                  id="edit-capacity"
                  type="number"
                  value={editRoomForm.capacity}
                  onChange={(e) => setEditRoomForm(prev => ({...prev, capacity: e.target.value}))}
                  placeholder="Enter capacity"
                />
              </div>
              <div>
                <Label htmlFor="edit-security-level">Security Level</Label>
                <Select
                  value={editRoomForm.securityLevel}
                  onValueChange={(value) => setEditRoomForm(prev => ({...prev, securityLevel: value}))}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select security level" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="low">Low</SelectItem>
                    <SelectItem value="medium">Medium</SelectItem>
                    <SelectItem value="high">High</SelectItem>
                    <SelectItem value="maximum">Maximum</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="flex gap-2">
                <Button onClick={updateRoom} className="flex-1">
                  Update Room
                </Button>
                <Button
                  variant="outline"
                  onClick={() => {
                    setShowEditRoomDialog(false);
                    setRoomToEdit(null);
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
    </div>
  );
}
