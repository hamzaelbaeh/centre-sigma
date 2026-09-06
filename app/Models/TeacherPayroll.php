<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPayroll extends Model
{
    protected $fillable = [
        'teacher_id','periode','mode_paiement','base','taux','brut','primes','avances','retenues','net','statut',
    ];
    protected function casts(): array
    {
        return [
            'base'=>'decimal:2','taux'=>'decimal:2','brut'=>'decimal:2','primes'=>'decimal:2',
            'avances'=>'decimal:2','retenues'=>'decimal:2','net'=>'decimal:2',
        ];
    }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public static function calcNet($brut, $primes, $avances, $retenues): float
    {
        return (float)$brut + (float)$primes - (float)$avances - (float)$retenues;
    }
}
