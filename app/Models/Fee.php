<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fee extends Model
{
    protected $fillable = [
        'class_id','school_year_id','inscription','mensualite','transport','cantine','activites','formation','autres',
    ];
    protected function casts(): array
    {
        return [
            'inscription'=>'decimal:2','mensualite'=>'decimal:2','transport'=>'decimal:2',
            'cantine'=>'decimal:2','activites'=>'decimal:2','formation'=>'decimal:2','autres'=>'decimal:2',
        ];
    }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function schoolYear(): BelongsTo { return $this->belongsTo(SchoolYear::class); }
}
