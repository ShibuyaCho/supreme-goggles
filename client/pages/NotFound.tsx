import { useLocation, useNavigate } from "react-router-dom";
import { useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Home, BarChart3, Users, FileText, Package, Clock, Settings } from "lucide-react";

const NotFound = () => {
  const location = useLocation();
  const navigate = useNavigate();

  useEffect(() => {
    console.error(
      "404 Error: User attempted to access non-existent route:",
      location.pathname,
    );
  }, [location.pathname]);

  const navigationOptions = [
    { path: "/", icon: Home, label: "Cashier (POS)", description: "Sales and checkout" },
    { path: "/queue", icon: Clock, label: "Order Queue", description: "Manage pending orders" },
    { path: "/analytics", icon: BarChart3, label: "Analytics", description: "Sales reports and metrics" },
    { path: "/employees", icon: Users, label: "Employees", description: "Staff management" },
    { path: "/reports", icon: FileText, label: "Reports", description: "Custom reporting" },
    { path: "/rooms", icon: Package, label: "Rooms & Drawers", description: "Facility management" },
    { path: "/settings", icon: Settings, label: "Settings", description: "Store configuration" }
  ];

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-6">
      <Card className="w-full max-w-2xl">
        <CardHeader className="text-center">
          <CardTitle className="text-4xl font-bold text-red-600 mb-2">404</CardTitle>
          <h2 className="text-2xl font-semibold mb-2">Page Not Found</h2>
          <p className="text-gray-600">
            The page "{location.pathname}" doesn't exist in Cannabest POS.
          </p>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="text-center mb-6">
            <p className="text-gray-700 mb-4">Navigate to any of these available sections:</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            {navigationOptions.map((option) => {
              const IconComponent = option.icon;
              return (
                <Button
                  key={option.path}
                  variant="outline"
                  className="h-auto p-4 flex flex-col items-start text-left"
                  onClick={() => navigate(option.path)}
                >
                  <div className="flex items-center gap-2 mb-1">
                    <IconComponent className="w-4 h-4" />
                    <span className="font-medium">{option.label}</span>
                  </div>
                  <span className="text-sm text-gray-600">{option.description}</span>
                </Button>
              );
            })}
          </div>

          <div className="text-center pt-4 border-t">
            <Button onClick={() => navigate("/")} size="lg">
              <Home className="w-4 h-4 mr-2" />
              Return to Main POS
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default NotFound;
