<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayroll extends Model
{
    protected $fillable = ['staff_id','periode','brut','primes','avances','retenues','net','statut'];
    protected function casts(): array
    {
        return [
            'brut'=>'decimal:2','primes'=>'decimal:2','avances'=>'decimal:2','retenues'=>'decimal:2','net'=>'decimal:2',
        ];
    }
    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
}
