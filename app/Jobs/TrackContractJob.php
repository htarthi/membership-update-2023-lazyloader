<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\GraphQLTrait;
use App\Models\User;
use App\Models\SsContract;
use App\Models\SsPlanGroup;
use App\Models\SsOrder;
use App\Models\Shop;
use App\Models\SsBillingAttempt;
use App\Models\SsWebhook;
use App\Events\CheckSubscriptionContract;
use App\Events\CheckBillingAttemptFailure;
use App\Events\CheckBillingAttemptSuccess;

use Illuminate\Support\Facades\DB;

class TrackContractJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
  use GraphQLTrait;

  private $user = '';
  private $limit = 180;
  private $page = '';
  private $contractCount = 0;
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct()
  {
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      logger("================ START:: TrackContractJob ==============");
      $this->fetchMigratedSavedSearch();
      logger("================ END:: TrackContractJob ==============");
    } catch (\Exception $e) {
      logger("================ ERROR:: TrackContractJob ==============");
      logger($e->getMessage());
    }
  }

  public function fetchMigratedSavedSearch()
  {
    try {
      logger("================ START:: fetchMigratedSavedSearch ==============");
      $updated = [];
      $error = [];
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
        $result = $this->graph($user, $query, [], '2022-04');

        if (!$result['errors']) {
          $segments = $result['body']['data']['segmentMigrations']['nodes'];
          logger('User :: ' . $user->name . '  Segemnts count :: (  ' . count($segments) . ' )');
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
        } else {
          $error[$user->id] = json_encode($result);
        }
      }
      logger('====== Result :: fetchMigratedSavedSearch =======');
      logger(json_encode($updated));

      logger('====== Result ERROR :: fetchMigratedSavedSearch =======');
      logger(json_encode($error));
    } catch (\Exception $e) {
      dump($e);
      logger("============= ERROR:: fetchMigratedSavedSearch =============");
      logger($e);
    }
  }

  public function getMissingOrder()
  {
    try {
      $missing = [];
      $billings = SsBillingAttempt::select('id', 'shop_id', 'ss_contract_id', 'shopify_order_id')->where('status', 'successful')->get();
      foreach ($billings as $key => $billing) {
        logger("======= getMissingOrder :: $key ======");
        $order = SsOrder::where('shopify_order_id', $billing->shopify_order_id)->first();
        if (!$order) {
          $missing[$billing->shop_id][] = $billing->id;
        }
      }

      logger('========== getMissingOrderResult ==========');
      logger(json_encode($missing));
    } catch (\Exception $e) {
      logger('======= ERROR :: getMissingOrder ========');
      logger($e);
    }
  }

  public function createMissingOrder()
  {
    try {
      $missing = [];
      $success = [];

      $missingOrderUsers = $this->missingOrder();
      foreach ($missingOrderUsers as $user_id => $billings) {
        foreach ($billings as $key => $billing_id) {
          logger("======= getMissingOrder :: $billing_id ======");
          $db_billing = SsBillingAttempt::find($billing_id);
          $contract = SsContract::find($db_billing->ss_contract_id);
          if ($contract) {
            $this->createOrder($contract->user_id, $contract->shop_id, $db_billing->shopify_order_id, $contract->ss_customer_id, $contract->id);
            $success[$user_id] = $billing_id;
          } else {
            $missing[$user_id] = $billing_id;
          }
        }
      }

      logger('========== createMissingOrder ==========');
      logger(json_encode($missing));

      logger('========== createMissingSucessOrder ==========');
      logger(json_encode($success));
    } catch (\Exception $e) {
      logger('======= ERROR :: createMissingOrder ========');
      logger($e);
    }
  }


  public function updateOrderCount()
  {
    try {
      $user = User::find(691);
      $orders = [
        4529078632621,
        4529080107181,
        4529080500397,
        4529089413293,
        4529091215533,
        4529094328493,
        4529092624557,
        4529078960301,
        4529089118381
      ];
      $contracts = SsContract::whereIN('origin_order_id', $orders)->get();
      foreach ($contracts as $key => $contract) {
        $this->updateShopifyNoteAttributes($user, $contract->origin_order_id, 'Membership Order Count', $contract->order_count, 'order');
      }

    //   logger('1213123123231232321323');
    } catch (\Exception $e) {
      logger('======= ERROR :: updateOrderCount ========');
      logger($e);
    }
  }
  public function checkSubscriptions()
  {
    try {
      // check wrong next biling date
      $missingContracts = $this->subscriptions();
      $missing = [];
      $missingDbContract = [];

      foreach ($missingContracts as $key => $missingContract) {
        $date = date('Y-m-d', strtotime($missingContract->nextBillingDate));
        $db_contract = DB::table('ss_contracts')->where('shopify_contract_id', $key)->where('status', 'active')->first();
        if ($db_contract) {
          $db_date = date('Y-m-d', strtotime($db_contract->next_processing_date));
          if ($db_date != $date) {
            $missing[$key] = $date . ' , ' . $db_date;
          }
        } else {
          $missingDbContract[] = $key;
        }
      }
      logger("============ Missing ===========");
      logger(json_encode($missing));

      logger("============ missingDbContract ===========");
      logger(json_encode($missingDbContract));

      // check missing biling attempt
      // $missingIds = $this->ids();
      // $missing = [];
      // foreach ($missingIds as $key => $missingId) {
      //    $idArr = explode(',', $missingId);
      //    if(((int)$idArr[0] - (int)$idArr[1]) > 1){
      //     $missing[] = $key;
      //    }
      // }
      // logger("============ Missing ===========");
      // logger(json_encode($missing));
    } catch (\Exception $e) {
      logger("================ ERROR:: checkSubscriptions ==============");
      logger($e);
    }
  }

  public function checkMissingBillingattemptInShopify($user)
  {
    try {
      $contracts = DB::table('ss_contracts')->where('user_id', $user->id)->get()->toArray();
      $sh_contracts = [];
      $contractErrors = [];
      $missingBillingAttempt = [];
      foreach ($contracts as $key => $contract) {
        $query = '{
                      subscriptionContract(id: "gid://shopify/SubscriptionContract/' . $contract->shopify_contract_id . '") {
                        nextBillingDate
                        billingAttempts(first: 10) {
                          edges {
                            node {
                              completedAt
                              createdAt
                              errorCode
                              errorMessage
                              id
                              idempotencyKey
                              nextActionUrl
                              ready
                              order {
                                legacyResourceId
                              }
                            }
                          }
                        }
                      }
                    }';
        $result = $this->graphQLRequest($user->id, $query);
        if (!$result['errors']) {
          $subscription = $result['body']->container['data']['subscriptionContract'];
          $sh_billing_attempt = $subscription['billingAttempts']['edges'];

          $db_billing_attempt = DB::table('ss_billing_attempts')->where('shopify_contract_id', $contract->shopify_contract_id)->count();
          $shBillingCount = count($sh_billing_attempt);
          if ($db_billing_attempt != $shBillingCount) {
            $sh_contracts[$contract->shopify_contract_id] = $subscription;
            $missingBillingAttempt[$contract->shopify_contract_id] = $db_billing_attempt . ',' . $shBillingCount;
          }
        } else {
          $contractErrors[] = $result;
        }
      }
      logger('======= checkMissingBillingattemptInShopify Contracts :: ========');
      logger(json_encode($contractErrors));

      logger('======= sh_contracts :: ========');
      logger(json_encode($sh_contracts));

      logger('======= missingBillingAttempt Contracts :: ========');
      logger(json_encode($missingBillingAttempt));
    } catch (\Exception $e) {
      logger("================ ERROR:: checkMissingBillingattemptInShopify ==============");
      logger($e);
    }
  }

  public function checkBillingAttempt()
  {
    try {
      $billings = DB::table('ss_billing_attempts')->where('status', 'sent')->where('created_at', '>', '2022-05-09 00:00:00')->get()->toArray();

      foreach ($billings as $key => $billing) {
        logger("================= checkBillingAttemptKEY :: $key =================");
        $shopifyID = $billing->shopify_id;

        $webhookResonse = DB::table('ss_webhooks')->where('shop_id', $billing->shop_id)->whereIN('topic', ['subscription_billing_attempts/success', 'subscription_billing_attempts/failure'])->where('body', 'like', '%' . $shopifyID . '%')->first();

        logger("Shop ID :: $billing->shop_id ========= Shopify ID :: $shopifyID");
        logger(json_encode($webhookResonse));
        if ($webhookResonse) {
          if ($webhookResonse->topic == 'subscription_billing_attempts/success') {
            event(new CheckBillingAttemptSuccess($webhookResonse->id, $webhookResonse->user_id, $webhookResonse->shop_id, $webhookResonse->body));
          } elseif ($webhookResonse->topic == 'subscription_billing_attempts/failure') {
            event(new CheckBillingAttemptFailure($webhookResonse->id, $webhookResonse->user_id, $webhookResonse->shop_id, $webhookResonse->body));
          }
        }

        // $webhookResonse = SsWebhook::where('shop_id', $value->id)->whereIN('topic', ['subscription_billing_attempts/success', 'subscription_billing_attempts/failure'])->where('body', 'like', '%'. $shopifyID. '%')->where('id', '>', 8400000)->first();
      }
    } catch (\Exception $e) {
      logger("================ ERROR :: checkBillingAttempt ===============");
      logger($e);
    }
  }

  public function checkSentBillingAttempt()
  {
    try {
      // $billings = DB::table('ss_billing_attempts')->where('status', 'sent')->get()->toArray();

      $ids = [4779409632];
      $billings = DB::table('ss_billing_attempts')->whereIN('shopify_id', $ids)->get()->toArray();
      $failed = [];
      $successed = [];
      $not_processed = [];
      $missing_user = [];

      foreach ($billings as $key => $billing) {
        $shop = Shop::find($billing->shop_id);
        $user = User::find($shop->user_id);

        if ($user) {
          $shopify_id = $billing->shopify_id;
          $query = '{
                        subscriptionBillingAttempt(id: "gid://shopify/SubscriptionBillingAttempt/' . $shopify_id . '") {
                          completedAt
                          createdAt
                          errorCode
                          errorMessage
                          id
                          idempotencyKey
                          nextActionUrl
                          ready
                        }
                      }';
          $result = $this->graphQLRequest($user->id, $query);
          if (!$result['errors']) {
            $billingAttempt = $result['body']->container['data']['subscriptionBillingAttempt'];
            if ($billingAttempt['errorCode'] == null) {
              $webhookJson = [
                "id" => $shopify_id,
                "admin_graphql_api_id" => "gid://shopify/SubscriptionBillingAttempt/" . $shopify_id,
                "idempotency_key" => $billingAttempt['idempotencyKey'],
                "order_id" => $billing->shopify_order_id,
                "admin_graphql_api_order_id" => "gid://shopify/Order/" . $billing->shopify_order_id,
                "subscription_contract_id" => $billing->shopify_contract_id,
                "admin_graphql_api_subscription_contract_id" => "gid://shopify/SubscriptionContract/" . $billing->shopify_contract_id,
                "ready" => $billingAttempt['ready'],
                "error_message" => null,
                "error_code" => null
              ];
              $webhookJson = json_encode($webhookJson);

              $db_webhook = new SsWebhook;
              $db_webhook->topic = 'subscription_billing_attempts/success';
              $db_webhook->user_id = $user->id;
              $db_webhook->shop_id = $shop->id;
              $db_webhook->api_version = '2021-07';
              $db_webhook->body = $webhookJson;
              $db_webhook->status = 'new';
              $db_webhook->save();

              event(new CheckBillingAttemptSuccess($db_webhook->id, $user->id, $shop->id, $webhookJson));
            } else {
              $failed[] = $shopify_id;
            }
          } else {
            $not_processed[] = $shopify_id;
          }
        } else {
          $missing_user[] = $shop->user_id;
        }
      }
      logger('successed Billing Attempt');
      logger(json_encode($successed));

      logger('failed Billing Attempt');
      logger(json_encode($failed));

      logger('not_processed Billing Attempt');
      logger(json_encode($not_processed));

      logger('missing_user Billing Attempt');
      logger(json_encode($missing_user));
    } catch (\Exception $e) {
      logger("================ ERROR:: checkSentBillingAttempt ==============");
      logger($e);
    }
  }

  public function createMissingBillingAttempt()
  {
    try {
      logger("================ START:: createMissingBillingAttempt ==============");

      $webhookIds = [11599668, 11611701, 11611693, 11611698, 11611702, 11611688, 11611687, 11611708, 11611709, 11611710, 11611711, 11611712, 11611714, 11611717, 11611719, 11611730, 11611735, 11611736, 11611743, 11615227, 11615230, 11615231, 11615235, 11615236, 11615238, 11615261, 11617466, 11624411, 11624412, 11624419];

      $webhookResponses = DB::table('ss_webhooks')->whereIN('id', $webhookIds)->get();
      $missingContract = [];

      foreach ($webhookResponses as $key => $webhook) {
        logger(json_encode($webhook));
        $subscriptionBillingAttempt = json_decode($webhook->body);

        $ssContract = DB::table('ss_contracts')->where('shopify_contract_id', $subscriptionBillingAttempt->subscription_contract_id)->first();

        if ($ssContract) {
          $status = ($webhook->topic == 'subscription_billing_attempts/success') ? 'successful' : 'failed';

          $date = $webhook->created_at;
          $billingAttempt = new SsBillingAttempt;
          $billingAttempt->shop_id = $webhook->shop_id;
          $billingAttempt->shopify_id = $subscriptionBillingAttempt->id;
          $billingAttempt->ss_contract_id = $ssContract->id;
          $billingAttempt->status =  $status;
          $billingAttempt->completedAt = date('Y-m-d H:i:s', strtotime($date));
          $billingAttempt->errorMessage = $subscriptionBillingAttempt->error_message;
          $billingAttempt->idempotencyKey = $subscriptionBillingAttempt->idempotency_key;

          $billingAttempt->shopify_contract_id =  $subscriptionBillingAttempt->subscription_contract_id;
          $billingAttempt->shopify_order_id =  $subscriptionBillingAttempt->order_id;

          $createdAt = date('Y-m-d H', strtotime($date)) . ':01:' . date('s', strtotime($date));
          $billingAttempt->created_at = $createdAt;
          $billingAttempt->updated_at = $createdAt;

          logger(json_encode($billingAttempt));
          $billingAttempt->save();
        } else {
          $missingContract[$webhook->id] = $subscriptionBillingAttempt->subscription_contract_id;
        }
      }
      logger('Missing Contracts :: ');
      logger(json_encode($missingContract));
    } catch (\Exception $e) {
      logger("================ ERROR:: createMissingBillingAttempt ==============");
      logger($e);
    }
  }

  public function retriveContracts($f, $user)
  {
    try {
      logger("================ START:: retriveContracts ==============");
      $shop = Shop::where('user_id', $user->id)->first();
      $allContractsResult = $this->fetchAllContracts($user, $this->limit, $this->page);

      $allContracts = $allContractsResult['subscriptionContracts']['edges'];

      $this->contractCount = $this->contractCount + count($allContracts);

      if (!empty($allContracts)) {
        // foreach ($allContracts as $key => $value) {
        //     // check for webhook exist or not in db if not then create webhook and contract
        //     // Check for contract exist or not if not then create
        //     # code...

        //     $sh_contract = $value['node'];
        //     $sh_contract_id = str_replace("gid://shopify/SubscriptionContract/", '', $sh_contract['id']);

        //     $is_exist_contract = SsContract::where('shopify_contract_id', $sh_contract_id)->where('user_id', $user->id)->first();
        //     if(!$is_exist_contract){
        //         $is_exist_webhook = SsWebhook::where('user_id', $user->id)->where('topic', 'subscription_contracts/create')->where('body', 'like', '%'. $sh_contract_id. '%')->first();
        //         if(!$is_exist_webhook){
        //             $sh_contract['sh_contract_id'] = $sh_contract_id;

        //             $db_webhook_id = $this->createWebhookInDBIfMissing($sh_contract, $user, $shop);
        //         }else{
        //             $db_webhook_id = $is_exist_webhook->id;
        //         }
        //         event(new CheckSubscriptionContract($db_webhook_id, $user->id, $shop->id));
        //     }
        // }

        // // check for next contracts
        $pageInfo = $allContractsResult['subscriptionContracts']['pageInfo'];
        if ($pageInfo['hasNextPage']) {
          $this->page = $allContracts[count($allContracts) - 1]['cursor'];
          // if($f > 0){
          $this->retriveContracts($f - 1, $user);
          // }
        }
      }

      logger("================ END:: retriveContracts ==============");
    } catch (\Exception $e) {
      logger("================ ERROR:: retriveContracts ==============");
      logger($e);
    }
  }

  public function updateMemberCount($user)
  {
    try {
      logger("================ START:: updateMemberCount ==============");
      $orders = SsOrder::select('shopify_order_name', 'shopify_order_id')->where('user_id', $user->id)->get();
      foreach ($orders as $key => $value) {
        $y[(int)substr($value['shopify_order_name'], 1)] = $value['shopify_order_id'];
      };
      ksort($y);

      $k = 1;
      foreach ($y as $key => $v) {
        $contract = SsContract::select('id', 'member_number')->where('origin_order_id', $v)->where('user_id', $user->id)->first();
        if ($contract) {
          if ($k == 99 || $k == 430 || $k == 99430) {
            $k++;
          }
          logger($key . '(' . $v . ') :: ' . $k);
          $contract->member_number = $k;
          $contract->save();
          $k++;
        }
      }
      // $orders = SsOrder::select('shopify_order_name', 'shopify_order_id')->where('user_id', $user->id)->orderBy('shopify_order_name')->get();

      //    $k=1;
      //   foreach ($orders as $key => $value) {

      //     $contract = SsContract::select('id', 'member_number')->where('origin_order_id', $value->shopify_order_id)->where('user_id', $user->id)->first();
      //     if($contract){
      //         if($k == 99 || $k == 430 || $k == 99430){
      //             $k++;
      //         }
      //         logger($value->shopify_order_name . '(' .$value->shopify_order_id . ') :: ' . $k);
      //         $contract->member_number = $k;
      //         $contract->save();
      //         $k++;
      //     }
      //   }
    } catch (\Exception $e) {
      logger("================ ERROR:: updateMemberCount ==============");
      logger($e);
    }
  }

  public function retriveMissingOrders($user)
  {
    try {
      $shop = Shop::where('user_id', $user->id)->first();
      $orderIds = SsOrder::where('user_id', $user->id)->pluck('shopify_order_id')->all();
      $contracts = SsContract::whereNotIn('origin_order_id', $orderIds)->where('user_id', $user->id)->select('id', 'origin_order_id', 'ss_customer_id', 'member_number', 'updated_at')->get();

      foreach ($contracts as $key => $contract) {
        $order = SsOrder::where('user_id', $user->id)->where('shopify_order_id', $contract->origin_order_id)->first();
        if ($order) {
          logger("Exist Order id :: " . $contract->origin_order_id);
        } else {
          logger("Created Order id :: " . $contract->origin_order_id);
          $this->createOrder($user->id, $shop->id, $contract->origin_order_id, $contract->ss_customer_id, $contract->id);
        }
      }
    } catch (\Exception $e) {
      logger("================ ERROR:: retriveMissingOrders ==============");
      logger($e);
    }
  }

  public function createMembershipFromOrder($user, $shop, $data, $webhook_id)
  {
    logger("=============== START:: createMembershipFromOrder ================");
    try {
      $sh_contract_id = $data['contract_id'];
      $sh_order_id = $data['order_id'];
      $sh_customer_id = $data['customer_id'];

      $webhookResonse = SsWebhook::where('user_id', $user->id)->where('topic', 'subscription_contracts/create')->where('body', 'like', '%' . $sh_contract_id . '%')->where('id', '>', 8400000)->first();

      if (!$webhookResonse) {

        $sh_contract = $this->fetchContract($sh_contract_id, $user);

        if (!$sh_contract['errors']) {
          $contractData = $sh_contract['body']->container['data']['subscriptionContract'];
          // dump($contractData);
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

          // dump($webhookJson);
          $db_webhook = new SsWebhook;
          $db_webhook->topic = 'subscription_contracts/create';
          $db_webhook->user_id = $user->id;
          $db_webhook->shop_id = $shop->id;
          $db_webhook->api_version = '2021-07';
          $db_webhook->body = $webhookJson;
          $db_webhook->status = 'new';
          $db_webhook->save();

          // dump($db_webhook);
          event(new CheckSubscriptionContract($db_webhook->id, $user->id, $shop->id, $webhookJson));
        } else {
          logger("Contract not exist :: $sh_contract_id");
        }
      } else {
        event(new CheckSubscriptionContract($webhookResonse->id, $user->id, $shop->id, $webhookResonse->body));
      }
      logger('Webhook created');
      logger("=============== END:: createMembershipFromOrder ================");
    } catch (\Exception $e) {
      logger($e);
    }
  }

  public function checkMissingContractsInWebhook($user)
  {
    try {
      logger("=============== START:: checkMissingContractsInWebhook ================");
      $shop = Shop::where('user_id', $user->id)->first();
      $webhooks = \DB::table('ss_webhooks')->where('topic', 'subscription_contracts/create')->where('id', '>', 8400000)->where('user_id', $user->id)->get();
      $contract_ids = [];
      $notExistIds = [];

      $i = 1;
      foreach ($webhooks as $key => $value) {
        $body = json_decode($value->body);
        $contract_ids[] = $body->id;

        // if($i == 1){
        $db_contract = SsContract::where('shopify_contract_id', $body->id)->where('user_id', $value->user_id)->first();

        // logger(json_encode($db_contract));
        if (!$db_contract) {
          logger("================= I :: $i===================");
          $notExistIds[$value->user_id . '-' . $value->id][] = $body;
          $data['contract_id'] = $body->id;
          $data['customer_id'] = $body->customer_id;
          $data['order_id'] = $body->origin_order_id;

          logger("====================== Missing contract webhook for loop ======================");
          $endPoint = "admin/api/2021-10/orders/$body->origin_order_id.json";
          $shOrderResult = $this->rest($user, $endPoint, [], 'GET');

          if (!$shOrderResult['errors']) {
            $shfinancial_status =  $shOrderResult['body']->container['order']['financial_status'];
            if ($shfinancial_status != 'refunded') {
              $i++;
              // $this->createMembershipFromOrder($user, $shop, $data, $value->id);
              event(new CheckSubscriptionContract($value->id, $user->id, $shop->id, $value->body));
            } else {
              logger("=============== $body->origin_order_id :: Refunded ==============");
            }
          } else {

            logger("$body->origin_order_id Order error");
            logger(json_encode($shOrderResult));
          }
        }
        // }
      }
      // dd($notExistIds);
    } catch (\Exception $e) {
      logger($e);
    }
  }

  public function checkForTags($user, $type)
  {
    try {
      logger("================ START:: checkForCustomerTags ==============");
      $tags = [];
      $mismatchTags = [];

      $dbContracts  = \DB::table('ss_contracts')->select('shopify_customer_id', 'origin_order_id', 'tag_customer', 'tag_order')->where('user_id', $user->id)->get();

      $fieldName = ($type == 'orders') ? 'origin_order_id' : 'shopify_customer_id';
      $bodyT = ($type == 'orders') ? 'order' : 'customer';
      $tagType = ($type == 'orders') ? 'tag_order' : 'tag_customer';

      foreach ($dbContracts as $key => $contract) {
        $parameter['fields'] = 'id,tags';
        $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . "/$type/" . $contract->$fieldName . '.json';
        $result = $this->rest($user, $endPoint, [], 'GET');
        if (!$result['errors']) {
          $resource = $result['body']->container[$bodyT];
          $tags[$resource['id']] = $resource['tags'];

          $resource['tags'] = str_replace(', ', ',', $resource['tags']);
          $tagsArr = explode(',', $resource['tags']);
          if (!in_array($contract->$tagType, $tagsArr)) {
            $mismatchTags[$resource['id'] . '-' . $contract->$tagType] = $resource['tags'];
          }
        } else {
          logger(json_encode($result));
        }
      }
      logger($tags);
      logger("================== mismatchTags ================");
      logger($mismatchTags);
      logger("================ END:: checkForCustomerTags ==============");
    } catch (\Exception $e) {
      logger("================ ERROR:: checkForCustomerTags ==============");
      logger($e);
    }
  }

  public function assignRightCustomerTag($user)
  {
    try {
      logger("================ START:: assignRightCustomerTag ==============");
      $tags = $this->mismatchTagArray();

      foreach ($tags as $key => $tag) {
        $arr = explode('-', $key);
        $customer_id = $arr[0];
        $db_tag = $arr[1];

        $newTag = ($tag == '') ? $db_tag : $db_tag . ',' . $tag;

        $p = [
          'customer' => [
            'id' => $customer_id,
            'tags' => $newTag
          ]
        ];
        $result = $user->api()->rest('PUT', "admin/customers/$customer_id", $p);
        if ($result['errors']) {
          logger(json_encode($result));
        }
      }
      logger("================ END:: assignRightCustomerTag ==============");
    } catch (\Exception $e) {
      logger("================ ERROR:: assignRightCustomerTag ==============");
      logger($e);
    }
  }
}
