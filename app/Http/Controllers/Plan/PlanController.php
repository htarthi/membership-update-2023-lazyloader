<?php

namespace App\Http\Controllers\Plan;

use App\Exports\PlansExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlanRequest;
use App\Models\Code;
use App\Models\Feature;
use App\Models\Install;
use App\Models\Shop;
use App\Models\SsEvents;
use App\Models\SsForm;
use App\Models\SsPlan;
use App\Models\SsContract;
use App\Models\SsPlanGroup;
use App\Models\SsPlanGroupVariant;
use App\Models\SsPosDiscounts;
use App\Models\SsRule;
use App\Models\SsShippingProfile;
use App\Models\SsStoreCreditRules;
// use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
// use LaravelFeature\Facade\Feature;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;
use Osiset\ShopifyApp\Storage\Models\Charge;
use Osiset\ShopifyApp\Storage\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\SsSetting;
use App\Models\AutomaticDiscount;
use App\Jobs\AutomaticAppDiscountJob;
use App\Jobs\AutomaticShippingDiscount;
use App\Models\ShippingDiscount;

class PlanController extends Controller
{
    use ShopifyTrait;
    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function appPlanIndex(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                $user = User::where('id', $id)->first();
                Auth::login($user);
            }

            $plans = Plan::select('name', 'price', 'transaction_fee')->get()->toArray();

