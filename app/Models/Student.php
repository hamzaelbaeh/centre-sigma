<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'matricule','nom','prenom','date_naissance','lieu_naissance','sexe','cin','telephone',
        'email','adresse','class_id','date_inscription','statut','code_massar','photo','school_year_id',
    ];
    protected function casts(): array
    {
        return ['date_naissance'=>'date','date_inscription'=>'date'];
    }
    public function getFullNameAttribute(): string { return trim($this->nom.' '.$this->prenom); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function schoolYear(): BelongsTo { return $this->belongsTo(SchoolYear::class); }
    public function parents(): BelongsToMany { return $this->belongsToMany(ParentGuardian::class, 'parent_student', 'student_id', 'parent_id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function departures(): HasMany { return $this->hasMany(Departure::class); }
}
