<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAudit extends Model
{
    use HasFactory;
    protected $fillable = ['audit_code', 'status', 'created_by'];

    public function items()
    {
        return $this->hasMany(InventoryAuditItem::class);
    }

    // Lấy thông tin người tạo phiếu
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
