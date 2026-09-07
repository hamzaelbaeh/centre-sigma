<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = ['student_id','type','montant','paye','periode','statut','school_year_id'];
    protected function casts(): array
    {
        return ['montant'=>'decimal:2','paye'=>'decimal:2'];
    }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function schoolYear(): BelongsTo { return $this->belongsTo(SchoolYear::class); }
    public function transactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }
    public function getRestantAttribute(): float { return max(0, (float)$this->montant - (float)$this->paye); }
    public function isCancelled(): bool { return $this->statut === 'Annulé'; }
    public function isCancelable(): bool { return !$this->isCancelled() && $this->restant > 0; }

    public function scopeActiveDue(Builder $q): Builder
    {
        return $q->where('statut', '!=', 'Annulé')->whereRaw('paye < montant');
    }

    public function scopeNotCancelled(Builder $q): Builder
    {
        return $q->where('statut', '!=', 'Annulé');
    }

    public function refreshStatut(): void
    {
        if ($this->statut === 'Annulé') {
            return;
        }
        if ($this->paye <= 0) $this->statut = 'Non payé';
        elseif ($this->paye >= $this->montant) $this->statut = 'Soldé';
        else $this->statut = 'Partiel';
        $this->save();
    }
}