            $plan['active_plan_id'] = $user->plan_id;
            $plan['data'] = $plans;
            $plan['shop']['name'] = $user->name;
            //            $plan['advance']['txn_fee'] = number_format($plan2->transaction_fee * 100, 2);
            return view('plans.plan', compact('plan', 'user'));
        } catch (\Exception $e) {
            logger("============= ERROR ::  appPlanIndex =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getPlan($productId)
    {
        try {
            $user = Auth::user();
            $shop = Shop::where('user_id', $user->id)->first();

            $mainData = [];
            $planGroups = SsPlanGroup::with(
                'hasManyPlan',
                'hasManyVariants',
                'hasManyRules',
                'hasManyForms',
                'hasManyPosDiscounts',
                'hasManyPlan.hasManyPosDiscounts',
                'hasManyCreditRules'
            )
                ->whereHas('hasManyVariants', function ($query) use ($productId) {
                    $query->where("shopify_product_id", $productId);
                })
                ->select(
                    'id',
                    'name',
                    'options',
                    'tag_customer',
                    'tag_order',
                    'discount_code',
                    'discount_code_members',
                    'is_display_on_cart_page',
                    'is_display_on_member_login',
                    'discount_type',
                    'activate_product_discount',
                    'activate_shipping_discount',
                    'shipping_discount_code',
                    'active_shipping_dic',
                    'shipping_discount_message',
                )
                ->get();

            foreach ($planGroups as $planG) {
                $mainData['plan_groups'][] = $this->preparePlanGroupData($planG, $user, $shop);
            }

            $mainData['storeData'] = $this->getStoreData($user);
            $mainData['shop']['isPosEnable'] = $this->getIsPosEnabled($shop->user_id);
            $mainData['shop']['currency'] = $shop->currency_symbol;
            $mainData['shop']['name'] = $shop->myshopify_domain;
            $mainData['shop']['storecredit'] = false;
            if ($this->isFeatureExist('store-credit', Auth::user())) {
                $mainData['shop']['storecredit'] = true;
            }
            return $mainData;
        } catch (\Throwable $e) {
            logger("============= ERROR ::  getPlan =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function preparePlanGroupData($planG, $user, $shop)
    {
        $plans = $planG->hasManyPlan;

        $rules = $planG->hasManyRules;
        $forms = $planG->hasManyForms;
        $product = $planG->hasManyVariants;
        $discounts = $planG->hasManyPosDiscounts;
        $creditRules = $planG->hasManyCreditRules;

        // $discounts = $plans[0]->hasManyPosDiscounts;

        $data['rules'] = [];
        $data['formFields'] = [];
        $data['discounts'] = [];
        $data['creditRules'] = [];
        foreach ($plans as $key => $value) {
            $data['membershipLength'][$key] = [
                'id' => $value->id,
                'billing_interval' => $value->billing_interval,
                'billing_interval_count' => $value->billing_interval_count,
                'pricing_adjustment_type' => $value->pricing_adjustment_type,
                'pricing_adjustment_value' => $value->pricing_adjustment_value,
                'options' => explode(',', $value->options),
                'name' => $value->name,
                'billing_min_cycles' => $value->billing_min_cycles,
                'billing_max_cycles' => $value->billing_max_cycles,
                'is_set_min' => $value->is_set_min,
                'is_set_max' => $value->is_set_max,
                'trial_available' => $value->trial_available,
                'trial_days' => $value->trial_days,
                'pricing2_adjustment_type' => $value->pricing2_adjustment_type,
                'pricing2_adjustment_value' => $value->pricing2_adjustment_value,
                'pricing2_after_cycle' => $value->pricing2_after_cycle,
                'description' => $value->description,
                'is_advance_option' => $value->is_advance_option,
                'is_onetime_payment' => $value->is_onetime_payment,
                'trial_type' => ($value->pricing2_after_cycle && !$value->trial_days) ? 'orders' : 'days',
                'store_credit' => isset($value->store_credit) ? $value->store_credit : false,
                'store_credit_amount' => $value->store_credit_amount,
                'store_credit_frequency' => isset($value->store_credit_frequency) ? $value->store_credit_frequency : '',
            ];
        }
        foreach ($rules as $key => $value) {
            $data['rules'][$key] = [
                'id' => $value->id,
                'rule_type' => $value->rule_type,
                'rule_name' => $value->rule_name,
                'rule_attribute1' => $value->rule_attribute1,
                'rule_attribute1_handle' => $value->rule_attribute1_handle,
                'rule_attribute2' => json_decode($value->rule_attribute2),
            ];
        }
        foreach ($creditRules as $key => $value) {
            $data['creditRules'][$key] = [
                'id' => $value->id,
                'shop_id' => $value->shop_id,
                'ss_plan_group_id' => $value->ss_plan_group_id,
                'trigger' => $value->trigger,
                'value_type' => $value->value_type,
                'value_amount' => $value->value_amount,
            ];
        }
        foreach ($forms as $key => $value) {
            $data['formFields'][$key] = [
                'id' => $value->id,
                'field_label' => $value->field_label,
                'field_type' => $value->field_type,
                'field_options' => $value->field_options,
                'field_required' => $value->field_required,
                'field_displayed' => json_decode($value->field_displayed),
            ];
        }
        foreach ($product as $key => $value) {
            $data['product'] = [
                'id' => $value->shopify_product_id,
                'name' => $value->product_title,
            ];
        }
        foreach ($discounts as $key => $value) {
            $data['discounts'][$key] = [
                'id' => $value->id,
                'discount_name' => $value->discount_name,
                'discount_amount' => number_format($value->discount_amount, 2),
                'discount_amount_type' => $value->discount_amount_type,
            ];
            // $data['discounts'][$key] = [
            //     'id' => $value->id,
            //     'discount_name' => $value->discount_name,
            //     'discount_amount' => ($value->discount_amount_type == '%') ? number_format(($value->discount_amount * 100), 2): number_format($value->discount_amount, 0),
            //     'discount_amount_type' => $value->discount_amount_type,
            // ];
        }
        $data['id'] = $planG->id;
        $data['name'] = $planG->name;
        $data['options'] = explode(',', $planG->options);
        $data['tag_customer'] = $planG->tag_customer;
        $data['tag_order'] = $planG->tag_order;
        $data['discount_code'] = $planG->discount_code;
        $data['discount_code_members'] = $planG->discount_code_members;
        $data['is_display_on_cart_page'] = $planG->is_display_on_cart_page;
        $data['is_display_on_member_login'] = $planG->is_display_on_member_login;
        $data['deleted']['membershipLength'] = [];
        $data['deleted']['rules'] = [];
        $data['deleted']['formFields'] = [];
        $data['deleted']['discounts'] = [];
        $data['deleted']['creditRules'] = [];
        $data['shop']['currency'] = $shop->currency_symbol;

        $data['contract_count'] = SsContract::where('user_id', $user->id)->where('shop_id', $shop->id)->where('ss_plan_groups_id', $planG->id)->count();

        return $data;
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function appPlanChange($plan, $name)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                $user = User::where('name', $name)->orderBy('created_at', 'desc')->first();
            }

            $db_plan = Plan::where('id', $plan)->first();

            $db_charge = Charge::where('user_id', $user->id)->withTrashed()->orderBy('created_at', 'desc')->first();

            if ($db_charge) {
                $curr_date = date('Y-m-d H:i:s');
                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $db_charge->trial_ends_on);
                $from = $curr_date;

                $trial_days = ($to > $from) ? $to->diffInDays($from) + 1 : 0;
            } else {
                $trial_days = $db_plan->trial_days;
            }

            $shop = Shop::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
            $is_test = ($shop->shopify_plan == 'partner_test' || $shop->shopify_plan == 'affiliate') ? 1 : $db_plan->test;
            $parameter = [
                'recurring_application_charge' => [
                    "name" => $db_plan->name,
                    "trial_days" => $trial_days,
                    "price" => $db_plan->price,
                    "return_url" => env('APP_URL') . '/change-plan-db/' . $user->id,
                    "capped_amount" => $db_plan->capped_amount,
                    "terms" => $db_plan->terms,
                    "test" => $is_test,
                ]
            ];

            // dump($parameter);

            $result = $user->api()->rest('POST', '/admin/api/recurring_application_charges.json', $parameter);
            // dd($result);

            //            $query = 'mutation{
            //                 appSubscriptionCreate(
            //                        name: "'. $db_plan->name .'"
            //                        returnUrl: "'.env('APP_URL').'/change-plan-db/'. $user->id . '"
            //                        test: true
            //                        trialDays: '.$trial_days.'
            //                        lineItems: [
            //                            {
            //                                plan: {
            //                                    appRecurringPricingDetails: {
            //                                        price: { amount: '.$db_plan->price.', currencyCode: USD },
            //                                        interval: '.$db_plan->interval.'
            //                                    },
            //                                     appUsagePricingDetails: {
            //                                        cappedAmount: {amount: '.$db_plan->capped_amount.', currencyCode: USD},
            //                                        terms: "'.$db_plan->terms.'"
            //                                    }
            //                                }
            //                            }
            //                        ]
            //                    ) {
            //                        appSubscription {
            //                            id
            //                        }
            //                        confirmationUrl
            //                        userErrors {
            //                            field
            //                            message
            //                        }
            //                    }
            //           }';

            //            $parameters = [];

            // Create options for the API
            //            $options = new Options();
            //            $options->setVersion('2020-07');

            // Create the client and session
            //            $api = new BasicShopifyAPI($options);
            //            $api->setSession(new Session(
            //                $user->name, $user->password));

            // Now run your requests...
            //            $result = $api->graph($query, $parameters);

            if (!$result['errors']) {
                $data = $result['body']->container['recurring_application_charge'];
                return response()->json(['data' => $data], 200);
            } else {
                logger("============= appPlanChange ::  recurring_application_charge error =============");
                logger(json_encode($result));
                Bugsnag::notifyException($result);
                return response()->json(['data' => []], 422);
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  appPlanChange =============");
            logger($e);
            Bugsnag::notifyException($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function appFreePlan($action = 'web', $userId = '')
    {
        try {
            $user = Auth::user();

            if (!$user && $userId != '') {
                $user = User::find($userId);
            }
            if ($user) {
                $old_charge = Charge::where('user_id', $user->id)->where('status', 'ACTIVE')->orderBy('created_at', 'desc')->first();

                if ($old_charge) {
                    //                    delete old charge
                    $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/recurring_application_charges/' . $old_charge->charge_id . '.json';
                    $result = $user->api()->rest('DELETE', $endPoint);
                }

                //                    create new charge
                $plan = Plan::where('id', 1)->first();
                $chargeJson = [
                    "application_charge" => [
                        "name" => $plan->name,
                        "price" => $plan->price,
                        "return_url" => env('APP_URL'),
                        "capped_amount" => $plan->capped_amount,
                        "terms" => $plan->terms,
                        "test" => $plan->test,
                    ]
                ];
                $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/application_charges.json';
                $result = $user->api()->rest('POST', $endPoint, $chargeJson);

                if (!$result['errors']) {
                    $charge_data = $result['body']->container['application_charge'];

                    //                        cancel old charge if exist
                    if ($old_charge) {
                        $old_charge->status = 'CANCELLED';
                        $old_charge->cancelled_on = date('Y-m-d H:i:s');
                        $old_charge->save();
                    }

                    //                        create new charge

                    $charge = new Charge;
                    $charge->charge_id = $charge_data['id'];
                    $charge->test = $charge_data['test'];
                    $charge->status = 'ACTIVE';
                    $charge->terms = $plan['terms'];
                    $charge->name = $charge_data['name'];
                    $charge->type = $plan['type'];
                    $charge->price = $charge_data['price'];
                    $charge->trial_days = 0;
                    $charge->capped_amount = $plan['capped_amount'];
                    $charge->interval = $plan['interval'];
                    $charge->billing_on = date("Y-m-d H:i:s", strtotime($charge_data['created_at']));
                    $charge->activated_on = date("Y-m-d H:i:s", strtotime($charge_data['created_at']));
                    $charge->trial_ends_on = date("Y-m-d H:i:s");
                    $charge->created_at = date("Y-m-d H:i:s", strtotime($charge_data['created_at']));
                    $charge->updated_at = date("Y-m-d H:i:s", strtotime($charge_data['updated_at']));
                    $charge->plan_id = 1;
                    $charge->user_id = $user->id;
                    $charge->save();

                    //                        update plan id in user table
                    $user->plan_id = 1;
                    $user->save();
                    Auth::login($user);

                    return ($action == 'web') ? redirect()->route('home') : response()->json(['data' => 'Free plan activated'], 200);
                } else {
                    return response()->json(['data' => 'Charge not created'], 422);
                }
            }
            return response()->json(['data' => 'Something went wrong'], 422);
        } catch (\Exception $e) {
            logger("============= ERROR ::  appFreePlan =============");
            logger($e);
            Bugsnag::notifyException($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePlanDB(Request $request, $id)
    {
        try {
            $shop = User::find($id);
            $old_charge = Charge::where('status', 'ACTIVE')->where('user_id', $shop->id)->first();
            if ($old_charge) {
                $old_charge->status = 'CANCELLED';
                $old_charge->cancelled_on = date('Y-m-d H:i:s');
                $old_charge->save();
            }
            $response = $shop->api()->rest("GET", '/admin/api/' . env('SHOPIFY_API_VERSION') . '/recurring_application_charges/' . $request->charge_id);

            if (!$response['errors']) {
                $charge_data = $response['body']->container['recurring_application_charge'];

                $plan = Plan::where('name', $charge_data['name'])->first();
                $charge = new Charge;
                $charge->charge_id = $charge_data['id'];
                $charge->test = $charge_data['test'];
                $charge->status = strtoupper($charge_data['status']);
                $charge->name = $charge_data['name'];
                $charge->terms = $plan->terms;
                $charge->interval = $plan->interval;
                $charge->capped_amount = $charge_data['capped_amount'];
                $charge->type = 'RECURRING';
                $charge->price = $charge_data['price'];
                $charge->trial_days = $charge_data['trial_days'];
                $charge->billing_on = date("Y-m-d H:i:s", strtotime($charge_data['billing_on']));
                $charge->activated_on = date("Y-m-d H:i:s", strtotime($charge_data['activated_on']));
                $charge->trial_ends_on = date("Y-m-d H:i:s", strtotime($charge_data['trial_ends_on']));
                $charge->created_at = date("Y-m-d H:i:s", strtotime($charge_data['created_at']));
                $charge->updated_at = date("Y-m-d H:i:s", strtotime($charge_data['updated_at']));
                $charge->plan_id = $plan->id;
                $charge->user_id = $shop->id;
                $charge->save();

                $shop->plan_id = $plan->id;

                $db_shop = Shop::where('user_id', $shop->id)->orderBy('created_at', 'desc')->first();

                if (!$db_shop->member_count_update_at) {
                    $db_shop->member_count_update_at = $charge->activated_on;
                    $db_shop->save();
                }

                $shop->save();
            }
            Auth::login($shop);
            return redirect()->route('home');
        } catch (\Exception $e) {
            logger("============= ERROR ::  changePlanDB =============");
            logger($e);
            Bugsnag::notifyException($e);
            return response()->json(['data' => $e], 422);
        }
    }
    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addEvent(Request $request)
    {
        try {
            // $shop = getShopH();
            $user = User::where('id',$request->user_id)->first();

            $res = $this->event($user->id, $request->category, '', $request->description);
            if ($res) {
                return response()->json(['data' => 'Event Added!!'], 200);
            } else {
                return response()->json(['data' => $res], 422);
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  addEvent =============");
            logger($e);
            Bugsnag::notifyException($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function planGroupIndex()
    {

        try {
            $shop = getShopH();
            $user = Auth::user();
            // Cache::forget($shop->id);

            $ConCount = SsContract::where('user_id', $user->id)->count();
            $MemCount = SsSetting::where('shop_id', $shop->id)->first();

            $contractCount = isset($ConCount) ? $ConCount : 0;
            $memberCount = isset($MemCount->free_memberships) ? $MemCount->free_memberships : 0;

            // Cache::forget($shop->id);
            $planType = true ;
            if ($user->plan_id) {
                $getPlan = Plan::where('id', $user->plan_id)->first();
                if ($getPlan && $getPlan->is_free_trial_plans) {
                    $planType = false;
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

            if (Cache::has($shop->id)) {
                // logger(" ================================>>>  Data load from cache ");
                $data = Cache::get($shop->id);

                $data['shop']['currency'] = $shop->currency_symbol;
                $data['shop']['storecredit'] = false;
                $data['shop']['is_membership_expired'] = $is_membership_expired;
                $data['shop']['freeMem'] = $freeMem;
                $data['shop']['contractCount'] = $contractCount;
                $data['shop']['memberCount'] = $memberCount;
                $data['shop']['myshopify_domain'] = $shop->myshopify_domain;
                $data['shop']['planType'] = $planType;
                if ($this->isFeatureExist('store-credit', Auth::user())) {
                    $data['shop']['storecredit'] = true;
                }

                return response()->json(['data' => $data], 200);
            } else {
                $planGroupVariant = SsPlanGroupVariant::where('shop_id', $shop->id)->with(['planGroups' => function ($query) {
                    $query->with('hasManyPlan', 'hasManyVariants', 'hasManyPlan.hasManyContracts',)->withCount(['hasManyRules', 'hasManyForms', 'hasManualMembership'])->orderBy('position', 'asc');
                }])->get();
                if (!empty($planGroupVariant)) {

                    $product_ids = array(); // Initialize the array outside the map function

                    $planGroupVariant->map(function ($item) use (&$product_ids) { // Pass $product_ids by reference using &

                        if (!array_key_exists($item['shopify_product_id'], $product_ids)) {

                            $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/' . $item['shopify_product_id'] . '.json';

                            $user = Auth::user();
                            $response = $user->api()->rest('GET', $endPoint);
                            if (isset($response['body']['product']['images'])) {
                                $image_array =   $response['body']['product']['images'];
                                if (count($image_array) >  0) {
                                    $item['img_src'] =  $image_array[0]['src'];
                                }
                            }

                            $product_ids[$item['shopify_product_id']] = $item['img_src']; // Add product ID and image source to the array

                        } else {
                            $item['img_src'] = $product_ids[$item['shopify_product_id']];
                        }
                    });


                    $user = Auth::user();
                    $data['planG'] = $planGroupVariant->toArray();
                    $data['products_list'] = SsPlanGroupVariant::where('shop_id', $shop->id)->distinct()->pluck('shopify_product_id')->toArray();
                    $data['shop']['domain'] = $shop->domain;
                    $data['shop']['myshopify_domain'] = $shop->myshopify_domain;
                    $data['shop']['currency'] = $shop->currency_symbol;
                    $data['shop']['id'] = $shop->id;
                    $data['shop']['storecredit'] = false;

                    $data['shop']['is_membership_expired'] = $is_membership_expired;
                    $data['shop']['freeMem'] = $freeMem;
                    $data['shop']['contractCount'] = $contractCount;
                    $data['shop']['memberCount'] = $memberCount;
                    $data['shop']['planType'] = $planType;
                    if ($this->isFeatureExist('store-credit', Auth::user())) {
                        $data['shop']['storecredit'] = true;
                    }
                    Cache::put($shop->id,  $data, now()->addMinutes(100));
                    return response()->json(['data' => $data], 200);
                }
                $data['shop']['is_membership_expired'] = $is_membership_expired;
                $data['shop']['freeMem'] = $freeMem;
                $data['shop']['contractCount'] = $contractCount;
                $data['shop']['memberCount'] = $memberCount;
                $data['shop']['currency'] = $shop->currency_symbol;
                $data['shop']['storecredit'] = false;
                $data['shop']['planType'] = $planType;
                $data['shop']['myshopify_domain'] = $shop->myshopify_domain;
                if ($this->isFeatureExist('store-credit', Auth::user())) {
                    $data['shop']['storecredit'] = true;
                }
                Cache::put($shop->id,  $data, now()->addMinutes(100));

                return response()->json(['data' => $data], 200);
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  planGroupIndex =============");
            logger($e);
            Bugsnag::notifyException($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function planGroupStore(PlanRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->data;
            $shop = getShopH();
            $planGCount = SsPlanGroup::where('shop_id', $shop->id)->count();
            ($planGCount > 0) ? '' : $this->event($shop->user_id, 'Onboarding', 'First Group Created', 'Merchant created group [' . $data['name'] . ']');
            $is_existplan = SsPlanGroup::where('shop_id', $shop->id)->where('id', $data['id'])->first();
            $plangroup = ($is_existplan) ? $is_existplan : new SsPlanGroup;
            $plangroup->shop_id = $shop->id;
            $plangroup->user_id =  $shop->user_id;
            $plangroup->active = 1;
            $plangroup->name = $data['name'];
            $plangroup->merchantCode = ($is_existplan) ? $is_existplan->merchantCode : strtolower(str_replace(' ', '-', $data['description']));
            $plangroup->description = $data['description'];
            $plangroup->position = ($is_existplan) ? $is_existplan->position : SsPlanGroup::where(
                'shop_id',
                $shop->id
            )->where('active', 1)->count();
            $plangroup->options = implode(',', array_filter($data['options']));
            $plangroup->save();

            // create/edit selling plan in shopify
            $result = ($plangroup->shopify_plan_group_id) ? $this->updateSellingPlanGroup($shop->user_id, $plangroup) : '';

            if ($result != '') {
                if ($result == 'success') {
                    DB::commit();
                    $msg = 'Saved!';
                    $success = true;
                } else if (!$result) {
                    DB::rollBack();
                    $msg = 'Error - please try again';
                    $success = false;
                } else {
                    DB::rollBack();
                    $msg = $result;
                    $success = false;
                }
            } else {
                DB::commit();
                $msg = 'Saved!';
                $success = true;
            }



            return response()->json(['data' => $msg, 'isSuccess' => $success], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  planGroupStore =============");
            logger($e);
            Bugsnag::notifyException($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function planGroupEdit($id = '')
    {
        try {
            $shopID = get_shopID_H();
            $plangroup = SsPlanGroup::select('id', 'name', 'description', 'options')->where('shop_id', $shopID)->where(
                'id',
                $id
            )->first();


            return response()->json(['data' => $plangroup], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  planGroupEdit =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function planGroupDestroy($id, $isRoute = true)
    {
        try {
            logger("=============== planGroupDestroy ================");
            DB::beginTransaction();
            $shop = getShopH();
            // logger(json_encode($shop));
            $user = User::find($shop->user_id);
            $planGroup = SsPlanGroup::where('id', $id)->first();

            if ($planGroup->shopify_plan_group_id) {
                //520
                $plans = SsPlan::where('ss_plan_group_id', $id)->get();
                //dd($plans, $id);
                if (count($plans) > 0) {
                    foreach ($plans as $key => $value) {
                        $planCount = SsPlan::where('ss_plan_group_id', $value->ss_plan_group_id)->count();
                        // $value->ss_plan_group_id
                        // logger("plan group count is an "  .  $planCount);
                        $result = $this->deleteSellingPlan($shop->user_id, $planGroup->shopify_plan_group_id, $value->shopify_plan_id, $planCount);
                        $newResult = $this->getControllerReturnData($result, 'Tier successfully removed');
                        if ($newResult['success']) {
                            $delDis = AutomaticDiscount::where(['user_id' => $shop->user_id])->first();
                            if($delDis){
                                $getdelId = $delDis->discount_id ? $delDis->discount_id : null;
                                AutomaticDiscount::where(['tier_id' => $value->ss_plan_group_id , 'user_id' => $shop->user_id])->delete();
                            }
                            $delShippingDis = ShippingDiscount::where(['user_id' => $shop->user_id])->first();
                            if($delShippingDis){
                                $getdelShipingId = $delShippingDis->discount_id ? $delShippingDis->discount_id : null;
                                ShippingDiscount::where(['tier_id' => $value->ss_plan_group_id , 'user_id' => $shop->user_id])->delete();
                            }
                            $value->delete();
                            $this->destroyRuleAndForm($id);
                            DB::commit();
                        } else {
                            DB::rollBack();
                        }
                        AutomaticAppDiscountJob::dispatch($shop->user_id);
                        AutomaticShippingDiscount::dispatch($shop->user_id);
                        $msg = $newResult['msg'];
                        $success = $newResult['success'];
                    }
                } else {

                    $msg = 'Deleted!';
                    $success = true;
                }

                if ($success) {
                    $result = $this->removeSegment($user, $planGroup->shopify_css_id);
                    // logger($result);
                    if ($result == 'success') {
                        // remove required selling plan for old product

                        $db_product = SsPlanGroupVariant::where('shop_id', $shop->id)->where('ss_plan_group_id', $id)->first();

                        $isExistProductForOtherPlan = SsPlanGroupVariant::where('shop_id', $shop->id)->where('ss_plan_group_id', '!=', $id)->where('shopify_product_id', $db_product->shopify_product_id)->count();

                        // logger($isExistProductForOtherPlan);
                        if ($isExistProductForOtherPlan == 0) {
                            $sres = $this->setRequiedSellingPlan($shop->user_id, $db_product->shopify_product_id, 'false');
                            if ($sres == 'success') {
                                $success = true;
                            } else {
                                DB::rollBack();
                                return response()->json(['data' => $sres, 'isSuccess' => false], 200);
                            }
                        }
                        $delDis = AutomaticDiscount::where(['user_id' => $shop->user_id])->first();
                        if($delDis){
                            $getdelId = $delDis->discount_id ? $delDis->discount_id : null;
                            AutomaticDiscount::where(['tier_id' => $id , 'user_id' => $shop->user_id])->delete();
                        }
                        $delShippingDis = ShippingDiscount::where(['user_id' => $shop->user_id])->first();
                        if($delShippingDis){
                            $getdelShipingId = $delShippingDis->discount_id ? $delShippingDis->discount_id : null;
                            ShippingDiscount::where(['tier_id' => $id , 'user_id' => $shop->user_id])->delete();
                        }
                        $planGroup->delete();
                        $db_product->delete();
                        AutomaticAppDiscountJob::dispatch($shop->user_id);
                        AutomaticShippingDiscount::dispatch($shop->user_id);
                        DB::commit();
                    }
                }
            } else {
                $planGroup->delete();
                $delDis = AutomaticDiscount::where(['user_id' => $shop->user_id])->first();
                if($delDis){
                    $getdelId = $delDis->discount_id ? $delDis->discount_id : null;
                    AutomaticDiscount::where(['tier_id' => $id , 'user_id' => $shop->user_id])->delete();
                }
                $delShippingDis = ShippingDiscount::where(['user_id' => $shop->user_id])->first();
                if($delShippingDis){
                    $getdelShipingId = $delShippingDis->discount_id ? $delShippingDis->discount_id : null;
                    ShippingDiscount::where(['tier_id' => $id , 'user_id' => $shop->user_id])->delete();
                }
                DB::commit();
                AutomaticAppDiscountJob::dispatch($shop->user_id);
                AutomaticShippingDiscount::dispatch($shop->user_id);
                $msg = 'Deleted!';
                $success = true;
            }
            if ($isRoute) {
                Cache::forget($shop->id);
                return response()->json(['data' => $msg, 'isSuccess' => $success], 200);
            } else {
                $r['msg'] = $msg;
                $r['success'] = $success;
                Cache::forget($shop->id);

                return $r;
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  planGroupDestroy =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  string  $planGid
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIsSellingPlanExists($id)
    {
        try {
            $shop = getShopH();
            $user = User::find($shop->user_id);
            $planGroupVariants = SsPlanGroupVariant::where('shopify_product_id', $id)->pluck('ss_plan_group_id')->toArray();
            $data = [
                'isAvl' => count($planGroupVariants) > 0 ? true : false,
                'id' => $id
            ];
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  checkIsSellingPlanExists =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function planEdit($id = '')
    {
        try {
            $shop = getShopH();
            $user = User::find($shop->user_id);
            $array = null;
            $formfield_index = null;

            $planGroupVariants = SsPlanGroupVariant::where('shopify_product_id', $id)->pluck('ss_plan_group_id')->toArray();
            $formfield_id = SsPlanGroup::whereIn('id', $planGroupVariants)->latest('updated_at')->first('id')->id;

            // logger("formfield_id is an " . $formfield_id);

            $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/' . $id . '.json';

            $response = $user->api()->rest('GET', $endPoint);


            if (!$response['errors']) {
                $res  = json_encode($response['body'], true);



                $image_array =   $response['body']['product']['images'];

                // JUst UNcomment line for image
                if (count($image_array) > 0) {

                    $array =  [
                        [
                            'originalSrc' => $response['body']['product']['images'][0]['src'],
                            // Add other properties as needed
                        ],
                        // Add more objects as needed
                    ];
                }
            }


            $planGroups = [];

            if (count($planGroupVariants) > 0) {

                foreach ($planGroupVariants as $formfield_key => $planGroupId) {
                    $planG = SsPlanGroup::with(
                        'hasManyPlan',
                        'hasManyVariants',
                        'hasManyRules',
                        'hasManyForms',
                        'hasManyPosDiscounts',
                        'hasManyPlan.hasManyPosDiscounts',
                        'hasManyCreditRules'
                    )
                        ->select(
                            'id',
                            'name',
                            'options',
                            'tag_customer',
                            'tag_order',
                            'discount_code',
                            'discount_code_members',
                            'is_display_on_cart_page',
                            'is_display_on_member_login',
                            'discount_type',
                            'activate_product_discount',
                            'activate_shipping_discount',
                            'shipping_discount_code',
                            'active_shipping_dic',
                            'shipping_discount_message',
                        )
                        ->where('shop_id', $shop->id)
                        ->where('id', $planGroupId)
                        ->first();
                    $plans = $planG->hasManyPlan;

                    $rules = $planG->hasManyRules;
                    $forms = $planG->hasManyForms;
                    $product = $planG->hasManyVariants;
                    $discounts = $planG->hasManyPosDiscounts;
                    $creditRules = $planG->hasManyCreditRules;

                    // $discounts = $plans[0]->hasManyPosDiscounts;

                    $data['rules'] = [];
                    $data['formFields'] = [];
                    $data['discounts'] = [];
                    $data['creditRules'] = [];
                    $data['membershipLength'] = [];

                    foreach ($plans as $key => $value) {
                        $data['membershipLength'][$key] = [
                            'id' => $value->id,
                            'billing_interval' => $value->billing_interval,
                            'billing_interval_count' => $value->billing_interval_count,
                            'pricing_adjustment_type' => $value->pricing_adjustment_type,
                            'pricing_adjustment_value' => $value->pricing_adjustment_value,
                            'options' => explode(',', $value->options),
                            'name' => $value->name,
                            'billing_min_cycles' => $value->billing_min_cycles,
                            'billing_max_cycles' => $value->billing_max_cycles,
                            'is_set_min' => $value->is_set_min,
                            'is_set_max' => $value->is_set_max,
                            'trial_available' => $value->trial_available,
                            'trial_days' => $value->trial_days,
                            'pricing2_adjustment_type' => $value->pricing2_adjustment_type,
                            'pricing2_adjustment_value' => $value->pricing2_adjustment_value,
                            'pricing2_after_cycle' => $value->pricing2_after_cycle,
                            'description' => $value->description,
                            'is_advance_option' => $value->is_advance_option,
                            'is_onetime_payment' => $value->is_onetime_payment,
                            'trial_type' => ($value->pricing2_after_cycle && !$value->trial_days) ? 'orders' : 'days',
                            'store_credit' => isset($value->store_credit) ? $value->store_credit : '',
                            'store_credit_amount' => $value->store_credit_amount,
                            'store_credit_frequency' => isset($value->store_credit_frequency) ?  $value->store_credit_frequency : '',

                        ];
                    }
                    foreach ($rules as $key => $value) {
                        $data['rules'][$key] = [
                            'id' => $value->id,
                            'rule_type' => $value->rule_type,
                            'rule_name' => $value->rule_name,
                            'rule_attribute1' => $value->rule_attribute1,
                            'rule_attribute1_handle' => $value->rule_attribute1_handle,
                            'rule_attribute2' => json_decode($value->rule_attribute2),
                        ];
                    }
                    foreach ($creditRules as $key => $value) {
                        $data['creditRules'][$key] = [
                            'id' => $value->id,
                            'shop_id' => $value->shop_id,
                            'ss_plan_group_id' => $value->ss_plan_group_id,
                            'trigger' => $value->trigger,
                            'value_type' => $value->value_type,
                            'value_amount' => $value->value_amount,
                        ];
                    }
                    foreach ($forms as $key => $value) {
                        $data['formFields'][$key] = [
                            'id' => $value->id,
                            'field_label' => $value->field_label,
                            'field_type' => $value->field_type,
                            'field_options' => $value->field_options,
                            'field_required' => $value->field_required,
                            'field_displayed' => json_decode($value->field_displayed),
                        ];
                    }
                    // foreach ($product as $key => $value) {
                    //     $data['product'] = [
                    //         'id' => $value->shopify_product_id,
                    //         'name' => $value->product_title,
                    //     ];
                    // }
                    foreach ($discounts as $key => $value) {
                        $data['discounts'][$key] = [
                            'id' => $value->id,
                            'discount_name' => $value->discount_name,
                            'discount_amount' => number_format($value->discount_amount, 2),
                            'discount_amount_type' => $value->discount_amount_type,
                        ];
                        // $data['discounts'][$key] = [
                        //     'id' => $value->id,
                        //     'discount_name' => $value->discount_name,
                        //     'discount_amount' => ($value->discount_amount_type == '%') ? number_format(($value->discount_amount * 100), 2): number_format($value->discount_amount, 0),
                        //     'discount_amount_type' => $value->discount_amount_type,
                        // ];
                    }
                    $automatice_discout = AutomaticDiscount::where(['tier_id' => $planGroupId , 'user_id' => $shop->user_id])->get();
                    $data['id'] = $planG->id;
                    $data['tier_id'] = $planG->tier_id;
                    $data['name'] = $planG->name;
                    $data['content'] = $planG->name;
                    $data['options'] = explode(',', $planG->options);
                    $data['tag_customer'] = $planG->tag_customer;
                    $data['tag_order'] = $planG->tag_order;
                    $data['automatic_checkout_discount'] = $automatice_discout;
                    $data['discount_code'] = $planG->discount_code;
                    $data['discount_code_members'] = $planG->discount_code_members;
                    $data['discount_type'] = $planG->discount_type;
                    $data['activate_product_discount'] = $planG->activate_product_discount;
                    $data['activate_shipping_discount'] = $planG->activate_shipping_discount;
                    $data['shipping_discount_code'] = $planG->shipping_discount_code ?? 0;
                    $data['active_shipping_dic'] = $planG->active_shipping_dic ?? '%';
                    $data['shipping_discount_message'] = $planG->shipping_discount_message;
                    $data['is_display_on_cart_page'] = $planG->is_display_on_cart_page;
                    $data['is_display_on_member_login'] = $planG->is_display_on_member_login;
                    $data['active_members'] = SsContract::where('ss_plan_groups_id', $planG->id)->count();
                    $data['deleted']['membershipLength'] = [];
                    $data['deleted']['rules'] = [];
                    $data['deleted']['formFields'] = [];
                    $data['deleted']['discounts'] = [];
                    $data['deleted']['creditRules'] = [];

                    $data['contract_count'] = SsContract::where('user_id', $user->id)->where('shop_id', $shop->id)->where('ss_plan_groups_id', $id)->count();


                    // logger("planG is an " . $planG);
                    // logger("=====================================================================================================================================================");

                    if ($planG->id == $formfield_id) {

                        $formfield_index = $formfield_key;
                        // logger("gere is nan " . $formfield_key);
                    }


                    array_push($planGroups, $data);
                }
            }

            // if ($this->isFeatureExist('automatic-discounts')) {
            //     $data['feature']['automatic_discounts'] = (Feature::isEnabledFor('automatic-discounts', $user));
            // } else {
            // }
            $response = [];
            $response['planGroups'] = $planGroups;
            $response['formfield_index'] = $formfield_index;
            $response['product'] = SsPlanGroupVariant::where('shopify_product_id', $id)->select('shopify_product_id as id', 'product_title as name')->first();
            $response['feature']['automatic_discounts'] = true;
            $response['product']['images'] =  $array;        //  Uncomment lIne
            $response['storeData'] = $this->getStoreData($user);
            $response['shop']['isPosEnable'] = $this->getIsPosEnabled($shop->user_id);
            $response['shop']['currency'] = $shop->currency_symbol;
            $response['shop']['name'] = $shop->myshopify_domain;
            $response['shop']['storecredit'] = false;
            if ($this->isFeatureExist('store-credit', Auth::user())) {
                $response['shop']['storecredit'] = false;
            }
            return response()->json(['data' => $response], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  planEdit =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function destroyRuleAndForm($id)
    {
        try {
            logger('========= START:: destroyRuleAndForm =========');

            // delete rules
            $rules = SsRule::where('ss_plan_group_id', $id)->get();
            if (count($rules) > 0) {
                foreach ($rules as $key => $value) {
                    $value->delete();
                }
            }

            // delete forms
            $forms = SsForm::where('ss_plan_group_id', $id)->get();
            if (count($forms) > 0) {
                foreach ($forms as $key => $value) {
                    $value->delete();
                }
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  destroyRuleAndForm =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function storeAllTiers(PlanRequest $request)
    {
        try {

            $getUser = Auth::user();
            $finalResult = [];
            $array = [];
            foreach ($request->data['tiers'] as $planGroup) {
                if ($request->data['newStore']) {
                    // logger("in if ");
                    $planGroup['product'] = $request->data['product'];
                    $planStoreResult = $this->planStore($planGroup, false);
                    $finalResult[$planGroup['tier_id']] = $planStoreResult;
                    //array_push($finalResult, $planStoreResult);
                } else {
                    if (isset($planGroup['tier_id'])) {
                        $planGroup['product'] = $request->data['product'];
                        $planStoreResult = $this->planStore($planGroup, false);
                        //array_push($finalResult, $planStoreResult);
                        $finalResult[$planGroup['tier_id']] = $planStoreResult;
                    } else {
                        logger("=======================> Plan group are skipped while updating:: " . $planGroup['id']);
                    }
                }
            }
            $shopID = get_shopID_H();
            $ids_for_job = array_values($finalResult);
            AutomaticAppDiscountJob::dispatch($getUser->id);
            AutomaticShippingDiscount::dispatch($getUser->id);
            Cache::forget($shopID);
            return response()->json($finalResult);
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  storeAllTiers =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function storeTier(PlanRequest $request)
    {

        return $this->planStore($request->data, true);
    }

    /**
     * @param  PlanRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function planStore($data, $val = false)
    {
        try {
            DB::beginTransaction();
            // $data = $request->data;

            // dd($this->getRuleMetaHtml(22, 128));
            $shop = getShopH();
            // dd($shop);
            // $user = User::find(22);
            // dd($this->updatePlanMetafields($user, 60, 6670859370662));
            $planCount = SsPlan::where('shop_id', $shop->id)->count();
            ($planCount > 0) ? '' : $this->event($shop->user_id, 'Onboarding', 'First Plan Created', 'Merchant created plan [' . $data['name'] . ']');

            //$is_existplanGroup = SsPlanGroup::where('shop_id', $shop->id)->where('name', $data['name'])->first();
            $is_existplanGroup = SsPlanGroup::where('shop_id', $shop->id)->where('id', $data['tier_id'])->first();
            //            add or update plan group

            $plangroup = ($is_existplanGroup) ? $is_existplanGroup : new SsPlanGroup;
            $plangroup->shop_id = $shop->id;
            $plangroup->user_id =  $shop->user_id;
            $plangroup->active = 1;
            $plangroup->name = $data['name'];
            $plangroup->merchantCode = ($is_existplanGroup) ? $is_existplanGroup->merchantCode : strtolower(str_replace(' ', '-', $data['name']));
            $plangroup->description = $data['name'];
            $plangroup->position = ($is_existplanGroup) ? $is_existplanGroup->position : SsPlanGroup::where(
                'shop_id',
                $shop->id
            )->where('active', 1)->count();
            $plangroup->options = implode(',', array_filter($data['options']));
            $plangroup->tag_customer = $data['tag_customer'];
            $plangroup->tag_order = $data['tag_order'];
            $plangroup->discount_type = $data['discount_type'];

            $plangroup->activate_product_discount = isset($data['activate_product_discount']) ? $data['activate_product_discount'] : false;
            if(empty($data['automatic_checkout_discount'])){
                $plangroup->activate_product_discount = false;
            }
            $plangroup->activate_shipping_discount = isset($data['activate_shipping_discount']) ? $data['activate_shipping_discount'] : false;
            if($data['discount_type'] == "3") {
                $plangroup->discount_code = $data['discount_code'] ? $data['discount_code'] : null;
                $plangroup->discount_code_members = $data['discount_code_members'] ? $data['discount_code_members'] : null;
                $plangroup->is_display_on_cart_page = $data['is_display_on_cart_page'] ? $data['is_display_on_cart_page'] : 0;
                $plangroup->is_display_on_member_login = $data['is_display_on_member_login'] ? $data['is_display_on_member_login'] : 0 ;
            }else {
                $plangroup->discount_code = null;
                $plangroup->discount_code_members = null;
                $plangroup->is_display_on_cart_page = 0;
                $plangroup->is_display_on_member_login = 0;
            }
            if($data['discount_type'] == "2") {
                if(isset($data['activate_shipping_discount']) && $data['activate_shipping_discount']) {
                    $plangroup->shipping_discount_code = isset($data['shipping_discount_code']) ? $data['shipping_discount_code'] : 0;
                    $plangroup->active_shipping_dic = isset($data['active_shipping_dic']) ? $data['active_shipping_dic'] : '%';
                    $plangroup->shipping_discount_message = isset($data['shipping_discount_message']) ? $data['shipping_discount_message'] : '';
                }else {
                    $plangroup->shipping_discount_code = 0;
                    $plangroup->active_shipping_dic = '%';
                    ShippingDiscount::where(['tier_id' => $plangroup->id ,'user_id' => $shop->user_id])->delete();
                }
            }else {
                $plangroup->shipping_discount_code = 0;
                $plangroup->active_shipping_dic = '%';
                $plangroup->activate_product_discount = false;
                $plangroup->activate_shipping_discount = false;
                $plangroup->shipping_discount_message = '';
            }
            $plangroup->save();
            // logger("================plangroup====================");
            // logger($plangroup);

            //add or update plan
            $f = 0;
            $billingInterval = [];
            $delDiscounts = [];

            if ($data['discount_type'] == "2") {
                if ($plangroup->activate_shipping_discount) {
                    $shippingData = [
                        'shop_id' => $shop->id,
                        'user_id' => $shop->user_id,
                        'product_id' => $data['product']['id'] ?? 0,
                        'tier_id' => $plangroup->id,
                        'customer_tag' => $data['tag_customer'] ?? '',
                        'shipping_discount' => $plangroup->shipping_discount_code ?? 0,
                        'shipping_discount_type' => isset($data['active_shipping_dic']) ? $data['active_shipping_dic'] : '%',
                        'shipping_discount_message' => isset($data['shipping_discount_message']) ? $data['shipping_discount_message'] : '',
                    ];
                    ShippingDiscount::updateOrCreate(
                        ['tier_id' => $plangroup->id, 'shop_id' => $shop->id, 'user_id' => $shop->user_id],
                        $shippingData
                    );
                } else {
                    ShippingDiscount::where([
                        'tier_id' => $plangroup->id,
                        'user_id' => $shop->user_id
                    ])->delete();
                }

                if(isset($data['activate_product_discount']) && $data['activate_product_discount']) {
                    if(!empty($data['automatic_checkout_discount'])){
                        foreach ($data['automatic_checkout_discount'] as $automatic_dicount) {
                            if(isset($automatic_dicount['user_id'])){
                                $delDiscounts[] = (int)($automatic_dicount['id']);
                                $exitsCheck = AutomaticDiscount::find((int)($automatic_dicount['id']));
                                $exitsCheck->shop_id = $shop->id;
                                $exitsCheck->user_id = $shop->user_id;
                                $exitsCheck->product_id = isset($data['product']['id']) ? $data['product']['id'] : 0;
                                $exitsCheck->tier_id = $plangroup->id;
                                $exitsCheck->collection_id = ($automatic_dicount['collection_id']);
                                $exitsCheck->collection_name = $automatic_dicount['collection_name'];
                                $exitsCheck->collection_discount = floatval($automatic_dicount['collection_discount']);
                                $exitsCheck->collection_discount_type = isset($automatic_dicount['collection_discount_type']) ? $automatic_dicount['collection_discount_type'] : '%';
                                $exitsCheck->collection_message = isset($automatic_dicount['collection_message'])? $automatic_dicount['collection_message'] : '';
                                $exitsCheck->customer_tag = isset($data['tag_customer']) ? $data['tag_customer'] : '';
                                $exitsCheck->save();
                            }else {
                                $updateandCreate =  new AutomaticDiscount;
                                $updateandCreate->shop_id = $shop->id;
                                $updateandCreate->user_id = $shop->user_id;
                                $updateandCreate->product_id = isset($data['product']['id']) ? $data['product']['id'] : 0;
                                $updateandCreate->tier_id = $plangroup->id;
                                $updateandCreate->collection_id = $automatic_dicount['collection_id'];
                                $updateandCreate->collection_name = $automatic_dicount['collection_name'];
                                $updateandCreate->collection_discount = floatval($automatic_dicount['collection_discount']);
                                $updateandCreate->collection_discount_type = isset($automatic_dicount['collection_discount_type']) ? $automatic_dicount['collection_discount_type']  : '%';
                                $updateandCreate->customer_tag = isset($data['tag_customer']) ? $data['tag_customer'] : '';
                                $updateandCreate->collection_message = isset($automatic_dicount['collection_message'])? $automatic_dicount['collection_message'] : '';
                                $updateandCreate->save();
                                $delDiscounts[] = (int)($updateandCreate->id);
                            }
                        }
                        if(!empty($delDiscounts)){
                            AutomaticDiscount::whereNotIn('id', $delDiscounts)->where('tier_id',$plangroup->id)->delete();
                        }
                    }else{

                        AutomaticDiscount::where(['tier_id' => $plangroup->id , 'user_id' => $shop->user_id])->delete();
                    }
                }else {
                    AutomaticDiscount::where(['tier_id' => $plangroup->id , 'user_id' => $shop->user_id])->delete();
                }
            } else {
                // Remove all  discounts
                ShippingDiscount::where([
                    'tier_id' => $plangroup->id,
                    'user_id' => $shop->user_id
                ])->delete();
                AutomaticDiscount::where([
                    'tier_id' => $plangroup->id,
                    'user_id' => $shop->user_id
                ])->delete();
            }


            foreach ($data['membershipLength'] as $mlkey => $mlval) {
                // logger("=======> Mlval Index : $mlkey <======");
                // logger(json_encode($mlval));
                $is_existplan = SsPlan::where('shop_id', $shop->id)->where(['id' => $mlval['id'], 'ss_plan_group_id' => $plangroup->id])->first();

                $plan = ($is_existplan) ? $is_existplan : new SsPlan;
                $plan->shop_id = $shop->id;
                $plan->user_id = $shop->user_id;
                $plan->ss_plan_group_id = $plangroup->id;
                $plan->shopify_plan_id  = ($is_existplan) ? $is_existplan->shopify_plan_id : null;
                $plan->name = $mlval['name'];
                $plan->description = $mlval['description'];
                $plan->options = $mlval['name'] . ' ' . $mlval['billing_interval_count'] . ' ' . $mlval['billing_interval'];

                if (in_array($mlval['billing_interval'], $billingInterval)) {
                    $plan->options .= " (" . count(array_keys($billingInterval, $mlval['billing_interval'])) . ")";
                }
                array_push($billingInterval,   $mlval['billing_interval']);
                $plan->status = 'active';
                // $plan->position = ($is_existplan) ? $is_existplan->position : SsPlan::where('shop_id', $shop->id)->where('ss_plan_group_id', $plangroup->id)->count();
                $plan->position = $mlkey;

                $plan->billing_interval = $mlval['billing_interval'];
                $plan->billing_interval_count = $mlval['billing_interval_count'];

                $plan->delivery_interval = $mlval['billing_interval'];
                $plan->delivery_interval_count = $mlval['billing_interval_count'];

                $plan->pricing_adjustment_type = $mlval['pricing_adjustment_type'];
                $plan->pricing_adjustment_value = $mlval['pricing_adjustment_value'];

                $plan->billing_anchor_day = null;
                $plan->billing_anchor_type = null;
                $plan->billing_anchor_month = null;

                $plan->delivery_anchor_day = null;
                $plan->delivery_anchor_type = null;
                $plan->delivery_anchor_month = null;

                $plan->delivery_cutoff = null;
                $plan->delivery_pre_cutoff_behaviour = null;

                $plan->is_advance_option = $mlval['is_advance_option'];
                $plan->trial_available = ($mlval['is_advance_option']) ? $mlval['trial_available'] : false;
                $plan->is_onetime_payment = ($mlval['is_advance_option']) ? $mlval['is_onetime_payment'] : false;

                $plan->pricing2_adjustment_type = $mlval['pricing2_adjustment_type'];
                $plan->pricing2_adjustment_value = $mlval['pricing2_adjustment_value'];
                // logger("===== Mval =====>" . $mlval['pricing2_after_cycle']);

                $plan->pricing2_after_cycle =   $mlval['is_advance_option'] ? ($mlval['trial_available'] ? ($mlval['trial_type'] == 'orders' && array_key_exists("pricing2_after_cycle", $mlval) ? $mlval['pricing2_after_cycle'] : null) : null) : null;

                $plan->trial_days =  $mlval['is_advance_option']  ? ($mlval['trial_available'] ? ($mlval['trial_type'] == 'days' && array_key_exists("trial_days", $mlval) ? $mlval['trial_days'] : null) : null) : null;


                $plan->is_set_min = $mlval['is_set_min'];
                $plan->is_set_max = $mlval['is_set_max'];

                $plan->billing_min_cycles = ($mlval['is_set_min']) ? $mlval['billing_min_cycles'] : null;
                $plan->billing_max_cycles = ($mlval['is_set_max']) ? $mlval['billing_max_cycles'] : null;

                $plan->store_credit = isset($mlval['store_credit']) ? $mlval['store_credit'] : false;
                $plan->store_credit_amount = $mlval['store_credit_amount'];
                $plan->store_credit_frequency = isset($mlval['store_credit_frequency']) ? $mlval['store_credit_frequency'] : '';

                $plan->save();


                // create/edit selling plan in shopify
                // logger("plan data before updating");
                // logger(json_encode($plan));
                $result = $this->ShopifySellingPlan($shop->user_id, $plan);
                if ($result == 'success') {
                    $f = 1;
                } else if (!$result) {
                    DB::rollBack();
                    $msg = 'Error - please try again';
                    // logger($msg);
                    $success = false;
                    throw new \Exception($msg);
                    // return response()->json(['data' => $msg, 'isSuccess' => $success], 422);
                } else {
                    DB::rollBack();
                    $msg = $result;
                    // logger($msg);
                    $success = false;
                    // logger("=-=======================>");
                    throw new \Exception($msg);
                    // return response()->json(['data' => $msg, 'isSuccess' => $success], 422);
                }
            }

            // logger("Membership Lengths endeddddd");
            // dd('vati gyu');
            // Add or update forms and rules

            // logger("plan group is an " .  $plangroup->id);
            if ($f == 1) {
                $plangroup = SsPlanGroup::find($plangroup->id);
                SsForm::Where('ss_plan_group_id', $plangroup->id)->delete();

                // update forms
                $frontFormData = $data['formFields'];
                foreach ($frontFormData as $fkey => $vval) {
                    // logger("form table id si an =====================================================>>>>>>>>>>>>>>>>>");
                    // logger($vval['id']);
                    $isExistForm = ($vval['id'] != '') ? SsForm::find($vval['id']) : '';
                    $form = ($isExistForm && $vval['id'] != '') ? $isExistForm : new SsForm;
                    $form->shop_id = $shop->id;
                    $form->ss_plan_group_id = $plangroup->id;
                    $form->field_label = $vval['field_label'];
                    $form->field_type = $vval['field_type'];
                    $form->field_options = $vval['field_options'];
                    $form->field_required = $vval['field_required'];
                    $form->field_required = $vval['field_required'];
                    $form->field_displayed = $vval['field_displayed'];
                    $form->field_order = $fkey;
                    $form->save();
                }
                // Credit Rules
                foreach ($data['creditRules'] as $crKey => $crVal) {
                    $is_exist_rule = SsStoreCreditRules::where('shop_id', $shop->id)
                        ->where('id', $crVal['id'])
                        ->first();
                    $creditRule = ($is_exist_rule) ? $is_exist_rule : new SsStoreCreditRules;

                    $creditRule->shop_id = $shop->id;
                    $creditRule->ss_plan_group_id = $plangroup->id;
                    $creditRule->trigger = $crVal['trigger'];
                    $creditRule->value_type = $crVal['value_type'];
                    $creditRule->value_amount = $crVal['value_amount'];
                    if ($crVal['value_type'] == 'membership_value') {
                        $creditRule->value_amount = null;
                    }
                    $creditRule->save();
                }

                $deletedCreditRules = $data['deleted']['creditRules'];
                foreach ($deletedCreditRules as $key => $value) {
                    $db_deleted = SsStoreCreditRules::find($value);
                    ($db_deleted) ? $db_deleted->delete() : '';
                }

                // update rules
                $frontRuleData = $data['rules'];
                foreach ($frontRuleData as $key => $rval) {
                    $isExistRule = ($rval['id'] != '') ? SsRule::find($rval['id']) : '';
                    $rule = ($isExistRule &&  $rval['id'] != '') ? $isExistRule : new SsRule;
                    $rule->shop_id = $shop->id;
                    $rule->ss_plan_group_id = $plangroup->id;


                    $rule->rule_type = $rval['rule_type'];
                    $rule->rule_name = $rval['rule_name'];
                    $rule->rule_attribute1 = $rval['rule_attribute1'];
                    $rule->rule_attribute2 = $rval['rule_attribute2'];
                    $rule->rule_attribute1_handle = $rval['rule_attribute1_handle'];
                    $rule->save();
                }

                // update POS discount

                $deletedDis = $data['deleted']['discounts'];
                foreach ($deletedDis as $key => $value) {
                    $db_disc = SsPosDiscounts::find($value);
                    ($db_disc) ? $db_disc->delete() : '';
                }
                $posDiscountData = $data['discounts'];
                foreach ($posDiscountData as $dkey => $dval) {
                    $isExistDisc = ($dval['id'] != '') ? SsPosDiscounts::find($dval['id']) : '';
                    $discount = ($isExistDisc && $dval['id'] != '') ? $isExistDisc : new SsPosDiscounts;
                    $discount->shop_id = $shop->id;
                    $discount->ss_plan_id = $plan->id;
                    $discount->ss_plan_groups_id = $plangroup->id;
                    $discount->discount_name = $dval['discount_name'];
                    $discount->discount_amount = $dval['discount_amount'];
                    // $discount->discount_amount = ($dval['discount_amount_type'] == '%') ? ($dval['discount_amount'] / 100) : $dval['discount_amount'];
                    $discount->discount_amount_type = $dval['discount_amount_type'];
                    $discount->save();
                }

                //update product

                $isExistProduct = SsPlanGroupVariant::where('shop_id', $shop->id)->where('ss_plan_group_id', $plangroup->id)->first();
                $oldProdId = ($isExistProduct) ? $isExistProduct->shopify_product_id : '';
                $productV =  ($isExistProduct) ? $isExistProduct : new SsPlanGroupVariant;
                $productV->shop_id = $shop->id;
                $productV->user_id = $shop->user_id;
                $productV->ss_plan_group_id = $plangroup->id;
                $productV->shopify_product_id = $data['product']['id'];
                $productV->product_title = $data['product']['name'];
                $productV->last_sync_date = date('Y-m-d H:i:s');
                $productV->save();

                if ($isExistProduct) {
                    if ($oldProdId != $data['product']['id']) {
                        $result = $this->updateSellingPlanGroupProduct($shop->user_id, (array)$data['product']['id'],  $plangroup->shopify_plan_group_id);
                        $newResult = $this->getControllerReturnData($result, 'Saved');
                        if ($newResult['success']) {
                            $success = true;
                        } else {
                            DB::rollBack();
                            throw new \Exception($newResult['msg']);
                            // return response()->json(['data' => $newResult['msg'], 'isSuccess' => $newResult['success']], 422);
                        }
                        $result = $this->updateSellingPlanGroupRemoveProduct($shop->user_id, (array)$oldProdId,  $plangroup->shopify_plan_group_id);

                        // remove required selling plan for old product

                        $isExistProductForOtherPlan = SsPlanGroupVariant::where('shop_id', $shop->id)->where('ss_plan_group_id', '!=', $plangroup->id)->where('shopify_product_id', $oldProdId)->count();

                        if ($isExistProductForOtherPlan == 0) {
                            $sres = $this->setRequiedSellingPlan($shop->user_id, $oldProdId, 'false');
                            if ($sres == 'success') {
                                $success = true;
                            } else {
                                DB::rollBack();
                                throw new \Exception($sres);

                                return response()->json(['data' => $sres, 'isSuccess' => false], 422);
                            }
                        }
                    }
                } else {
                    $result = $this->updateSellingPlanGroupProduct($shop->user_id, (array)$data['product']['id'],  $plangroup->shopify_plan_group_id);
                }

                $newResult = $this->getControllerReturnData($result, 'Saved');
                if ($newResult['success']) {
                    // logger("delete plan data ==========>>>>>>>>>>");
                    // logger($data['deleted']);
                    $result = $this->deletePlanData($data['deleted']);
                    $newResult = $this->getControllerReturnData($result, 'Saved');
                    if ($newResult['success']) {
                        // update metafield html
                        $user = User::find($shop->user_id);
                        // logger("***********  try to update meta fields *****************");
                        $result = $this->updatePlanMetafields($user, $plangroup->id, $data['product']['id']);

                        if ($result['success']) {
                            $result = $this->createSegment($user, $plangroup->name, $plangroup->tag_customer, $plangroup->shopify_css_id);
                            $f = 0;
                            if ($result['msg'] == 'success') {

                                $plangroup->shopify_css_id = $result['id'];
                                $plangroup->save();

                                $res = $this->setRequiedSellingPlan($user->id, $data['product']['id'], 'true');

                                if ($res == 'success') {
                                    $f = 1;


                                    $user = Auth::user();
                                    $res = $this->saveSellingPlanMetafields($user, $data['product']['id'], "sellings_plans", "json_string");
                                    if (!$shop->is_discount_added) {
                                        // update css file
                                        $theme_id = $this->getPublishTheme($user);
                                        $schema = $this->getThemeSchema($user->id, $theme_id);

                                        $data['name'] = '';
                                        if ($schema['status']) {
                                            $schema = $schema['data'][0];
                                            $data['name'] = (@$schema->theme_name) ? $schema->theme_name : '';
                                        }
                                        updateFilesForAutomaticDiscount($user, $theme_id, $data);

                                        $shop->is_discount_added = 1;
                                        $shop->save();
                                    }
                                    DB::commit();
                                    if ($val &&  $val === true) {
                                        return response()->json(['data' => 'Saved', 'isSuccess' => true], 200);
                                    }
                                    return $plangroup->id;
                                } else {
                                    DB::rollBack();
                                    return response()->json(['data' => $res, 'isSuccess' => false], 422);
                                }
                            } else {
                                $newResult['msg'] = $result['msg'];
                                $newResult['success'] = false;
                            }
                            if ($f == 0) {
                                if (!$is_existplanGroup) {
                                    $res = $this->planGroupDestroy($plangroup->id, false);
                                }

                                // logger(json_encode($result));
                                DB::rollBack();
                                throw new \Exception($result['msg']);
                                return response()->json(['data' => $result['msg'], 'isSuccess' => false], 422);
                            }
                        }
                    } else {
                        DB::rollBack();
                    }
                } else {
                    DB::rollBack();
                }
                return response()->json(['data' => $newResult['msg'], 'isSuccess' => $newResult['success']], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  planStore =============");
            logger($e);
            throw new \Exception($e->getMessage());
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function deletePlanData($data)
    {
        try {
            // delete plan
            $deletedPlan = $data['membershipLength'];
            foreach ($deletedPlan as $key => $value) {
                $res = $this->planDestroy($value);
                if (!$res['success']) {
                    return $res;
                }
            }

            // delete rules
            $deletedRules = $data['rules'];
            foreach ($deletedRules as $key => $value) {
                $res = $this->ruleDestroy($value);
            }

            // delete forms
            $deletedRules = $data['formFields'];
            foreach ($deletedRules as $key => $value) {
                $res = $this->formDestroy($value);
            }
            $res = 'success';
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  deletePlanData =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function planDestroy($id)
    {
        try {
            // dd('============> Id ===> ' . $id);

            $shop = getShopH();
            $plan = SsPlan::where('id', $id)->first();
            $planGroup = SsPlanGroup::where('id', $plan->ss_plan_group_id)->first();
            $planCount = SsPlan::where('ss_plan_group_id', $plan->ss_plan_group_id)->count();

            $result = $this->deleteSellingPlan($shop->user_id, $planGroup->shopify_plan_group_id, $plan->shopify_plan_id, $planCount);
            $newResult = $this->getControllerReturnData($result, 'Saved');

            if ($newResult['success']) {
                $plan->delete();
                if ($planCount == 1) {
                    $planGroup->shopify_plan_group_id = null;
                    $planGroup->save();
                }
            }

            return $newResult;
        } catch (\Exception $e) {
            logger("============= ERROR ::  planDestroy =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    public function ruleDestroy($id)
    {
        try {
            $shop = getShopH();
            $rule = SsRule::find($id);
            if ($rule) {
                $rule->delete();
            }
            $res['msg'] = 'saved!';
            $res['success'] = true;
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  ruleDestroy =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function formDestroy($id)
    {
        try {
            $shop = getShopH();
            $rule = SsForm::find($id);
            if ($rule) {
                $rule->delete();
            }
            $res['msg'] = 'saved!';
            $res['success'] = true;
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  formDestroy =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignProduct(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->data;
            $shop = getShopH();

            $newResource = $data['resource'];
            foreach ($newResource as $key => $val) {
                $variants = new SsPlanGroupVariant;
                $variants->shop_id =  $shop->id;
                $variants->user_id =  $shop->user_id;
                $variants->ss_plan_group_id = $data['planGroupId'];
                $variants->shopify_product_id = $val;
                // $variants->shopify_variant_id = $val['variant_id'];
                $variants->last_sync_date = date('Y-m-d H:i:s');
                $variants->save();
            }

            // remove deselected product

            $removeResource = $data['removeProducts'];
            foreach ($removeResource as $key => $val) {
                $variant = SsPlanGroupVariant::where('shop_id', $shop->id)->where('ss_plan_group_id', $data['planGroupId'])->where('shopify_product_id', $val)->first();
                if ($variant) {
                    $variant->delete();
                }
            }

            // assign product in shopify plan group
            $plangroup = SsPlanGroup::where('shop_id', $shop->id)->where('id', $data['planGroupId'])->first();
            // dd('gid://shopify/Product/' . implode(',gid://shopify/Product/', $newResource));

            $msg = 'Product added successfully';
            $success = true;

            if (!empty($newResource)) {
                $result = $this->updateSellingPlanGroupProduct($shop->user_id, $newResource,  $plangroup->shopify_plan_group_id);

                if ($result == 'success') {
                    DB::commit();
                    $msg = 'Product added successfully';
                    $success = true;
                } else if (!$result) {
                    DB::rollBack();
                    $msg = 'Error - please try again';
                    $success = false;
                } else {
                    DB::rollBack();
                    $msg = $result;
                    $success = false;
                }
            }

            if (!empty($removeResource)) {
                $result = $this->updateSellingPlanGroupRemoveProduct($shop->user_id, $removeResource,  $plangroup->shopify_plan_group_id);
                if ($result == 'success') {
                    DB::commit();
                    $msg = 'Product added successfully';
                    $success = true;
                } else if (!$result) {
                    DB::rollBack();
                    $msg = 'Error - please try again';
                    $success = false;
                } else {
                    DB::rollBack();
                    $msg = $result;
                    $success = false;
                }
            }
            return response()->json(['data' => $msg, 'isSuccess' => $success], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  assignProduct =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function position(Request $request)
    {
        try {
            $shop = getShopH();
            $data = $request->data;

            foreach ($data as $key => $val) {
                $plan = $val['plan'];
                foreach ($plan as $pkay => $pval) {
                    $plan = SsPlan::find($pkay);
                    $plan->update(['position' => $pval - 1]);
                }
                $plangp = SsPlanGroup::find($key);
                $plangp->update(['position' => $val['index']]);
                $plangp->save();
            }

            // update position in shopify plan/plan group
            $planGroup = SsPlanGroup::where('shop_id', $shop->id)->get();
            if (count($planGroup) > 0) {
                foreach ($planGroup as $pgkey => $pgvalue) {
                    if ($pgvalue->shopify_plan_group_id) {
                        $pgres = $this->updateSellingPlanGroup($shop->user_id, $pgvalue);

                        $plan = SsPlan::where('shop_id', $shop->id)->where('ss_plan_group_id', $pgvalue->id)->get();
                        // logger($pgvalue->id);
                        // logger(count($plan));
                        if (count($plan) > 0) {
                            foreach ($plan as $pkey => $pvalue) {
                                if ($pvalue->shopify_plan_id) {
                                    // logger('Updating.........................................');
                                    $pres = $this->updateSellingPlan($shop->user_id, $pgvalue->shopify_plan_group_id, $pvalue);
                                    // logger($pres);
                                }
                                $result = $this->ShopifySellingPlan($shop->user_id, $pvalue);
                            }
                        }
                    }
                }
            }
            return response()->json(['data' => 'Saved!'], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  position =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function checkActivePlan()
    {
        try {
            $user = Auth::user();

            $shopID = getShopH()->domain;
            logger("==========================================================>");
            $free_plan = Plan::where('is_free_trial_plans', 1)->first();
            logger("free plans is na ---------------> " . $user);


            if ($user->expired_plan_id !== $free_plan->id && $user->plan_id === null  && $user->expired_plan_id !== null) {
                logger("*********************************************************************************");
                $response = DB::table('charges')->where(['status' => 'ACTIVE', 'user_id' => $user->id])->count();
                $response = $response - 1;

                return response()->json(['data' => ['userID' => $user->id, 'plan' => 0]], 200);
            }
            if ($user->plan_id == null) {
                $user = User::find($user->id);
                $user->update(['plan_id' => $free_plan->id, 'expired_plan_id' => $free_plan->id]);
                return response()->json(['data' => ['userID' => $user->id, 'plan' => 1]], 200);
            } elseif ($free_plan && $user->plan_id == $free_plan->id) {
                return response()->json(['data' => ['userID' => $user->id, 'plan' => 1]], 200);
            } else {



                $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/shop.json';
                $result = $user->api()->rest('GET', $endPoint);

                if (!$result['errors']) {
                    $plan_name =  $result['body']['shop']['plan_name'];
                    $free_plans = ['affiliate', 'staff', 'partner_test', 'cancelled'];


                    if (in_array($plan_name, $free_plans, true)) {
                        $response = DB::table('charges')->where(['status' => 'ACTIVE', 'user_id' => $user->id])->count();
                    } else {
                        $response = DB::table('charges')->where(['status' => 'ACTIVE', 'user_id' => $user->id])->where('test', 0)->count();
                    }
                }

                // return (count($response) == 0) ?  redirect('app-plan/' . $shopID) : response()->json(['data' => ['shopID' => $shopID, 'plan' => $response]], 200);
                logger("response is an ===============================****************************************************< " . $response);
                return response()->json(['data' => ['userID' => $user->id, 'plan' => $response]], 200);
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  checkActivePlan =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function plansExport($shopID)
    {

        try {
            // logger("dsadsadsa");
            $shop = Shop::find($shopID);

            return   Excel::download(new PlansExport($shopID), 'plans.CSV');
        } catch (\Exception $e) {
            logger("============= ERROR ::  PlansExport =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function saveSellingPlanMetafields($user, $shopifyProductID, $metaKey, $metaValueType)
    {
        // dd($shopifyProductID);

        $planGroupVariants = SsPlanGroupVariant::where('shopify_product_id', $shopifyProductID)->pluck('ss_plan_group_id')->toArray();

        $ssPlans = SsPlan::whereIn('ss_plan_group_id', $planGroupVariants)->get();

        // dd($ssPlans);

        $metaValue = [];
        foreach ($ssPlans as $plan) {
            $metaValue[] = $plan->toArray();
        }


        $metafieldJson = [
            "metafield" => [
                'namespace' => 'simplee',
                'key' => $metaKey,
                'value' => json_encode($metaValue),
                'type' => $metaValueType
            ]
        ];

        $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/' . $shopifyProductID . '/metafields.json';

        $result = $user->api()->rest('POST', $endPoint, $metafieldJson);   // shopify metafield result.

        return $result;
    }
    public function restricated_contents()
    {
        try {

            $user = Auth::user();
            $mainData['storeData'] = $this->getStoreData($user);
            return $mainData;
        } catch (\Throwable $e) {
            logger("============= ERROR ::  restricated_contents =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
