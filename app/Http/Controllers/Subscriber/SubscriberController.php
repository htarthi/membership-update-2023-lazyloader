<?php

namespace App\Http\Controllers\Subscriber;

use App\Exports\PlansExport;
use App\Models\SsSetting;
use Carbon\Carbon;
use App\Exports\SubscribersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriberRequest;
use App\Http\Resources\SubscriptionResource;
use App\Jobs\ExportSubscribersCsvJob;
use Illuminate\Support\Str;

use App\Models\ExchangeRate;
use App\Models\Shop;
use App\Models\SsPlan;
use App\Models\SsPlanGroup;
use App\Models\SsContract;
use App\Models\SsContractLineItem;
use App\Models\SsOrder;
use App\Models\SsCustomer;
use App\Models\SsEmail;
use App\Models\SsAnswer;
use App\Models\User;
use App\Traits\ShopifyTrait;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\error;

class SubscriberController extends Controller
{
    use ShopifyTrait;

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $shopID = get_shopID_H();
            $shop = getShopH();
            $s = $request->s;
            $f = $request->f;
            $p = $request->p;
            $lp = $request->lp;
            $sort_key = $request->sk;
            $sort_value = $request->sv;

            $subscriber = SsContract::select(
                'ss_contracts.id',
                'ss_contracts.shop_id',
                'ss_contracts.ss_customer_id',
                'ss_contracts.shopify_contract_id',
                'ss_contracts.status',
                'ss_contracts.status_billing',
                'ss_contracts.status_display',
                'ss_contracts.is_migrated',
                'next_order_date',
                'next_processing_date',
                'ss_contracts.order_count',
                'ss_contracts.member_number',
                'ss_customers.first_name',
                'ss_customers.last_name',
                'ss_customers.email',
                'ss_customers.phone',
                'ss_customers.date_first_order',
                'ss_contracts.created_at',
                'ss_plan_groups.name As plan_name',
                'is_onetime_payment',
                DB::raw('(SELECT status FROM ss_billing_attempts
                WHERE ss_billing_attempts.ss_contract_id = ss_contracts.id
                ORDER BY id DESC
                LIMIT 1) as last_payment_status')
            )
                ->join('ss_customers', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
                ->leftJoin('ss_plan_groups', 'ss_plan_groups.id', '=', 'ss_contracts.ss_plan_groups_id')
                ->leftJoin('ss_billing_attempts', 'ss_contracts.id', '=', 'ss_billing_attempts.ss_contract_id') // Use left join to ensure we get contracts even without billing attempts
                ->where(function ($query) use ($f) {
                    if ($f == 'failed') {
                        $query->where('ss_contracts.status', 'active')->where('ss_contracts.status_billing', 'failed');
                    } elseif ($f == 'expired') {
                        $query->where('ss_contracts.status_display', 'Active')->where('ss_contracts.status', 'cancelled');
                    } elseif ($f == 'cancelled') {
                        // $query->where('ss_contracts.status', 'LIKE', '%' . $f . '%')->where('ss_contracts.status_display', '!=', 'Expired');
                        $query->where('ss_contracts.status_display','Access Removed');

                    } elseif ($f != 'all') {
                        $query->where('ss_contracts.status', 'LIKE', '%' . $f . '%');
                    } elseif ($f == 'active') {
                        $query->where('ss_contracts.status', 'active');
                    }
                })
                ->where(function ($query) use ($p) {
                    if (isset($p) && !empty($p)) {
                        $planArr = explode(',', $p);
                        $query->whereIn('ss_plan_groups.name', $planArr);
                    }
                })
                ->where(function ($query) use ($lp) {
                    if (isset($lp) && !empty($lp)) {
                        $statusArr = explode(',', $lp);
                        $query->whereIn('ss_billing_attempts.status', $statusArr);
                    }
                })->groupBy('ss_contracts.id');







            if (isset($s) and !empty($s)) {
                $subscriber = $subscriber->where(function ($query) use ($s) {
                    $query->where('order_count', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_contracts.id', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_contracts.shopify_contract_id', 'LIKE', '%' . $s . '%')
                        ->orWhere('first_name', 'LIKE', '%' . $s . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $s . '%')
                        ->orWhere('email', 'LIKE', '%' . $s . '%')
                        ->orWhere('ss_contracts.member_number', $s)
                        ->orWhere('phone', 'LIKE', '%' . $s . '%');
                });
                // logger("============>   Query Is");
                // logger(json_encode($subscriber));
            }

            if (isset($sort_key) && !empty($sort_key) && isset($sort_value) && !empty($sort_value)) {
                if ($sort_key == "customer") {
                    $sort_key = 'ss_customers.first_name';
                }else if($sort_key == 'member_number'){
                    $sort_key = 'ss_contracts.id';
                }else if($sort_key == 'next_billing_date'){
                    $sort_key = 'ss_contracts.next_processing_date';
                }
            }


            // logger("Shop ID IS AN " . $shopID);

            $subscriber = $subscriber->where('ss_contracts.shop_id', $shopID)
                ->where('ss_customers.shop_id', $shopID)
                ->orderBy($sort_key ?  $sort_key : "ss_contracts.id", $sort_value ? $sort_value : 'desc')
                // ->orderBy($sort['column'], $sort['direction'] == 'descending' ? 'desc': 'asc')
                ->paginate(20);


            $activePlans = SsPlanGroup::select('name')->where('shop_id', $shop->id)->get();

            $res['memberships'] = paginateH($subscriber);
            $res['memberships']['data'] = SubscriptionResource::collection($subscriber);
            $res['shop'] = $shop->id;
            $res['activePlans'] = (count($activePlans) > 0) ? array_unique($activePlans->pluck('name')->toArray()) : [];
            return response()->json(['data' => $res], 200);
        } catch (\Exception $e) {
            logger('==============> Error while fetching subscribers');
            logger($e);
            return response()->json(['data' => $e], 422);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id, Request $request)
    {
        try {
            $shop = getShopH();
            $user = User::where('id', $shop->user_id)->first();
            $user = Auth::user();

            $subscriber = SsContract::with('ActivityLog', 'LineItems', 'Customer', 'BillingAttempt', 'CustomerAnswer', 'PlanGroup.hasManyPlan')->where('shop_id', $shop['id'])->where('id', $id)->first();

            // logger(json_encode($subscriber));
            if ($subscriber->is_physical_product === null) {

                $variant_id  = SsContractLineItem::where('ss_contract_id', $subscriber->id)->first('shopify_variant_id')->shopify_variant_id;


                $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/variants/' . $variant_id . '.json';

                $shopify_variant = $user->api()->rest('GET', $endPoint);


                if (!$shopify_variant['errors']) {
                    $subscriber->update(['is_physical_product' => $shopify_variant['body']['variant']['requires_shipping']]);
                }
                // dd( [$shopify_variant['body']['variant']['requires_shipping'],$variant_id]);
            }

            $next = SsContract::select('id')->where('id', '>', $id)->where('shop_id', $shop['id'])->orderBy('id')->first();
            $previous = SsContract::select('id')->where('id', '<', $id)->where('shop_id', $shop['id'])->orderBy('id', 'desc')->first();

            $data['contract'] = [];
            if ($subscriber) {

                $nextOD = $this->getSubscriptionTimeDate(date('Y-m-d', strtotime($subscriber->next_order_date)), $shop->id, date('H:i:s', strtotime($subscriber->next_order_date)));
                $data['availableDates'] = [];

                // Check if anchor day
                $anchorDateType = $subscriber->billing_anchor_type;

                if (isset($anchorDateType)) {
                    $anchorDay = $subscriber->billing_anchor_day;
                    $anchorMonth = $subscriber->billing_anchor_month;
                    $data['availableDates'] = $this->getAvailableDates($anchorDateType, $anchorDay, $anchorMonth, $subscriber->next_order_date);
                    $data['contract']['anchorDay'] = $anchorDay;
                    $data['contract']['anchorMonth'] = $anchorMonth;
                    $data['contract']['anchorDateType'] = $anchorDateType;
                }

                $subscriber->next_order_date = date('M d, Y', strtotime($nextOD));
                $subscriber->next_processing_date = date('M d, Y H:i', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($subscriber->next_processing_date)), $shop->id, date("H:i:s", strtotime($subscriber->next_processing_date)), 'UTC')));

