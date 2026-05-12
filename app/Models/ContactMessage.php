<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;
    protected $fillable = ['name','email','phone','subject','message','branch_id','is_read'];
    protected function casts(): array { return ['is_read'=>'boolean']; }
    public function branch() { return $this->belongsTo(Branch::class); }
}