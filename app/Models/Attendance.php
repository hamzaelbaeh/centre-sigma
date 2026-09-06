<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = ['student_id','class_id','subject_id','date','status','justifie','motif'];
    protected function casts(): array
    {
        return ['date'=>'date','justifie'=>'boolean'];
    }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
}
