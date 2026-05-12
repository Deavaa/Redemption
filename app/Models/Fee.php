<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;
    protected $fillable = ['class_id','academic_year_id','fee_type','amount','due_date','description','is_active'];
    protected function casts(): array { return ['amount'=>'decimal:2','due_date'=>'date','is_active'=>'boolean']; }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function feePayments() { return $this->hasMany(FeePayment::class); }
}