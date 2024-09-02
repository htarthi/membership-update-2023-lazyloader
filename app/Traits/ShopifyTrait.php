<?php

namespace App\Traits;

use App\Events\CheckSubscriptionContract;
use App\Models\App;
use App\Models\Country;
use App\Models\ExchangeRate;
use App\Models\Shop;
use App\Models\SsActivityLog;
use App\Models\SsAnswer;
use App\Models\SsCancellation;
use App\Models\SsContract;
use App\Models\SsEvents;
use App\Models\SsOrder;
use App\Models\SsPlan;
use App\Models\SsPlanGroup;
use App\Models\SsEmail;
use App\Models\SsSetting;
use App\Models\SsShippingProfile;
use App\Models\SsShippingZone;
use App\Models\SsWebhook;
use App\Models\SsForm;
use App\Models\SsRule;
use App\Traits\GraphQLTrait;
use App\Models\SsPortal;
use App\Models\SsLanguage;
use App\Models\SsCustomPlan;
use App\Models\SsCancellationReason;
use App\Models\SsContractLineItem;
use App\Models\User;
use App\Models\Feature;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Osiset\BasicShopifyAPI\Options;
use Osiset\ShopifyApp\Storage\Models\Plan;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Osiset\ShopifyApp\Util;
use App\Models\SsStoreCredit;
use App\Models\Featurables;
use App\Models\SsCustomer;
use App\Models\SsPlanGroupVariant;
use App\Models\Transaction;
use Gnikyt\BasicShopifyAPI\BasicShopifyAPI;
use Gnikyt\BasicShopifyAPI\Options as OptionsO;
use Gnikyt\BasicShopifyAPI\Session as SessionO;
use Illuminate\Support\Facades\Http;
/**
 * Trait ShopifyTrait.
 */
