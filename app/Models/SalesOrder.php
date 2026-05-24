<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_number',
        'partner_id',
        'customer_name',
        'warehouse_id',
        'status',
        'assigned_to',
        'created_by',
        'confirmed_by',
        'shipped_by',
        'cancelled_by',
        'confirmed_at',
        'shipped_at',
        'cancelled_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    const STATUS_DRAFT    = 'draft';
    const STATUS_PICKING  = 'picking';
    const STATUS_PICKED   = 'picked';
    const STATUS_SHIPPED  = 'shipped';
    const STATUS_CANCELLED = 'cancelled';

    public function getStatusMetaAttribute()
    {
        return [
            self::STATUS_DRAFT => [
                'label' => 'Chờ Lấy Hàng',
                'class' => 'badge-light-warning'
            ],
            self::STATUS_PICKING => [
                'label' => 'Đang Lấy',
                'class' => 'badge-light-primary'
            ],
            self::STATUS_PICKED => [
                'label' => 'Chờ Xuất',
                'class' => 'badge-light-info'
            ],
            self::STATUS_SHIPPED => [
                'label' => 'Đã Xuất',
                'class' => 'badge-light-success'
            ],
            self::STATUS_CANCELLED => [
                'label' => 'Đã Hủy',
                'class' => 'badge-light-danger'
            ],
        ][$this->status] ?? [
            'label' => strtoupper($this->status),
            'class' => 'badge-light-secondary'
        ];
    }

    public function isLockedByOther()
    {
        return $this->assigned_to
            && $this->assigned_to != auth()->id()
            && !in_array($this->status, [self::STATUS_SHIPPED, self::STATUS_CANCELLED]);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
