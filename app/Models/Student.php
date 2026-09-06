<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** Stable stored values for academic level (translated in lang files). */
    public const NIVEAUX_SCOLAIRES = [
        '1ère année primaire',
        '2ème année primaire',
        '3ème année primaire',
        '4ème année primaire',
        '5ème année primaire',
        '6ème année primaire',
        '1ère année collège',
        '2ème année collège',
        '3ème année collège',
        'Tronc commun',
        '1ère bac',
        '2ème bac',
        'Non scolarisé',
    ];

    protected $fillable = [
        'matricule','nom','prenom','date_naissance','lieu_naissance','sexe','cin','telephone',
        'email','adresse','niveau_scolaire','class_id','date_inscription','statut','code_massar','photo','school_year_id',
    ];
    protected function casts(): array
    {
        return ['date_naissance'=>'date','date_inscription'=>'date'];
    }
    public function getFullNameAttribute(): string { return trim($this->nom.' '.$this->prenom); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function schoolYear(): BelongsTo { return $this->belongsTo(SchoolYear::class); }
    public function parents(): BelongsToMany { return $this->belongsToMany(ParentGuardian::class, 'parent_student', 'student_id', 'parent_id'); }
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subject')
            ->withPivot('prix')
            ->withTimestamps();
    }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function departures(): HasMany { return $this->hasMany(Departure::class); }

    /** Sum of enrolled subject prices (pivot.prix, else subject.prix as safety). */
    public function monthlyFeeAmount(): float
    {
        $this->loadMissing('subjects');
        return (float) $this->subjects->sum(function (Subject $subject) {
            $pivotPrix = $subject->pivot->prix ?? null;
            return $pivotPrix !== null && $pivotPrix !== ''
                ? (float) $pivotPrix
                : (float) ($subject->prix ?? 0);
        });
    }
}
