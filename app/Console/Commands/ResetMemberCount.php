<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Osiset\ShopifyApp\Storage\Models\Charge;
use App\Models\SsSetting;
use App\Models\SsUsageFee;
use Carbon\Carbon;

class ResetMemberCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:membercount';

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
        try {
            logger("=============== START :: ResetMemberCount ===============");
            $currentDate = date('Y-m-d');
            $status = ['active', 'cancelled'];
            $users = User::select(DB::raw('count(*) as contract_count, users.id as user_id, users.name'))
                ->join('shops', 'shops.user_id', '=', 'users.id')
                ->join('charges', 'charges.user_id', '=', 'users.id')
                ->join('ss_contracts', 'ss_contracts.user_id', '=', 'users.id')
                ->where('shops.test_store', 0)
                ->where('users.active', 1)
                ->where('users.plan_id', '!=', null)
                ->where('charges.status', 'ACTIVE')
                ->whereIn('ss_contracts.status', $status)
                ->where('ss_contracts.status_display', '!=', 'Access Removed')
                ->where('ss_contracts.next_processing_date', '>', $currentDate)
                ->groupBy('users.id')
                ->get();

            foreach ($users as $ukey => $uval) {
                $user = User::find($uval->user_id)->first();
                $shop = Shop::where('user_id', $uval->user_id)->orderBy('created_at', 'desc')->first();
                $shop->member_count = $uval->contract_count;
                $shop->save();

                $dbCharge = Charge::where('user_id', $uval->user_id)->where('status', 'ACTIVE')->orderBy('created_at', 'desc')->first();
                if ($dbCharge) {
                    $currDate = date('Y-m-d H:i:s');
                    $lastUpdateAt = date('Y-m-d', strtotime($shop->member_count_update_at)) . ' ' . date('H:i:s', strtotime($currDate));
                    $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $currDate);
                    $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $lastUpdateAt);
                    $diff_in_days = $to->diffInDays($from);
                    $r = (fmod($diff_in_days, 30) == 0);
                    // logger("Shop :: $uval->name -----------> ( From :: $from ) ( To :: $to ) ( diff_in_days :: $diff_in_days ) ( fmod($diff_in_days, 30) == 0  :: $r) ( Contract count :: $uval->contract_count )");
                    if ($diff_in_days > 27) {
                        if (fmod($diff_in_days, 30) == 0) {
                            $isUpdate = true;
                            if ($shop->member_count_update_at) {
                                $updateAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $shop->member_count_update_at);
                                $diff_in_days = $to->diffInDays($updateAt);
                            }
                            if ($isUpdate) {
                                // logger("=============== create usage charge for :: " . $shop->myshopify_domain . " ===============");
                                if ($uval->contract_count > 0) {
                                    $setting = SsSetting::select('member_fee')->where('shop_id', $shop->id)->orderBy('created_at', 'desc')->first();
                                    $total_members = $uval->contract_count;
                                    $member_fee = ($setting->member_fee * 100);
                                    $price = ($total_members * $member_fee);
                                    $result = $this->createUsageCharge($uval->user_id, $price, $total_members, $member_fee, $dbCharge->charge_id);
                                    if (!$result['errors']) {
                                        $usageFee = new SsUsageFee;
                                        $usageFee->shop_id = $shop->id;
                                        $usageFee->charge_id = $dbCharge->charge_id;
                                        $usageFee->charge_amount = number_format($price, 2);
                                        $usageFee->member_count = $total_members;
                                        $usageFee->save();
                                    }
                                }
                                $shop->member_count_update_at = date('Y-m-d H:i:s');
                                $shop->save();
                            }
                        }
                    }
                }
            }
            logger("=============== END :: ResetMemberCount ===============");
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("=============== ERROR :: ResetMemberCount ===============");
            logger($e);
        }
        return 0;
    }

    /**
     * @param $user_id
     */
    public function createUsageCharge($user_id, $price, $total_members, $member_fee, $shChargeId)
    {
        try {
            logger('=============== START:: createUsageCharge ==============');
            $user = User::find($user_id);
            $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/recurring_application_charges/' . $shChargeId . '/usage_charges.json';
            $now = Carbon::now()->format('M d Y');
            $charge = [
                "usage_charge" => [
                    "description" => " Monthly member fee for $total_members members - $$member_fee per member",
                    "price" => number_format($price, 2),
                ],
            ];
            $result = $user->api()->rest('POST', $endPoint, $charge);
            // logger(json_encode($result));
            if ($result['errors']) {
                logger(json_encode($result));
            }
            logger('=============== END:: createUsageCharge ==============');
            return $result;
        } catch (\Exception $e) {
            logger('=============== ERROR:: createUsageCharge ==============');
            logger($e);
        }
    }
}
