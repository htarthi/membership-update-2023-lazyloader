<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\SsContract;
use App\Models\SsPlan;
use App\Models\SsSetting;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class HandleCancelMembership extends Command
{
    use ShopifyTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'handle:cancelmembership';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When a member cancels their membership, they have already paid to be a member until their next billing date, so we want to delay removing their customer tag until their next billing date (when the membership would renew).';

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
        logger('=============== START::HandleCancelMembership ==============');
        try {
            $oneTimeContracts = SsContract::select('id', 'status', 'user_id', 'ss_customer_id', 'shopify_customer_id', 'tag_customer')->where('is_onetime_payment', 1)->where('status', 'active')->where('status_display', "!=", 'Access Removed')->where('next_processing_date', '<', Carbon::now())->get();
            foreach ($oneTimeContracts as $key => $value) {
                $value->status = 'cancelled';
                $value->status_display = 'Access Removed';
                $user = User::find($value->user_id);
                if ($user) {
                    $this->checkForActiveMemberTag($user, $value->shopify_customer_id, $value->tag_customer);
                    $this->saveActivity($user->id, $value->ss_customer_id, $value->id, 'system', 'Membership was cancelled (and access removed) manually.');
                    $value->save();
                } else {
                    logger("========== PROBLEM :: HandleCancelMembership.php :: User not found with user_id :: $value->user_id for contract id :: $value->id");
                }
            }

            // check for cancelled contracts and remove there access
            $contracts = SsContract::select('id', 'user_id', 'shopify_customer_id', 'tag_customer')->where('status', 'cancelled')->where('next_order_date', '<', Carbon::now())->where('tag_customer', '!=', '')->get();

            if (count($contracts) > 0) {
                foreach ($contracts as $key => $val) {
                    $user = User::find($val->user_id);

                    if ($user) {
                        $shop = Shop::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
                        $val->status_display = 'Access Removed';
                        $val->save();
                        $this->checkForActiveMemberTag($user, $val->shopify_customer_id, $val->tag_customer);
                        $setting = SsSetting::select('notify_revoke', 'notify_email')->where('shop_id', $shop->id)->first();
                        if ($setting->notify_paymentfailed && $setting->notify_email != '') {
                            $notifyData = config('notify-mails.notify_revoke');
                            $newData = $this->fetchContractFormFields($val->id, $notifyData['body']);
                            $db_ss_plan = SsPlan::select('name')->where('id', $val->ss_plan_id)->first();
                            $planData['next_billing_date'] = $val->next_processing_date;
                            $planData['membership_plan'] = ($db_ss_plan) ? $db_ss_plan->name : '';
                            $notifyMailRes = sendMailH(
                                $notifyData['subject'],
                                $newData,
                                config('notify-mails.notify_from_email'),
                                $setting->notify_email,
                                config('notify-mails.notify_from_name'),
                                $val->shop_id,
                                $val->ss_customer_id,
                                $planData
                            );
                            logger('======= NOtify mail response ======');
                            logger($notifyMailRes);
                        }
                    }
                }
            }
            logger('=============== END::HandleCancelMembership ==============');
        } catch (\Exception $e) {
            logger('=============== ERROR:: Handle cancel membership cron ==============');
            logger($e);
        }
        return 0;
    }
}
