<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeatherTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

//types of weather, like sun, cloudy, anything else really
        Schema::create('weather', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 64);
            $table->string('summary', 256)->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->boolean('is_visible')->default(true);
            $table->boolean('has_image')->default(0);
        });

        //the weather seasons, could also call them cycles i guess, it's hard to come up with a generic term for every site
        Schema::create('weather_seasons', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 64);

            $table->string('summary', 256)->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->timestamps();
            $table->timestamp('cycle_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);
            $table->boolean('is_visible')->default(true);
            $table->boolean('has_image')->default(0);
        });

        //table for outputs to roll on for the seasons
        Schema::create('weather_table', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('weather_season_id')->unsigned();
            $table->integer('weather_id')->unsigned();
            $table->integer('weight')->unsigned();  
            $table->foreign('weather_season_id')->references('id')->on('weather_seasons');
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
