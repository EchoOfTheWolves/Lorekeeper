<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('sort')->unsigned()->default(0);
            $table->text('description')->nullable()->default(null);
            $table->boolean('has_image')->default(0);
        });

        Schema::table('shops', function (Blueprint $table) { 
            $table->integer('shop_category_id')->unsigned()->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
