<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['nom', 'capacite', 'actif'];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'capacite' => 'integer',
        ];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'room_id');
    }

    public function scopeActive($query)
    {
        return $query->where('actif', true)->orderBy('nom');
    }
}
