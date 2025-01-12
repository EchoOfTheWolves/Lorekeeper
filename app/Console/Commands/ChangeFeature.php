<?php

namespace App\Console\Commands;

use DB;
use Settings;
use Log;
use Illuminate\Console\Command;
use App\Models\Character\Character;

class ChangeFeature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change-feature';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes current featured character.';

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
     * @return mixed
     */
    public function handle()
    {
        $id = Character::myo()->random()->id;
        $setting = Settings::get('featured_character');
        while($id == $setting) {
            $id = Character::myo()->random()->id;
        }

        DB::table('site_settings')->where('key', 'featured_character')->update(['value' => $id]);
    }
}
