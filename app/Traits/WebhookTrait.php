<?php

namespace App\Traits;

use App\Events\CheckBillingAttemptFailure;
use App\Events\CheckBillingAttemptSuccess;
use App\Events\CheckCustomerPaymentMethodUpdate;
use App\Events\CheckProductUpdate;
use App\Events\CheckSubscriptionContract;
use App\Models\Shop;
use App\Models\SsCustomer;
use App\Jobs\AppUninstalledJob;
use App\Jobs\CheckBillingAttemptFailureJob;
use App\Jobs\CheckBillingAttemptSuccessJob;
use App\Jobs\CustomerPaymentMethodUpdateJob;
use App\Jobs\SubscriptionContractJob;
use App\Models\SsContractLineItem;
use App\Models\SsDeletedProduct;
use App\Models\SsShippingProfile;
use App\Models\SsPlanGroupVariant;
use App\Models\SsPlanGroup;
use App\Models\User;
use App\Jobs\DiscountDeleteJob;
use App\Jobs\CustomerMetafiedsJob;

/**
 * Trait WebhookTrait
 * @package App\Traits
 */

use App\Traits\ShopifyTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;


trait WebhookTrait
{
    use ShopifyTrait;
    public function webhookIndex($request)
    {
        try {

            $requestData = $request->json()->all();
            $data = $requestData['detail'];
            $payload = $data['payload'];
            $metadata = $data['metadata'];
            $domain = $metadata['X-Shopify-Shop-Domain'];
            $topic = $metadata['X-Shopify-Topic'];
            logger('============> Data');
            logger(json_encode($data));
            logger('============> Payload');
            logger(json_encode($payload));
            logger('============> Meta Data');
            logger(json_encode($metadata));
            logger('============> Domain');
            logger($domain);
            logger('============> Topic');
            logger($topic);
            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();
            $payloadJson = json_encode($payload);
            $webhookId = $this->webhook($topic, $user->id, $payloadJson);
            switch ($topic) {
                case 'customer_payment_methods/create':
                    break;
                case 'customer_payment_methods/revoke':
                    break;
                case 'customer_payment_methods/update':
                    // CustomerPaymentMethodUpdateJob::dispatch($webhookId, $user->id, $shop->id, $payloadJson)->onQueue('high');
                    event(new CheckCustomerPaymentMethodUpdate($webhookId, $user->id, $shop->id, $payloadJson));
                    break;
                case 'customers/delete':
                    $customer = SsCustomer::where('shop_id', $shop->id)->where('shopify_customer_id', $payload['id'])->first();
                    if ($customer) {
                        $customer->delete();
                    }
                    $this->updateWebhookStatus($webhookId, 'processed', null);
                    break;
                case 'customers/update':
                    // $is_existcustomer = SsCustomer::where('shop_id', $shop->id)->where('shopify_customer_id', $payload['id'])->first();
                    // if( $is_existcustomer ){
                    //     $customer = $is_existcustomer;
                    //     $customer->first_name = $payload['first_name'];
                    //     $customer->last_name = $payload['last_name'];
                    //     $customer->email = $payload['email'];
                    //     $customer->phone = $payload['phone'];
                    //     $customer->save();
                    // }
                    $affected = DB::table('ss_customers')
                        ->where('shop_id', $shop->id)
                        ->where('shopify_customer_id', $payload['id'])
                        ->update([
                            'first_name' => $payload['first_name'],
                            'last_name' => $payload['last_name'],
                            'email' => $payload['email'],
                            'phone' => $payload['phone'],
                        ]);
                    CustomerMetafiedsJob::dispatch($user->id,$payload);
                    $this->updateWebhookStatus($webhookId, 'processed', null);
                    break;
                case 'locations/create':
                    if ($payload['active']) {
                        $profiles = SsShippingProfile::where('shop_id', $shop->id)->get();
                        foreach ($profiles as $pkey => $pval) {
                            $result = $this->createDeliveryProfile($user->id, $pval->id,  'gid://shopify/Location/' . $payload['id']);
                        }
                    }
                    $this->updateWebhookStatus($webhookId, 'processed', null);
                    break;
                case 'locations/update':
                    if ($payload['active']) {
                        $profiles = SsShippingProfile::where('shop_id', $shop->id)->get();
                        foreach ($profiles as $pkey => $pval) {
                            $result = $this->createDeliveryProfile($user->id, $pval->id, 'gid://shopify/Location/' . $payload['id']);
                        }
                    }
                    $this->updateWebhookStatus($webhookId, 'processed', null);
                    break;
                case 'orders/create':
                    $this->updateWebhookStatus($webhookId, 'processed', null);
                    break;
                case 'orders/updated':
                    $this->updateWebhookStatus($webhookId, 'processed', null);
                    break;
                case 'products/delete':
                    $productCnt = SsContractLineItem::select('ss_contract_id')->distinct()->where('user_id',  $user->id)->where('shopify_product_id', $payload['id'])->count();
                    if ($productCnt > 0) {
                        $deleted_product = new SsDeletedProduct;
                        $deleted_product->shop_id = $shop->id;
                        $deleted_product->user_id = $user->id;
                        $deleted_product->shopify_product_id =  $payload['id'];
                        $deleted_product->subscriptions_impacted = $productCnt;
                        $deleted_product->active = 1;
                        $deleted_product->save();
                    }
                    $db_variants = SsPlanGroupVariant::where('shopify_product_id', $payload['id'])->get();
                    if (count($db_variants) > 0) {
                        foreach ($db_variants as $key => $variant) {
                            $planGroup = SsPlanGroup::find($variant->ss_plan_group_id);
                            ($planGroup) ? $planGroup->delete() : '';
                            $variant->delete();
                        }
                    }
                    Cache::forget($shop->id);
                    $this->updateWebhookStatus($webhookId, 'processed', null);

                    break;
                case 'products/update':
                    // event(new CheckProductUpdate($webhookId, $user->id, $shop->id, $payloadJson));
                    break;
                case 'shop/redact':
                    $this->sendGDPRMail($webhookId, $user, 'shop/redact', $payloadJson);
                    break;
                case 'customers/data_request':
                    $this->sendGDPRMail($webhookId, $user, 'customers/data_request', $payloadJson);
                    break;
                case 'customers/redact':
                    $this->sendGDPRMail($webhookId, $user, 'customers/redact', $payloadJson);
                    break;
                case 'shop/update':
                    $this->updateshop($payload, $user, $shop);
                    $this->updateWebhookStatus($webhookId, 'processed', null);
                    break;
                case 'subscription_billing_attempts/failure':
                    // CheckBillingAttemptFailureJob::dispatch($webhookId, $user->id, $shop->id, $payloadJson)->onQueue('high');
                    event(new CheckBillingAttemptFailure($webhookId, $user->id, $shop->id, $payloadJson));
                    break;
                case 'subscription_billing_attempts/success':
                    // CheckBillingAttemptSuccessJob::dispatch($webhookId, $user->id, $shop->id, $payloadJson)->onQueue('high');
                    event(new CheckBillingAttemptSuccess($webhookId, $user->id, $shop->id, $payloadJson));
                    break;
                case 'subscription_contracts/create':
                    // SubscriptionContractJob::dispatch($webhookId, $user->id, $shop->id, $payloadJson)->onQueue('high');
                    event(new CheckSubscriptionContract($webhookId, $user->id, $shop->id, $payloadJson));
                    break;
                case 'subscription_contracts/update':
                    // SubscriptionContractJob::dispatch($webhookId, $user->id, $shop->id, $payloadJson)->onQueue('high');
                    event(new CheckSubscriptionContract($webhookId, $user->id, $shop->id, $payloadJson));
                    break;
                case 'app/uninstalled':
                    AppUninstalledJob::dispatch(new ShopDomain($domain), $data);
                    break;
                case 'discounts/delete' :
                    DiscountDeleteJob::dispatch($shop->id,$user->id,$data);
                    break;
                default:
            }
            return;
        } catch (\Exception $e) {
            logger("============= ERROR ::  webhookIndex =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updateshop($payload, $user, $shop)
    {
        try {
            logger('============== START:: updateshop ===========');
            $db_shop = ($shop) ? $shop : new Shop;
            $sh_shop = $payload;
            $db_shop->user_id = $user->id;
            $db_shop->shopify_store_id = $sh_shop['id'];
            $db_shop->active = true;
            $db_shop->ts_last_deactivation = date('Y-m-d H:i:s');
            $db_shop->test_store = ($sh_shop['plan_name'] == 'partner_test');
            $db_shop->name = $sh_shop['name'];
            $db_shop->email = $sh_shop['email'];
            $db_shop->myshopify_domain = $sh_shop['myshopify_domain'];
            $db_shop->domain = $sh_shop['domain'];
            $db_shop->owner = $sh_shop['shop_owner'];
            $db_shop->shopify_plan = $sh_shop['plan_name'];
            $db_shop->timezone = $sh_shop['timezone'];
            $db_shop->address1 = $sh_shop['address1'];
            $db_shop->address2 = $sh_shop['address2'];
            $db_shop->checkout_api_supported = $sh_shop['checkout_api_supported'];
            $db_shop->city = $sh_shop['city'];
            $db_shop->country = $sh_shop['country'];
            $db_shop->country_code = $sh_shop['country_code'];
            $db_shop->country_name = $sh_shop['country_name'];
            $db_shop->country_taxes = $sh_shop['county_taxes'];
            $db_shop->ss_created_at = ($shop) ? $shop->ss_created_at : date('Y-m-d H:i:s');
            $db_shop->customer_email = $sh_shop['customer_email'];
            $db_shop->currency = $sh_shop['currency'];
            $db_shop->currency_symbol = currencyH($sh_shop['currency']);
            $db_shop->enabled_presentment_currencies = json_encode($sh_shop['enabled_presentment_currencies']);
            $db_shop->eligible_for_payments = $sh_shop['eligible_for_payments'];
            $db_shop->has_discounts = $sh_shop['has_discounts'];
            $db_shop->has_gift_cards = $sh_shop['has_gift_cards'];
            $db_shop->has_storefront = $sh_shop['has_storefront'];
            $db_shop->iana_timezone = $sh_shop['iana_timezone'];
            $db_shop->latitude = $sh_shop['latitude'];
            $db_shop->longitude = $sh_shop['longitude'];
            $db_shop->money_format = $sh_shop['money_format'];
            $db_shop->money_in_emails_format = $sh_shop['money_in_emails_format'];
            $db_shop->money_with_currency_format = $sh_shop['money_with_currency_format'];
            $db_shop->money_with_currency_in_emails_format = $sh_shop['money_with_currency_in_emails_format'];
            $db_shop->multi_location_enabled = $sh_shop['multi_location_enabled'];
            $db_shop->password_enabled = $sh_shop['password_enabled'];
            $db_shop->phone = $sh_shop['phone'];
            $db_shop->pre_launch_enabled = $sh_shop['pre_launch_enabled'];
            $db_shop->primary_locale = $sh_shop['primary_locale'];
            $db_shop->province = $sh_shop['province'];
            $db_shop->province_code = $sh_shop['province_code'];
            $db_shop->requires_extra_payments_agreement = $sh_shop['requires_extra_payments_agreement'];
            $db_shop->setup_required = $sh_shop['setup_required'];
            $db_shop->taxes_included = $sh_shop['taxes_included'];
            $db_shop->tax_shipping = $sh_shop['tax_shipping'];
            $db_shop->tbl_updated_at = date('Y-m-d H:i:s');
            $db_shop->weight_unit = $sh_shop['weight_unit'];
            $db_shop->zip = $sh_shop['zip'];
            $db_shop->save();
        } catch (\Exception $e) {
            logger("============= ERROR ::  updateshop =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
