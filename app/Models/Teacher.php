<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'matricule','nom','prenom','cin','telephone','email','adresse','date_embauche',
        'specialite','statut','mode_paiement','valeur_dh',
    ];
    protected function casts(): array
    {
        return ['date_embauche'=>'date','valeur_dh'=>'decimal:2'];
    }
    public function getFullNameAttribute(): string { return trim($this->nom.' '.$this->prenom); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class, 'teacher_id'); }
    public function timetableSlots(): HasMany { return $this->hasMany(TimetableSlot::class); }
    public function payrolls(): HasMany { return $this->hasMany(TeacherPayroll::class); }
}
