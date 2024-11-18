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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('vehicle', 100)->nullable();
            $table->string('mobile', 11)->nullable();
            $table->string('area', 100)->nullable();
            $table->bigInteger('lead_source')->default(0);
            $table->bigInteger('salesman')->default(0);
            $table->text('notes')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
