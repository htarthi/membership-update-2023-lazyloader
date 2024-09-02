<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SsBillingAttempt;
use App\Events\CheckBillingAttemptFailure;
use App\Events\CheckBillingAttemptSuccess;
use App\Models\Shop;
use App\Models\User;
use App\Traits\ShopifyTrait;
use Carbon\Carbon;

class MissingBillingAttempt extends Command
{
    use ShopifyTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missing-billing-attempt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for missing billing attempts';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            logger("==========START:: MissingBillingAttempt ========");
            // $billingAttemptNew = SsBillingAttempt::where('status','sent')->whereDate('created_at','>','2024-07-20')->get();
            $billingAttemptNew = SsBillingAttempt::where('status','sent')->whereDate('created_at','>',Carbon::now()->subWeek())->get();

            foreach ($billingAttemptNew as $billingAttempt) {
                $shop = Shop::where('id', $billingAttempt['shop_id'])->latest()->first();
                // dd($shop);
                logger("==========checkSuccessfulBillingAttempt Shop Id ========> " . $billingAttempt['shop_id']);

                if ($shop) {
                    $user = User::where('id', $shop->user_id)->first();
                    // dd($user);
                    $res = $user->api()->graph('
                    query MyQuery {
                        subscriptionBillingAttempt(id: "gid://shopify/SubscriptionBillingAttempt/' . $billingAttempt['shopify_id'] . '") {
                            ready
                            completedAt
                            createdAt
                            errorCode
                            errorMessage
                            id
                            idempotencyKey
                            nextActionUrl
                            subscriptionContract {
                                id
                                originOrder {
                                    id
                                    legacyResourceId
                                }
                                orders(first: 1, reverse: true) {
                                    nodes {
                                    id
                                    legacyResourceId
                                    }
                                }
                            }
                        }
                    }
                ');


                    // logger("res==>" . json_encode($res));
                    if (!$res["errors"]) {
                        $res = $res["body"]->container["data"]["subscriptionBillingAttempt"];
                        // logger('********************************************************************************** SHOP ID IS AN -------------------------------------------------');
                        // logger($shop->id);

                        if ($res["ready"]) {
                            $payloadJson = [];
                            $webhookId = null;
                            // dd($res);
                            if ($res['errorCode'] && $res['errorMessage']) {

                                $payloadJson = [
                                    "id" => str_replace('gid://shopify/SubscriptionBillingAttempt/', '', $res['id']),
                                    "admin_graphql_api_id" => $res["id"],
                                    "idempotency_key" => $res["idempotencyKey"],
                                    "order_id" => null,
                                    "admin_graphql_api_order_id" => null,
                                    "subscription_contract_id" => str_replace('gid://shopify/SubscriptionContract/', '', $res["subscriptionContract"]["id"]),
                                    "admin_graphql_api_subscription_contract_id" => $res["subscriptionContract"]["id"],
                                    "ready" => $res["ready"],
                                    "error_message" => $res["errorMessage"],
                                    "error_code" => $res["errorCode"],
                                ];
                                // dd($payloadJson);
                                $webhookId = null;

                                // return "in order loop";

                                $webhookId = $this->webhook("subscription_billing_attempts/failure", $user->id, json_encode($payloadJson));
                                event(new CheckBillingAttemptFailure($webhookId, $user->id, $shop->id, json_encode($payloadJson)));
                            } else {


                                $payloadJson = [
                                    "id" => str_replace('gid://shopify/SubscriptionBillingAttempt/', '', $res['id']),
                                    "admin_graphql_api_id" => $res["id"],
                                    "idempotency_key" => $res["idempotencyKey"],
                                    "order_id" => $res["subscriptionContract"]["orders"]["nodes"][0]["legacyResourceId"],
                                    "admin_graphql_api_order_id" => 'gid://shopify/Order/' .  $res["subscriptionContract"]["orders"]["nodes"][0]["legacyResourceId"],
                                    "subscription_contract_id" => str_replace('gid://shopify/SubscriptionContract/', '', $res["subscriptionContract"]["id"]),
                                    "admin_graphql_api_subscription_contract_id" => $res["subscriptionContract"]["id"],
                                    "ready" => $res["ready"],
                                    "error_message" => $res["errorMessage"],
                                    "error_code" => $res["errorCode"],
                                ];
                                // dd($payloadJson);
                                $webhookId = null;


                                $webhookId = $this->webhook("subscription_billing_attempts/success", $user->id, json_encode($payloadJson));
                                // $webhook = SsWebhook::find($webhookId);
                                event(new CheckBillingAttemptSuccess($webhookId, $user->id, $shop->id, json_encode($payloadJson)));
                                // $webhook->delete();
                            }
                            // dd($webhook);
                            $responseData = [
                                "success" => true,
                                "message" => "Job dispatched :: For Attempt Id :: " . $billingAttempt['id'] . " Shopify Id ::" . $billingAttempt['shopify_id'],
                                "data" => [
                                    "processedData" => $payloadJson,
                                    "webhookId" => $webhookId
                                ],
                            ];
                            logger(json_encode($responseData));
                        }
                        $responseData = [
                            "success" => true,
                            "message" => "Job dispatched :: For Attempt Id :: " . $billingAttempt['id'] . " Shopify Id ::" . $billingAttempt['shopify_id'],
                            "data" => "Not yet paid",
                        ];
                        logger(json_encode($responseData));
                    }
                    //($res);
                    $responseData = [
                        "success" => true,
                        "message" => $res,
                        "data" => "error",
                    ];
                    logger(json_encode($responseData));
                }
            }

    } catch (\Throwable $e) {
        logger("============= ERROR ::  MissingBillingAttempt  =============");
        logger($e);
    }
    }
}
