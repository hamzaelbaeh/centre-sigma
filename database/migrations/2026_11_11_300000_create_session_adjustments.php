<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('session_adjustments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('student_id');
            $t->unsignedBigInteger('subject_id');
            $t->string('periode', 7);
            $t->date('as_of');               // correction date: the corrected count covers the month up to this day (inclusive)
            $t->unsignedSmallInteger('old_count');
            $t->unsignedSmallInteger('new_count');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('note')->nullable();
            $t->timestamps();
            $t->index(['student_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_adjustments');
    }
};
