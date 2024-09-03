<?php

namespace App\Http\Controllers\Test;

use App\Events\CheckSubscriptionContract;
use App\Events\CheckBillingAttemptFailure;
use App\Http\Controllers\Controller;
use App\Jobs\CalculateShopMetrics;
use App\Jobs\CancellationMetricsJob;
use App\Models\App;
use App\Models\ExchangeRate;
use App\Models\Shop;
use App\Models\SsAnswer;
use App\Models\SsCustomPlan;
use App\Models\SsContract;
use App\Models\SsContractLineItem;
use App\Models\SsForm;
use App\Models\SsOrder;
use App\Models\SsPlan;
use App\Models\SsSetting;
use App\Traits\ShopifyTrait;
use App\Models\SsWebhook;
use App\Models\SsCustomer;
use App\Models\SsPlanGroup;
use App\Models\SsLanguage;
use App\Models\SsPortal;
use App\Models\SsTrackContract;
use App\Jobs\TrackContractJob;
use App\Models\AutomaticDiscount;
use App\Models\Feature;
use App\Models\SsBillingAttempt;
use App\Models\SsCancellation;
use App\Models\SsMetric;
use App\Models\SsPlanGroupVariant;
use App\Models\SsStoreCredit;
use App\Models\SsThemeInstall;
use App\Models\User;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Osiset\ShopifyApp\Storage\Models\Charge;
use Illuminate\Support\Carbon;
// use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Osiset\BasicShopifyAPI\Session;
use Osiset\ShopifyApp\Util;
use Validator;
use Illuminate\Support\Facades\Schema;
use Osiset\BasicShopifyAPI\Options;
use Gnikyt\BasicShopifyAPI\BasicShopifyAPI;
use Gnikyt\BasicShopifyAPI\Options as OptionsO;
use Gnikyt\BasicShopifyAPI\Session as SessionO;
use Illuminate\Support\Facades\File;
use App\Jobs\AutomaticAppDiscountJob;
use App\Jobs\CustomerMetafiedsJob;

class TestController extends Controller
{
    use ShopifyTrait;
    private $LocationIds = [];


