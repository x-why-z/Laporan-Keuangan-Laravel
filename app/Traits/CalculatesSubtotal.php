<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Centralized subtotal calculation logic.
 * Used by OrderItem, OrderService, and OrderResource.
 * 
 * Formula:
 * - Unit-based: quantity × unit_price
 * - Area-based: quantity × unit_price × (width × height / 10000) [cm² to m²]
 * - Plus finishing cost if provided
 */
trait CalculatesSubtotal
{
    /**
     * Calculate subtotal for an order item.
     *
     * @param int $quantity Number of items
     * @param float $unitPrice Price per unit
     * @param float|null $width Width in centimeters (for area-based pricing)
     * @param float|null $height Height in centimeters (for area-based pricing)
     * @param float|null $finishingCost Additional finishing cost
     * @return float Calculated subtotal rounded to 2 decimals
     */
    public static function calculateSubtotal(
        int $quantity,
        float $unitPrice,
        ?float $width = null,
        ?float $height = null,
        ?float $finishingCost = null
    ): float {
        $subtotal = (float) $quantity * $unitPrice;

        // Area-based pricing: convert cm² to m²
        if ($width !== null && $height !== null && $width > 0 && $height > 0) {
            $areaCm2 = $width * $height;
            $areaM2 = $areaCm2 / 10000;
            $subtotal = (float) $quantity * $unitPrice * $areaM2;
        }

        // Add finishing cost if provided
        $subtotal += (float) ($finishingCost ?? 0);

        return round($subtotal, 2);
    }

    /**
     * Calculate subtotal from array data (useful for Filament forms).
     *
     * @param array $data Item data with keys: quantity, unit_price, width, height, finishing_cost
     * @return float Calculated subtotal
     */
    public static function calculateSubtotalFromArray(array $data): float
    {
        return self::calculateSubtotal(
            quantity: (int) ($data['quantity'] ?? 0),
            unitPrice: (float) ($data['unit_price'] ?? 0),
            width: isset($data['width']) ? (float) $data['width'] : null,
            height: isset($data['height']) ? (float) $data['height'] : null,
            finishingCost: isset($data['finishing_cost']) ? (float) $data['finishing_cost'] : null
        );
    }
}
