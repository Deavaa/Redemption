<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;
    protected $fillable = ['name','start_date','end_date','is_current'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date','is_current'=>'boolean']; }
    public function terms() { return $this->hasMany(Term::class); }
    public function classes() { return $this->hasMany(Classroom::class); }
    public function students() { return $this->hasMany(Student::class); }
    public function exams() { return $this->hasMany(Exam::class); }
    public function teacherAssignments() { return $this->hasMany(TeacherAssignment::class); }
    public function fees() { return $this->hasMany(Fee::class); }
    public function budgets() { return $this->hasMany(Budget::class); }
    public function incomeExpenses() { return $this->hasMany(IncomeExpense::class); }
    public function progressReports() { return $this->hasMany(ProgressReport::class); }
    public function performanceReports() { return $this->hasMany(PerformanceReport::class); }
    public function audits() { return $this->hasMany(Audit::class); }
    public function financeStatements() { return $this->hasMany(FinanceStatement::class); }
}