    public function test(Request $request)
    {

        $user = User::where('name', 'polaries12test.myshopify.com')->first();


        $discount_id = 'gid://shopify/SubscriptionManualDiscount/d8910bde-16bb-4ad0-9b97-e99bddb6ca0e';
        $draft_id    = 'gid://shopify/SubscriptionDraft/367459434789';

        $query = 'mutation MyMutation {
            subscriptionDraftDiscountRemove(discountId: "' . $discount_id . '", draftId: "' . $draft_id . '") {
              discountRemoved
                draft {
                    discountsRemoved(reverse: true, first: 10) {
                    nodes {
                        ... on SubscriptionManualDiscount {
                        id
                        title
                        }
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
        $result = $this->graphQLRequest($user->id, $query);
        return $result;

              //     ];
        //     $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/7598525481200/metafields.json', $parameter);

        $query = '{
            subscriptionContract(id: "gid://shopify/SubscriptionContract/20433568037") {
                discounts(first: 10) {
                edges {
                    node {
                    id
                    }
                }
                }
            }
            }
            ';
        $ssContractResult = $this->graphQLRequest($user->id, $query);
        return $ssContractResult;

        // $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/20506476837");
        // $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);
        // return $ssContractResult;

        // $draftId = $this->getSubscriptionDraft($user->id, 20433568037);
        // return $draftId;


        $mutation = '
            mutation subscriptionDraftDiscountUpdate{
                    subscriptionDraftDiscountUpdate(discountId: "gid://shopify/SubscriptionManualDiscount/d8910bde-16bb-4ad0-9b97-e99bddb6ca0e", draftId: "gid://shopify/SubscriptionDraft/367459434789", input: {
                        entitledLines: {
                            all : false,
                            lines : {
                                remove : [
                                    "gid://shopify/SubscriptionLine/77b427ef-1ab0-4ec7-b144-e72224e3097b"
                                ]
                            }
                        }
                    })
                    {
                        discountUpdated {
                            id
                        }
                        draft {
                            id
                            status
                        }
                        userErrors {
                            field
                            code
                            message
                        }
                    }
            }
        ';
        $result = $this->graphQLRequest($user->id, $mutation);
        return $result;




        return $ssContractResult;


        return $user;

        // return $user;
        $theme = $this->getPublishTheme($user);
        //         $fileData = getSimpleeMembershipSnippetCode();
        //         $value = <<<EOF
        //                 $fileData
        // EOF;



        //         $config = config('const.SNIPPETS.SIMPLEE_MEMBERSHIP');
        //         $parameter['asset']['key'] = 'snippets/' . $config . '.liquid';
        //         $parameter['asset']['value'] = $value;
        //         $asset = $user->api()->rest('PUT', 'admin/themes/' . $theme . '/assets.json', $parameter);

        //         return $asset;

        $simpleeButButton = getLiquidAssetH($theme, $user->id, 'snippets/buy-buttons.liquid');

        return $simpleeButButton;

        logger(json_encode($asset));

        return $user;
        // $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/orders/6228068270373.json';

        // return $endPoint;

        // $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/20433568037");
        // $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);


        // $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/33857962303");
        // $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);

        // $SsContractLineItem = SsContractLineItem::where('ss_contract_id', 11213)->first();
        // $draftId = $this->getSubscriptionDraft($user->id, $SsContractLineItem->shopify_contract_id);
        // return $draftId;


        // return SsBillingAttempt::where('ss_contract_id', 6313)->first();

        // $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/shop.json';
        // $result = $user->api()->rest('GET', $endPoint);
        // if(isset($result['body']['shop']->plan_name)){

        // }


        // $response = Http::post('https://api.example.com/api/callFlowTrigger', [
        //                 'action' => "new_member" ,
        //                 'customer_id' => $shopify_customer_id ,
        //                 'order_id' => $data->origin_order_id,
        //                 'product_id' => $sf_product_id,
        //                 'tag_customer' => $db_plan_group->tag_customer ,
        //                 'tag_order' => $db_plan_group->tag_order,
        //                 'next_processing_date' => $contract->next_processing_date ,
        //                 'member_number' => $contract->member_number ,
        //                 'shopify_contract_id' =>  $shopify_contract_id ,
        //                 'uid' =>  $user->id ,
        //             ]);



        // return SsPlanGroup::where('discount_type',0)
        //     ->update([
        //         'discount_type' => 1
        //     ]);


        // $user = User::where('name', 'simplee-prod-test32.myshopify.com')->first();
        // $getCustId = 7606628778224;
        // // $getCustomer = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/7605599142128/metafields.json');
        // $getCustomer = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/'.$getCustId.'/metafields.json');

        // if(!$getCustomer['errors']){
        //     $check = isset($getCustomer['body']) ? $getCustomer['body']['container']['metafields'] : '';

        //     if($check){
        //         dd($check);
        //     }else{
        //         dd("noo");
        //     }
        // };



        // if( && !empty($getCustomer['body']['container'])){
        //     dd("yes");
        // }else{
        //     dd("no");
        // }
        // return $getCustomer['body']->metafields ;

        // $getTag  = isset($getCustomer['body']->container['customer']['tags']) ? $getCustomer['body']->container['customer']['tags'] : '';
        // if($getTag){
        //     $formattedStr = preg_replace('/\s*,\s*/', ',', $getTag);
        //     $keyVals['tags'] = $formattedStr;
        //     $parameter = [
        //         "metafield" => [
        //             'namespace' => 'simplee',
        //             'key' => 'customer-discount-tags',
        //             'value' => json_encode($keyVals),
        //             'type' => 'json'
        //         ]
        //     ];
        //     $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/7598525481200/metafields.json', $parameter);
        // }

        // $shop = Shop::where('user_id',$user->id)->first();

        // $collectionArray = AutomaticDiscount::where(['shop_id' => $shop->id , 'user_id' =>  $shop->user_id])
        // ->pluck('collection_id')
        // ->flatMap(function ($collection) {
        //     if($collection){
        //         return array_map(function ($id) {
        //             return 'gid://shopify/Collection/' . $id;
        //         }, explode(',', $collection));
        //     }
        // })
        // ->unique()
        // ->toArray();

        // dd($collectionArray) ;



        //  $chunkSize = 100;
        //  SsCustomer::chunk($chunkSize, function ($customers) {
        //      foreach ($customers as $custm) {
        //          logger("==============CUSTOMER=============");
        //          $shop = Shop::find($custm->shop_id);
        //          if ($shop) {
        //              $user = User::find($shop->user_id);
        //              if ($user) {
        //                  $shopifyApiVersion = env('SHOPIFY_API_VERSION');
        //                  $getCustomer = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/'.$custm->shopify_customer_id.'.json');
        //                  $parameter = [
        //                      "metafield" => [
        //                          'namespace' => 'simplee',
        //                          'key' => 'customer-discount-tags',
        //                          'value' => isset($getCustomer['body']->container['customer']['tags']) ? $getCustomer['body']->container['customer']['tags'] : null,
        //                          'type' => 'string'
        //                      ]
        //                  ];
        //                  $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/'.$custm->shopify_customer_id.'/metafields.json', $parameter);
        //              }
        //          }
        //      }
        //  });
        // return "DONE";






        // $getCustomer = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/7096462180535.json');
        // if(isset($getCustomer['body']->container['customer']['tags'])){
        //     $parameter = [
        //         "metafield" => [
        //             'namespace' => 'simplee',
        //             'key' => 'demo',
        //             'value' => $getCustomer['body']->container['customer']['tags'],
        //             'type' => 'string'
        //         ]
        //     ];
        //     $response = $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/7096462180535/metafields.json', $parameter);
        //     return $response;
        // }



        // $shop = Shop::where('user_id', $user->id)->first();


        $shipingDiscount = SsPlanGroup::with(['hasManyVariants:ss_plan_group_id,shopify_product_id'])
            ->where([
                'shop_id' => $shop->id,
                'user_id' => $shop->user_id
            ])
            ->whereNotNull('shipping_discount_code')
            ->where('shipping_discount_code', '!=', '0.0')
            ->select('id', 'shipping_discount_id', 'shipping_discount_code', 'active_shipping_dic', 'tag_customer')
            ->get();
        $getOldDisID  = SsPlanGroup::where(['shop_id' => $shop->id, 'user_id' =>  $shop->user_id])->whereNotNull('shipping_discount_id')->first();
        $jsonArray = [
            'product' => []
        ];
        $escapedJsonString = '';
        if ($shipingDiscount->isNotEmpty()) {
            foreach ($shipingDiscount as $shipvals) {
                $autoDisArray = [
                    'discount_type' => 'shipping_discounts',
                    'discount_method' =>  $shipvals->active_shipping_dic == '$' ? 'fixedamount' : 'percentage',
                    'discount_value' =>  $shipvals->shipping_discount_code ?? null,
                    'tags' => $shipvals->tag_customer ?? null,
                ];
                $jsonArray['product']["gid://shopify/Product/{$shipvals->hasManyVariants[0]->shopify_product_id}"] = $autoDisArray;
            }
            $jsonString = json_encode($jsonArray, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $escapedJsonString = addslashes($jsonString);
        } else {
            $mutation = '
                mutation discountAutomaticDelete{
                discountAutomaticDelete(id: "gid://shopify/automaticDiscountNodes") {
                    deletedAutomaticDiscountId
                    userErrors {
                    field
                    code
                    message
                    }
                }
                }
            ';
            $this->graphQLRequest($shop->user_id, $mutation);
        }
        if ($escapedJsonString) {
            if ($getOldDisID) {
                $dicId = "gid://shopify/DiscountAutomaticNode/1521263411493";
                $getExistDisCountsquery = '
                            query {
                                automaticDiscountNode(id: "' . $dicId . '") {
                                metafields(first: 1) {
                                    edges {
                                    node {
                                        id
                                        namespace
                                        key
                                        value
                                        type
                                    }
                                    }
                                }
                                }
                            }
                        ';
                $resultMeatfileds = $this->graphQLRequest($user->id, $getExistDisCountsquery);
                $getMetafieldsId = isset($resultMeatfileds['body']->container['data']['automaticDiscountNode']['metafields']['edges'][0]['node']['id']) ? $resultMeatfileds['body']->container['data']['automaticDiscountNode']['metafields']['edges'][0]['node']['id'] : '';
                if ($getMetafieldsId) {
                    $query = '
                    mutation {
                        discountAutomaticAppUpdate(
                            automaticAppDiscount: {
                                combinesWith: {
                                    productDiscounts : true,
                                },
                                metafields: [
                                    {
                                        id: "gid://shopify/Metafield/39963058503973",
                                        value: "' . $escapedJsonString . '",
                                        type: "json"
                                    }
                                ]
                            },
                            id : "gid://shopify/DiscountAutomaticNode/1521263411493"
                        ) {
                            automaticAppDiscount {
                                discountId
                                title
                                startsAt
                                endsAt
                                status
                                appDiscountType {
                                appKey
                                functionId
                                }
                                combinesWith {
                                orderDiscounts
                                productDiscounts
                                shippingDiscounts
                                }
                            }
                            userErrors {
                                field
                                message
                            }
                        }
                    }
                ';
                    $result = $this->graphQLRequest($shop->user_id, $query);
                    return $result;
                }
            } else {
                $query = '
                mutation {
                    discountAutomaticAppCreate(automaticAppDiscount: {
                        title: "Shipping Automatic Discount",
                        functionId: "9c53cf37-c9cf-4508-b631-c8273a275eba",
                        combinesWith: {
                            productDiscounts: true,
                        },
                        metafields: [
                            {
                                namespace: "volume-discount",
                                key: "function-configuration",
                                value: "' .  $escapedJsonString . '",
                                type: "json"
                            }
                        ],
                        startsAt: "2021-06-22T00:00:00"
                    }) {
                        automaticAppDiscount {
                            discountId
                            title
                            startsAt
                            endsAt
                            status
                            appDiscountType {
                                appKey
                                functionId
                            }
                            combinesWith {
                                orderDiscounts
                                productDiscounts
                                shippingDiscounts
                            }
                        }
                        userErrors {
                            field
                            message
                        }
                    }
                }';
                $automaticAppDiscount =  $this->graphQLRequest($shop->user_id, $query);
                return  $automaticAppDiscount;
                $getURL = isset($automaticAppDiscount['body']->container['data']['discountAutomaticAppCreate']['automaticAppDiscount']['discountId']) ? $automaticAppDiscount['body']->container['data']['discountAutomaticAppCreate']['automaticAppDiscount']['discountId'] : null;
                return  $automaticAppDiscount;
                if ($getURL) {
                    $lastPart = strrchr($getURL, '/');
                    $discountID =  (int) ltrim($lastPart, '/');
                }
            }
        }
        return 1;

        $tableName = 'ss_store_credits';
        if (Schema::hasTable($tableName)) {
            echo "Table $tableName exists";
        }

        return SsStoreCredit::get();
        return SsPlan::where('shopify_plan_id', 5198512365)->first();
        $res = Feature::create([
            'name' => 'store-credit',
            'is_enabled' => true
        ]);
        return $res;

        $user = User::where('name', 'testcustomeraccountextensibility.myshopify.com')->first();
        $result = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json');
        return $result['body']['metafields'];


        $fields = [
            'mem_biling_info' => 'Manage your membership’s billing information',
            'mem_resume' => 'view upcoming renewals',
            'mem_upcoming' => 'cancel or resume your existing membership'
        ];
        $update = [];
        foreach ($fields as $key => $label) {
            $parameter = [
                "metafield" => [
                    'namespace' => 'simplee',
                    'key' => $key,
                    'value' => $label,
                    'type' => 'string'
                ]
            ];
            $update[$key] = $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json', $parameter);
        }
        return $update;


        $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/orders/6078656184603.json';
        for ($i = 0; $i < 50; $i++) {
            $response = $user->api()->rest('GET', $endPoint);
            if ($response['status'] == 429 && $response['body'] == 'Exceeded 2 calls per second for api client. Reduce request rates to resume uninterrupted service.') {
                return response()->json(['message' => 'Rate limit exceeded'], 429);
            }
            // usleep(50000); // Adding a small delay to avoid network issues (50ms)
        }
        return response()->json(['message' => 'Requests completed']);



        // /admin/collects.json?limit=250&page=401



        DB::statement('SET SESSION innodb_lock_wait_timeout = 50');

        return "dasdas";
        $user = User::where('name', 'prouction-app-test-2024.myshopify.com')->first();

        $quantity = '{
            order(id: "gid://shopify/Order/6061612531995") {
                subtotalLineItemsQuantity,
                currentSubtotalLineItemsQuantity
            }
        }';
        return $this->graphQLRequest($user->id, $quantity);
        return isset($getBillingAddress['body']['data']['order']['subtotalLineItemsQuantity']) ? $getBillingAddress['body']['data']['order']['subtotalLineItemsQuantity'] : 1;


        $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/12208570651");
        $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);
        return $ssContractResult;

        return SsBillingAttempt::where('ss_contract_id', 6313)->first();


        // $is_existplan = SsPlan::where('shop_id', $shop->id)->where(['id' => $mlval['id'], 'ss_plan_group_id' => $plangroup->id])->first();


        // $result = $this->ShopifySellingPlan($shop->user_id, $plan);



        // $shop = getShopH();
        // $plan = SsPlan::where('id', $id)->first();
        // $planGroup = SsPlanGroup::where('id', $plan->ss_plan_group_id)->first();
        // $planCount = SsPlan::where('ss_plan_group_id', $plan->ss_plan_group_id)->count();

        // $result = $this->deleteSellingPlan($shop->user_id, $planGroup->shopify_plan_group_id, $plan->shopify_plan_id, $planCount);
        // $newResult = $this->getControllerReturnData($result, 'Saved');



        DB::table('users')->update(['is_old_installation' => true]);

        return "dsa";



        $aa = DB::table('plans')->insert([
            'type' => 'ONETIME',
            'name' => 'FREE',
            'price' => 0.50,
            'capped_amount' => 0,
            'terms' => 'FREE',
            'trial_days' => 0,
            'test' => 1,
            'on_install' => 0,
            'is_free_trial_plans' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        return $aa;



        // $query = DB::table('meta')->where('id', 1)->update(['meta' => $updatedData]);


        // $createMeta = DB::table('meta')->insert([
        //         'name' => 'maintenance',
        //         'meta' => '[
        //                 {
        //                     "key": "secret",
        //                     "value": "ijEO4eUyZFY7zB3l"
        //                 },
        //                 {
        //                     "key": "is_enable",
        //                     "value": "0"
        //                 }
        //         ]' ,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        // ]);
        // return $createMeta;


        $enable =  $request->input('is_enable');
        $row = DB::table('meta')->first();
        $jsonData = json_decode($row->meta, true);


        foreach ($jsonData as &$item) {
            if ($item['key'] === 'is_enable') {
                $item['value'] = $enable; // Update the value to 0 or any other desired value
                break; // Assuming 'key' is unique, stop the loop once found
            }
        }

        $updatedData = json_encode($jsonData);

        DB::table('meta')->where('id', 1)->update(['meta' => $updatedData]);


        return $enable;
        try {
            // Simuate a condition that causes an exception
            $condition = true; // Change this to your actual condition

            if ($condition) {
                throw ValidationException::withMessages([
                    'error' => 'Something went wrong!',
                ]);
            }

            // Normal processing code
            return response()->json(['message' => 'All good!'], 200);
        } catch (ValidationException $e) {
            // Handle validation exception
            Bugsnag::notifyException($e);

            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            // Handle general exceptions

            Bugsnag::notifyException("dsds");

            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
        return 'dsadasd';
        $user = User::where('name', 'testupdateapp.myshopify.com')->first();

        $query = '{
            sellingPlanGroup(id: "gid://shopify/SellingPlanGroup/487587922") {
              options
              appId
              createdAt
              id
              description
              merchantCode
              name
              position
              sellingPlans (first: 10) {
                edges {
                  node {
                    billingPolicy {
                      ... on SellingPlanRecurringBillingPolicy {
                        maxCycles
                        minCycles
                        intervalCount
                        interval
                        createdAt
                      }
                      ... on SellingPlanFixedBillingPolicy {
                        remainingBalanceChargeExactTime
                        remainingBalanceChargeTimeAfterCheckout
                      }
                    }
                    createdAt
                    category
                    description
                    deliveryPolicy {
                      ... on SellingPlanFixedDeliveryPolicy {
                        cutoff
                      }
                    }
                    id
                    name
                    options
                    pricingPolicies {
                      ... on SellingPlanRecurringPricingPolicy {
                        afterCycle
                        adjustmentType
                        adjustmentValue {
                          ... on SellingPlanPricingPolicyPercentageValue {
                            __typename
                            percentage
                          }
                          ... on MoneyV2 {
                            __typename
                            currencyCode
                            amount
                          }
                        }
                        createdAt
                      }
                      ... on SellingPlanFixedPricingPolicy {
                        __typename
                        adjustmentType
                        adjustmentValue {
                          ... on MoneyV2 {
                            __typename
                            amount
                            currencyCode
                          }
                          ... on SellingPlanPricingPolicyPercentageValue {
                            __typename
                            percentage
                          }
                        }
                        createdAt
                      }
                    }
                  }
                }
              }
            }
          }
          ';


        $result = $this->graphQLRequest(53, $query);
        return $result;


        $metafieldJson = [
            "metafield" => [
                'namespace' => 'simplee',
                'key' => 'is_membership_expired',
                'value' => false,
                'type' => 'boolean'
            ]
        ];
        $res =  $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json', $metafieldJson);
        return $res;


        $user = User::where('name', 'simplee-update-prod-with-credits.myshopify.com')->first();
        $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/12212732187");
        $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);
        return $ssContractResult;




        return User::where('name', "simplee-update-prod-with-credits.myshopify.com")->first();

        $response = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json');

        // return $response;

        if (!$response['errors'] && $response['body']['metafields']) {
            foreach ($response['body']['metafields'] as $metafield) {
                if ($metafield['key'] == "is_membership_expired") {
                    $metafieldJson = [
                        "metafield" => [
                            'namespace' => 'simplee',
                            'key' => 'is_membership_expired',
                            'value' => false,
                            'type' => 'boolean'
                        ]
                    ];

                    $res =  $user->api()->rest('PUT', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields/' . $metafield['id'] . '.json', $metafieldJson);
                    logger("-================================");
                    exit;
                }
                logger("ddadadadad");
            }
        }
        return "das";

        $allUser = User::all();
        foreach ($allUser as $user) {
            $metafieldJson = [
                "metafield" => [
                    'namespace' => 'simplee',
                    'key' => 'is_membership_expired',
                    'value' => 0,
                    'type' => 'boolean'
                ]
            ];
            $response[] = $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json', $metafieldJson);
        }
        return $response;
        $user = User::where('id', 68)->first();
        $parameter = [
            "metafield" => [
                'namespace' => 'simplee',
                'key' => 'membership_expires',
                'value' => false,
                'type' => 'boolean'
            ]
        ];
        $response = $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json', $parameter);
        return $response;

        $user = User::find(68);
        $user->update(['plan_id' => 11]);
        return "dasdas";
        $user = User::where('name', 'testupdateapp.myshopify.com')->first();
        $SsContractLineItem = SsContractLineItem::where('ss_contract_id', 5681)->first();
        $draftId = $this->getSubscriptionDraft(5, $SsContractLineItem->shopify_contract_id);
        if ($draftId) {
            $query = '
         mutation{
              subscriptionDraftLineUpdate(
                    draftId: "' . $draftId . '",
                    input: {
                        productVariantId: "gid://shopify/ProductVariant/' . $SsContractLineItem->shopify_variant_id . '",
                        quantity: ' . $SsContractLineItem->quantity . '
                    },
                ){
                    userErrors {
                      code
                      field
                      message
                    }
                }
            }';
            $subscriptionDraftResult = $this->graphQLRequest(5, $query);
            $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineUpdate');
            if ($message == 'success') {
                $result = $this->commitDraft(5, $draftId);
                $message = $result['message'];
            }
            return $message;
        }

        // $user = User::where('id', 37)->first();
        // $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/7724163465399.json';
        // $response = $user->api()->rest('GET', $endPoint);
        // return $response;


        $user = User::where('name', 'testupdateapp.myshopify.com')->first();
        // $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/10376937627");
        // $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);
        // return $ssContractResult;



        // $user = User::where('name', 'testupdateapp.myshopify.com')->first();

        // $user = User::find(33);
        // $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/10400399515");
        // $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);
        // return $ssContractResult;



        $query = '{
                subscriptionContracts(first: 10, query: "created_at:>=2024-05-01 AND created_at:<2024-06-30" ) {
                edges {
                node {
                    id
                    deliveryPrice {
                    amount
                    currencyCode
                    }
                    deliveryPolicy {
                    intervalCount
                    interval
                    }
                    nextBillingDate
                    updatedAt
                    createdAt
                }
                }
                pageInfo {
                hasNextPage
                hasPreviousPage
                }
            }
        }';

        $result = $this->graphQLRequest($user->id, $query);
        return $result;


        // return "hello";
        return  User::where('name', 'nikunj-membership-update-dev.myshopify.com')->update(['password' => '', 'deleted_at' => Carbon::now()]);
        $image = $request->file('image');

        try {

            $file =  Storage::disk('s3')->put('', $image);
            return $file;
            return "Dasds";
        } catch (Exception $e) {
            return $e;
        }


        //

        //     // $contract = SsContract::where('shopify_contract_id',9223405728)->first();
        //     // return $contract;
        //    return  $user = User::where('id',61)->first();
        // $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/shop.json';
        // $result = $user->api()->rest('GET', $endPoint);
        // return $result;



        // $SsContractLineItem = SsContractLineItem::where('ss_contract_id',5236)->first();
        // $shop = getShopH();
        // $user = User::where('id', 5)->first();
        // $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/9382316245311.json';
        // $response = $user->api()->rest('GET', $endPoint);
        // $result = $response['body']->container['product'];
        // $variants = $response['body']->container['product']['variants'];
        // $SsContractLineItem->shopify_product_id = 9382316245311;
        // $SsContractLineItem->title = $result['title'];
        // $SsContractLineItem->shopify_variant_id = $variants[0]['id'];
        // $SsContractLineItem->sku = $variants[0]['sku'];
        // $SsContractLineItem->shopify_variant_title = $variants[0]['title'];
        // $SsContractLineItem->requiresShipping = $variants[0]['requires_shipping'] ? 1 : 0;
        // $SsContractLineItem->shopify_contract_id = $SsContractLineItem->shopify_contract_id;
        // $SsContractLineItem->ss_contract_id = $SsContractLineItem->ss_contract_id;
        // $SsContractLineItem->user_id = $SsContractLineItem->user_id;
        // $SsContractLineItem->currency = $SsContractLineItem->currency;
        // $SsContractLineItem->currency_symbol = $SsContractLineItem->currency_symbol;
        // $SsContractLineItem->quantity = $SsContractLineItem->quantity;
        // $SsContractLineItem->shopify_line_id = "";
        // $result = $this->subscriptionDraftLineAdd(5, $SsContractLineItem);
        // return $result;

        $user = User::find(5);
        $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/28952691007");
        $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);
        return $ssContractResult;

        // 49087016337727
        $SsContractLineItem = SsContractLineItem::where('ss_contract_id', 5298)->first();
        $draftId = $this->getSubscriptionDraft(5, $SsContractLineItem->shopify_contract_id);
        if ($draftId) {
            $query = '
         mutation{
              subscriptionDraftLineUpdate(
                    draftId: "' . $draftId . '",
                    input: {
                        quantity: 1,
                        productVariantId: "gid://shopify/ProductVariant/49087016337727",
                    },
                    lineId: "gid://shopify/SubscriptionLine/b6895de7-65ae-40f5-9c1c-8feff1f240f0"
                ){
                    userErrors {
                      code
                      field
                      message
                    }
                }
            }';
            $subscriptionDraftResult = $this->graphQLRequest(5, $query);
            $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineUpdate');
            if ($message == 'success') {
                $result = $this->commitDraft(5, $draftId);
                $message = $result['message'];
            }
            return $message;
        }

        // DB::table('plans')->insert([
        //     'type' => 'ONETIME',
        //     'name' => 'FREE',
        //     'price' => 0.5,
        //     'capped_amount' => 10.00,
        //     'terms' => 'FREE',
        //     'trial_days' => 0,
        //     'test' => 1,
        //     'on_install' => 0,
        //     'created_at' => date("Y-m-d H:i:s"),
        //     'updated_at' => date("Y-m-d H:i:s")
        // ]);


        // return "dsa";
        // return  User::where('name', 'simplee-prod-mem1.myshopify.com')->update(['password' => '', 'deleted_at' => Carbon::now()]);

        // $user = User::where('name', 'testupdateapp.myshopify.com')->first();




        //     $query = '{
        //         subscriptionContracts(first: 10, query: "created_at:>=2024-05-01 AND created_at:<2024-06-05" ) {
        //          edges {
        //            node {
        //              id
        //              deliveryPrice {
        //                amount
        //                currencyCode
        //              }
        //              deliveryPolicy {
        //                intervalCount
        //                interval
        //              }
        //              nextBillingDate
        //              updatedAt
        //              createdAt
        //            }
        //          }
        //          pageInfo {
        //            hasNextPage
        //            hasPreviousPage
        //          }
        //        }
        //    }';

        //     $result = $this->graphQLRequest($user->id, $query);
        return $result['body']->container;


        // return "hello";
        return  User::where('name', 'nikunj-membership-update-dev.myshopify.com')->update(['password' => '', 'deleted_at' => Carbon::now()]);
        $image = $request->file('image');

        try {

            $file =  Storage::disk('s3')->put('', $image);
            return $file;
            return "Dasds";
        } catch (Exception $e) {
            return $e;
        }

        $planGroupVariants = SsPlanGroupVariant::where('shopify_product_id', '8322978840738')->pluck('ss_plan_group_id')->toArray();
        $id = SsPlanGroup::whereIn('id', $planGroupVariants)->latest('updated_at')->first('id')->id;


        return $id;
        try {
            $user = User::where('id', 38)->first();
            $shop = Shop::where('user_id', $user->id)->first(['id', 'currency', 'currency_symbol', 'domain', 'myshopify_domain']);
            $asset = $user->api()->rest('GET', 'admin/themes/158852350227/assets.json', ["asset[key]" => "config/settings_data.json"]);
            $res = $asset['body']['asset']['value'];
            $response = json_decode($res, true);

            $df = 'blocks';
            dd(array_key_exists('blockss', $response['current']));

            return $response;
            $datas = $response['current']['sadsa'];
            return $datas;

            $setting = SsSetting::where('shop_id', $shop->id)->orderBy('id', 'desc')->first();


            $theme = SsThemeInstall::where('shop_id', $shop->id)->orderBy('id', 'desc')->first();

            if ($setting->theme_app_embed !== 1) {

                $asset = $user->api()->rest('GET', 'admin/themes/148057555263/assets.json', ["asset[key]" => "config/settings_data.json"]);
                $res = $asset['body']['asset']['value'];
                $response = json_decode($res, true);
                $datas = $response['current']['blocks'];
                return $datas;
                foreach ($datas as $data) {

                    $app_embed_id = Str::after($data['type'], 'app-block/');
                    if ($app_embed_id === "3f14561a-79a2-4fb3-9d0e-dd303bf3cc2e") {

                        if ($data['disabled'] == false) {
                            $data = $setting;
                            $data['theme_app_embed']  = 1;
                            $data->save();
                            break;
                        }
                    }
                }
            }
            return "DASda";

            return  $response['current']['blocks'];


            return "dasd";
            $shop = Shop::where('user_id', Auth::user()->id)->first();

            // // dd($request->shop);
            $f = SsSetting::where('shop_id', $shop->id)->update(['theme_app_embed' => 1]);

            return $f;
            // return $f;

            if ($request['contract_id'] == null && $request['contract_id'] == "") {
                return "Contract id is required";
            }
            $contract_id  = SsContract::where('shopify_contract_id', $request['contract_id'])->first('id')->id;

            $orders = SsOrder::where('ss_contract_id', $contract_id)->latest()->get();
            foreach ($orders as $order) {

                $db_rates = ExchangeRate::where('created_at', '<=', $order->created_at)->latest()->first();
                $fromCurrency = 'USD';
                $toCurrency  = $order->order_currency;
                $amount = $order->usd_order_amount;
                $rates = json_decode($db_rates->conversion_rates);
                $calculated = round((($amount * $rates->$toCurrency) / $rates->$fromCurrency), 2);
                $order->update(['order_amount' => $calculated]);
            }


            return "Order Updated Successfully";




            $user  = User::where('id', 26)->first();
            $contract = SsContract::where('shopify_contract_id', 17262510386)->first();
            //   return $contract;
            $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/17262510386");
            $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);

            return ($ssContractResult);





            // return $this->makeWebhooksFromConfig($user);
            // $webhookks =  [
            //     [
            //         'topic' => 'app/uninstalled',
            //         'address' =>  env('AWS_ARN_WEBHOOK_ADDRESS')
            //     ],
            //     [
            //         'topic' => 'shop/update',
            //         'address' => env('AWS_ARN_WEBHOOK_ADDRESS')
            //     ],
            // ];

            // foreach ($webhookks as $newWebhook) {
            //     $res = $user->api()->rest('POST', '/admin/api/' . env('SHOPIFY_API_VERSION') . '/webhooks.json', ['webhook' => $newWebhook]);


            //     logger($res['errors']);
            //     return $res;

            //     if (!$res['errors']) {
            //         logger('==========> Webhook registered successfully');
            //     } else {
            //         logger('something went wrong');
            //         $isSuccess = false;
            //     }
            // }










            $address_type = Util::getShopifyConfig('webhook_address_type');

            if ($address_type === "arn") {
                $query = '
                mutation eventBridgeWebhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $webhookSubscription: EventBridgeWebhookSubscriptionInput!) {
                    eventBridgeWebhookSubscriptionCreate(topic: $topic, webhookSubscription: $webhookSubscription) {
                      userErrors {
                        message
                    }
                      webhookSubscription {
                        id
                        topic
                      }
                    }
                  }
                  ';
            }

            $topics = Util::getShopifyConfig('webhooks');


            // return $topics;
            foreach ($topics as $topic) {


                $variables = [
                    'topic' => $topic['topic'],
                    'webhookSubscription' => [
                        'arn' =>    env('AWS_ARN_WEBHOOK_ADDRESS'),
                        'format' => 'JSON',
                    ],
                ];

                $res =   $user->api()->graph($query, $variables);
                if ($res['errors']) {
                    logger(json_encode($topic));
                }
            }


            $result = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/webhooks.json');
            return $result;

            $user = User::where('id', 5)->first();


            dispatch(new CalculateShopMetrics());

            return "Ddadas";

            $webhookks = [
                [
                    'topic' => 'app/uninstalled',
                    'address' =>  env('AWS_ARN_WEBHOOK_ADDRESS')
                ],
            ];


            // return env('AWS_ARN_WEBHOOK_ADDRESS');


            foreach ($webhookks as $newWebhook) {
                $res = $user->api()->rest('POST', '/admin/api/' . env('SHOPIFY_API_VERSION') . '/webhooks.json', ['webhook' => $newWebhook]);


                logger($res['errors']);
                return $res;

                if (!$res['errors']) {
                    logger('==========> Webhook registered successfully');
                } else {
                    logger('something went wrong');
                    $isSuccess = false;
                }
            }

            return "Dads";



            if ($webhooks['errors']) {
                return false;
                logger('============= Error :: Mannual webhooks register ==============');
                logger($webhooks);
                // dump('ERROR::');
                // dd($webhooks);
            }
            return $webhooks;

            $order_sum = SsOrder::where('shopify_order_id', 5666961752383)->first('order_amount')->order_amount;
            return  $order_sum;

            $shop = User::where('id', 11)->first();
            $result = $shop->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/webhooks.json');
            return $result;

            $query = '{
                sellingPlanGroup(id: "gid://shopify/SellingPlanGroup/1817706815") {
                  options
                  appId
                  createdAt
                  id
                  description
                  merchantCode
                  name
                  position
                  productCount
                  sellingPlans (first: 10) {
                    edges {
                      node {
                        billingPolicy {
                          ... on SellingPlanRecurringBillingPolicy {
                            maxCycles
                            minCycles
                            intervalCount
                            interval
                            createdAt
                          }
                          ... on SellingPlanFixedBillingPolicy {
                            remainingBalanceChargeExactTime
                            remainingBalanceChargeTimeAfterCheckout
                          }
                        }
                        createdAt
                        category
                        description
                        deliveryPolicy {
                          ... on SellingPlanFixedDeliveryPolicy {
                            cutoff
                          }
                        }
                        name
                        options
                        pricingPolicies {
                          ... on SellingPlanRecurringPricingPolicy {
                            afterCycle
                            adjustmentType
                            adjustmentValue {
                              ... on SellingPlanPricingPolicyPercentageValue {
                                __typename
                                percentage
                              }
                              ... on MoneyV2 {
                                __typename
                                currencyCode
                                amount
                              }
                            }
                            createdAt
                          }
                          ... on SellingPlanFixedPricingPolicy {
                            __typename
                            adjustmentType
                            adjustmentValue {
                              ... on MoneyV2 {
                                __typename
                                amount
                                currencyCode
                              }
                              ... on SellingPlanPricingPolicyPercentageValue {
                                __typename
                                percentage
                              }
                            }
                            createdAt
                          }
                        }
                      }
                    }
                  }
                }
              }
              ';


            $result = $this->graphQLRequest(5, $query);
            return $result;

            $user = User::where('name', 'nikunj-membership-dev.myshopify.com')->first();

            $webhooks = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/webhooks.json');
            return $webhooks;


            $contracts = SsContract::where('user_id', $user->id)
                ->where('shopify_contract_id', '!=', null)
                ->where('status', 'active')
                ->where('cc_id', '!=', null)
                ->orderBy('cc_id')
                ->get();

            $previosCcId = '';
            $previosMethod = '';
            $previosResult = null;

            foreach ($contracts as $contract) {
                $query = $this->getPaymentQuery($contract->payment_method, $contract->shopify_contract_id);
                if ($contract->shopify_customer_id && $query) {
                    $result =  $this->graphQLRequest($user->id, $query);
                    // Dont fetch data if exist
                    // if ($previosCcId !== $contract->cc_id && $previosMethod !== $contract->previosMethod) {
                    //   dump('New Got');
                    //   $previosResult = $result;
                    // } else {
                    //   dump('Continue');
                    //   $result = $previosResult;
                    // }
                    $previosCcId = $contract->cc_id;
                    $previosMethod = $contract->previosMethod;

                    if (!$result['errors']) {

                        $paymentMothodData = $result['body']->container['data']['subscriptionContract'];
                        if ($paymentMothodData !== null) {
                            $paymentMothodData = $paymentMothodData['customerPaymentMethod'];

                            $contract->cc_id = (@$paymentMothodData['id']) ? str_replace(
                                'gid://shopify/CustomerPaymentMethod/',
                                '',
                                $paymentMothodData['id']
                            ) : null;

                            $paymentInstrument = $paymentMothodData['instrument'];
                            if ($contract->payment_method == 'credit_card' || $contract->payment_method == 'shop_pay') {
                                isset($paymentInstrument['firstDigits']) ? $contract->cc_firstDigits = $paymentInstrument['firstDigits'] : '';
                                isset($paymentInstrument['source']) ? $contract->cc_source = $paymentInstrument['source'] : '';
                                isset($paymentInstrument['maskedNumber']) ? $contract->cc_maskedNumber = $paymentInstrument['maskedNumber'] : '';
                                isset($paymentInstrument['lastDigits']) ? $contract->cc_lastDigits = $paymentInstrument['lastDigits'] : '';
                                isset($paymentInstrument['expiryYear']) ? $contract->cc_expiryYear = $paymentInstrument['expiryYear'] : '';
                                isset($paymentInstrument['expiryMonth']) ? $contract->cc_expiryMonth = $paymentInstrument['expiryMonth'] : '';
                                isset($paymentInstrument['expiresSoon']) ? $contract->cc_expires_soon = $paymentInstrument['expiresSoon'] : '';
                                isset($paymentInstrument['brand']) ? $contract->cc_brand = $paymentInstrument['brand'] : '';
                                isset($paymentInstrument['name']) ? $contract->cc_name = $paymentInstrument['name'] : '';
                            } else if ($contract->payment_method == 'paypal') {
                                isset($paymentInstrument['paypalAccountEmail']) ? $contract->paypal_account = $paymentInstrument['paypalAccountEmail'] : '';
                                isset($paymentInstrument['inactive']) ? $contract->paypal_inactive = $paymentInstrument['inactive'] : '';
                                isset($paymentInstrument['isRevocable']) ? $contract->paypal_isRevocable = $paymentInstrument['isRevocable'] : '';
                            }

                            $contract->save();
                            dump($contract->id);
                            dump($paymentInstrument);
                        }
                    }
                }
            }

            dd('Test');
            dump($user->name);
            $webhooks = $user->api()->rest('GET', 'admin/webhooks.json');
            $scrpits = $user->api()->rest('GET', 'admin/script_tags.json');
            // $scrpits = $this->makeScriptTagsFromConfig($user);

            dump($webhooks);
            dd($scrpits);

            return $this->makeMissingContract($user);
            // dd($webhooks);


            $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/10758324533");
            $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);