                $subscriber->customer->date_first_order = date('M d, Y', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($subscriber->customer->date_first_order)), $shop->id, date('H:i:s', strtotime($subscriber->customer->date_first_order)))));

                $data['contract'] = $subscriber;
                $data['contract']['lineItems'] = (!empty($subscriber['LineItems'])) ? $this->getLineItemsData($subscriber, $user) : [];
                $data['contract']['activityLog'] = $this->getActivity($subscriber['ActivityLog'], $shop->id, $shop->iana_timezone);
                $data['contract']['billingAttempt'] = $this->billingAttempt($subscriber->BillingAttempt()->paginate(5, ['*'], 'page', 1), $shop->id);
                // $data['contract']['fulfillmentOrders'] = ($subscriber->last_billing_order_number) ? $this->getFulfillments($user->id, $subscriber->last_billing_order_number) : [];
                $subscriber->shopify_contract_id &&  $this->getSubscriptionDiscount($user->id, $subscriber->shopify_contract_id);                // $data['contract']['customerAnswer'] = (!empty($subscriber['CustomerAnswer'])) ? $this->getLineItemsData($subscriber, $user) : [];
                // $subscriber->shopify_contract_id && $this->subscriptionContractSetNextBillingDate($shop->user_id, $subscriber->shopify_contract_id);
                $data['otherContracts'] = $this->getOtherContracts($subscriber);
                $subscriber->unsetRelation('ActivityLog');
                $subscriber->unsetRelation('LineItems');
                $subscriber->unsetRelation('BillingAttempt');

                $data['contract']['prev'] = ($previous) ? '/subscriber/' . $previous->id . '/edit?page=1' : null;
                $data['contract']['next'] = ($next) ? '/subscriber/' . $next->id . '/edit?page=1' : null;
                $data['contract']['all_plans'] = (@$subscriber->PlanGroup->hasManyPlan) ? $subscriber->PlanGroup->hasManyPlan : [];
                $data['contracts_list'] = SsContract::where('ss_customer_id', $subscriber->ss_customer_id)->get(['id', 'status', 'next_processing_date', 'member_number']);
            }
            $data['shop']['domain'] = $shop['myshopify_domain'];
            $data['shop']['name'] =  strstr($shop['myshopify_domain'], '.', true);
            $data['shop']['currency'] = $shop->currency_symbol;

            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  edit =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request)
    {
        try {


            DB::beginTransaction();
            $result = 'success';
            $data   = $request->data;

            if (@$data['mode'] == 'api') {
                $domain = $data['shop'];
                $user = User::where('name', $domain)->first();
                $shop = Shop::where('user_id', $user->id)->first();
                $user_type = 'customer';
            } else {
                $shop = getShopH();
                $user = User::find($shop->user_id);
                $user_type = 'user';
            }

            $Contract = SsContract::find($data['contract_id']);

            $dbNextOD = date('Y-m-d', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($Contract->next_order_date)), $shop->id, date('H:i:s', strtotime($Contract->next_order_date)))));


            $contractNextOD = (@$data['next_order_date']) ? date('Y-m-d', strtotime($data['next_order_date'])) : $dbNextOD;


            if (@$data['mode'] == 'api' && @$data['type'] == "resumed" && $contractNextOD <= Carbon::now() && $Contract->order_count <= $Contract->is_set_max) {
                $ret['data'] = "Sorry, you can't resume this membership as the maximum length of the membership has already been reached";
                return $ret;
            }

            // 2024-06-21T08:37:47.000Z

            if (@$data['mode'] == 'api' && @$data['type'] == "resumed" && $contractNextOD <= Carbon::now()) {

                $date = Carbon::now('UTC')->format('Y-m-d\TH:i:s.v\Z');


                $dr =    $this->getSubscriptionTimeDate(date("Y-m-d",strtotime($date)), $shop->id);


                $Contract->next_order_date = $dr;
                $Contract->next_processing_date = $dr;

            }







            if (@$data['type'] == 'all' || @$data['type'] == 'remove_lineitem' || @$data['type'] == 'portal_all') {
                $deleted = array_filter($data['deleted']);

                $lineitems = ($data['type'] == 'all' || $data['type'] == 'portal_all') ? $data['line_items'] : [];
                if (!empty($deleted)) {
                    foreach ($deleted as $key => $val) {
                        $lineitem = SsContractLineItem::find($val);
                        $result = $this->subscriptionDraftLineRemove($shop->user_id, $lineitem);
                        ($result == 'success') ? SsContractLineItem::find($val)->delete() : '';
                    }
                }

                if ($data['type'] == 'all' || $data['type'] == 'portal_all') {

                    if (!empty($lineitems)) {
                        foreach ($lineitems as $key => $val) {
                            $lineitem = SsContractLineItem::where('id', $val['id'])->first();

                            foreach ($val['sh_variants'] as $skey => $svalue) {
                                if ($svalue['id'] == $val['selected_variant']) {
                                    $selected_variant = $svalue;
                                    break;
                                }
                            }

                            if ($val['selected_variant'] != $val['shopify_variant_id']) {
                                if (!empty($selected_variant)) {
                                    $lineitem->shopify_variant_id = $selected_variant['id'];
                                    $lineitem->shopify_variant_title = $selected_variant['title'];
                                    $lineitem->sku = $selected_variant['sku'];
                                    $lineitem->price = $selected_variant['price'];
                                }
                            }
                            if (!empty($selected_variant)) {
                                $lineitem->price = $selected_variant['price'];
                            }
                            // $lineitem->quantity = $val['quantity'];
                            if($val['quantity']){
                                $lineitem->quantity = $val['quantity'];
                            }
                            if ($lineitem->discount_amount != $val['discount_amount']) {
                                // logger('..................... Mannually changed price : ' . $val['discount_amount'] . ' .......................');
                                $this->saveActivity($shop->user_id, $Contract->ss_customer_id, $Contract->id, $user_type, "Membership price was updated from $Contract->currency_code$lineitem->discount_amount to $Contract->currency_code" .  number_format($val['discount_amount'], 2, '.', ''));
                                $lineitem->discount_amount = $val['discount_amount'];
                            } else if ($data['selling_plan_id'] != $Contract->ss_plan_id) {
                                // logger('..................... Changed Selling Plan .......................');
                                // Updating for multicurency
                                if (!empty($data['selling_plan_id'])) {
                                    $db_sellingPlan = SsPlan::where('id', $data['selling_plan_id'])->first();
                                    // logger($db_sellingPlan);
                                    $deliveryPrice = floatval($db_sellingPlan->pricing_adjustment_value);
                                    if (isset($lineitem->currency) && $shop->currency != $lineitem->currency) {
                                        // logger('..................... Diffrent Currency .......................');
                                        $customer_currency = $lineitem->currency;
                                        $exchange_rate = ExchangeRate::orderBy('created_at', 'desc')->first();

                                        $amount = floatval($db_sellingPlan->pricing_adjustment_value);
                                        $conversion_rates = collect(json_decode($exchange_rate->conversion_rates))->toArray();
                                        $defaultCurrencyUsdRate = floatval($conversion_rates[$shop->currency]);

                                        // Calculated amount in USD
                                        $amountInUSD = floatval($amount / $defaultCurrencyUsdRate);

                                        $customerCurrencyUsdRate = floatval($conversion_rates[$customer_currency]);

                                        // Calculated USD to CUSTOMER Currency
                                        $deliveryPrice = floatval($amountInUSD * $customerCurrencyUsdRate);
                                    }
                                    $lineitem->discount_amount = $deliveryPrice;
                                    // logger('..................... Final discount_amount : ' . $lineitem->discount_amount . ' .......................');
                                }
                            }
                            $lineitem->save();
                            if ($lineitem->shopify_contract_id) {
                                $result = $this->subscriptionDraftLineUpdate($shop->user_id, $lineitem);
                            }
                        }
                    }

                    if ($data['type'] == 'all') {
                        $contract = SsContract::find($data['contract_id']);
                        $contract->prepaid_renew = $data['prepaid_renew'];
                        $contract->billing_interval = isset($data['billing_interval']) ? $data['billing_interval'] : $contract->billing_interval;
                        $contract->billing_interval_count = isset($data['billing_interval_count']) ? $data['billing_interval_count'] : $contract->billing_interval_count;
                        $contract->save();

                        //                save customer note
                        $subscriber = SsCustomer::find($data['customer_id']);
                        $subscriber->update(['notes' => $data['note']]);
                        $subscriber->save();

                        $answers = $data['customer_answer'];
                        foreach ($answers as $key => $value) {
                            $ans = SsAnswer::find($value['id']);
                            $ans->answer = $value['answer'];
                            $ans->save();
                        }

                        // Change Frequency
                        if ($contract->shopify_contract_id) {
                            $result = $this->updateSubscriptionContract($shop->user_id, $data['contract_id'], 'change_frequency');
                        }

                        //update selling plan if required
                        if ($data['selling_plan_id'] != '' && ($data['selling_plan_id'] != $Contract->ss_plan_id)) {
                            $db_plan = SsPlan::find($data['selling_plan_id']);

                            $Contract->ss_plan_id = $data['selling_plan_id'];
                            $Contract->billing_interval = $db_plan->billing_interval;
                            $Contract->billing_interval_count = $db_plan->billing_interval_count;
                            $Contract->delivery_interval = $db_plan->delivery_interval;
                            $Contract->delivery_interval_count = $db_plan->delivery_interval_count;
                            $Contract->billing_min_cycles = $db_plan->billing_min_cycles;
                            $Contract->billing_max_cycles = $db_plan->billing_max_cycles;
                            $Contract->save();

                            if ($Contract->shopify_contract_id) {

                                // $db_sellingPlan = SsPlan::where('id', $contract->ss_plan_id)->first();

                                // $EditedlineItem = SsContractLineItem::where('ss_contract_id', $contract->id)->first();

                                // $lineResult = $this->subscriptionDraftLineUpdate($shop->user_id, $EditedlineItem);

                                $result = $this->updateSubscriptionContract($shop->user_id, $data['contract_id'], 'selling_plan');
                            }
                        }
                    }
                }

                $msg = 'Membership saved';
            }
            if ($data['type'] == 'paused' || $data['type'] == 'resumed' || $data['type'] == 'cancelled' || $data['type'] == 'cancelled-removeaccess') {

                $type = $data['type'];
                $type = ($data['type'] == 'resumed') ? 'active' : $type;
                $type = ($data['type'] == 'cancelled-removeaccess') ? 'cancelled' : $type;

                $Contract->update(['status' => $type]);
                if ($data['type'] == 'user' && $data['type'] == 'cancelled') {
                    $Contract->update(['status_display' => 'Active - Expiring']);
                }
                $Contract->save();
                $msg = 'Membership ' . ($data['type'] == 'cancelled-removeaccess') ? 'cancelled' : $data['type'] . ' successfully';

                if ($data['type'] == 'cancelled-removeaccess' && ($Contract->shopify_contract_id == null || $Contract->shopify_contract_id == '')) {
                    $activityMsg = 'Membership was cancelled by merchant & access removed immediately';
                } elseif ($data['type'] == 'cancelled-removeaccess' && $Contract->shopify_contract_id !== null) {
                    $activityMsg = 'Membership was cancelled (and access removed) manually.';
                } else {
                    $activityMsg = ($user_type == 'user') ? 'Membership was ' . $data['type'] . ' manually' : 'Customer ' . $data['type'] . ' their membership';
                    $cancelReason = isset( $data['selectedReason']) ?  $data['selectedReason'] : '';
                    if($cancelReason){
                        $activityMsg = $activityMsg . ' Reason : ' . $cancelReason ;
                    }

                }

                $this->saveActivity($shop->user_id, $Contract->ss_customer_id, $Contract->id, $user_type, $activityMsg);

                if ($data['type'] == 'cancelled' || $data['type'] == 'cancelled-removeaccess') {
                    // logger('\n=======================> Inside the cancel membership\n');
                    $setting = SsSetting::select('notify_cancel', 'notify_revoke', 'notify_email', 'membership_cancel_email_enabled','email_from_email', 'email_from_name')->where('shop_id', $shop->id)->first();


                    if (($Contract->shopify_contract_id == null || $Contract->shopify_contract_id == '')) {
                        $Contract->status_display = 'Access Removed';
                        $Contract->save();
                        $isExistTag = SsContract::where('user_id', $user->id)
                            ->where('shopify_customer_id', $Contract->shopify_customer_id)
                            ->where('tag_customer',  $Contract->tag_customer)
                            ->where('status', 'active')
                            ->where('id', '!=', $Contract->id)
                            ->count();
                        if ($isExistTag == 0) {
                            $this->updateShopifyTags(
                                $user,
                                $Contract->shopify_customer_id,
                                $Contract->tag_customer,
                                'customer',
                                'remove'
                            );
                        }
                    } else if ($data['type'] == 'cancelled-removeaccess') {
                        $Contract->status_display = 'Access Removed';
                        $Contract->save();
                        // logger('\n=======================> Cancelled remove all access\n');
                        // $this->checkForActiveMemberTag($user, $Contract->shopify_customer_id, $Contract->tag_customer);
                        $this->updateShopifyTags(
                            $user,
                            $Contract->shopify_customer_id,
                            $Contract->tag_customer,
                            'customer',
                            'remove'
                        );
                    }


                    if ($setting->notify_cancel && $setting->notify_email != '') {
                        $notifyData = config('notify-mails.notify_cancel');

                        $newData = $this->fetchContractFormFields($Contract->id, $notifyData['body']);

                        $db_ss_plan = SsPlan::select('name')->where('id', $Contract->ss_plan_id)->first();
                        $planData['next_billing_date'] = $Contract->next_processing_date;
                        $planData['membership_plan'] = ($db_ss_plan) ? $db_ss_plan->name : '';

                        $notifyMailRes = sendMailH($notifyData['subject'], $newData, config('notify-mails.notify_from_email'), $setting->notify_email, config('notify-mails.notify_from_name'), $shop->id, $Contract->ss_customer_id, $planData);
                        // logger('======= cancel NOtify mail response ======');
                        // logger($notifyMailRes);
                    }

                    if ($setting->notify_revoke && $data['type'] == 'cancelled-removeaccess') {
                        $notifyData = config('notify-mails.notify_revoke');

                        $newData = $this->fetchContractFormFields($Contract->id, $notifyData['body']);

                        $db_ss_plan = SsPlan::select('name')->where('id', $Contract->ss_plan_id)->first();
                        $planData['next_billing_date'] = $Contract->next_processing_date;
                        $planData['membership_plan'] = ($db_ss_plan) ? $db_ss_plan->name : '';

                        $notifyMailRes = sendMailH($notifyData['subject'], $newData, config('notify-mails.notify_from_email'), $setting->notify_email, config('notify-mails.notify_from_name'), $shop->id, $Contract->ss_customer_id, $planData);
                        // logger('======= access revoke NOtify mail response ======');
                        // logger($notifyMailRes);
                    }

                    if ($setting->membership_cancel_email_enabled) {
                        // logger("================ membership_cancel_email_enabled ==============");
                        $planData['renewal_date'] = date('d/m/Y', strtotime($Contract->next_order_date));
                        $customer = SsCustomer::where('shopify_customer_id', $Contract->shopify_customer_id)->first();
                        $email = SsEmail::where('shop_id', $shop->id)->where('category', 'cancelled_membership')->first();

                        $res = sendMailH($email->subject, $email->html_body, $setting->email_from_email, $customer->email, $setting->email_from_name, $shop->id, $customer->id, $planData);
                        // logger('======= nmembership_cancel_email_enabled mail response ======');
                        // logger($res);
                    }

                    $cancelReason = isset( $data['selectedReason']) ?  $data['selectedReason'] : '';
                    $this->saveCancellation($shop->id, $Contract->shopify_contract_id, $Contract->id, $user_type, $data['type'] , $cancelReason);

                    if ($user_type == 'customer' && @$data['selectedCancellation']) {
                        $this->saveContractCancelReason($shop->id, $data['selectedCancellation'], $data['otherReason']);
                    }

                    // Shopify Flow - Membership Cancelled
                    $ss_cotract_line_item = SsContractLineItem::where('ss_contract_id', $Contract->id)->first();
                    if ($ss_cotract_line_item) {
                        // logger(json_encode($data));
                        // $shop = getShopH();
                        $this->flowTrigger(
                            config('const.SHOPIFY_FLOW.MEMBERSHIP_CANCEL'),
                            env('APP_TRIGGER_URL'),
                            '
                                {
                                    \"customer_id\": ' . $Contract->shopify_customer_id . ',
                                    \"product_id\": ' . $ss_cotract_line_item->shopify_product_id . ',
                                    \"Contract ID\": \" ' . $Contract->shopify_contract_id . ' \" ,
                                    \"Access Removal Date\": \" \",
                                    \"Customer Tag\": \"' . $Contract->tag_customer . '\",
                                    \"Cancellation Reason\": \" \",
                                    \"Member Number\": ' . $Contract->member_number . '
                                }
                            ',
                            $user
                        );
                    } else {
                        logger('$ss_cotract_line_item :: Not found');
                    }
                }
            }
            if (@$data['type'] == 'reactive') {
                $next_date = $this->getSubscriptionTimeDate(date("Y-m-d", strtotime($data['reactiveDate'])), $shop->id);
                $Contract->update([
                    'status' => 'active', 'status_display' => 'Active', 'next_order_date' => $next_date, 'next_processing_date' => $next_date,
                ]);
                $Contract->save();
                $msg = 'Membership active successfully';

                $res = $this->updateShopifyTags($user, $Contract->shopify_customer_id, $Contract->tag_customer, 'customer');

                if ($user_type == 'user') {
                    $this->saveActivity($shop->user_id, $Contract->ss_customer_id, $Contract->id, $user_type, 'Membership was reactivated manually');
                }
            }
            if (@$data['type'] == 'edit_shipping_address') {


                $updateArray = [
                    'ship_firstName' => $data['contract']['ship_firstName'],
                    'ship_lastName' => $data['contract']['ship_lastName'],
                    'ship_company' => $data['contract']['ship_company'],
                    'ship_address1' => $data['contract']['ship_address1'],
                    'ship_address2' => $data['contract']['ship_address2'],
                    'ship_city' => $data['contract']['ship_city'],
                    'ship_province' => $data['contract']['ship_province'],
                    'ship_provinceCode' => isset($data['contract']['ship_provinceCode']) ? $data['contract']['ship_provinceCode'] : '',
                    'ship_zip' => $data['contract']['ship_zip'],
                    'ship_country' => $data['contract']['ship_country'],
                ];


                if (isset($data['contract']['ship_phone'])) { // This is added after - Some shops might not have this attribute so we are cross checking this to avoid errors.
                    $updateArray['ship_phone'] = $data['contract']['ship_phone'];
                }

                $Contract->update($updateArray);
                $msg = 'Address updated successfully';



                if ($user_type == 'user') {
                    $this->saveActivity($shop->user_id, $Contract->ss_customer_id, $Contract->id, $user_type, 'Membership shipping address was updated manually');
                }
            }
            if (@$data['type'] == 'skip_next_order') {

                $next_order_date = $Contract->next_order_date;
                $interval_count = $Contract->delivery_interval_count;
                $interval = $Contract->delivery_interval;

                $new_next_order = date('Y-m-d', strtotime($next_order_date . ' + ' . $interval_count . ' ' . $interval . 's'));

                $time = date('H:i:s', strtotime($next_order_date));
                $dateInMerchatTime = $this->getSubscriptionTimeDate(date(
                    "Y-m-d",
                    strtotime($new_next_order)
                ), $shop->id);

                $saveDate = date('Y-m-d', strtotime($new_next_order)) . ' ' . date('H:i:s', strtotime($dateInMerchatTime));
                $Contract->update([
                    'next_order_date' => $saveDate,
                    'next_processing_date' => $saveDate,
                ]);
                $Contract->save();
                $msg = 'Skip order successfully';

                if ($user_type == 'customer') {
                    $this->saveActivity($shop->user_id, $Contract->ss_customer_id, $Contract->id, $user_type, 'Customer skipped their next order');
                }
            }
            if (@$data['type'] == 'next_order_date' || (($dbNextOD != $contractNextOD) && @$data['type'] == 'all')) {
                $newOrderDate = ($user_type == 'user') ? $data['next_order_date'] : $data['contract']['next_order_date'];
                $merchant_next_date = $this->getSubscriptionTimeDate(date(
                    "Y-m-d",
                    strtotime($newOrderDate)
                ), $shop->id);

                $Contract->update([
                    'next_order_date' => $merchant_next_date,
                    'next_processing_date' => $merchant_next_date,
                    'error_state' => null,
                    'failed_payment_count' => 0,
                ]);
                $Contract->save();

                $activityDate = date("Y-m-d", strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($Contract->next_order_date)), $shop->id, date("H:i:s", strtotime($Contract->next_order_date)), 'UTC')));
                $activityMsg = 'Updated next billing date to ' . $activityDate;

                if ($user_type == 'user') {
                    $this->saveActivity($shop->user_id, $Contract->ss_customer_id, $Contract->id, $user_type, 'Membership’s next order date was set to ' . $activityDate . ' manually');
                }
            }
            if (@$data['type'] == 'updatePaymentDetailEmail') {
                $result = $this->createCustomerPaymentMethodSendUpdateEmail($shop->user_id, $Contract->shopify_contract_id);
                if ($user_type == 'user') {
                    $this->saveActivity($shop->user_id, $Contract->ss_customer_id, $Contract->id, $user_type, 'Email was sent to the customer to reset their payment information');
                }
            }
            if (@$data['type'] == 'remove-discount') {
                $this->subscriptionRemoveAutomaticDiscount($shop->user_id, $data['shopify_discount_id'], $Contract->shopify_contract_id);
            }

            // update contract in shopify
            if ($data['type'] == 'paused' || $data['type'] == 'resumed' || $data['type'] == 'cancelled' || $data['type'] == 'cancelled-removeaccess' || @$data['type'] == 'reactive' || @$data['type'] == 'edit_shipping_address') {
                if ($data['type'] == 'resumed') {
                    $type = ($data['type'] == 'resumed') ? 'active' : $data['type'];
                    $Contract->update(['status' => $type, 'status_display' => 'Active']);
                    // logger("Res Tag ------------------------------------------------");
                    $res = $this->updateShopifyTags(
                        $user,
                        $Contract->shopify_customer_id,
                        $Contract->tag_customer,
                        'customer',
                        'add'
                    );
                    // logger($res);
                }
                if ($data['type'] == 'resumed' || $data['type'] == 'reactive') {
                    $Contract->update(['status_billing' => null, 'failed_payment_count' => 0]);
                }
                $update = (@$data['type'] == 'edit_shipping_address') ? 'address' : 'status';
                if ($data['contract_id'] && $Contract->shopify_contract_id) {
                    $result = $this->updateSubscriptionContract($shop->user_id, $data['contract_id'], $update);
                    // logger("TESt ::");
                    // logger($result);
                } else {
                    $result = 'success';
                }
            }

            if ($data['type'] == "change_frequency") {
                $contract = SsContract::find($data['contract_id']);

                logger("billing_interval is an " . $data['billing_interval']);
                logger("billing_interval_count is an " . $data['billing_interval_count']);

                $contract->billing_interval = $data['billing_interval'];
                $contract->billing_interval_count = $data['billing_interval_count'];
                $contract->delivery_interval = $data['billing_interval'];
                $contract->delivery_interval_count = $data['billing_interval_count'];

                $contract->save();

                $update  = "change_frequency";
                $result = $this->updateSubscriptionContract($shop->user_id, $data['contract_id'], $update);
            }

            if ($data['type'] == 'skip_next_order' || $data['type'] == 'next_order_date' || @$data['type'] == 'reactive' || ($dbNextOD != $contractNextOD)) {
                if ($data['contract_id'] && $Contract->shopify_contract_id) {
                    $result = $this->subscriptionContractSetNextBillingDate($shop->user_id, $Contract->shopify_contract_id);
                }
            }
            if (@$data['changeProduct'] && @$data['newProductID']) {
                $checkContractExist = SsContract::whereNotNull('shopify_contract_id')->where('id', $data['contract_id'])->first();
                $SsContractLineItem = SsContractLineItem::where('ss_contract_id', $data['contract_id'])->first();
                if ($checkContractExist && $SsContractLineItem) {
                    $user = User::where('id', $shop->user_id)->first();
                    $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/' . $data['newProductID'] . '.json';
                    $response = $user->api()->rest('GET', $endPoint);

                    $getDataresult = $response['body']->container['product'];
                    $variants = $response['body']->container['product']['variants'];

                    $updateLine = SsContractLineItem::find($SsContractLineItem->id);
                    $updateLine->shopify_product_id = $getDataresult['id'];
                    $updateLine->title = $getDataresult['title'];
                    $updateLine->shopify_variant_id = $variants[0]['id'];
                    $updateLine->sku = $variants[0]['sku'];
                    $updateLine->shopify_variant_title = $variants[0]['title'];
                    $updateLine->requiresShipping = $variants[0]['requires_shipping'] ? 1 : 0;
                    $updateLine->save();
                    $getOrder = SsOrder::where('ss_contract_id', $data['contract_id'])->orderby('id', 'DESC')->first();
                    $draftId = $this->getSubscriptionDraft($user->id, $SsContractLineItem->shopify_contract_id);
                    if ($getOrder) {
                        $getOrder = '{
                            order(id: "gid://shopify/Order/' . $getOrder->shopify_order_id . '") {
                                    billingAddress {
                                    address1,
                                    address2,
                                    city,
                                    company,
                                    country,
                                    firstName,
                                    lastName,
                                    province,
                                    provinceCode,
                                    zip,
                                    countryCode,
                                    phone,
                                }
                            }
                        }';
                        $getBillingAddress = $this->graphQLRequest($user->id, $getOrder);
                        $getAddress = $getBillingAddress['body']->container['data']['order']['billingAddress'];
                        if ($draftId && $getAddress) {
                            $draftQuery = '
                                mutation{
                                subscriptionDraftUpdate(draftId: "' . $draftId . '",
                                input: {';
                            $draftQuery .= '
                                        deliveryMethod: {
                                        shipping: {
                                            address: {
                                                address1: "' . $getAddress['address1'] . '",
                                                address2: "' . $getAddress['address2'] . '",
                                                city: "' . $getAddress['city'] . '",
                                                company: "' . $getAddress['company'] . '",
                                                country: "' . $getAddress['country'] . '",
                                                firstName: "' . $getAddress['firstName'] . '",
                                                lastName: "' . $getAddress['lastName'] . '",
                                                province: "' . $getAddress['province'] . '",
                                                provinceCode: "' . $getAddress['provinceCode'] . '",
                                                zip: "' . $getAddress['zip'] . '",
                                                countryCode: ' . $getAddress['countryCode'] . ',
                                                phone: "' . $getAddress['phone'] . '"
                                            }
                                        }
                                    }
                                ';
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
                            $resultSubscriptionDraftContract = $this->graphQLRequest($user->id, $draftQuery);
                            $this->getReturnMessage($resultSubscriptionDraftContract, 'subscriptionDraftUpdate');
                        }

                        if ($draftId) {
                            $query = '
                            mutation{
                                subscriptionDraftLineUpdate(
                                        draftId: "' . $draftId . '",
                                        input: {
                                            productVariantId: "gid://shopify/ProductVariant/' . $variants[0]['id'] . '",
                                        },
                                        lineId: "gid://shopify/SubscriptionLine/' . $SsContractLineItem->shopify_line_id . '"
                                    ){
                                        userErrors {
                                        code
                                        field
                                        message
                                        }
                                    }
                                }';
                            $subscriptionDraftResult = $this->graphQLRequest($user->id, $query);
                            $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineUpdate');
                            if ($message == 'success') {
                                $this->commitDraft($user->id, $draftId);
                                if ($variants[0]['title'] == 'Default Title') {
                                    $this->saveActivity($shop->user_id, $checkContractExist->ss_customer_id, $checkContractExist->id, $user_type, 'Membership product was updated to ' . $getDataresult['title'] . '');
                                } else {
                                    $this->saveActivity($shop->user_id, $checkContractExist->ss_customer_id, $checkContractExist->id, $user_type, 'Membership product was updated to  ' . $getDataresult['title'] . " / " . $variants[0]['title'] . '');
                                }
                            }
                        }
                    }
                }
            }
            if ($result != '') {
                if ($result == 'success') {
                    DB::commit();
                    $msg = (@$data['type'] == 'updatePaymentDetailEmail') ? 'Email sent' : 'Membership updated';
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
            if ($success) {
            }
            if (@$data['mode'] == 'api') {
                $ret['data'] = $msg;
                $ret['isSuccess'] = $success;
                return $ret;
            } else {
                return response()->json(['data' => $msg, 'isSuccess' => $success], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  update =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }


    public function uppdateCustomerDetail(Request $request, $id)
    {

        try {
            $shop = getShopH();
            $user_type = 'user';
            $Contract = SsContract::find($id);

            $updateArray = [
                'ship_firstName' => $request->ship_firstName,
                'ship_lastName' => $request->ship_lastName,
                'ship_company' => $request->ship_company,
                'ship_address1' => $request->ship_address1,
                'ship_address2' => $request->ship_address2,
                'ship_city' => $request->ship_city,
                'ship_province' => $request->ship_province,
                'ship_provinceCode' => $request->ship_provinceCode,
                'ship_zip' => $request->ship_zip,
                'ship_country' => $request->ship_country,
            ];

            if (isset($request->ship_phone)) { // This is added after - Some shops might not have this attribute so we are cross checking this to avoid errors.
                $updateArray['ship_phone'] = $request->ship_phone;
            }
            $Contract->update($updateArray);
            $msg = 'Address updated successfully';

            if ($user_type == 'user') {
                $this->saveActivity($shop->user_id, $Contract->ss_customer_id, $Contract->id, $user_type, 'Membership shipping address was updated manually');
            }

            $update = 'address';
            if ($id && $Contract->shopify_contract_id) {
                $result = $this->updateSubscriptionContract($shop->user_id, $id, $update);
            }

            return response()->json(['msg' => $msg]);
        } catch (Exception $e) {
            logger("============= ERROR ::  uppdateCustomerDetail =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $type
     * @param  string  $s
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    // public function export($shopID, $type = 'active', $p = 'All Plans', $s = '')
    // {
    //     try {

    //         $shop = Shop::find($shopID);
    //         ExportSubscribersCsvJob::dispatch($type, $s, $p, $shopID);
    //         return response()->json(["data" => $shop, "is_success" => true]);
    //     } catch (Exception $e) {
    //         logger($e);
    //         return $e;
    //     }
    // }

    public function export($shopID, $email, $type = 'active', $p = 'All Plans', $lp='' , $s = '')
    {
        try {
            $shop = Shop::find($shopID);
            ExportSubscribersCsvJob::dispatch($type, $s, $p,$lp ,$shopID ,trim($email))->onQueue('UpdateServer');
            return response()->json(["data" => $shop, "is_success" => true]);
        } catch (Exception $e) {
            logger($e);
            return $e;
        }
    }

    /**
     * @param  SubscriberRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveComment(SubscriberRequest $request)
    {
        try {
            $shop = getShopH();
            $data = $request->data;
            $user = Auth::user();

            $activity = $this->saveActivity($shop->user_id, $data['customer_id'], $data['id'], 'user', $data['msg']);
            $activityLogs = $this->getActivity([$activity], $shop->id, $shop->iana_timezone);

            $res = [
                'status' => true,
                'msg' => 'Activity saved!',
                'data' => $activityLogs[0],
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  saveComment =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $subscriber
     * @return false
     */
    public function orderSubscriber($subscriber, $type, $user)
    {
        try {
            if (!empty($subscriber)) {
                if ($type == 'all') {
                    $contracts = SsContract::with('LineItems')->where('ss_customer_id', $subscriber->id)->get()->toArray();
                } else {
                    $contracts = SsContract::with('LineItems')->where('ss_customer_id', $subscriber->id)->whereIn('status', ['active', 'paused'])->get()->toArray();
                }
                $c['active'] = [];
                $c['paused'] = [];
                $c['failed'] = [];
                $c['expired'] = [];
                $c['cancelled'] = [];
                if (count($contracts) > 0) {
                    foreach ($contracts as $key => $val) {
                        $val['created_at'] = date("M d, Y", strtotime($val['created_at']));
                        $val['next_order_date'] = date("M d, Y", strtotime($val['next_order_date']));
                        $val['line_items'] = (!empty($val)) ? $this->getLineItemsData($val, $user) : [];
                        if ($val['status'] == 'active') {
                            $c['active'][] = $val;
                        } else {
                            if ($val['status'] == 'paused') {
                                $c['paused'][] = $val;
                            } else {
                                if ($val['status'] == 'failed') {
                                    $c['failed'][] = $val;
                                } else {
                                    if ($val['status'] == 'expired') {
                                        $c['expired'][] = $val;
                                    } else {
                                        if ($val['status'] == 'cancelled') {
                                            $c['cancelled'][] = $val;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $cnt = array_merge($c['active'], $c['paused']);
                $cnt = array_merge($cnt, $c['failed']);
                $cnt = array_merge($cnt, $c['expired']);
                $cnt = array_merge($cnt, $c['cancelled']);
                $subscriber['Contracts'] = $cnt;
                $subscriber['Activities'] = $this->getActivity($subscriber['ActivityLog']);
            }
            return $subscriber;
        } catch (\Exception $e) {
            logger("============= ERROR ::  orderSubscriber =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function billingAttempt($attempts, $shopID)
    {
        $shop = Shop::where('id',$shopID)->first();
        $attempt = [];
        if (count($attempts) > 0) {
            foreach ($attempts as $key => $value) {


                $contract = SsContract::where('shopify_contract_id', $value->shopify_contract_id)->first();
                $order_sum = 0;
                $qun = 1;

                // logger("value is an ====================================================> " . $value);

                if ($value->shopify_order_id) {

                    // $order_sum = SsOrder::where('ss_contract_id',$contract->id)->sum('order_amount');
                    $order = SsOrder::where('shopify_order_id', $value->shopify_order_id)->first();

                    if ($order) {
                        $order_sum = $order->order_amount;
                    }
                    $quantity = '{
                        order(id: "gid://shopify/Order/'. $value->shopify_order_id .'") {
                            subtotalLineItemsQuantity
                        }
                    }';
                    $getQuantity = $this->graphQLRequest($shop->user_id, $quantity);
                    $qun = isset($getQuantity['body']['data']['order']['subtotalLineItemsQuantity']) ? $getQuantity['body']['data']['order']['subtotalLineItemsQuantity'] : 1;
                }
                $attempt[$key]['status'] = $value->status;
                $attempt[$key]['completedAt'] = date('M d, Y', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($value->completedAt)), $shopID, date('H:i:s', strtotime($value->completedAt)))));
                $attempt[$key]['attempted_on'] = date('M d, Y H:i', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($value->created_at)), $shopID, date('H:i:s', strtotime($value->created_at)))));
                $attempt[$key]['created_at'] = date('M d, Y', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($value->created_at)), $shopID, date('H:i:s', strtotime($value->created_at)))));
                $attempt[$key]['shopify_order_id'] = $value->shopify_order_id;
                $attempt[$key]['errorMessage'] = $value->errorMessage;
                $attempt[$key]['total'] = ( $order_sum  * $qun) ;

                // $attempt[$key]['quantity'] = $qun;

                $ss_order = SsOrder::where('shopify_order_id', $value->shopify_order_id)->first();
                $attempt[$key]['shopify_order_name'] = (@$ss_order->shopify_order_name) ? $ss_order->shopify_order_name : '';
            }
        }

        // logger("TOTAL===");
        // logger($attempt);
        $res = paginateH($attempts);
        $res['data'] = $attempt;
        return $res;
    }

    public function getFulfillments($user_id, $bill_number)
    {
        $fullfilment = [];
        $order = $this->getPrepaidFulfillments($user_id, $bill_number);
        if (!empty($order)) {
            foreach ($order as $key => $value) {
                $node = $value['node'];
                $node['fulfillAt'] = date('M d, Y', strtotime($node['fulfillAt']));
                $fullfilment[$key] = $node;
            }
        }
        return $fullfilment;
    }

    public function getActivity($ActivityLog, $shop_id, $timezone)
    {
        try {
            $default_timezone = date_default_timezone_get();

            $activity = [];
            if (!empty($ActivityLog)) {

                date_default_timezone_set($timezone);
                $curr_date = date('Y-m-d');
                date_default_timezone_set($default_timezone);

                foreach ($ActivityLog as $key => $val) {
                    $merchant_time = $this->getSubscriptionTimeDate(date('Y-m-d', strtotime($val['created_at'])), $shop_id, date('H:i:s', strtotime($val['created_at'])));

                    // $activity[$key]['create'] = ( $curr_date > $val['created_at'] ) ? date("F d", strtotime($val['created_at'])) : 'Today';
                    $activity[$key]['create'] = ($curr_date > $merchant_time) ? date("F d", strtotime($merchant_time)) : 'Today';
                    // $activity[$key]['time'] = date("H:i A", strtotime($val['created_at']));
                    $activity[$key]['time'] = date("H:i A", strtotime($merchant_time));
                    $activity[$key]['message'] = $val['message'];
                    $activity[$key]['user_type'] = $val['user_type'];
                    $activity[$key]['user_name'] = $val['user_name'];
                }
            }
            return $activity;
        } catch (\Exception $e) {
            logger("============= ERROR ::  getActivity =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function saveLineItem(Request $request)
    {
        try {
            DB::beginTransaction();
            $shop = getShopH();
            $data = $request->data;
            $contract_id = $data['contract_id'];
            $contract = SsContract::find($contract_id);
            $variants = $data['resource'];
            foreach ($variants as $key => $val) {
                $result = [];

                if ($contract->is_multicurrency) {
                    $val['price'] = calculateCurrency($shop->currency, $contract->currency_code, $val['price']);
                    $val['final_amount'] = $val['price'];
                    $currency = $contract->currency_code;
                    $currencySymbol = currencyH($currency);
                } else {
                    $currency = $shop->currency;
                    $currencySymbol = $shop->currency_symbol;
                }

                $lineItem = new SsContractLineItem;
                $lineItem->shopify_contract_id = $contract->shopify_contract_id;
                $lineItem->ss_contract_id = $contract_id;
                $lineItem->user_id = $shop->user_id;
                $lineItem->shopify_product_id = $val['product_id'];
                $lineItem->shopify_variant_id = $val['variant_id'];
                $lineItem->sku = $val['sku'];
                $lineItem->shopify_variant_image = $val['shopify_variant_image'];
                $lineItem->shopify_variant_title = $val['sku'];
                $lineItem->title = $val['product_title'];
                $lineItem->price = $val['price'];
                $lineItem->currency = $currency;
                $lineItem->currency_symbol = $currencySymbol;
                $lineItem->discount_type = '%';
                $lineItem->discount_amount = 0;
                $lineItem->final_amount = $val['final_amount'];
                $lineItem->price_discounted = $val['final_amount'];
                $lineItem->quantity = 1;
                $lineItem->save();

                $result = $this->subscriptionDraftLineAdd($shop->user_id, $lineItem);

                if (gettype($result) == 'array') {
                    $lineItem->shopify_line_id = str_replace('gid://shopify/SubscriptionLine/', '', $result['id']);
                    // $lineItem->shopify_line_id = $result['id'];
                    $lineItem->selling_plan_id = $result['sellingPlanId'];
                    $lineItem->selling_plan_name = $result['sellingPlanName'];
                    $lineItem->save();

                    DB::commit();
                    $msg = 'Subscription updated';
                    $success = true;
                } else {
                    DB::rollBack();
                    $msg = $result;
                    $success = false;
                }
            }

            return response()->json(['data' => $msg, 'isSuccess' => $success], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  saveLineItem =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $contract
     * @return array
     */
    public function getLineItemsData($contract, $user)
    {
        try {
            $res = [];
            //  $lineItems = SsContractLineItem::where('ss_contract_id', $val['id'])->get()->toArray();
            $lineItems = $contract['LineItems'];
            if (!empty($lineItems)) {
                foreach ($lineItems as $lkey => $lval) {
                    $lval = $lval->toArray();
                    $sh_product = $this->getShopifyProduct($lval['shopify_product_id'], $lval['shopify_variant_id'], $user);
                    $sh_variants = $this->getShopifyVariants($lval['shopify_product_id'], $user);
                    $var['sh_variants'] = $sh_variants;
                    $var['selected_variant'] = $lval['shopify_variant_id'];
                    $var['sh_product'] = $sh_product;
                    $res[$lkey] = array_merge($lval, $var);
                }
            }
            return $res;
        } catch (\Exception $e) {
            logger("============= ERROR ::  getLineItemsData =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $product_id
     * @param $variant_id
     * @return mixed
     */
    public function getShopifyProduct($product_id, $variant_id, $user)
    {
        $shop = $user;
        $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/' . $product_id . '.json';
        $parameter['fields'] = 'id,title,image,handle';
        $result = $this->request('GET', $endPoint, $parameter, $shop->id);
        $variant = $this->getShopifyVariant($variant_id, $user);
        if (!$result['errors']) {
            $sh_product = $result['body']->container['product'];
            $res['product_name'] = $sh_product['title'];
            $res['product_image'] = (@$sh_product['image']['src']) ? $sh_product['image']['src'] : noImagePathH();
            $res['variant_name'] = $variant['variant_name'];
            $res['sku'] = $variant['sku'];
            $res['handle'] = $sh_product['handle'];
        } else {
            $res['product_name'] = '';
            $res['product_image'] = '';
            $res['variant_name'] = '';
            $res['sku'] = '';
            $res['handle'] = '';
        }
        return $res;
    }

    /**
     * Get shopify variant sku,image
     * @param $variant_id
     * @return mixed
     */
    public function getShopifyVariant($variant_id, $user)
    {
        $shop = $user;
        $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/variants/' . $variant_id . '.json';
        $parameter['fields'] = 'id,title,sku';
        $result = $this->request('GET', $endPoint, $parameter, $shop->id);
        $res['variant_name'] = '';
        $res['sku'] = '';
        if (!$result['errors']) {
            $sh_variant = $result['body']->container['variant'];
            $res['variant_name'] = $sh_variant['title'];
            $res['sku'] = $sh_variant['sku'];
        }
        return $res;
    }

    /**
     * Get shopify variant sku,image
     * @param $variant_id
     * @return mixed
     */
    public function getShopifyVariants($product_id, $user)
    {
        $shop = $user;
        $endPoint = '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products/' . $product_id . '/variants.json';
        $parameter['fields'] = 'id,title,sku, price';
        $result = $this->request('GET', $endPoint, $parameter, $shop->id);
        $res['variant_id'] = '';
        $res['title'] = '';
        $res['sku'] = '';
        $res['price'] = '';
        if (!$result['errors']) {
            $sh_variant = $result['body']->container['variants'];
        }
        return $sh_variant;
    }
    /**
     * @param $subscriber
     * @return mixed
     */
    public function getOtherContracts($subscriber)
    {
        try {
            $contracts = SsContract::select('id', 'status', 'created_at', 'updated_at')->where('ss_customer_id', $subscriber->ss_customer_id)->where('id', '!=', $subscriber->id)->where('shop_id', $subscriber->shop_id)->where('user_id', $subscriber->user_id)->get()->toArray();
            return $contracts;
        } catch (\Exception $e) {
            logger("============= ERROR ::  getOtherContracts =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getCountry($country)
    {
        //        $data['countries'] = Country::select('name')->get()->toArray();
        //        $data['states'] = State::select('name')->get()->toArray();
        $data['countries'] = countryH();
        $data['states'] = stateFromCountryH(array_search($country, $data['countries']));
        return response()->json(['data' => $data], 200);
    }

    public function getAvailableDates($anchorDateType, $anchorDay, $anchorMonth, $nextOrderDate)
    {
        if ($anchorDateType == 'WEEKDAY') {
            $range = CarbonPeriod::create($nextOrderDate, Carbon::now()->addWeek(10));
            $availableDates = [];
            foreach ($range as $carbon) { //This is an iterator
                if ($carbon->dayOfWeekIso == $anchorDay) {
                    $availableDates[] = date('M d, Y', strtotime($carbon));
                }
            }
        } elseif ($anchorDateType == 'MONTHDAY') {
            $anchorDay == 31
                ? $endDate = Carbon::now()->addMonth(18)->lastOfMonth()
                : $endDate = Carbon::now()->addMonth(10)->lastOfMonth();
            $period = CarbonPeriod::create($nextOrderDate, $endDate);

            $availableDates = [];
            foreach ($period as $carbon) {
                if ($carbon->day == $anchorDay) {
                    $availableDates[] = date('M d, Y', strtotime($carbon));
                }
            }
        } else {
            $period = CarbonPeriod::create($nextOrderDate, Carbon::create($nextOrderDate)->addYears(9));
            $availableDates = [];
            foreach ($period as $carbon) {
                if ($carbon->day == $anchorDay && $carbon->month == $anchorMonth) {
                    $availableDates[] = date('M d, Y', strtotime($carbon));
                }
            }
        }

        return $availableDates;
    }


    public function billingattempts($id, Request $request)
    {
        try {
            $shop = getShopH();
            $user = User::where('id', $shop->user_id)->first();
            $user = Auth::user();

            $subscriber = SsContract::with('BillingAttempt')->where('shop_id', $shop['id'])->where('id', $id)->first();
            $data = $this->billingAttempt($subscriber->BillingAttempt()->paginate(5, ['*'], 'page', $request->page), $shop->id);
            return response()->json(['data' => $data], 200);
        } catch (Exception $e) {
            logger("============= ERROR ::  Billing Attempts  =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function callFlowTrigger(Request $request){
        logger("=================== START :: callFlowTrigger ================");
        if($request->action == 'new_member'){
            $user = User::find($request->uid);
            $this->flowTrigger(
              config('const.SHOPIFY_FLOW.NEW_MEMBERSHIP'),
              env('APP_URL'),
              '{\"customer_id\": ' . $request->customer_id . ',\"order_id\": ' . $request->order_id . ',\"product_id\": ' . $request->product_id . ',\"Customer Tag\": \"' . $request->tag_customerr . '\",\"Order Tag\": \"' . $request->tag_order . '\",\"Next Billing Date\": \"' . $request->next_processing_date . '\",\"Member Number\": ' . $request->member_number . ',\"Contract ID\": ' . $request->shopify_contract_id . '}',
              $user
          );
        }else if($request->action == 'payment_success'){
            $user = User::find($request->uid);
            $this->flowTrigger(
                config('const.SHOPIFY_FLOW.PAYMENT_SUCCESS'),
                env('APP_URL'),
                '
                    {
                        \"customer_id\": ' . $request->customer_id . ',
                        \"order_id\": ' . $request->order_id . ',
                        \"product_id\": ' . $request->product_id . ',
                        \"Last Order\": ' . $request->last_order . ' ,
                        \"Membership Order Count\": ' . $request->order_count . ',
                        \"Next Billing Date\": \"' . $request->next_processing_date . '\",
                        \"Member Number\": ' . $request->member_number . ',
                        \"Contract ID\": ' . $request->shopify_contract_id . ',
                        \"Customer Tag\": \"' . $request->tag_customer . '\",
                        \"Order Tag\": \"' . $request->order_tag . '\"
                    }
                ',
                $user
            );
        }else if($request->action == 'payment_fail'){
            $user = User::find($request->uid);
            $this->flowTrigger(
                config('const.SHOPIFY_FLOW.PAYMENT_FAIL'),
                env('APP_URL'),
                '
                    {
                        \"customer_id\": ' . $request->customer_id . ',
                        \"product_id\": ' . $request->product_id . ',
                        \"Next Billing Attempt\": \"' . $request->next_processing_date . '\",
                        \"Final Attempt\": ' . $request->condition . ',
                        \"Failure Count\": ' . $request->failed_payment_count . '
                    }
                ',
                $user
            );
        }
        logger("=================== END :: callFlowTrigger ================");
    }
}
