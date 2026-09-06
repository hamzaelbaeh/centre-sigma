<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff';
    protected $fillable = [
        'matricule','nom','prenom','cin','telephone','poste','date_embauche','salaire','mode_paiement','statut',
    ];
    protected function casts(): array
    {
        return ['date_embauche'=>'date','salaire'=>'decimal:2'];
    }
    public function getFullNameAttribute(): string { return trim($this->nom.' '.$this->prenom); }
    public function payrolls(): HasMany { return $this->hasMany(StaffPayroll::class); }
}
