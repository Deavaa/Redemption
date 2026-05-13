<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryVideo extends Model
{
    use HasFactory;
    protected $fillable = ['title','description','video_url','thumbnail','sort_order','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}