<?php

namespace App\Console\Commands;

use App\Traits\ShopifyTrait;
use App\Models\User;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Osiset\ShopifyApp\Services\ChargeHelper;
use Osiset\ShopifyApp\Storage\Models\Charge;
class TrackCharge extends Command
{
    use ShopifyTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will track charge of all users and update billing on date if needed';

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
            logger("=============== START :: TrackCharge ===============");
            $chs = resolve(ChargeHelper::class);
            // logger(json_encode($chs));
            $shops = User::all();
            foreach ($shops as $shop) {
                if ($shop->password) {
                    $plan = $shop->plan;
                    if ($plan === null) {
                        continue;
                    }
                    $charge_shop = Charge::where('plan_id', $plan->id)->where('user_id', $shop->id)->where('status', 'ACTIVE')->first();
                    if ($charge_shop === null) {
                        continue;
                    }
                    $billing_on = $charge_shop->billing_on;
                    $current_date = date('Y-m-d H:i:s');
                    $date1 = new DateTime($billing_on);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $next_billing_for = $interval->days;

                    if ($date1 >= $date2 && $next_billing_for <= 5) {
                        $exist_shop = $this->getShop($shop);
                        // logger('================= $exist_shop ::  ===============');
                        // logger($exist_shop);
                        if (!$exist_shop) {
                            // logger('================= shop exist ===============');
                            $dbCharge = $chs->chargeForPlan($plan->getId(), $shop);
                            if ($dbCharge) {
                                // logger('================= charge exist ===============');
                                // logger(json_encode($dbCharge));
                                $chs->useCharge($dbCharge->getReference());
                                $chargeData = $chs->retrieve($shop);
                                // logger('================= chargeData ===============');
                                // logger(json_encode($chargeData));
                                if ($chargeData->status == 'active') {
                                    DB::beginTransaction();
                                    $dbCharge->billing_on = date('Y-m-d H:i:s', strtotime($chargeData->billing_on));
                                    $dbCharge->save();
                                    DB::commit();
                                }
                            }
                        }
                    }
                }
            }
            logger("=============== END :: TrackCharge ===============");
        } catch (\Exception $e) {
            DB::rollBack();
            logger("=============== ERROR :: TrackCharge ===============");
            logger(json_encode($e));
        }
        return 0;
    }

    public function getShop($user)
    {
        try {
            logger('================= START:: getShop =================');
            $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/shop.json';
            $parameter['fields'] = 'id';
            $result = $user->api()->rest('GET', $endPoint, $parameter);
            logger('================= END:: getShop =================');
            return $result['errors'];
        } catch (\Exception $e) {
            logger('================= ERROR:: getShop =================');
            logger($e->getMessage());
            return true;
        }
    }
}