            return ($ssContractResult);

            // CancellationMetricsJob::dispatch();
            // $this->trackPastCancellations();
            // $user = User::find(3);
            // $webhooks = $user->api()->rest('GET', 'admin/webhooks.json');
            // dd($webhooks);
            // $this->trackMetrics();
            // TrackContractJob::dispatch();
            dd('111');
            $user = User::find(22);
            dd($user);
            $this->addCustomPlan(22, 4);
            dd('111');
            $draft_id = $this->getSubscriptionDraft($user->id, 4430102694);
            // dd($draft_id);

            // get discounts

            //  $query = '{
            //       subscriptionContract(id: "gid://shopify/SubscriptionContract/4430102694") {
            //         discounts(first: 10, reverse: true) {
            //           nodes {
            //             title
            //             value {
            //               ... on SubscriptionDiscountPercentageValue {
            //                 __typename
            //                 percentage
            //               }
            //             }
            //             recurringCycleLimit
            //             id
            //           }
            //         }
            //       }
            //     }
            // ';
            // $result = $this->graphQLRequest($user->id, $query);
            // dd($result);

            $discount_id = 'gid://shopify/SubscriptionManualDiscount/f9227b0c-bae8-47d6-a1a9-6323c3009c02';

            // remove discounts

            $query = 'mutation MyMutation {
                    subscriptionDraftDiscountRemove(discountId: "' . $discount_id . '", draftId: "' . $draft_id . '") {
                      discountRemoved
                      draft {
                        discountsRemoved(reverse: true, first: 10) {
                          nodes {
                            ... on SubscriptionManualDiscount {
                              id
                              title
                            }
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
            $result = $this->graphQLRequest($user->id, $query);

            dump($result);
            $result = $this->commitDraft($user->id, $draft_id);
            dd($result);

            dd(SsCustomPlan::where('user_id', 18)->first()->pluck('plan_id'));
            // $webhooks = SsWebhook::where('id', '<', SsWebhook::max('id') - 1000)->orderBy('created_at', 'desc')->get()->toArray();
            $skip = 13759;
            $count = SsWebhook::count();
            if ($count > $skip) {
                $limit = $count - $skip;
                $webhookIds = SsWebhook::latest()->take($limit)->skip($skip)->get()->pluck('id');

                $res = SsWebhook::whereIN('id', $webhookIds)->delete();
            }
            dd($webhooks);
            dd(date('M d, Y'));
            $users = DB::table('users')->select('users.id AS user_id', 'shops.id AS shop_id', 'ss_emails.days_ahead', 'ss_emails.html_body', 'ss_emails.subject', 'ss_contracts.next_order_date', 'ss_contracts.shopify_contract_id', 'ss_contracts.ss_customer_id', 'shops.iana_timezone', 'ss_settings.recurring_notify_email_enabled', 'ss_settings.email_from_email', 'ss_settings.email_from_name', 'ss_customers.email', 'ss_customers.id AS customer_id')->where(['users.active' => 1, 'users.is_working' => 1])
                ->join('shops', 'shops.user_id', '=', 'users.id')
                ->join('ss_emails', 'ss_emails.shop_id', '=', 'shops.id')
                ->join('ss_settings', 'ss_settings.shop_id', '=', 'shops.id')
                ->join('ss_contracts', 'ss_contracts.shop_id', '=', 'shops.id')
                ->join('ss_customers', 'ss_customers.id', 'ss_contracts.ss_customer_id')
                ->where('ss_contracts.status', 'active')
                ->where('ss_emails.category', 'recurring_notify')
                ->where('ss_settings.recurring_notify_email_enabled', 1)
                ->count();
            dd($users);
            dump($users);

            foreach ($users as $key => $user) {
                // $db_contracts = DB::table('ss_contracts')->where('user_id', $user-)
                $default_timezone = date_default_timezone_get();
                date_default_timezone_set($user->iana_timezone);
                $currDate = date('Y-m-d H:i:s');

                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $currDate);
                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $user->next_order_date);
                $diff_in_days = $to->diffInDays($from);

                if ($diff_in_days == $user->days_ahead) {
                    $res = sendMailH($user->subject, $user->html_body, $user->email_from_email, $user->email, $user->email_from_name, $user->shop_id, $user->customer_id);
                    dump($res);
                }
                date_default_timezone_set($default_timezone);
            }
            dd('11');

            $activeExpiringContracts = SsContract::select('id', 'status')->where('status_display', 'Active - Expiring')->where('next_order_date', '<', Carbon::now())->get();
            dump($activeExpiringContracts);
            $activeExpiringContracts = SsContract::select('id', 'status')->where('status_display', 'Active - Expiring')->where('next_processing_date', '<', Carbon::now())->get();
            dd($activeExpiringContracts);
            $user = User::find(22);
            $this->fetchMigratedSavedSearch();
            dd('11');
            $query = '{
            segmentMigrations(first: 250) {
              nodes {
                id
                savedSearchId
                segmentId
              }
            }}';
            $result = $this->graphQLRequest($user->id, $query);
            $segments = $result['body']->container['data']['segmentMigrations']['nodes'];

            foreach ($segments as $key => $segment) {
                // $planGroup = SsPlanGroup::where('shopify_css_id', )

            }

            $this->migrateSavedSearch($user);
            dd('1111');
            $query = '{
                    shop {
                      name
                      customerSavedSearches(
                        first: 10
                        reverse: true
                        query: "*simplee*"
                        sortKey: NAME
                      ) {
                        edges {
                          node {
                            name
                            query
                            id
                          }
                        }
                      }
                    }
                  }';
            $result = $this->graphQLRequest($user->id, $query);
            dd($result);

