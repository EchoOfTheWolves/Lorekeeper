<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Settings;
use DB;
use Carbon\Carbon;
use App\Models\Weather\WeatherSeason;
use App\Models\Weather\Weather;

class cycle_site_weather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycle-site-weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cycles the site\'s weather.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentweather = Weather::where('id', Settings::get('site_weather'))->first();
        $currentseason = WeatherSeason::where('id', Settings::get('site_season'))->first();

        if(isset($currentseason)) {
        //change the weather
        if(Settings::get('site_weather_cycle') == 0) {
            //no reset setting
            $this->info('Not set to cycle weather currently. Adjust the settings if this is an error.');

        }
        if(Settings::get('site_weather_cycle') == 1) {
            //daily reset
                $results = [];
                $results[] = $currentseason->roll();
                if(count($results) >= 1) {
                    $finalweather = array_values($results[0]["weathers"])[0]["asset"]->id;
                    DB::table('site_settings')->where('key', 'site_weather')->update(['value' => $finalweather]);
                    $this->info('Weather adjusted successfully.');
                } else {
                    $this->info('No valid weather found!');
                }
        }
        if(Settings::get('site_weather_cycle') == 2) {
            //weekly reset setting
            $now = Carbon::now();
                $day = $now->dayOfWeek;
                if($day == 1) { $results = [];
                    $results[] = $currentseason->roll(); 
                    if(count($results) >= 1) {
                        $finalweather = array_values($results[0]["weathers"])[0]["asset"]->id;
                        DB::table('site_settings')->where('key', 'site_weather')->update(['value' => $finalweather]);
                        $this->info('Weather adjusted successfully.');
                    } else {
                        $this->info('No valid weather found!');
                    }
                }
        }
        if(Settings::get('site_weather_cycle') == 3) {
            //monthly reset
                $now = Carbon::now();
                $day = $now->day;
                if($day == 1) {
                    $results = [];
                    $results[] = $currentseason->roll(); 
                if(count($results) >= 1) {
                    $finalweather = array_values($results[0]["weathers"])[0]["asset"]->id;
                    DB::table('site_settings')->where('key', 'site_weather')->update(['value' => $finalweather]);
                    $this->info('Weather adjusted successfully.'); 
                } else {
                    $this->info('No valid weather found!');
                }
            }
        }
        } else {
            $this->info('No valid season is set.');
        }
    }
}
