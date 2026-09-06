<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    protected $fillable = ['nom','code','niveau','heures_semaine','prix'];
    protected function casts(): array
    {
        return [
            'heures_semaine' => 'decimal:2',
            'prix' => 'decimal:2',
        ];
    }
    public function classes(): BelongsToMany { return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'class_id'); }
    public function teachers(): BelongsToMany { return $this->belongsToMany(Teacher::class); }
    public function trainings(): BelongsToMany { return $this->belongsToMany(Training::class, 'subject_training'); }
}
