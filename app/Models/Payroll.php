<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id','basic_salary','allowances','deductions','tax','net_salary','pay_period','payment_date','status'];
    protected function casts(): array { return ['basic_salary'=>'decimal:2','allowances'=>'decimal:2','deductions'=>'decimal:2','tax'=>'decimal:2','net_salary'=>'decimal:2','payment_date'=>'date']; }
    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
}