trait ShopifyTrait
{
    use GraphQLTrait;
    private $locationIds = [];
    /**
     * @param $method
     * @param $endPoint
     * @param $parameter
     * @param $userID
     * @return false
     */
    public function request($method, $endPoint, $parameter, $userID)
    {
        try {
            $shop = User::where('id', $userID)->first();
            return $shop->api()->rest($method, $endPoint, $parameter);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  request =============");
            logger($e->getMessage());
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @return false|mixed
     */
    public function shopFeature($user_id)
    {
        try {
            $query = '
                {
                  shop {
                    features {
                      eligibleForSubscriptions
                    }
                  }
                }
            ';
            $sh_shop = $this->graphQLRequest($user_id, $query);
            if (!$sh_shop['errors']) {
                return $sh_shop['body']->container['data']['shop']['features']['eligibleForSubscriptions'];
            }
            return false;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  shopFeature =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * Add webhook while any of the webhook calling
     * @param $topic
     * @param $user_id
     * @param $data
     * @return false
     */
    public function webhook($topic, $user_id, $data)
    {
        try {
            $app = App::where('app_url', env('APP_URL'))->first();
            $shop = Shop::where('user_id', $user_id)->first();
            $ss_webhook = new SsWebhook();
            $ss_webhook->topic = $topic;
            $ss_webhook->user_id = $user_id;
            $ss_webhook->shop_id = $shop->id;
            $ss_webhook->api_version = ($app) ? $app['api_version'] : env('SHOPIFY_SAPI_VERSION');
            $ss_webhook->body = $data;
            $ss_webhook->status = 'new';
            $ss_webhook->save();
            return $ss_webhook->id;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  webhook =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user
     * @param $topic
     */
    public function sendGDPRMail($webhookId, $user, $topic, $payload)
    {
        try {
            logger("====================== START:: sendGDPRMail ==> User :: " . $user->id . " ======================");
            $shop = Shop::where('user_id', $user->id)->first();
            if ($shop) {
                $setting = SsSetting::select('email_from_email', 'email_from_name')->where('shop_id', $shop->id)->first();
                $from = $setting->email_from_email;
                $fromname = $setting->email_from_name;
                $to = env('GDPR_MAIL_TO');
                $subject = 'GDPR webhook submitted!';
                $data['topic'] = $topic;
                $data['shop'] = $shop->domain;
                $data['body'] = $payload;
                $res = Mail::send('mail.gdpr', $data, function ($message) use ($subject, $from, $to, $fromname) {
                    $message->from($from, $fromname);
                    $message->to($to);
                    $message->subject($subject);
                });
                // logger(json_encode($res));
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  sendGDPRMail =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user
     * @param $topic
     */
    public function sendErrorMail($user, $topic, $payload)
    {
        try {
            logger("====================== START:: sendErrorMail ==> User :: " . $user->id . " ======================");
            $shop = Shop::where('user_id', $user->id)->first();
            if ($shop) {
                $setting = SsSetting::select('email_from_email', 'email_from_name')->where('shop_id', $shop->id)->first();
                $from = $setting->email_from_email;
                $fromname = $setting->email_from_name;
                $to = env('ERROR_MAIL_TO');
                $subject = $topic;
                $data['topic'] = $topic;
                $data['shop'] = $shop->domain;
                $data['body'] = $payload;
                $res = Mail::send('mail.error-info', $data, function ($message) use ($subject, $from, $to, $fromname) {
                    $message->from($from, $fromname);
                    $message->to($to);
                    $message->subject($subject);
                });
                // logger(json_encode($res));
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  sendErrorMail =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $customer_id
     * @param $contract_id
     * @param $user_type
     * @param $msg
     */
    public function saveActivity($user_id, $customer_id, $contract_id, $user_type, $msg)
    {
        try {
            $shop = Shop::where('user_id', $user_id)->first();
            $activity = new SsActivityLog();
            $activity->shop_id = $shop->id;
            $activity->user_id = $shop->user_id;
            //$activity->ss_plan_id = $user->plan_id;
            $activity->ss_customer_id = $customer_id;
            $activity->ss_contract_id = $contract_id;
            $activity->user_type = $user_type;
            $activity->user_name = $shop->owner;
            $activity->message = $msg;
            $activity->save();
            return $activity;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  saveActivity =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function saveCancellation($shop_id, $shopify_contract_id, $contract_id, $user_type, $type , $reason = NULL)
    {
        try {
            $cancelledType = ($type == 'cancelled') ? 'date_cancelled' : 'date_accessremoved';
            $cancelled_by = ($user_type == 'user') ? 'owner' : 'member';
            $cancellation = new SsCancellation();
            $cancellation->shop_id = $shop_id;
            $cancellation->shopify_contract_id = $shopify_contract_id;
            $cancellation->ss_contract_id = $contract_id;
            $cancellation->$cancelledType = date('Y-m-d H:i:s');
            $cancellation->reason = $reason;
            $cancellation->description = 'Membership #' . $shopify_contract_id . ' was cancelled by the ' . $cancelled_by;
            $cancellation->cancelled_by = $cancelled_by;
            $cancellation->save();
            // logger(json_encode($cancellation));
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  saveCancellation =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function saveContractCancelReason($shop_id, $description, $otherReason)
    {
        try {
            $cancellation = new SsCancellationReason();
            $cancellation->shop_id = $shop_id;
            $cancellation->description = ($description == 'Other') ? $otherReason : $description;
            $cancellation->get_details = ($description == 'Other');
            $cancellation->save();
            // logger(json_encode($cancellation));
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  saveContractCancelReason =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     *
     */
    public function subscriptionUpdateActivity()
    {
        try {
            logger("====================== START:: subscriptionUpdateActivity ======================");
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  subscriptionUpdateActivity =============");
            logger($e->getMessage());
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateWebhookStatus($id, $status, $e)
    {
        try {
            $result = ($status == 'error') ? $e->getMessage() . ' IN ' . $e->getFile() . ' ON Line ' . $e->getLine() : null;
            $webhookResonse = SsWebhook::find($id);
            // logger($webhookResonse);
            $webhookResonse->status = $status;
            $webhookResonse->error_status_result = $result;
            $webhookResonse->save();
        } catch (\Exception $e) {
            logger("============= ERROR ::  updateWebhookStatus =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createSubscriptionContractInShopify($migrateData, $user_id, $isMigrate = false)
    {
        try {
            $user = User::find($user_id);
            $minCycle =  ($migrateData['min_cycles']) ? 'minCycles: ' . $migrateData['min_cycles'] : '';
            $maxCycle =  ($migrateData['max_cycles']) ? 'maxCycles: ' . $migrateData['max_cycles'] : '';
            $cycle = ($minCycle != '') ? $minCycle . ',' . $maxCycle : $maxCycle;
            $query = '
                mutation MyMutation {
                    subscriptionContractCreate(
                        input: {
                            customerId: "gid://shopify/Customer/' . $migrateData['customer_shopify_id'] . '",
                            nextBillingDate: "' . $migrateData['next_billing_date'] . '",
                            currencyCode: ' . $migrateData['currency_code'] . ',
                            contract: {
                                paymentMethodId: "' . $migrateData['paymentMethodId'] . '",
                                billingPolicy: {
                                    intervalCount: ' . $migrateData['billing_interval_count'] . ',
                                    interval: ' . strtoupper($migrateData['billing_interval_type']) . ',
                                    ' . $cycle . '
                                },';
            if ($isMigrate) {
                $query .= 'customAttributes: [
                    {
                      key: "customer_tag",
                      value: "' . $migrateData['customer_tag'] . '"
                    }
                  ],';
            }
            $query .=           'deliveryPolicy: {
                                interval: ' . strtoupper($migrateData['delivery_interval_type']) . ',
                                intervalCount: ' . $migrateData['delivery_interval_count'] . '
                            },
                            deliveryPrice: 0,
                            status: ' . strtoupper($migrateData['subscription_status']) . '
                        }
                    }
                  ){
                    draft {
                      id,
                      status,
                      customerPaymentMethod {
                        id
                        customer {
                          email
                          firstName
                          id
                        }
                      }
                       originalContract {
                        originOrder {
                          id
                        }
                      }
                    }
                    userErrors {
                      code
                      field
                      message
                    }
                  }
                }';
            // $query = '
            //     mutation MyMutation {
            //         subscriptionContractCreate(
            //             input: {
            //                 customerId: "gid://shopify/Customer/' . $migrateData['customer_shopify_id'] .'",
            //                 nextBillingDate: "' . $migrateData['next_billing_date'] .'",
            //                 currencyCode: ' . $migrateData['currency_code'] .',
            //                 contract: {
            //                     paymentMethodId: "' . $migrateData['paymentMethodId'] .'",
            //                     billingPolicy: {
            //                         intervalCount: ' . $migrateData['billing_interval_count'] .',
            //                         interval: ' . strtoupper($migrateData['billing_interval_type']) .',
            //                         '.$cycle.'
            //                     },
            //                     deliveryMethod: {
            //                     shipping: {
            //                         address: {
            //                             address1: "' . $migrateData['shipping_address1'] .'",
            //                             address2: "' . $migrateData['shipping_address2'] .'",
            //                             city: "' . $migrateData['shipping_city'] .'",
            //                             company: "' . $migrateData['next_billing_date'] .'",
            //                             country: "' . $migrateData['next_billing_date'] .'",
            //                             countryCode: "' . $migrateData['shipping_countrycode'] .'",
            //                             firstName: "' . $migrateData['shipping_firstname'] .'",
            //                             lastName: "' . $migrateData['shipping_lastname'] .'",
            //                             phone: "' . $migrateData['next_billing_date'] .'",
            //                             province: "' . $migrateData['next_billing_date'] .'",
            //                             provinceCode: "' . $migrateData['shipping_state'] .'",
            //                             zip: "' . $migrateData['shipping_zip'] .'"
            //                         },
            //                     }
            //                 },
            //                 deliveryPolicy: {
            //                     interval: ' . strtoupper($migrateData['delivery_interval_type']) .',
            //                     intervalCount: ' . $migrateData['delivery_interval_count'] .'
            //                 },
            //                 deliveryPrice: 0.5,
            //                 status: ' . strtoupper($migrateData['subscription_status']) .'
            //             }
            //         }
            //       ){
            //         draft {
            //           id,
            //           status,
            //           customerPaymentMethod {
            //             id
            //             customer {
            //               email
            //               firstName
            //               id
            //             }
            //           }
            //            originalContract {
            //             originOrder {
            //               id
            //             }
            //           }
            //         }
            //         userErrors {
            //           code
            //           field
            //           message
            //         }
            //       }
            //     }';
            $result = $this->graphQLRequest($user->id, $query);
            // logger("==============================================================================");
            // logger(json_encode($result));
            $message = $this->getReturnMessage($result, 'subscriptionContractCreate');
            $res['success'] = false;
            $res['message'] = $message;
            if ($message == 'success') {
                $res['success'] = true;
                $subscriptionContract = $result['body']->container['data']['subscriptionContractCreate'];
                $res['message'] = (@$subscriptionContract['draft']['id']) ? $subscriptionContract['draft']['id'] : '';
                // if(!empty($draftId)){
                //     $message = $this->commitDraft($user_id, $draftId);
                // }
            }

            return $res;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  createSubscriptionContractInShopify =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    public function redactCustomer($customerData)
    {
        logger("************** REDACT CUSTOMER START****************************");

        $customer = SsCustomer::where('email', $customerData['customer']['email'])
            ->where('shopify_customer_id', $customerData['customer']['id'])
            ->first();
        if ($customer) {
            $contracts = SsContract::where('ss_customer_id', $customer->id)
                ->get();
            foreach ($contracts as $contract) {
                $contract->cc_name = "[redacted]";
                $contract->cc_maskedNumber = "[redacted]";
                $contract->cc_expiryMonth = 00;
                $contract->cc_expiryYear = 0000;
                $contract->cc_firstDigits = 0000;
                $contract->cc_lastDigits = 0000;
                $contract->save();
            }
            $customer->first_name = "[redacted]";
            $customer->last_name = "[redacted]";
            $customer->email = "[redacted]";
            $customer->phone = "[redacted]";
            $customer->save();
        }
    }

    /**
     * @param $webhooks
     * @param $user_id
     * @return false|void
     */
    public function createWebhook($webhooks, $user_id)
    {
        try {
            foreach ($webhooks as $key => $value) {
                $endPoint = 'admin/api/' . env('SHOPIFY_SAPI_VERSION') . '/webhooks/count.json';
                $parameter['topic'] = $key;
                $result = $this->request('GET', $endPoint, $parameter, $user_id);
                if (!$result['errors']) {
                    $count = $result['body']->container['count'];
                    if ($count == 0) {
                        $parameter = [
                            'webhook' => [
                                'topic' => $key,
                                'address' => env('AWS_ARN_WEBHOOK_ADDRESS'),
                                'format' => 'json',
                            ],
                        ];
                        $endPoint = 'admin/api/' . env('SHOPIFY_SAPI_VERSION') . '/webhooks.json';
                        $result = $this->request('POST', $endPoint, $parameter, $user_id);
                        // logger(json_encode($result));
                    }
                }
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  createWebhook =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $category
     * @param $subCategory
     * @param $des
     * @return bool|string
     */
    public function event($user_id, $category, $subCategory, $des)
    {
        try {
            $shop = Shop::where('user_id', $user_id)->first();
            $events = new SsEvents;
            $events->shop_id = $shop->id;
            $events->user_id = $user_id;
            $events->myshopify_domain = $shop->myshopify_domain;
            $events->category = $category;
            $events->subcategory = $subCategory;
            $events->description = $des;
            $events->save();
            return true;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  event =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @return array
     */
    public function getThemes()
    {
        try {
            $user = Auth::user();
            $parameter['fields'] = 'id,name,role';
            $sh_themes = $user->api()->rest('GET', 'admin/themes.json', $parameter);
            $theme = [];
            if (!$sh_themes['errors']) {
                $themes = $sh_themes['body']->container['themes'];
                foreach ($themes as $key => $val) {
                    $theme[$key]['id'] = $val['id'];
                    $theme[$key]['name'] = $val['name'];
                    $theme[$key]['role'] = $val['role'];
                }
            }
            return $theme;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getThemes =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @return mixed|string|void
     */
    public function getPublishTheme($user)
    {
        try {
            $parameter['role'] = 'main';
            $sh_themes = $user->api()->rest('GET', 'admin/themes.json', $parameter);
            if (!$sh_themes['errors']) {
                return (@$sh_themes['body']->container['themes'][0]['id']) ? $sh_themes['body']->container['themes'][0]['id'] : '';
            }
            return '';
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getPublishTheme =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $theme_id
     * @return array|void
     */
    public function getThemeSchema($user_id, $theme_id)
    {
        try {
            $user = User::find($user_id);
            $parameter['fields'] = 'id,name';
            $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/themes/' . $theme_id . '/assets.json';
            $theme = $user->api()->rest('GET', $endPoint, ['asset' => ['key' => 'config/settings_schema.json']]);
            if (!$theme['errors']) {
                $res['status'] = true;
                $res['data'] = json_decode($theme['body']->container['asset']['value']);
            } else {
                $res['status'] = false;
                $res['data'] = $theme['body'];
            }
            return $res;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getThemeSchema =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $query
     * @param  array  $parameter
     * @return array|\GuzzleHttp\Promise\Promise
     */
    public function graphQLRequest($user_id, $query, $parameters = [], $version = null)
    {
        try {
            if ($version == null) {
                $version = config('shopify-app.api_version');
            }
            logger("====================== graphQLRequest ======================");
            $user = User::find($user_id);
            return $user->api()->graph($query);
            // return $this->graph($user, $query, $parameters, $version);
            //            $options = new Options();
            //            $options->setVersion(env('SHOPIFY_SAPI_VERSION'));
            //            $api = new BasicShopifyAPI($options);
            //            $api->setSession(new Session(
            //                $user->name, $user->password));
            //            return $api->graph($query, $parameter);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  graphQLRequest =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * Make GraphQL api request
     * @param $user_id
     * @param $planData
     */
    public function ShopifySellingPlan($user_id, $planData)
    {
        // logger("***************************************** PLAN DATA IS AN ********************************************");
        // logger($planData);
        // logger("***************************************** PLAN DATA IS AN ********************************************");
        try {
            $shop = Shop::Where('user_id', $user_id)->first();
            $plan_gp_id = $planData->ss_plan_group_id;
            //$action = (SsPlan::where('ss_plan_group_id', $plan_gp_id)->count() <= 1) && !$planData->shopify_plan_group_id ? 'create' : 'update';
            $action = (SsPlan::where('ss_plan_group_id', $plan_gp_id)->count() <= 1) && !$planData->shopify_plan_id ? 'create' : 'update';
            $query = ($action == 'create') ? $this->createSellingPlanQuery($planData) : $this->updateSellingPlanQuery($planData);
            $result = $this->graphQLRequest($user_id, $query);
            // logger('createSellingPlanQuery');
            // logger(json_encode($result));
            if (!$result['errors']) {
                if ($action == 'create') {
                    $sh_sellingplan = $result['body']->container['data']['sellingPlanGroupCreate'];
                } else {
                    $sh_sellingplan = $result['body']->container['data']['sellingPlanGroupUpdate'];
                }
                if (empty($sh_sellingplan['userErrors'])) {

                    // logger("sh_sellingplan");
                    // logger($sh_sellingplan);

                    $sh_sellingPlanGroup = $sh_sellingplan['sellingPlanGroup'];
                    $planGroupData = SsPlanGroup::where('id', $plan_gp_id)->first();
                    if (!$planGroupData->shopify_plan_group_id) {
                        $planGroupData->shopify_plan_group_id = str_replace('gid://shopify/SellingPlanGroup/', '', $sh_sellingPlanGroup['id']);
                        $planGroupData->save();
                    }

                    $sh_sellingPlan = (!$planData->shopify_plan_id) ? $sh_sellingPlanGroup['sellingPlans']['edges'][0]['node'] : $sh_sellingPlanGroup['sellingPlans']['edges'][$planData->position]['node'];


                    // $sh_sellingPlan = $sh_sellingPlanGroup['sellingPlans']['edges'][$planData->position]['node'];
                    if (!$planData->shopify_plan_id) {
                        $planData->shopify_plan_id = str_replace('gid://shopify/SellingPlan/', '', $sh_sellingPlan['id']);
                        $planData->save();
                    }
                    if ($action == 'create') {
                        $profiles = SsShippingProfile::where('shop_id', $shop->id)->get();
                        if (count($profiles) > 0) {
                            foreach ($profiles as $pkey => $pval) {
                                $gpIds = json_decode($pval->plan_group_ids);
                                $gpIds[] = $planGroupData->shopify_plan_group_id;
                                $pval->plan_group_ids = json_encode($gpIds);
                                $pval->save();
                                $result = $this->createDeliveryProfile($user_id, $pval->id, '');
                            }
                            return $result;
                        } else {
                            return 'success';
                        }
                    } else {
                        return 'success';
                    }
                } else {
                    logger('============ ERROR:: ShopifySellingPlan User Errors ============');
                    logger(json_encode($sh_sellingplan['userErrors'][0]));
                    if ($sh_sellingplan['userErrors'][0]['code'] == 'SELLING_PLAN_DUPLICATE_OPTIONS') {
                        return 'Each plan must have unique option names.';
                    } else {
                        return $sh_sellingplan['userErrors'][0]['message'];
                    }
                }
            } else {
                logger(json_encode($result));
            }
            return false;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  ShopifySellingPlan =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $planData
     * @return string
     */
    public function createSellingPlanQuery($planData)
    {
        try {
            $plan_gp_id = $planData->ss_plan_group_id;
            $planGroupData = SsPlanGroup::where('id', $plan_gp_id)->first();
            $policies = $this->getPolicies($planData, $planData->user_id);
            return '
                mutation {
                      sellingPlanGroupCreate(input: {
                            name: "' . $planGroupData->name . '",
                            description: "' . $planGroupData->description . '",
                            merchantCode: "' . $planGroupData->merchantCode . '",
                            position: ' . $planGroupData->position . ',
                            options: ["' . $planGroupData->options . '"],
                                sellingPlansToCreate: [
                                {
                                  name: "' . $planData->name . '"
                                  description: "' . $planData->description . '"
                                  options: ["' . $planData->options . '"],
                                  position: ' . $planData->position . '
                                  category: SUBSCRIPTION,
                                  billingPolicy: ' . $policies['billingPolicy'] . '
                                  deliveryPolicy: ' . $policies['deliveryPolicy'] . '
                                  pricingPolicies: [' . $policies['pricingPolicy'] . ']
                                }]
                        }) {
                            sellingPlanGroup {
                              id
                              sellingPlans(first: ' . ($planData->position + 1) . ') {
                                edges {
                                  node {
                                    id
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
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  createSellingPlanQuery =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $planData
     * @return string
     */
    public function updateSellingPlanQuery($planData)
    {
        try {
            $plan_gp_id = $planData->ss_plan_group_id;
            // logger($plan_gp_id);
            $planGroupData = SsPlanGroup::where('id', $plan_gp_id)->first();
            // logger('shopify plan id =******************////////////*');
            // logger($planGroupData);
            $action = ($planData->shopify_plan_id) ? 'sellingPlansToUpdate' : 'sellingPlansToCreate';
            $policies = $this->getPolicies($planData, $planData->user_id);

            $string  = $action == 'sellingPlansToCreate' ? "first: 1 , reverse: true" : "first :" . $planData->position + 1;

            $query = '
                mutation {
                      sellingPlanGroupUpdate(id: "gid://shopify/SellingPlanGroup/' . $planGroupData->shopify_plan_group_id . '", input: {
                            name: "' . $planGroupData->name . '",
                            description: "' . $planGroupData->description . '",
                            merchantCode: "' . $planGroupData->merchantCode . '",
                            position: ' . $planGroupData->position . ',
                            options: ["' . $planGroupData->options . '"],
                                ' . $action . ': [
                                {';
            $query .= ($planData->shopify_plan_id) ? 'id: "gid://shopify/SellingPlan/' . $planData->shopify_plan_id . '", ' : '';
            $query .= 'name: "' . $planData->name . '"
                                  description: "' . $planData->description . '"
                                  options: ["' . $planData->options . '"]
                                  position: ' . $planData->position . '
                                  category: SUBSCRIPTION
                                  billingPolicy: ' . $policies['billingPolicy'] . '
                                  deliveryPolicy: ' . $policies['deliveryPolicy'] . '
                                  pricingPolicies: [' . $policies['pricingPolicy'] . ']
                                }]
                        }) {
                            deletedSellingPlanIds,
                            sellingPlanGroup {
                              id
                              sellingPlans('. $string .' ){
                                edges {
                                  node {
                                    id
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

            logger('*****************--------------  QUERY IS AN -------------------');
            logger($query);
            return $query;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateSellingPlanQuery =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $planData
     * @param $user_id
     * @return array
     */
    public function getPolicies($planData, $user_id)
    {
        $policies = [];
        $user = User::find($user_id);
        if ($planData->trial_available) {
            $adjustmentType = 'PRICE';
            $adjustmentTypeValue = 'fixedValue: ';
        } else {
            // fixed adjustment
            $adjustmentType = ($planData->pricing_adjustment_type == '%') ? 'PERCENTAGE' : 'FIXED_AMOUNT';
            $adjustmentType = ($planData->pricing_adjustment_type == 'PRICE') ? 'PRICE' : $adjustmentType;
            $adjustmentTypeValue = ($adjustmentType == 'PERCENTAGE') ? 'percentage: ' : 'fixedValue: ';
        }
        $min_cycle = ($planData['billing_min_cycles']) ? ', minCycles: ' . $planData->billing_min_cycles : '';
        $max_cycle = ($planData['billing_max_cycles']) ? ', maxCycles: ' . $planData->billing_max_cycles : '';
        // if (Feature::where('name', 'Prepaid subscriptions')->count() > 0) {
        //     if (Feature::isEnabledFor('Prepaid subscriptions', $user)) {
        //         $delivery_interval = ($planData->is_prepaid) ? $planData->delivery_interval : $planData->billing_interval;
        //         $delivery_interval_count = ($planData->is_prepaid) ? $planData->delivery_interval_count : $planData->billing_interval_count;
        //     } else {
        //         $delivery_interval = $planData->billing_interval;
        //         $delivery_interval_count = $planData->billing_interval_count;
        //     }
        // } else {
        //     $delivery_interval = $planData->billing_interval;
        //     $delivery_interval_count = $planData->billing_interval_count;
        // }
        // TODO :: Please remove it after Feature flags are done.
        $delivery_interval = $planData->billing_interval;
        $delivery_interval_count = $planData->billing_interval_count;
        if ($planData->delivery_intent == 'A fixed day each delivery cycle') {
            if ($planData->billing_interval == 'day') {
                $policies['billingPolicy'] = '{ recurring: { interval: ' . strtoupper($planData->billing_interval) . ', intervalCount: ' . $planData->billing_interval_count . $min_cycle . $max_cycle . ' } }';
                $policies['deliveryPolicy'] = '{ recurring: { interval: ' . strtoupper($delivery_interval) . ', intervalCount: ' . $delivery_interval_count . ', cutoff: ' . $planData->delivery_cutoff . ', preAnchorBehavior: ' . $planData->delivery_pre_cutoff_behaviour . ', intent: FULFILLMENT_BEGIN} }';
                // if(\LaravelFeature\Model\Feature::where('name', 'Anchor days')->count() > 0){
                //     if( Feature::isEnabledFor('Anchor days', $user) ){
                //          $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($delivery_interval) .', intervalCount: '. $delivery_interval_count .', cutoff: '. $planData->delivery_cutoff .', preAnchorBehavior: '. $planData->delivery_pre_cutoff_behaviour .', intent: FULFILLMENT_BEGIN} }';
                //     }else{
                //         $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($delivery_interval) .', intervalCount: '. $delivery_interval_count .', intent: FULFILLMENT_BEGIN} }';
                //     }
                // }else{
                //     $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($delivery_interval) .', intervalCount: '. $delivery_interval_count .', intent: FULFILLMENT_BEGIN} }';
                // }
            } else {
                $delivery_interval = ($planData->is_prepaid) ? $planData->delivery_interval : $planData->billing_interval;
                $delivery_interval_count = ($planData->is_prepaid) ? $planData->delivery_interval_count : $planData->billing_interval_count;
                if ($planData->billing_anchor_type == 'YEARDAY') {
                    $policies['billingPolicy'] = '{ recurring: { interval: ' . strtoupper($planData->billing_interval) . ', intervalCount: ' . $planData->billing_interval_count . $min_cycle . $max_cycle . ', anchors: { day: ' . $planData->billing_anchor_day . ', month: ' . $planData->billing_anchor_month . ', type: ' . $planData->billing_anchor_type . '} } }';

                    // if(\LaravelFeature\Model\Feature::where('name', 'Anchor days')->count() > 0){
                    //     if( Feature::isEnabledFor('Anchor days', $user) ){
                    //         $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($delivery_interval) .', intervalCount: '. $delivery_interval_count .', cutoff: '. $planData->delivery_cutoff .', preAnchorBehavior: '. $planData->delivery_pre_cutoff_behaviour .', intent: FULFILLMENT_BEGIN, anchors: { day: '. $planData->billing_anchor_day .', month: '. $planData->billing_anchor_month .', type: '. $planData->billing_anchor_type .'} } }';
                    //     }else{
                    //          $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($delivery_interval) .', intervalCount: '. $delivery_interval_count .', intent: FULFILLMENT_BEGIN, anchors: { day: '. $planData->billing_anchor_day .', month: '. $planData->billing_anchor_month .', type: '. $planData->billing_anchor_type .'} } }';
                    //     }
                    // }else{
                    //     $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($pdelivery_interval) .', intervalCount: '. $delivery_interval_count .', intent: FULFILLMENT_BEGIN, anchors: { day: '. $planData->billing_anchor_day .', month: '. $planData->billing_anchor_month .', type: '. $planData->billing_anchor_type .'} } }';
                    // }
                    $policies['deliveryPolicy'] = '{ recurring: { interval: ' . strtoupper($delivery_interval) . ', intervalCount: ' . $delivery_interval_count . ', cutoff: ' . $planData->delivery_cutoff . ', preAnchorBehavior: ' . $planData->delivery_pre_cutoff_behaviour . ', intent: FULFILLMENT_BEGIN, anchors: { day: ' . $planData->billing_anchor_day . ', month: ' . $planData->billing_anchor_month . ', type: ' . $planData->billing_anchor_type . '} } }';
                } else {
                    $policies['billingPolicy'] = '{ recurring: { interval: ' . strtoupper($planData->billing_interval) . ', intervalCount: ' . $planData->billing_interval_count . $min_cycle . $max_cycle . ', anchors: { day: ' . $planData->billing_anchor_day . ', type: ' . $planData->billing_anchor_type . '} } }';
                    $policies['deliveryPolicy'] = '{ recurring: { interval: ' . strtoupper($delivery_interval) . ', intervalCount: ' . $delivery_interval_count . ', cutoff: ' . $planData->delivery_cutoff . ', preAnchorBehavior: ' . $planData->delivery_pre_cutoff_behaviour . ', intent: FULFILLMENT_BEGIN, anchors: {day: ' . $planData->billing_anchor_day . ', type: ' . $planData->billing_anchor_type . '} } }';
                    // if(\LaravelFeature\Model\Feature::where('name', 'Anchor days')->count() > 0){
                    //     if( Feature::isEnabledFor('Anchor days', $user) ){
                    //         $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($delivery_interval) .', intervalCount: '. $delivery_interval_count .', cutoff: '. $planData->delivery_cutoff .', preAnchorBehavior: '. $planData->delivery_pre_cutoff_behaviour .', intent: FULFILLMENT_BEGIN, anchors: {day: '. $planData->billing_anchor_day .', type: '. $planData->billing_anchor_type .'} } }';
                    //     }else{
                    //          $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($delivery_interval) .', intervalCount: '. $delivery_interval_count .', intent: FULFILLMENT_BEGIN, anchors: { day: '. $planData->billing_anchor_day .', type: '. $planData->billing_anchor_type .'} } }';
                    //     }
                    // }else{
                    //     $policies['deliveryPolicy'] = '{ recurring: { interval: '. strtoupper($delivery_interval) .', intervalCount: '. $delivery_interval_count .', intent: FULFILLMENT_BEGIN, anchors: { day: '. $planData->billing_anchor_day .', type: '. $planData->billing_anchor_type .'} } }';
                    // }
                }
            }
        } else {
            $policies['billingPolicy'] = '{ recurring: { interval: ' . strtoupper($planData->billing_interval) . ', intervalCount: ' . $planData->billing_interval_count . $min_cycle . $max_cycle . ' } }';
            if ($planData->is_prepaid) {
                $policies['deliveryPolicy'] = '{ recurring: { interval: ' . strtoupper($planData->delivery_interval) . ', intervalCount: ' . $planData->delivery_interval_count . ', intent: FULFILLMENT_BEGIN } }';
            } else {
                $policies['deliveryPolicy'] = '{ recurring: { interval: ' . strtoupper($planData->billing_interval) . ', intervalCount: ' . $planData->billing_interval_count . ', intent: FULFILLMENT_BEGIN } }';
            }
        }
        if ($planData->trial_available) {
            $adjustmentValue = $planData->pricing2_adjustment_value;
        } else {
            $adjustmentValue = ($planData->pricing_adjustment_value != '') ? $planData->pricing_adjustment_value : 0;
        }
        // if( $planData->pricing_adjustment_value != '' ){
        if ($planData->is_advance_option) {
            $adValue = ($planData->pricing_adjustment_value != '') ? $planData->pricing_adjustment_value : 0;
            if ($planData->trial_available) {
                $afterCycle = $planData->pricing2_after_cycle ? $planData->pricing2_after_cycle : 1;
                $policies['pricingPolicy'] = '{
                        fixed: {
                          adjustmentType: PRICE
                          adjustmentValue: { fixedValue: ' . $planData->pricing2_adjustment_value . '}
                        }
                    },
                    {
                        recurring: {
                            afterCycle: ' . $afterCycle  . '
                            adjustmentType: PRICE
                            adjustmentValue: { fixedValue: ' . $adValue . ' }
                          }
                    }';
            } elseif ($planData->is_onetime_payment) {
                $policies['pricingPolicy'] = '{
                        fixed: {
                          adjustmentType: PRICE
                          adjustmentValue: { fixedValue: ' . $adValue . '}
                        }
                    },
                    {
                        recurring: {
                            afterCycle: 1
                            adjustmentType: PRICE
                            adjustmentValue: { fixedValue:  0.00  }
                          }
                    }';
            } else {
                $policies['pricingPolicy'] = '{
                fixed: {
                  adjustmentType: ' . $adjustmentType . '
                  adjustmentValue: { ' . $adjustmentTypeValue . $adjustmentValue . '}
                }
              }';
            }
        } else {
            $policies['pricingPolicy'] = '{
                fixed: {
                  adjustmentType: ' . $adjustmentType . '
                  adjustmentValue: { ' . $adjustmentTypeValue . $adjustmentValue . '}
                }
              }';
        }
        // }
        // else if ( $planData->pricing2_adjustment_value != '' ){
        //    $policies['pricingPolicy'] = '{
        //     recurring: {
        //       adjustmentType: '. $rAdjustmentType .'
        //       adjustmentValue: { '. $rAdjustmentValue . $planData->pricing2_adjustment_value .'}
        //       afterCycle: '. $planData->pricing2_after_cycle .'
        //     }
        //   }';
        // }
        logger('========== Policies =');
        logger($policies);
        return $policies;
    }

    /**
     * @param $user_id
     * @param $planGroupData
     * @return false|mixed|string|void
     */
    public function updateSellingPlanGroup($user_id, $planGroupData)
    {
        try {
            $query = '
                mutation {
                    sellingPlanGroupUpdate(id: "gid://shopify/SellingPlanGroup/' . $planGroupData->shopify_plan_group_id . '", input: {
                            name: "' . $planGroupData->name . '",
                            description: "' . $planGroupData->description . '",
                            position: ' . $planGroupData->position . ',
                             options: ["' . $planGroupData->options . '"],
                    }){
                      userErrors {
                        code
                        field
                        message
                      }
                    }
                }
            ';
            $result = $this->graphQLRequest($user_id, $query);
            $msg = $this->getReturnMessage($result, 'sellingPlanGroupUpdate');
            return $msg;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateSellingPlanGroup =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $planGroupID
     * @param $planData
     * @return false|mixed|string|void
     */
    public function updateSellingPlan($user_id, $planGroupID, $planData)
    {
        try {
            $query = '
                mutation {
                    sellingPlanGroupUpdate(id: "gid://shopify/SellingPlanGroup/' . $planGroupID . '", input: {
                            sellingPlansToUpdate: [{
                                id: "gid://shopify/SellingPlan/' . $planData->shopify_plan_id . '", position: ' . $planData->position . '
                            }]
                    }){
                      userErrors {
                        code
                        field
                        message
                      }
                    }
                }
            ';
            $result = $this->graphQLRequest($user_id, $query);
            $msg = $this->getReturnMessage($result, 'sellingPlanGroupUpdate');
            return $msg;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateSellingPlan =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    /**
     * @param $user_id
     * @return false|mixed|string|void
     */
    public function getIsPosEnabled($user_id)
    {
        try {
            $query = '
               {
                  channels(first: 250) {
                    edges {
                      node {
                        name
                        app {
                          shopifyDeveloped
                          handle
                          published
                        }
                      }
                    }
                  }
                }
            ';
            $result = $this->graphQLRequest($user_id, $query);
            $isEnabled = false;
            if (!$result['errors']) {
                $data = (@$result['body']->container['data']['channels']['edges']) ? $result['body']->container['data']['channels']['edges'] : [];
                if (!empty($data)) {
                    foreach ($data as $key => $val) {
                        $node = $val['node'];
                        if ($node['name'] == 'Point of Sale') {
                            return true;
                        }
                    }
                }
            }
            return $isEnabled;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getIsPosEnabled =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $productIds
     * @param $planGroupID
     * @return false|mixed|string|void
     */
    public function updateSellingPlanGroupProduct($user_id, $productIds, $planGroupID)
    {
        try {
            $query = '
                mutation {
                    sellingPlanGroupAddProducts(id: "gid://shopify/SellingPlanGroup/' . $planGroupID . '", productIds: ["gid://shopify/Product/' . implode('","gid://shopify/Product/', $productIds) . '"]) {
                      userErrors {
                        code
                        field
                        message
                      }
                    }
                }
            ';
            $result = $this->graphQLRequest($user_id, $query);
            $msg = $this->getReturnMessage($result, 'sellingPlanGroupAddProducts');
            return $msg;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateSellingPlanGroupProduct =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $productIds
     * @param $planGroupID
     * @return false|mixed|string|void
     */
    public function updateSellingPlanGroupRemoveProduct($user_id, $productIds, $planGroupID)
    {
        try {
            $query = '
                mutation {
                    sellingPlanGroupRemoveProducts(id: "gid://shopify/SellingPlanGroup/' . $planGroupID . '", productIds: ["gid://shopify/Product/' . implode('","gid://shopify/Product/', $productIds) . '"]) {
                      userErrors {
                        code
                        field
                        message
                      }
                    }
                }
            ';
            $result = $this->graphQLRequest($user_id, $query);
            $msg = $this->getReturnMessage($result, 'sellingPlanGroupRemoveProducts');
            return $msg;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateSellingPlanGroupRemoveProduct =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $planGroupID
     * @param $planID
     * @param $planCount
     * @return array|false|\GuzzleHttp\Promise\Promise|mixed|string|void
     */
    public function deleteSellingPlan($user_id, $planGroupID, $planID, $planCount)
    {
        try {
            $query = '
                mutation {
                    sellingPlanGroupUpdate(id: "gid://shopify/SellingPlanGroup/' . $planGroupID . '", input: {
                            sellingPlansToDelete: "gid://shopify/SellingPlan/' . $planID . '",
                    }){
                      userErrors {
                        code
                        field
                        message
                      }
                    }
                }
            ';
            $result = $this->graphQLRequest($user_id, $query);
            $msg = $this->getReturnMessage($result, 'sellingPlanGroupUpdate');
            if ($planCount == 1) {
                $msg = $this->deleteSellingPlanGroup($user_id, $planGroupID);
            }
            return $msg;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  deleteSellingPlan =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $planGroupID
     * @return array|false|\GuzzleHttp\Promise\Promise|mixed|string|void
     */
    public function deleteSellingPlanGroup($user_id, $planGroupID)
    {
        try {
            $shop = Shop::Where('user_id', $user_id)->first();
            $query = '
                mutation {
                    sellingPlanGroupDelete(id: "gid://shopify/SellingPlanGroup/' . $planGroupID . '") {
                    userErrors {
                      code
                      field
                      message
                    }
                  }
                }
            ';
            $result = $this->graphQLRequest($user_id, $query);
            $msg = $this->getReturnMessage($result, 'sellingPlanGroupDelete');
            if ($msg == 'success') {
                $profiles = SsShippingProfile::where('shop_id', $shop->id)->get();
                if (count($profiles) > 0) {
                    foreach ($profiles as $pkey => $pval) {
                        $result = $this->createDeliveryProfile($user_id, $pval->id, '');
                        if ($result == 'success') {
                            $gpIds = json_decode($pval->plan_group_ids);
                            unset($gpIds[array_search($planGroupID, $gpIds)]);
                            $pval->plan_group_ids = json_encode($gpIds);
                            $pval->save();
                        }
                    }
                    return $result;
                }
            }
            return $msg;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  deleteSellingPlanGroup =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $result
     * @param $action
     * @return false|mixed|string
     */
    public function getReturnMessage($result, $action)
    {
        logger('=============== ' . $action . ' ==============');
        logger(json_encode($result));
        if (!$result['errors']) {
            $data = $result['body']->container['data'][$action];
            if (empty($data['userErrors'])) {
                return 'success';
            } else {
                return $data['userErrors'][0]['message'];
            }
        } else {
            return (@$result['errors'][0]['message']) ? $result['errors'][0]['message'] : false;
        }
        return false;
    }

    /**
     * @param $result
     * @param $successMsg
     * @return array|void
     */
    public function getControllerReturnData($result, $successMsg)
    {
        try {
            logger('========= START:: getControllerReturnData =========');
            if ($result == 'success') {
                $data['msg'] = $successMsg;
                $data['success'] = true;
            } else if (!$result) {
                $data['msg'] = 'Error - please try again';
                $data['success'] = false;
            } else {
                $data['msg'] = $result;
                $data['success'] = false;
            }
            return $data;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getControllerReturnData =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user_id
     * @param $shop_id
     * @param $shopify_order_id
     * @param $customer_id
     * @param $contract_id
     * @return SsOrder|null
     */

    public function createOrder($user_id, $shop_id, $shopify_order_id, $customer_id, $contract_id)
    {
        try {
            $user = User::find($user_id);
            if ($user) {
                // logger('============= Create order ==>  user :: ' . $user_id . ' ==============');
                $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/orders/' . $shopify_order_id . '.json';
                $result = $user->api()->rest('GET', $endPoint);

                if (!$result['errors']) {
                    $data = $result['body']['order'];
                    logger("===============================STORE CREDIT START===============================");
                    $payment_gateway_names = json_encode($data->payment_gateway_names);
                    $contract = SsContract::where('id', $contract_id)->first();
                    $ssPlan = SsPlan::where('id', $contract->ss_plan_id)->first();
                    if (str_contains($payment_gateway_names, 'shopify_store_credit')) {
                        $store_credit_update = SsStoreCredit::where(['shop_id' => $shop_id, 'ss_customer_id' =>  $customer_id])->first();
                        if ($store_credit_update) {
                            $debit_amt =  $store_credit_update->balance;
                            if ($store_credit_update->balance >= $data->current_total_price) {
                                $store_credit_update->balance =  $store_credit_update->balance - $data->current_total_price;
                                $debit_amt = $data->current_total_price;
                            } else {
                                if ($ssPlan->store_credit_frequency == 'first_order') {
                                    $checkPlans =  SsContract::where(['ss_plan_id' => $ssPlan->id, 'shop_id' => $ssPlan->shop_id, 'ss_customer_id' =>  $contract->ss_customer_id, 'store_credit_frequency' => 'first_order'])->count();
                                    if ($checkPlans == 1) {
                                        $store_credit_update->amount = 0;
                                        $store_credit_update->balance = 0;
                                    }
                                } else {
                                    if ($ssPlan->store_credit) {
                                        $store_credit_update->balance = $ssPlan->store_credit_amount;
                                        $debit_amt =  $debit_amt - $ssPlan->store_credit_amount;
                                    } else {
                                        $store_credit_update->amount = 0;
                                        $store_credit_update->balance = 0;
                                    }
                                }
                            }
                            $store_credit_update->save();
                            $this->addTrasaction($ssPlan->shop_id, $ssPlan->user_id, $customer_id,  $contract_id, "debit", $debit_amt);
                        }
                    }
                    logger("===============================STORE CREDIT END===============================");
                    $db_order = new SsOrder;
                    $plan = Plan::find($user->plan_id);
                    // logger("========================PLAN======================");
                    // logger($plan);
                    $userSettings = SsSetting::where('shop_id', $shop_id)->first();
                    if ($userSettings && $userSettings->auto_fulfill) {
                        $fullfilOrder = $this->fulfillOrder($user, $data->id);
                        // logger(json_encode($fullfilOrder));
                    }
                    $line_items  = $data['line_items'];
                    $order_amount  = 0;
                    if (count($line_items) > 1) {
                        $contract = SsContract::where('id', $contract_id)->first();
                        $productId  = SsPlanGroupVariant::where('ss_plan_group_id', $contract->ss_plan_groups_id)->first();
                        foreach ($line_items as $line_item) {
                            if ($line_item['product_exists']) {
                                if ($line_item['product_id'] == $productId->shopify_product_id) {
                                    $order_amount = $line_item['price'];
                                    break;
                                }
                            } else {
                                if ($line_item['name'] == $productId->product_title) {
                                    $order_amount = $line_item['price'];
                                    break;
                                }
                            }
                        }
                    } else {
                        $order_amount =  $line_items[0]['price'];
                    }
                    $db_rates = ExchangeRate::orderBy('created_at', 'desc')->first();
                    $rate = json_decode($db_rates->conversion_rates);
                    $currencyCode = $data->currency;
                    $tx_fee = $plan->transaction_fee;
                    $db_order->shop_id = $shop_id;
                    $db_order->user_id = $user->id;
                    $db_order->shopify_order_id = $shopify_order_id;
                    $db_order->ss_customer_id = $customer_id;
                    $db_order->ss_contract_id = $contract_id;
                    $db_order->shopify_order_name = $data->name;
                    $db_order->order_currency = $data->currency;
                    $db_order->currency_symbol = currencyH($data->currency);
                    $db_order->order_amount = $order_amount;
                    $db_order->usd_order_amount = calculateCurrency($data->currency, 'USD', $data->total_price);
                    $db_order->conversion_rate = round($rate->$currencyCode, 6);
                    $db_order->tx_fee_status = 'pending';
                    $db_order->tx_fee_percentage = $tx_fee;
                    $db_order->tx_fee_amount = number_format(($db_order->order_amount * $tx_fee), 4);
                    $db_order->is_test = ($data->test);
                    $db_order->save();
                    return $db_order;
                }else {
                    if ($result['status'] == 429){
                        sleep(10);
                        $this->createOrder($user_id, $shop_id, $shopify_order_id, $customer_id, $contract_id);
                    }
                }
            }
            return null;
        } catch (\Exception $e) {
            logger("============= ERROR ::  createOrder =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
    public function fulfillOrder($user, $orderId)
    {
        $fulfillments = $user->api()->rest('GET', 'admin/orders/' . $orderId . '/fulfillment_orders.json');
        if (!$fulfillments['errors']) {
            $fulfillments = $fulfillments['body']->container['fulfillment_orders'];
            $subQuery = '';
            foreach ($fulfillments as $fulfillment) {
                $fulfillmentLineItems = $fulfillment['line_items'];
                $lineItemString = '';
                foreach ($fulfillmentLineItems as $lineItem) {
                    $isExist = SsContractLineItem::where('shopify_variant_id', $lineItem['variant_id'])->first();
                    if ($isExist) {
                        $lineItemString .= '
                            {
                                id: "gid://shopify/FulfillmentOrderLineItem/' . $lineItem['id'] . '",
                                quantity: ' . $lineItem['quantity'] . '
                            }
                        ';
                    }
                }
                $lineItemString = explode('}{', $lineItemString);
                $lineItemString = implode('},{', $lineItemString);
                if ($lineItemString != '') {
                    $subQuery .= '
                    {
                        fulfillmentOrderId: "gid://shopify/FulfillmentOrder/' . $fulfillment['id'] . '",
                        fulfillmentOrderLineItems:
                        [
                            ' . $lineItemString . '
                        ]
                    }
                    ';
                }
            }
            $subQuery = explode('}{', $subQuery);
            $subQuery = implode('},{', $subQuery);
            $query = '
                mutation MyMutation {
                    fulfillmentCreateV2(
                    fulfillment: {
                        lineItemsByFulfillmentOrder:
                        [
                            ' . $subQuery . '
                        ]
                    }
                    ) {
                        userErrors {
                            field
                            message
                        }
                    }
                }
            ';
            $result = ($this->graphQLRequest($user->id, $query));
            if ($result['status'] == 429){
                sleep(10);
                $this->fulfillOrder($user, $orderId);
            }
            return $result;
        } else {
            logger("=============> Error while fulfill the order");
            return $fulfillments;
        }
        // $user = User::find($user_id);
        // logger('============= Create order ==>  user :: ' . $user_id . ' ==============');
        // $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/orders/' . $shopify_order_id . '.json';
        // $result = $user->api()->rest('GET', $endPoint);
        // if (!$result['errors']) {
        //     $data = $result['body']['order'];
        //     $db_order = new SsOrder;
        //     $plan = Plan::find($user->plan_id);
        //     $db_rates = ExchangeRate::orderBy('created_at', 'desc')->first();
        //     $rate = json_decode($db_rates->conversion_rates);
        //     $currencyCode = $data->currency;
        //     $tx_fee = $plan->transaction_fee;
        //     $db_order->shop_id = $shop_id;
        //     $db_order->user_id = $user->id;
        //     $db_order->shopify_order_id = $shopify_order_id;
        //     $db_order->ss_customer_id = $customer_id;
        //     $db_order->ss_contract_id = $contract_id;
        //     $db_order->shopify_order_name = $data->name;
        //     $db_order->order_currency = $data->currency;
        //     $db_order->currency_symbol = currencyH($data->currency);
        //     $db_order->order_amount = calculateCurrency($data->currency, 'USD', $data->total_price);
        //     $db_order->conversion_rate = round($rate->$currencyCode, 6);
        //     $db_order->tx_fee_status = 'pending';
        //     $db_order->tx_fee_percentage = $tx_fee;
        //     $db_order->tx_fee_amount = number_format(($db_order->order_amount * $tx_fee), 4);
        //     $db_order->is_test = ($data->test);
        //     $db_order->save();
        //     return $db_order;
        // }
    }

    /**
     * @param $shop_id
     * @return string
     */
    public function getSubscriptionTime($shop_id)
    {
        $setting = SsSetting::select('subscription_daily_at')->where('shop_id', $shop_id)->first();
        $subscription_daily_at = $setting->subscription_daily_at;
        $timeformat = (substr($subscription_daily_at, -2));
        $time = substr($subscription_daily_at, 0, 5);
        if ($timeformat == 'PM' && $time == '11:59') {
            $addtime = 23 . ':59:00';
        } elseif ($timeformat == 'AM' && $time == '12:01') {
            $addtime = '00:01:00';
        } else {
            if ($timeformat == 'PM' && $time == '12:01') {
                $addtime = 1 . ':00:00';
            } else {
                if ($timeformat == 'PM' && $time != '12:00') {
                    $addtime = (12 + substr($subscription_daily_at, 0, 2)) . ':00:00';
                } else {
                    $addtime = substr($subscription_daily_at, 0, 2) . ':00:00';
                }
            }
        }
        return $addtime;
    }

    /**
     * @param $nextDate
     * @param $shop_id
     * @param  string  $customTime
     * @param  string  $customTimeZone
     * @return false|string
     * @throws Exception
     */
    public function getSubscriptionTimeDate($nextDate, $shop_id, $customTime = '', $customTimeZone = '')
    {
        $shop = Shop::find($shop_id);
        $tz = $shop->timezone;
        $ianatz = $shop->iana_timezone;
        // $gmt = str_replace('GMT', '', substr($tz, (strpos($tz, '(') + 1), (strpos($tz, ')') - 1)));
        $gmt = $this->getUTCOffset($ianatz);
        $gmtSign = substr($gmt, 0, 1);
        if ($customTime == '') {
            $deductSign = ($gmtSign == '+') ? '-' : '+';
        } else {
            $deductSign = $gmtSign;
        }
        $gmtHour = explode(':', str_replace($gmtSign, '', $gmt));
        $fdate = new DateTime();
        if ($customTime == '') {
            $customTime = $this->getSubscriptionTime($shop->id);
        }
        $mydate = $nextDate . ' ' . $customTime;
        $default_timezone = date_default_timezone_get();
        ($customTimeZone == '') ? date_default_timezone_set($shop->iana_timezone) : date_default_timezone_set($customTimeZone);
        $sdate = $fdate = new DateTime();
        $dateTime = new DateTime($mydate);
        $dateTime->modify("$deductSign$gmtHour[1] minutes");
        $dateTime->modify("$deductSign$gmtHour[0] hours");
        // if( $customTimeZone == '' ){
        //     $interval = $fdate->diff($sdate);
        //     $dateTime = new DateTime($mydate);
        //     $dateTime->modify("$deductSign$interval->m minutes");
        //     $dateTime->modify("$deductSign$interval->h hours");
        // }else{
        //     $dateTime = new DateTime($mydate);
        //     $dateTime->modify("$deductSign$gmtHour[1] minutes");
        //     $dateTime->modify("$deductSign$gmtHour[0] hours");
        // }
        $returnD = date_format($dateTime, 'Y-m-d H:i:s');
        date_default_timezone_set($default_timezone);
        return $returnD;
    }

    /**
     * @param $timezone
     * @return string
     * @throws Exception
     */
    public function getUTCOffset($timezone)
    {
        $current = timezone_open($timezone);
        $utcTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $offsetInSecs = timezone_offset_get($current, $utcTime);
        $hoursAndSec = gmdate('H:i', abs($offsetInSecs));
        return stripos($offsetInSecs, '-') === false ? "+{$hoursAndSec}" : "-{$hoursAndSec}";
    }

    //     public function getSubscriptionTimeDate($nextDate, $shop_id, $customTime = '', $customTimeZone = ''){
    //         $shop = Shop::find($shop_id);

    //         $tz = $shop->timezone;

    //         $gmt = str_replace('GMT', '', substr($tz, (strpos($tz, '(') + 1), (strpos($tz, ')') - 1)));
    //         $gmtSign = substr($gmt, 0, 1);

    //         if( $customTime == '' ){
    //             $deductSign = ( $gmtSign == '+' ) ? '-' : '+';
    //         }else{
    //             $deductSign = $gmtSign;
    //         }
    //         $gmtHour = explode(':', str_replace($gmtSign, '', $gmt));

    //         // dump('$gmtHour :: ' );
    //         // dump($gmtHour);

    //         if( $customTime == ''){
    //             $customTime = $this->getSubscriptionTime($shop->id);
    //         }

    // //        $mydate = date('Y-m-d ' . $customTime);
    //         $mydate = $nextDate . ' ' . $customTime;

    //         $default_timezone = date_default_timezone_get();
    //          // dump($default_timezone .' :: ' . date('Y-m-d H:i:s'));
    //         ( $customTimeZone == '' ) ? date_default_timezone_set($shop->iana_timezone) : date_default_timezone_set($customTimeZone);

    //          // dump($shop->iana_timezone .' :: ' . date('Y-m-d H:i:s'));

    //         $dateTime = new DateTime($mydate);
    //         $dateTime->modify("$deductSign$gmtHour[1] minutes");
    //         $dateTime->modify("$deductSign$gmtHour[0] hours");

    //         $returnD = date_format($dateTime, 'Y-m-d H:i:s');
    //         date_default_timezone_set($default_timezone);

    //         return $returnD;
    //     }

    public function getShopifyOrder($user, $orderId, $fields = 'id,total_price')
    {
        logger('======= getShopifyOrder =======');
        $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/orders/' . $orderId . '.json';
        $parameter['fields'] = $fields;
        $result = $user->api()->rest('GET', $endPoint, $parameter);
        if (!$result['errors']) {
            return $result['body']->container['order'];
        } else {
            return [];
        }
    }

    public function getShopifyData($user, $shopifyId, $resource, $fields = 'id')
    {
        $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/' . $resource . 's/' . $shopifyId . '.json';
        $parameter['fields'] = $fields;
        $result = $user->api()->rest('GET', $endPoint, $parameter);
        if (!$result['errors']) {
            return $result['body']->container[$resource];
        } else {
            if ($result['status'] == 429){

                logger("************ API LIMIT REACHED IN getShopifyData***************************");
                sleep(10);
                $this->getShopifyData($user, $shopifyId, $resource, $fields = 'id');
            }
            return [];
        }
    }

    public function filterShopifyData($user, $resource, $fields = 'id', $filter = [])
    {
        try {
            $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/' . $resource . '.json';
            $parameter['fields'] = $fields;
            foreach ($filter as $key => $value) {
                $parameter[$key] = $value;
            }
            $result = $user->api()->rest('GET', $endPoint, $parameter);
            if (!$result['errors']) {
                return $result['body']->container[$resource];
            } else {
                return [];
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  filterShopifyData =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateSubscriptionContract($user_id, $contractID, $update = 'status')
    {
        try {
            logger('========= START:: updateSubscriptionContract ==> User :: ' . $user_id . ' contract :: ' . $contractID . '=========');
            $contract = SsContract::find($contractID);
            $shop = Shop::where('user_id', $user_id)->first();
            $draftId = $this->getSubscriptionDraft($user_id, $contract->shopify_contract_id);
            if ($draftId) {
                $countryCode = Country::select('code')->where('name', $contract->ship_country)->first();
                $code = ($countryCode) ? $countryCode->code : $shop->country_code;
                $draftQuery = '
                  mutation{
                    subscriptionDraftUpdate(draftId: "' . $draftId . '",
                    input: {';
                if ($update == 'status') {
                    // logger($contract->status);
                    if ($contract->status_display == 'Expired') {
                        $sh_status = 'Expired';
                    } elseif ($contract->status_display == 'Billing Failed') {
                        $sh_status = 'Failed';
                    } elseif ($contract->status == 'resumed') {
                        $sh_status = 'ACTIVE';
                    } else {
                        $sh_status = $contract->status;
                    }
                    $draftQuery .= '
                         status: ' . strtoupper($sh_status) . '
                    ';
                } elseif ($update == 'address') {

                    $draftQuery .= '
                         deliveryMethod: {
                            shipping: {
                                address: {
                                    address1: "' . $contract->ship_address1 . '",
                                    address2: "' . $contract->ship_address2 . '",
                                    city: "' . $contract->ship_city . '",
                                    company: "' . $contract->ship_company . '",
                                    country: "' . $contract->ship_country . '",
                                    firstName: "' . $contract->ship_firstName . '",
                                    lastName: "' . $contract->ship_lastName . '",
                                    province: "' . $contract->ship_province . '",
                                    provinceCode: "' . $contract->ship_provinceCode . '",
                                    zip: "' . $contract->ship_zip . '",
                                    countryCode: ' . $code . ',
                                    phone: "' . $contract->ship_phone . '"
                                }
                            }
                        }
                    ';
                } elseif ($update == 'selling_plan') {
                    $db_sellingPlan = SsPlan::where('id', $contract->ss_plan_id)->first();
                    $draftQuery .= '
                        billingPolicy: {
                            intervalCount: ' . $db_sellingPlan['billing_interval_count'] . ',
                            interval: ' . strtoupper($db_sellingPlan['billing_interval']) . ',
                        },
                        deliveryPolicy: {
                            intervalCount: ' . $db_sellingPlan['delivery_interval_count'] . ',
                            interval: ' . strtoupper($db_sellingPlan['delivery_interval']) . ',
                        },
                        ';
                    // deliveryPrice: ' . $deliveryPrice . ',
                } elseif ($update == "change_frequency") {
                    $draftQuery .= '
                    billingPolicy: {
                        intervalCount: ' . $contract->billing_interval_count . ',
                        interval: ' . strtoupper($contract->billing_interval) . ',
                    },
                    deliveryPolicy: {
                        intervalCount: ' . $contract->billing_interval_count . ',
                        interval: ' . strtoupper($contract->billing_interval) . ',
                    },
                    ';
                }
                $draftQuery .= '
                    }) {
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
                $resultSubscriptionDraftContract = $this->graphQLRequest($user_id, $draftQuery);
                $message = $this->getReturnMessage($resultSubscriptionDraftContract, 'subscriptionDraftUpdate');
                if ($message == 'success') {
                    $result = $this->commitDraft($user_id, $draftId);
                    $message = $result['message'];
                }
                return $message;
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateSubscriptionContract =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function commitDraft($user_id, $draftID)
    {
        try {
            logger('========= START:: commitDraft ==> User :: ' . $user_id . '=========');
            $commitDraft = '
                  mutation{
                    subscriptionDraftCommit(draftId: "' . $draftID . '") {
                    contract {
                      id
                    }
                    userErrors {
                      code
                      field
                      message
                    }
                  }
                }';
            $result = $this->graphQLRequest($user_id, $commitDraft);
            $res['message'] = $this->getReturnMessage($result, 'subscriptionDraftCommit');
            $res['result'] = $result;
            return $res;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  commitDraft =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getSubscriptionDraft($user_id, $contractID)
    {
        $user = User::find($user_id);
        // get draft id from contract id
        $query = 'mutation {
          subscriptionContractUpdate(contractId: "gid://shopify/SubscriptionContract/' . $contractID . '") {
              draft {
                id
              }
              userErrors {
                  message
              }
            }
          }';
        $resultSubscriptionContract = $this->graphQLRequest($user->id, $query);
        $message = $this->getReturnMessage($resultSubscriptionContract, 'subscriptionContractUpdate');
        if ($message == 'success') {
            $subscriptionContract = $resultSubscriptionContract['body']->container['data']['subscriptionContractUpdate'];
            return (@$subscriptionContract['draft']['id']) ? $subscriptionContract['draft']['id'] : '';
        } else {
            return $message;
        }
    }

    public function subscriptionContractSetNextBillingDate($user_id, $contractID)
    {
        try {
            logger('========= START:: subscriptionContractSetNextBillingDate ==> user :: ' . $user_id . ' ==> contractID :: ' . $contractID . '=========');
            $contract = SsContract::where('shopify_contract_id', $contractID)->first();
            $query = 'mutation {
                 subscriptionContractSetNextBillingDate(contractId: "gid://shopify/SubscriptionContract/' . $contractID . '", date: "' . date('Y-m-d\TH:i:s\Z', strtotime($contract->next_order_date)) . '") {
                    userErrors {
                      code
                      field
                      message
                    }
                  }
                }';
            $result = $this->graphQLRequest($user_id, $query);
            return $this->getReturnMessage($result, 'subscriptionContractSetNextBillingDate');
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  subscriptionContractSetNextBillingDate =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createCustomerPaymentMethod($data, $user_id)
    {
        try {
            logger('========= START:: createCustomerPaymentMethod ==> user :: ' . $user_id . ' =========');
            $query = 'mutation MyMutation {
                        customerPaymentMethodRemoteCreditCardCreate(
                          customerId: "gid://shopify/Customer/' . $data['customer_shopify_id'] . '",
                          stripeCustomerId: "' . $data['gateway_customer_id'] . '"
                          ) {
                            customerPaymentMethod {
                              id
                              instrument {
                                 ... on CustomerCreditCard {
                                    firstDigits
                                    source
                                    expiresSoon
                                    brand
                                    expiryMonth
                                    expiryYear
                                    isRevocable
                                    lastDigits
                                    maskedNumber
                                    name
                                }
                              }
                            }
                            userErrors {
                              field
                              message
                            }
                      }
                    }';
            // $query = '
            //      mutation{
            //         customerPaymentMethodRemoteCreate(
            //             customerId: "gid://shopify/Customer/' . $data['customer_shopify_id'] .'"
            //             remoteReference: {stripePaymentMethod: {customerId: "'. $data['gateway_customer_id'] .'"}}
            //           ) {
            //             customerPaymentMethod{
            //                 id
            //                 instrument {
            //                     ... on CustomerCreditCard {
            //                       brand
            //                       lastDigits
            //                     }
            //                 }
            //             }
            //           }
            //     }';
            $customerPaymentMethod = $this->graphQLRequest($user_id, $query);
            // logger("========================= customerPaymentMethod ======================");
            // logger(json_encode($customerPaymentMethod));
            $message = $this->getReturnMessage($customerPaymentMethod, 'customerPaymentMethodRemoteCreditCardCreate');
            $res['success'] = false;
            $res['message'] = $message;
            if ($message == 'success') {
                $res['success'] = true;
                $res['message'] = $customerPaymentMethod['body']->container['data']['customerPaymentMethodRemoteCreditCardCreate']['customerPaymentMethod']['id'];
            }
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  createCustomerPaymentMethod =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function subscriptionDraftLineAdd($user_id, $lineItem, $draftId = '')
    {
        try {
            logger('========= START:: subscriptionDraftLineAdd ==> user :: ' . $user_id . '=========');
            $lineItem = (gettype($lineItem) == 'array') ? $lineItem : $lineItem->toArray();
            $draftId = ($draftId == '') ? $this->getSubscriptionDraft($user_id, $lineItem['shopify_contract_id']) : $draftId;
            $adjustmentType = ($lineItem['discount_type'] == '%') ? 'PERCENTAGE' : 'PRICE';
            $adjustmentValue = ($lineItem['discount_type'] == '%') ? 'percentage' : 'fixedValue';
            if ($draftId) {
                $query = '
                 mutation{
                      subscriptionDraftLineAdd(
                            draftId: "' . $draftId . '",
                            input: {
                                currentPrice: "' . $lineItem['price'] . '"
                                pricingPolicy: {
                                    basePrice: "' . $lineItem['price'] . '"
                                    cycleDiscounts: {
                                        adjustmentType: ' . $adjustmentType . ',
                                        adjustmentValue: {
                                            ' . $adjustmentValue . ': ' . $lineItem['discount_amount'] . '
                                        },
                                        computedPrice: "' . $lineItem['final_amount'] . '",
                                        afterCycle: 0
                                    }
                                },
                                productVariantId: "gid://shopify/ProductVariant/' . $lineItem['shopify_variant_id'] . '",
                                quantity: ' . $lineItem['quantity'] . '
                            },
                        )
                        {
                            lineAdded {
                              id
                              sellingPlanId
                              sellingPlanName
                            }
                            userErrors {
                              code
                              field
                              message
                            }
                        }
                    }';
                $subscriptionDraftResult = $this->graphQLRequest($user_id, $query);
                $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineAdd');
                if ($message == 'success') {
                    $commitResult = $this->commitDraft($user_id, $draftId);
                    $message = $commitResult['message'];
                    if ($message == 'success') {
                        $res['contractID'] = $commitResult['result']['body']['data']['subscriptionDraftCommit']['contract']['id'];
                        $res['id'] = $subscriptionDraftResult['body']->container['data']['subscriptionDraftLineAdd']['lineAdded']['id'];
                        $res['sellingPlanId'] = $subscriptionDraftResult['body']->container['data']['subscriptionDraftLineAdd']['lineAdded']['sellingPlanId'];
                        $res['sellingPlanName'] = $subscriptionDraftResult['body']->container['data']['subscriptionDraftLineAdd']['lineAdded']['sellingPlanName'];
                        return $res;
                    }
                }
                return $message;
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  subscriptionDraftLineAdd =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function subscriptionDraftLineUpdate($user_id, $lineItem)
    {
        try {
            logger('========= START:: subscriptionDraftLineUpdate ==> user :: ' . $user_id . '=========');
            $draftId = $this->getSubscriptionDraft($user_id, $lineItem->shopify_contract_id);
            $adjustmentType = ($lineItem->discount_type == '%') ? 'PERCENTAGE' : 'FIXED_AMOUNT';
            $adjustmentValue = ($lineItem->discount_type == '%') ? 'percentage' : 'fixedValue';
            if ($draftId) {
                $query = '
                 mutation{
                      subscriptionDraftLineUpdate(
                            draftId: "' . $draftId . '",
                            input: {
                                currentPrice: "' . $lineItem->discount_amount . '"
                                pricingPolicy: {
                                    basePrice: "' . $lineItem->price . '",
                                    cycleDiscounts: {
                                        adjustmentType: ' . $adjustmentType . ',
                                        adjustmentValue: {
                                            ' . $adjustmentValue . ': ' . $lineItem->discount_amount . '
                                        },
                                        computedPrice: "' . $lineItem->discount_amount . '",
                                        afterCycle: 0
                                    }
                                },
                                productVariantId: "gid://shopify/ProductVariant/' . $lineItem->shopify_variant_id . '",
                                quantity: ' . $lineItem->quantity . '
                            },
                            lineId: "gid://shopify/SubscriptionLine/' . $lineItem->shopify_line_id . '"
                        ){
                            userErrors {
                              code
                              field
                              message
                            }
                        }
                    }';
                $subscriptionDraftResult = $this->graphQLRequest($user_id, $query);
                $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineUpdate');
                if ($message == 'success') {
                    $result = $this->commitDraft($user_id, $draftId);
                    $message = $result['message'];
                }
                return $message;
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  subscriptionDraftLineUpdate =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function subscriptionDraftLineRemove($user_id, $lineItem)
    {
        try {
            logger('========= START:: subscriptionDraftLineRemove ==> user :: ' . $user_id . '=========');
            $draftId = $this->getSubscriptionDraft($user_id, $lineItem->shopify_contract_id);
            if ($draftId) {
                $query = '
                mutation{
                     subscriptionDraftLineRemove(draftId: "' . $draftId . '", lineId: "gid://shopify/SubscriptionLine/' . $lineItem->shopify_line_id . '") {
                        userErrors {
                          code
                          message
                          field
                        }
                      }
                    }';
                $subscriptionDraftResult = $this->graphQLRequest($user_id, $query);
                $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineRemove');
                if ($message == 'success') {
                    $result = $this->commitDraft($user_id, $draftId);
                    $message = $result['message'];
                }
                return $message;
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  subscriptionDraftLineRemove =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function subscriptionRemoveAutomaticDiscount($user_id, $discount_id, $shopify_contract_id)
    {
        try {
            logger('========= START:: subscriptionRemoveAutomaticDiscount ==> user :: ' . $user_id . '=========');
            $draftId = $this->getSubscriptionDraft($user_id, $shopify_contract_id);
            if ($draftId) {
                $query = '
                mutation MyMutation {
                    subscriptionDraftDiscountRemove(discountId: "' . $discount_id . '", draftId: "' . $draftId . '") {
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
                $subscriptionDraftResult = $this->graphQLRequest($user_id, $query);
                // logger('subscriptionDraftResult :: ' . json_encode($subscriptionDraftResult));
                $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftDiscountRemove');
                // logger($message);
                if ($message == 'success') {
                    $result = $this->commitDraft($user_id, $draftId);
                    logger('Commit Result :: ' . json_encode($result));
                    $message = $result['message'];
                }
                return $message;
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  subscriptionRemoveAutomaticDiscount =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function subscriptionContractPriceUpdate($user_id, $lineItem, $contract)
    {
        try {
            logger('========= START:: subscriptionContractPriceUpdate ==> user :: ' . $user_id . '=========');
            $draftId = $this->getSubscriptionDraft($user_id, $lineItem->shopify_contract_id);
            $adjustmentType = ($lineItem->discount_type == '%') ? 'PERCENTAGE' : 'FIXED_AMOUNT';
            $adjustmentValue = ($lineItem->discount_type == '%') ? 'percentage' : 'fixedValue';
            $computedPrice = $contract->pricing_adjustment_value;
            if ($contract->is_onetime_payment) {
                $computedPrice = 0;
            } else {
                if ($contract->trial_available && $contract->trial_days == Carbon::now()->diffInDays($contract->created_at)) {
                    $computedPrice = $contract->pricing_adjustment_value;
                }
            }
            if ($draftId) {
                $query = '
                mutation{
                      subscriptionDraftLineUpdate(
                            draftId: "' . $draftId . '",
                            input: {
                                currentPrice: ' . $computedPrice . '
                                pricingPolicy: {
                                basePrice: "' . $lineItem->price . '",
                                cycleDiscounts: {
                                    afterCycle: 1
                                    adjustmentType: PRICE
                                    adjustmentValue: { fixedValue:  ' . $computedPrice . '  }
                                    computedPrice: ' . $computedPrice . ',                                }
                                },
                                productVariantId: "gid://shopify/ProductVariant/' . $lineItem->shopify_variant_id . '",
                                quantity: ' . $lineItem->quantity . '
                            },
                            lineId: "gid://shopify/SubscriptionLine/' . $lineItem->shopify_line_id . '",
                        ){
                            userErrors {
                              code
                              field
                              message
                            }
                        }
                }';

                $subscriptionDraftResult = $this->graphQLRequest($user_id, $query);
                $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineUpdate');
                if ($message == 'success') {
                    $this->saveActivity($user_id, $contract->ss_customer_id, $contract->id, 'System', 'Membership price automatically updated from ' . $lineItem->currency_symbol . $lineItem->price . ' to ' . $lineItem->currency_symbol . $computedPrice);
                    $lineItem->price = $computedPrice;
                    $lineItem->discount_amount = $computedPrice;
                    $lineItem->save();
                    $result = $this->commitDraft($user_id, $draftId);
                    $message = $result['message'];
                }
                return $message;
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  subscriptionContractPriceUpdate =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getCustomerPaymentMethodID($user_id, $contractID)
    {
        try {
            logger('========= START:: getCustomerPaymentMethodID =========');
            $paymentMethodID = '';
            $query = '{
                     subscriptionContract(id: "gid://shopify/SubscriptionContract/' . $contractID . '") {
                        customerPaymentMethod {
                          id
                        }
                    }
                }';
            $result = $this->graphQLRequest($user_id, $query);
            if (!$result['errors']) {
                $subContract = $result['body']->container['data']['subscriptionContract'];
                $paymentMethodID = (@$subContract['customerPaymentMethod']['id']) ? $subContract['customerPaymentMethod']['id'] : '';
            } else {
                // logger('============== getCustomerPaymentMethodID ===============');
                // logger(json_encode($result));
            }
            return $paymentMethodID;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getCustomerPaymentMethodID =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    public function createCustomerPaymentMethodSendUpdateEmail($user_id, $contractID)
    {
        try {
            logger('========= START:: createCustomerPaymentMethodSendUpdateEmail ==> user :: ' . $user_id . '=========');
            $paymentMethodID = $this->getCustomerPaymentMethodID($user_id, $contractID);
            if ($paymentMethodID != '') {
                $query = 'mutation{
                          customerPaymentMethodSendUpdateEmail(customerPaymentMethodId: "' . $paymentMethodID . '") {
                            customer {
                              id
                            }
                            userErrors {
                              field
                              message
                            }
                        }
                      }';
                $result = $this->graphQLRequest($user_id, $query);
                $message = $this->getReturnMessage($result, 'customerPaymentMethodSendUpdateEmail');
            } else {
                $message = 'Customer payment method not found';
            }
            return $message;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  createCustomerPaymentMethodSendUpdateEmail =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getPrepaidFulfillments($user_id, $last_billing_order)
    {
        try {
            logger('========= START:: getPrepaidFulfillments =========');
            $query = '{
                    order(id: "gid://shopify/Order/' . $last_billing_order . '") {
                      fulfillmentOrders(first: 250) {
                        edges {
                          node {
                            fulfillAt
                            status
                            id
                            order {
                              name
                              legacyResourceId
                            }
                          }
                        }
                      }
                    }
                  }';
            $result = $this->graphQLRequest($user_id, $query);
            if (!$result['errors']) {
                $fulfillmentOrders = isset($result['body']->container['data']['order']['fulfillmentOrders']) ? $result['body']->container['data']['order']['fulfillmentOrders'] : null;
                return ($fulfillmentOrders) ? $fulfillmentOrders['edges'] : [];
            } else {
                logger('========= USER ERROR:: getPrepaidFulfillments =========');
                logger(json_encode($result));
                return [];
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getPrepaidFulfillments =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createDeliveryProfile($user_id, $ss_profile_id, $shp_location_id = '')
    {
        try {
            logger('========= START:: createDeliveryProfile ==> user :: ' . $user_id . '=========');
            $user = User::find($user_id);
            $shop = Shop::select('currency')->where('user_id', $user_id)->first();
            if ($shp_location_id == '') {
                $this->getActiveLocations($user, '');
            } else {
                $this->locationIds = (array) $shp_location_id;
            }
            $activeLocations = $this->locationIds;
            foreach ($activeLocations as $lkey => $lval) {
                $location = str_replace('gid://shopify/Location/', '', $lval);
                $profile = SsShippingProfile::with('ShippingZones')->where('id', $ss_profile_id)->first()->toArray();
                $profileAction = ($profile['shopify_profile_id']) ? 'deliveryProfileUpdate' : 'deliveryProfileCreate';
                $locationGroup = (json_decode($profile['shopify_location_group_id'])) ? (array) json_decode($profile['shopify_location_group_id']) : [];
                $locationAction = (array_key_exists($location, $locationGroup)) ? 'locationGroupsToUpdate' : 'locationGroupsToCreate';
                // $locationAction = ( $profile['shopify_location_group_id'] ) ? 'locationGroupsToUpdate' : 'locationGroupsToCreate';
                if ($profileAction == 'deliveryProfileCreate') {
                    $query = 'mutation MyMutation {
                      ' . $profileAction . '(profile: { name: "' . $profile['name'] . '"';
                } else {
                    $query = 'mutation MyMutation {
                      ' . $profileAction . '(id: "gid://shopify/DeliveryProfile/' . $profile['shopify_profile_id'] . '", profile: {';
                }
                // logger(json_encode($profile));
                // logger(json_decode($profile['plan_group_ids']));
                if (!empty(json_decode($profile['plan_group_ids']))) {
                    $query .= ' sellingPlanGroupsToAssociate: ["gid://shopify/SellingPlanGroup/' . implode('","gid://shopify/SellingPlanGroup/', json_decode($profile['plan_group_ids'])) . '"],';
                }
                // $query .= ( $profile['shopify_profile_id'] ) ? ', id: "gid://shopify/DeliveryProfile/' . $profile['shopify_profile_id'] .'", ' : '';
                $zonesToCreate = [];
                $zonesToUpdate = [];
                foreach ($profile['shipping_zones'] as $zkey => $zval) {
                    $zoneIDs = (array) json_decode($zval['shopify_zone_id']);
                    $zoneAction = (empty($zoneIDs) || !array_key_exists($location, $zoneIDs)) ? 'zonesToCreate' : 'zonesToUpdate';
                    $country = implode(',', json_decode($zval['countries']));
                    if ($country == 'Rest of World') {
                        $countries = '{ restOfWorld: true }';
                    } else {
                        $country = explode(',', $country);
                        $countries = '[';
                        foreach ($country as $ckey => $cval) {
                            $countries .= "{code: $cval, includeAllProvinces: true}";
                        }
                        $countries .= ']';
                    }
                    $zactive = ($zval['active']) ? 'true' : 'false';
                    $zoneArryQ = '{';
                    $zoneArryQ .= ($zoneAction == 'zonesToCreate') ? '' : 'id: "gid://shopify/DeliveryZone/' . $zoneIDs[$location] . '" ';
                    $zoneArryQ .= 'name: "' . $zval['zone_name'] . '",
                      countries: ' . $countries . '
                      methodDefinitionsToCreate: {
                        name: "' . $zval['rate_name'] . '",
                        active: ' . $zactive . ',
                        rateDefinition: {
                            price: {
                                amount: "' . $zval['rate_value'] . '",
                                currencyCode: ' . $shop['currency'] . '
                            }
                        },
                      }
                  }';
                    ($zoneAction == 'zonesToCreate') ? array_push($zonesToCreate, $zoneArryQ) : array_push($zonesToUpdate, $zoneArryQ);
                }
                $query .= $locationAction . ': {';
                $query .= ($locationAction == 'locationGroupsToUpdate') ? 'id: "gid://shopify/DeliveryLocationGroup/' . $locationGroup[$location] . '", ' : '';
                $query .= '
                          locations: "' . $lval . '",';
                $query .= (!empty($zonesToCreate)) ? 'zonesToCreate: [' : '';
                $query .= (!empty($zonesToCreate)) ? implode('', $zonesToCreate) : '';
                $query .= (!empty($zonesToCreate)) ? ']' : '';

                $query .= (!empty($zonesToUpdate)) ? 'zonesToUpdate: [' : '';
                $query .= (!empty($zonesToUpdate)) ? implode('', $zonesToUpdate) : '';
                $query .= (!empty($zonesToUpdate)) ? ']' : '';
                $query .= '}';
                $query .= '}) {
                      profile {
                        id
                        profileLocationGroups {
                          locationGroup {
                            id
                          }
                          locationGroupZones(first: ' . count($profile['shipping_zones']) . ', reverse: false) {
                            edges {
                              node {
                                zone {
                                  id
                                  name
                                }
                              }
                            }
                          }
                        }
                      }
                      userErrors {
                        field
                        message
                      }
                    }
                  }';
                $result = $this->graphQLRequest($user->id, $query);
                if (!$result['errors']) {
                    $message = $this->getReturnMessage($result, $profileAction);
                    if ($message == 'success') {
                        $deliveryProfile = $result['body']->container['data'][$profileAction]['profile'];
                        $locationGroup = $deliveryProfile['profileLocationGroups'][$lkey];
                        $saveProfile = SsShippingProfile::where('id', $ss_profile_id)->first();
                        $saveProfile->shopify_profile_id = str_replace('gid://shopify/DeliveryProfile/', '', $deliveryProfile['id']);
                        $locationGids = (json_decode($saveProfile->shopify_location_group_id)) ? (array) json_decode($saveProfile->shopify_location_group_id) : [];
                        $locationGid = str_replace('gid://shopify/DeliveryLocationGroup/', '', $locationGroup['locationGroup']['id']);
                        // if( !in_array($location, $locationGids) ){
                        //   $locationGids[] = $location;
                        // }
                        if (@$locationGids[$location]) {
                        } else {
                            $locationGids[$location] = '';
                        }
                        $locationGids[$location] = $locationGid;
                        $saveProfile->shopify_location_group_id = json_encode($locationGids);
                        $saveProfile->save();
                        $locationGZone = $locationGroup['locationGroupZones'];
                        $saveZones = SsShippingZone::where('ss_shipping_profile_id', $ss_profile_id)->get();
                        foreach ($saveZones as $zkey => $zvalue) {
                            $zones = (array) json_decode($zvalue->shopify_zone_id);
                            if (@$zones[$location]) {
                            } else {
                                $zones[$location] = '';
                            }
                            $zones[$location] = str_replace('gid://shopify/DeliveryZone/', '', $locationGZone['edges'][$zkey]['node']['zone']['id']);
                            $zvalue->shopify_zone_id = json_encode($zones);
                            $zvalue->save();
                        }
                    } else {
                        return $message;
                    }
                } else {
                    logger('========= USER ERROR:: createDeliveryProfile =========');
                    logger(json_encode($result));
                    return [];
                }
            }
            return 'success';
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  createDeliveryProfile =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function removeDeliveryProfile($user_id, $ss_profile_id)
    {
        try {
            logger('========= START:: removeDeliveryProfile ==> user :: ' . $user_id . '=========');
            $profile = SsShippingProfile::with('ShippingZones')->where('id', $ss_profile_id)->first()->toArray();
            $query = 'mutation MyMutation{
                    deliveryProfileRemove(id: "gid://shopify/DeliveryProfile/' . $profile['shopify_profile_id'] . '") {
                      userErrors {
                        field
                        message
                      }
                    }
                  }';
            $result = $this->graphQLRequest($user_id, $query);
            return $this->getReturnMessage($result, 'deliveryProfileRemove');
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  removeDeliveryProfile =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getSubscriptionDiscount($user_id, $shopify_contract_id)
    {
        try {
            logger('========= START:: getSubscriptionDiscount ==> ' . $shopify_contract_id . ' =========');
            $user = User::find($user_id);
            $query = ' {
            subscriptionContract(id: "gid://shopify/SubscriptionContract/' . $shopify_contract_id . '") {
              discounts(first: 10) {
                edges {
                  node {
                    id
                    rejectionReason
                    type
                    title
                    value {
                      ... on SubscriptionDiscountPercentageValue {
                        __typename
                        percentage
                      }
                      ... on SubscriptionDiscountFixedAmountValue {
                        __typename
                        amount {
                          amount
                          currencyCode
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        ';
            $result = $this->graphQLRequest($user->id, $query);
            $discount['title'] = '';
            $discount['amount'] = '';
            // logger(json_encode($result));
            if (!$result['errors']) {
                $subContract = $result['body']->container['data']['subscriptionContract'];
                $discounts = (@$subContract['discounts']['edges']) ? $subContract['discounts']['edges'] : [];
                if (!empty($discounts)) {
                    foreach ($discounts as $dkey => $dvalue) {
                        $dnode = $dvalue['node'];
                        if ($dnode['rejectionReason'] == null && $dnode['type'] == 'CODE_DISCOUNT') {
                            $discount['title'] = $dnode['title'];
                            $discount['id'] = $dnode['id'];

                            if (@$dnode['value']['__typename'] == 'SubscriptionDiscountPercentageValue') {
                                $discount['type'] = 'percentage';
                                $discount['displayAmount'] = $dnode['value']['percentage'] . '%';
                                $discount['amount'] = $dnode['value']['percentage'];
                            } elseif (@$dnode['value']['__typename'] == 'SubscriptionDiscountFixedAmountValue') {
                                $discount['type'] = 'fixed';
                                if ($dnode['value']['amount']['currencyCode'] == 'JPY') {
                                    $discount['amount'] = number_format($dnode['value']['amount']['amount'], 0);
                                } else {
                                    $discount['amount'] = number_format($dnode['value']['amount']['amount'], 2);
                                }
                                $discount['displayAmount'] = currencyH($dnode['value']['amount']['currencyCode']) . $discount['amount'];
                            }
                        }
                    }
                }
            }
            return $discount;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getSubscriptionDiscount =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
    //    public function createDeliveryProfile($user_id, $ss_profile_id){
    //       try{
    //           logger('========= START:: createDeliveryProfile =========');
    //           $profile = SsShippingProfile::with('ShippingZones')->where('id', $ss_profile_id)->first()->toArray();
    //           $profileAction = ( $profile['shopify_profile_id'] ) ? 'deliveryProfileUpdate' : 'deliveryProfileCreate';
    //           $locationAction = ( $profile['shopify_location_group_id'] ) ? 'locationGroupsToUpdate' : 'locationGroupsToCreate';
    //           $user = User::find($user_id);
    //           $shop = Shop::select('currency')->where('user_id', $user_id)->first();
    //
    //           $this->getActiveLocations($user, '');
    //           $activeLocations = $this->locationIds;
    //
    //           $query = 'mutation MyMutation {
    //                    '. $profileAction .'(profile: { name: "'. $profile['name'] . '"';
    //
    //           $query .= ( $profile['shopify_profile_id'] ) ? 'id: "gid://shopify/DeliveryProfile/' . $profile['shopify_profile_id'] .'", ' : '';
    //
    //           $query .= ' sellingPlanGroupsToAssociate: ["gid://shopify/SellingPlanGroup/'. implode('","gid://shopify/SellingPlanGroup/', json_decode($profile['plan_group_ids'])) .'"],';
    //
    //           $query .=  $locationAction .': ';
    //
    //           foreach ( $activeLocations as $lkey=>$lval ){
    //               $query .=  '{
    //                        locations: "'. $lval .'",
    //                            zonesToCreate: [';
    //               foreach ( $profile['shipping_zones'] as $zkey=>$zval ) {
    //                   $country = implode(',', json_decode($zval['countries']));
    //                   if( $country == 'Rest of World' ){
    //                       $countries = '{ restOfWorld: true}';
    //                   }else{
    //                       $country = explode(',', $country);
    //                       $countries = '';
    //                       foreach ( $country as $ckey=>$cval ){
    //                           $countries .= "{code: $cval, includeAllProvinces: true}";
    //                       }
    //                   }
    //                   $zactive = ( $zval ) ? 'true' : 'false';
    //                   $query .= '{
    //                                name: "'. $zval['zone_name'] .'",
    //                                countries: ['.$countries.']
    //                                methodDefinitionsToCreate: {
    //                                    name: "'. $zval['rate_name'] .'",
    //                                    active: '. $zactive .',
    //                                    rateDefinition: {
    //                                        price: {
    //                                            amount: "'. $zval['rate_value'] .'",
    //                                            currencyCode: '.$shop['currency'].'
    //                                        }
    //                                    },
    //                                 }
    //                             }';
    //               }
    //               $query .=  ']';
    //           }
    //           $query .= '}) {
    //                    userErrors {
    //                      field
    //                      message
    //                    }
    //                  }
    //                }';
    //           dump($query);
    //           $result = $this->graphQLRequest($user->id, $query);
    //           dd($result);
    //       }catch( \Exception $e ){
    //          logger('========= ERROR:: createDeliveryProfile =========');
    ////          logger($e);
    //           dd($e);
    //       }
    //    }

    public function getActiveLocations($user, $after)
    {
        try {
            logger('========= START:: getActiveLocations =========');
            $this->locationIds = [];
            $afterK = ($after) ? "after: " : '';
            $after = ($after) ? '"' . $after . '", ' : $after;
            $query = '{
              locations(first: 250, ' . $afterK . $after . 'includeInactive: false) {
                edges {
                  node {
                    id
                  }
                  cursor
                }
                pageInfo {
                  hasNextPage
                }
              }
            }';
            $result = $this->graphQLRequest($user->id, $query);
            if (!$result['errors']) {
                $locations = $result['body']->container['data']['locations'];
                $edges = $locations['edges'];
                foreach ($edges as $key => $value) {
                    $this->locationIds[] = $value['node']['id'];
                }
                if ($locations['pageInfo']['hasNextPage']) {
                    $after = end($locations['edges'])['cursor'] ?? null;
                    $this->getActiveLocations($user, $after);
                }
            } else {
                logger(json_encode($result));
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getActiveLocations =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function calculateNextOrderDate($sellingPlan, $shop)
    {
        $orderDate = Carbon::now();

        // logger("=======================  Order Date ======================");
        // logger($orderDate);

        $deliveryCutOff = $sellingPlan->delivery_cutoff;
        $billingAnchorType = $sellingPlan->billing_anchor_type;
        $billingAnchoMonth = $sellingPlan->billing_anchor_month;
        $billingAnchorDay = $sellingPlan->billing_anchor_day;

        if ($billingAnchorType == 'WEEKDAY') {
            $range = CarbonPeriod::create($orderDate, 7);

            // logger("======================= Weekday Order Range ======================");
            // logger(json_encode($range));

            foreach ($range as $carbon) { //This is an iterator
                if ($carbon->dayOfWeekIso == $billingAnchorDay) {
                    $nextOrderDate = $carbon;
                    // logger("======================= Next Order Date ======================");
                    // logger($nextOrderDate);
                }
            }

            if ($orderDate >= $nextOrderDate->copy()->subDays($deliveryCutOff)) {
                $nextOrderDate = $nextOrderDate->addDays(7);
                // logger("======================= Next Order Date ======================");
                // logger($nextOrderDate);
            }
        } elseif ($billingAnchorType == 'MONTHDAY') {
            $range = CarbonPeriod::create(Carbon::now(), Carbon::now()->addMonths(1));
            foreach ($range as $carbon) {
                if ($carbon->day == $billingAnchorDay) {
                    $nextOrderDate = Carbon::create($this->getSubscriptionTimeDate(date(
                        "Y-m-d",
                        strtotime($carbon)
                    ), $shop->id));
                }
            }
            if ($orderDate >= $nextOrderDate->copy()->subDay($deliveryCutOff)) {
                $nextOrderDate = $this->getSubscriptionTimeDate(date(
                    "Y-m-d",
                    strtotime($nextOrderDate->addMonth(1))
                ), $shop->id);
            }
        } else {
            $range = CarbonPeriod::create(Carbon::now(), Carbon::now()->addYears(1));
            foreach ($range as $carbon) {
                if ($carbon->day == $billingAnchorDay && $carbon->month == $billingAnchoMonth) {
                    $nextOrderDate = Carbon::create($this->getSubscriptionTimeDate(date(
                        "Y-m-d",
                        strtotime($carbon)
                    ), $shop->id));
                }
            }

            if ($orderDate >= $nextOrderDate->copy()->subDay($deliveryCutOff)) {
                $nextOrderDate = $this->getSubscriptionTimeDate(date(
                    "Y-m-d",
                    strtotime($nextOrderDate->addYears(1))
                ), $shop->id);
            }
        }

        return $nextOrderDate;
    }

    /**
     * Fetch online store data like page, product, collection, article, blog data
     * @param $user
     */
    public function getStoreData($user)
    {
        try {
            logger('========= START:: getStoreData =========');
            $ruleType = ['pages', 'blogs', 'articles'];
            $data = [];
            foreach ($ruleType as $key => $value) {
                $endPoint = '/admin/api/' . $value . '.json';
                $parameter['limit'] = 250;
                $parameter['fields'] = "id, title";

                $result = $user->api()->rest('GET', $endPoint, $parameter);

                $k = substr($value, 0, -1);
                if (!$result['errors']) {
                    $data[$k] = $result['body']->container[$value];
                }
            }
            return $data;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getStoreData =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updatePlanMetafields($user, $planGroupID, $shopifyProductID)
    {
        try {
            logger('========= START:: updatePlanMetafields ==> user :: ' . $user->id . '=========');
            // update form metafields
            $ret['success'] = false;
            $ret['msg'] = 'Error - please try again';
            $result = $this->saveMetafields($user, 'questions', 'string', $planGroupID, $shopifyProductID, '');
            if (!$result) {
                $result = $this->saveMetafields($user, 'memberships', 'json_string', $planGroupID, '', '');
                if (!$result) {
                    $ret['success'] = true;
                    $ret['msg'] = 'Saved';
                }
            }
            return $ret;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updatePlanMetafields =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function saveMetafields($user, $metaKey, $metaValueType, $planGroupID = '', $shopifyProductID = '', $metaValue = '')
    {
        try {
            logger('========= START:: saveMetafields =========');
            if ($metaKey == 'questions' && $metaValue == '') {
                $metaValue = $this->getFormMetaHtml($user->id, $planGroupID);
            } elseif ($metaKey == 'memberships' && $metaValue == '') {
                // logger('****************** memberships  is called *****************');
                $metaValue = $this->getRuleMetaHtml($user->id, $planGroupID);

                // logger("-----------------------------------  Meta Value is an =====================");
                // logger($metaValue);
            }
            if ($metaValue != '') {
                $metafieldJson = [
                    "metafield" => [
                        'namespace' => 'simplee',
                        'key' => $metaKey,
                        'value' => $metaValue,
                        'type' => $metaValueType
                    ]
                ];
                $endPoint = ($shopifyProductID == '') ? '/admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json' : '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/' . $shopifyProductID . '/metafields.json';
                $result = $user->api()->rest('POST', $endPoint, $metafieldJson);   // shopify metafield result.
                logger(json_encode($result));
                return $result['errors'];
            }
            return false;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  saveMetafields =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getFormMetaHtml($userID, $planGroupID)
    {
        try {
            logger('========= START:: getFormMetaHtml ==> user :: ' . $userID . '=========');
            $html = '';
            $forms = SsForm::where('ss_plan_group_id', $planGroupID)->orderBy('field_order')->get();
            $html .= '<div class="simplee-properties">';
            if (count($forms) > 0) {
                foreach ($forms as $key => $value) {
                    if ($value->field_type == 'File Upload') {
                        $class = ($value->field_required) ? 'class="required fileuploadevent"' : 'class="fileuploadevent"';
                    } else {
                        $class = ($value->field_required) ? 'class="required"' : '';
                    }
                    $required = ($value->field_required) ? 'required' : '';
                    $label = ($value->field_displayed) ? 'name="properties[' . $value->field_label . ']"' : 'name="properties[_' . $value->field_label . ']"';
                    $id = strtolower(str_replace(' ', '-', $value->field_label));
                    if ($value->field_type == 'Dropdown List' || $value->field_type == 'Radio Group') {
                        $options = explode(',', $value->field_options);
                        $optionHtml = '';
                        foreach ($options as $key => $ovalue) {
                            $fvalue = strtolower(str_replace(' ', '-', $ovalue));
                            if ($value->field_type == 'Dropdown List') {
                                $optionHtml .= '<option value="' . $ovalue . '" for="' . $fvalue . '">' . $ovalue . '</option>';
                            } elseif ($value->field_type == 'Radio Group') {
                                $checked = ($key == 0) ? 'checked' : '';
                                $optionHtml .= '<div class="simplee-defaultwidget__checkbox-wrapper"><input type="radio" ' . $label . ' ' . $checked  . ' value="' . $ovalue . '" id="' . $fvalue . '"> <label class="simplee-defaultwidget__radio" for="' . $fvalue . '">' . $ovalue . '</label></div>';
                            }
                        }
                    }
                    if ($value->field_type == 'Text Field') {
                        $html .= '<div class="line-item-property__field memberships_options">';
                        $html .= '<p class="mb-0"><label for="' . $id . '">' . $value->field_label . '</label></p>';
                        $html .= '<div><input ' . $required . ' ' . $class . ' id="' . $id . '" type="text" ' . $label . ' value="">';
                        $html .= '<span class="sm-error ' . $id . '"></span></div>';
                        $html .= '</div>';
                    } elseif ($value->field_type == 'File Upload') {
                        $html .= '<div class="line-item-property__field memberships_options">';
                        $html .= '<p class="mb-0"><label for="' . $id . '">' . $value->field_label . '</label></p>';
                        $html .= '<div class="file-upload-wrapper"><input ' . $required . ' ' . $class . ' id="file-upload" type="file" ' . $label . ' value="">';
                        $html .= '<span class="sm-error ' . $id . '"></span></div>';
                        $html .= '</div>';
                    } elseif ($value->field_type == 'Text Area') {
                        $html .= '<div class="line-item-property__field memberships_options">';
                        $html .= '<p class="mb-0"><label for="' . $id . '">' . $value->field_label . '</label></p>';
                        $html .= '<div><textarea ' . $required . ' ' . $class . ' id="' . $id . '" ' . $label . '></textarea>';
                        $html .= '<span class="sm-error ' . $id . '"></span></div>';
                        $html .= '</div>';
                    } elseif ($value->field_type == 'Checkbox') {
                        $html .= '<div class="line-item__chkbox"><p class="line-item-property__field chkbox">';
                        $html .= '<input type="hidden" ' . $label . ' value="No">';
                        $html .= '<input id="' . $id . '" ' . $required . ' ' . $class . ' type="checkbox" ' . $label . 'value="Yes">';
                        $html .= '<label for="' . $id . '">' . $value->field_label . '</label>';

                        $html .= '</p>';
                        $html .= '<span class="sm-error ' . $id . '"></span></div>';
                    } elseif ($value->field_type == 'Dropdown List') {
                        $html .= '<p class="line-item-property__field">';
                        $html .= '<label>' .  $value->field_label . '</label>';
                        $html .= '<select ' . $class . ' id="' . $id . '" ' . $label . '>';
                        $html .= $optionHtml;
                        $html .= '</select>';
                        $html .= '<span class="sm-error ' . $id . '"></span>';
                        $html .= '</p>';
                    } elseif ($value->field_type == 'Radio Group') {
                        $html .= '<div class="line-item-property__field">';
                        $html .= '<p><label>' .  $value->field_label . '</label></p>';
                        $html .= '<div class="simplee-defaultwidget__checkbox">';
                        $html .= $optionHtml;
                        $html .= '</div>';
                        $html .= '<span class="sm-error ' . $id . '"></span>';
                        $html .= '</div>';
                    }
                }
            }
            $html .= '</div>';
            return $html;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  getFormMetaHtml =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getRuleMetaHtml($userID, $planGroupID)
    {
        try {
            logger('========= START:: getRuleMetaHtml ==> user :: ' . $userID . '=========');
            $html = '';
            $shop = Shop::where('user_id', $userID)->first();


            if (!$shop) {
                return false;
            }

            $rules = SsRule::where('shop_id', $shop->id)->get();

            $plans = SsPlanGroup::with('hasManyVariants')->where('shop_id', $shop->id)->where('active', 1)->get();

            $activeTags = $plans->pluck('tag_customer')->toArray();
            $activeTags = explode(",", implode(",", array_unique($activeTags)));

            $ruleEntity = [];
            if (count($rules) > 0) {

                foreach ($rules as $rkey => $rvalue) {
                    $planGroup = SsPlanGroup::where('id', $rvalue->ss_plan_group_id)->first();

                    if ($planGroup) {
                        $ruleattr1IdArrr = array_map('intval', explode(',',  $rvalue['rule_attribute1']));
                        $isMultipleIds = (count($ruleattr1IdArrr) > 1) ? true : false;

                        if ($rvalue['rule_attribute1'] != '' && !$isMultipleIds) {
                            $is_id = array_search($rvalue['rule_attribute1'], array_column($ruleEntity, 'id'));

                            if (false !== $is_id) {
                                $keys = array_keys($ruleEntity);
                                $actual_index = $keys[$is_id];
                                $tags = $this->filterTags($ruleEntity[$actual_index]['tags'], $planGroup->tag_customer);
                                $ruleEntity[$actual_index]['tags'] = $tags;
                            } else {
                                $ruleEntity[$rkey]['id'] = $rvalue['rule_attribute1'];
                                $ruleEntity[$rkey]['type'] = $rvalue['rule_type'];
                                $ruleEntity[$rkey]['tags'] = $planGroup->tag_customer;
                            }
                        } else if ($isMultipleIds) {
                            $ruleEntity[$rkey]['id'] =  $ruleattr1IdArrr;
                            $ruleEntity[$rkey]['type'] = $rvalue['rule_type'];
                            $ruleEntity[$rkey]['tags'] = $planGroup->tag_customer;
                        } else {
                            $is_type = array_search($rvalue['rule_type'], array_column($ruleEntity, 'type'));

                            if (false !== $is_type) {
                                $keys = array_keys($ruleEntity);
                                $actual_index = $keys[$is_type];

                                $tags = $this->filterTags($ruleEntity[$actual_index]['tags'], $planGroup->tag_customer);
                                $ruleEntity[$actual_index]['tags'] = $tags;
                            } else {
                                $ruleEntity[$rkey]['id'] = '';
                                $ruleEntity[$rkey]['type'] = $rvalue['rule_type'];
                                $ruleEntity[$rkey]['tags'] = $planGroup->tag_customer;
                            }
                        }
                    }
                }

                foreach ($ruleEntity as $key => $value) {
                    if ($value['id'] == '') {
                        unset($ruleEntity[$key]['id']);
                    } else {
                        $ruleEntity[$key]['id'] = (array)$ruleEntity[$key]['id'];
                    }
                }
            }
            $objRule = [];
            foreach ($ruleEntity as $key => $value) {
                array_push($objRule, (object)$value);
            }

            // get active selling plan id
            $activeProducts = [];
            $discounts = [];

            foreach ($plans as $pkey => $plan) {
                // Add active products
                $products = $plan->hasManyVariants;
                foreach ($products as $key => $product) {
                    array_push($activeProducts, $product->shopify_product_id);
                }

                // Add automatic discounts

                // dd((bool)$plan->is_display_on_cart_page);
                if ($plan->discount_code) {
                    $discounts[] =  [
                        "tag" => $plan->tag_customer,
                        "code" => $plan->discount_code,
                        "message" => $plan->discount_code_members,
                        "display_cart" => (bool)$plan->is_display_on_cart_page,
                        "display_login" => (bool)$plan->is_display_on_member_login
                    ];
                }
            }

            $activeProducts = array_map('intval', explode(",", implode(",", array_unique($activeProducts))));
            $ruleJson = [
                'config' =>  [
                    'active' => (count($rules) > 0),
                    'active_tags' => $activeTags,
                    'active_products' => array_unique($activeProducts)
                ],
                'rules' => $objRule,
                'discounts' => $discounts,
            ];

            return json_encode($ruleJson);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger('========= ERROR:: getRuleMetaHtml ==> user :: ' . $userID . '=========');
            logger($e);
        }
    }

    public function filterTags($tags, $newTag)
    {
        try {
            logger('========= START:: filterTags =========');
            $arr = explode(',', $tags);
            if (is_array($arr) && !in_array($newTag, $arr)) {
                array_push($arr, $newTag);
            }
            return implode(',', $arr);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  filterTags =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateShopifyTags($user, $shopifyID, $newTag, $tagType, $action = 'add')
    {
        try {
            logger('========= START:: updateShopifyTags ==> user :: ' . $user->id . ' ==> Shopify ID :: ' . $shopifyID . ' =========');
            $tagResult = $this->getShopifyData($user, $shopifyID, $tagType, 'id,tags');
            if (!empty($tagResult)) {
                $tagResult['tags'] = str_replace(', ', ',', $tagResult['tags']);
                $prevTags = explode(',', $tagResult['tags']);
                if ($action == 'add') {
                    if (!in_array($newTag, $prevTags)) {
                        array_push($prevTags, $newTag);
                    }
                } elseif ($action == 'remove') {
                    $exist_contract_tags = SsContract::where(['shopify_customer_id' => $tagResult['id'], 'status' => 'active'])->count();
                    if ($exist_contract_tags == 0) {
                        if (($key = array_search($newTag, $prevTags)) !== false) {
                            unset($prevTags[$key]);
                        }
                    }
                }
                array_filter($prevTags);
                $filteredTag = implode(',', $prevTags);
                $json = [
                    $tagType => [
                        'id' => $shopifyID,
                        'tags' => $filteredTag
                    ]
                ];
                $this->updateShopifyData($user, $shopifyID, $json, $tagType, true);
            }
            logger('========= END:: updateShopifyTags =========');
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateShopifyTags =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateShopifyNoteAttributes($user, $shopifyID, $newAttrKey, $newAttrVal, $tagType)
    {
        try {
            logger('========= START:: updateShopifyNoteAttributes :: ' . $shopifyID . ' ==> NewAttrKey :: ' . $newAttrKey . ' ==> newAttrVal :: ' . $newAttrVal . ' =========');
            $attrResult = $this->getShopifyData($user, $shopifyID, $tagType, 'id,note_attributes');
            if (!empty($attrResult)) {
                $prevAttrs = $attrResult['note_attributes'];
                $noteJson = [
                    'name' => $newAttrKey,
                    'value' => $newAttrVal
                ];
                array_push($prevAttrs, $noteJson);
                $json = [
                    $tagType => [
                        'id' => $shopifyID,
                        'note_attributes' => $prevAttrs
                    ]
                ];
                $this->updateShopifyData($user, $shopifyID, $json, $tagType);
            }
            logger('========= END:: updateShopifyNoteAttributes =========');
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateShopifyNoteAttributes =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateShopifyData($user, $shopifyID, $json, $tagType, $isCustomerTag = false)
    {
        try {
            logger('========= START:: updateShopifyData :: User :: ' . $user->name . ' :: Shopify ID :: ' . $shopifyID . '=========');
            $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/' . $tagType . 's/' . $shopifyID . '.json';
            $result = $user->api()->rest('PUT', $endPoint, $json);
            if (!$result['errors']) {
                return $result['body']->container[$tagType];
            } else {
                // logger('========= ERROR In shopify =========');
                // logger($endPoint);
                // logger(json_encode($json));
                // logger(($result));
                if ($result['status'] == 429){
                    logger("************ API LIMIT REACHED***************************");
                    sleep(10);
                    $this->updateShopifyData($user, $shopifyID, $json, $tagType, $isCustomerTag = false);
                }
                if ($isCustomerTag) {
                    $topic = "Failed to update tag";
                    $payload['parameter'] =  json_encode($json);
                    $payload['result'] = json_encode($result);
                    $this->sendErrorMail($user, $topic, $payload);
                }
                return [];
            }
            logger('========= END:: updateShopifyData =========');
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  updateShopifyData =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $contract_id
     * @param $html
     * @return string|void
     */
    public function fetchContractFormFields($contract_id, $html)
    {
        try {
            $formFields = SsAnswer::where('ss_contract_id', $contract_id)->get();
            $html .= (count($formFields) > 0) ? 'Additional Form Fields: <br><br>' : '';
            foreach ($formFields as $key => $formfield) {
                $html .= $formfield->question . ': ' . $formfield->answer . '<br>';
            }
            return $html;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  fetchContractFormFields =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user
     * @param $name
     * @param $tag
     * @param $shopify_css_id
     * @return array|void
     * This method is used to create or edit saved search in shopify while create or edit membership plan
     */
    public function createSavedSearch($user, $name, $tag, $shopify_css_id)
    {
        try {
            logger('========= START:: createSavedSearch  ==> user :: ' . $user->id . ' =========');
            $name = substr('[Simplee] - ' . $name, 0, 40);
            $parameter = [
                'customer_saved_search' => [
                    'name' => $name,
                    'query' => 'tag:"' . $tag . '"'
                ]
            ];
            $method = 'POST';
            $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/customer_saved_searches.json';
            if ($shopify_css_id != '' || $shopify_css_id != null) {
                $method = 'PUT';
                $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/customer_saved_searches/' . $shopify_css_id . '.json';
                $parameter['customer_saved_search']['id'] = $shopify_css_id;
            }
            $result = $user->api()->rest($method, $endPoint, $parameter);
            // logger(json_encode($result));
            $ret['msg'] = 'Error';
            if (!$result['errors']) {
                $savedSearch = $result['body']->container['customer_saved_search'];
                $ret['msg'] = 'success';
                $ret['id'] = $savedSearch['id'];
            }
            return $ret;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  createSavedSearch =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user
     * @param $user
     * @param $shopify_css_id
     * @return string|void
     * This method is used to remove saved search in shopify while delete membership plan
     */
    public function removeSavedSearch($user, $shopify_css_id)
    {
        try {
            logger('========= START:: removeSavedSearch =========');
            if ($shopify_css_id != '' || $shopify_css_id != null) {
                $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/customer_saved_searches/' . $shopify_css_id . '.json';
                $result = $user->api()->rest('DELETE', $endPoint);

                return (!$result['errors']) ? 'success' : 'Error';
            } else {
                return 'success';
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  removeSavedSearch =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user
     * @param $name
     * @param $tag
     * @param $shopify_css_id
     * @return array|void
     * This method is used to create or edit segment in shopify while create or edit membership plan
     */

    public function createSegment($user, $name, $tag, $shopify_css_id)
    {
        try {
            logger('========= START:: createSegment  ==> user :: ' . $user->id . ' =========');
            $name = substr('[Simplee] - ' . $name, 0, 40);
            $action = ($shopify_css_id != '' || $shopify_css_id != null) ? 'segmentUpdate' : 'segmentCreate';
            $query = 'mutation MyMutation {';
            $query .= ($action == 'segmentUpdate') ? 'segmentUpdate(id: "gid://shopify/Segment/' . $shopify_css_id . '"' : 'segmentCreate(';
            $query .= ' name: "' . $name . '", query: "customer_tags CONTAINS ';
            $query .= "'" . $tag . "'";
            $query .= '") {';
            $query .= 'segment {
                          id
                          name
                          query
                        }
                        userErrors {
                          field
                          message
                        }
                      }
                    }';
            $result = $this->graphQLRequest($user->id, $query);
            $message = $this->getReturnMessage($result, $action);
            $ret = [
                'msg' => 'Error',
                'id' => ''
            ];
            if ($message == 'success') {
                $segment = $result['body']['data'][$action]['segment'];
                $ret['msg'] = 'success';
                $ret['id'] = gidToShopifyId($segment['id']);
            } else {

                $ret['msg'] = ($shopify_css_id != '' || $shopify_css_id != null) ? $result['body']['data']['segmentUpdate']['userErrors'][0]['message'] : $result['body']['data']['segmentCreate']['userErrors'][0]['message'];
            }
            return $ret;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  createSegment =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $user
     * @param $user
     * @param $shopify_css_id
     * @return string|void
     * This method is used to remove segment in shopify while delete membership plan
     */
    public function removeSegment($user, $shopify_css_id)
    {
        try {
            logger('========= START:: removeSegment =========');
            if ($shopify_css_id != '' || $shopify_css_id != null) {
                $query = 'mutation MyMutation {
                          segmentDelete(id: "gid://shopify/Segment/' . $shopify_css_id . '") {
                            userErrors {
                              field
                              message
                            }
                          }
                        }';
                $result = $this->graphQLRequest($user->id, $query);
                $message = $this->getReturnMessage($result, 'segmentDelete');
                return ($message == 'success') ? 'success' : 'Error';
            } else {
                return 'success';
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  removeSegment =============");
            logger($e->getMessage());
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function customerPaymentMethodGetUpdateUrl($user_id, $customerPaymentMethodId)
    {
        try {
            $query = '
                mutation MyMutation {
                  customerPaymentMethodGetUpdateUrl(customerPaymentMethodId: "gid://shopify/CustomerPaymentMethod/' . $customerPaymentMethodId . '") {
                    userErrors {
                      code
                      field
                      message
                    }
                    updatePaymentMethodUrl
                  }
                }';
            $result = $this->graphQLRequest($user_id, $query);
            $msg = $this->getReturnMessage($result, 'customerPaymentMethodGetUpdateUrl');
            if ($msg == 'success') {
                $url = $result['body']->container['data']['customerPaymentMethodGetUpdateUrl']['updatePaymentMethodUrl'];
            } else {
                $url = '';
            }
            return $url;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  customerPaymentMethodGetUpdateUrl =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
    /**
     * @param $user
     * @param $feature
     * @return false|mixed|string
     */

    public function checkShopFeature($user, $feature)
    {
        try {
            $query = '{
                shop {
                    features {
                        ' . $feature . '
                    }
                }
           }';
            $result = $this->graphQLRequest($user->id, $query);
            $retMsg = $this->getReturnMessage($result, 'shop');
            if ($retMsg == 'success') {
                return $result['body']->container['data']['shop']['features'][$feature];
            } else {
                return $retMsg;
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  checkShopFeature =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createPortalsInDB($shop_id)
    {
        try {
            $db_portal = SsPortal::where('shop_id', $shop_id)->first();
            if (!$db_portal) {
                $db_portal = new SsPortal;
                $db_portal->shop_id = $shop_id;
                $db_portal->portal_liquid = getPortalLiquidH();
                $db_portal->portal_css = getPortalCssH();
                $db_portal->portal_js = getPoratlJsH();
                $db_portal->save();
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  createPortalsInDB =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function checkIsMigrationCSV($file, $type)
    {
        try {
            $row = 1;
            $res['isSuccess'] = true;
            $res['message'] = '';
            if (($handle = fopen($file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    if ($row == 1) {
                        if ($type == 'recurring') {
                            $header = ['gateway_customer_id', 'customer_shopify_id', 'customer_email', 'customer_firstname', 'customer_lastname', 'customer_lastname', 'next_billing_date', 'currency_code', 'subscription_status', 'billing_interval_type', 'billing_interval_count', 'delivery_interval_type', 'delivery_interval_count', 'min_cycles', 'max_cycles', 'order_count', 'shipping_firstname', 'shipping_lastname', 'shipping_address1', 'shipping_address2', 'shipping_city', 'shipping_state', 'shipping_countryCode', 'shipping_zip', 'shipping_price', 'line_item_qty', 'line_item_price', 'line_item_product_id', 'line_item_variant_id'];
                        }
                        for ($c = 0; $c < count($header); $c++) {
                            if (!in_array(trim($header[$c]), $data)) {
                                $res['isSuccess'] = false;
                                $res['message'] = 'Uploaded file is not valid migration CSV file.';
                                return $res;
                            }
                        }
                    }
                    $row++;
                }
            }
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  checkIsMigrationCSV =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function checkIsAppMigrationCSV($file)
    {
        try {
            $row = 1;
            $res['isSuccess'] = true;
            $res['message'] = '';
            if (($handle = fopen($file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    if ($row == 1) {
                        $header = ['firstname', 'lastname', 'email'];

                        for ($c = 0; $c < count($header); $c++) {

                            if (!in_array(trim($header[$c]), $data)) {
                                $res['isSuccess'] = false;
                                $res['errors'] = [
                                    "fileError" => 'Uploaded file is not valid migration CSV file.'
                                ];
                                $res['isFileValidationError'] = true;
                                return $res;
                            }
                        }
                    }
                    $row++;
                }
            }
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  checkIsAppMigrationCSV =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function isExistCustomer($user, $email)
    {
        try {
            $apiEndpoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers.json';
            $parameter['fields'] = 'id';
            $parameter['email'] = $email;
            $result = $user->api()->rest('GET', $apiEndpoint, $parameter);
            $id = '';
            if (!$result['errors']) {
                $customer = $result['body']->container['customers'];
                $id = (@$customer[0]['id']) ? $customer[0]['id'] : '';
            }
            return $id;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  isExistCustomer =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function setRequiedSellingPlan($user_id, $shopify_product_id, $isSellingRequire)
    {
        try {
            logger('============ START:: setRequiedSellingPlan  ==> User :: ' . $user_id . ' ===========');
            $query =  'mutation MyMutation {
                          productUpdate(input: {id: "gid://shopify/Product/' . $shopify_product_id . '", requiresSellingPlan: ' . $isSellingRequire . '}){
                            userErrors {
                              field
                              message
                            }
                          }
                        }';
            $result = $this->graphQLRequest($user_id, $query);
            // logger(json_encode($result));
            $msg = $this->getReturnMessage($result, 'productUpdate');
            return $msg;
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  setRequiedSellingPlan =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function fetchAllContracts($user, $limit, $page)
    {
        $after = ($page == '') ? '' : ', after: "' . $page . '"';
        $query = '{
                    subscriptionContracts(first: ' . $limit . $after . ') {
                        pageInfo {
                          hasNextPage
                        }
                        edges {
                          cursor
                          node {
                            id
                          }
                        }
                    }
                }  ';
        // $query = '{
        //     subscriptionContracts(first: '.$limit . $after.') {
        //         pageInfo {
        //           hasNextPage
        //         }
        //         edges {
        //           cursor
        //           node {
        //             id
        //             originOrder {
        //               legacyResourceId
        //             }
        //             billingPolicy {
        //               interval
        //               intervalCount
        //               maxCycles
        //               minCycles
        //             }
        //             currencyCode
        //             customer {
        //               legacyResourceId
        //             }
        //             deliveryPrice {
        //               amount
        //               currencyCode
        //             }
        //             deliveryPolicy {
        //               interval
        //               intervalCount
        //             }
        //             nextBillingDate
        //           }
        //         }
        //     }
        // }  ';
        $result = $this->graphQLRequest($user->id, $query);
        if (!$result['errors']) {
            return $result['body']['data'];
        } else {
            logger(json_encode($result));
            return [];
        }
    }

    public function createWebhookInDBIfMissing($contractData, $user, $shop)
    {
        try {
            $webhookJson = '
                {
                  "admin_graphql_api_id": "gid://shopify/SubscriptionContract/' . $contractData['sh_contract_id'] . '",
                  "id": ' . $contractData['sh_contract_id'] . ',
                  "billing_policy": {
                    "interval": "' . $contractData['billingPolicy']['interval'] . '",
                    "interval_count": ' . $contractData['billingPolicy']['intervalCount'] . ',
                    "min_cycles": "' . $contractData['billingPolicy']['minCycles'] . '",
                    "max_cycles": "' . $contractData['billingPolicy']['maxCycles'] . '"
                  },
                  "currency_code": "' . $contractData['currencyCode'] . '",
                  "customer_id": ' . $contractData['customer']['legacyResourceId'] . ',
                  "admin_graphql_api_customer_id": "gid://shopify/Customer/' . $contractData['customer']['legacyResourceId'] . '",
                  "delivery_policy": {
                    "interval": "' . $contractData['deliveryPolicy']['interval'] . '",
                    "interval_count": ' . $contractData['deliveryPolicy']['intervalCount'] . '
                  },
                  "status": "active",
                  "admin_graphql_api_origin_order_id": "gid://shopify/Order/' . $contractData['originOrder']['legacyResourceId'] . '",
                  "origin_order_id": ' . $contractData['originOrder']['legacyResourceId'] . '
                }';
            $db_webhook = new SsWebhook;
            $db_webhook->topic = 'subscription_contracts/create';
            $db_webhook->user_id = $user->id;
            $db_webhook->shop_id = $shop->id;
            $db_webhook->api_version = '2021-07';
            $db_webhook->body = $webhookJson;
            $db_webhook->status = 'new';
            $db_webhook->save();
            return $db_webhook->id;
        } catch (\Exception $e) {
            logger("============= ERROR ::  createWebhookInDBIfMissing =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createUpdateCustomer($row, $user_id)
    {
        try {
            $user = User::find($user_id);
            $customer_id = $this->isExistCustomer($user, $row['customer_email']);
            if ($customer_id != '') {
                $prevTags = [];
                $tagResult = $this->getShopifyData($user, $customer_id, 'customer', 'id,tags');
                if (!empty($tagResult)) {
                    $prevTags = explode(',', $tagResult['tags']);
                }
                if (@$row['customer_tag'] && @$row['customer_tag'] != '' && !in_array(@$row['customer_tag'], $prevTags)) {
                    array_push($prevTags, $row['customer_tag']);
                }
                array_filter($prevTags);
                $filteredTag = implode(',', $prevTags);
            } else {
                $filteredTag = (@$row['customer_tag'] && @$row['customer_tag'] != '') ? $row['customer_tag'] : '';
            }
            $query = 'mutation { ';
            $action = '';
            if ($customer_id == '') {
                $action = 'customerCreate';
                $query .= 'customerCreate(
                            input: {
                                firstName: "' . $row['customer_firstname'] . '",
                                lastName: "' . $row['customer_lastname'] . '"';
            } else {
                $action = 'customerUpdate';
                $query .= 'customerUpdate(
                            input: {
                            id:"gid://shopify/Customer/' . $customer_id . '",
                           ';
            }
            $query .= 'tags: "' . $filteredTag . '",
                       email: "' . $row['customer_email'] . '",
                    }){
                     customer {
                      id
                    }
                    userErrors {
                      field
                      message
                    }
                  }
              }';
            $result = $this->graphQLRequest($user->id, $query);

            $retMsg = $this->getReturnMessage($result, $action);
            $customerId = '';
            if ($retMsg == 'success') {
                $customerId = str_replace('gid://shopify/Customer/', '', $result['body']->container['data'][$action]['customer']['id']);
            }
            return $customerId;
        } catch (\Exception $e) {
            logger("============= ERROR ::  createUpdateCustomer =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createUpdateCustomerByMarchant($row, $user_id)
    {
        try {
            $user = User::find($user_id);
            $customer_id = $this->isExistCustomer($user, $row['customer_email']);
            if ($customer_id != '') {
                $prevTags = [];
                $tagResult = $this->getShopifyData($user, $customer_id, 'customer', 'id,tags');
                if (!empty($tagResult)) {
                    $prevTags = explode(',', $tagResult['tags']);
                }
                if (@$row['customer_tag'] && @$row['customer_tag'] != '' && !in_array(@$row['customer_tag'], $prevTags)) {
                    array_push($prevTags, $row['customer_tag']);
                }
                array_filter($prevTags);
                $filteredTag = implode(',', $prevTags);
            } else {
                $filteredTag = (@$row['customer_tag'] && @$row['customer_tag'] != '') ? $row['customer_tag'] : '';
            }
            $query = 'mutation { ';
            $action = '';
            if ($customer_id == '') {
                $action = 'customerCreate';
                $query .= 'customerCreate(
                            input: {
                               ';
            } else {
                $action = 'customerUpdate';
                $query .= 'customerUpdate(
                            input: {
                            id:"gid://shopify/Customer/' . $customer_id . '",
                           ';
            }
            $query .= 'firstName: "' . $row['customer_firstname'] . '",
                        lastName: "' . $row['customer_lastname'] . '",
                        tags: "' . $filteredTag . '",
                       email: "' . $row['customer_email'] . '",
                    }){
                     customer {
                      id
                    }
                    userErrors {
                      field
                      message
                    }
                  }
              }';
            $result = $this->graphQLRequest($user->id, $query);
            $retMsg = $this->getReturnMessage($result, $action);
            $customerId = '';
            if ($retMsg == 'success') {
                $customerId = str_replace('gid://shopify/Customer/', '', $result['body']->container['data'][$action]['customer']['id']);
            }
            return $customerId;
        } catch (\Exception $e) {
            logger("============= ERROR ::  createUpdateCustomerByMarchant =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createBillingAttemptAfterMigration($contractId, $user_id)
    {
        try {
            logger('========= START:: createBillingAttemptAfterMigration ==> User :: ' . $user_id . '=========');
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
                                order {
                                    id
                                }
                                ready
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
            $retMsg = $this->getReturnMessage($result, 'subscriptionBillingAttemptCreate');
            $res['isSuccess'] = false;
            if ($retMsg == 'success') {
                $res['isSuccess'] = true;
                $subscriptionBillingAttempt = $result['body']->container['data']['subscriptionBillingAttemptCreate']['subscriptionBillingAttempt'];
                $res['order_id'] = ($subscriptionBillingAttempt['order'] != null) ? ['order']['id'] : '';
            }
            logger(json_encode($result));
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  createBillingAttemptAfterMigration =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function sendAccountInvites($user, $shopify_customer_id, $from_email, $to_email)
    {
        try {
            logger('============ START:: sendAccountInvites ===========');
            $parameter = [
                'customer_invite' => ''
            ];
            $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/' . $shopify_customer_id . '/send_invite.json';
            $result = $user->api()->rest('POST', $endPoint, $parameter);
            if (!$result['errors']) {
                $savedSearch = $result['body']->container['customer_invite'];
                $ret['msg'] = 'success';
            } else {
                logger($result['body']);
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            logger("============= ERROR ::  sendAccountInvites =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getShop($user)
    {
        try {
            $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/shop.json';
            $parameter['fields'] = 'id';
            $result = $user->api()->rest('GET', $endPoint, $parameter);
            return $result['errors'];
        } catch (\Exception $e) {
            logger("============= ERROR ::  getShop =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function isFeatureExist($featureName, $user = null)
    {
        try {
            $result = Feature::where(['name' => $featureName, 'is_enabled' => true])->first();
            logger("================================FEatures1");
            if ($result) {
                logger($user->id);
                $featurable =  Featurables::where(['feature_id' => $result->id, 'featurable_id' => $user->id])->first();
                logger("================================FEatures2");
                logger($featurable);
                if ($featurable) {
                    return true;
                }
                return false;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  isFeatureExist =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function checkForActiveMemberTag($user, $shopify_customer_id, $customer_tag)
    {
        logger("============= START ::  checkForActiveMemberTag =============");
        try {
            $isExistTag = SsContract::where('user_id', $user->id)->where('shopify_customer_id', $shopify_customer_id)->where('tag_customer', $customer_tag)->whereIN('status', ['active', 'cancelled'])->where('next_processing_date', '>', Carbon::now())->count();
            if ($isExistTag == 0) {
                $isAccessRemoved = DB::table('ss_contracts')->where('shopify_customer_id', $shopify_customer_id)->where('user_id', $user->id)->where('tag_customer', $customer_tag)->where('status', 'cancelled')->where('status_display', '!=', 'Access Removed')->count();
                if ($isAccessRemoved == 0) {
                    logger("User :: " . $user->name . "   Customer :: " . $shopify_customer_id . "  Tag :: " . $customer_tag);
                    $this->updateShopifyTags(
                        $user,
                        $shopify_customer_id,
                        $customer_tag,
                        'customer',
                        'remove'
                    );
                }
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  checkForActiveMemberTag =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createLanguageInDb($shop_id)
    {
        try {
            logger("========= START:: createLanguageInDb =========");
            $portalLang = SsLanguage::where('shop_id', $shop_id)->first();
            if (!$portalLang) {
                $portalLang = new SsLanguage;
                $portalLang->shop_id = $shop_id;
                $portalLang->portal_action_cancel = 'Cancel next renewal';
                $portalLang->portal_popup_cancel_text = 'Your membership will be active until your next billing date';
                $portalLang->portal_billing_send = 'Update Billing';
                $portalLang->portal_status_display_lifetime = 'Lifetime Access';
                $portalLang->portal_status_display_expiring = 'Active - Expiring';
                $portalLang->portal_status_display_billing_failed = 'Billing Failed';
                $portalLang->portal_member_id = 'Member ID';
                $portalLang->save();
            } else {
                if (is_null($portalLang->portal_status_display_lifetime) || $portalLang->portal_status_display_lifetime == '') {
                    $portalLang->portal_status_display_lifetime = 'Lifetime Access';
                }
                if (is_null($portalLang->portal_status_display_expiring) || $portalLang->portal_status_display_expiring == '') {
                    $portalLang->portal_status_display_expiring = 'Active - Expiring';
                }
                if (is_null($portalLang->portal_status_display_billing_failed) || $portalLang->portal_status_display_billing_failed == '') {
                    $portalLang->portal_status_display_billing_failed = 'Billing Failed';
                }
                $portalLang->save();
            }
            return SsLanguage::where('shop_id', $shop_id)->first();
        } catch (\Exception $e) {
            logger("============= ERROR ::  createLanguageInDb =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function addCustomPlan($user_id, $plan_id)
    {
        try {
            $isExistCustomPlan = SsCustomPlan::where(['user_id' => $user_id, 'status' => 'active'])->first();
            if ($isExistCustomPlan) {
                $isExistCustomPlan->status = 'cancelled';
                $isExistCustomPlan->cancelled_at = date('Y-m-d H:i:s');
                $isExistCustomPlan->save();
            }
            $customPlan = new SsCustomPlan;
            $customPlan->user_id = $user_id;
            $customPlan->plan_id = $plan_id;
            $customPlan->status = 'active';
            $customPlan->save();
            return 'Custom plan added';
        } catch (\Exception $e) {
            logger("============= ERROR ::  addCustomPlan =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function addEmailTemplate($type, $shop)
    {
        try {
            $data = config("const.email_categories.$type");
            $db_email = new SsEmail;
            $db_email->shop_id = $shop->id;
            $db_email->category = $type;
            $db_email->description = '';
            $db_email->active = 1;
            $db_email->subject = $shop->name . $data['subject'];
            $db_email->plain_text = '';
            $db_email->html_body = $data['html_body'];
            $db_email->days_ahead = $data['days_ahead'];
            $db_email->save();
            return $db_email;
        } catch (\Exception $e) {
            logger("============= ERROR ::  addEmailTemplate =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function removeUserAllData($user_id)
    {
        try {
            logger("================== START:: remove data for user :: " . $user_id . " =====================");
            SsContract::where('user_id', $user_id)->delete();
            SsPlanGroup::where('user_id', $user_id)->delete();
            SsPlan::where('user_id', $user_id)->delete();
        } catch (\Exception $e) {
            logger("============= ERROR ::  removeUserAllData =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function flowTrigger($trigger_id, $trigger_url, $properties_string, $user)
    {
        try {
            $sf_query = '
                mutation
                {
                    flowTriggerReceive(body:"{\"trigger_id\": \"' . $trigger_id . '\",\"resources\": [{\"name\": \"\",\"url\": \"' . $trigger_url . '\"}],\"properties\": ' . $properties_string . '}") {
                        userErrors {field, message}
                    }
                }
            ';
            logger('Flow Query :: ' . $sf_query);
            $sf_result = $user->api()->graph($sf_query);
            logger('Flow Result :: ');
            logger(json_encode($sf_result));
        } catch (\Throwable $e) {
            logger("============= ERROR ::  flowTrigger =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    // Pass user instace of shop to make missing contracts of it. Which are not created in app but exist in shop.
    public function makeMissingContract(User $user, $cursor = null, array $createdContracts = [], $called = 1)
    {
        logger('================== Mannual :: Contract Create Started :: ' . $user->name . ' ================');
        $shop = Shop::where('user_id', $user->id)->select('id')->first();
        $query = '
            {
                subscriptionContracts(first: 100) {';
        if ($cursor) {
            $query = '
            {
                subscriptionContracts(first: 100 , after : "' . $cursor . '") {';
        }
        $query .= '
                edges {
                    cursor
                    node {
                        id
                        status
                        deliveryPolicy {
                            interval
                            intervalCount
                        }
                        billingPolicy {
                            interval
                            intervalCount
                            maxCycles
                            minCycles
                        }
                        customer {
                            id
                        }
                        originOrder {
                            id
                        }
                        currencyCode
                            nextBillingDate
                        }
                    }
                }
            }
        ';
        $result = $this->graphQLRequest($user->id, $query);
        if (!$result['errors']) {
            $result = $result['body']->container['data']['subscriptionContracts']['edges'];
            if (count($result) == 0) {
                return $createdContracts;
            }
            foreach ($result as $i => $queryContract) {
                $cursor = $queryContract['cursor'];
                $queryContract = $queryContract['node'];
                $queryContract['admin_graphql_api_id'] = $queryContract['id'];
                $queryContract['id'] = (int) str_replace('gid://shopify/SubscriptionContract/', '', $queryContract['admin_graphql_api_id']);
                $is_exist_db_contract = DB::table('ss_contracts')->where('shopify_contract_id', $queryContract['id'])->where('shop_id', $shop->id)->first();
                if ($is_exist_db_contract) {
                    // array_push($createdContracts,$queryContract);
                    // $createdContracts[] = $queryContract;
                } else {
                    // Making Payload Replica
                    $queryContract['customer_id'] = (int) str_replace('gid://shopify/Customer/', '', $queryContract['customer']['id']);
                    unset($queryContract['customer']);
                    $queryContract['origin_order_id'] = (int) str_replace('gid://shopify/Order/', '', $queryContract['originOrder']['id']);
                    unset($queryContract['originOrder']);
                    // Changig cases
                    $queryContract['status'] = strtolower($queryContract['status']);
                    $queryContract['delivery_policy']['interval'] = strtolower($queryContract['deliveryPolicy']['interval']);
                    $queryContract['delivery_policy']['interval_count'] = (int)($queryContract['deliveryPolicy']['intervalCount']);
                    $queryContract['billing_policy']['interval'] = strtolower($queryContract['billingPolicy']['interval']);
                    $queryContract['billing_policy']['interval_count'] = (int)($queryContract['billingPolicy']['intervalCount']);
                    $queryContract['billing_policy']['max_cycles'] = (int)($queryContract['billingPolicy']['maxCycles']);
                    $queryContract['billing_policy']['min_cycles'] = (int)($queryContract['billingPolicy']['minCycles']);
                    $queryContract['currency_code'] = ($queryContract['currencyCode']);
                    unset($queryContract['billingPolicy']);
                    unset($queryContract['deliveryPolicy']);
                    $payloadJson = json_encode($queryContract);
                    $webhookId = $this->webhook('subscription_contracts/create', $user->id, $payloadJson);
                    logger('================== Mannual :: Contract Create Dispatched ================');
                    event(new CheckSubscriptionContract($webhookId, $user->id, $shop->id, $payloadJson));
                    $createdContracts[] = $queryContract['id'];
                }
                if (count($result) == $i + 1) {
                    logger('=========== Recalled :: makeMissingContract ' . $called . ' ==========');
                    $called = $called + 1;
                    return $this->makeMissingContract($user, $cursor, $createdContracts, $called);
                }
            }
        } else {
        }
        return $createdContracts;
    }

    // Pass user instace of and contract id Which are not created in app but exist in shop.
    public function makeMissingContractRecord(User $user, $contractId)
    {
        logger('================== Mannual :: Contract Create Started :: ' . $user->name . ' ================');
        $shop = Shop::where('user_id', $user->id)->select('id')->first();
        $query = '
            {
                subscriptionContract(id: "gid://shopify/SubscriptionContract/' . $contractId . '") {
                    id
                    status
                    deliveryPolicy {
                        interval
                        intervalCount
                    }
                    billingPolicy {
                        interval
                        intervalCount
                        maxCycles
                        minCycles
                    }
                    customer {
                        id
                    }
                    originOrder {
                        id
                    }
                    currencyCode
                    nextBillingDate
                }
            }
        ';
        $result = $this->graphQLRequest($user->id, $query);
        if (!$result['errors']) {
            $result = $result['body']->container['data']['subscriptionContract'];
            $queryContract = $result;
            $queryContract['admin_graphql_api_id'] = $queryContract['id'];
            $queryContract['id'] = (int) str_replace('gid://shopify/SubscriptionContract/', '', $queryContract['admin_graphql_api_id']);
            $is_exist_db_contract = DB::table('ss_contracts')->where('shopify_contract_id', $queryContract['id'])->where('shop_id', $shop->id)->first();
            if (!$is_exist_db_contract) {
                $queryContract['customer_id'] = (int) str_replace('gid://shopify/Customer/', '', $queryContract['customer']['id']);
                unset($queryContract['customer']);
                $queryContract['origin_order_id'] = (int) str_replace('gid://shopify/Order/', '', $queryContract['originOrder']['id']);
                unset($queryContract['originOrder']);
                // Changig cases
                $queryContract['status'] = strtolower($queryContract['status']);
                $queryContract['delivery_policy']['interval'] = strtolower($queryContract['deliveryPolicy']['interval']);
                $queryContract['delivery_policy']['interval_count'] = (int)($queryContract['deliveryPolicy']['intervalCount']);
                $queryContract['billing_policy']['interval'] = strtolower($queryContract['billingPolicy']['interval']);
                $queryContract['billing_policy']['interval_count'] = (int)($queryContract['billingPolicy']['intervalCount']);
                $queryContract['billing_policy']['max_cycles'] = (int)($queryContract['billingPolicy']['maxCycles']);
                $queryContract['billing_policy']['min_cycles'] = (int)($queryContract['billingPolicy']['minCycles']);
                $queryContract['currency_code'] = ($queryContract['currencyCode']);
                unset($queryContract['billingPolicy']);
                unset($queryContract['deliveryPolicy']);
                $payloadJson = json_encode($queryContract);
                $webhookId = $this->webhook('subscription_contracts/create', $user->id, $payloadJson);
                logger('================== Mannual :: Contract Create Dispatched ================');
                event(new CheckSubscriptionContract($webhookId, $user->id, $shop->id, $payloadJson));
                $createdContract = $queryContract['id'];
            }
        } else {
        }
        logger('================== Mannual :: Contract Create End================');
        return $createdContract;
    }

    // Make webhooks of the app to a shop by passing shops User instance
    public function makeWebhooksFromConfig(User $user)
    {
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
        logger('============= Start :: Mannual webhooks register ' . $user->name . ' ==============');
        $webhooks = $user->api()->rest('GET', 'admin/webhooks.json');
        if ($webhooks['errors']) {
            return false;
            logger('============= Error :: Mannual webhooks register ==============');
            logger($webhooks);
        }
        $webhooks = $webhooks['body']->container['webhooks'];
        if (count($webhooks) == 17) {
            logger('============= Start :: All webhooks exist ==============');
            return $webhooks;
        }
        // logger(count($webhooks));
        $exists = function (array $webhook, $webhooks): bool {
            if (count($webhooks) == 0) {
                return false;
            }
            foreach ($webhooks as $shopWebhook) {
                if ($shopWebhook['address'] === $webhook['address'] && $shopWebhook['topic'] === $webhook['topic']) {
                    // Found the webhook in our list
                    return true;
                }
            }
            return false;
        };
        $createWebhookRes = [];
        foreach (Util::getShopifyConfig('webhooks') as $configWebhook) {
            // Check if the required webhook exists on the shop
            if (!$exists($configWebhook, $webhooks)) {
                logger('Will Create');
                $createWebhook =  [
                    'topic' => $configWebhook['topic'],
                    'webhookSubscription' => [
                        'arn' =>    env('AWS_ARN_WEBHOOK_ADDRESS'),
                        'format' => 'JSON',
                    ],
                ];
                $res = $user->api()->graph($query, $createWebhook);
                if ($res['errors']) {
                    logger(json_encode($configWebhook));
                }
                // if (!$createWebhook['errors']) {
                //     $createWebhook = $createWebhook['body']->container['webhook'];
                //     $createWebhookRes[] = [$createWebhook['id'], $createWebhook['topic']];
                // } else {
                //     logger(json_encode($createWebhook));
                // }
            } else {
                logger('Will Not Create');
            }
        }
        return $createWebhookRes;
    }

    // Shops which not registers their webhooks yet.
    public function getMissingShops()
    {
        $users = User::where('active', 1)->where('is_working', 1)->where('plan_id', '!=', null)->get();
        $nonWebhooks = [];
        foreach ($users as $user) {
            $webhooks = $user->api()->rest('GET', 'admin/webhooks.json');
            if (!$webhooks['errors']) {
                $count = count($webhooks['body']->container['webhooks']);
                if ($count == 0) {
                    $nonWebhooks[] = $user->name;
                }
            } else {
            }
        }
        return ($nonWebhooks);
    }

    public function makeScriptTagsFromConfig($user)
    {
        logger('============= Start :: Mannual scriiptTag register ' . $user->name . ' ==============');
        $checkIfExist = function ($existingScript) {
            foreach (config('shopify-app.scripttags') as $configScript) {
                if ($existingScript['src'] == $configScript['src']) {
                    return true;
                }
            }
            return false;
        };
        $scripts = $user->api()->rest('GET', '/admin/script_tags.json');
        $addedScripts = [];
        if (!$scripts['errors']) {
            $apiHelper = $user->apiHelper();
            $scripts = $scripts['body']->container['script_tags'];
            foreach ($scripts as $script) {
                if (!$checkIfExist($script)) {
                    // logger('Making ScriptTag');
                    // Make scripttag
                    $newScript = $apiHelper->createScriptTag(config('shopify-app.scripttags'));
                    $addedScripts[] = $newScript;
                } else {
                    $addedScripts[] = $script;
                    logger('Script Tags Exist');
                }
            }
        } else {
            logger('============= Error :: Mannual scriiptTag register ' . $user->name . ' ==============');
        }
        logger('============= END :: Mannual scriiptTag register ' . $user->name . ' ==============');
        return $addedScripts;
    }

    public function checkIsPriceUpdateCSV($file)
    {
        try {
            $row = 1;
            $res['isSuccess'] = true;
            $res['message'] = '';
            if (($handle = fopen($file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    if ($row == 1) {
                        $header = ['contract_id', 'price'];
                        for ($c = 0; $c < count($header); $c++) {
                            if (!in_array(trim($header[$c]), $data)) {
                                $res['isSuccess'] = false;
                                $res['message'] = 'Uploaded file is not valid migration CSV file.';
                                return $res;
                            }
                        }
                    }
                    $row++;
                }
            }
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  checkIsPriceUpdateCSV =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateImportPriceForSC($data, $user, $sessionKey, $sessionKey2)
    {
        try {
            logger("====================== START:: updatePriceForSC ======================");
            $shop = Shop::where('user_id', $user->id)->first();
            $ssContract = SsContract::where('shop_id', $shop->id)->where('shopify_contract_id', $data['contract_id'])->first();
            if (!$ssContract) {
                ($sessionKey != '') ? $this->setSession($data['contract_id'], $sessionKey) : '';
                logger($data['contract_id'] . ' Contract id Not updated');
            } else {
                $ssContract = SsContract::where('shop_id', $shop->id)->where('shopify_contract_id', $data['contract_id'])->first();
                $ssContract->pricing_adjustment_value  = $data['price'];
                // logger("************************************************* PRicing*");
                // logger($ssContract->pricing_adjustment_value);
                $ssContract->Save();
                $lineItems = SsContractLineItem::where('user_id', $user->id)->where('ss_contract_id', $ssContract->id)->get();
                foreach ($lineItems as $key => $lineItem) {
                    $this->subscriptionContractPriceUpdate($user->id, $lineItem, $ssContract);
                }
                ($sessionKey2 != '') ? $this->setSession($data['contract_id'], $sessionKey2) : '';
                // logger($data['contract_id'] . ' Contract Id updated');
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  updateImportPriceForSC =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function setSession($msg, $sessionKey)
    {
        $data = Session::get($sessionKey);
        array_push($data, $msg);
        session([$sessionKey =>  $data]);
        \Log::info(Session::get($sessionKey));
    }

    public function addTrasaction($shop_id, $user_id, $ss_customer_id, $contract_id, $credit_debit, $amount)
    {
        logger("==============================START :: STORE CREDIT Transaction =============================");
        DB::table('transaction')->insert([
            'shop_id' =>  $shop_id,
            'user_id' =>  $user_id,
            'customer_id' =>  $ss_customer_id,
            'contract_id' =>  $contract_id,
            'credit_debit' =>  $credit_debit,
            'amount' =>  $amount
        ]);
        logger("==============================END :: STORE CREDIT Transaction =============================");
    }

    public function createstoreCredit($user_id, $shopify_customer_id, $amount, $currency)
    {
        logger("==============================START :: STORE CREDIT Mutation =============================");
        $parameters = [];
        $version = 'unstable';
        $trans_Acc_id = 0;
        $id = "gid://shopify/Customer/$shopify_customer_id";
        $query = 'mutation Mymutation{
        storeCreditAccountCredit(
            id: "' . $id . '",
            creditInput : {
            creditAmount : {
                amount : ' . $amount . '
                currencyCode : ' . $currency . '
                }
            }
            ){
            storeCreditAccountTransaction {
                account {
                    id
                }
            }
            userErrors {
            message
            field
            }
        }
        }';
        $user = User::where('id', $user_id)->first();
        if($user){
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $user->password,
                'Content-Type' => 'application/json',
            ])->post("https://{$user->name}/admin/api/{$version}/graphql.json", [
                'query' => $query,
            ]);
            $result = $response->json();
            if (isset($result['data'])) {
                $storecreditaccounttransaction =  isset($result['data']['storeCreditAccountCredit']['storeCreditAccountTransaction']['account']['id'])  ? $result['data']['storeCreditAccountCredit']['storeCreditAccountTransaction']['account']['id'] : '';
                $trans_Acc_id_exist = explode("/", $storecreditaccounttransaction);
                $trans_Acc_id = isset($trans_Acc_id_exist[4]) ? $trans_Acc_id_exist[4] : 0;
            }
        }
        logger("==============================END :: STORE CREDIT Mutation =============================");
        return $trans_Acc_id;
    }

    public function setScriptTag($user)
    {
        $scripts = $user->api()->rest('GET', '/admin/script_tags.json');
        $existConfig = config('shopify-app.scripttags');
        if (!$scripts['errors']) {
            $scripts = $scripts['body']->container['script_tags'];
            $exist = array_column($scripts, 'src');
            foreach ($existConfig as $config) {
                if (!in_array($config['src'], $exist)) {
                    $user->api()->rest('POST', '/admin/script_tags.json', ['script_tag' => $config]);
                }
            }
        }
    }


    public function is_membership_expired($user)
    {
        $getPlan = Plan::where('id', $user->plan_id)->first();
        if (!$getPlan->is_free_trial_plans) {
            return false;
        } else {
            $shop = Shop::where('user_id', $user->id)->first();
            $contractCount = SsContract::where('user_id', $user->id)->count();
            $setting = SsSetting::where('shop_id', $shop->id)->first();
            if ($setting->free_memberships > $contractCount) {
                return  false;
            } else {
                return true;
            }
        }
    }

    public function membershipexpireMetaUpdate($user, $is_membership_expired)
    {
        logger("=========================    START MEMBERSHIPEXPIREMETAFIELDUPDATE ================================= ");
        $parameter = [
            "metafield" => [
                'namespace' => 'simplee',
                'key' => 'is_membership_expired',
                'value' => $is_membership_expired,
                'type' => 'boolean'
            ]
        ];
        $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json', $parameter);
        logger("=========================    END MEMBERSHIPEXPIREMETAFIELDUPDATE ================================= ");
    }

    public function getShopdata(){

        $shop = getShopH();
        $user = Auth::user();
        $ConCount = SsContract::where('user_id', $user->id)->count();
        $MemCount = SsSetting::where('shop_id', $shop->id)->first();

        $contractCount = isset($ConCount) ? $ConCount : 0;
        $memberCount = isset($MemCount->free_memberships) ? $MemCount->free_memberships : 0;
        $planType = true ;
        if ($user->plan_id) {
            $getPlan = Plan::where('id', $user->plan_id)->first();
            if ($getPlan && $getPlan->is_free_trial_plans) {
                $planType = false ;
                if ($memberCount > $contractCount) {
                    $is_membership_expired =  false;
                    $freeMem = true;
                } else {
                    $is_membership_expired =  true;
                    $freeMem = true;
                }
            } else {
                $is_membership_expired = false;
                $freeMem = false;
            }
        } else {
            $is_membership_expired = false;
            $freeMem = true;
        }

        $shopCall = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/shop.json';
        $getShopJson = $user->api()->rest('GET', $shopCall);
        if(isset($getShopJson['body']['shop']->plan_name) && $getShopJson['body']['shop']->plan_name == 'partner_test'){
            $planType = true ;
        }

        $response['isPosEnable'] = $this->getIsPosEnabled($shop->user_id);
        $response['currency'] = $shop->currency_symbol;
        $response['name'] = $shop->myshopify_domain;
        $response['storecredit'] = false;
        $response['is_membership_expired'] = $is_membership_expired;
        $response['freeMem'] = $freeMem;
        $response['contractCount'] = $contractCount;
        $response['memberCount'] = $memberCount;
        $response['planType'] = $planType;
        if ($this->isFeatureExist('store-credit', Auth::user())) {
            $response['storecredit'] = true;
        }
        return $response;
    }

    public function updateOrderTag($user,$orderId,$tag_order){
        $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/orders/' . $orderId . '.json';
        $parameter = [
            'order' => [
                'id' => $orderId,
                'tags' => $tag_order,
            ],
        ];
        $result = $this->rest($user, $endPoint, $parameter, 'PUT');
        if($result['status'] == 429){
            sleep(10);
            $this->updateOrderTag($user,$orderId,$tag_order);
        }
        return $result;
    }
}
