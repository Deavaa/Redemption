<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceStatement extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','branch_id','statement_type','period_from','period_to','total_income','total_expense','net_balance','description'];
    protected function casts(): array { return ['period_from'=>'date','period_to'=>'date','total_income'=>'decimal:2','total_expense'=>'decimal:2','net_balance'=>'decimal:2']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}