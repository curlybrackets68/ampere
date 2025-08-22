<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->tinyInteger('language_type')->default(1)->after('notes')->comment('1=english, 2=gujarati');
            $table->tinyInteger('location_type')->default(1)->after('language_type')->comment('1=Sama Savli Road, 2=Kalali-Vadsar Road');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
};
