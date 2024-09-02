<?php

namespace App\Listeners;

use App\Events\CheckBillingAttemptFailure;
use App\Models\Shop;
use App\Models\SsActivityLog;
use App\Models\SsBillingAttempt;
use App\Models\SsContract;
use App\Models\SsContractLineItem;
use App\Models\SsEmail;
use App\Models\SsPlan;
use App\Models\SsSetting;
use App\Models\SsWebhook;
use App\Models\SsCustomer;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Http;

class BillingAttemptFailure
{
    use ShopifyTrait;

    private $statuswebhook_id = '';
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CheckBillingAttemptFailure  $event
     * @return void
     */
    public function handle(CheckBillingAttemptFailure $event)
    {
        try {
            $ids = $event->ids;
            $user = User::find($ids['user_id']);
            $shop = Shop::find($ids['shop_id']);
            $this->statuswebhook_id = $ids['webhook_id'];
            // $webhookResonse = SsWebhook::find($ids['webhook_id']);

            // if ($webhookResonse) {
            $data = json_decode($ids['payload']);

            logger('========== Listener:: BillingAttemptFailure :: Webhook :: ' . $ids['webhook_id'] . ' ==> shopify_id :: ' . $data->id . '==========');

            //update billing attempt
            // $ssBillingAttempt = SsBillingAttempt::where('shop_id', $shop->id)->where('shopify_id', $data->id)->first();
            // if ($ssBillingAttempt) {
            //     $ssBillingAttempt->status = 'failed';
            //     $ssBillingAttempt->errorMessage = $data->error_message;
            //     $ssBillingAttempt->completedAt = date('Y-m-d H:i:s');
            //     $ssBillingAttempt->save();
            // }

            $affected = \DB::table('ss_billing_attempts')
                ->where('shop_id', $shop->id)
                ->where('shopify_id', $data->id)
                ->update([
                    'status' => 'failed',
                    'completedAt' => date('Y-m-d H:i:s'),
                    'errorMessage' => $data->error_message
                ]);

            $setting = SsSetting::select('dunning_retries', 'dunning_daysbetween', 'dunning_failedaction', 'dunning_email_enabled', 'email_from_email', 'email_from_name', 'notify_paymentfailed', 'notify_email')->where('shop_id', $shop->id)->first();
            if ($setting) {
                $ssContract = SsContract::where('shop_id', $shop->id)->where('shopify_contract_id', $data->subscription_contract_id)->first();
                if ($ssContract) {
                    $ssContract->status_billing = 'failed';
                    $contract_failed_payment_count = $ssContract->failed_payment_count;
                    $setting_dunning_retries = $setting->dunning_retries;

                    if (($setting_dunning_retries == 0 && $contract_failed_payment_count == 0) || $contract_failed_payment_count == ($setting_dunning_retries - 1)) {
                        $ssContract->failed_payment_count = $ssContract->failed_payment_count + 1;

                        $ss_activity_log = new SsActivityLog;
                        $ss_activity_log->shop_id = $shop->id;
                        $ss_activity_log->user_id = $user->id;
                        $ss_activity_log->ss_contract_id = $ssContract->id;
                        $ss_activity_log->ss_customer_id = $ssContract->ss_customer_id;
                        $ss_activity_log->user_type = 'System';
                        $ss_activity_log->user_name = $shop->owner;

                        if ($setting->dunning_failedaction == 'cancel') {
                            $ssContract->status = 'cancelled';
                            $ssContract->status_display = 'Billing Failed';
                            $ss_activity_log->message = "Subscription [contract #$data->subscription_contract_id] cancelled after reaching max number of failed billing attempts";

                            // remove customer tag from shopify if membership is no longer active
                            $this->checkForActiveMemberTag($user, $ssContract->shopify_customer_id, $ssContract->tag_customer);
                        } elseif ($setting->dunning_failedaction == 'pause') {
                            $ssContract->status = 'paused';
                            $ss_activity_log->message = "Subscription [contract #$data->subscription_contract_id] paused after reaching max number of failed billing attempts";
                        } elseif ($setting->dunning_failedaction == 'skip') {

                            $next_order_date = date('Y-m-d H:i:s', strtotime($ssContract->next_order_date . ' + ' . $ssContract->billing_interval_count . ' ' . strtolower($ssContract->billing_interval)));

                            $ssContract->next_order_date = $next_order_date;
                            $ssContract->next_processing_date = $next_order_date;

                            $ss_activity_log->message = "Subscription [contract #$data->subscription_contract_id] skipped an order after reaching max number of failed billing attempts";
                        }
                        $ssContract->save();
                        $ss_activity_log->save();

                        if ($setting->dunning_failedaction == 'skip') {
                            $result = $this->subscriptionContractSetNextBillingDate($shop->user_id, $ssContract->shopify_contract_id);
                        } else if ($setting->dunning_failedaction == 'cancel' || $setting->dunning_failedaction == 'pause') {
                            $result = $this->updateSubscriptionContract($shop->user_id, $ssContract->id);
                        }
                    } else if ($contract_failed_payment_count >= 0 && $contract_failed_payment_count < $setting_dunning_retries) {
                        $increaseDay = $setting->dunning_daysbetween;
                        $ssContract->failed_payment_count = $ssContract->failed_payment_count + 1;
                        $ssContract->next_processing_date = date('Y-m-d H:i:s', strtotime($ssContract->next_processing_date . ' + ' . $increaseDay . ' day'));
                        $ssContract->save();

                        //send mail
                        if ($setting->dunning_email_enabled) {
                            $email = SsEmail::where('shop_id', $shop->id)->where('category', 'failed_payment_to_customer')->first();
                            $customer = SsCustomer::select('email')->where('shop_id', $shop->id)->where('shopify_customer_id', $ssContract->shopify_customer_id)->first();

                            $htmlBody = $email->html_body;
                            $newHtml = str_replace('[CARD_TYPE]', $ssContract->cc_brand, $htmlBody);
                            $newHtml = str_replace('[EXPIRY_DATE]', substr('0' . $ssContract->cc_expiryMonth, -2) . '/' . substr($ssContract->cc_expiryYear, 2), $newHtml);

                            $res = sendMailH($email->subject, $newHtml, $setting->email_from_email, $customer->email, $setting->email_from_name, $shop->id, $customer->id);
                            // logger('============ Failed payment customer email ============');
                            // logger(json_encode($res));
                        }
                    } else if (($contract_failed_payment_count >= $setting_dunning_retries) && $setting->dunning_failedaction == 'skip') {
                        $ssContract->failed_payment_count = $ssContract->failed_payment_count + 1;

                        $ss_activity_log = new SsActivityLog;
                        $ss_activity_log->shop_id = $shop->id;
                        $ss_activity_log->user_id = $user->id;
                        $ss_activity_log->ss_contract_id = $ssContract->id;
                        $ss_activity_log->ss_customer_id = $ssContract->ss_customer_id;
                        $ss_activity_log->user_type = 'System';
                        $ss_activity_log->user_name = $shop->owner;

                        $next_order_date = date('Y-m-d H:i:s', strtotime($ssContract->next_order_date . ' + ' . $ssContract->billing_interval_count . ' ' . strtolower($ssContract->billing_interval)));

                        $ssContract->next_order_date = $next_order_date;
                        $ssContract->next_processing_date = $next_order_date;

                        $ss_activity_log->message = "Subscription [contract #$data->subscription_contract_id] skipped an order after reaching max number of failed billing attempts";

                        $ssContract->save();
                        $ss_activity_log->save();

                        $result = $this->subscriptionContractSetNextBillingDate($shop->user_id, $ssContract->shopify_contract_id);
                    } else if ($contract_failed_payment_count > $setting_dunning_retries) {
                        // cancel the contract if contract_failed_payment_count is greater than retries
                        $ss_activity_log = new SsActivityLog;
                        $ss_activity_log->shop_id = $shop->id;
                        $ss_activity_log->user_id = $user->id;
                        $ss_activity_log->ss_contract_id = $ssContract->id;
                        $ss_activity_log->ss_customer_id = $ssContract->ss_customer_id;
                        $ss_activity_log->user_type = 'System';
                        $ss_activity_log->user_name = $shop->owner;

                        // if ($setting->dunning_failedaction == 'cancel') {
                        $ssContract->status = 'cancelled';
                        $ss_activity_log->message = "Subscription [contract #$data->subscription_contract_id] cancelled after reaching max number of failed billing attempts";

                        $ssContract->save();
                        $ss_activity_log->save();
                        $result = $this->updateSubscriptionContract($shop->user_id, $ssContract->id);
                        // }
                    }

                    // mail for some error code
                    $error_codes = ['authentication_error', 'customer_invalid', 'customer_not_found', 'invalid_shipping_address', 'test_mode'];

                    if (in_array($data->error_code, $error_codes)) {
                        // send mail for need attention

                        $subject = 'Error ' . $data->error_code . ' needs attention';
                        $newData = 'Merchant URL: ' . $user->name . ' <br>
                                        Contract ID: ' . $data->subscription_contract_id . ' <br>
                                        Billing Attempt ID: ' . $data->id . ' <br>
                                        ErrorCode: ' . $data->error_code . ' <br>
                                        ErrorMessage: ' . $data->error_message;

                        $attentionMailRes = sendMailH($subject, $newData, $setting->email_from_email, config('notify-mails.failed_payment_to_email'), $setting->email_from_name, $ssContract->shop_id, $ssContract->ss_customer_id, []);
                        // logger('======= Attention Mail response ======');
                        // logger($attentionMailRes);
                    }
                    //                        notify mail to given email(user) for failed payment in setting tab
                    if ($setting->notify_paymentfailed && $setting->notify_email != '') {
                        $notifyData = config('notify-mails.notify_paymentfailed');

                        $newData = $this->fetchContractFormFields($ssContract->id, $notifyData['body']);

                        $db_ss_plan = SsPlan::select('name')->where('id', $ssContract->ss_plan_id)->first();
                        $planData['next_billing_date'] = $ssContract->next_processing_date;
                        $planData['membership_plan'] = ($db_ss_plan) ? $db_ss_plan->name : '';

                        $notifyMailRes = sendMailH($notifyData['subject'], $newData, config('notify-mails.notify_from_email'), $setting->notify_email, config('notify-mails.notify_from_name'), $ssContract->shop_id, $ssContract->ss_customer_id, $planData);
                        // logger('======= NOtify mail response ======');
                        // logger($notifyMailRes);
                    }

                    // Shopify Flow - Membership Payment Failed

                    $ss_cotract_line_item = SsContractLineItem::where('ss_contract_id', $ssContract->id)->first();

                    if ($ss_cotract_line_item) {
                        $condition = $setting->dunning_retries == $ssContract->failed_payment_count ? 'true' : 'false';
                        // $this->flowTrigger(
                        //     config('const.SHOPIFY_FLOW.PAYMENT_FAIL'),
                        //     env('APP_TRIGGER_URL'),
                        //     '
                        //         {
                        //             \"customer_id\": ' . $ssContract->ss_customer_id . ',
                        //             \"product_id\": ' . $ss_cotract_line_item->shopify_product_id . ',
                        //             \"Next Billing Attempt\": \"' . $ssContract->next_processing_date . '\",
                        //             \"Final Attempt\": ' . $condition . ',
                        //             \"Failure Count\": ' . $ssContract->failed_payment_count . '
                        //         }
                        //     ',
                        //     $user
                        // );
                        Http::post(env('APP_TRIGGER_URL').'/api/callFlowTrigger', [
                            'action' => "payment_fail" ,
                            'customer_id' => $ssContract->ss_customer_id ,
                            'product_id' => $ss_cotract_line_item->shopify_product_id,
                            'next_processing_date' => $ssContract->next_processing_date ,
                            'condition' => $condition ,
                            'failed_payment_count' => $ssContract->failed_payment_count ,
                            'uid' =>  $user->id ,
                        ]);
                        logger('------ Shopify Flow - Membership Payment Failed :: Api called');
                    } else {
                        logger('------ Shopify Flow - Membership Payment Failed :: SsContractLineItem not found');
                    }
                }
            }
            // }
            $this->updateWebhookStatus($this->statuswebhook_id, 'processed', null);
        } catch (\Exception $e) {
            logger('========== ERROR:: Listener:: BillingAttemptFailure ==========');
            logger($e);
            $this->updateWebhookStatus($this->statuswebhook_id, 'error', $e);
            Bugsnag::notifyException($e);
        }
    }
}
