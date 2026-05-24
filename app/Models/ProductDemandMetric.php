<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDemandMetric extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'sales_30_days',
        'sales_90_days',
        'velocity_score',
        'velocity_category',
        'last_calculated_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
