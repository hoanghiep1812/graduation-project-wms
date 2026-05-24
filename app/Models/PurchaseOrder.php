<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'supplier_name',
        'warehouse_id',
        'status',
        'assigned_to',
        'created_by',
        'approved_by',
        'expected_date',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'expected_date' => 'datetime',
        'approved_at'   => 'datetime',
        'completed_at'  => 'datetime',
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_APPROVED  = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function getStatusMetaAttribute()
    {
        return [
            self::STATUS_DRAFT => [
                'label' => 'Chờ',
                'class' => 'badge-light-warning'
            ],
            self::STATUS_APPROVED => [
                'label' => 'Chờ Cất',
                'class' => 'badge-light-primary'
            ],
            self::STATUS_COMPLETED => [
                'label' => 'Hoàn Tất',
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
            && $this->status != self::STATUS_COMPLETED;
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }


    public function totalItems()
    {
        return $this->items->count();
    }

    public function totalQuantity()
    {
        return $this->items->sum('quantity');
    }

    public function isAssigned()
    {
        return !is_null($this->assigned_to);
    }

    public function canBeProcessedBy($userId)
    {
        return $this->assigned_to === null || $this->assigned_to == $userId;
    }
}
