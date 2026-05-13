<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model
{
    use HasFactory;
    protected $fillable = ['name','code','type','description'];
    public function assignments() { return $this->hasMany(\App\Models\TeacherAssignment::class, 'subject_id'); }
}