<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SchoolClass extends Model
{
    protected $table = 'classes';
    protected $fillable = ['nom','niveau','salle','room_id','teacher_id','capacite','school_year_id'];

    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function schoolYear(): BelongsTo { return $this->belongsTo(SchoolYear::class); }
    public function students(): HasMany { return $this->hasMany(Student::class, 'class_id'); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id'); }
    public function timetableSlots(): HasMany { return $this->hasMany(TimetableSlot::class, 'class_id'); }
    public function fee(): HasOne { return $this->hasOne(Fee::class, 'class_id'); }
    public function getOccupancyAttribute(): int { return $this->students()->where('statut','Actif')->count(); }

    /** Sync salle display string from the linked room name. */
    public function syncSalleFromRoom(?Room $room = null): void
    {
        $room = $room ?? $this->room;
        if ($room) {
            $this->salle = $room->nom;
        }
    }
}
