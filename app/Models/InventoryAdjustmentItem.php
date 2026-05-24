<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'inventory_adjustment_id',
        'inventory_id',
        'system_quantity',
        'counted_quantity',
        'variance'
    ];

    public function adjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
