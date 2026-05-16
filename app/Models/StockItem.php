<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'category', 'description', 'unit',
        'quantity', 'minimum_stock', 'unit_price', 'total_value',
        'location', 'branch_id', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit_price' => 'decimal:2',
            'total_value' => 'decimal:2',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->minimum_stock;
    }

    public function isOutOfStock()
    {
        return $this->quantity <= 0;
    }

    public function recalculate()
    {
        $this->total_value = $this->quantity * $this->unit_price;
        $this->save();
    }

    public static function categoryOptions(): array
    {
        return [
            'fixed_asset' => __('Fixed Asset'),
            'stationary' => __('Stationary'),
            'furniture' => __('Furniture'),
            'electronics' => __('Electronics'),
            'cleaning' => __('Cleaning Supply'),
            'other' => __('Other'),
        ];
    }

    public static function unitOptions(): array
    {
        return [
            'pcs' => __('Pieces'),
            'box' => __('Box'),
            'ream' => __('Ream'),
            'set' => __('Set'),
            'pack' => __('Pack'),
            'bundle' => __('Bundle'),
            'roll' => __('Roll'),
            'liter' => __('Liter'),
            'kg' => __('Kilogram'),
            'pair' => __('Pair'),
        ];
    }

    public function getCategoryLabelAttribute()
    {
        return self::categoryOptions()[$this->category] ?? $this->category;
    }
}
