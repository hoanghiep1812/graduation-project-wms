<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'tax_code',
        'address',
        'status',
    ];
    
    public function salesOrders() { 
    	return $this->hasMany(SalesOrder::class); 
    	
    }
}
