<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'inventory_id',
        'transaction_type',
        'quantity_change',
        'balance_after',
        'reference_type',
        'reference_id',
        'created_by',
        'note'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
