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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 255)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_mobile', 12)->nullable();
            $table->string('customer_vehicle_no', 255)->nullable();
            $table->text('order_name')->nullable();
            $table->dateTime('order_date')->nullable();
            $table->bigInteger('status_id')->default(1);
            $table->text('status_remark')->nullable();
            $table->dateTime('status_date')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
