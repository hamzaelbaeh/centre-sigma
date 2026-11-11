<?php
namespace App\Models;
use App\Services\Matricule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentGuardian extends Model
{
    protected $table = 'parents';
    protected $fillable = ['nom','prenom','cin','telephone','email','adresse','profession'];

    protected static function booted(): void
    {
        static::creating(function (ParentGuardian $p) {
            if (!$p->numero) $p->numero = ((int) static::max('numero')) + 1;
        });
    }

    public function getFullNameAttribute(): string { return trim($this->nom.' '.($this->prenom ?? '')); }
    public function students(): BelongsToMany { return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id'); }

    /** 3-digit parent number, e.g. 012 */
    public function getNumeroPadAttribute(): string { return Matricule::parentNumero($this->numero); }
    public function getChildrenCountAttribute(): int
    {
        return (int) ($this->attributes['students_count'] ?? $this->students()->count());
    }
    /** Display ID: number-children, e.g. 012-3 (children count computed live). */
    public function getDisplayIdAttribute(): string { return $this->numero_pad.'-'.$this->children_count; }
}
