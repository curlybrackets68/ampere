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
        Schema::create('user_rights', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->default(0);
            $table->bigInteger('module_id')->default(0);
            $table->bigInteger('sub_module_id')->default(0);
            $table->tinyInteger('role_view')->default(0)->comment('1 - Allow, 0 - Not allow	');
            $table->tinyInteger('role_viewAll')->default(0)->comment('1 - Allow, 0 - Not allow	');
            $table->tinyInteger('role_add')->default(0)->comment('1 - Allow, 0 - Not allow	');
            $table->tinyInteger('role_edit')->default(0)->comment('1 - Allow, 0 - Not allow	');
            $table->tinyInteger('role_delete')->default(0)->comment('1 - Allow, 0 - Not allow	');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rights');
    }
};
