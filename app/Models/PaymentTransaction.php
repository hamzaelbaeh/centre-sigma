<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = ['payment_id','date','montant','mode','reference'];
    protected function casts(): array
    {
        return ['date'=>'date','montant'=>'decimal:2'];
    }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