            dd(date('M d, Y'));

            $data = [];

            $res = $user->api()->rest('GET', 'admin/shop.json')['response'];
            dd($res);
            dd($res->get_headers());
            for ($i = 0; $i < 50; $i++) {
                $data[] = $user->api()->rest('GET', 'admin/shop.json')['response'];
            }
            dd($data);

            $contract = SsContract::select('id')->where('id', 6715)->with('ActivityLog', 'LineItems')->get()->toArray();
            dd($contract);
            dd($result);
            // $shop = DB::table('shops')->find(18);
            dd($db_portal);
            $this->updateDisplayStatus();
            dd('Status Updated');
            $id = 1234;
            $key = 'MissingCustomers' . $id;
            session([$key => []]);
            $data = Session::get($key);
            // dd($data);

            array_push($data, 'xmurray@hotmail.com');
            array_push($data, 'mauricio28@gulgowski.biz');
            array_push($data, 'shad.bradtke@thompson.biz');
            session([$key =>  $data]);

            // $data['missing_customers']['email'] =
            $from = 'ruchita.crawlapps@gmail.com';
            $to = 'ruchita.crawlapps@gmail.com';
            $subject = 'Migration complete';
            $fromName = 'Simplee Membership';

            $d['shop'] = 'simplee-test-2.myshopify.com';
            $d['id'] = 1234;
            $d['key'] = $key;
            $res = Mail::send('mail.migrate', $d, function ($message) use ($from, $to, $fromName, $subject) {
                $message->from($from, $fromName);
                $message->to($to);
                $message->subject($subject);
            });
            dd('111');

