<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Weather\WeatherSeason;
use DB;
use Carbon\Carbon;

class change_site_season extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change-site-season';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the site season.';

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
       //change the season
       $updateto = WeatherSeason::whereNotNull('cycle_at')->where('cycle_at', '<', Carbon::now())->whereNotNull('end_at')->where('end_at', '>', Carbon::now())->first();

       if(isset($updateto)) {
            DB::table('site_settings')->where('key', 'site_season')->update(['value' => $updateto->id]);
            $this->info('Season adjusted successfully.');
        } else {
            $this->info('No seasons found!');
        }

    }
}
