import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Label } from "@/components/ui/label";
import { 
  CreditCard, 
  DollarSign,
  ArrowLeft,
  Check,
  Smartphone,
  Banknote,
  Gift
} from "lucide-react";
import { useNavigate } from "react-router-dom";

interface CartItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
}

interface LoyaltyCustomer {
  id: string;
  name: string;
  email: string;
  phone: string;
  pointsBalance: number;
  tier: string;
}

interface PaymentProps {
  cart: CartItem[];
  total: number;
  loyaltyCustomer?: LoyaltyCustomer;
}

type PaymentMethod = "card" | "cash" | "mobile" | "gift";

export default function Payment() {
  const navigate = useNavigate();
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>("card");
  const [cashAmount, setCashAmount] = useState("");
  const [isProcessing, setIsProcessing] = useState(false);
  const [isComplete, setIsComplete] = useState(false);

  // Mock cart data for demo
  const mockCart: CartItem[] = [
    { id: "1", name: "Blue Dream", price: 7.00, quantity: 2 },
    { id: "2", name: "Gummy Bears", price: 25.00, quantity: 1 },
  ];

  // Mock loyalty customer for demo
  const mockLoyaltyCustomer: LoyaltyCustomer = {
    id: "1",
    name: "John Doe",
    email: "john@example.com",
    phone: "(555) 123-4567",
    pointsBalance: 150,
    tier: "Gold"
  };

  // Mock medical customer for demo (set to null for recreational customer)
  const mockMedicalCustomer = null; // Set to medical card number for medical patients

  const subtotal = mockCart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  // Medical customers are tax exempt in Oregon
  const tax = mockMedicalCustomer ? 0 : subtotal * 0.08;
  const total = subtotal + tax;

  // Calculate points based on loyalty tier: Bronze 1%, Silver 2%, Gold 3%, Platinum 5%
  const getTierPointsMultiplier = (tier: string) => {
    switch (tier) {
      case 'Bronze': return 0.01;
      case 'Silver': return 0.02;
      case 'Gold': return 0.03;
      case 'Platinum': return 0.05;
      default: return 0.01; // Default to Bronze rate
    }
  };

  const pointsEarned = mockLoyaltyCustomer
    ? Math.floor(total * getTierPointsMultiplier(mockLoyaltyCustomer.tier))
    : Math.floor(total * 0.01); // Default 1% for non-loyalty customers

  const handlePayment = async () => {
    setIsProcessing(true);
    
    // Simulate payment processing
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    setIsProcessing(false);
    setIsComplete(true);
  };

  const calculateChange = () => {
    const cash = parseFloat(cashAmount) || 0;
    return Math.max(0, cash - total);
  };

  const printReceipt = () => {
    const receiptWindow = window.open('', '_blank');
    if (!receiptWindow) return;

    const receiptHTML = `
      <!DOCTYPE html>
      <html>
        <head>
          <title>Receipt</title>
          <style>
            body { font-family: monospace; font-size: 14px; margin: 20px; max-width: 400px; }
            .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
            .item { display: flex; justify-content: space-between; margin: 5px 0; }
            .total { border-top: 1px solid #000; padding-top: 10px; margin-top: 10px; font-weight: bold; }
            .loyalty { border-top: 1px dashed #000; padding-top: 10px; margin-top: 10px; background: #f9f9f9; padding: 10px; }
            .footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #000; }
          </style>
        </head>
        <body>
          <div class="header">
            <h2>Cannabis POS Receipt</h2>
            <p>Date: ${new Date().toLocaleDateString()}</p>
            <p>Time: ${new Date().toLocaleTimeString()}</p>
            <p>Transaction #: ${Math.random().toString(36).substr(2, 9).toUpperCase()}</p>
          </div>

          <div class="items">
            ${mockCart.map(item => `
              <div class="item">
                <span>${item.name} x${item.quantity}</span>
                <span>$${(item.price * item.quantity).toFixed(2)}</span>
              </div>
            `).join('')}
          </div>

          <div class="total">
            <div class="item">
              <span>Subtotal:</span>
              <span>$${subtotal.toFixed(2)}</span>
            </div>
            <div class="item">
              <span>Tax (8%):</span>
              <span>$${tax.toFixed(2)}</span>
            </div>
            <div class="item">
              <span>TOTAL:</span>
              <span>$${total.toFixed(2)}</span>
            </div>
          </div>

          ${mockMedicalCustomer ? `
            <div class="loyalty">
              <h3>Medical Patient Information</h3>
              <div class="item">
                <span>Medical Card #:</span>
                <span>${mockMedicalCustomer}</span>
              </div>
              <div class="item">
                <span>Tax Status:</span>
                <span>EXEMPT</span>
              </div>
            </div>
          ` : ''}

          ${mockLoyaltyCustomer ? `
            <div class="loyalty">
              <h3>Loyalty Program</h3>
              <div class="item">
                <span>Customer:</span>
                <span>${mockLoyaltyCustomer.name}</span>
              </div>
              <div class="item">
                <span>Previous Balance:</span>
                <span>${mockLoyaltyCustomer.pointsBalance} points</span>
              </div>
              <div class="item">
                <span>Points Earned:</span>
                <span>+${pointsEarned} points</span>
              </div>
              <div class="item" style="font-weight: bold;">
                <span>New Balance:</span>
                <span>${mockLoyaltyCustomer.pointsBalance + pointsEarned} points</span>
              </div>
              <div class="item">
                <span>Tier Status:</span>
                <span>${mockLoyaltyCustomer.tier}</span>
              </div>
            </div>
          ` : ''}

          <div class="footer">
            <p>Thank you for your business!</p>
            <p>Have a great day!</p>
            ${mockLoyaltyCustomer ? '<p>Keep collecting points for rewards!</p>' : '<p>Ask about our loyalty program!</p>'}
          </div>
        </body>
      </html>
    `;

    receiptWindow.document.write(receiptHTML);
    receiptWindow.document.close();
    receiptWindow.print();
  };

  if (isComplete) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <Card className="w-full max-w-md">
          <CardContent className="p-8 text-center">
            <div className="w-16 h-16 bg-success rounded-full flex items-center justify-center mx-auto mb-4">
              <Check className="w-8 h-8 text-success-foreground" />
            </div>
            <h2 className="text-2xl font-semibold mb-2">Payment Successful!</h2>
            <p className="text-muted-foreground mb-4">Transaction completed successfully</p>
            {mockLoyaltyCustomer && (
              <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div className="text-center space-y-2">
                  <p className="text-green-800 font-medium">
                    🎉 {pointsEarned} loyalty points earned!
                  </p>
                  <p className="text-green-600 text-sm">
                    New balance: {mockLoyaltyCustomer.pointsBalance + pointsEarned} points
                  </p>
                </div>
              </div>
            )}
            <div className="space-y-3">
              <Button className="w-full" onClick={() => navigate("/")}>
                New Transaction
              </Button>
              <Button variant="outline" className="w-full" onClick={() => printReceipt()}>
                Print Receipt
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-pos-header text-pos-header-foreground shadow-sm">
        <div className="px-6 py-4 flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <Button variant="ghost" size="sm" onClick={() => navigate("/")}>
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back to POS
            </Button>
            <h1 className="text-xl font-semibold">Payment Processing</h1>
          </div>
        </div>
      </header>

      <div className="container mx-auto p-6 max-w-4xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Order Summary */}
          <Card>
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3 mb-4">
                {mockCart.map(item => (
                  <div key={item.id} className="flex justify-between">
                    <span>{item.name} x{item.quantity}</span>
                    <span>${(item.price * item.quantity).toFixed(2)}</span>
                  </div>
                ))}
              </div>
              <Separator className="mb-4" />
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span>Subtotal</span>
                  <span>${subtotal.toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Tax (8%)</span>
                  <span>${tax.toFixed(2)}</span>
                </div>
                <Separator />
                <div className="flex justify-between font-semibold text-lg">
                  <span>Total</span>
                  <span>${total.toFixed(2)}</span>
                </div>
                {mockLoyaltyCustomer && (
                  <>
                    <Separator className="my-2" />
                    <div className="space-y-2 text-sm text-muted-foreground">
                      <div className="flex justify-between">
                        <span>Loyalty Customer:</span>
                        <span>{mockLoyaltyCustomer.name}</span>
                      </div>
                      <div className="flex justify-between">
                        <span>Current Points:</span>
                        <span>{mockLoyaltyCustomer.pointsBalance}</span>
                      </div>
                      <div className="flex justify-between font-medium text-foreground">
                        <span>Points Earned:</span>
                        <span>+{pointsEarned}</span>
                      </div>
                    </div>
                  </>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Payment Methods */}
          <Card>
            <CardHeader>
              <CardTitle>Payment Method</CardTitle>
            </CardHeader>
            <CardContent>
              <RadioGroup value={paymentMethod} onValueChange={(value: PaymentMethod) => setPaymentMethod(value)}>
                <div className="space-y-4">
                  <div className="flex items-center space-x-2 p-3 border rounded-lg">
                    <RadioGroupItem value="card" id="card" />
                    <Label htmlFor="card" className="flex items-center space-x-2 cursor-pointer flex-1">
                      <CreditCard className="w-5 h-5" />
                      <span>Credit/Debit Card</span>
                    </Label>
                  </div>

                  <div className="flex items-center space-x-2 p-3 border rounded-lg">
                    <RadioGroupItem value="cash" id="cash" />
                    <Label htmlFor="cash" className="flex items-center space-x-2 cursor-pointer flex-1">
                      <Banknote className="w-5 h-5" />
                      <span>Cash</span>
                    </Label>
                  </div>

                  <div className="flex items-center space-x-2 p-3 border rounded-lg">
                    <RadioGroupItem value="mobile" id="mobile" />
                    <Label htmlFor="mobile" className="flex items-center space-x-2 cursor-pointer flex-1">
                      <Smartphone className="w-5 h-5" />
                      <span>Mobile Payment</span>
                    </Label>
                  </div>

                  <div className="flex items-center space-x-2 p-3 border rounded-lg">
                    <RadioGroupItem value="gift" id="gift" />
                    <Label htmlFor="gift" className="flex items-center space-x-2 cursor-pointer flex-1">
                      <Gift className="w-5 h-5" />
                      <span>Gift Card</span>
                    </Label>
                  </div>
                </div>
              </RadioGroup>

              {/* Payment Method Specific Forms */}
              <div className="mt-6">
                {paymentMethod === "card" && (
                  <div className="space-y-4">
                    <Input placeholder="Card Number" />
                    <div className="grid grid-cols-2 gap-4">
                      <Input placeholder="MM/YY" />
                      <Input placeholder="CVV" />
                    </div>
                    <Input placeholder="Cardholder Name" />
                  </div>
                )}

                {paymentMethod === "cash" && (
                  <div className="space-y-4">
                    <div>
                      <Label htmlFor="cash-amount">Cash Amount Received</Label>
                      <Input
                        id="cash-amount"
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        value={cashAmount}
                        onChange={(e) => setCashAmount(e.target.value)}
                      />
                    </div>
                    {cashAmount && (
                      <div className="p-3 bg-muted rounded-lg">
                        <div className="flex justify-between">
                          <span>Change Due:</span>
                          <span className="font-semibold">${calculateChange().toFixed(2)}</span>
                        </div>
                      </div>
                    )}
                  </div>
                )}

                {paymentMethod === "mobile" && (
                  <div className="text-center py-8">
                    <div className="w-32 h-32 bg-gray-200 mx-auto mb-4 rounded-lg flex items-center justify-center">
                      <span className="text-gray-500">QR Code</span>
                    </div>
                    <p className="text-muted-foreground">Scan with mobile payment app</p>
                  </div>
                )}

                {paymentMethod === "gift" && (
                  <div className="space-y-4">
                    <Input placeholder="Gift Card Number" />
                    <Input placeholder="Security Code" />
                  </div>
                )}
              </div>

              <Button 
                className="w-full mt-6" 
                size="lg"
                onClick={handlePayment}
                disabled={isProcessing || (paymentMethod === "cash" && calculateChange() < 0)}
              >
                {isProcessing ? "Processing..." : `Process Payment - $${total.toFixed(2)}`}
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