            // $data['contract_id'] = $request->contract_id;
            // $data['customer_id'] = $request->customer_id;
            // $data['order_id'] = $request->order_id;

            // $user = User::find($request->user_id);
            // $shop = Shop::where('user_id', $request->user_id)->first();
            // $domain = 'simplee-test-2.myshopify.com';

            // $this->createMembershipFromOrder($user, $shop, $data);
            // dd('Contract created');
            // $this->AddMemberNumberToExistingContract();
            // dd('11111');
            // $domain = 'simplee-test-2.myshopify.com';
            // dd($domain);
            // $domain = str_replace('-', '_', $domain);
            // $domain = str_replace('.', '_', $domain);
            // $const = 'const.PREVENT_NUMBER_FOR_MEMBER';
            // dd(config("$const.$domain"));
            // dd(config("const.PREVENT_NUMBER_FOR_MEMBER.$domain"));

            // dump(array_key_exists('simplee-test-2.myshopify.com', config('const.PREVENT_NUMBER_FOR_MEMBER')));
            // dd(in_array('71', config("const.PREVENT_NUMBER_FOR_MEMBER.$domain")));
            // $LastMembernumber = SsContract::select('member_number')->orderBy('created_at', 'desc')->limit(1)->get();
            // dd($LastMembernumber);
            // // SsPlanGroup::find(60)->delete();
            // $num = '115.675562569';
            // dump(strlen($num) - strrpos($num, '.') - 1 );
            // dd(round($num, 6));
            // $webhooks = [2007953, 2002808, 1999390, 1996116, 1995455, 1991837, 1991782, 1976336, 1976319, 1976261, 1975701, 1973835, 1969988];

            $user = User::where('name', 'simplee-test-2.myshopify.com')->first();

