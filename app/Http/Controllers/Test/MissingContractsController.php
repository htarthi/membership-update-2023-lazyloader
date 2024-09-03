<?php

namespace App\Http\Controllers\Test;

use App\Events\CheckBillingAttemptSuccess;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\SsBillingAttempt;
use App\Models\SsContract;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class MissingContractsController extends Controller
{
    use ShopifyTrait;

    public function addMissingContract($shop_name)
    {
        $user = User::where('name', $shop_name)->first();
        if ($user) {
            return $this->makeMissingContract($user);
        } else {
            dd('ERROR :: Shop doesnt exist');
        }
    }

    public function addMissingContractRecord(Request $request)
    {
        try {
            $shop_name = $request->input('shop_name');
            $contract_id = $request->input('contract');

            $user = User::where('name', $shop_name)->first();
            if ($user) {
                $this->makeMissingContractRecord($user, $contract_id);
                return response()->json(['Message' => "Contract Created Successfully"]);
            } else {
                dd('ERROR :: Shop doesnt exist');
            }
        } catch (Exception $e) {
            logger("============= ERROR ::  addMissingContractRecord =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function addMissingContractRecords(Request $request)
    {
        try {
            $executed = [];
            $fail_to_execute = [];
            $shop_name = $request->shop_name;
            $contracts = $request->contracts;

            $user = User::where('name', $shop_name)->first();
            if ($user) {
                if (count($contracts) > 0) {
                    foreach ($contracts as $i => $contract_id) {
                        $res = $this->makeMissingContractRecord($user, $contract_id);
                        if ($res == $contract_id) {
                            array_push($executed, $res);
                        } else {
                            array_push($fail_to_execute, $res);
                        }
                    }
                }
                return response()->json(['executed_contracts' => $executed, 'failed' => $fail_to_execute,  'isSuccess' => true], 200);
            } else {
                dd('ERROR :: Shop doesnt exist');
            }
        } catch (Exception $e) {
            logger("============= ERROR ::  addMissingContractRecords =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function regesterMissingShop($shop_name)
    {
        $user = User::where('name', $shop_name)->first();
        if ($user) {
            $webhooks = $this->makeWebhooksFromConfig($user);
            $scripts = $this->makeScriptTagsFromConfig($user);
            return [
                'webhooks' => $webhooks,
                'scripts' => $scripts,
            ];
        } else {
            dd('ERROR :: Shop doesnt exist');
        }
    }

    public function addMissingCustomerTags($shop_name)
    {
        $user = User::where('name', $shop_name)->first();
        if ($user) {

            $allContracts = SsContract::where('user_id', $user->id)
                ->where('tag_customer', '!=', '')
                ->whereIN('status', ['active', 'cancelled'])
                ->where('next_processing_date', '>', Carbon::now())
                ->chunk(200, function ($chunkedContracts) use ($user) {

                    foreach ($chunkedContracts as $index => $isExistTag) {

                        logger("Addin Missing :: User :: " . $user->name . "   Customer :: " .  $isExistTag->shopify_customer_id . "  Tag :: " .  $isExistTag->tag_customer . " Index :: $index");

                        $this->updateShopifyTags(
                            $user,
                            $isExistTag->shopify_customer_id,
                            $isExistTag->tag_customer,
                            'customer',
                            'add'
                        );
                    }
                });
            // dd($allContracts);

            dd('Done');
        } else {
            dd('ERROR :: Shop doesnt exist');
        }
    }

    public function checkSuccessfulBillingAttempt($billingAttempt, Request $request)
    {
        try {
            logger("==========START:: checkSuccessfulBillingAttempt========");
            $billingAttemptIds = explode(",", $billingAttempt);
            $billingAttemptNew = SsBillingAttempt::whereIn('id', $billingAttemptIds)->get();
            // $billingAttempt = SsBillingAttempt::find($billingAttempt);
            // dd($billingAttempt);

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
                                    legacyResourceId
                                }
                            }
                        }
                    }
                ');

                    // logger("res==>" . json_encode($res));
                    if (!$res["errors"]) {
                        $res = $res["body"]->container["data"]["subscriptionBillingAttempt"];
                        if ($res["ready"]) {
                            // dd($res);
                            $payloadJson = [
                                "id" => str_replace('gid://shopify/SubscriptionBillingAttempt/', '', $res['id']),
                                "admin_graphql_api_id" => $res["id"],
                                "idempotency_key" => $res["idempotencyKey"],
                                "order_id" => $res["subscriptionContract"]["originOrder"]["legacyResourceId"],
                                "admin_graphql_api_order_id" => 'gid://shopify/Order/' . $res["subscriptionContract"]["originOrder"]["legacyResourceId"],
                                "subscription_contract_id" => str_replace('gid://shopify/SubscriptionContract/', '', $res["subscriptionContract"]["id"]),
                                "admin_graphql_api_subscription_contract_id" => $res["subscriptionContract"]["id"],
                                "ready" => $res["ready"],
                                "error_message" => $res["errorMessage"],
                                "error_code" => $res["errorCode"],
                            ];
                            // dd($payloadJson);
                            $webhookId = $this->webhook("subscription_billing_attempts/success", $user->id, json_encode($payloadJson));
                            // $webhook = SsWebhook::find($webhookId);
                            event(new CheckBillingAttemptSuccess($webhookId, $user->id, $shop->id, json_encode($payloadJson)));
                            // $webhook->delete();
                            // dd($webhook);
                            $responseData = [
                                "success" => true,
                                "message" => "Job dispatched :: For Attempt Id :: " . $billingAttempt['id'] . " Shopify Id ::" . $billingAttempt['shopify_id'],
                                "data" => [
                                    "processedData" => $payloadJson,
                                    "webhookId" => $webhookId
                                ],
                            ];
                            // logger(json_encode($responseData));
                        }
                        $responseData = [
                            "success" => true,
                            "message" => "Job dispatched :: For Attempt Id :: " . $billingAttempt['id'] . " Shopify Id ::" . $billingAttempt['shopify_id'],
                            "data" => "Not yet paid",
                        ];
                        // logger(json_encode($responseData));
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
            return response()->json([
                "success" => true,
                "message" => "Job dispatched :: For Attempt Ids :: " . json_encode($billingAttemptIds),
                "data" => [
                    "processedData" => [],
                    "webhookId" => null
                ],
            ]);

            //dd("Not found");
        } catch (\Throwable $e) {
            logger("============= ERROR ::  checkSuccessfulBillingAttempt =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
