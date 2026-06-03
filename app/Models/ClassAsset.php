<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ClassAsset extends Model
{
    use HasFactory;
    protected $fillable = [
        "class_id", "section_id", "name", "quantity", "condition",
        "purchase_date", "purchase_price", "description"
    ];

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, "class_id");
    }

    public function section()
    {
        return $this->belongsTo(Section::class, "section_id");
    }
}