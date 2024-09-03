<?php

namespace App\Listeners;

use App\Events\CheckSubscriptionContract;
use App\Models\Shop;
use App\Models\SsBillingAttempt;
use App\Models\SsContract;
use App\Models\SsAnswer;
use App\Models\SsContractLineItem;
use App\Models\SsEmail;
use App\Models\SsPlan;
use App\Models\SsSetting;
use App\Models\SsWebhook;
use App\Models\SsCustomer;
use App\Models\SsStoreCredit;
use App\Models\SsStoreCreditRules;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Osiset\ShopifyApp\Storage\Models\Plan;

class SubscriptionContract
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
     * @param  CheckSubscriptionContract  $event
     * @return void
     */
    public function handle(CheckSubscriptionContract $event)
    {
        try {
            $ids = $event->ids;
            $user = User::find($ids['user_id']);
            $shop = Shop::find($ids['shop_id']);
            $this->statuswebhook_id = $ids['webhook_id'];
            // $webhookResonse = SsWebhook::find($ids['webhook_id']);
            // if ($webhookResonse) {
            $data = json_decode($ids['payload']);
            logger('========== Listener:: SubscriptionContract :: Webhook :: ' . $ids['webhook_id'] . ' ==> shopify_id :: ' . $data->id . '==========');
            $shopify_contract_id = str_replace('gid://shopify/SubscriptionContract/', '', $data->admin_graphql_api_id);
            $is_exist_db_contract = DB::table('ss_contracts')->where('shopify_contract_id', $shopify_contract_id)->where('shop_id', $shop->id)->first();
            // logger('is_exist_db_contract :: ' . json_encode($is_exist_db_contract));
            if (!$is_exist_db_contract) {

                // // create billing attempt if migrated contract
                // if($data->origin_order_id == '' || $data->origin_order_id == null){
                //     $mBillingAttempt = $this->createBillingAttemptAfterMigration($shopify_contract_id, $user->id);
                //     if($mBillingAttempt['isSuccess']){
                //         $data->origin_order_id = $mBillingAttempt['order_id'];
                //     }
                // }

                // update or create customer in db
                $shopify_customer_id = $data->customer_id;
                $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/' . $shopify_customer_id . '.json';

                $result = $user->api()->rest('GET', $endPoint);
                if (!$result['errors']) {
                    $sh_customer = $result['body']['customer'];
                    $is_existcustomer = SsCustomer::where('shop_id', $ids['shop_id'])->where(
                        'shopify_customer_id',
                        $shopify_customer_id
                    )->first();
                    $customer = ($is_existcustomer) ? $is_existcustomer : new SsCustomer;
                    $customer->shop_id = $ids['shop_id'];
                    $customer->shopify_customer_id = @$sh_customer->id;
                    $customer->active = 1;
                    $customer->first_name = @$sh_customer->first_name;
                    $customer->last_name = @$sh_customer->last_name;
                    $customer->email = @$sh_customer->email;
                    $customer->phone = @$sh_customer->phone;
                    $customer->notes = @$sh_customer->note;
                    $customer->total_orders = ($is_existcustomer) ? $is_existcustomer->total_orders + 1 : 1;
                    $customer->total_spend_currency = @$sh_customer->currency;
                    $customer->currency_symbol = currencyH(@$sh_customer->currency);
                    $customer->avg_order_value = (@$sh_customer->orders_count > 0) ? preg_replace('/[^0-9 .]/s', '', number_format((@$sh_customer->total_spent / @$sh_customer->orders_count), 2)) : 0;
                    $customer->date_first_order = ($is_existcustomer && $is_existcustomer->date_first_order) ? $is_existcustomer->date_first_order : date('Y-m-d H:i:s');
                    $customer->save();

                    // get subscription contract from shopify
                    $ssContractQuery = $this->subscriptionContractLineItems($data->admin_graphql_api_id);
                    $ssContractResult = $this->graphQLRequest($user->id, $ssContractQuery);

                    if (!$ssContractResult['errors']) {

                        $subscriptionContract = $ssContractResult['body']->container['data']['subscriptionContract'];
                        // logger("******************************** CONTRACT RESULT IS AN ********************************");
                        // logger($subscriptionContract);
                        // logger("********************************* CONTRAC END ********************************");
                        $sh_lines = (@$subscriptionContract['lines']['edges']) ? $subscriptionContract['lines']['edges'] : [];
                        $sh_cpm = (@$subscriptionContract['customerPaymentMethod']['instrument']) ? $subscriptionContract['customerPaymentMethod']['instrument'] : [];
                        $sh_deliveryMethod = (@$subscriptionContract['deliveryMethod']) ? $subscriptionContract['deliveryMethod'] : [];

                        // add contract
                        $billing_policy = $data->billing_policy->interval_count . $data->billing_policy->interval;
                        $delivery_policy = $data->delivery_policy->interval_count . $data->delivery_policy->interval;

                        // update member number in shop table
                        $shop->member_number = $shop->member_number + 1;
                        $shop->save();

                        $contract = new SsContract;
                        $contract->shop_id = $ids['shop_id'];
                        $contract->user_id = $user->id;
                        $contract->shopify_contract_id = $data->id;
                        $contract->shopify_customer_id = $shopify_customer_id;
                        $contract->ss_customer_id = $customer->id;
                        $contract->origin_order_id = $data->origin_order_id;
                        $contract->status = $data->status;

                        // $addtime = $this->getSubscriptionTime($shop->id);
                        // Assuming this is regular subscription
                        // without anchor and cut-off days

                        $nextOrderDate = $this->getSubscriptionTimeDate(date(
                            "Y-m-d",
                            strtotime($subscriptionContract['nextBillingDate'])
                        ), $shop->id);
                        // $utc_next_date = $this->getSubscriptionTimeDate(date("Y-m-d",
                        //    strtotime($merchant_next_date)), $shop->id, date("H:i:s",
                        //    strtotime($merchant_next_date)), 'UTC');
                        // $next_order_date = date("Y-m-d H:i:s",
                        // strtotime($subscriptionContract['nextBillingDate']));

                        $sellingPlanId = $subscriptionContract['lines']['edges'][0]['node']['sellingPlanId'];
                        $sellingPlanId = str_replace('gid://shopify/SellingPlan/', '', $sellingPlanId);

                        $sellingPlan = DB::table('ss_plans')->where([
                            ['shop_id', $shop->id],
                            ['user_id', $user->id],
                            ['shopify_plan_id', $sellingPlanId]
                        ])->first();

                        if (isset($sellingPlan->delivery_cutoff) || isset($sellingPlan->billing_anchor_type) && !$db_plan->trial_available) {
                            $nextOrderDate = $this->calculateNextOrderDate($sellingPlan, $shop);
                            // logger("----- Selling Plan ----------------->");
                        }

                        // Add next order date to contract
                        $contract->next_order_date = $nextOrderDate;
                        $contract->next_processing_date = $nextOrderDate;

                        $deliveryAnchors = $subscriptionContract['deliveryPolicy']['anchors'];
                        $billingAnchors = $subscriptionContract['billingPolicy']['anchors'];

                        $contract->is_prepaid = strcmp($billing_policy, $delivery_policy);
                        $contract->prepaid_renew = 0;
                        $contract->last_billing_order_number = $data->origin_order_id;

                        $contract->billing_interval = (@$data->billing_policy->interval) ? $data->billing_policy->interval : '';
                        $contract->billing_interval_count = (@$data->billing_policy->interval_count) ? $data->billing_policy->interval_count : 0;
                        $contract->billing_min_cycles = (@$data->billing_policy->min_cycles) ? $data->billing_policy->min_cycles : 0;
                        $contract->billing_max_cycles = (@$data->billing_policy->max_cycles) ? $data->billing_policy->max_cycles : 0;
                        $contract->billing_anchor_day = (!empty($billingAnchors)) ? $billingAnchors[0]['day'] : null;
                        $contract->billing_anchor_type = (!empty($billingAnchors)) ? $billingAnchors[0]['type'] : null;
                        $contract->billing_anchor_month = (!empty($billingAnchors)) ? $billingAnchors[0]['month'] : null;

                        $contract->delivery_anchor_day = (!empty($deliveryAnchors)) ? $deliveryAnchors[0]['day'] : null;
                        $contract->delivery_anchor_type = (!empty($deliveryAnchors)) ? $deliveryAnchors[0]['type'] : null;
                        $contract->delivery_anchor_month = (!empty($deliveryAnchors)) ? $deliveryAnchors[0]['month'] : null;

                        $contract->delivery_intent = (!empty($deliveryAnchors)) ? 'A fixed day each delivery cycle' : 'The initial day of purchase';
                        $contract->delivery_interval = $data->delivery_policy->interval;
                        $contract->delivery_interval_count = $data->delivery_policy->interval_count;

                        $contract->delivery_price = (@$subscriptionContract['deliveryPrice']['amount']) ? $subscriptionContract['deliveryPrice']['amount'] : 0;
                        $contract->delivery_price_currency_symbol = currencyH($subscriptionContract['deliveryPrice']['currencyCode']);

                        $contract->currency_code = $data->currency_code;
                        $contract->lastPaymentStatus = $subscriptionContract['lastPaymentStatus'];
                        $contract->order_count = 1;

                        // store payment method details

                        $payment_method = '';

                        if (!empty($sh_cpm)) {
                            $payment_method = (@$sh_cpm['source'] && @$sh_cpm['source'] != 'remote') ? $sh_cpm['source'] : 'credit_card';
                            $payment_method = $sh_cpm['__typename'] == 'CustomerPaypalBillingAgreement' ? 'paypal' : $payment_method;
                            $payment_method = $sh_cpm['__typename'] == 'CustomerShopPayAgreement' ? 'shop_pay' : $payment_method;
                        }
                        $contract->payment_method = $payment_method;
                        $contract->paypal_account = (@$sh_cpm['paypalAccountEmail']) ? $sh_cpm['paypalAccountEmail'] : '';
                        $contract->paypal_inactive = (@$sh_cpm['paypal_inactive']) ? $sh_cpm['paypal_inactive'] : '';
                        $contract->paypal_isRevocable = (@$sh_cpm['paypal_isRevocable']) ? $sh_cpm['paypal_isRevocable'] : false;

                        $contract->cc_id = (@$subscriptionContract['customerPaymentMethod']['id']) ? str_replace(
                            'gid://shopify/CustomerPaymentMethod/',
                            '',
                            $subscriptionContract['customerPaymentMethod']['id']
                        ) : null;
                        $contract->cc_source = (@$sh_cpm['source'] && @$sh_cpm['source'] != 'remote') ? $sh_cpm['source'] : '';
                        $contract->cc_brand = (@$sh_cpm['brand']) ? $sh_cpm['brand'] : '';
                        $contract->cc_expiryMonth = (@$sh_cpm['expiryMonth']) ? $sh_cpm['expiryMonth'] : 0;
                        $contract->cc_expires_soon = (@$sh_cpm['expiresSoon']) ? $sh_cpm['expiresSoon'] : 0;
                        $contract->cc_expiryYear = (@$sh_cpm['expiryYear']) ? $sh_cpm['expiryYear'] : 0;
                        $contract->cc_firstDigits = (@$sh_cpm['firstDigits']) ? $sh_cpm['firstDigits'] : 0;
                        $contract->cc_lastDigits = (@$sh_cpm['lastDigits']) ? $sh_cpm['lastDigits'] : 0;
                        $contract->cc_maskedNumber = (@$sh_cpm['maskedNumber']) ? $sh_cpm['maskedNumber'] : '';
                        $contract->cc_name = (@$sh_cpm['name']) ? $sh_cpm['name'] : '';

                        $ship_address = (@$sh_deliveryMethod['address']) ? $sh_deliveryMethod['address'] : [];

                        $contract->ship_company = (@$ship_address['company']) ? $ship_address['company'] : '';
                        $contract->ship_firstName = (@$ship_address['firstName']) ? $ship_address['firstName'] : '';
                        $contract->ship_lastName = (@$ship_address['lastName']) ? $ship_address['lastName'] : '';
                        $contract->ship_provinceCode = (@$ship_address['provinceCode']) ? $ship_address['provinceCode'] : '';
                        $contract->ship_name = (@$ship_address['name']) ? $ship_address['name'] : '';
                        $contract->ship_address1 = (@$ship_address['address1']) ? $ship_address['address1'] : '';
                        $contract->ship_address2 = (@$ship_address['address2']) ? $ship_address['address2'] : '';
                        $contract->ship_city = (@$ship_address['city']) ? $ship_address['city'] : '';
                        $contract->ship_province = (@$ship_address['province']) ? $ship_address['province'] : '';
                        $contract->ship_zip = (@$ship_address['zip']) ? $ship_address['zip'] : '';
                        $contract->ship_country = (@$ship_address['country']) ? $ship_address['country'] : '';
                        $contract->ship_phone = (@$ship_address['phone']) ? $ship_address['phone'] : '';

                        $ship_option = (@$sh_deliveryMethod['shippingOption']) ? $sh_deliveryMethod['shippingOption'] : [];

                        $contract->shipping_code = (@$ship_option['code']) ? $ship_option['code'] : '';
                        $contract->shipping_description = (@$ship_option['description']) ? $ship_option['description'] : '';
                        $contract->shipping_presentmentTitle = (@$ship_option['presentmentTitle']) ? $ship_option['presentmentTitle'] : '';
                        $contract->shipping_title = (@$ship_option['title']) ? $ship_option['title'] : '';
                        $contract->failed_payment_count = 0;
                        $contract->is_multicurrency = ($contract->currency_code != $shop->currency) ? true : false;

                        // START :: Update member number in contract
                        $domain = $shop->myshopify_domain;
                        $domain = str_replace('-', '_', $domain);
                        $domain = str_replace('.', '_', $domain);

                        // $LastMembernumber = SsContract::select('member_number')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
                        $LastMembernumber = DB::table('ss_contracts')->select('member_number')->where('user_id', $user->id)->latest()->first();

                        if ($LastMembernumber) {
                            if (array_key_exists($domain, config('const.PREVENT_NUMBER_FOR_MEMBER'))) {
                                $const = 'const.PREVENT_NUMBER_FOR_MEMBER';

                                $contract->member_number = (in_array(($LastMembernumber->member_number + 1), config("$const.$domain"))) ? $LastMembernumber->member_number + 2 : $LastMembernumber->member_number + 1;
                            } else {
                                $contract->member_number = $LastMembernumber->member_number + 1;
                            }
                        } else {
                            $contract->member_number = $shop->member_number;
                        }
                        // END :: Update member number in contract

                        $contract->is_migrated = (!$contract->origin_order_id);
                        $contract->save();
                        // add contract line items

                        $is_added_cutoff = false;
                        if (is_array($sh_lines) && !empty($sh_lines)) {
                            foreach ($sh_lines as $key => $value) {
                                $node = $value['node'];
                                $db_plan = DB::table('ss_plans')->where('shop_id', $shop->id)->where(
                                    'user_id',
                                    $user->id
                                )->where(
                                    'shopify_plan_id',
                                    str_replace('gid://shopify/SellingPlan/', '', $node['sellingPlanId'])
                                )->first();
                                if (count($node['customAttributes']) > 0) {
                                    foreach ($node['customAttributes'] as $attributeKey => $attributeObj) {
                                        if ($attributeObj['key'] == 'customer_tag') {
                                            $contract->tag_customer = $attributeObj['value'];
                                            $contract->save();
                                        }
                                    }
                                }
                                $pricingPolicy = (gettype($node['pricingPolicy'] == null)) ? $node['currentPrice'] : $node['pricingPolicy'];
                                $pricingPolicyAmount = (gettype($node['pricingPolicy'] == null)) ? $pricingPolicy['amount'] : $pricingPolicy['basePrice']['amount'];
                                if ($db_plan) {
                                    $contract->ss_plan_id = $db_plan->id;
                                    // $contract->on_trial = ($db_plan->trial_available);
                                    $contract->is_set_min = $db_plan->is_set_min;
                                    $contract->is_set_max = $db_plan->is_set_max;
                                    $contract->trial_available = $db_plan->trial_available;
                                    $contract->pricing2_after_cycle = $db_plan->pricing2_after_cycle;
                                    $contract->pricing_adjustment_value = (gettype($node['pricingPolicy'] == null))  ? $node['currentPrice']['amount'] : $node['pricingPolicy']['cycleDiscounts'][0]['computedPrice']['amount'];
                                    $contract->pricing2_adjustment_value = $db_plan->pricing2_adjustment_value;
                                    $contract->is_onetime_payment = $db_plan->is_onetime_payment;

                                    if ($db_plan->trial_available && $db_plan->trial_days) {
                                        $upcoming_order_date = Carbon::now()->addDays($db_plan->trial_days);
                                        $nextOrderDate = $this->getSubscriptionTimeDate(date(
                                            "Y-m-d",
                                            strtotime($upcoming_order_date)
                                        ), $shop->id);
                                        $contract->next_order_date = $nextOrderDate;
                                        $contract->next_processing_date = $nextOrderDate;
                                    }
                                    $contract->save();

                                    $db_plan_group = DB::table('ss_plan_groups')->select('id', 'tag_customer', 'tag_order')->where('shop_id', $shop->id)->where('user_id', $user->id)->where('id', $db_plan->ss_plan_group_id)->first();

                                    if ($db_plan_group) {
                                        $contract->ss_plan_groups_id = $db_plan_group->id;
                                        $contract->tag_customer = $db_plan_group->tag_customer;
                                        $contract->tag_order = $db_plan_group->tag_order;
                                        $contract->save();

                                        // Store Credits........
                                        // $storeCreditRules = SsStoreCreditRules::where('ss_plan_group_id', $contract->ss_plan_groups_id)->get();
                                        // foreach ($storeCreditRules as $creditRule) {
                                        //     $creditCustomer = SsCustomer::where('shop_id', $ids['shop_id'])
                                        //         ->where('shopify_customer_id', $shopify_customer_id)
                                        //         ->select('id', 'credit_balance')
                                        //         ->first();
                                        //     if ($creditCustomer) {
                                        //         // Getting beggining balace from customer.
                                        //         $beginningBalance = $creditCustomer->credit_balance != null ? $creditCustomer->credit_balance : 0;
                                        //         $value_amount = null;

                                        //         // It will Work for both new and renewal orders.
                                        //         // It will Count amount of credits.
                                        //         if ($creditRule->value_type == 'fixed_amount') {
                                        //             $value_amount = $creditRule->value_amount;
                                        //         } else if ($creditRule->value_type == 'multiplier') {
                                        //             $value_amount = $pricingPolicyAmount * $creditRule->value_amount;
                                        //         } else if ($creditRule->value_type == 'membership_value') {
                                        //             $value_amount = $pricingPolicyAmount;
                                        //         }

                                        //         $creditDescription = "Earned $value_amount for starting a new membership"; // Description
                                        //         $endingBalance = $beginningBalance + $value_amount; // Summingup ending balance

                                        //         // Making credit transaction.
                                        //         $dbCreditRule = new SsStoreCredit();
                                        //         $dbCreditRule->ss_customer_id = $creditCustomer->id;
                                        //         $dbCreditRule->transaction_date = Carbon::now();
                                        //         $dbCreditRule->description = $creditDescription;
                                        //         $dbCreditRule->beginning_balance = $beginningBalance;
                                        //         $dbCreditRule->ending_balance = $endingBalance;
                                        //         $dbCreditRule->save();

                                        //         // Updating credit balance of customer.
                                        //         $creditCustomer->credit_balance = $endingBalance;
                                        //         $creditCustomer->save();
                                        //     }
                                        // }
                                        // save form answer

                                        $isActiveFormFields = DB::table('ss_forms')->where('ss_plan_group_id', $db_plan_group->id)->where('shop_id', $shop->id)->count();
                                        if ($isActiveFormFields > 0) {
                                            $sh_properties = $node['customAttributes'];
                                            foreach ($sh_properties as $pkey => $pval) {

                                                if ($pval['value'] != '') {
                                                    $formatKey = str_replace("_", "", $pval['key']);
                                                    $is_form_property = DB::table('ss_forms')->where('ss_plan_group_id', $db_plan_group->id)->where('shop_id', $shop->id)->where('field_label', $formatKey)->first();
                                                    if ($is_form_property) {
                                                        $ans = new SsAnswer;
                                                        $ans->ss_contract_id = $contract->id;
                                                        $ans->question = $formatKey;
                                                        $ans->field_type = $is_form_property->field_type  || null;
                                                        $ans->answer = $pval['value'];
                                                        $ans->save();
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if (!$is_added_cutoff) {
                                        $contract->delivery_cutoff = $db_plan->delivery_cutoff;
                                        $contract->delivery_pre_cutoff_behaviour = $db_plan->delivery_pre_cutoff_behaviour;
                                        $contract->save();
                                        $is_added_cutoff = true;
                                    }

                                    // if(!$db_plan->trial_available){
                                    $pricing_adjustment_type = $db_plan->pricing_adjustment_type;
                                    $pricing_adjustment_value = (gettype($node['pricingPolicy'] == null))  ? $node['currentPrice']['amount'] : $node['pricingPolicy']['cycleDiscounts'][0]['computedPrice']['amount'];



                                    $price = preg_replace('/[^0-9 .]/s', '', $pricingPolicyAmount);

                                    $discount = 0;
                                    if ($pricing_adjustment_value != null && $pricing_adjustment_value != '') {
                                        $discount = ($pricing_adjustment_type == '%') ? (($price * $pricing_adjustment_value) / 100) : $pricing_adjustment_value;
                                    }
                                    $discounted_price = $price - $discount;
                                    // }else{
                                    //     $pricingPolicyAmount = $db_plan->pricing_adjustment_value;
                                    //     $price = $db_plan->pricing_adjustment_value;
                                    //     $discounted_price = 0.0;
                                    //     $pricing_adjustment_value = ($price - $discounted_price);
                                    // }
                                } else {
                                    $price = preg_replace('/[^0-9 .]/s', '', $pricingPolicyAmount);
                                    $discounted_price = 0;
                                    $pricing_adjustment_value = ($price - $discounted_price);
                                }

                                $lineItems = new SsContractLineItem;
                                $lineItems->shopify_contract_id = $data->id;
                                $lineItems->ss_contract_id = $contract->id;
                                $lineItems->user_id = $user->id;
                                $lineItems->shopify_line_id = str_replace('gid://shopify/SubscriptionLine/', '', $node['id']);
                                $lineItems->shopify_product_id = str_replace('gid://shopify/Product/', '', $node['productId']);
                                $lineItems->shopify_variant_id = str_replace(
                                    'gid://shopify/ProductVariant/',
                                    '',
                                    $node['variantId']
                                );
                                $lineItems->price = preg_replace('/[^0-9 .]/s', '', number_format($pricingPolicyAmount, 2));
                                $lineItems->price_discounted = ($lineItems->price > 0) ? preg_replace('/[^0-9 .]/s', '', number_format($discounted_price, 2)) : 0;
                                $lineItems->currency = (@$node['currentPrice']['currencyCode']) ? $node['currentPrice']['currencyCode'] : '';
                                $lineItems->currency_symbol = currencyH($node['currentPrice']['currencyCode']);
                                $lineItems->discount_type = (@$pricingPolicy['cycleDiscounts'][0]['adjustmentType'] == 'PERCENTAGE') ? '%' : currencyH($node['currentPrice']['currencyCode']);
                                $lineItems->discount_amount = ($lineItems->price > 0) ? $pricing_adjustment_value : 0;
                                $lineItems->final_amount = ($lineItems->price > 0) ? $discounted_price : 0;
                                $lineItems->quantity = (@$node['quantity']) ? $node['quantity'] : 0;
                                $lineItems->selling_plan_id = str_replace(
                                    'gid://shopify/SellingPlan/',
                                    '',
                                    $node['sellingPlanId']
                                );
                                $lineItems->selling_plan_name = $node['sellingPlanName'];
                                $lineItems->sku = (@$node['sku']) ? $node['sku'] : '';
                                $lineItems->taxable = (@$node['taxable']) ? $node['taxable'] : 0;
                                $lineItems->title = (@$node['title']) ? $node['title'] : '';
                                $lineItems->shopify_variant_image = (@$node['variantImage']['originalSrc']) ? $node['variantImage']['originalSrc'] : '';
                                $lineItems->shopify_variant_title = (@$node['variantTitle']) ? $node['variantTitle'] : '';
                                $lineItems->requiresShipping = (@$node['requiresShipping']) ? $node['requiresShipping'] : 0;

                                $lineItems->save();

                                if ($contract->trial_available) {

                                    //     $this->subscriptionDraftLineUpdate($user->id, $lineItems);
                                    //     $this->subscriptionContractSetNextBillingDate($user->id, $contract->shopify_contract_id);
                                }

                                $ssPlan = SsPlan::where('id', $contract->ss_plan_id)->first();
                                logger("==============================START :: STORE CREDIT =============================");
                                if ($ssPlan && $ssPlan->store_credit) {
                                    $is_exist_store_credit = SsStoreCredit::where(['shop_id' => $ssPlan->shop_id, 'ss_customer_id' =>  $contract->ss_customer_id])->first();
                                    if ($ssPlan->store_credit_frequency == 'all_orders') {
                                        $trans_Acc_id = $this->createstoreCredit($ssPlan->user_id, $contract->shopify_customer_id, $ssPlan->store_credit_amount, $shop->currency);

                                        $store__credit = ($is_exist_store_credit) ? $is_exist_store_credit : new SsStoreCredit;
                                        $store__credit->shop_id = $ssPlan->shop_id;
                                        $store__credit->shopify_storecreditaccount_id = $trans_Acc_id;
                                        $store__credit->ss_customer_id = $contract->ss_customer_id;
                                        $store__credit->shopify_customer_id = $contract->shopify_customer_id;
                                        $store__credit->amount = $ssPlan->store_credit_amount;
                                        $store__credit->balance = ($is_exist_store_credit) ? ($is_exist_store_credit->balance + $ssPlan->store_credit_amount) :  $ssPlan->store_credit_amount;
                                        $store__credit->save();

                                        $contract->store_credit_frequency = 'all_orders';
                                        $contract->store_credit_amount = $ssPlan->store_credit_amount;
                                        $this->addTrasaction($ssPlan->shop_id, $ssPlan->user_id, $contract->ss_customer_id,  $contract->id, "credit", $ssPlan->store_credit_amount);
                                        $this->saveActivity($ssPlan->user_id, $contract->ss_customer_id, $contract->id, "System", "Customer received $ssPlan->store_credit_amount $shop->currency in store credits");
                                    } else if ($ssPlan->store_credit_frequency == 'first_order') {
                                        $checkPlans =  SsContract::where(['ss_plan_id' => $ssPlan->id, 'shop_id' => $ssPlan->shop_id, 'ss_customer_id' =>  $contract->ss_customer_id, 'store_credit_frequency' => 'first_order'])->first();
                                        if (!$checkPlans) {
                                            $trans_Acc_id = $this->createstoreCredit($ssPlan->user_id, $contract->shopify_customer_id, $ssPlan->store_credit_amount, $shop->currency);

                                            $store__credit = ($is_exist_store_credit) ? $is_exist_store_credit : new SsStoreCredit;
                                            $store__credit->shop_id = $ssPlan->shop_id;
                                            $store__credit->shopify_storecreditaccount_id = $trans_Acc_id;
                                            $store__credit->ss_customer_id = $contract->ss_customer_id;
                                            $store__credit->shopify_customer_id = $contract->shopify_customer_id;
                                            $store__credit->amount = $ssPlan->store_credit_amount;
                                            $store__credit->balance = ($is_exist_store_credit) ? ($is_exist_store_credit->balance + $ssPlan->store_credit_amount) :  $ssPlan->store_credit_amount;
                                            $store__credit->save();

                                            $contract->store_credit_frequency = 'first_order';
                                            $contract->store_credit_amount = $ssPlan->store_credit_amount;
                                            $this->addTrasaction($ssPlan->shop_id, $ssPlan->user_id, $contract->ss_customer_id,  $contract->id, "credit", $ssPlan->store_credit_amount);
                                            $this->saveActivity($ssPlan->user_id, $contract->ss_customer_id, $contract->id, "System", "Customer received $ssPlan->store_credit_amount $shop->currency in store credits");
                                        }
                                    }
                                }
                                logger("==============================END :: STORE CREDIT =============================");
                                if ($ssPlan->is_advance_option && ($contract->pricing2_after_cycle || ($ssPlan->trial_days &&  $ssPlan->trial_days > 0))) {

                                    if ($shop->currency != $contract->currency_code) {
                                        $contract->pricing_adjustment_value = $subscriptionContract['lines']['edges'][0]['node']['pricingPolicy']['cycleDiscounts'][1]['computedPrice']['amount'];
                                        if ($contract->pricing2_after_cycle) {
                                            logger("************** IN TRIAL ORDERS****");
                                            $lineItems->discount_amount  = $contract->pricing_adjustment_value;
                                            $lineItems->save();
                                        }
                                        logger("************************************************* PRicing ADJUSTMENT VALUE");
                                        logger($contract->pricing_adjustment_value);
                                    } else {
                                        $contract->pricing_adjustment_value  = $ssPlan->pricing_adjustment_value;
                                        if ($contract->pricing2_after_cycle) {
                                            logger("************** IN TRIAL ORDERS****");
                                            $lineItems->discount_amount  = $contract->pricing_adjustment_value;
                                            $lineItems->save();
                                        }
                                        // logger("************************************************* PRicing*");
                                        // logger($contract->pricing_adjustment_value);
                                    }
                                    $contract->Save();
                                }
                                if ($contract->is_onetime_payment || (($contract->pricing2_after_cycle != 1 &&  $contract->order_count == $contract->pricing2_after_cycle) || ($contract->pricing2_after_cycle &&  $contract->pricing2_after_cycle == 1)  || ($ssPlan->trial_days && $ssPlan->trial_days > 0))) {
                                    $this->subscriptionContractPriceUpdate($user->id, $lineItems, $contract);
                                }

                                //set display status if contract have one_time purchase active
                                if ($contract->is_onetime_payment) {
                                    $displayStatus = 'Lifetime Access';
                                } else {
                                    $displayStatus = 'Active';
                                    if ($contract->billing_max_cycles) {
                                        if ($contract->order_count == $contract->billing_max_cycles) {
                                            $displayStatus = 'Active - Expiring';
                                        } else if (($contract->order_count + 1) >= $contract->billing_max_cycles) {
                                            $displayStatus = 'Active Until Next Bill';
                                        }
                                    }
                                }
                                $contract->status_display = $displayStatus;
                                $contract->save();
                                $sf_product_id = str_replace('gid://shopify/Product/', '', $node['productId']);
                            }
                        }

                        // update order count in note_attributes
                        if ($data->origin_order_id) {
                            $this->updateShopifyNoteAttributes($user, $data->origin_order_id, 'Membership Order Count', $contract->order_count, 'order');
                        }

                        // create order
                        if ($data->origin_order_id) {
                            //update customer
                            if ($customer) {
                                $sh_order = $this->getShopifyOrder($user, $data->origin_order_id);
                                $customer->total_spend = (@$sh_order['total_price']) ? $customer->total_spend + $sh_order['total_price'] : $customer->total_spend;
                                $customer->avg_order_value = ($customer->total_orders > 0) ? preg_replace('/[^0-9_ .]/s', '', number_format(($customer->total_spend / $customer->total_orders), 2)) : $customer->total_spend;
                                $customer->save();
                            }
                            $order = $this->createOrder($user->id, $shop->id, $data->origin_order_id, $customer->id, $contract->id);

                            //add billing attempt
                            $billingAttempt = new SsBillingAttempt;
                            $billingAttempt->shop_id = $shop->id;
                            $billingAttempt->ss_contract_id = $contract->id;
                            $billingAttempt->status = 'successful';
                            $billingAttempt->completedAt = date('Y-m-d H:i:s');
                            $billingAttempt->shopify_contract_id = $contract->shopify_contract_id;
                            $billingAttempt->shopify_order_id = $contract->origin_order_id;
                            $billingAttempt->save();

                            // $setting = SsSetting::select('new_subscription_email_enabled', 'email_from_email', 'email_from_name', 'notify_new', 'notify_email', 'send_account_invites')->where('shop_id', $shop->id)->first();

                            $setting = DB::table('ss_settings')->select('new_subscription_email_enabled', 'email_from_email', 'email_from_name', 'notify_new', 'notify_email', 'send_account_invites')->where('shop_id', $shop->id)->first();

                            // send mail to customer for new membership
                            if ($setting->new_subscription_email_enabled) {
                                // $email = SsEmail::where('shop_id', $shop->id)->where('category', 'new_membership_to_customer')->first();
                                $email = DB::table('ss_emails')->where('shop_id', $shop->id)->where('category', 'new_membership_to_customer')->first();
                                if ($email) {
                                    $res = sendMailH($email->subject, $email->html_body, $setting->email_from_email, $customer->email, $setting->email_from_name, $shop->id, $customer->id);
                                }
                            }

                            // notify mail to given email(user) in setting tab
                            if ($setting->notify_new && $setting->notify_email != '') {
                                $notifyData = config('notify-mails.notify_new');
                                $newData = $this->fetchContractFormFields($contract->id, $notifyData['body']);
                                // $db_ss_plan = SsPlan::select('name')->where('id', $contract->ss_plan_id)->first();
                                $db_ss_plan = DB::table('ss_plans')->select('name')->where('id', $contract->ss_plan_id)->first();
                                $planData['next_billing_date'] = $contract->next_processing_date;
                                $planData['membership_plan'] = ($db_ss_plan) ? $db_ss_plan->name : '';
                                $notifyMailRes = sendMailH($notifyData['subject'], $newData, config('notify-mails.notify_from_email'), $setting->notify_email, config('notify-mails.notify_from_name'), $contract->shop_id, $contract->ss_customer_id, $planData);
                            }

                            // send customer invites
                            if ($setting->send_account_invites) {
                                $this->sendAccountInvites($user, $shopify_customer_id, $setting->email_from_email, $customer->email);
                            }
                        }

                        ($contract->tag_customer != '') ? $this->updateShopifyTags($user, $shopify_customer_id, $contract->tag_customer, 'customer') : '';
                        if ($data->origin_order_id) {
                            ($contract->tag_order != '') ? $this->updateShopifyTags($user, $data->origin_order_id, $contract->tag_order, 'order') : '';
                        }
                        $is_membership_expired = $this->is_membership_expired($user);
                        $this->membershipexpireMetaUpdate($user, $is_membership_expired);
                        // $this->flowTrigger(
                        //     config('const.SHOPIFY_FLOW.NEW_MEMBERSHIP'),
                        //     env('APP_TRIGGER_URL'),
                        //     '{\"customer_id\": ' . $shopify_customer_id . ',\"order_id\": ' . $data->origin_order_id . ',\"product_id\": ' . $sf_product_id . ',\"Customer Tag\": \"' . $db_plan_group->tag_customer . '\",\"Order Tag\": \"' . $db_plan_group->tag_order . '\",\"Next Billing Date\": \"' . $contract->next_processing_date . '\",\"Member Number\": ' . $contract->member_number . ',\"Contract ID\": ' . $shopify_contract_id . '}',
                        //     $user
                        // );
                        Http::post(env('APP_TRIGGER_URL').'/api/callFlowTrigger', [
                            'action' => 'new_member',
                            'customer_id' => $shopify_customer_id ,
                            'order_id' => $data->origin_order_id,
                            'product_id' => $sf_product_id,
                            'tag_customer' => $db_plan_group->tag_customer ,
                            'tag_order' => $db_plan_group->tag_order,
                            'next_processing_date' => $contract->next_processing_date ,
                            'member_number' => $contract->member_number ,
                            'shopify_contract_id' =>  $shopify_contract_id ,
                            'uid' =>  $user->id ,
                        ]);
                    }
                } else {
                    logger(json_encode($result));
                }
            }
            // }
            $this->updateWebhookStatus($this->statuswebhook_id, 'processed', null);
        } catch (\Exception $e) {
            logger($e);
            $this->updateWebhookStatus($this->statuswebhook_id, 'error', $e);
            Bugsnag::notifyException($e);
        }
    }


    public function subscriptionContractLineItems($contractId)
    {
        $query = '
                {
                        subscriptionContract(id: "' . $contractId . '") {
                          lastPaymentStatus
                          nextBillingDate
                          customAttributes {
                            key
                            value
                          }
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
}
