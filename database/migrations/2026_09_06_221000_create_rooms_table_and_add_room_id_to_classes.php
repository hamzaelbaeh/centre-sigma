<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->unsignedInteger('capacite')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('rooms')->insert([
            ['nom' => 'قاعة 1', 'capacite' => null, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'قاعة 2', 'capacite' => null, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'قاعة 3', 'capacite' => null, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nom' => 'قاعة 4', 'capacite' => null, 'actif' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('salle')->constrained('rooms')->nullOnDelete();
        });

        // Best-effort: link existing classes whose salle matches a room nom or "Salle N"
        $rooms = DB::table('rooms')->get();
        foreach ($rooms as $room) {
            DB::table('classes')->where('salle', $room->nom)->update(['room_id' => $room->id]);
        }
        for ($i = 1; $i <= 4; $i++) {
            $roomId = DB::table('rooms')->where('nom', "قاعة {$i}")->value('id');
            if (!$roomId) {
                continue;
            }
            DB::table('classes')
                ->whereNull('room_id')
                ->whereIn('salle', ["Salle {$i}", "salle {$i}", "SALLE {$i}"])
                ->update(['room_id' => $roomId, 'salle' => "قاعة {$i}"]);
        }
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });
        Schema::dropIfExists('rooms');
    }
};
