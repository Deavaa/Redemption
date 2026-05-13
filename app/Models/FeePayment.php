<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    use HasFactory;
    protected $fillable = ['fee_id','student_id','amount_paid','payment_date','payment_method','transaction_id','receipt_number','status'];
    protected function casts(): array { return ['amount_paid'=>'decimal:2','payment_date'=>'date']; }
    public function fee() { return $this->belongsTo(Fee::class); }
    public function student() { return $this->belongsTo(Student::class); }
}