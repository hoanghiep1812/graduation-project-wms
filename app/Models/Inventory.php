<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'bin_location_id',
        'batch_id',
        'on_hand_quantity',
        'reserved_quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->on_hand_quantity - $this->reserved_quantity;
    }
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
