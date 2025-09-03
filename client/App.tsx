import "./global.css";

import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { createRoot } from "react-dom/client";
import Index from "./pages/Index";
import Payment from "./pages/Payment";
import OrderQueue from "./pages/OrderQueue";
import Analytics from "./pages/Analytics";
import Employees from "./pages/Employees";
import Reports from "./pages/Reports";
import RoomsDrawers from "./pages/RoomsDrawers";
import Settings from "./pages/Settings";
import Deals from "./pages/Deals";
import Loyalty from "./pages/Loyalty";
import Sales from "./pages/Sales";
import PriceTiers from "./pages/PriceTiers";
import Customers from "./pages/Customers";
import Products from "./pages/Products";
import InventoryReport from "./pages/InventoryReport";
import NotFound from "./pages/NotFound";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<Index />} />
          <Route path="/payment" element={<Payment />} />
          <Route path="/queue" element={<OrderQueue />} />
          <Route path="/analytics" element={<Analytics />} />
          <Route path="/employees" element={<Employees />} />
          <Route path="/reports" element={<Reports />} />
          <Route path="/rooms" element={<RoomsDrawers />} />
          <Route path="/settings" element={<Settings />} />
          <Route path="/deals" element={<Deals />} />
          <Route path="/loyalty" element={<Loyalty />} />
          <Route path="/sales" element={<Sales />} />
          <Route path="/price-tiers" element={<PriceTiers />} />
          <Route path="/customers" element={<Customers />} />
          <Route path="/products" element={<Products />} />
          <Route path="/inventory-report" element={<InventoryReport />} />
          {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
          <Route path="*" element={<NotFound />} />
        </Routes>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

// Only create root once
const container = document.getElementById("root");
if (container) {
  // Check if root already exists by looking for React's internal marker
  if (!container.hasAttribute('data-reactroot') && !container._reactInternalFiber && !container._reactInternalInstance) {
    const root = createRoot(container);
    root.render(<App />);
  }
}
