<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAsset extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id','name','quantity','condition','issue_date','return_date','description'];
    protected function casts(): array { return ['issue_date'=>'date','return_date'=>'date']; }
    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
}