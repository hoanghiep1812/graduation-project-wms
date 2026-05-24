<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'unit',
        'minimum_stock',
        'has_expiry',
        'expiry_duration',
        'is_active'
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
