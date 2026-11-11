<?php
use App\Services\Matricule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) parent number
        if (!Schema::hasColumn('parents', 'numero')) {
            Schema::table('parents', fn (Blueprint $t) => $t->unsignedInteger('numero')->nullable()->unique()->after('id'));
        }

        // 2) sessions per week (default 2) replaces sessions_per_month; timetable no longer used for billing
        if (!Schema::hasColumn('subjects', 'sessions_per_week')) {
            Schema::table('subjects', fn (Blueprint $t) => $t->unsignedSmallInteger('sessions_per_week')->default(2)->after('prix'));
        }
        if (Schema::hasColumn('subjects', 'sessions_per_month')) {
            foreach (DB::table('subjects')->whereNotNull('sessions_per_month')->get(['id', 'sessions_per_month']) as $s) {
                DB::table('subjects')->where('id', $s->id)->update(['sessions_per_week' => max(1, (int) round($s->sessions_per_month / 4))]);
            }
            Schema::table('subjects', fn (Blueprint $t) => $t->dropColumn('sessions_per_month'));
        }

        // 3) credit / advance columns
        Schema::table('payments', function (Blueprint $t) {
            if (!Schema::hasColumn('payments', 'full_amount')) $t->decimal('full_amount', 12, 2)->nullable()->after('paye');
            if (!Schema::hasColumn('payments', 'credit_in')) $t->decimal('credit_in', 12, 2)->default(0)->after('full_amount');
            if (!Schema::hasColumn('payments', 'credit_out')) $t->decimal('credit_out', 12, 2)->default(0)->after('credit_in');
        });

        // 4) existing data: parent numbers in id order, students renumbered per level in id order
        $r = Matricule::renumberAll();
        logger()->info('Sigma renumber', $r);
    }

    public function down(): void
    {
        foreach (['full_amount', 'credit_in', 'credit_out'] as $col) {
            Schema::table('payments', fn (Blueprint $t) => $t->dropColumn($col));
        }
        Schema::table('subjects', function (Blueprint $t) {
            $t->unsignedSmallInteger('sessions_per_month')->nullable()->after('prix');
        });
        DB::table('subjects')->update(['sessions_per_month' => DB::raw('sessions_per_week * 4')]);
        Schema::table('subjects', fn (Blueprint $t) => $t->dropColumn('sessions_per_week'));
        Schema::table('parents', fn (Blueprint $t) => $t->dropColumn('numero'));
    }
};
