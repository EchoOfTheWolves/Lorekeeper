<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('character_images', function (Blueprint $table) {
            $table->string('label')->nullable()->default(null);
        });

        Schema::table('design_updates', function (Blueprint $table) {
            $table->string('label')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('character_images', function (Blueprint $table) {
            $table->dropColumn('label');
        });

        Schema::table('design_updates', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
