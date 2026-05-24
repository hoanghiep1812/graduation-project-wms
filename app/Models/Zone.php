<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;
    protected $fillable = ['warehouse_id', 'code', 'description', 'distance_to_packing'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function binLocations()
    {
        return $this->hasMany(BinLocation::class, 'zone_id');
    }
}
