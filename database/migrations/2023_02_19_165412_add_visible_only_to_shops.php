<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibleOnlyToShops extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('visible_only')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('visible_only');
        });
    }
}
