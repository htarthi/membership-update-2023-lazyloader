<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SsTrackContract;
class TrackContract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:contracts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will track contracts of all users and Create or record in database if not being created in app';

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
        try {
            logger('================= START:: TrackContract =================');
            $contracts = SsTrackContract::orderBy('created_at', 'desc')->distinct('user_id')->get();
            logger('================= END:: TrackContract =================');
        } catch (\Exception $e) {
            logger('================= ERROR:: TrackContract =================');
        }
    }
}
