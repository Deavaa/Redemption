<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'branch_id',
        'class_id',
        'section_id',
        'roll_number',
        'enrollment_date',
        'status',
        'enrollment_type',
        'registration_fee',
        'fee_discount',
        'discount_type',
        'discount_reason',
        'registration_fee_paid',
        'registration_fee_date',
        'registration_fee_status',
        'registration_fee_payment_method',
        'registration_fee_receipt_number',
        'registration_fee_notes',
        'withdrawal_date',
        'withdrawal_reason',
        'transferred_to_branch_id',
        'notes',
        'enrolled_by',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
            'registration_fee' => 'decimal:2',
            'fee_discount' => 'decimal:2',
            'registration_fee_paid' => 'decimal:2',
            'registration_fee_date' => 'date',
            'withdrawal_date' => 'date',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function transferredToBranch()
    {
        return $this->belongsTo(Branch::class, 'transferred_to_branch_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeEnrolled($query)
    {
        return $query->where('status', 'enrolled');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeWithdrawn($query)
    {
        return $query->where('status', 'withdrawn');
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeRegistrationFeeUnpaid($query)
    {
        return $query->whereIn('registration_fee_status', ['unpaid', 'partial']);
    }

    public function scopeRegistrationFeePaid($query)
    {
        return $query->where('registration_fee_status', 'paid');
    }

    // ── Helper Methods ─────────────────────────────────────────────

    /**
     * Check if the registration fee is fully paid.
     */
    public function isRegistrationFeePaid(): bool
    {
        return $this->registration_fee_status === 'paid' || $this->registration_fee_status === 'waived';
    }

    /**
     * Get the effective registration fee after discount.
     */
    public function getEffectiveFeeAttribute(): float
    {
        if ($this->discount_type === 'percentage' && $this->fee_discount > 0) {
            $discountAmount = $this->registration_fee * ($this->fee_discount / 100);
            return max(0, $this->registration_fee - $discountAmount);
        }
        return max(0, $this->registration_fee - $this->fee_discount);
    }

    /**
     * Get the remaining registration fee balance.
     */
    public function getRegistrationFeeBalanceAttribute(): float
    {
        return max(0, $this->effective_fee - $this->registration_fee_paid);
    }

    /**
     * Record a registration fee payment.
     */
    public function payRegistrationFee(float $amount, string $paymentMethod, ?string $receiptNumber = null, ?string $notes = null): void
    {
        $this->registration_fee_paid += $amount;
        $this->registration_fee_payment_method = $paymentMethod;
        $this->registration_fee_date = now()->toDateString();

        if ($receiptNumber) {
            $this->registration_fee_receipt_number = $receiptNumber;
        }
        if ($notes) {
            $this->registration_fee_notes = $notes;
        }

        if ($this->registration_fee_paid >= $this->effective_fee) {
            $this->registration_fee_status = 'paid';
        } elseif ($this->registration_fee_paid > 0) {
            $this->registration_fee_status = 'partial';
        }

        $this->save();
    }

    /**
     * Withdraw a student from this enrollment.
     */
    public function withdraw(string $reason, ?string $date = null): void
    {
        $this->update([
            'status' => 'withdrawn',
            'withdrawal_date' => $date ?? now()->toDateString(),
            'withdrawal_reason' => $reason,
        ]);

        // Also update the student's main status
        $this->student->update([
            'status' => 'inactive',
            'leave_date' => $date ?? now()->toDateString(),
            'leave_reason' => $reason,
        ]);

        // Deactivate user account
        if ($this->student->user) {
            $this->student->user->update(['is_active' => false]);
        }
    }
}
