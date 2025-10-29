<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('awards_log', function (Blueprint $table) {
            $table->text('log')->change();
            $table->text('data')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('awards_log', function (Blueprint $table) {
            $table->string('log', 191)->change();
            $table->string('data', 1024)->nullable()->change();
        });
    }
};
