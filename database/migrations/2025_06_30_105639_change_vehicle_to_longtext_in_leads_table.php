<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Step 1: Change the column type
            Schema::table('leads', function (Blueprint $table) {
                $table->longText('vehicle')->change();
            });

            // Step 2: Convert existing values to JSON arrays without quotes (e.g., '1' → [1])
            DB::table('leads')->whereNotNull('vehicle')->update([
                'vehicle' => DB::raw("CONCAT('[', vehicle, ']')")
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Step 1: Convert [1] → 1 (remove brackets)
            DB::table('leads')->whereNotNull('vehicle')->update([
                'vehicle' => DB::raw("REPLACE(REPLACE(vehicle, '[', ''), ']', '')")
            ]);

            // Step 2: Revert column type back to varchar(100)
            Schema::table('leads', function (Blueprint $table) {
                $table->string('vehicle', 100)->change();
            });
        });
    }
};
