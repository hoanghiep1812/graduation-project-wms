<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name'
    ];

    public function binLocations()
    {
        return $this->hasMany(BinLocation::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
