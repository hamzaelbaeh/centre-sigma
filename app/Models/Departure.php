<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Departure extends Model
{
    protected $fillable = [
        'student_id','nom_externe','date_sortie','statut','raison','destination','documents_remis','observation','solde_du',
    ];
    protected function casts(): array
    {
        return ['date_sortie'=>'date','solde_du'=>'decimal:2'];
    }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function getDisplayNameAttribute(): string
    {
        return $this->student?->full_name ?? ($this->nom_externe ?? '—');
    }
}
