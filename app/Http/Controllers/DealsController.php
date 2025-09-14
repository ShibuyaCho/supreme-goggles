<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DealsController extends Controller
{
    public function index(Request $request)
    {
        $deals = Deal::orderBy('created_at', 'desc')->get();

        // Load METRC categories with safe fallback
        $categories = [];
        try {
            $categories = app(\App\Services\MetrcService::class)->getItemCategories();
            if (is_array($categories) && isset($categories[0]) && is_array($categories[0]) && (isset($categories[0]['Name']) || isset($categories[0]['name']))) {
                $categories = collect($categories)->map(fn($c) => $c['Name'] ?? $c['name'])->values()->all();
            }
        } catch (\Throwable $e) {
            $categories = [
                'Flower','Pre-Rolls','Concentrates','Extracts','Edibles','Topicals','Tinctures','Vape Cartridges','Vape Pens','Inhalable Cannabinoids','Clones','Immature Plants','Seeds','Shake/Trim','Kief','Accessories'
            ];
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'deals' => $deals->map(fn($d) => $this->formatDealForResponse($d))->values()->all(),
                'categories' => $categories,
            ]);
        }

        return view('deals.index', compact('deals','categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_amount,bogo,bulk',
            'value' => 'required|numeric|min:0',
            'frequency' => 'required|in:always,daily,weekly,monthly,custom',
            'day_of_week' => 'nullable|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'applicable_categories' => 'nullable|array',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'minimum_purchase_type' => 'nullable|in:dollars,grams',
            'max_uses' => 'nullable|integer|min:1',
            'email_customers' => 'boolean',
            'loyalty_only' => 'boolean',
            'medical_only' => 'boolean',
            'is_active' => 'boolean',
            'active_days' => 'nullable|array'
        ];

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $dealData = $request->all();
            $dealData['current_uses'] = 0;

            // Normalize optional dates
            foreach (['start_date','end_date'] as $dk) {
                if (isset($dealData[$dk]) && (!$dealData[$dk] || $dealData[$dk] === '')) {
                    $dealData[$dk] = null;
                }
            }

            // Convert applicable_categories array to JSON if present
            if (isset($dealData['applicable_categories']) && is_array($dealData['applicable_categories'])) {
                $dealData['applicable_categories'] = json_encode($dealData['applicable_categories']);
            }
            // Normalize active_days
            if (isset($dealData['active_days']) && is_array($dealData['active_days'])) {
                $dealData['active_days'] = json_encode(array_values(array_unique(array_map('intval', $dealData['active_days']))));
            }

            $deal = Deal::create($dealData);

            // Send email campaign if requested
            if (!empty($deal->email_customers)) {
                $this->sendDealEmailCampaign($deal);
            }

            Log::info('Deal created successfully', [
                'deal_id' => $deal->id,
                'deal_name' => $deal->name,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal created successfully',
                'deal' => $this->formatDealForResponse($deal)
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating deal', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create deal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_amount,bogo,bulk',
            'value' => 'required|numeric|min:0',
            'frequency' => 'required|in:always,daily,weekly,monthly,custom',
            'day_of_week' => 'nullable|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'applicable_categories' => 'nullable|array',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'minimum_purchase_type' => 'nullable|in:dollars,grams',
            'max_uses' => 'nullable|integer|min:1',
            'email_customers' => 'boolean',
            'loyalty_only' => 'boolean',
            'medical_only' => 'boolean',
            'is_active' => 'boolean',
            'active_days' => 'nullable|array'
        ];

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deal = Deal::findOrFail($id);
            $dealData = $request->all();

            // Normalize optional dates
            foreach (['start_date','end_date'] as $dk) {
                if (isset($dealData[$dk]) && (!$dealData[$dk] || $dealData[$dk] === '')) {
                    $dealData[$dk] = null;
                }
            }

            // Convert applicable_categories array to JSON if present
            if (isset($dealData['applicable_categories']) && is_array($dealData['applicable_categories'])) {
                $dealData['applicable_categories'] = json_encode($dealData['applicable_categories']);
            }
            // Normalize active_days
            if (isset($dealData['active_days']) && is_array($dealData['active_days'])) {
                $dealData['active_days'] = json_encode(array_values(array_unique(array_map('intval', $dealData['active_days']))));
            }

            $originalEmail = (bool)$deal->email_customers;
            $deal->update($dealData);

            // Send email campaign if newly enabled
            if ($deal->email_customers && !$originalEmail) {
                $this->sendDealEmailCampaign($deal);
            }

            Log::info('Deal updated successfully', [
                'deal_id' => $deal->id,
                'deal_name' => $deal->name,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal updated successfully',
                'deal' => $this->formatDealForResponse($deal)
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating deal', [
                'deal_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update deal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deal = Deal::findOrFail($id);
            $dealName = $deal->name;

            $deal->delete();

            Log::info('Deal deleted successfully', [
                'deal_id' => $id,
                'deal_name' => $dealName,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting deal', [
                'deal_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete deal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function applyDeal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deal_id' => 'required|exists:deals,id',
            'cart_total' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'categories' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deal = Deal::findOrFail($request->deal_id);

            // Check if deal is active by model rules (dates, usage, frequency)
            if (!$deal->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal is not currently active or has expired'
                ], 400);
            }

            // Check loyalty requirement
            if ($deal->loyalty_only && !$request->customer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This deal is only available to loyalty program members'
                ], 400);
            }

            // Check minimum purchase requirement
            if ($deal->minimum_purchase && $request->cart_total < $deal->minimum_purchase) {
                $type = $deal->minimum_purchase_type === 'grams' ? 'grams' : 'dollars';
                return response()->json([
                    'success' => false,
                    'message' => "Minimum purchase of {$deal->minimum_purchase} {$type} required"
                ], 400);
            }

            // Check usage limits
            if ($deal->max_uses && $deal->current_uses >= $deal->max_uses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal usage limit has been reached'
                ], 400);
            }

            // Calculate discount
            $discount = $this->calculateDiscount($deal, $request->cart_total);

            // Update usage count
            $deal->increment('current_uses');

            // Log deal usage
            $this->logDealUsage($deal, $request->customer_id, $discount);

            return response()->json([
                'success' => true,
                'discount' => $discount,
                'deal' => $this->formatDealForResponse($deal)
            ]);

        } catch (\Exception $e) {
            Log::error('Error applying deal', [
                'deal_id' => $request->deal_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply deal'
            ], 500);
        }
    }

    public function getAnalytics()
    {
        try {
            $analytics = [
                'total_deals' => Deal::count(),
                'active_deals' => Deal::where('is_active', true)->count(),
                'total_usage' => Deal::sum('current_uses'),
                'loyalty_deals' => Deal::where('loyalty_only', true)->count(),
                'medical_deals' => Deal::where('medical_only', true)->count(),
                'email_campaigns' => Deal::where('email_customers', true)->count(),
                'top_deals' => Deal::orderBy('current_uses', 'desc')->take(5)->get(),
                'recent_deals' => Deal::orderBy('created_at', 'desc')->take(10)->get()
            ];

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching deal analytics', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analytics'
            ], 500);
        }
    }

    private function formatDealForResponse($deal)
    {
        $dealArray = $deal->toArray();

        // Decode JSON fields
        if ($dealArray['applicable_categories']) {
            $dealArray['applicable_categories'] = json_decode($dealArray['applicable_categories'], true);
        }
        if (array_key_exists('active_days', $dealArray) && is_string($dealArray['active_days'])) {
            $decoded = json_decode($dealArray['active_days'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $dealArray['active_days'] = $decoded;
            }
        }

        return $dealArray;
    }

    private function isDealValid($deal)
    {
        return $deal->isActive();
    }

    private function calculateDiscount($deal, $cartTotal)
    {
        switch ($deal->type) {
            case 'percentage':
                return $cartTotal * ($deal->value / 100);
            case 'fixed_amount':
                return min($deal->value, $cartTotal);
            case 'bogo':
                // Simplified BOGO calculation
                return $cartTotal * ($deal->value / 200); // Half the percentage discount
            case 'bulk':
                return $cartTotal * ($deal->value / 100);
            default:
                return 0;
        }
    }

    private function logDealUsage($deal, $customerId, $discount)
    {
        // Here you would log the deal usage to a usage history table
        Log::info('Deal applied', [
            'deal_id' => $deal->id,
            'customer_id' => $customerId,
            'discount_amount' => $discount,
            'timestamp' => now()
        ]);
    }

    private function sendDealEmailCampaign($deal)
    {
        // Get loyalty program members
        $loyaltyMembers = Customer::whereNotNull('loyalty_member_id')
                                ->whereNotNull('email')
                                ->get();

        foreach ($loyaltyMembers as $customer) {
            try {
                $payloadDeal = $deal->toArray();
                if (!empty($payloadDeal['applicable_categories']) && is_string($payloadDeal['applicable_categories'])) {
                    $decoded = json_decode($payloadDeal['applicable_categories'], true);
                    if (json_last_error() === JSON_ERROR_NONE) $payloadDeal['applicable_categories'] = $decoded;
                }
                Mail::send('emails.deal-notification', [
                    'deal' => $payloadDeal,
                    'customer' => $customer->toArray(),
                ], function ($message) use ($customer, $deal) {
                    $message->to($customer->email)
                        ->subject(($deal->name ? ($deal->name.' - ') : '').'New Deal at '.(config('app.name','Cannabis POS')))
                        ->from(config('mail.from.address', env('MAIL_FROM_ADDRESS')), config('mail.from.name', env('MAIL_FROM_NAME')));
                });

                Log::info('Deal email sent', [
                    'deal_id' => $deal->id,
                    'customer_id' => $customer->id,
                    'email' => $customer->email
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send deal email', [
                    'deal_id' => $deal->id,
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function sendEmailCampaign($id)
    {
        try {
            $deal = Deal::findOrFail($id);
            $this->sendDealEmailCampaign($deal);
            return response()->json([
                'success' => true,
                'message' => 'Email campaign sent successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error sending deal email campaign', ['deal_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email campaign'
            ], 500);
        }
    }
}
