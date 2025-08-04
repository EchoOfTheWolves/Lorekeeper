<?php

namespace App\Console\Commands;

use App\Models\User\UserSettings;
use Illuminate\Console\Command;

class ResetHigherOrLower extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset-hol';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'resets daily plays for higher or lower.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $defaultPlays = config('lorekeeper.hol.hol_plays');
        $users = UserSettings::where('hol_plays', '<', $defaultPlays);

        if ($users->count() > 0) {
            $this->info('Resetting HoL plays for '.$users->count().' users...');
            $users->update(['hol_plays' => $defaultPlays]);
            $this->info('HoL plays have been reset.');
        }
    }
}
