<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAlert extends Model
{
    protected $fillable = ['student_id', 'type', 'message', 'read_at'];
    protected function casts(): array { return ['read_at' => 'datetime']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
