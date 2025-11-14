<?php

namespace App\Services;

/**
 * LBC Shipping Fee Calculator
 * Based on LBC's standard shipping rates
 */
class LbcShippingCalculator
{
    // Weight-based pricing (per kg)
    private const BASE_RATE_PER_KG = 50.00; // Base rate for first kg
    private const ADDITIONAL_KG_RATE = 30.00; // Rate for each additional kg
    
    // Zone-based multipliers
    private const ZONE_METRO_MANILA = 1.0; // Metro Manila
    private const ZONE_LUZON = 1.2; // Luzon (outside Metro Manila)
    private const ZONE_VISAYAS = 1.5; // Visayas
    private const ZONE_MINDANAO = 1.8; // Mindanao
    
    // COD fee (percentage of order amount)
    private const COD_FEE_PERCENTAGE = 0.02; // 2% of order amount
    private const MIN_COD_FEE = 50.00; // Minimum COD fee
    private const MAX_COD_FEE = 500.00; // Maximum COD fee
    
    // Minimum shipping fee
    private const MIN_SHIPPING_FEE = 100.00;
    
    /**
     * Calculate shipping fee based on weight and destination
     */
    public static function calculateShippingFee(float $weightKg, string $province, string $city = ''): float
    {
        // Determine zone based on province/city
        $zone = self::determineZone($province, $city);
        
        // Calculate base shipping fee
        $shippingFee = self::BASE_RATE_PER_KG;
        
        // Add additional weight charges
        if ($weightKg > 1) {
            $additionalWeight = ceil($weightKg - 1); // Round up to nearest kg
            $shippingFee += ($additionalWeight * self::ADDITIONAL_KG_RATE);
        }
        
        // Apply zone multiplier
        $shippingFee *= $zone;
        
        // Ensure minimum shipping fee
        $shippingFee = max($shippingFee, self::MIN_SHIPPING_FEE);
        
        return round($shippingFee, 2);
    }
    
    /**
     * Calculate COD fee based on order amount
     */
    public static function calculateCodFee(float $orderAmount): float
    {
        $codFee = $orderAmount * self::COD_FEE_PERCENTAGE;
        
        // Apply min and max limits
        $codFee = max($codFee, self::MIN_COD_FEE);
        $codFee = min($codFee, self::MAX_COD_FEE);
        
        return round($codFee, 2);
    }
    
    /**
     * Calculate total COD amount (order + shipping + COD fee)
     */
    public static function calculateTotalCodAmount(float $orderAmount, float $weightKg, string $province, string $city = ''): array
    {
        $shippingFee = self::calculateShippingFee($weightKg, $province, $city);
        $codFee = self::calculateCodFee($orderAmount);
        $total = $orderAmount + $shippingFee + $codFee;
        
        return [
            'order_amount' => round($orderAmount, 2),
            'shipping_fee' => $shippingFee,
            'cod_fee' => $codFee,
            'total' => round($total, 2),
        ];
    }
    
    /**
     * Determine shipping zone based on province and city
     */
    private static function determineZone(string $province, string $city = ''): float
    {
        $province = strtolower(trim($province));
        $city = strtolower(trim($city));
        
        // Metro Manila cities
        $metroManilaCities = [
            'manila', 'makati', 'quezon city', 'pasig', 'mandaluyong', 'san juan',
            'taguig', 'pasay', 'paranaque', 'las pinas', 'muntinlupa', 'valenzuela',
            'caloocan', 'malabon', 'navotas', 'marikina', 'pateros'
        ];
        
        // Check if it's Metro Manila
        if (in_array($city, $metroManilaCities) || strpos($province, 'metro manila') !== false || strpos($province, 'ncr') !== false) {
            return self::ZONE_METRO_MANILA;
        }
        
        // Luzon provinces (outside Metro Manila)
        $luzonProvinces = [
            'bulacan', 'cavite', 'laguna', 'rizal', 'pampanga', 'bataan', 'zambales',
            'tarlac', 'nueva ecija', 'pangasinan', 'la union', 'ilocos', 'benguet',
            'batanes', 'cagayan', 'isabela', 'nueva vizcaya', 'quirino', 'aurora',
            'batangas', 'quezon', 'camarines', 'albay', 'sorsogon', 'masbate',
            'catanduanes', 'marinduque', 'romblon', 'palawan', 'mindoro'
        ];
        
        foreach ($luzonProvinces as $luzonProvince) {
            if (strpos($province, $luzonProvince) !== false) {
                return self::ZONE_LUZON;
            }
        }
        
        // Visayas provinces
        $visayasProvinces = [
            'cebu', 'bohol', 'leyte', 'samar', 'negros', 'iloilo', 'capiz',
            'aklan', 'antique', 'guimaras', 'siquijor', 'biliran', 'eastern samar',
            'northern samar', 'western samar', 'southern leyte'
        ];
        
        foreach ($visayasProvinces as $visayasProvince) {
            if (strpos($province, $visayasProvince) !== false) {
                return self::ZONE_VISAYAS;
            }
        }
        
        // Mindanao provinces
        $mindanaoProvinces = [
            'davao', 'cagayan de oro', 'zamboanga', 'cotabato', 'sarangani',
            'south cotabato', 'north cotabato', 'bukidnon', 'misamis', 'agusan',
            'surigao', 'lanao', 'maguindanao', 'sulu', 'tawi-tawi', 'basilan',
            'compostela valley', 'davao oriental', 'davao del sur', 'davao del norte',
            'davao occidental'
        ];
        
        foreach ($mindanaoProvinces as $mindanaoProvince) {
            if (strpos($province, $mindanaoProvince) !== false) {
                return self::ZONE_MINDANAO;
            }
        }
        
        // Default to Luzon if not found
        return self::ZONE_LUZON;
    }
    
    /**
     * Estimate weight based on order items
     */
    public static function estimateWeight($items): float
    {
        // Default weight per item (in kg)
        $defaultWeightPerItem = 0.5;
        
        $totalWeight = 0;
        foreach ($items as $item) {
            $quantity = $item->quantity ?? 1;
            $itemWeight = $item->item->weight ?? $defaultWeightPerItem;
            $totalWeight += ($itemWeight * $quantity);
        }
        
        // Minimum weight of 0.5kg
        return max($totalWeight, 0.5);
    }
}

