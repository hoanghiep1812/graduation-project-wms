<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAuditItem extends Model
{
    use HasFactory;

    protected $fillable = ['inventory_audit_id', 'inventory_id', 'system_quantity', 'actual_quantity', 'note'];

    public function audit()
    {
        return $this->belongsTo(InventoryAudit::class, 'inventory_audit_id');
    }

    
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
