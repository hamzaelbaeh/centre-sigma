<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('subjects', 'sessions_per_month')) {
            Schema::table('subjects', function (Blueprint $t) {
                // Fallback when the class has no timetable slot for this subject.
                $t->unsignedSmallInteger('sessions_per_month')->nullable()->after('prix');
            });
        }
        Schema::table('payments', function (Blueprint $t) {
            if (!Schema::hasColumn('payments', 'sessions')) $t->unsignedSmallInteger('sessions')->nullable()->after('periode');
            if (!Schema::hasColumn('payments', 'details')) $t->json('details')->nullable()->after('sessions');
        });
        Schema::table('students', function (Blueprint $t) {
            if (!Schema::hasColumn('students', 'suspendu_at')) $t->timestamp('suspendu_at')->nullable();
            if (!Schema::hasColumn('students', 'retire_at')) $t->timestamp('retire_at')->nullable();
        });
        if (!Schema::hasTable('student_alerts')) {
            Schema::create('student_alerts', function (Blueprint $t) {
                $t->id();
                $t->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $t->string('type', 40);
                $t->string('message');
                $t->timestamp('read_at')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_alerts');
        Schema::table('students', fn (Blueprint $t) => $t->dropColumn(['suspendu_at', 'retire_at']));
        Schema::table('payments', fn (Blueprint $t) => $t->dropColumn(['sessions', 'details']));
        Schema::table('subjects', fn (Blueprint $t) => $t->dropColumn('sessions_per_month'));
    }
};
