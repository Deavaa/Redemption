<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_integration_id',
        'transaction_reference',
        'bank_transaction_id',
        'amount',
        'currency',
        'transaction_date',
        'sender_name',
        'sender_account',
        'description',
        'status',
        'student_id',
        'fee_payment_id',
        'matched_amount',
        'match_notes',
        'matched_by',
        'matched_at',
        'source_file',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'matched_amount' => 'decimal:2',
        'transaction_date' => 'date',
        'matched_at' => 'datetime',
    ];

    public function bankIntegration()
    {
        return $this->belongsTo(BankIntegration::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feePayment()
    {
        return $this->belongsTo(FeePayment::class);
    }

    public function matchedByUser()
    {
        return $this->belongsTo(User::class, 'matched_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeMatched($query)
    {
        return $query->where('status', 'matched');
    }

    public function scopeUnmatched($query)
    {
        return $query->where('status', 'unmatched');
    }

    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pending Review',
            'matched' => 'Matched to Student',
            'unmatched' => 'Unmatched',
            'rejected' => 'Rejected',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'pending' => 'modern-badge-warning',
            'matched' => 'modern-badge-success',
            'unmatched' => 'modern-badge-danger',
            'rejected' => 'modern-badge-light',
            default => 'modern-badge-light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst($this->status);
    }
}
