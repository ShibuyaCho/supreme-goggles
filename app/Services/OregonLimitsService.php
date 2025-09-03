<?php

namespace App\Services;

use App\Models\Product;

class OregonLimitsService
{
    // Oregon daily possession limits (in mg for consistency)
    protected $limits = [
        'flower' => 56700, // 56.7g
        'concentrates' => 10000, // 10g
        'edibles' => 454000, // 454g
        'tinctures' => 72000, // 72oz (in ml, assuming 1oz = 1000ml)
        'inhalableCannabinoidsExtracts' => 10000, // 10g
        'topicals' => 454000, // 454g - no specific limit, using general product limit
        'infusedPreRolls' => 28350, // 28.35g (half of flower limit)
        'clones' => 4 // 4 clones max per day
    ];

    public function getLimits()
    {
        return $this->limits;
    }

    public function calculateCurrentUsage($cart)
    {
        $usage = [
            'flower' => 0,
            'concentrates' => 0,
            'edibles' => 0,
            'tinctures' => 0,
            'inhalableCannabinoidsExtracts' => 0,
            'topicals' => 0,
            'infusedPreRolls' => 0,
            'clones' => 0
        ];

        foreach ($cart as $item) {
            $product = is_array($item) ? (object)$item : $item;
            $quantity = $product->quantity ?? 1;
            
            $category = $product->category ?? '';
            $weight = $product->weight ?? '';

            if (in_array($category, ['Flower', 'Pre-Rolls'])) {
                $usage['flower'] += $this->parseWeightToMg($weight) * $quantity;
            } elseif ($category === 'Concentrates') {
                $usage['concentrates'] += $this->parseWeightToMg($weight) * $quantity;
            } elseif ($category === 'Vapes') {
                $usage['inhalableCannabinoidsExtracts'] += $this->parseWeightToMg($weight) * $quantity;
            } elseif ($category === 'Edibles') {
                $usage['edibles'] += $this->parseWeightToMg($weight) * $quantity;
            } elseif ($category === 'Tinctures') {
                $usage['tinctures'] += $this->parseTinctureVolumeToMl($weight) * $quantity;
            } elseif ($category === 'Topicals') {
                $usage['topicals'] += $this->parseWeightToMg($weight) * $quantity;
            } elseif ($category === 'Infused Pre-Rolls') {
                $usage['infusedPreRolls'] += $this->parseWeightToMg($weight) * $quantity;
            } elseif ($category === 'Clones') {
                $usage['clones'] += $quantity;
            }
        }

        return $usage;
    }

    public function wouldExceedLimits(Product $product, $quantity = 1, $customerInfo = [])
    {
        // Get current cart from session or parameter
        $cart = session('cart', []);
        $currentUsage = $this->calculateCurrentUsage($cart);
        
        // Add daily purchases if customer info provided
        if (isset($customerInfo['daily_purchases'])) {
            foreach ($customerInfo['daily_purchases'] as $category => $amount) {
                if (isset($currentUsage[$category])) {
                    $currentUsage[$category] += $amount;
                }
            }
        }

        $category = $product->category;
        $weight = $product->weight;
        $newUsage = $currentUsage;

        // Calculate what would be added
        if (in_array($category, ['Flower', 'Pre-Rolls'])) {
            $newUsage['flower'] += $this->parseWeightToMg($weight) * $quantity;
        } elseif ($category === 'Concentrates') {
            $newUsage['concentrates'] += $this->parseWeightToMg($weight) * $quantity;
        } elseif ($category === 'Vapes') {
            $newUsage['inhalableCannabinoidsExtracts'] += $this->parseWeightToMg($weight) * $quantity;
        } elseif ($category === 'Edibles') {
            $newUsage['edibles'] += $this->parseWeightToMg($weight) * $quantity;
        } elseif ($category === 'Tinctures') {
            $newUsage['tinctures'] += $this->parseTinctureVolumeToMl($weight) * $quantity;
        } elseif ($category === 'Topicals') {
            $newUsage['topicals'] += $this->parseWeightToMg($weight) * $quantity;
        } elseif ($category === 'Infused Pre-Rolls') {
            $newUsage['infusedPreRolls'] += $this->parseWeightToMg($weight) * $quantity;
        } elseif ($category === 'Clones') {
            $newUsage['clones'] += $quantity;
        }

        // Check each limit
        return [
            'flower' => $newUsage['flower'] > $this->limits['flower'],
            'concentrates' => $newUsage['concentrates'] > $this->limits['concentrates'],
            'edibles' => $newUsage['edibles'] > $this->limits['edibles'],
            'tinctures' => $newUsage['tinctures'] > $this->limits['tinctures'],
            'inhalableCannabinoidsExtracts' => $newUsage['inhalableCannabinoidsExtracts'] > $this->limits['inhalableCannabinoidsExtracts'],
            'topicals' => $newUsage['topicals'] > $this->limits['topicals'],
            'infusedPreRolls' => $newUsage['infusedPreRolls'] > $this->limits['infusedPreRolls'],
            'clones' => $newUsage['clones'] > $this->limits['clones']
        ];
    }

