<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'status',
        'reason',
        'counter_name',
        'created_by',
        'approved_by'
    ];

    public function items()
    {
        return $this->hasMany(InventoryAdjustmentItem::class);
    }
}
