<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlottingRecommendation extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'current_bin_id',
        'recommended_bin_id',
        'suggested_zone_id',
        'reason',
        'priority',
        'status'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currentBin()
    {
        return $this->belongsTo(BinLocation::class, 'current_bin_id');
    }

    public function suggestedZone()
    {
        return $this->belongsTo(Zone::class, 'suggested_zone_id');
    }
    public function recommendedBin()
    {
        return $this->belongsTo(BinLocation::class, 'recommended_bin_id');
    }
}
