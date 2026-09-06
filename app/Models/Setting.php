<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'nom_etablissement','sous_titre','adresse','telephone','email','logo',
        'couleur_principale','couleur_sidebar',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? static::create([
            'nom_etablissement' => 'NOOR ACADEMY',
            'sous_titre' => 'SOUTIEN SCOLAIRE',
            'couleur_principale' => '#f7be1d',
            'couleur_sidebar' => '#081f52',
        ]);
    }
}
