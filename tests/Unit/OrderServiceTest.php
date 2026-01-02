<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\OrderService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    /**
     * Test calculate item subtotal with dimensions (area-based pricing).
     */
    public function test_calculate_item_subtotal_with_dimensions(): void
    {
        // 100cm x 200cm = 20000 cm² = 2 m²
        // 10 qty * 50000 price * 2 m² = 1,000,000
        $subtotal = $this->orderService->calculateItemSubtotal(
            quantity: 10,
            unitPrice: 50000.00,
            width: 100.0,
            height: 200.0
        );

        $this->assertEquals(1000000.00, $subtotal);
    }

    /**
     * Test calculate item subtotal without dimensions.
     */
    public function test_calculate_item_subtotal_without_dimensions(): void
    {
        // 5 qty * 25000 price = 125,000
        $subtotal = $this->orderService->calculateItemSubtotal(
            quantity: 5,
            unitPrice: 25000.00
        );

        $this->assertEquals(125000.00, $subtotal);
    }

    /**
     * Test calculate item subtotal with zero dimensions (should not apply area).
     */
    public function test_calculate_item_subtotal_with_zero_dimensions(): void
    {
        // If width or height is 0, area-based pricing should not apply
        $subtotal = $this->orderService->calculateItemSubtotal(
            quantity: 5,
            unitPrice: 25000.00,
            width: 0.0,
            height: 100.0
        );

        // Should fall back to simple quantity * price
        $this->assertEquals(125000.00, $subtotal);
    }

    /**
     * Test validate dimensions rejects zero quantity.
     */
    public function test_validate_dimensions_rejects_zero_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be greater than zero.');

        $this->orderService->validateDimensions(
            width: 100.0,
            height: 200.0,
            quantity: 0
        );
    }

    /**
     * Test validate dimensions rejects negative width.
     */
    public function test_validate_dimensions_rejects_negative_width(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Width cannot be negative.');

        $this->orderService->validateDimensions(
            width: -50.0,
            height: 200.0,
            quantity: 5
        );
    }

    /**
     * Test validate dimensions rejects negative height.
     */
    public function test_validate_dimensions_rejects_negative_height(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Height cannot be negative.');

        $this->orderService->validateDimensions(
            width: 100.0,
            height: -200.0,
            quantity: 5
        );
    }

    /**
     * Test validate dimensions accepts valid values.
     */
    public function test_validate_dimensions_accepts_valid_values(): void
    {
        // Should not throw any exception
        $this->orderService->validateDimensions(
            width: 100.0,
            height: 200.0,
            quantity: 5
        );

        $this->assertTrue(true); // If we reach here, validation passed
    }

    /**
     * Test validate order items rejects empty items.
     */
    public function test_validate_order_items_rejects_empty_items(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order must have at least one item.');

        $this->orderService->validateOrderItems([]);
    }

    /**
     * Test validate order items rejects zero quantity.
     */
    public function test_validate_order_items_rejects_zero_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item #1: Quantity must be greater than zero.');

        $this->orderService->validateOrderItems([
            [
                'product_id' => 1,
                'quantity' => 0,
                'unit_price' => 50000,
            ],
        ]);
    }

    /**
     * Test validate order items rejects negative unit price.
     */
    public function test_validate_order_items_rejects_negative_price(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item #1: Unit price must be greater than zero.');

        $this->orderService->validateOrderItems([
            [
                'product_id' => 1,
                'quantity' => 5,
                'unit_price' => -1000,
            ],
        ]);
    }

    /**
     * Test validate order items rejects partial dimensions.
     */
    public function test_validate_order_items_rejects_partial_dimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item #1: Both width and height must be provided');

        $this->orderService->validateOrderItems([
            [
                'product_id' => 1,
                'quantity' => 5,
                'unit_price' => 50000,
                'width' => 100,
                // height not provided
            ],
        ]);
    }

    /**
     * Test validate order items accepts valid items.
     */
    public function test_validate_order_items_accepts_valid_items(): void
    {
        // Should not throw any exception
        $this->orderService->validateOrderItems([
            [
                'product_id' => 1,
                'quantity' => 5,
                'unit_price' => 50000,
                'width' => 100,
                'height' => 200,
            ],
            [
                'product_id' => 2,
                'quantity' => 10,
                'unit_price' => 25000,
            ],
        ]);

        $this->assertTrue(true); // If we reach here, validation passed
    }
}
