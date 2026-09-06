<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingParticipant extends Model
{
    protected $fillable = [
        'training_id','student_id','nom_externe','telephone','montant_paye','resultat','certificat',
    ];
    protected function casts(): array
    {
        return ['montant_paye'=>'decimal:2','certificat'=>'boolean'];
    }
    public function training(): BelongsTo { return $this->belongsTo(Training::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function getDisplayNameAttribute(): string
    {
        return $this->student?->full_name ?? ($this->nom_externe ?? '—');
    }
}
