<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    protected $fillable = ['nom','date_debut','date_fin','is_active'];
    protected function casts(): array
    {
        return ['date_debut'=>'date','date_fin'=>'date','is_active'=>'boolean'];
    }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class, 'school_year_id'); }
    public function students(): HasMany { return $this->hasMany(Student::class, 'school_year_id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class, 'school_year_id'); }
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