            $sh_product = $this->getShopifyData($user, 7139455598758, 'product', $fields = 'id,variants');
            dd($sh_product);
            $payload = '{"shop_id":60331262158,"shop_domain":"portlandgamelibrary.myshopify.com","customer":{"id":5333725085902,"email":"finaltest@genemerrill.com","phone":null},"orders_to_redact":[3812611260622]}';
            $this->sendGDPRMail(12011, $user, 'customers/data_request', $payload);
            dd('1111');
            // $shop = Shop::where('user_id', $user->id)->first();
            // $id = 1299546278;
            // $webhookResonse = SsWebhook::where('user_id', $user->id)->where('topic', 'subscription_contracts/create')->where('body', 'like', '%'. $id. '%')->first();
            // dd($webhookResonse);
            // dump($webhookResonse->body);
            // $data = json_decode($webhookResonse->body);
            // dd($data);

            // $res = TrackContractJob::dispatch();
            $this->sendAccountInvites($user, 4385689960614, '', '');
            // $this->AddMemberNumberToExistingContract();

            // $this->AddMemberNumberToExistingContract();

            // dd('Member count updated');
            // dd('1111111111111111111111111');

            // $this->addInitialCursor();
            // $this->trackContract();
            // dd('1111');
            // $asset = $this->getAsset();

            // $formIndex = strpos($asset,"form 'product'");

            // dd($formIndex);
            // $endFormIndex = (strpos($asset,"%}", $formIndex) + 2);

            // $newAsset = substr_replace($asset, "\n{% render 'simplee-widget', product:product %}", $endFormIndex, 0);

            // dd($data);
            $webhookResonse = DB::table('ss_webhooks')->find(13908);
            event(new CheckSubscriptionContract($webhookResonse->id, 22, 18, $webhookResonse->body));
            dd('11');
            // $user = User::where('name', 'ianchanfansclub.myshopify.com')->first();

            // $user = User::find(543);
            // dd($user);
            // $contracts =  DB::table('ss_contracts')
            //             ->select('user_id','member_number', DB::raw('COUNT(*) as `count`'))
            //             ->groupBy('user_id', 'member_number')
            //             ->having('count', '>', 1)
            //             ->having('user_id', 543)
            //             ->get()->pluck('user_id', 'member_number', 'count');


            $user = User::where('name', 'simplee-test-2.myshopify.com')->sharedLock()->first();
            dd($user);
            $contract = DB::table('ss_contracts')->where('user_id', 921)->orderBy('created_at', 'desc')->take(5)->get()->pluck('member_number', 'created_at');
            dd($contract);
            // $webhookResonse = DB::table('ss_webhooks')->find(13760);
            // event(new CheckSubscriptionContract($webhookResonse->id, 22, 18, $webhookResonse->body));
            dd('111');
            $user = collect($user);
            $result = $user->api()->rest('GET', 'admin/shop.json')['body']['shop'];
            dd($result);
            $user = new User;
            $user->name = 'test123';
            $user->email = 'test123@gmail.com';
            $user->password = '232323';
            $user->save();

            dd(User::orderBy('created_at', 'desc')->first());

