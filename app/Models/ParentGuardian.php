<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentGuardian extends Model
{
    protected $table = 'parents';
    protected $fillable = ['nom','prenom','cin','telephone','email','adresse','profession'];
    public function getFullNameAttribute(): string { return trim($this->nom.' '.($this->prenom ?? '')); }
    public function students(): BelongsToMany { return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id'); }
}
