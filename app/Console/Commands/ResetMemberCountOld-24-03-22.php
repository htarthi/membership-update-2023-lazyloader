<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Osiset\ShopifyApp\Storage\Models\Charge;

class ResetMemberCounttt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resettt:membercounttt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset member count when billing cycle is going to reset every 30 days.';

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
        try{
            logger("=============== START :: ResetMemberCount ===============");

            $users = User::all();
            foreach ($users as $ukey=>$uval){
                $dbCharge = Charge::where('user_id', $uval->id)->where('status', 'ACTIVE')->orderBy('created_at', 'desc')->first();
                if($dbCharge){
                    $shop = Shop::where('user_id', $uval->id)->orderBy('created_at', 'desc')->first();

                    $currDate = date('Y-m-d H:i:s');
                    $lastUpdateAt = date('Y-m-d H:i:s', strtotime($shop->member_count_update_at));

                    $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $currDate);
                    $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $lastUpdateAt);
                    $diff_in_days = $to->diffInDays($from);

                    // logger($shop->myshopify_domain);
                    // logger('$to :: ' . $to . ' ==> $from :: ' . $from);
                    // logger('$diff_in_days :: ' . $diff_in_days);
                    // logger('fmod($diff_in_days, 30) :: ' . fmod($diff_in_days, 30));
                    // logger(fmod($diff_in_days, 30) == 0);

//                            update member count
                    if($diff_in_days > 27){
                        if(fmod($diff_in_days, 30) == 0){

                            $isUpdate = true;
                            if($shop->member_count_update_at){
                                $updateAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $shop->member_count_update_at);
                                $diff_in_days = $to->diffInDays($updateAt);

                                // if($diff_in_days < 27){
                                //     $isUpdate = false;
                                // }
                                logger('$updateAt :: ' . $updateAt . ' ==> $diff_in_days UpdateAt :: ' . $diff_in_days);
                            }

                            if($isUpdate){
                                logger("=============== update member count for :: ".$shop->myshopify_domain." ===============");
                                $oldCounter = $shop->member_count;
                                $shop->member_count = 0;
                                $shop->member_count_update_at = date('Y-m-d H:i:s');
                                $shop->save();

                              logger("============== OLD COUNTER :: [".$oldCounter ."] ==> NEW COUNTER :: [".$shop->member_count."]===============");
                            }
                        }
                    }
                }
            }
             logger("=============== END :: ResetMemberCount ===============");
        }catch(\Exception $e){
            Bugsnag::notifyException($e);
            logger("=============== ERROR :: ResetMemberCount ===============");
            logger($e);
        }
        return 0;
    }
}
