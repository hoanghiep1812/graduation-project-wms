<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'code',
        'zone_id',
        'max_capacity',
        'current_capacity'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
