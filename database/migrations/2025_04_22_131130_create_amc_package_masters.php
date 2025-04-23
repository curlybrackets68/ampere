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
        Schema::create('amc_package_masters', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('vehicle_type')->default(1)->comment('1 = New, 2 = Old');
            $table->integer('service_count')->default(0);
            $table->integer('duration')->default(0);
            $table->integer('time_period')->default(0);
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amc_package_masters');
    }
};
