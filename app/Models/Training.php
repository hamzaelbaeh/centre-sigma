<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Training extends Model
{
    protected $fillable = [
        'nom','formateur','description','duree','date_debut','date_fin','prix','salle','classe_niveau','nombre_seances',
    ];
    protected function casts(): array
    {
        return ['date_debut'=>'date','date_fin'=>'date','prix'=>'decimal:2'];
    }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'subject_training'); }
    public function participants(): HasMany { return $this->hasMany(TrainingParticipant::class); }
}
