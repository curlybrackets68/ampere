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
        Schema::table('service_details', function (Blueprint $table) {
            $table->dropColumn('inquery_flag');
            $table->bigInteger('inquiry_flag')->after('status')->default(1)->comment('1 = pending, 2 = complete');;
            $table->bigInteger('inquiry_id')->after('inquiry_flag')->default(0)->comment('inquery id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_details', function (Blueprint $table) {
            //
        });
    }
};
