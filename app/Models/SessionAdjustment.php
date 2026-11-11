<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Manual correction of the number of sessions held for a student+subject in a month, up to as_of (inclusive). */
class SessionAdjustment extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'periode', 'as_of', 'old_count', 'new_count', 'user_id', 'note'];

    protected function casts(): array
    {
        return ['as_of' => 'date'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
