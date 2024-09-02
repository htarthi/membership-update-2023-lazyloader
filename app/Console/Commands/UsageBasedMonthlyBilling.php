<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Osiset\ShopifyApp\Services\ChargeHelper;
use App\Models\User;
use App\Traits\ShopifyTrait;
class UsageBasedMonthlyBilling extends Command
{
    use ShopifyTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage:monthlybilling';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "This command is used to create monthly usage charge for merchant's members.";

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
            logger("=============== START :: UsageBasedMonthlyBilling ===============");
            $chs = resolve(ChargeHelper::class);
            $users = User::where('active', 1)->where('password', '!=', null)->where('deleted_at', null)->where('plan_id', '!=', null)->get();
            foreach ($users as $ukey => $user) {
                $plan = $user->plan;
                $exist_shop = $this->getShop($user);
                if (!$exist_shop) {
                    $dbCharge = $chs->chargeForPlan($plan->getId(), $user);
                    if ($dbCharge) {
                        $chs->useCharge($dbCharge->getReference());
                        $chargeData = $chs->retrieve($user);
                        $billingDateObj = \Carbon\Carbon::parse($chargeData->billing_on);
                        // logger('Billing on obj :: ' . $billingDateObj);
                        // logger('Is Today :: ' . $billingDateObj->isToday());
                        if ($billingDateObj->isToday()) {
                            // logger('Billing ON :: ' . $chargeData->billing_on);
                            // logger('Today :: ' . $chargeData->billing_on);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            logger("=============== ERROR :: UsageBasedMonthlyBilling ===============");
            logger($e);
        }
        return 0;
    }

    // public function getShop($user){
    //     try{
    //         $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/shop.json';
    //         $parameter['fields'] = 'id';
    //         $result = $user->api()->rest('GET', $endPoint, $parameter);
    //         return $result['errors'];
    //     }catch( \Exception $e ){
    //         logger('================= ERROR:: getShop =================');
    //         logger($e->getMessage());
    //         return true;
    //     }
    // }
}
