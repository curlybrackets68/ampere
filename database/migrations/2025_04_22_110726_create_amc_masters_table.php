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
        Schema::create('amc_masters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('amc_display_number')->nullable();
            $table->unsignedBigInteger('amc_reference_id')->nullable()->default(0)->comment('If AMC is renewed, parent AMC ID is here');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('chassis_number')->nullable();
            $table->tinyInteger('vehicle_type')->default(1)->comment('1 = New, 2 = Old');
            $table->unsignedBigInteger('vehicle_master_id');
            $table->string('vehicle_number')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->tinyInteger('amc_type')->default(1)->comment('1 = Free, 2 = Paid');
            $table->decimal('amc_basic_price', 10, 2)->nullable()->default(0);
            $table->dateTime('amc_start_date')->nullable();
            $table->dateTime('amc_end_date')->nullable();
            $table->tinyInteger('payment_type')->default(0)->comment('1 = Cash, 2 = Online, 3 = Cheque');
            $table->text('transaction_details')->nullable();
            $table->integer('no_of_service')->default(4)->comment('Total services in AMC');
            $table->tinyInteger('status')->default('10')->comment('10 = active, 11 = inactive');;
            $table->text('status_remark')->nullable();
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
        Schema::dropIfExists('amc_masters');
    }
};
