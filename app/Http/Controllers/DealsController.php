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
    public function index()
    {
        $deals = Deal::with(['categories', 'usageHistory'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('deals.index', compact('deals'));
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
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'applicable_categories' => 'nullable|array',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'minimum_purchase_type' => 'nullable|in:dollars,grams',
            'max_uses' => 'nullable|integer|min:1',
            'email_customers' => 'boolean',
            'loyalty_only' => 'boolean',
            'medical_only' => 'boolean',
            'is_active' => 'boolean'
        ]);

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

            // Convert applicable_categories array to JSON if present
            if (isset($dealData['applicable_categories'])) {
                $dealData['applicable_categories'] = json_encode($dealData['applicable_categories']);
            }

            $deal = Deal::create($dealData);

            // Send email campaign if requested
            if ($request->email_customers) {
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
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'applicable_categories' => 'nullable|array',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'minimum_purchase_type' => 'nullable|in:dollars,grams',
            'max_uses' => 'nullable|integer|min:1',
            'email_customers' => 'boolean',
            'loyalty_only' => 'boolean',
            'medical_only' => 'boolean',
            'is_active' => 'boolean'
        ]);

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

            // Convert applicable_categories array to JSON if present
            if (isset($dealData['applicable_categories'])) {
                $dealData['applicable_categories'] = json_encode($dealData['applicable_categories']);
            }

            $deal->update($dealData);

            // Send email campaign if newly enabled
            if ($request->email_customers && !$deal->getOriginal('email_customers')) {
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

            // Check if deal is active and within date range
            if (!$this->isDealValid($deal)) {
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

        return $dealArray;
    }

    private function isDealValid($deal)
    {
        if (!$deal->is_active) {
            return false;
        }

        $now = now();
        $startDate = \Carbon\Carbon::parse($deal->start_date);
        $endDate = $deal->end_date ? \Carbon\Carbon::parse($deal->end_date) : null;

        if ($now < $startDate) {
            return false;
        }

        if ($endDate && $now > $endDate) {
            return false;
        }

        return true;
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
                // Here you would send the actual email
                // Mail::to($customer->email)->send(new DealNotification($deal, $customer));

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
}
