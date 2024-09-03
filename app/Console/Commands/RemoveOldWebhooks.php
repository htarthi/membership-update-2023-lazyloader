<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveOldWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:oldwebhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Every day at 7am UTC,deletes all old webhooks in the database. Only keep 500,000 records.';

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
            logger('============== START:: RemoveOldWebhooks =============');
            $skip = 500000;
            $count = DB::table('ss_webhooks')->count();
            logger("=================== All Webhooks Count :: $count ==================");
            if ($count > $skip) {
                logger("================== Found more then 500000 records ====================");
                $limit = $count - $skip;
                $webhookIds = DB::table('ss_webhooks')->latest()->take($limit)->skip($skip)->select('id')->get()->pluck('id');
                $res = DB::table('ss_webhooks')->whereIN('id', $webhookIds)->delete();
                logger("================ Deleted extra records. ===================");
            }
        } catch (\Exception $e) {
            logger('============== ERROR:: RemoveOldWebhooks =============');
            logger($e);
        }
        return 0;
    }
}