            dd('111');
            dd($webhooks);
        } catch (\Exception $e) {
            logger("============= ERROR ::  test =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    public function getPaymentQuery($method, $customerId)
    {
        switch ($method) {
            case 'credit_card':

                return '
          query MyQuery {
            subscriptionContract(id: "gid://shopify/SubscriptionContract/' . $customerId . '") {
              customerPaymentMethod {
                instrument {
                  ... on CustomerCreditCard {
                    firstDigits
                    source
                    maskedNumber
                    lastDigits
                    expiryYear
                    expiryMonth
                    expiresSoon
                    brand
                    name
                  }
                }
                id
              }
            }
          }
        ';

                break;

            case 'paypal':

                return '
          query MyQuery {
            subscriptionContract(id: "gid://shopify/SubscriptionContract/' . $customerId . '") {
              customerPaymentMethod {
                instrument {
                  ... on CustomerPaypalBillingAgreement {
                    paypalAccountEmail
                    inactive
                    isRevocable
                  }
                }
                id
              }
            }
          }
        ';

                break;

            case 'shop_pay':

                return '
          query MyQuery {
            subscriptionContract(id: "gid://shopify/SubscriptionContract/' . $customerId . '") {
              customerPaymentMethod {
                instrument {
                  ... on CustomerShopPayAgreement {
                    __typename
                    expiresSoon
                    expiryMonth
                    expiryYear
                    inactive
                    isRevocable
                    lastDigits
                    maskedNumber
                    name
                  }
                }
                id
              }
            }
          }
        ';

                break;

            default:
                return false;
                break;
        }
    }

    public $startTime = null;
    public $endTime = null;

    public function trackPastCancellations()
    {
        DB::beginTransaction();

        $this->startTime = Carbon::createFromFormat('d/m/Y H:i:s', '01/09/2022 00:00:00')->settings([
            'timezone',
            'UTC',
        ]);
        $this->endTime = $this->startTime->copy()->endOfDay();

        $shops = Shop::with(['user' => function ($query) {
            $query->where('password', '<>', null);
        }])->get();
        ini_set('xdebug.max_nesting_level', 999);

        $this->recordsWithDate($shops);

        DB::rollBack();

        dd('end');
    }

    public function recordsWithDate($shops)
    {
        foreach ($shops as $shop) {
            $record = SsMetric::where('shop_id', '=', $shop->id)
                ->where('cancelled_subscriptions', null)
                ->whereBetween('created_at', [
                    $this->startTime,
                    $this->endTime,
                ])
                ->first();
            if ($record) {
                $cacelledSubscriptions = SsCancellation::where('shop_id', '=', $shop->id)
                    ->whereBetween('created_at', [
                        $this->startTime,
                        $this->endTime,
                    ])->count();

                if ($cacelledSubscriptions > 0) {
                    $record->cancelled_subscriptions = $cacelledSubscriptions;

                    dump('Shop Id :: ' . $shop->id);
                    dump('Satrt time :: ' . $this->startTime);
                    dump('End time :: ' . $this->endTime);
                } else {
                    $record->cancelled_subscriptions = 0;
                }
                $record->save();
            }
        }
        $this->startTime = $this->startTime->copy()->addDay();
        $this->endTime = $this->startTime->copy()->endOfDay();

        if (Carbon::parse('2022-10-31 00:00:00')->greaterThanOrEqualTo($this->startTime)) {
            $this->recordsWithDate($shops);
        } else {
            $this->startTime = Carbon::createFromFormat('d/m/Y H:i:s', '01/09/2022 00:00:00')->settings([
                'timezone',
                'UTC',
            ]);
            $this->endTime = $this->startTime->copy()->endOfDay();
        }
    }

    public function trackMetrics()
    {
        try {

            $this->yesterday = Carbon::yesterday()
                ->settings([
                    'timezone',
                    'UTC',
                ]);

            $this->yesterdayStartOfTheDay = $this->yesterday->copy()->startOfDay();

            $this->yesterdayEndOfTheDay = $this->yesterday->endOfDay();

            logger('============== START:: CalculateMetrics ===========');
            logger('============== Yesterday Start :: ' . $this->yesterdayStartOfTheDay . ' ===========');
            logger('============== Yesterday End :: ' . $this->yesterdayEndOfTheDay . ' ===========');
            // Look for any shops that are currently installed
            // shops that have a row in the users table with password <> NULL
            // TODO: Check the relationship between shops and users
            $shops = Shop::with(['user' => function ($query) {
                $query->where('password', '<>', null);
            }])->get();

            foreach ($shops as $shop) {

                // Calculate active_subscriptions for each shop
                $active_subscriptions = SsContract::where([
                    ['shop_id', '=', $shop->id],
                    ['status', '=', 'active'],
                ])->get()->count();

                // Calculate paused_subscriptions for each shop
                $paused_subscriptions = SsContract::where([
                    ['shop_id', '=', $shop->id],
                    ['status', '=', 'paused'],
                ])->get()->count();

                // new_subscriptions
                // All contracts which were created between 00:00:00 and 23:59:59UTC the previous day
                $new_subscriptions = SsContract::where('shop_id', '=', $shop->id)
                    ->whereBetween('created_at', [
                        $this->yesterdayStartOfTheDay,
                        $this->yesterdayEndOfTheDay,
                    ])->get()->count();

                // orders_processed
                // All orders in which were created between 00:00:00 and 23:59:50UTC the previous day
                $orders_processed = SsOrder::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [
                        $this->yesterdayStartOfTheDay,
                        $this->yesterdayEndOfTheDay,
                    ])->get();
                logger('============== Order Processes ===========');
                logger(json_encode($orders_processed));

                // amount_processed
                // Sum of all orders in ss_orders which were created between 00:00:00 and 23:59:59 the previous day.
                // in shop’s default currency
                $amount_processed = 0;
                foreach ($orders_processed as $order) {
                    if ($shop->currency != $order->order_currency) {
                        $amount_processed += calculateCurrency(
                            $order->order_currency,
                            $shop->currency,
                            $order->order_amount
                        );
                    } else {
                        $amount_processed += $order->order_amount;
                    }
                }

                // cacelledSubscriptions
                // All contracts which were cancelled between 00:00:00 and 23:59:59UTC the previous day
                $cacelledSubscriptions = SsCancellation::where('shop_id', '=', $shop->id)
                    ->whereBetween('created_at', [
                        $this->yesterdayStartOfTheDay,
                        $this->yesterdayEndOfTheDay,
                    ])->get()->count();

                SsMetric::create([
                    'shop_id' => $shop->id,
                    'shop_currency' => $shop->currency,
                    'date' => $this->yesterday,
                    'active_subscriptions' => $active_subscriptions,
                    'paused_subscriptions' => $paused_subscriptions,
                    'new_subscriptions' => $new_subscriptions,
                    'cancelled_subscriptions' => $cacelledSubscriptions ?? 0,
                    'orders_processed' => $orders_processed->count(),
                    'amount_processed' => $amount_processed ?? 0,
                ]);
            }
            logger('============== END:: CalculateMetrics ===========');
        } catch (\Exception $e) {
            logger("============= ERROR ::  trackMetrics =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function fetchMigratedSavedSearch()
    {
        try {
            $updated = [];

            $users = User::where('is_working', 1)->get();
            foreach ($users as $key => $user) {
                $query = '{
              segmentMigrations(first: 250) {
                nodes {
                  id
                  savedSearchId
                  segmentId
                }
              }}';
                $result = $this->graphQLRequest($user->id, $query);
                $segments = $result['body']->container['data']['segmentMigrations']['nodes'];

                foreach ($segments as $key => $segment) {
                    $sh_css_id_arr = explode('/', $segment['savedSearchId']);
                    $sh_css_id = end($sh_css_id_arr);

                    $sh_segment_id_arr = explode('/', $segment['segmentId']);
                    $sh_segment_id = end($sh_segment_id_arr);

                    $planGroup = SsPlanGroup::where('shopify_css_id', $sh_css_id)->first();
                    if ($planGroup) {
                        $planGroup->shopify_css_id = $sh_segment_id;
                        $planGroup->save();

                        $updated[$user->id][] = $planGroup->id;
                    }
                }
            }
            dd($updated);
        } catch (\Exception $e) {
            logger("============= ERROR ::  fetchMigratedSavedSearch =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function migrateSavedSearch($user)
    {
        try {
            $query = '{
           shop {
            name
            customerSavedSearches(
              first: 250
              reverse: true
              query: "*simplee*"
              sortKey: NAME
            ) {
              edges {
                node {
                  name
                  query
                  id
                }
              }
            }
          }
        }';

            $result = $this->graphQLRequest($user->id, $query);
            // dd($result);
            logger(json_encode($result));
            if (!$result['errors']) {
                $savedSearches = $result['body']['data']['shop']['customerSavedSearches']['edges'];
                // dd($savedSearches);
                foreach ($savedSearches as $savedSearchKey => $savedSearchNode) {
                    $savedSearch = $savedSearchNode['node'];
                    $isSegmentExist = $this->checkSegmentExist($savedSearch['name'], $user);
                    $planGroups = SsPlanGroup::select('id')->where('shopify_css_id', $savedSearch['id'])->get();
                    // dump('Saved Search :: ' . $savedSearch['id'] . ' Plan Group :: ' . json_encode($planGroups));
                    // dump($planGroups['id']);
                }
            } else {
                dd($result);
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  migrateSavedSearch =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function checkSegmentExist($segmentName, $user)
    {
        try {
            $segmentName = "'" . $segmentName . "'";
            $query = '{
           segments(query: "name:' . $segmentName . '", reverse: true, first: 1) {
            nodes {
              name
              query
              id
            }
          }
        }';
            $result = $this->graphQLRequest($user->id, $query);

            if (!$result['errors']) {
                $segment = $result['body']->container['data']['segments']['nodes'];
                if (!empty($segment)) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            logger("============= ERROR ::  checkSegmentExist =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function trackContract()
    {
        // $contracts = SsTrackContract::groupBy('user_id')->orderBy('created_at', 'desc')->get();
        $contracts = \DB::table('ss_track_contracts')->select(\DB::raw('DISTINCT(user_id)'))->orderBy('created_at')->get();
        // $contracts = \DB::table('ss_track_contracts')->latest('created_at')->distinct('user_id')->get();
        dd($contracts);
    }

    public function addInitialCursor()
    {
        $activeUsers = Charge::select('users.id', 'shops.id AS shop_id')->where('status', 'ACTIVE')
            ->join('users', 'users.id', '=', 'charges.user_id')
            ->join('shops', 'shops.user_id', '=', 'users.id')
            ->where('users.password', '!=', null)
            ->where('users.plan_id', '!=', null)
            ->where('users.active', 1)->where('users.deleted_at', null)->get();

        // Add cusor for all user for first time
        foreach ($activeUsers as $key => $value) {
            $res = $this->fetchLastContract($value);
            // dump($res);
            if ($res == '') {
                continue;
            }
            dump($res);
            $contract = (!empty($res['data']['subscriptionContracts']['edges'])) ? $res['data']['subscriptionContracts']['edges'][0] : [];

            if (!empty($contract)) {
                $trackcontract = new SsTrackContract;
                $trackcontract->user_id = $value->id;
                $trackcontract->shop_id = $value->shop_id;
                $trackcontract->shopify_contract_id = str_replace("gid://shopify/SubscriptionContract/", "", $contract['node']['id']);
                $trackcontract->last_tracked_cursor = $contract['cursor'];
                $trackcontract->save();
            }
        }

        dd('Contract saved!');
    }

    public function fetchContract($sh_contract_id, $user)
    {
        $query = '{
            subscriptionContract(id: "gid://shopify/SubscriptionContract/' . $sh_contract_id . '") {
              billingPolicy {
                interval
                intervalCount
                maxCycles
                minCycles
              }
              currencyCode
              customer {
                id
              }
              deliveryPolicy {
                intervalCount
                interval
              }
            }
          }';

        $result = $this->graphQLRequest($user->id, $query);
        return $result;
    }

    public function fetchLastDayContracts($user)
    {
        $query = '{
             subscriptionContracts(first: 10, query: "created_at:>=2020-01-01 AND created_at:<2020-05-01" ) {
              edges {
                node {
                  id
                  deliveryPrice {
                    amount
                    currencyCode
                  }
                  deliveryPolicy {
                    intervalCount
                    interval
                  }
                  nextBillingDate
                  updatedAt
                  createdAt
                }
              }
              pageInfo {
                hasNextPage
                hasPreviousPage
              }
            }
        }';

        $result = $this->graphQLRequest($user->id, $query);
        return $result['body']->container;
    }

    public function fetchLastContract($user)
    {
        $query = '{
            subscriptionContracts(reverse: true, first: 1) {
              edges {
                cursor
                node {
                  id
                  createdAt
                  updatedAt
                  status
                }
              }
            }
          }';

        $result = $this->graphQLRequest($user->id, $query);
        return (!$result['errors']) ? $result['body']->container : '';
    }

    // never delete
    public function createMembershipFromOrder($user, $shop, $data)
    {
        $sh_contract_id = $data['contract_id'];
        $sh_order_id = $data['order_id'];
        $sh_customer_id = $data['customer_id'];

        $webhookResonse = SsWebhook::where('user_id', $user->id)->where('topic', 'subscription_contracts/create')->where('body', 'like', '%' . $sh_contract_id . '%')->where('id', '>', 8400000)->first();

        if (!$webhookResonse) {

            $sh_contract = $this->fetchContract($sh_contract_id, $user);

            if (!$sh_contract['errors']) {
                $contractData = $sh_contract['body']->container['data']['subscriptionContract'];
                dump($contractData);
                $webhookJson = '
              {
                "admin_graphql_api_id": "gid://shopify/SubscriptionContract/' . $sh_contract_id . '",
                "id": ' . $sh_contract_id . ',
                "billing_policy": {
                  "interval": "' . $contractData['billingPolicy']['interval'] . '",
                  "interval_count": ' . $contractData['billingPolicy']['intervalCount'] . ',
                  "min_cycles": "' . $contractData['billingPolicy']['minCycles'] . '",
                  "max_cycles": "' . $contractData['billingPolicy']['maxCycles'] . '"
                },
                "currency_code": "' . $contractData['currencyCode'] . '",
                "customer_id": ' . $sh_customer_id . ',
                "admin_graphql_api_customer_id": "gid://shopify/Customer/' . $sh_customer_id . '",
                "delivery_policy": {
                  "interval": "' . $contractData['deliveryPolicy']['interval'] . '",
                  "interval_count": ' . $contractData['deliveryPolicy']['intervalCount'] . '
                },
                "status": "active",
                "admin_graphql_api_origin_order_id": "gid://shopify/Order/' . $sh_order_id . '",
                "origin_order_id": ' . $sh_order_id . '
              }';

                dump($webhookJson);
                $db_webhook = new SsWebhook;
                $db_webhook->topic = 'subscription_contracts/create';
                $db_webhook->user_id = $user->id;
                $db_webhook->shop_id = $shop->id;
                $db_webhook->api_version = '2021-07';
                $db_webhook->body = $webhookJson;
                $db_webhook->status = 'new';
                $db_webhook->save();

                dump($db_webhook);
                event(new CheckSubscriptionContract($db_webhook->id, $user->id, $shop->id, $webhookJson));
            } else {
                dd('Contract not exist');
            }
        } else {
            event(new CheckSubscriptionContract($webhookResonse->id, $user->id, $shop->id, $webhookResonse->body));
        }
        dd('Webhook creaetd');
    }

    public function subscriptionContractLineItems($contractId)
    {
        $query = '
                {
                        subscriptionContract(id: "' . $contractId . '") {
                            appAdminUrl
                          lastPaymentStatus
                          nextBillingDate
                          billingPolicy {
                              interval
                              intervalCount
                              maxCycles
                              minCycles
                              anchors {
                                day
                                month
                                type
                              }
                            }
                            deliveryPolicy {
                              interval
                              intervalCount
                              anchors {
                                day
                                month
                                type
                              }
                            }
                            deliveryPrice {
                              amount
                              currencyCode
                            }
                            lines (first: 150){
                              edges {
                                node {
                                  id
                                  customAttributes {
                                    key
                                    value
                                  }
                                  productId
                                  variantId
                                  currentPrice {
                                    amount
                                    currencyCode
                                  }
                                  pricingPolicy {
                                    basePrice {
                                      amount
                                      currencyCode
                                    }
                                    cycleDiscounts {
                                      adjustmentType
                                      afterCycle
                                      computedPrice {
                                        amount
                                        currencyCode
                                      }
                                    }
                                  }
                                  variantImage {
                                    originalSrc
                                  }
                                  sku
                                  title
                                  quantity
                                  taxable
                                  sellingPlanName
                                  sellingPlanId
                                  requiresShipping
                                  variantTitle
                                  lineDiscountedPrice {
                                    amount
                                    currencyCode
                                  }
                                }
                              }
                            }
                            deliveryMethod {
                            ... on SubscriptionDeliveryMethodShipping {
                              __typename
                              address {
                                 address1
                                address2
                                city
                                company
                                country
                                countryCode
                                firstName
                                lastName
                                name
                                phone
                                province
                                provinceCode
                                zip
                              }
                              shippingOption {
                                code
                                description
                                presentmentTitle
                                title
                              }
                            }
                          }
                          customerPaymentMethod {
                              id
                              instrument {
                                ... on CustomerCreditCard {
                                  __typename
                                  firstDigits
                                  brand
                                  expiresSoon
                                  expiryMonth
                                  expiryYear
                                  isRevocable
                                  lastDigits
                                  maskedNumber
                                  name
                                  source
                                }
                                ... on CustomerPaypalBillingAgreement {
                                  __typename
                                  paypalAccountEmail
                                }
                                ... on CustomerShopPayAgreement {
                                  __typename
                                  lastDigits
                                  name
                                  expiryYear
                                  expiryMonth
                                  expiresSoon
                                  isRevocable
                                  maskedNumber
                                }
                              }
                            }
                      }
                }
            ';
        return $query;
    }

    public function AddMemberNumberToExistingContract()
    {
        $shops = Shop::all();
        foreach ($shops as $key => $shop) {
            $contracts = SsContract::where('shop_id', $shop->id)->orderBy('created_at', 'asc')->get();

            foreach ($contracts as $key => $contract) {
                $contract->member_number = $key + 1;
                $contract->save();
            }
            $shop->member_number = count($contracts);
            $shop->save();
        }
    }

    //never delete
    public function createMissingMembershipShopify(Request $request)
    {
        try {
            $err = [];

            $ids = ['contract_id', 'customer_id', 'order_id', 'user_id'];
            foreach ($ids as $key => $id) {
                if ($request->$id == '' || $request->$id == null) {
                    $err[] = "$id is required";
                }
            }

            if (!empty($err)) {
                dd($err);
            }

            $data['contract_id'] = $request->contract_id;
            $data['customer_id'] = $request->customer_id;
            $data['order_id'] = $request->order_id;


            $user = User::find($request->user_id);
            $shop = Shop::where('user_id', $request->user_id)->first();

            $this->createMembershipFromOrder($user, $shop, $data);
            dd('Contract created');
        } catch (\Exception $e) {
            logger("============= ERROR ::  createMissingMembershipShopify =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    //never delete
    public function checkMissingContractsInWebhook()
    {
        try {

            $webhooks = \DB::table('ss_webhooks')->where('topic', 'subscription_contracts/create')->where('user_id', 921)->where('id', '>', 9000000)->get();
            $contract_ids = [];
            $notExistIds = [];
            foreach ($webhooks as $key => $value) {
                $body = json_decode($value->body);
                $contract_ids[] = $body->id;

                $db_contract = SsContract::where('shopify_contract_id', $body->id)->where('user_id', $value->user_id)->first();
                if (!$db_contract) {
                    $notExistIds[$value->user_id . '-' . $value->id][] = $body;
                }
            }
            dd($notExistIds);
        } catch (\Exception $e) {
            logger("============= ERROR ::  checkMissingContractsInWebhook =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateDisplayStatus()
    {
        try {
            $contracts = DB::table('ss_contracts')->get();
            foreach ($contracts as $key => $contract) {
                $displayStatus = '';

                if ($contract->is_onetime_payment == 1) {
                    $displayStatus = 'Lifetime Access';
                } elseif ($contract->status == 'cancelled') {
                    $displayStatus = 'Access Removed';
                } elseif ($contract->status == 'expired') {
                    $displayStatus = 'Expired';
                } elseif ($contract->status == 'active') {
                    $displayStatus = 'Active';
                    if ($contract->billing_max_cycles) {
                        if (($contract->order_count + 1) >= $contract->billing_max_cycles) {
                            $displayStatus = 'Active Until Next Bill';
                        }
                    }
                }
                \DB::table('ss_contracts')->where('id', $contract->id)->update(['status_display' => $displayStatus]);
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  updateDisplayStatus =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function chageCurrency(Request $request)
    {
        // $user = User::find($user_id);
        // // get draft id from contract id
        // $query = 'mutation {
        //   subscriptionContractUpdate(contractId: "gid://shopify/SubscriptionContract/' . $contractID . '") {
        //       draft {
        //         id
        //       }
        //       userErrors {
        //           message
        //       }
        //     }
        //   }';
        // $resultSubscriptionContract = $this->graphQLRequest($user->id, $query);
        // $message = $this->getReturnMessage($resultSubscriptionContract, 'subscriptionContractUpdate');
        // if ($message == 'success') {
        //     $subscriptionContract = $resultSubscriptionContract['body']->container['data']['subscriptionContractUpdate'];
        //     return (@$subscriptionContract['draft']['id']) ? $subscriptionContract['draft']['id'] : '';
        // } else {
        //     return $message;
        // }

        // $shop = \DB::table('shops')->where('name',$request->shop_name)->first();
        // $ss_contarct = \DB::table('ss_contracts')->where(['shop_id' => $shop->user_id , 'shopify_contract_id' => $request->shopify_contract_id])->update(
        //     [
        //         'currency_code' =>  isset($request->currency_code) ?  $request->currency_code : ''
        //     ]
        // );
        // $ss_contract_line_items = \DB::table('ss_contract_line_items')->where('shopify_contract_id' , $request->shopify_contract_id)->update([
        //     'currency' => isset($request->currency_code) ?  $request->currency_code : ''
        // ]);

        $draftId = $this->getSubscriptionDraft(25, 19316539689);
        if ($draftId) {
            $query = '
                mutation{
                    subscriptionDraftLineUpdate(
                        draftId: "' . $draftId . '",
                        input: {
                            currentPrice : "22.00"
                        },
                        lineId: "gid://shopify/SubscriptionLine/' . "281e4310-bbc4-4fd3-bfd4-cd02ff34fb89" . '"
                    ){
                        userErrors {
                            code
                            field
                            message
                        }
                    }
                }';

            $subscriptionDraftResult = $this->graphQLRequest(25, $query);
            $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineUpdate');

            if ($message == 'success') {
                $result = $this->commitDraft(25, $draftId);
                $draftId = $this->getSubscriptionDraft(25, "19316539689");
                $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/19316539689");
                $ssContractResult = $this->graphQLRequest(25, $ssContractQuery);
                $message = $ssContractResult;
            }
        }
        return $message;
    }

    public function shippingCostRemove(Request $request)
    {
        try {

            $shop = User::where('name', $request->shop_name)->first();
            $validator = Validator::make($request->all(), [
                'shop_name' => 'required|exists:users,name',
                'shopify_contract_id' => 'required|exists:ss_contracts,shopify_contract_id',
            ]);
            if ($validator->fails()) {
                $messages = $validator->messages();
                return response()->json(['message' => $messages], 422);
            }
            SsContract::where([
                'user_id' => $shop->id,
                'shopify_contract_id' => $request->shopify_contract_id
            ])->update([
                'delivery_price' =>  0
            ]);
            $draftId = $this->getSubscriptionDraft($shop->id, $request->shopify_contract_id);
            if ($draftId) {
                $draftQuery = '
                  mutation{
                    subscriptionDraftUpdate(draftId: "' . $draftId . '", , input: {deliveryPrice: "0"}) {
                        userErrors {
                          code
                          field
                          message
                        }
                        draft {
                          status
                        }
                      }
                  }
                ';
                $resultSubscriptionDraftContract = $this->graphQLRequest($shop->id, $draftQuery);
                $message = $this->getReturnMessage($resultSubscriptionDraftContract, 'subscriptionDraftUpdate');
                if ($message == 'success') {
                    $result = $this->commitDraft($shop->id, $draftId);
                    $message = $result['message'];
                    $ssContractQuery = $this->subscriptionContractLineItems("gid://shopify/SubscriptionContract/" . $request->shopify_contract_id . "");
                    $ssContractResult = $this->graphQLRequest($shop->id, $ssContractQuery);
                    return $ssContractResult;
                }
                return $message;
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  shippingCostRemove =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function ordertest(Request $request)
    {


        $shops = Shop::where('id', 3)->get();

        foreach ($shops as $shop) {
            $user  = User::where('id', $shop->user_id)->first();
            $orders = SsOrder::where('shop_id', $shop->id)->get();
            foreach ($orders as $order) {

                $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/orders/' . $order['shopify_order_id'] .  '.json';
                $order_api  = $user->api()->rest('GET', $endPoint);
                if ($order_api['errors']) {

                    logger("---------------------------------->  Error order id is an " .  $order['shopify_order_id'] . " And error  is an =============>  " .  $order_api['body']);
                    continue;
                }

                $line_items  = $order_api['body']['order']['line_items'];

                if (count($line_items) > 1) {

                    // return $order;
                    $contract = SsContract::where('id', $order['ss_contract_id'])->first();
                    $productId  = SsPlanGroupVariant::where('ss_plan_group_id', $contract->ss_plan_groups_id)->first();
                    foreach ($line_items as $line_item) {


                        if ($line_item['product_exists']) {

                            if ($line_item['product_id'] == $productId->shopify_product_id) {

                                $order->update(['order_amount' => $line_item['price']]);



                                logger("Succes   item id ========================>" . $order['shopify_order_id']);

                                logger("line item id ========================>");
                                break;
                            }
                        } else {
                            if ($line_item['name'] == $productId->product_title) {

                                $order->update(['order_amount' => $line_item['price']]);



                                logger("Succes   item id ========================>" . $order['shopify_order_id']);

                                logger("line item id ========================>");
                                break;
                            }
                        }
                    }
                } else {
                    logger("Succes   item id ========================>" . $order['shopify_order_id']);

                    $order->update(['order_amount' => $line_items[0]['price']]);
                }
            }
        }

        return "dsada";
        $order = SsOrder::where('shopify_order_id', $request->shopify_order_id)->first();
        $shop = Shop::find($order->shop_id);
        $shop_currency = $shop->currency;
        $date = $order->created_at;
        $date->modify('-6 hours');
        $formatted_date = $date->format('Y-m-d H:i:s');
        $getRates = ExchangeRate::where('created_at', '>=', $formatted_date)->first();
        $rates = json_decode($getRates->conversion_rates);
        $calculated = round((($order->usd_order_amount * $rates->$shop_currency) / $rates->USD), 4);
        $order->order_amount = $calculated;
        $order->save();

        return $order;
    }

    function normalizeWhitespace($string)
    {
        return preg_replace('/\s+/', ' ', trim($string));
    }
}
