<?php

namespace App\Console\Commands;

use App\Models\SsSetting;
use App\Models\SsUsageFee;
use App\Traits\ShopifyTrait;
use Illuminate\Console\Command;
use Osiset\ShopifyApp\Storage\Models\Charge;
use App\Models\User;
use App\Models\Shop;
use Carbon\Carbon;

class UsageCharge extends Command
{
    use ShopifyTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usage:charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create usage charge';

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
        logger('=============== START:: UsageCharge ==============');
        try {
            $status = ['active', 'paused'];

            $users = User::select(\DB::raw('count(*) as contract_count, users.id as user_id'))
                ->join('shops', 'shops.user_id', '=', 'users.id')
                ->join('charges', 'charges.user_id', '=', 'users.id')
                ->join('ss_contracts', 'ss_contracts.user_id', '=', 'users.id')
                ->where('shops.test_store', 0)
                ->where('charges.status', 'ACTIVE')
                ->whereIn('ss_contracts.status', $status)
                ->groupBy('users.id')
                ->get();

            if (count($users) > 0) {
                foreach ($users as $key => $val) {
                    $shop = Shop::select('id', 'member_count')->where('user_id', $val->user_id)->orderBy('created_at', 'desc')->first();
                    if ($val->contract_count > $shop->member_count) {
                        $setting = SsSetting::select('member_fee')->where('shop_id', $shop->id)->orderBy('created_at', 'desc')->first();
                        $extra_users = ((int)$val->contract_count - (int)$shop->member_count);
                        $member_fee = ($setting->member_fee * 100);
                        $price = ($extra_users * $member_fee);
                        $charge = Charge::where('user_id', $val->user_id)->where('status', 'ACTIVE')->orderBy('created_at', 'desc')->first();
                        $result = $this->createUsageCharge($val->user_id, $price, $extra_users, $member_fee, $charge->charge_id);
                        if (!$result['errors']) {
                            $usageFee = new SsUsageFee;
                            $usageFee->shop_id = $shop->id;
                            $usageFee->charge_id = $charge->charge_id;
                            $usageFee->charge_amount = number_format($price, 2);
                            $usageFee->member_count = $extra_users;
                            $usageFee->save();
                            $shop->member_count = $val->contract_count;
                            $shop->save();
                        }
                    }
                }
            }
            logger('=============== END:: UsageCharge ==============');
        } catch (\Exception $e) {
            logger('=============== ERROR:: UsageCharge ==============');
            logger($e);
        }
        return 0;
    }

    /**
     * @param $user_id
     */
    public function createUsageCharge($user_id, $price, $extra_users, $member_fee, $shChargeId)
    {
        try {
            logger('=============== START:: createUsageCharge ==============');
            $user = User::find($user_id);
            $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/recurring_application_charges/' . $shChargeId . '/usage_charges.json';
            $now = Carbon::now()->format('M d Y');
            $charge = [
                "usage_charge" => [
                    "description" => "Member fees for an additional $extra_users members at $$member_fee per member",
                    "price" => number_format($price, 2),
                ],
            ];
            $result = $user->api()->rest('POST', $endPoint, $charge);
            logger('=============== END:: createUsageCharge ==============');
            // logger(json_encode($result));
            return $result;
        } catch (\Exception $e) {
            logger('=============== ERROR:: createUsageCharge ==============');
            logger($e);
        }
    }
}
