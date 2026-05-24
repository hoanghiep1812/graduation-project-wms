<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItemAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_item_id',
        'inventory_id',
        'allocated_quantity',
    ];

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
