<?php
use App\Services\TimetableSnapshot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('timetable_weeks')) {
            Schema::create('timetable_weeks', function (Blueprint $t) {
                $t->id();
                $t->date('week_start')->unique(); // Monday
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('timetable_week_slots')) {
            Schema::create('timetable_week_slots', function (Blueprint $t) {
                $t->id();
                $t->date('week_start');
                $t->unsignedBigInteger('class_id');
                $t->unsignedBigInteger('subject_id')->nullable();
                $t->unsignedBigInteger('teacher_id')->nullable();
                $t->string('jour')->nullable();
                $t->time('debut')->nullable();
                $t->time('fin')->nullable();
                $t->string('salle')->nullable();
                $t->timestamps();
                $t->index(['class_id', 'week_start']);
            });
        }
        if (!Schema::hasColumn('payments', 'estimated')) {
            Schema::table('payments', fn (Blueprint $t) => $t->boolean('estimated')->default(false)->after('credit_out'));
        }
        // Start the snapshot history with the current week.
        TimetableSnapshot::ensureCurrent();
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $t) => $t->dropColumn('estimated'));
        Schema::dropIfExists('timetable_week_slots');
        Schema::dropIfExists('timetable_weeks');
    }
};
