<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Subscriber\SubscriberController;
use App\Models\Shop;
use App\Models\SsContract;
use App\Models\SsLanguage;
use App\Models\SsSetting;
use App\Models\SsPortal;
use App\Models\SsCustomer;
use App\Traits\ShopifyTrait;
use LaravelFeature\Facade\Feature;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SsCancellationReason;

class PortalController extends Controller
{
    use ShopifyTrait;
    public function index(Request $request)
    {
        try {
            $customer_id = $request->customer;
            $is_initLoad = (bool) $request->init_load;

            $shop = Shop::where('domain', $request->shop)->first();
            $user = ($shop) ? User::where('id', $shop->user_id)->first() : null;

            $initcustomer = SsCustomer::select('id', 'shop_id', 'email', 'phone')->where('shopify_customer_id', $customer_id)->first();
            if ($is_initLoad) {

                if ($initcustomer) {
                    $shop = Shop::find($initcustomer->shop_id);
                    $this->saveActivity($shop->user_id, $initcustomer->id, null, 'customer', 'Customer logged into the customer portal');
                }
            }

            $subscriber = ($request->contract == '') ? SsContract::with('LineItems')->where('shopify_customer_id', $customer_id)->orderBy('created_at', 'desc')->first() : SsContract::with('LineItems')->where('shopify_customer_id', $customer_id)->where('id', $request->contract)->first();

            $subscriberC = new SubscriberController();

            $data['contract'] = [];
            $data['otherContracts'] = [];
            $data['languages'] = [];

            if ($subscriber) {
                $user_id = $subscriber->user_id;
                $shop_id = $subscriber->shop_id;
                $shop = Shop::find($shop_id);
                $user = User::find($user_id);

                $id = $subscriber->id;
                $next = SsContract::select('id')->where('id', '>', $id)->orderBy('id')->first();
                $previous = SsContract::select('id')->where('id', '<', $id)->orderBy('id', 'desc')->first();

                $nextOD = $this->getSubscriptionTimeDate(date('Y-m-d', strtotime($subscriber->next_order_date)), $shop->id, date('H:i:s', strtotime($subscriber->next_order_date)));
                $nextProcessD = $this->getSubscriptionTimeDate(date('Y-m-d', strtotime($subscriber->next_processing_date)), $shop->id, date('H:i:s', strtotime($subscriber->next_processing_date)));
                $subscriber->next_order_date = date('M d, Y', strtotime($nextOD));
                $subscriber->next_processing_date = date('M d, Y', strtotime($nextProcessD));

                $data['contract'] = $subscriber;
                $data['contract']['lineItems'] = (!empty($subscriber['LineItems'])) ? $subscriberC->getLineItemsData($subscriber, $user) : [];
                $data['contract']['fulfillmentOrders'] = ($subscriber['last_billing_order_number']) ? $subscriberC->getFulfillments($user->id, $subscriber['last_billing_order_number']) : [];
                $data['shop']['domain'] = $shop->myshopify_domain;
                $data['shop']['currency'] = $shop->currency_symbol;
                $data['contract']['prev'] = ($previous) ? '/subscriber/' . $previous->id . '/edit?page=1' : null;
                $data['contract']['next'] = ($next) ? '/subscriber/' . $next->id . '/edit?page=1' : null;
                $data['otherContracts'] = SsContract::select('id', 'status', 'shopify_contract_id')->where('shop_id', $shop_id)->where('shopify_customer_id', $customer_id)->orderBy('created_at', 'desc')->get()->toArray();

                $data['contract']['billing_update_url'] = $this->customerPaymentMethodGetUpdateUrl($user_id, $subscriber->cc_id);
                $data['settings'] = SsSetting::where('shop_id', $shop_id)->select(['portal_can_cancel', 'portal_can_skip', 'portal_can_ship_now', 'portal_can_change_qty', 'portal_can_change_nod', 'portal_can_change_freq', 'portal_can_add_product', 'portal_show_content', 'portal_content','cancellation_reason_enable'])->first();

                $data['reasons'] = SsCancellationReason::select('id','reason','is_enabled')->where(['shop_id' => $shop_id , 'is_enabled' => 1])->get();
                $data['customer'] = ($initcustomer) ? $initcustomer : [];

                $data['contract']['isPast'] = true;

                if ($data['contract']['status'] == 'cancelled') {

                    if (date('Y-m-d H:i:s', strtotime($subscriber->next_processing_date)) > date('Y-m-d H:i:s')) {
                        $data['contract']['isPast']  = false;
                    }
                }

                $orderFields = 'id, current_subtotal_price, current_total_price, currency, shipping_lines';
                $data['order'] = $this->getShopifyOrder($user, $subscriber->origin_order_id, $orderFields);
                $subscriber->unsetRelation('LineItems');

                $data['feature']['hide-portal-part'] = (Feature::isEnabledFor('hide-portal-part', $user));
            }

            if ($shop) {
                $lang = SsLanguage::where('shop_id', $shop->id)->first();
                $data['languages'] = ($lang) ? $lang : [];
            }

            $data['images']['img'] = asset('images/static/cards');
            $data['images']['no_img'] = asset('images/static/no-image-box.png');

            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function liquidIndex(Request $request)
    {
        try {
            $customer_id = $request->customer;
            $is_initLoad = (bool) $request->init_load;

            $shop = Shop::where('domain', $request->shop)->orWhere('myshopify_domain', $request->shop)->first();
            $user = ($shop) ? User::where('id', $shop->user_id)->first() : null;

            $initcustomer = SsCustomer::select('id', 'shop_id', 'first_name', 'last_name', 'email', 'phone')->where('shopify_customer_id', $customer_id)->first();
            if ($is_initLoad) {

                if ($initcustomer) {
                    $shop = Shop::find($initcustomer->shop_id);
                    $this->saveActivity($shop->user_id, $initcustomer->id, null, 'customer', 'Customer logged into the customer portal');
                }
            }

            $data['language'] = [];

            $subscriberQuery = SsContract::select('id', 'user_id', 'shop_id', 'shopify_contract_id', 'shopify_customer_id', 'origin_order_id', 'status', 'status_display', 'error_state', 'next_order_date', 'next_processing_date', 'last_billing_order_number', 'billing_interval', 'billing_interval_count', 'billing_min_cycles', 'billing_max_cycles', 'delivery_interval', 'delivery_interval_count', 'delivery_cutoff', 'delivery_pre_cutoff_behaviour', 'delivery_price', 'delivery_price_currency_symbol', 'ship_company AS shipping_company', 'ship_firstName AS shipping_firstname', 'ship_lastName AS shipping_lastname', 'ship_provinceCode AS shipping_stateprov', 'ship_name AS shipping_fullname', 'ship_address1 AS shipping_address1', 'ship_address2 AS shipping_address2', 'ship_phone AS shipping_phone', 'ship_city AS shipping_city', 'ship_province AS shipping_stateprovname', 'ship_zip AS shipping_postalzip', 'ship_country AS shipping_country', 'ship_phone AS shipping_phone', 'currency_code', 'payment_method', 'paypal_account', 'paypal_inactive', 'paypal_isRevocable', 'cc_id', 'cc_source', 'cc_brand', 'cc_expiryMonth', 'cc_expiryYear', 'cc_expires_soon', 'cc_lastDigits', 'cc_name', 'is_multicurrency', 'shipping_carrier', 'shipping_code', 'shipping_description', 'shipping_presentmentTitle', 'shipping_title', 'lastPaymentStatus', 'status_billing', 'failed_payment_count', 'order_count', 'tag_customer', 'tag_order', 'on_trial', 'is_onetime_payment', 'pricing2_after_cycle', 'pricing2_adjustment_value', 'is_migrated', 'pricing_adjustment_value', 'member_number', 'created_at')->where('shopify_customer_id', $customer_id)->with('LineItems')->orderBy('status', 'asc');
            $subscriber = ($request->contract == '') ? $subscriberQuery->orderBy('created_at', 'desc')->first() : $subscriberQuery->where('id', $request->contract)->first();
            $subscriberC = new SubscriberController();

            $data['membership'] = [];
            $data['other_memberships'] = [];
            $data['shop'] = [];
            $data['customer'] = [];
            $data['countries'] = [];
            $data['settings'] = [];
            $data['reasons'] = [];

            $isContract = false;

            if ($shop) {
                $portal = SsPortal::where('shop_id', $shop->id)->first();
                $liquid = $portal->portal_liquid;
            }

            if ($subscriber) {
                $lang = SsLanguage::select('*', 'portal_title_subscriptions AS portal_title_membership')->where('shop_id', $shop->id)->first();
                $data['language'] = ($lang) ? $lang : [];

                $isContract = true;
                $user_id = $subscriber->user_id;
                $shop_id = $subscriber->shop_id;
                $shop = Shop::find($shop_id);
                $user = User::find($user_id);

                $id = $subscriber->id;
                $next = SsContract::select('id')->where('id', '>', $id)->orderBy('id')->first();
                $previous = SsContract::select('id')->where('id', '<', $id)->orderBy('id', 'desc')->first();

                $nextOD = $this->getSubscriptionTimeDate(date('Y-m-d', strtotime($subscriber->next_order_date)), $shop->id, date('H:i:s', strtotime($subscriber->next_order_date)));
                $nextProcessD = $this->getSubscriptionTimeDate(date('Y-m-d', strtotime($subscriber->next_processing_date)), $shop->id, date('H:i:s', strtotime($subscriber->next_processing_date)));
                $subscriber->next_order_date = date($lang->date_format, strtotime($nextOD));
                $subscriber->next_processing_date = date($lang->date_format, strtotime($nextProcessD));

                // dd($subscriber);
                $data['membership'] = $subscriber;
                $data['membership']['lineItems'] = (!empty($subscriber['LineItems'])) ? $subscriberC->getLineItemsData($subscriber, $user) : [];
                $data['membership']['fulfillmentOrders'] = ($subscriber['last_billing_order_number']) ? $subscriberC->getFulfillments($user->id, $subscriber['last_billing_order_number']) : [];
                $data['shop']['domain'] = $shop->myshopify_domain;
                $data['shop']['currency'] = $shop->currency_symbol;
                $data['membership']['prev'] = ($previous) ? '/subscriber/' . $previous->id . '/edit?page=1' : null;
                $data['membership']['next'] = ($next) ? '/subscriber/' . $next->id . '/edit?page=1' : null;

                $other_memberships = SsContract::with('LineItemsProductOtherMembership')->select('id', 'status', 'shopify_contract_id', 'status_display', 'created_at')->where('shop_id', $shop_id)->where('shopify_customer_id', $customer_id)->orderBy('status','asc')->orderBy('created_at', 'desc')->get()->toArray();
                // dd($other_memberships);
                $data['other_memberships'] = $this->formatOtherMemberships($other_memberships);

                $data['membership']['billing_update_url'] = $this->customerPaymentMethodGetUpdateUrl($user_id, $subscriber->cc_id);
                $data['settings'] = SsSetting::where('shop_id', $shop_id)->select(['portal_can_cancel','cancellation_reason_enable','cancellation_reason_enable_custom','custom_options','custom_reason_message','custom_submit','custom_cancel','required_reason'])->first();
                $data['customer'] = ($initcustomer) ? $initcustomer->toArray() : [];
                $data['reasons'] = SsCancellationReason::select('id','reason','is_enabled')->where(['shop_id' => $shop_id , 'is_enabled' => 1])->get();



                // Get customer meta data from shopify
                $data['customer_meta'] = [];
                $endPoint = "/admin/api/" . env('SHOPIFY_API_VERSION') . "/customers/" . $customer_id . "/metafields.json";
                $result = $user->api()->rest('GET', $endPoint);
                if (!$result['errors']) {
                    $data['customer_meta'] = $result['body']['metafields'];
                } else {
                    logger('=========> PORTAL::: error while get customer metafields');
                    logger(json_encode($result));
                }

                $data['membership']['isPast'] = true;

                unset($data['membership']['cc_id']);

                if ($data['membership']['status'] == 'cancelled') {
                    if (date('Y-m-d H:i:s', strtotime($subscriber->next_processing_date)) > date('Y-m-d H:i:s')) {
                        $data['membership']['isPast']  = false;
                    }
                }

                $orderFields = 'id, current_subtotal_price, current_total_price, currency, shipping_lines';
                $data['order'] = $this->getShopifyOrder($user, $subscriber->origin_order_id, $orderFields);
                $subscriber->unsetRelation('LineItems');

                $data['countries'] = countryH();

                unset($data['membership']['user_id'], $data['membership']['shop_id']);

                $newLineItems = [];
                foreach ($data['membership']['lineItems'] as $key => $lineItem) {
                    unset($lineItem['id'], $lineItem['ss_contract_id'], $lineItem['user_id'], $lineItem['shopify_line_id'], $lineItem['selling_plan_id'], $lineItem['selling_plan_name'], $lineItem['created_at'], $lineItem['updated_at'], $lineItem['deleted_at']);
                    array_push($newLineItems, $lineItem);
                }

                $data['membership']['lineItems'] = $newLineItems;
            } else {
                $lang = SsLanguage::select('portal_no_membership')->where('shop_id', $shop->id)->first();
                $data['language'] = ($lang) ? $lang : [];
            }

            $data['images']['cards'] = asset('images/static/cards');
            $data['images']['no_img'] = asset('images/static/no-image-box.png');

            $html = view('portal.liquid', compact('data', 'liquid'))->render();

            return response()->json([
                'isContract' => $isContract,
                'html' => $html,
                'data' => $data,
            ], 200)->withHeaders([
                'Content-Type' => 'application/liquid',
            ]);
        } catch (\Exception $e) {
            logger("============= ERROR ::  liquidIndex =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function formatOtherMemberships($entities)
    {
        try {
            $newEntity = [];
            foreach ($entities as $key => $value) {
                $value['product_title'] = $value['line_items_product_other_membership']['title'];
                unset($value['line_items_product_other_membership']);
                $newEntity[$key] = $value;
            }
            return $newEntity;
        } catch (\Exception $e) {
            logger("============= ERROR ::  formatOtherMemberships =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request)
    {
        try {
            $subscriberC = new SubscriberController();
            $result = $subscriberC->update($request);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  store =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