    public function canAddProduct(Product $product, $customerType = 'recreational', $quantity = 1)
    {
        // Medical customers have different limits (usually higher)
        if ($customerType === 'medical') {
            return true; // For now, assume medical customers can add anything
        }

        $violations = $this->wouldExceedLimits($product, $quantity);
        return !collect($violations)->contains(true);
    }

    public function parseWeightToMg($weight)
    {
        if (!$weight) return 0;
        
        $num = (float) preg_replace('/[^\d.]/', '', $weight);
        
        if (stripos($weight, 'g') !== false && stripos($weight, 'mg') === false) {
            return $num * 1000; // convert grams to mg
        }
        
        return $num; // assume mg
    }

    public function parseTinctureVolumeToMl($weight)
    {
        if (!$weight) return 0;
        
        $num = (float) preg_replace('/[^\d.]/', '', $weight);
        
        if (stripos($weight, 'ml') !== false) {
            return $num;
        }
        if (stripos($weight, 'oz') !== false) {
            return $num * 29.5735; // convert oz to ml
        }
        
        return $num; // assume ml
    }

    public function getRemainingLimits($cart, $customerInfo = [])
    {
        $currentUsage = $this->calculateCurrentUsage($cart);
        
        // Add daily purchases if provided
        if (isset($customerInfo['daily_purchases'])) {
            foreach ($customerInfo['daily_purchases'] as $category => $amount) {
                if (isset($currentUsage[$category])) {
                    $currentUsage[$category] += $amount;
                }
            }
        }

        $remaining = [];
        foreach ($this->limits as $category => $limit) {
            $remaining[$category] = max(0, $limit - ($currentUsage[$category] ?? 0));
        }

        return $remaining;
    }

    public function formatWeight($mg, $category = null)
    {
        if ($category === 'tinctures') {
            $ml = $mg; // For tinctures, we store in ml
            if ($ml >= 1000) {
                return number_format($ml / 1000, 2) . 'L';
            }
            return number_format($ml, 1) . 'ml';
        }

        if ($mg >= 1000) {
            return number_format($mg / 1000, 2) . 'g';
        }
        return number_format($mg, 0) . 'mg';
    }

    public function getLimitStatus($cart, $customerInfo = [])
    {
        $currentUsage = $this->calculateCurrentUsage($cart);
        $remaining = $this->getRemainingLimits($cart, $customerInfo);
        
        $status = [];
        foreach ($this->limits as $category => $limit) {
            $used = $currentUsage[$category] ?? 0;
            $percentage = $limit > 0 ? ($used / $limit) * 100 : 0;
            
            $status[$category] = [
                'used' => $used,
                'limit' => $limit,
                'remaining' => $remaining[$category],
                'percentage' => $percentage,
                'formatted_used' => $this->formatWeight($used, $category),
                'formatted_limit' => $this->formatWeight($limit, $category),
                'formatted_remaining' => $this->formatWeight($remaining[$category], $category),
                'status' => $percentage >= 100 ? 'exceeded' : ($percentage >= 80 ? 'warning' : 'ok')
            ];
        }

        return $status;
    }
}
