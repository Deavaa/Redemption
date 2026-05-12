<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','category','allocated_amount','spent_amount','description','status'];
    protected function casts(): array { return ['allocated_amount'=>'decimal:2','spent_amount'=>'decimal:2']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}