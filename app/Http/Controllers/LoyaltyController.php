<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LoyaltyController extends Controller
{
    private $tierThresholds = [
        'Bronze' => 0,
        'Silver' => 500,
        'Gold' => 1500,
        'Platinum' => 3000
    ];

    private $tierMultipliers = [
        'Bronze' => 1,
        'Silver' => 2,
        'Gold' => 3,
        'Platinum' => 5
    ];

    public function index()
    {
        $loyaltyMembers = Customer::whereNotNull('loyalty_member_id')
                                ->with(['loyaltyTransactions' => function($query) {
                                    $query->orderBy('created_at', 'desc')->take(5);
                                }])
                                ->orderBy('loyalty_points', 'desc')
                                ->get();

        $stats = $this->calculateLoyaltyStats($loyaltyMembers);

        return view('loyalty.index', compact('loyaltyMembers', 'stats'));
    }

    public function enroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers,email',
            'is_veteran' => 'boolean',
            'data_retention_consent' => 'required|boolean|accepted'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'is_veteran' => $request->is_veteran ?? false,
                'data_retention_consent' => $request->data_retention_consent,
                'loyalty_member_id' => $this->generateLoyaltyMemberId(),
                'loyalty_points' => 0,
                'points_earned' => 0,
                'points_redeemed' => 0,
                'total_spent' => 0,
                'total_visits' => 0,
                'tier' => 'Bronze',
                'join_date' => now(),
                'last_visit' => null
            ]);

            // Log enrollment
            Log::info('Customer enrolled in loyalty program', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'is_veteran' => $customer->is_veteran,
                'enrolled_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer enrolled successfully',
                'customer' => $customer
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error enrolling customer in loyalty program', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adjustPoints(Request $request, $customerId)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1',
            'type' => 'required|in:earned,redeemed,adjustment',
            'reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = Customer::findOrFail($customerId);

            DB::transaction(function() use ($customer, $request) {
                // Create loyalty transaction record
                LoyaltyTransaction::create([
                    'customer_id' => $customer->id,
                    'points' => abs($request->points),
                    'type' => $request->type,
                    'reason' => $request->reason,
                    'created_by' => auth()->id()
                ]);

                // Update customer points
                if ($request->type === 'redeemed') {
                    $customer->decrement('loyalty_points', abs($request->points));
                    $customer->increment('points_redeemed', abs($request->points));
                } else {
                    $customer->increment('loyalty_points', abs($request->points));
                    $customer->increment('points_earned', abs($request->points));
                }

                // Update tier if necessary
                $this->updateCustomerTier($customer);
            });

            Log::info('Loyalty points adjusted', [
                'customer_id' => $customer->id,
                'points' => $request->points,
                'type' => $request->type,
                'reason' => $request->reason,
                'adjusted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Points adjusted successfully',
                'customer' => $customer->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error adjusting loyalty points', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust points: ' . $e->getMessage()
            ], 500);
        }
    }

    public function earnPoints(Request $request, $customerId)
    {
        $validator = Validator::make($request->all(), [
            'purchase_amount' => 'required|numeric|min:0',
            'sale_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = Customer::findOrFail($customerId);

            if (!$customer->loyalty_member_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is not enrolled in loyalty program'
                ], 400);
            }

            // Calculate points based on tier
            $tier = $customer->tier ?? 'Bronze';
            $multiplier = $this->tierMultipliers[$tier] ?? 1;
            $pointsEarned = floor($request->purchase_amount * ($multiplier / 100) * 100);

            DB::transaction(function() use ($customer, $request, $pointsEarned) {
                // Update customer totals
                $customer->increment('loyalty_points', $pointsEarned);
                $customer->increment('points_earned', $pointsEarned);
                $customer->increment('total_spent', $request->purchase_amount);
                $customer->increment('total_visits');
                $customer->update(['last_visit' => now()]);

                // Create transaction record
                LoyaltyTransaction::create([
                    'customer_id' => $customer->id,
                    'points' => $pointsEarned,
                    'type' => 'earned',
                    'reason' => 'Purchase - Sale #' . ($request->sale_id ?? 'Manual'),
                    'sale_id' => $request->sale_id,
                    'created_by' => auth()->id()
                ]);

                // Update tier if necessary
                $this->updateCustomerTier($customer);
            });

            Log::info('Loyalty points earned', [
                'customer_id' => $customer->id,
                'points_earned' => $pointsEarned,
                'purchase_amount' => $request->purchase_amount,
                'sale_id' => $request->sale_id
            ]);

            return response()->json([
                'success' => true,
                'points_earned' => $pointsEarned,
                'customer' => $customer->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error earning loyalty points', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process points: ' . $e->getMessage()
            ], 500);
        }
    }

    public function redeemPoints(Request $request, $customerId)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1',
            'sale_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = Customer::findOrFail($customerId);

            if ($customer->loyalty_points < $request->points) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient points balance'
                ], 400);
            }

            $discountAmount = $request->points / 100; // 100 points = $1.00

            DB::transaction(function() use ($customer, $request, $discountAmount) {
                // Update customer points
                $customer->decrement('loyalty_points', $request->points);
                $customer->increment('points_redeemed', $request->points);

                // Create transaction record
                LoyaltyTransaction::create([
                    'customer_id' => $customer->id,
                    'points' => $request->points,
                    'type' => 'redeemed',
                    'reason' => 'Discount Applied - Sale #' . ($request->sale_id ?? 'Manual'),
                    'sale_id' => $request->sale_id,
                    'created_by' => auth()->id()
                ]);
            });

            Log::info('Loyalty points redeemed', [
                'customer_id' => $customer->id,
                'points_redeemed' => $request->points,
                'discount_amount' => $discountAmount,
                'sale_id' => $request->sale_id
            ]);

            return response()->json([
                'success' => true,
                'discount_amount' => $discountAmount,
                'customer' => $customer->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error redeeming loyalty points', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to redeem points: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);

            if (!$customer->loyalty_member_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is not a loyalty member'
                ], 400);
            }

            DB::transaction(function() use ($customer) {
                // Delete loyalty transactions
                LoyaltyTransaction::where('customer_id', $customer->id)->delete();

                // Remove loyalty data
                $customer->update([
                    'loyalty_member_id' => null,
                    'loyalty_points' => 0,
                    'points_earned' => 0,
                    'points_redeemed' => 0,
                    'tier' => null
                ]);
            });

            Log::info('Customer removed from loyalty program', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'removed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer removed from loyalty program'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing customer from loyalty program', [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAnalytics()
    {
        try {
            $loyaltyMembers = Customer::whereNotNull('loyalty_member_id')->get();
            $stats = $this->calculateLoyaltyStats($loyaltyMembers);

            return response()->json([
                'success' => true,
                'analytics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching loyalty analytics', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analytics'
            ], 500);
        }
    }

    private function calculateLoyaltyStats($loyaltyMembers)
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'total_members' => $loyaltyMembers->count(),
            'total_points_issued' => $loyaltyMembers->sum('points_earned'),
            'total_points_redeemed' => $loyaltyMembers->sum('points_redeemed'),
            'active_points' => $loyaltyMembers->sum('loyalty_points'),
            'average_spending' => $loyaltyMembers->avg('total_spent'),
            'veteran_count' => $loyaltyMembers->where('is_veteran', true)->count(),
            'active_members' => $loyaltyMembers->where('last_visit', '>=', $thirtyDaysAgo)->count(),
            'tier_distribution' => [
                'Bronze' => $loyaltyMembers->where('tier', 'Bronze')->count(),
                'Silver' => $loyaltyMembers->where('tier', 'Silver')->count(),
                'Gold' => $loyaltyMembers->where('tier', 'Gold')->count(),
                'Platinum' => $loyaltyMembers->where('tier', 'Platinum')->count()
            ],
            'redemption_rate' => $loyaltyMembers->sum('points_earned') > 0
                ? ($loyaltyMembers->sum('points_redeemed') / $loyaltyMembers->sum('points_earned')) * 100
                : 0
        ];
    }

    private function updateCustomerTier($customer)
    {
        $totalSpent = $customer->total_spent;
        $newTier = 'Bronze';

        foreach (array_reverse($this->tierThresholds, true) as $tier => $threshold) {
            if ($totalSpent >= $threshold) {
                $newTier = $tier;
                break;
            }
        }

        if ($customer->tier !== $newTier) {
            $oldTier = $customer->tier;
            $customer->update(['tier' => $newTier]);

            Log::info('Customer tier updated', [
                'customer_id' => $customer->id,
                'old_tier' => $oldTier,
                'new_tier' => $newTier,
                'total_spent' => $totalSpent
            ]);
        }
    }

    private function generateLoyaltyMemberId()
    {
        do {
            $memberId = 'LM' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Customer::where('loyalty_member_id', $memberId)->exists());

        return $memberId;
    }
}
