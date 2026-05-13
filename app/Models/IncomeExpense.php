<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeExpense extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','type','category','amount','date','description','reference','branch_id'];
    protected function casts(): array { return ['amount'=>'decimal:2','date'=>'date']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}