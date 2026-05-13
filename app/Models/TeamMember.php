<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;
    protected $fillable = ['name','designation','department','qualification','experience','phone','email','photo','bio','sort_order','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}