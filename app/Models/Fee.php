<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;
    protected $fillable = ['class_id','enrollment_type','branch_id','academic_year_id','fee_type','amount','due_date','description','is_active'];
    protected function casts(): array { return ['amount'=>'decimal:2','due_date'=>'date','is_active'=>'boolean','branch_id'=>'integer']; }
    public function classroom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function feePayments() { return $this->hasMany(FeePayment::class); }
    public function branch() { return $this->belongsTo(Branch::class); }

    /**
     * Enrollment type labels for display.
     */
    public static function enrollmentTypes(): array
    {
        return [
            'all' => 'All Students',
            'new' => 'New Enrollment',
            'transfer' => 'Transfer',
            'readmission' => 'Readmission',
        ];
    }

    /**
     * Get the human-readable label for the enrollment_type field.
     */
    public function getEnrollmentTypeLabelAttribute(): string
    {
        return self::enrollmentTypes()[$this->enrollment_type] ?? ucfirst($this->enrollment_type);
    }
}