<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','type','certificate_number','issue_date','content','template'];
    protected function casts(): array { return ['issue_date'=>'date']; }
    public function student() { return $this->belongsTo(Student::class); }
}
