<?php

namespace App\Listeners;

use App\Events\CheckBillingAttemptSuccess;
use App\Models\Shop;
use App\Models\SsBillingAttempt;
use App\Models\SsContract;
use App\Models\SsSetting;
use App\Models\SsWebhook;
use App\Models\SsContractLineItem;
use App\Models\SsCustomer;
use App\Models\SsPlan;
use Illuminate\Support\Carbon;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use App\Models\SsStoreCredit;
use Illuminate\Support\Facades\Http;

class BillingAttemptSuccess
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
     * @param  CheckBillingAttemptSuccess  $event
     * @return void
     */
    public function handle(CheckBillingAttemptSuccess $event)
    {
        try {
            $ids = $event->ids;
            $user = User::find($ids['user_id']);
            $shop = Shop::find($ids['shop_id']);

            $this->statuswebhook_id = $ids['webhook_id'];
            $data = json_decode($ids['payload']);

            logger('========== Listener:: BillingAttemptSuccess :: Webhook :: ' . $ids['webhook_id'] . ' ==> shopify_id :: ' . $data->id . '==========');
            // update billing attempt
            // $ssBillingAttempt = SsBillingAttempt::where('shop_id', $shop->id)->where('shopify_id', $data->id)->first();
            // logger(json_encode($ssBillingAttempt));

            $sh_order_id = ($data->order_id) ? $data->order_id : null;
            $affected = \DB::table('ss_billing_attempts')
                ->where('shop_id', $shop->id)
                ->where('shopify_id', $data->id)
                ->update([
                    'status' => 'successful',
                    'completedAt' => date('Y-m-d H:i:s'),
                    'shopify_order_id' => $sh_order_id,
                    'updated_at' => date('Y-m-d H') . ':01:' . date('s')
                ]);
            // if ($ssBillingAttempt) {
            //     $ssBillingAttempt->status = 'successful';
            //     $ssBillingAttempt->completedAt = date('Y-m-d H:i:s');
            //     $ssBillingAttempt->shopify_order_id = ($data->order_id ) ? $data->order_id : null;
            //     $ssBillingAttempt->updated_at = date('Y-m-d H') . ':01:' . date('s');
            //     $ssBillingAttempt->save();
            // }
            // update contract
            $ssContract = SsContract::where('shop_id', $shop->id)->where('shopify_contract_id', $data->subscription_contract_id)->first();
            if ($ssContract) {

                // $nextDate = $this->getSubscriptionTimeDate(date("Y-m-d",
                // strtotime($ssContract->next_order_date)), $shop->id);
                $next_order_date = date("Y-m-d H:i:s", strtotime($ssContract->next_order_date . ' + ' . $ssContract->billing_interval_count . ' ' . strtolower($ssContract->billing_interval)));
                // $next_order_date = date("Y-m-d $addtime", strtotime($ssContract->next_order_date . ' + ' .       $ssContract->billing_interval_count . ' ' . strtolower($ssContract->billing_interval)));
                if (isset($ssContract->delivery_cutoff) || isset($ssContract->billing_anchor_type)) {
                    $next_order_date = $this->calculateNextOrderDate($ssContract, $shop);
                }
                $ssContract->status_billing = 'successful';
                $ssContract->error_state = null;
                $ssContract->next_order_date = $next_order_date;
                $ssContract->next_processing_date = $next_order_date;
                $ssContract->last_billing_order_number = $data->order_id;
                $ssContract->order_count = ($ssContract->order_count) ? $ssContract->order_count + 1 : 1;
                $ssContract->failed_payment_count = 0;
                if ($ssContract->on_trial) {
                    $ssContract->on_trial = 0;
                }
                $ssContract->save();
                // update order count in note_attributes
                $this->updateShopifyNoteAttributes($user, $data->order_id, 'Membership Order Count', $ssContract->order_count, 'order');
                // update status to expired if maximum order count is reached
                if ($ssContract->billing_max_cycles) {
                    if ($ssContract->order_count >= $ssContract->billing_max_cycles) {
                        $ssContract->status = 'cancelled';
                        $ssContract->status_display = 'Expired';
                        $ssContract->save();
                        $result = $this->updateSubscriptionContract($shop->user_id, $ssContract->id);
                        $this->saveActivity($shop->user_id, $ssContract->ss_customer_id, $ssContract->id, 'system', 'Membership has expired, maximum number of orders reached');
                        // remove customer tag from shopify if membership is no longer active
                        $this->checkForActiveMemberTag($user, $ssContract->shopify_customer_id, $ssContract->tag_customer);
                    }
                }

                $ssPlan = SsPlan::where('id', $ssContract->ss_plan_id)->first();
                logger("==============================STORE CREDIT START=============================");
                if ($ssPlan && $ssPlan->store_credit) {
                    $is_exist_store_credit = SsStoreCredit::where(['shop_id' => $ssPlan->shop_id, 'ss_customer_id' =>  $ssContract->ss_customer_id])->first();
                    if ($ssPlan->store_credit_frequency == 'all_orders') {
                        $this->createstoreCredit($ssPlan->user_id, $ssContract->shopify_customer_id, $ssPlan->store_credit_amount, $shop->currency);

                        $store__credit = ($is_exist_store_credit) ? $is_exist_store_credit : new SsStoreCredit;
                        $store__credit->shop_id = $ssPlan->shop_id;
                        $store__credit->ss_customer_id = $ssContract->ss_customer_id;
                        $store__credit->shopify_customer_id = $ssContract->shopify_customer_id;
                        $store__credit->amount = $ssPlan->store_credit_amount;
                        $store__credit->balance = ($is_exist_store_credit) ? ($is_exist_store_credit->balance + $ssPlan->store_credit_amount) :  $ssPlan->store_credit_amount;
                        $store__credit->save();

                        $this->addTrasaction($ssPlan->shop_id, $ssPlan->user_id, $ssContract->ss_customer_id,  $ssContract->id, "credit", $ssPlan->store_credit_amount);
                        $this->saveActivity($ssPlan->user_id, $ssContract->ss_customer_id, $ssContract->id, "System", "Customer received $ssPlan->store_credit_amount $shop->currency in store credits");
                    }
                }
                logger("==============================STORE CREDIT END=============================");
                // update contract price if trial order cycle limit reached
                if ($ssContract->status == 'active') {
                    if ($ssContract->trial_available && (($ssContract->pricing2_after_cycle != 1 &&  $ssContract->order_count == $ssContract->pricing2_after_cycle) || ($ssPlan->trial_days &&  $ssPlan->trial_days == Carbon::now()->diffInDays($ssContract->created_at)))) {
                        $lineItems = SsContractLineItem::where('user_id', $user->id)->where('ss_contract_id', $ssContract->id)->get();
                        foreach ($lineItems as $key => $lineItem) {
                            $this->subscriptionContractPriceUpdate($user->id, $lineItem, $ssContract);
                        }
                    }
                }
                //update customer
                $ssCustomer = SsCustomer::where('shop_id', $shop->id)->where('shopify_customer_id', $ssContract->shopify_customer_id)->first();
                if ($ssCustomer) {
                    $sh_order = $this->getShopifyOrder($user, $data->order_id);
                    $ssCustomer->total_orders = $ssCustomer->total_orders + 1;
                    $ssCustomer->total_spend = (@$sh_order['total_price']) ? $ssCustomer->total_spend + $sh_order['total_price'] : 0;
                    $ssCustomer->avg_order_value = preg_replace('/[^0-9_ .]/s', '', number_format(($ssCustomer->total_spend / $ssCustomer->total_orders), 2));
                    $ssCustomer->save();
                    // create order
                    $order = $this->createOrder($user->id, $shop->id, $data->order_id, $ssCustomer->id, $ssContract->id);
                    // Add order tags
                    logger('============== START:: AddOrderTags ===========');
                    $result = $this->updateOrderTag($user , $data->order_id,$ssContract->tag_order);
                    // logger(json_encode($result));
                    logger('============== END:: AddOrderTags ===========');
                }
                // update next_order_date in shopify
                $result = $this->subscriptionContractSetNextBillingDate($shop->user_id, $ssContract->shopify_contract_id);

                // Shopify Flow - Membership Payment Success
                $ss_cotract_line_item = SsContractLineItem::where('ss_contract_id', $ssContract->id)->first();
                if ($ss_cotract_line_item) {
                    $condition = $ssContract->status != 'active' ? 'false' : 'true';
                    // $this->flowTrigger(
                    //     config('const.SHOPIFY_FLOW.PAYMENT_SUCCESS'),
                    //     env('APP_TRIGGER_URL'),
                    //     '
                    //         {
                    //             \"customer_id\": ' . $ssContract->shopify_customer_id . ',
                    //             \"order_id\": ' . $data->order_id . ',
                    //             \"product_id\": ' . $ss_cotract_line_item->shopify_product_id . ',
                    //             \"Last Order\": ' . $condition . ' ,
                    //             \"Membership Order Count\": ' . $ssContract->order_count . ',
                    //             \"Next Billing Date\": \"' . $ssContract->next_processing_date . '\",
                    //             \"Member Number\": ' . $ssContract->member_number . ',
                    //             \"Contract ID\": ' . $ssContract->shopify_contract_id . ',
                    //             \"Customer Tag\": \"' . $ssContract->tag_customer . '\",
                    //             \"Order Tag\": \"' . $ssContract->tag_order . '\"
                    //         }
                    //     ',
                    //     $user
                    // );
                    Http::post(env('APP_TRIGGER_URL').'/api/callFlowTrigger', [
                        'action' => "payment_success" ,
                        'customer_id' => $ssContract->shopify_customer_id ,
                        'order_id' => $data->order_id,
                        'product_id' => $ss_cotract_line_item->shopify_product_id,
                        'last_order' => $condition ,
                        'order_count' => $ssContract->order_count,
                        'next_processing_date' => $ssContract->next_processing_date ,
                        'member_number' => $ssContract->member_number ,
                        'shopify_contract_id' => $ssContract->shopify_contract_id,
                        'tag_customer' => $ssContract->tag_customer,
                        'order_tag' => $ssContract->tag_order,
                        'uid' =>  $user->id ,
                    ]);
                    logger('------ Shopify Flow - Membership Payment Success :: Api called');
                } else {
                    logger('------ Shopify Flow - Membership Payment Success :: SsContractLineItem not found');
                }
            }
            $this->updateWebhookStatus($this->statuswebhook_id, 'processed', null);
        } catch (\Throwable $e) {
            logger('========== ERROR:: Listener:: BillingAttemptSuccess ==========');
            $this->updateWebhookStatus($this->statuswebhook_id, 'error', $e);
            logger($e);
            Bugsnag::notifyException($e);
        }
    }
}
