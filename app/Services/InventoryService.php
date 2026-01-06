<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\Material;
use App\Models\MaterialUsage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * InventoryService - Manages material stock and inventory logs.
 */
class InventoryService
{
    /**
     * Record a purchase (stock in) for a material.
     *
     * @param Material $material
     * @param float $quantity
     * @param float $costPerUnit
     * @param string|null $description
     * @return InventoryLog
     */
    public function recordPurchase(
        Material $material,
        float $quantity,
        float $costPerUnit,
        ?string $description = null
    ): InventoryLog {
        return DB::transaction(function () use ($material, $quantity, $costPerUnit, $description) {
            // Create inventory log
            $log = InventoryLog::create([
                'material_id' => $material->id,
                'type' => 'in',
                'quantity' => $quantity,
                'cost_per_unit' => $costPerUnit,
                'reference_type' => 'purchase',
                'description' => $description ?? "Pembelian {$material->name}",
                'user_id' => auth()->id(),
            ]);

            // Update material stock and cost
            $material->adjustStock('in', $quantity);
            $material->updateCost($costPerUnit);

            return $log;
        });
    }

    /**
     * Record usage (stock out) for a material usage entry.
     *
     * @param MaterialUsage $usage
     * @return InventoryLog
     */
    public function recordUsage(MaterialUsage $usage): InventoryLog
    {
        return DB::transaction(function () use ($usage) {
            $material = $usage->material;

            // Create inventory log
            $log = InventoryLog::create([
                'material_id' => $material->id,
                'type' => 'out',
                'quantity' => $usage->quantity_used,
                'cost_per_unit' => $usage->cost_per_unit,
                'reference_type' => 'order_item',
                'reference_id' => $usage->order_item_id,
                'description' => "Pemakaian untuk pesanan",
                'user_id' => auth()->id(),
            ]);

            // Update material stock
            $material->adjustStock('out', $usage->quantity_used);

            return $log;
        });
    }

    /**
     * Record stock adjustment.
     *
     * @param Material $material
     * @param string $type 'in' or 'out'
     * @param float $quantity
     * @param string|null $description
     * @return InventoryLog
     */
    public function recordAdjustment(
        Material $material,
        string $type,
        float $quantity,
        ?string $description = null
    ): InventoryLog {
        return DB::transaction(function () use ($material, $type, $quantity, $description) {
            $log = InventoryLog::create([
                'material_id' => $material->id,
                'type' => $type,
                'quantity' => $quantity,
                'cost_per_unit' => $material->cost_per_unit,
                'reference_type' => 'adjustment',
                'description' => $description ?? "Penyesuaian stok",
                'user_id' => auth()->id(),
            ]);

            $material->adjustStock($type, $quantity);

            return $log;
        });
    }

    /**
     * Check if material has enough stock for usage.
     *
     * @param int $materialId
     * @param float $quantity
     * @return bool
     */
    public function checkAvailability(int $materialId, float $quantity): bool
    {
        $material = Material::find($materialId);
        return $material && $material->hasEnoughStock($quantity);
    }

    /**
     * Get materials with low stock.
     *
     * @return Collection
     */
    public function getLowStockMaterials(): Collection
    {
        return Material::active()
            ->lowStock()
            ->get();
    }

    /**
     * Get stock movement history for a material.
     *
     * @param Material $material
     * @param int $limit
     * @return Collection
     */
    public function getStockHistory(Material $material, int $limit = 50): Collection
    {
        return $material->inventoryLogs()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Record material usage from order when completed.
     * This is called by OrderObserver.
     *
     * @param \App\Models\Order $order
     * @return void
     */
    public function recordOrderMaterialUsage(\App\Models\Order $order): void
    {
        foreach ($order->items as $item) {
            foreach ($item->materialUsages as $usage) {
                $this->recordUsage($usage);
            }
        }
    }
}
