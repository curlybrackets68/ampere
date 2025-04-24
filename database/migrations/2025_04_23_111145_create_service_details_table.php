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
        Schema::create('service_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amc_id');
            $table->dateTime('service_date');
            $table->text('service_remark')->nullable();
            $table->string('attachment')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = pending, 2 = complete');
            $table->tinyInteger('inquery_flag')->default(1)->comment('1 = pending, 2 = complete');
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
        Schema::dropIfExists('service_details');
    }
};
