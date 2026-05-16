<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_item_id', 'type', 'reason', 'quantity',
        'unit_price', 'total_price', 'transaction_date',
        'recipient_id', 'recipient_type', 'reference_no',
        'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }

    public function recipient()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function reasonOptions(): array
    {
        return [
            'purchase' => __('Purchase / Stock In'),
            'return' => __('Return'),
            'issue_employee' => __('Issue to Employee'),
            'issue_class' => __('Issue to Class'),
            'damaged' => __('Damaged / Wasted'),
            'lost' => __('Lost'),
            'adjustment' => __('Stock Adjustment'),
            'transfer' => __('Branch Transfer'),
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'in' => __('Stock In'),
            'out' => __('Stock Out'),
        ];
    }

    public function getReasonLabelAttribute()
    {
        return self::reasonOptions()[$this->reason] ?? $this->reason;
    }
}
