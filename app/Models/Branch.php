<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Branch extends Model
{
    protected $fillable = ['name','address','phone','email','principal_id','gps_lat','gps_lng','map_embed_url','is_active','is_headquarters','order'];
    protected $casts = ['is_active'=>'boolean','is_headquarters'=>'boolean'];
    public function principal()
    {
        return $this->belongsTo(Teacher::class,'principal_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
