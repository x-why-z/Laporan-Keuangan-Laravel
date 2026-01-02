<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * OrderService - Centralized business logic for order management.
 * 
 * This service consolidates all order-related calculations and validations
 * following Clean Code XP principles.
 */
class OrderService
{
    /**
     * Create a new order with items.
     *
     * @param array{customer_id: int, order_date: string, due_date?: string, notes?: string} $orderData
     * @param array<int, array{product_id: int, quantity: int, unit_price: float, width?: float, height?: float, specifications?: string}> $items
     * @return Order
     * @throws InvalidArgumentException
     */
    public function createOrder(array $orderData, array $items): Order
    {
        $this->validateOrderItems($items);

        return DB::transaction(function () use ($orderData, $items): Order {
            // Create order
            $order = Order::create([
                'customer_id' => $orderData['customer_id'],
                'order_date' => $orderData['order_date'],
                'due_date' => $orderData['due_date'] ?? null,
                'notes' => $orderData['notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'production_status' => 'pending',
                'total_amount' => 0,
                'paid_amount' => 0,
                'down_payment' => 0,
            ]);

            // Create order items
            $totalAmount = 0.0;
            foreach ($items as $itemData) {
                $subtotal = $this->calculateItemSubtotal(
                    quantity: (int) $itemData['quantity'],
                    unitPrice: (float) $itemData['unit_price'],
                    width: isset($itemData['width']) ? (float) $itemData['width'] : null,
                    height: isset($itemData['height']) ? (float) $itemData['height'] : null
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'width' => $itemData['width'] ?? null,
                    'height' => $itemData['height'] ?? null,
                    'subtotal' => $subtotal,
                    'specifications' => $itemData['specifications'] ?? null,
                ]);

                $totalAmount += $subtotal;
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            return $order->fresh(['items', 'customer']);
        });
    }

    /**
     * Update an existing order.
     *
     * @param Order $order
     * @param array $orderData
     * @param array $items
     * @return Order
     * @throws InvalidArgumentException
     */
    public function updateOrder(Order $order, array $orderData, array $items): Order
    {
        $this->validateOrderItems($items);

        return DB::transaction(function () use ($order, $orderData, $items): Order {
            // Update order fields
            $order->update([
                'customer_id' => $orderData['customer_id'] ?? $order->customer_id,
                'order_date' => $orderData['order_date'] ?? $order->order_date,
                'due_date' => $orderData['due_date'] ?? $order->due_date,
                'notes' => $orderData['notes'] ?? $order->notes,
            ]);

            // Delete existing items and recreate
            $order->items()->delete();

            $totalAmount = 0.0;
            foreach ($items as $itemData) {
                $subtotal = $this->calculateItemSubtotal(
                    quantity: (int) $itemData['quantity'],
                    unitPrice: (float) $itemData['unit_price'],
                    width: isset($itemData['width']) ? (float) $itemData['width'] : null,
                    height: isset($itemData['height']) ? (float) $itemData['height'] : null
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'width' => $itemData['width'] ?? null,
                    'height' => $itemData['height'] ?? null,
                    'subtotal' => $subtotal,
                    'specifications' => $itemData['specifications'] ?? null,
                ]);

                $totalAmount += $subtotal;
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            return $order->fresh(['items', 'customer']);
        });
    }

    /**
     * Calculate subtotal for a single order item.
     * 
     * Formula:
     * - With dimensions: quantity × unit_price × (width × height / 10000) [cm² to m²]
     * - Without dimensions: quantity × unit_price
     *
     * @param int $quantity
     * @param float $unitPrice
     * @param float|null $width Width in centimeters
     * @param float|null $height Height in centimeters
     * @return float
     */
    public function calculateItemSubtotal(
        int $quantity,
        float $unitPrice,
        ?float $width = null,
        ?float $height = null
    ): float {
        $subtotal = (float) $quantity * $unitPrice;

        // If dimensions provided, calculate area-based pricing
        if ($width !== null && $height !== null && $width > 0 && $height > 0) {
            $areaCm2 = $width * $height;
            $areaM2 = $areaCm2 / 10000; // Convert cm² to m²
            $subtotal = (float) $quantity * $unitPrice * $areaM2;
        }

        return round($subtotal, 2);
    }

    /**
     * Calculate total amount for an order.
     *
     * @param Order $order
     * @return float
     */
    public function calculateOrderTotal(Order $order): float
    {
        return (float) $order->items()->sum('subtotal');
    }

    /**
     * Validate order items for arithmetic correctness.
     * Rejects zero or negative values for quantity and dimensions.
     *
     * @param array $items
     * @throws InvalidArgumentException
     */
    public function validateOrderItems(array $items): void
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Order must have at least one item.');
        }

        foreach ($items as $index => $item) {
            $itemNumber = $index + 1;

            // Validate quantity
            if (!isset($item['quantity']) || (int) $item['quantity'] <= 0) {
                throw new InvalidArgumentException(
                    "Item #{$itemNumber}: Quantity must be greater than zero."
                );
            }

            // Validate unit_price
            if (!isset($item['unit_price']) || (float) $item['unit_price'] <= 0) {
                throw new InvalidArgumentException(
                    "Item #{$itemNumber}: Unit price must be greater than zero."
                );
            }

            // Validate dimensions if provided
            if (isset($item['width']) && (float) $item['width'] < 0) {
                throw new InvalidArgumentException(
                    "Item #{$itemNumber}: Width cannot be negative."
                );
            }

            if (isset($item['height']) && (float) $item['height'] < 0) {
                throw new InvalidArgumentException(
                    "Item #{$itemNumber}: Height cannot be negative."
                );
            }

            // If one dimension is provided, both must be provided and positive
            $hasWidth = isset($item['width']) && (float) $item['width'] > 0;
            $hasHeight = isset($item['height']) && (float) $item['height'] > 0;
            
            if ($hasWidth xor $hasHeight) {
                throw new InvalidArgumentException(
                    "Item #{$itemNumber}: Both width and height must be provided for area-based pricing."
                );
            }
        }
    }

    /**
     * Validate dimension values.
     *
     * @param float|null $width
     * @param float|null $height
     * @param int|null $quantity
     * @throws InvalidArgumentException
     */
    public function validateDimensions(?float $width, ?float $height, ?int $quantity): void
    {
        if ($quantity !== null && $quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        if ($width !== null && $width < 0) {
            throw new InvalidArgumentException('Width cannot be negative.');
        }

        if ($height !== null && $height < 0) {
            throw new InvalidArgumentException('Height cannot be negative.');
        }
    }

    /**
     * Get order statistics for dashboard.
     *
     * @return array{total_orders: int, pending_production: int, unpaid_orders: int, total_revenue: float}
     */
    public function getOrderStatistics(): array
    {
        return [
            'total_orders' => Order::active()->count(),
            'pending_production' => Order::active()
                ->where('production_status', 'pending')
                ->count(),
            'unpaid_orders' => Order::active()
                ->where('payment_status', 'unpaid')
                ->count(),
            'total_revenue' => (float) Order::active()
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
        ];
    }
}
