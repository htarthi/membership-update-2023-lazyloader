<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ExportReportsCsvJob;
use App\Jobs\UpdateProductData;
use App\Models\Shop;
use App\Models\SsBillingAttempt;
use App\Models\SsCancellation;
use App\Models\SsContract;
use App\Models\SsContractLineItem;
use App\Models\SsCustomer;
use App\Models\SsDeletedProduct;
use App\Models\SsMetric;
use App\Models\SsOrder;
use App\Models\SsThemeInstall;
use Illuminate\Support\Str;
use App\Models\SsCustomPlan;
use App\Models\SsPlan;
use App\Models\SsPlanGroup;
use App\Models\SsPlanGroupVariant;
use App\Models\SsSetting;
use App\Models\User;
use App\Traits\ShopifyTrait;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Osiset\ShopifyApp\Storage\Models\Plan;
use PDO;

class DashboardController extends Controller
{
    use ShopifyTrait;

    public function appBladeindex(Request $request)
    {
        try {
            $id = ($request->id) ? $request->id : 0;
            $current_user = $this->getCurrentUser();
            //            if( empty($current_user) ){
            //                return redirect('/login');
            //            }
            return view('layouts.app', compact('id', 'current_user'));
        } catch (\Exception $e) {
            logger("============= ERROR ::  appBladeindex =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function subscriptionsBladeindex(Request $request)
    {
        try {
            $contract = SsContract::where('shopify_contract_id', $request->id)->where('shopify_customer_id', $request->customer_id)->first();
            $id = ($contract) ? $contract->id : -1;
            $current_user = $this->getCurrentUser();
            return view('layouts.app', compact('id', 'current_user'));
        } catch (\Exception $e) {
            logger("============= ERROR ::  subscriptionsBladeindex =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function plansBladeindex()
    {
        try {
            $id = -2;
            $current_user = $this->getCurrentUser();
            return view('layouts.app', compact('id', 'current_user'));
        } catch (\Exception $e) {
            logger("============= ERROR ::  plansBladeindex =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getCurrentUser()
    {
        $user = Auth::user();
        $shop = getShopH();
        $plan = Plan::find($user->plan_id);
        $current_user['user_id'] = $user->id;
        $current_user['hmac'] = hash_hmac('sha256', $user->id, config('const.HMAC_KEY'));
        $current_user['shop_name'] = $user->name;
        $current_user['name'] = $shop->owner;
        $current_user['email'] = $shop->email;
        $current_user['created_at'] = $user->created_at;
        $current_user['myshopify_domain'] = $shop->myshopify_domain;
        $current_user['simplee_shop_id'] = $shop->id;
        $current_user['simplee_plans_created'] = SsPlan::where('user_id', $user->id)->where('shop_id', $shop->id)->where('status', 'active')->count();
        $current_user['simplee_subscriptions'] = SsContract::where('user_id', $user->id)->where('shop_id', $shop->id)->where('status', 'active')->count();
        $current_user['simplee_install_tried'] = 0;
        $current_user['simplee_plan'] = $plan->name;
        $current_user['plan_id'] = $user->plan_id;
        $customPlan = SsCustomPlan::where('user_id', $user->id)->where('status', 'active')->first();
        $current_user['custom_plan'] = ($customPlan) ? $customPlan->plan_id : 0;
        return $current_user;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $shop = Shop::where('user_id', $user->id)->first();

            $active_subscription = SsContract::where('shop_id', $shop->id)->where('user_id', $user->id)->where('status', 'active')->count();
            $pause_subscription = SsContract::where('shop_id', $shop->id)->where('user_id', $user->id)->where('status', 'paused')->count();

            $default_timezone = date_default_timezone_get();
            $timezone = $shop->iana_timezone;

            date_default_timezone_set($shop->iana_timezone);
            $date = date("Y-m-d");

            $merchantTime = $this->getSubscriptionTimeDate(
                date(
                    "Y-m-d",
                    strtotime($date)
                ),
                $shop->id
            );
            $dateTime = new DateTime($merchantTime);
            $dateTime->modify("+24 hours");

            $AddedTime = date_format($dateTime, 'Y-m-d');
            $timeH = date_format($dateTime, 'H');

            $from = date('Y-m-d H', strtotime($merchantTime)) . ':00:00';
            $to = $AddedTime . ' ' . ($timeH - 1) . ':59:59';

            $new_subscription = SsContract::where('shop_id', $shop->id)->where('user_id', $user->id)->whereBetween('created_at', [$from, $to])->count();

            $today_order = SsOrder::where('shop_id', $shop->id)->where('user_id', $user->id)->whereBetween('created_at', [$from, $to])->count();

            $today_sales = SsOrder::where('shop_id', $shop->id)->where('user_id', $user->id)->whereBetween('created_at', [$from, $to])->sum('order_amount');

            $today_sales = calculateCurrency('USD', $shop->currency, $today_sales);
            $order = SsOrder::where('shop_id', $shop->id)->where('user_id', $user->id)->first();
            $deletedProduct = SsDeletedProduct::where('user_id', $user->id)->where('shop_id', $shop->id)->where('active', 1)->get()->toarray();

            if ($order) {
                $currencyS = $order->currency_symbol;
                $currency = preg_replace("/[A-Za-z]/", "", $currencyS);
            }

            $matrics = SsMetric::select('id', 'active_subscriptions', 'paused_subscriptions', 'orders_processed', 'amount_processed', 'cancelled_subscriptions')->where('shop_id', $shop->id)->orderBy('created_at', 'desc')->take('30')->get();

            $data['matrics']['active'] = array_fill(0, 30, 0);
            $data['matrics']['cancelled_subscriptions'] = array_fill(0, 30, 0);
            $data['matrics']['paused'] = array_fill(0, 30, 0);
            $data['matrics']['orders'] = array_fill(0, 30, 0);
            $data['matrics']['amount'] = array_fill(0, 30, 0);

            if (count($matrics) > 0) {
                $matrics = $matrics->toArray();

                $actives = array_column($matrics, 'active_subscriptions');
                $cancelled_subscriptions = array_column($matrics, 'cancelled_subscriptions');
                $paused = array_column($matrics, 'paused_subscriptions');
                $orders = array_column($matrics, 'orders_processed');
                $amount = array_column($matrics, 'amount_processed');

                $data['matrics']['active'] = $this->fillArray($actives);
                $data['matrics']['cancelled_subscriptions'] = $this->fillArray($cancelled_subscriptions);
                $data['matrics']['paused'] = $this->fillArray($paused);
                $data['matrics']['orders'] = $this->fillArray($orders);
                $data['matrics']['amount'] = $this->fillArray($amount);
            }

            $totalContracts = SsContract::where('shop_id', $shop->id)->where('user_id', $user->id)->count();
            $data['today_sales'] = ($order) ? $currency . number_format($today_sales, 2) . ' ' . $order->order_currency : 0;
            $data['today_order'] = $today_order;
            $data['new_subscription'] = $new_subscription;
            $data['active_subscription'] = $active_subscription;
            $data['paused_subscription'] = $pause_subscription;
            $data['currency'] = $shop->currency;
            $data['currency_symbol'] = $shop->currency_symbol;
            $data['total_contracts'] = $totalContracts;
            //            $data['matrics']['active'] = ;
            // $data['deletedProducts'] = $this->getShopifyVariant($deletedProduct, $user->id);

            date_default_timezone_set($default_timezone);
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $deletedProduct
     * @param $userID
     * @return array
     */
    public function getShopifyVariant($deletedProduct, $userID)
    {
        $res = [];
        if (!empty($deletedProduct)) {
            foreach ($deletedProduct as $key => $val) {
                // fetch product
                $endPoint = '/admin/products/' . $val['shopify_product_id'] . '.json';
                $parameter['fields'] = 'id,title';
                $result = $this->request('GET', $endPoint, $parameter, $userID);
                $res[$key]['display_name'] = '';
                if (!$result['errors']) {
                    $sh_variant = $result['body']->container['product'];
                    $res[$key]['display_name'] = $sh_variant['title'];
                    $res[$key]['subscriptions_impacted'] = $val['subscriptions_impacted'];
                    $res[$key]['id'] = $val['id'];
                } else {
                    $res[$key]['display_name'] = '#' . $val['shopify_product_id'];
                    $res[$key]['subscriptions_impacted'] = $val['subscriptions_impacted'];
                    $res[$key]['id'] = $val['id'];
                }

                if ($val['shopify_variant_id']) {
                    $endPoint = '/admin/variants/' . $val['shopify_variant_id'] . '.json';
                    $parameter['fields'] = 'id,title';
                    $result = $this->request('GET', $endPoint, $parameter, $userID);
                    if (!$result['errors']) {
                        $sh_variant = $result['body']->container['variant'];
                        $res[$key]['display_name'] = $res[$key]['display_name'] . '/' . $sh_variant['title'];
                    }
                }
            }
        }
        return $res;
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function replaceLineItem(Request $request)
    {
        try {
            $user = Auth::user();
            $shop = Shop::where('user_id', $user->id)->first();
            $data = $request->data;
            $deleted_id = $data['deleted_id'];
            $new_resource = $data['resource'][0];

            $deletedProduct = SsDeletedProduct::where('id', $deleted_id)->where('user_id', $user->id)->first();
            $lineItems = SsContractLineItem::where('user_id', $user->id)->where('shopify_product_id', $deletedProduct->shopify_product_id)->where('shopify_variant_id', $deletedProduct->shopify_variant_id)->get();

            if (count($lineItems) > 0) {
                foreach ($lineItems as $key => $val) {
                    //                    $result = $this->subscriptionDraftLineRemove($user->id, $val);

                    //                    if( $result == 'success' ){
                    $val->shopify_product_id = $new_resource['product_id'];
                    $val->shopify_variant_id = $new_resource['variant_id'];
                    $val->price = $new_resource['price'];

                    if ($val->discount_type == $shop->currency_symbol) {
                        $final_amt = number_format(($new_resource['price'] - $val->discount_amount), 2);
                    } elseif ($val->discount_type == '%') {
                        $nwprice = $new_resource['price'];
                        $cal_price = ($nwprice * $val->quantity);
                        $final_amt = number_format(($cal_price - (($cal_price * $val->discount_amount) / 100)), 2);
                    } else {
                        $final_amt = number_format($new_resource['price'], 2);
                    }
                    $val->final_amount = $final_amt;
                    $val->save();
                    //                    }else{
                    //                        logger($result);
                    //                    }

                    $result = $this->subscriptionDraftLineUpdate($user->id, $val);
                }
            }
            $deletedProduct->active = 0;
            $deletedProduct->save();
            return response()->json(['data' => 'Product updated successfully'], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  replaceLineItem =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $data
     */
    public function fillArray($data)
    {
        if (count($data) < 30) {
            $len = count($data);
            $diff = 30 - $len;
            $diffFill = array_fill(0, $diff, 0);
            return array_merge($diffFill, array_reverse($data));
        }
        return array_reverse($data);
    }

    public function dashboardgetdata(Request $request)
    {
        // $user = User::find(Auth::user()->id);

        $user = Auth::user();
        // $shop = Shop::where('user_id', $user->id)->first();

        $lastweek = Carbon::now()->subWeek();
        $lasttolastweek = Carbon::now()->subWeek(2);

        // Calculation for churn Rate
        $last_Active_membership = SsContract::where('user_id', $user->id)->whereDate('created_at', '<=', Carbon::now()->subDays(30))->where('status', 'active')->count();

        $cancelled_membership = SsCancellation::where('ss_contracts.user_id', $user->id)->whereDate('ss_cancellations.created_at', '<=', Carbon::now()->subDays(30))->where('ss_contracts.status', 'cancelled')->join('ss_contracts', 'ss_cancellations.ss_contract_id', '=', 'ss_contracts.id')->count();


        if ($last_Active_membership == 0 && $cancelled_membership == 0) {
            $churnrate  = 0;
        } else {
            if ($last_Active_membership > 0) {
                $churnrate =  ($cancelled_membership / $last_Active_membership) * 100;
            } else {
                $churnrate = 0;
            }
        }


        // Last Active Membership Percentage
        $last_ACM = SsContract::where('user_id', $user->id)->whereDate('created_at', '>=', $lastweek)->where('status', 'active')->count();
        $last_to_last_ACM = SsContract::where('user_id', $user->id)->whereBetween('created_at', [$lastweek, $lasttolastweek])->where('status', 'active')->count();
        $ACM_percentage   = $this->percentageCalculate($last_ACM, $last_to_last_ACM);


        // Membership Spends Percentage
        $last_MS  = SsOrder::where('user_id', $user->id)->whereDate('created_at', '>=', $lastweek)->sum('order_amount');
        $last_to_last_MS = SsOrder::where('user_id', $user->id)->whereBetween('created_at', [$lastweek, $lasttolastweek])->whereDate('created_at', '<=', $lastweek)->sum('order_amount');
        $MS_percentage = $this->percentageCalculate($last_MS, $last_to_last_MS);

        // Average Lifetime Percentage
        $last_order_count = SsOrder::where('user_id', $user->id)->whereDate('created_at', '>=', $lastweek)->count();
        $last_to_last_order_count  = SsOrder::where('user_id', $user->id)->whereBetween('created_at', [$lastweek, $lasttolastweek])->count();

        if ($last_order_count == 0 || $last_to_last_order_count == 0) {
            $Avg_percentage = $this->percentageCalculate(0, 0);
        } else {
            $last_avg = $last_MS / $last_order_count;
            $last_to_last_avg = $last_to_last_MS  / $last_to_last_order_count;
            $Avg_percentage = $this->percentageCalculate($last_avg, $last_to_last_avg);
        }


        $active_memberships_total = SsContract::where('user_id', $user->id)->where('status', 'active')->count();

        $membership_spend = SsOrder::where('user_id', $user->id)->sum('order_amount');

        $order_count  = SsOrder::where('user_id', $user->id)->count();
        if ($order_count !== 0) {
            $avg_lifetime_value = $membership_spend / SsOrder::where('user_id', $user->id)->count();
        } else {
            $avg_lifetime_value = 0;
        }

        $upcoming_renewals = SsContract::where('ss_contracts.user_id', $user->id)
            ->where('ss_contracts.status', 'active')
            ->where('ss_contracts.is_onetime_payment', 0)
            ->whereDate('ss_contracts.next_processing_date', '>', Carbon::today())
            ->join('ss_contract_line_items', 'ss_contracts.id', '=', 'ss_contract_line_items.ss_contract_id')
            ->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
            ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
            ->join('shops', 'ss_customers.shop_id', '=', 'shops.id')
            ->orderBy('ss_contracts.next_processing_date', 'ASC')
            ->limit(4)
            ->get(['ss_contracts.next_processing_date', 'ss_contracts.shopify_contract_id', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_contract_line_items.discount_amount', 'ss_contract_line_items.currency', 'ss_contracts.currency_code', 'ss_plans.name', 'ss_contracts.id', 'shops.iana_timezone']);



        $latestOrders = DB::table('ss_orders')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('ss_contract_id');


        $new_members = SsContract::select(
            'ss_contracts.id',
            'ss_customers.created_at',
            'ss_plans.name',
            'ss_plans.billing_interval_count',
            'ss_plans.billing_interval',
            'ss_customers.first_name',
            'ss_customers.last_name',
            'ss_orders.shopify_order_id',
            'ss_orders.shopify_order_name',
            'shops.iana_timezone'
        )
            ->join('ss_orders', 'ss_contracts.id', '=', 'ss_orders.ss_contract_id')
            ->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
            ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
            ->join('shops', 'ss_customers.shop_id', '=', 'shops.id')
            ->joinSub($latestOrders, 'latest_orders', function ($join) {
                $join->on('ss_contracts.id', '=', 'ss_orders.ss_contract_id')
                    ->on('ss_orders.id', '=', 'latest_orders.id');
            })
            ->where('ss_contracts.user_id', $user->id)
            ->orderBy('ss_contracts.member_number', 'DESC')
            ->limit(4)
            ->get();

        // $new_members = SsContract::select('ss_contracts.id', 'ss_customers.created_at', 'ss_plans.name', 'ss_plans.billing_interval_count', 'ss_plans.billing_interval', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_orders.shopify_order_name', 'ss_orders.shopify_order_id')
        //     ->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
        //     ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
        //     ->where('ss_contracts.user_id', $user->id)
        //     ->distinct()
        //     ->orderBy('ss_contracts.member_number', 'DESC')
        //     ->limit(4)
        //     ->get();

        $recent_cancelation = SsCancellation::orderBy('ss_cancellations.created_at', 'DESC')
            ->join(DB::raw('(SELECT ss_contract_id, MAX(created_at) AS max_created_at FROM ss_contract_line_items GROUP BY ss_contract_id) AS latest_joined'), function ($join) {
                $join->on('ss_cancellations.ss_contract_id', '=', 'latest_joined.ss_contract_id');
            })
            ->join('ss_contracts', 'ss_cancellations.ss_contract_id', '=', 'ss_contracts.id')
            ->join('ss_contract_line_items', function ($join) {
                $join->on('ss_contracts.id', '=', 'ss_contract_line_items.ss_contract_id')
                    ->on('ss_contract_line_items.created_at', '=', 'latest_joined.max_created_at');
            })
            ->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
            ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
            ->where(['ss_contracts.user_id' => $user->id, 'ss_contracts.status' => 'cancelled'])->limit(4)
            ->get(['ss_cancellations.id', 'ss_cancellations.ss_contract_id', 'ss_cancellations.created_at', 'ss_cancellations.cancelled_by', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_plans.name', 'ss_contract_line_items.discount_amount', 'ss_contracts.order_count']);


        $shop = Shop::where('user_id', $user->id)->first(['id', 'currency', 'currency_symbol', 'domain', 'myshopify_domain']);


        $themes = $this->getThemes();
        $theme_id  = null;

        foreach ($themes as $theme) {
            if ($theme['role'] == 'main') {
                $theme_id = $theme['id'];
                break;
            }
        }
        $setting = SsSetting::where('shop_id', $shop->id)->orderBy('id', 'desc')->first();
        // logger('THEME ID IS AN =======================================>' . $theme_id);
        // logger('USER ID IS AN =======================================>' . $user->id);

        if ($setting->theme_app_embed !== 1) {
            $asset = $user->api()->rest('GET', 'admin/themes/' . $theme_id . '/assets.json', ["asset[key]" => "config/settings_data.json"]);

            $res = $asset['body']['asset']['value'];
            $response = json_decode($res, true);

            if (is_array($response['current'])) {
                if (isset($response['current']['blocks'])) {

                    $datas = $response['current']['blocks'];
                    foreach ($datas as $data) {
                        $app_embed_id = Str::after($data['type'], 'app-block/');
                        if ($app_embed_id === env('SHOPIFY_APP_EMBEDED_ID')) {
                            if ($data['disabled'] == false) {
                                $data = $setting;
                                $data['theme_app_embed']  = 1;
                                $data->save();
                                break;
                            }
                        }
                    }
                }
            }
        }

        $contracts_count  = SsContract::where('user_id', $user->id)->count();
        $total_free_memberships = $setting->free_memberships;
        $count_free_memberships = $contracts_count;

        if ($user->plan_id) {
            $getPlan = Plan::where('id', $user->plan_id)->first();
            if ($getPlan && $getPlan->is_free_trial_plans) {
                $freePlans = true;
            } else {
                $freePlans = false;
            }
        } else {
            $freePlans = true;
        }

        $data =  [
            "active_memberships_total" => $active_memberships_total,
            "membership_spend" => round($membership_spend),
            "avg_lifetime_value" => round($avg_lifetime_value),
            "active_memberships_percentage" => $ACM_percentage,
            "membership_spend_percentage" => $MS_percentage,
            "avg_lifetime_value_percentage" => $Avg_percentage,
            "upcoming_renewals" => $upcoming_renewals,
            "new_members" => $new_members,
            "recent_cancelation" => $recent_cancelation,
            "shop" => $shop,
            "is_app_embed" => $setting->theme_app_embed  === 1 ? true : false,
            "is_old_installation" => $user->is_old_installation === 1 ? true : false,
            "churnrate" => round($churnrate),
            'eligibleForSubscriptions' => $this->shopFeature($user->id),
            'themes' => $theme_id,
            'total_free_memberships' => $total_free_memberships,
            'count_free_memberships' => $count_free_memberships,
            'freePlans' => $freePlans,


        ];
        return response()->json($data);
    }

    public function percentageCalculate($last_week, $last_to_last_week)
    {

        $profitOrLoss = $last_week - $last_to_last_week;

        if ($profitOrLoss > 0) {
            $is_increase = 1;
            if ($last_to_last_week == 0) {
                $percenrtage = ($profitOrLoss * 100);
            } else {

                $percenrtage = ($profitOrLoss / $last_to_last_week) * 100;
            }
        } elseif ($profitOrLoss <  0) {
            $is_increase = 0;
            $percenrtage = ($profitOrLoss / $last_to_last_week) * 100;
        } else {
            $is_increase = null;
            $percenrtage = null;
        }


        return ["is_increase" => $is_increase, "percentage" => number_format(abs($percenrtage))];
    }

    public function reportExport(Request $request,$shopID, $email, $selectedSegmentIndex)
    {
            try {

                $shop = Shop::find($shopID);
                $p=$request->p;
                $lp=$request->lp;
                $em=$request->em;
                $s=$request->s;

                ExportReportsCsvJob::dispatch($shopID, trim($email),$selectedSegmentIndex ,$p,$lp,$em,$s);
                return response()->json(["data" => $shop, "is_success" => true]);
            } catch (Exception $e) {
                logger($e);
                return $e;
            }
    }

    public function getReportsData(Request $request)
    {
        try {

            $shop = Shop::where('user_id', Auth::user()->id)->first();
            $ss_metrics = SsMetric::where('shop_id', $shop->id)->orderBy('date', 'ASC');

            $active_key = [];
            $active_values = [];
            $cancelled_values = [];
            $new_values = [];
            $days_difference = 0;



            switch ($request['period']) {
                case "today":
                    $ss_metrics  =  $ss_metrics->whereDate('date', Carbon::today())->get(['date', 'active_subscriptions', 'new_subscriptions', 'cancelled_subscriptions']);
                    break;
                case "yesterday":
                    $ss_metrics  =  $ss_metrics->whereDate('date', Carbon::now()->subDay())->get(['date', 'active_subscriptions', 'new_subscriptions', 'cancelled_subscriptions']);
                    break;
                case "last_30_days":
                    $ss_metrics = $ss_metrics->whereBetween('date', [Carbon::now()->subDays(30), Carbon::now()])->get(['date', 'active_subscriptions', 'new_subscriptions', 'cancelled_subscriptions']);
                    break;
                case "last_90_days":

                    $start_date = Carbon::now()->subDays(90);
                    $end_date = Carbon::now();
                    $results = $this->monthWiseData($ss_metrics, $start_date, $end_date);
                    break;
                case "last_365_days":
                    $start_date = Carbon::now()->subDays(365);
                    $end_date = Carbon::now();
                    $results = $this->monthWiseData($ss_metrics, $start_date, $end_date);
                    break;
                case "last_7_days":
                    $ss_metrics = $ss_metrics->whereBetween('date', [Carbon::now()->subDays(7), Carbon::now()])->get(['date', 'active_subscriptions', 'new_subscriptions', 'cancelled_subscriptions']);
                    break;
            }



            $explode = explode("to", $request['period']);
            if (count($explode) > 1 && $request['period'] !== "today") {

                $date1 = new DateTime($explode[0]);
                $date2 = new DateTime($explode[1]);

                $days_difference = $date1->diff($date2)->days;

                if ($days_difference > 32) {
                    $results = $this->monthWiseData($ss_metrics, $explode[0], $explode[1]);
                } else {
                    $ss_metrics = $ss_metrics->whereBetween('date', [$explode[0], $explode[1]])->get(['date', 'active_subscriptions', 'new_subscriptions', 'cancelled_subscriptions']);
                }
            }


            if ($request['period'] == "last_90_days" || $request['period'] == "last_365_days" || $days_difference > 31) {

                foreach ($results as $result) {
                    array_push($active_key, $result['month']);
                    array_push($active_values, $result['total_active_subscriptions']);
                    array_push($cancelled_values, $result['total_new_subscriptions']);
                    array_push($new_values, $result['total_cancelled_subscriptions']);
                }
            }

            if ($request['period'] == "today" ||  $request['period'] == "last_7_days" || $request['period'] == "yesterday" || $request['period'] == "last_30_days" || $days_difference < 32) {
                foreach ($ss_metrics as $ss_metric) {
                    array_push($active_key, Carbon::parse($ss_metric->date)->format('M-d'));
                    array_push($active_values, $ss_metric->active_subscriptions);
                    array_push($cancelled_values, $ss_metric->cancelled_subscriptions);
                    array_push($new_values, $ss_metric->new_subscriptions);
                }
            }


            return ["active_memberships" => ["key" => $active_key, "values" => $active_values,], "cancelled_subscriptions" => ["key" => $active_key, "values" => $cancelled_values], "new_memberships" => ["key" => $active_key, "values" => $new_values],'shop'=>$shop];

        } catch (Exception $e) {
            logger("============= ERROR ::  getReportsData =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    public function  monthWiseData($ss_metrics, $start_date, $end_date)
    {
        // Query the records within the last 90 days
        $ss_metrics = $ss_metrics->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m');
            })
            ->each(function ($chunk, $month) use (&$results) {
                // Calculate totals for each chunk
                $total_active_subscriptions = $chunk->sum('active_subscriptions');
                $total_new_subscriptions = $chunk->sum('new_subscriptions');
                $total_cancelled_subscriptions = $chunk->sum('cancelled_subscriptions');

                // Get the start and end dates for each chunk
                $start_date = Carbon::parse($chunk->first()->created_at)->format('M-Y');
                $end_date = $chunk->last()->created_at;

                $results[] = [
                    'month' => $start_date,
                    'total_active_subscriptions' => $total_active_subscriptions,
                    'total_new_subscriptions' => $total_new_subscriptions,
                    'total_cancelled_subscriptions' => $total_cancelled_subscriptions,
                ];
            });

        return $results;
    }

    public function upcoming_renewals(Request $request)
    {
        try {
            $s = $request['s'];
            $lp = $request['lp'];
            $sort_key = $request['sk'];
            $sort_value = $request['sv'];
            $user = Auth::user();

            $sentences = preg_split('/(?<=[.!?])\s+/', $lp);

            // If needed, further split any sentences that still have commas within them
            $finalArray = [];
            foreach ($sentences as $sentence) {
                $parts = array_map('trim', explode(',', $sentence));
                $finalArray = array_merge($finalArray, $parts);
            }

            // return $finalArray;

            $upcoming_renewals = SsContract::join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')->join('shops', 'ss_customers.shop_id', '=', 'shops.id')->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')->where('ss_contracts.status', 'active')->whereDate('ss_contracts.next_processing_date', '>', Carbon::today())->join('ss_contract_line_items', 'ss_contracts.id', '=', 'ss_contract_line_items.ss_contract_id')->where('ss_contracts.user_id', $user->id)->where('ss_contracts.shopify_contract_id', '!=', null)->where('ss_contracts.is_onetime_payment', 0)->select([
                'ss_contracts.id',
                'ss_contracts.member_number',
                'ss_contracts.next_processing_date',
                'ss_contracts.next_order_date',
                'ss_contracts.pricing_adjustment_value',
                'ss_contracts.failed_payment_count',
                'ss_contracts.created_at',
                'ss_contracts.order_count',
                'ss_contracts.trial_available',
                'ss_contracts.pricing2_adjustment_value',
                'ss_customers.first_name',
                'ss_customers.last_name',
                'ss_plans.name',
                'ss_plans.trial_days',
                'ss_plans.pricing2_after_cycle',
                'shops.domain',
                'shops.iana_timezone',
                'shops.currency_symbol',
                'ss_customers.shopify_customer_id',
                'ss_contracts.currency_code',
                'ss_contract_line_items.discount_amount'
            ]);

            if (isset($lp) && !empty($lp)) {
                $statusArr = explode(',', $lp);

                $upcoming_renewals = $upcoming_renewals->whereIn('ss_plans.name', $finalArray);
                logger('=======> last payment status Array ');
                logger($statusArr);
            }

            if (isset($s) && !empty($s)) {
                $upcoming_renewals = $upcoming_renewals->where(function ($query) use ($s) {
                    $query->Where('ss_customers.first_name', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_customers.last_name', 'LIKE', '%' . $s . '%');
                });
            }

            if (isset($sort_key) && !empty($sort_key) && isset($sort_value) && !empty($sort_value)) {
                if ($sort_key == "Member_Number") {
                    $sort_key = "ss_contracts.member_number";
                } elseif ($sort_key == "Customer_Name") {
                    $sort_key = "ss_customers.first_name";
                } elseif ($sort_key == "Order_Date") {
                    $sort_key = "ss_contracts.next_order_date";
                } elseif ($sort_key == "Failed_Orders") {
                    $sort_key = "ss_contracts.failed_payment_count";
                } elseif ($sort_key == "Next_Billing_Attempt") {
                    $sort_key = "ss_contracts.next_processing_date";
                }
            }



            $shop = Shop::where('user_id', $user->id)->first();
            $activePlans = SsPlan::select('name')->where('shop_id', $shop->id)->get();

            $ss_plans = (count($activePlans) > 0) ? array_unique($activePlans->pluck('name')->toArray()) : [];
            $upcoming_renewals = $upcoming_renewals->orderBy($sort_key ?  $sort_key : "ss_contracts.next_processing_date", $sort_value ? $sort_value : 'asc')->paginate(20);

            return response()->json(['data' => $upcoming_renewals], 200);
        } catch (Exception $e) {
            logger("============= ERROR ::  upcoming_renewals =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    public function recent_billing_attempts(Request $request)
    {
        try {

            $user = Auth::user();

            $s = $request['s'];
            $sort_key = $request['sk'];
            $sort_value = $request['sv'];
            $lp  = $request['lp'];
            $em = $request['em'];
            //
            $shop =  Shop::where('user_id', $user->id)->first();

            $attempts = SsBillingAttempt::join('ss_contracts', 'ss_billing_attempts.ss_contract_id', '=', 'ss_contracts.id')->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')->join('shops', 'ss_customers.shop_id', '=', 'shops.id')->leftJoin('ss_orders', 'ss_billing_attempts.shopify_order_id', '=', 'ss_orders.shopify_order_id')->where('ss_billing_attempts.shop_id', $shop->id)->select(['ss_billing_attempts.id', 'ss_billing_attempts.completedAt', 'ss_billing_attempts.status', 'ss_contracts.member_number', 'ss_contracts.id', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_billing_attempts.errorMessage', 'ss_orders.order_amount', 'ss_orders.shopify_order_name', 'shops.domain', 'shops.myshopify_domain', 'shops.currency_symbol', 'shops.iana_timezone', 'ss_orders.shopify_order_id']);

            // SsBillingAttempt::join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')->join('shops', 'ss_customers.shop_id', '=', 'shops.id')->join('ss_orders', 'ss_billing_attempts.shopify_order_id', '=', 'ss_orders.shopify_order_id')->where('ss_billing_attempts.shop_id', $shop->id)->select(['ss_billing_attempts.id', 'ss_billing_attempts.completedAt', 'ss_billing_attempts.status','ss_contracts.id', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_billing_attempts.errorMessage', 'ss_orders.order_amount', 'ss_orders.shopify_order_name', 'shops.domain', 'shops.currency_symbol', 'ss_orders.shopify_order_id']);



            if (isset($lp) && !empty($lp)) {
                $statusArr = explode(',', $lp);
                $attempts = $attempts->whereIn('ss_billing_attempts.status', $statusArr);
            }

            if (isset($em) && !empty($em)) {
                $statusArr = explode(',', $em);
                $attempts = $attempts->whereIn('ss_billing_attempts.errorMessage', $statusArr);
            }


            if (isset($s) && !empty($s)) {
                $attempts = $attempts->where(function ($query) use ($s) {
                    $query->Where('ss_customers.first_name', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_customers.last_name', 'LIKE', '%' . $s . '%');
                });
            }



            if (isset($sort_key) && !empty($sort_key) && isset($sort_value) && !empty($sort_value)) {
                if ($sort_key == "Member_Number") {
                    $sort_key = "ss_contracts.member_number";
                } elseif ($sort_key == "Customer_Name") {
                    $sort_key = "ss_customers.first_name";
                } elseif ($sort_key == "completedAt") {
                    $sort_key = null;
                }
            }

            // logger("sort key is :" . $sort_key);
            // logger("sort val is :" . $sort_value);

            $attempts = $attempts->orderBy($sort_key ?  $sort_key : "ss_billing_attempts.completedAt", $sort_value ? $sort_value : 'Desc')->paginate(20);
            return response()->json(['data' => $attempts], 200);
        } catch (Exception $e) {
            logger("============= ERROR ::  recent_billing_attempts =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    public function active_plans()
    {
        try {
            $user = Auth::user();
            $shop = Shop::where('user_id', $user->id)->first();
            $activePlans = SsPlanGroup::select('name')->where('shop_id', $shop->id)->get();

            $ss_plans = SsPlan::where('shop_id', $shop->id)->pluck('name')->toArray();
            $uniquePlans = array_values(array_unique($ss_plans));

            return response()->json(['data' => $uniquePlans], 200);

            $ss_plans = (count($activePlans) > 0) ? array_unique($activePlans->pluck('name')->toArray()) : [];
            return response()->json(['data' => $ss_plans], 200);
        } catch (Exception $e) {
            logger("============= ERROR ::  active_plans =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateProductsData(Request $request)
    {
        $user = Auth::user();
        // logger('=========== Inside the update product data');

        $products = SsPlanGroupVariant::where('user_id', $user->id)->whereNull('deleted_at')->get();

        foreach ($products as $product) {
            dispatch(new UpdateProductData($user, $product))->onQueue('UpdateServer');
        }
        $shop = $this->getShopdata();
        // $user->last_login = Carbon::now();
        // $user->save();
        return response()->json(['data' => 'Product updated successfully', 'shop' => $shop], 200);
    }

    public function checkMaintenance(Request $request)
    {
        $maintanceMeta = DB::table('meta')->where('name', 'maintenance')->first();
        if ($maintanceMeta) {
            foreach (json_decode($maintanceMeta->meta) as $meta) {
                if ($meta->key === 'is_enable') {
                    $inMaintenance = $meta->value;
                } else {
                    $secret = $meta->value;
                }
            }
            // logger("inMaintenance");
            // logger($inMaintenance);
            if ($inMaintenance == 1) {
                $querySecret = $request->query('secret');
                if ($querySecret == $secret) {
                    $data = [
                        'is_maintence' => false,
                        'is_secret' => $querySecret,
                    ];
                    return $data;
                } else {
                    $data = [
                        'is_maintence' => true,
                        'is_secret' => null,
                    ];
                    return $data;
                }
            } else {
                $data = [
                    'is_maintence' => false,
                    'is_secret' => null,
                ];
                return $data;
            }

            // return true;
            // logger($inMaintenance);
            // logger($secret);
            // if(!$inMaintenance) return false;
            // $querySecret = $request->query('secret');
            // if(!$querySecret) return true;
            // if($querySecret == $secret) return false;
        }
        $data = [
            'is_maintence' => false,
            'is_secret' => null,
        ];
        return $data;
    }

    public function newest_members(Request $request)
    {
        try {

            $user = Auth::user();
            $s = $request['s'];
            $sort_key = $request['sk'];
            $sort_value = $request['sv'];
            $shop =  Shop::where('user_id', $user->id)->first();

            $contract = SsContract::where('ss_contracts.shop_id', $shop->id)->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')->join('shops', 'ss_customers.shop_id', '=', 'shops.id')->join('ss_orders', 'ss_contracts.origin_order_id', '=', 'ss_orders.shopify_order_id')->select(['ss_contracts.id', 'ss_contracts.member_number', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_orders.order_amount', 'ss_orders.shopify_order_id', 'ss_plans.name as plan_name', 'ss_contracts.created_at', 'ss_contracts.next_order_date', 'shops.iana_timezone']);

            if (isset($s) && !empty($s)) {
                $contract = $contract->where(function ($query) use ($s) {
                    $query->Where('ss_customers.first_name', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_customers.last_name', 'LIKE', '%' . $s . '%');
                });
            }

            if (isset($sort_key) && !empty($sort_key) && isset($sort_value) && !empty($sort_value)) {
                if ($sort_key == "Member_Number") {
                    $sort_key = "ss_contracts.member_number";
                } elseif ($sort_key == "Customer_Name") {
                    $sort_key = "ss_customers.first_name";
                } elseif ($sort_key == "created_at") {
                    $sort_key = 'ss_contracts.created_at';
                } elseif ($sort_key == "next_order_date") {
                    $sort_key = 'ss_contracts.next_order_date';
                }
            }

            $contract = $contract->orderBy($sort_key ?  $sort_key : "ss_contracts.member_number", $sort_value ? $sort_value : 'Desc')->paginate(20);
            return response()->json(['data' => $contract], 200);
        } catch (Exception $e) {
            logger("============= ERROR ::  newest_members =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function recent_cancellation(Request $request)
    {

        try {

            $user = Auth::user();
            $s = $request['s'];
            $sort_key = $request['sk'];
            $sort_value = $request['sv'];
            $shop =  Shop::where('user_id', $user->id)->first();

            $recent_cancelation = SsCancellation::join(DB::raw('(SELECT ss_contract_id, MAX(created_at) AS max_created_at FROM ss_contract_line_items GROUP BY ss_contract_id) AS latest_joined'), function ($join) {
                $join->on('ss_cancellations.ss_contract_id', '=', 'latest_joined.ss_contract_id');
            })
                ->join('ss_contracts', 'ss_cancellations.ss_contract_id', '=', 'ss_contracts.id')
                ->join('ss_contract_line_items', function ($join) {
                    $join->on('ss_contracts.id', '=', 'ss_contract_line_items.ss_contract_id')
                        ->on('ss_contract_line_items.created_at', '=', 'latest_joined.max_created_at');
                })
                ->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
                ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
                ->where(['ss_contracts.shop_id' => $shop->id, 'ss_contracts.status' => 'cancelled'])

                ->select(['ss_cancellations.id', 'ss_cancellations.ss_contract_id', 'ss_cancellations.created_at', 'ss_cancellations.cancelled_by', 'ss_customers.first_name', 'ss_customers.last_name', 'ss_plans.name', 'ss_contract_line_items.discount_amount', 'ss_contracts.order_count']);


            if (isset($s) && !empty($s)) {
                $recent_cancelation = $recent_cancelation->where(function ($query) use ($s) {
                    $query->Where('ss_customers.first_name', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_customers.last_name', 'LIKE', '%' . $s . '%');
                });
            }

            if (isset($sort_key) && !empty($sort_key) && isset($sort_value) && !empty($sort_value)) {
                if ($sort_key == "Customer_Name") {
                    $sort_key = "ss_customers.first_name";
                } elseif ($sort_key == "created_at") {
                    $sort_key = null;
                }
            }

            $recent_cancelation = $recent_cancelation->orderBy($sort_key ?  $sort_key : "ss_cancellations.created_at", $sort_value ? $sort_value : 'Desc')->paginate(20);
            return response()->json(['data' => $recent_cancelation], 200);
        } catch (Exception $e) {
            logger("============= ERROR ::  newest_members =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getUserDetails()
    {
        $user = Auth::user();
        $shop = getShopH();
        $current_user['user_id'] = $user->id;
        $current_user['hmac'] = hash_hmac('sha256', $user->id, config('const.HMAC_KEY'));
        $current_user['shop_name'] = $user->name;
        $current_user['name'] = $shop->owner;
        $current_user['email'] = $shop->email;
        $current_user['created_at'] = $user->created_at;
        $current_user['myshopify_domain'] = $shop->myshopify_domain;
        return $current_user;
    }
}
