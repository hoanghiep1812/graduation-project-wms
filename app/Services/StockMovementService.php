<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

class StockMovementService
{
    public function recordMovement(
        Inventory $inventory,
        int $quantityChange,
        string $transactionType,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null
    ) {

        $movement = StockMovement::create([
            'inventory_id'     => $inventory->id,
            'transaction_type' => $transactionType,
            'quantity_change'  => $quantityChange,
            'balance_after'    => $inventory->on_hand_quantity,
            'reference_type'   => $reference ? get_class($reference) : null,
            'reference_id'     => $reference ? $reference->id : null,
            'created_by'       => $userId,
            'note'             => $note,
        ]);

        return $movement;
    }
}
