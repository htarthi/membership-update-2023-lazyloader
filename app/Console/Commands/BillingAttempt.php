<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\SsBillingAttempt;
use App\Models\SsContract;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Illuminate\Console\Command;

class BillingAttempt extends Command
{
    use ShopifyTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:attempt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to check for contracts that require a billing attempt';

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
            logger('============= START:: Billing Attempt ==========');
            $users = User::where('active', 1)->where('plan_id', '!=', null)->get();
            $minute = date('i');
            foreach ($users as $ukey => $uval) {
                $shop = Shop::where('user_id', $uval->id)->latest()->first();
                if ($shop) {
                    $default_timezone = date_default_timezone_get();
                    // logger('============== currentTime :: ' . date('Y-m-d H:i:s') . '===============');

                    $currUTCTime = date('Y-m-d H:') . (date('i') + 1) . ':00';
                    date_default_timezone_set($shop->iana_timezone);
                    $currentTime = date('Y-m-d H:i:s');
                    $currentDate = date('Y-m-d');
                    // logger('============== currentTime :: ' . $currentTime . '===============');

                    $status = ['active', 'paused'];
                    $db_contract = SsContract::where('user_id', $uval->id)->whereIn('status', $status)->where('next_processing_date', '<=', $currUTCTime)->where('is_onetime_payment', 0)
                        ->where(function ($query) {
                            $query->where('status_billing', '!=', 'pending')->orWhere('status_billing', null);
                        })->limit(50)->get();

                    logger('============== ' . $uval->name . ' =============');
                    logger('============== count($db_contract) :: ' . count($db_contract) . '===============');
                    date_default_timezone_set($default_timezone);

                    if (count($db_contract) > 0) {
                        foreach ($db_contract as $ckey => $cval) {
                            if ($cval->status == 'active') {
                                $IsEligibleForExpire = 0;
                                if ($cval->billing_max_cycles) {
                                    if ($cval->order_count >= $cval->billing_max_cycles) {
                                        $IsEligibleForExpire = 1;
                                    }
                                }
                                if ($IsEligibleForExpire) {
                                    $cval->status = 'cancelled';
                                    $cval->status_display = 'Expired';
                                    $cval->save();
                                    $result = $this->updateSubscriptionContract($uval->id, $cval->id);
                                    $this->saveActivity($uval->id, $cval->ss_customer_id, $cval->id, 'system', 'Membership has expired, maximum number of orders reached');
                                    // remove customer tag from shopify if membership is no longer active
                                    $this->checkForActiveMemberTag($uval, $cval->shopify_customer_id, $cval->tag_customer);
                                } else {
                                    // create billing attempt and order
                                    $result = $this->billingAttempt($cval->shopify_contract_id, $uval->id);
                                    if (!$result['errors']) {
                                        $sh_billingAttempt = $result['body']->container['data']['subscriptionBillingAttemptCreate'];
                                        if (empty($sh_billingAttempt['userErrors'])) {
                                            $subscriptionBillingAttempt = $sh_billingAttempt['subscriptionBillingAttempt'];
                                            // logger(json_encode($subscriptionBillingAttempt));
                                            $cval->status_billing = 'pending';
                                            $cval->save();

                                            $billingAttempt = new SsBillingAttempt;
                                            $billingAttempt->shop_id = $shop->id;
                                            $billingAttempt->shopify_id = str_replace('gid://shopify/SubscriptionBillingAttempt/', '', $subscriptionBillingAttempt['id']);
                                            $billingAttempt->ss_contract_id = $cval->id;
                                            $billingAttempt->status = 'sent';
                                            $billingAttempt->completedAt = date('Y-m-d H:i:s', strtotime($subscriptionBillingAttempt['completedAt']));
                                            $billingAttempt->errorMessage = $subscriptionBillingAttempt['errorMessage'];
                                            $billingAttempt->idempotencyKey = $subscriptionBillingAttempt['idempotencyKey'];
                                            $billingAttempt->nextActionUrl = $subscriptionBillingAttempt['nextActionUrl'];
                                            $billingAttempt->shopify_contract_id = (@$subscriptionBillingAttempt['subscriptionContract']['id']) ? str_replace('gid://shopify/SubscriptionContract/', '', $subscriptionBillingAttempt['subscriptionContract']['id']) : null;
                                            // $billingAttempt->shopify_order_id = (@$subscriptionBillingAttempt['subscriptionContract']['originOrder']['legacyResourceId']) ? $subscriptionBillingAttempt['subscriptionContract']['originOrder']['legacyResourceId'] : null;

                                            $createdAt = date('Y-m-d H') . ':01:' . date('s');
                                            $billingAttempt->created_at = $createdAt;
                                            $billingAttempt->updated_at = $createdAt;
                                            $billingAttempt->save();
                                        } else {
                                            logger('=============== Shopify billing attempt user ERROR ================');
                                            logger(json_encode($sh_billingAttempt['userErrors']));
                                        }
                                    } else {
                                        logger('=============== Shopify billing attempt graphQL ERROR ================');
                                        logger(json_encode($result));
                                    }
                                }
                            } elseif ($cval->status == 'paused') {
                                // skip next order and next processing date
                                $next_order_date = $cval->next_order_date;
                                $interval_count = $cval->delivery_interval_count;
                                $interval = $cval->delivery_interval;
                                $timestamp = date('H:i:s', strtotime($next_order_date));
                                $new_next_order = date('Y-m-d', strtotime($next_order_date . ' + ' . $interval_count . ' ' . $interval . 's')) . ' ' . $timestamp;
                                $new_processing_order = date('Y-m-d', strtotime($cval->next_processing_date . ' + ' . $interval_count . ' ' . $interval . 's')) . ' ' . $timestamp;
                                $cval->update([
                                    'next_order_date' => $new_next_order,
                                    'next_processing_date' => $new_processing_order,
                                ]);
                                $cval->save();
                                $this->saveActivity($shop->user_id, $cval->ss_customer_id, $cval->id, 'system', 'Order was skipped because subscription is paused');
                            }
                        }
                    }
                }
            }
            // }
            logger('============= END:: Billing Attempt ==========');
        } catch (\Exception $e) {
            logger('============= ERROR:: Billing Attempt ==========');
            logger($e);
        }
        return 0;
    }
    public function billingAttempt($contractId, $user_id)
    {
        $idempotencyKey = $user_id . $contractId . date('YmdHis');
        $query = 'mutation {
                    subscriptionBillingAttemptCreate(subscriptionBillingAttemptInput: {idempotencyKey: "' . $idempotencyKey . '"}, subscriptionContractId: "gid://shopify/SubscriptionContract/' . $contractId . '") {
                      subscriptionBillingAttempt {
                        completedAt
                        createdAt
                        errorMessage
                        id
                        idempotencyKey
                        nextActionUrl
                        subscriptionContract {
                            id
                            originOrder {
                                legacyResourceId
                            }
                        }
                      }
                      userErrors {
                        code
                        field
                        message
                      }
                    }
                  }
                ';
        $result = $this->graphQLRequest($user_id, $query);
        return $result;
    }
}
