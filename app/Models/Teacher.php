<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
protected $fillable = ['first_name','last_name','email','phone','qualification','department','hire_date','salary','status','address','photo'];
public function getFullNameAttribute() { return trim($this->first_name . ' ' . $this->last_name); }
}
