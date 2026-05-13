<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdCard extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','card_number','issue_date','valid_until','status'];
    protected function casts(): array { return ['issue_date'=>'date','valid_until'=>'date']; }
    public function student() { return $this->belongsTo(Student::class); }
}