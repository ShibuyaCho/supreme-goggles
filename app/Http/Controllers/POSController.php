<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SavedSale;
use App\Models\Deal;
use App\Services\OregonLimitsService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class POSController extends Controller
{
    protected $oregonLimits;
    protected $cartService;

    public function __construct(OregonLimitsService $oregonLimits, CartService $cartService)
    {
        $this->oregonLimits = $oregonLimits;
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $selectedCategory = $request->get('category', 'All');
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $searchQuery = $request->get('search', '');
        $showInventoryTab = $request->get('inventory_tab', false);

        // Get products with filtering and sorting
        $productsQuery = Product::query()
            ->search($searchQuery)
            ->byCategory($selectedCategory);

        // Apply room filtering based on tab
        if ($showInventoryTab) {
            $productsQuery->notOnSalesFloor();
        }

        // Apply sorting
        $products = $productsQuery->orderBy($sortBy, $sortOrder)->get();

        // Get cart and other data
        $cart = $this->cartService->getCart();
        $categories = ['All'] + Product::getCategories();
        $savedSales = SavedSale::where('employee_id', Auth::id())->latest()->get();
        $loyaltyCustomers = Customer::loyaltyMembers()->get();
        $currentDeals = Deal::where('is_active', true)
                          ->where('start_date', '<=', today())
                          ->where('end_date', '>=', today())
                          ->get();

        // Get customer info from session
        $customerInfo = Session::get('customer_info', [
            'name' => '',
            'phone' => '',
            'medical_card' => '',
            'caregiver_card' => '',
            'is_verified' => false,
            'is_oregon_resident' => true,
            'daily_purchases' => $this->getDefaultDailyPurchases()
        ]);

        $selectedLoyaltyCustomer = Session::get('selected_loyalty_customer');
        $saleStarted = Session::get('sale_started', false);
        $customerType = Session::get('customer_type', 'rec');

        // Calculate cart totals
        $cartTotals = $this->cartService->calculateTotals($cart, $customerInfo);

        return view('pos.index', compact(
            'products', 'cart', 'categories', 'selectedCategory', 'sortBy', 'sortOrder',
            'searchQuery', 'showInventoryTab', 'savedSales', 'loyaltyCustomers',
            'currentDeals', 'customerInfo', 'selectedLoyaltyCustomer', 'saleStarted',
            'customerType', 'cartTotals'
        ));
    }

    public function newSale(Request $request)
    {
        $request->validate([
            'customer_type' => 'required|in:recreational,medical',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'medical_card_number' => 'required_if:customer_type,medical|string|max:50',
            'medical_card_issue_date' => 'required_if:customer_type,medical|date',
            'medical_card_expiration_date' => 'required_if:customer_type,medical|date',
            'data_retention_consent' => 'required_if:customer_type,medical|boolean',
        ]);

        // Clear previous sale data
        $this->cartService->clearCart();
        Session::forget(['customer_info', 'selected_loyalty_customer', 'cart_discount']);

        // Set customer info
        $customerInfo = [
            'name' => $request->customer_name ?? '',
            'phone' => $request->customer_phone ?? '',
            'medical_card' => $request->medical_card_number ?? '',
            'caregiver_card' => '',
            'is_verified' => $request->customer_type === 'medical' ? true : (bool)$request->customer_verified,
            'is_oregon_resident' => true,
            'daily_purchases' => $this->getDefaultDailyPurchases()
        ];

        Session::put('customer_info', $customerInfo);
        Session::put('customer_type', $request->customer_type === 'medical' ? 'medical' : 'rec');
        Session::put('sale_started', true);

        // Check for existing loyalty customer
        if ($request->customer_phone) {
            $loyaltyCustomer = Customer::where('phone', $request->customer_phone)->first();
            if ($loyaltyCustomer) {
                Session::put('selected_loyalty_customer', $loyaltyCustomer);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'New sale started successfully',
            'customer_type' => $request->customer_type
        ]);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:1000'
        ]);

        if (!Session::get('sale_started')) {
            return response()->json([
                'success' => false,
                'message' => 'Please start a new sale before adding items to cart.'
            ]);
        }

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        $customerInfo = Session::get('customer_info', []);

        // Check if item can be added to cart
        if (!$product->canAddToCart()) {
            return response()->json([
                'success' => false,
                'message' => "This item cannot be added to cart as it is currently stored in {$product->room}."
            ]);
        }

        // Check Oregon limits
        if ($customerInfo['is_verified'] ?? false) {
            $violations = $this->oregonLimits->wouldExceedLimits($product, $quantity, $customerInfo);
            if (collect($violations)->contains(true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adding this item would exceed Oregon possession limits.'
                ]);
            }
        }

        // Check stock
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock. Available: {$product->stock}"
            ]);
        }

        // Add to cart
        $cart = $this->cartService->addToCart($product, $quantity);
        $cartTotals = $this->cartService->calculateTotals($cart, $customerInfo);

        return response()->json([
            'success' => true,
            'cart_count' => collect($cart)->sum('quantity'),
            'cart_total' => $cartTotals['total'],
            'message' => "{$product->name} added to cart"
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string'
        ]);

        $cart = $this->cartService->removeFromCart($request->product_id);
        $customerInfo = Session::get('customer_info', []);
        $cartTotals = $this->cartService->calculateTotals($cart, $customerInfo);

        return response()->json([
            'success' => true,
            'cart_count' => collect($cart)->sum('quantity'),
            'cart_total' => $cartTotals['total']
        ]);
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
            'quantity' => 'required|numeric|min:0'
        ]);

        $quantity = $request->quantity;
        
        if ($quantity == 0) {
            $cart = $this->cartService->removeFromCart($request->product_id);
        } else {
            $cart = $this->cartService->updateQuantity($request->product_id, $quantity);
        }

        $customerInfo = Session::get('customer_info', []);
        $cartTotals = $this->cartService->calculateTotals($cart, $customerInfo);

        return response()->json([
            'success' => true,
            'cart_count' => collect($cart)->sum('quantity'),
            'cart_total' => $cartTotals['total']
        ]);
    }

    public function clearCart()
    {
        $this->cartService->clearCart();
        
        return response()->json([
            'success' => true,
            'cart_count' => 0,
            'cart_total' => 0
        ]);
    }

    public function applyDiscount(Request $request)
    {
        $request->validate([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'reason_code' => 'required|string|max:255',
            'item_id' => 'nullable|string'
        ]);

        if ($request->item_id) {
            // Apply item-level discount
            $cart = $this->cartService->applyItemDiscount(
                $request->item_id,
                $request->value,
                $request->type,
                $request->reason_code
            );
        } else {
            // Apply cart-level discount
            Session::put('cart_discount', [
                'type' => $request->type,
                'value' => $request->value,
                'label' => $request->type === 'percentage' ? "{$request->value}% off" : "$" . number_format($request->value, 2) . " off",
                'reason_code' => $request->reason_code
            ]);
            $cart = $this->cartService->getCart();
        }

        $customerInfo = Session::get('customer_info', []);
        $cartTotals = $this->cartService->calculateTotals($cart, $customerInfo);

        return response()->json([
            'success' => true,
            'cart_total' => $cartTotals['total'],
            'message' => 'Discount applied successfully'
        ]);
    }

    public function saveSale(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        $cart = $this->cartService->getCart();
        
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot save an empty cart'
            ]);
        }

        $customerInfo = Session::get('customer_info', []);
        $cartTotals = $this->cartService->calculateTotals($cart, $customerInfo);

        $savedSale = SavedSale::create([
            'name' => $request->name,
            'employee_id' => Auth::id(),
            'employee_name' => Auth::user()->name,
            'customer_type' => Session::get('customer_type', 'rec'),
            'customer_info' => $customerInfo,
            'cart_items' => $cart,
            'cart_discount' => Session::get('cart_discount'),
            'selected_loyalty_customer' => Session::get('selected_loyalty_customer'),
            'total_items' => collect($cart)->sum('quantity'),
            'total_amount' => $cartTotals['total'],
            'notes' => $request->notes
        ]);

        // Clear current sale
        $this->cartService->clearCart();
        Session::forget(['customer_info', 'selected_loyalty_customer', 'cart_discount', 'sale_started']);

        return response()->json([
            'success' => true,
            'message' => "Sale '{$savedSale->name}' saved successfully"
        ]);
    }

    public function loadSale($id)
    {
        $savedSale = SavedSale::where('employee_id', Auth::id())->findOrFail($id);

        // Clear current sale
        $this->cartService->clearCart();

        // Load saved sale data
        Session::put('customer_type', $savedSale->customer_type);
        Session::put('customer_info', $savedSale->customer_info);
        Session::put('cart_discount', $savedSale->cart_discount);
        Session::put('selected_loyalty_customer', $savedSale->selected_loyalty_customer);
        Session::put('sale_started', true);

        // Load cart items
        foreach ($savedSale->cart_items as $item) {
            $this->cartService->setCartItem($item['id'], $item);
        }

        return response()->json([
            'success' => true,
            'message' => "Loaded saved sale: '{$savedSale->name}'"
        ]);
    }

    public function savedSales()
    {
        $savedSales = SavedSale::where('employee_id', Auth::id())
                              ->orderBy('created_at', 'desc')
                              ->get();

        return response()->json([
            'success' => true,
            'saved_sales' => $savedSales
        ]);
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,debit,credit',
            'payment_amount' => 'required|numeric|min:0',
            'debit_last_four' => 'required_if:payment_method,debit|string|size:4',
            'employee_pin' => 'required|string|max:10'
        ]);

        $cart = $this->cartService->getCart();
        
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ]);
        }

        $customerInfo = Session::get('customer_info', []);
        $cartTotals = $this->cartService->calculateTotals($cart, $customerInfo);

        // Verify payment amount
        if ($request->payment_amount < $cartTotals['total']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient payment amount'
            ]);
        }

        // Find or create customer
        $customer = null;
        $selectedLoyaltyCustomer = Session::get('selected_loyalty_customer');
        
        if ($selectedLoyaltyCustomer) {
            $customer = Customer::find($selectedLoyaltyCustomer['id']);
        } elseif (!empty($customerInfo['phone'])) {
            $customer = Customer::where('phone', $customerInfo['phone'])->first();
        }

        // Create sale
        $sale = Sale::create([
            'sale_number' => Sale::generateSaleNumber(),
            'customer_id' => $customer?->id,
            'employee_id' => Auth::id(),
            'customer_type' => Session::get('customer_type', 'recreational'),
            'customer_info' => $customerInfo,
            'subtotal' => $cartTotals['subtotal'],
            'tax_amount' => $cartTotals['tax'],
            'discount_amount' => $cartTotals['discount'],
            'total_amount' => $cartTotals['total'],
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->debit_last_four ?? null,
            'cart_items' => $cart,
            'applied_deals' => [], // TODO: implement deal tracking
            'tax_rate' => config('pos.tax_rate', 0.20),
            'notes' => $request->notes ?? null
        ]);

        // Complete the sale
        $sale->complete($request->payment_method, $request->payment_reference);

        // Clear session
        $this->cartService->clearCart();
        Session::forget(['customer_info', 'selected_loyalty_customer', 'cart_discount', 'sale_started']);

        $change = $request->payment_amount - $cartTotals['total'];

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'change' => $change,
            'message' => 'Payment processed successfully'
        ]);
    }

    private function getDefaultDailyPurchases()
    {
        return [
            'flower' => 0,
            'concentrates' => 0,
            'edibles' => 0,
            'tinctures' => 0,
            'inhalableCannabinoidsExtracts' => 0,
            'topicals' => 0,
            'infusedPreRolls' => 0,
            'clones' => 0
        ];
    }
}
