<?php

namespace App\Jobs;

use App\Models\SsLanguage;
use App\Traits\ShopifyTrait;
use App\Models\Shop;
use App\Models\SsContract;
use App\Models\SsPortal;
use App\Models\SsEmail;
use App\Models\SsSetting;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Osiset\ShopifyApp\Storage\Models\Plan;
use Osiset\ShopifyApp\Util;

class AfterAuthenticationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ShopifyTrait;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            logger('============== START:: AfterAuthenticationJob ===========');

            $user = Auth::user();
            // logger(json_encode($user));
            $user->active = 1;

            if($user->plan_id !== null)
            {

                $user->expired_plan_id = $user->plan_id;
            }
            $user->save();
            $user = User::where('id', $user->id)->first();
            $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/shop.json';
            $result = $user->api()->rest('GET', $endPoint);
            // logger('========> Shop details');
            // logger(json_encode($result));



            // logger('plan ID is an ==================================>');
            // logger($user->plan);
            // logger($user);

            $this->setScriptTag($user);
            if (!$result['errors']) {
                $sh_shop = $result['body']->container['shop'];

                $is_exist_shop = Shop::where('user_id', $user->id)->first();
                $db_shop = ($is_exist_shop) ? $is_exist_shop : new Shop;
                $db_shop->user_id = $user['id'];
                $db_shop->shopify_store_id = $sh_shop['id'];
                $db_shop->active = true;
                $db_shop->ts_last_deactivation = date('Y-m-d H:i:s');
                $db_shop->test_store = ($sh_shop['plan_name'] == 'partner_test');
                $db_shop->name = $sh_shop['name'];
                $db_shop->email = $sh_shop['email'];
                $db_shop->myshopify_domain = $sh_shop['myshopify_domain'];
                $db_shop->domain = $sh_shop['domain'];
                $db_shop->owner = $sh_shop['shop_owner'];
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
                $db_shop->ss_created_at = ($is_exist_shop) ? $is_exist_shop['ss_created_at'] : date('Y-m-d H:i:s');
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
                $db_shop->is_discount_added = 1;
                $db_shop->save();

                $setting = SsSetting::where('shop_id', $db_shop->id)->first();
                // logger(json_encode($setting));
                if (!$setting) {

                    $setting = new SsSetting;
                    $setting->shop_id = $db_shop->id;
                    $setting->dunning_retries = 7;
                    $setting->dunning_daysbetween = 1;
                    $setting->email_from_name = $sh_shop['name'];
                    $setting->email_from_email = $sh_shop['email'];
                    $setting->dunning_email_enabled = 1;
                    $setting->dunning_failedaction = 'cancel';
                    $setting->subscription_daily_at = '12:01 AM';
                    $setting->portal_can_skip = 0;
                    $setting->portal_can_change_qty = 0;
                    $setting->mailgun_method = 'Safe';
                    $setting->mailgun_verified = 0;
                    $setting->send_account_invites = 1;

                    if ($user->plan_id == null) {
                        $setting->free_memberships  = env('FREE_MEMBERSHIPS');
                    }
                    $setting->restricted_content = getRestrictedContentHtml();
                    $setting->save();
                }

                $this->createLanguageInDb($db_shop->id);

                $portalLang = SsLanguage::where('shop_id', $db_shop->id)->first();
                if (!$portalLang) {
                    $portalLang = new SsLanguage;
                    $portalLang->shop_id = $db_shop->id;
                    $portalLang->portal_action_cancel = 'Cancel next renewal';
                    $portalLang->portal_popup_cancel_text = 'Your membership will be active until your next billing date';
                    $portalLang->portal_billing_send = 'Update Billing';
                    $portalLang->save();
                }

                $emailDescriptions = config('const.email_categories');

                foreach ($emailDescriptions as $key => $email) {
                    $db_email = SsEmail::where('shop_id', $db_shop->id)->where('category', $key)->first();

                    if (!$db_email) {
                        $db_email = new SsEmail;
                        $db_email->shop_id = $db_shop->id;
                        $db_email->category = $key;
                        $db_email->description = '';
                        $db_email->active = 1;
                        $db_email->subject = $db_shop->name . $email['subject'];
                        $db_email->plain_text = '';
                        $db_email->html_body = $email['html_body'];
                        $db_email->days_ahead = $email['days_ahead'];
                        $db_email->save();
                    }
                }

                // Add Metafields For Shop
                if ($user->plan_id) {
                    $is_membership_expired = $this->is_membership_expired($user);
                    $this->membershipexpireMetaUpdate($user, $is_membership_expired);
                } else {
                    $metafieldJson = [
                        "metafield" => [
                            'namespace' => 'simplee',
                            'key' => 'is_membership_expired',
                            'value' => 0,
                            'type' => 'boolean'
                        ]
                    ];
                    $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json', $metafieldJson);
                }


                // Add portals code in db
                $this->createPortalsInDB($db_shop->id);
                if (!$is_exist_shop) {
                    $setting = SsSetting::where('shop_id', $db_shop->id)->first();
                    //                    $themeID = $this->getPublishTheme();
                    //                    ($themeID != '') ? addSnippetH($themeID, $user->id, false) : '';

                    // Add user metafields
                    logger('============== START:: AddMetafields ===========');
                    $metafields = Config::get('metafields.options'); // TODO: add columns to ss_settings to store the values in our app
                    $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json';
                    foreach ($metafields as $metafield) {
                        if ($metafield['key'] == 'restricted') {
                            $metafield['value'] = $setting->restricted_content;
                        }
                        $user->api()->rest('POST', $endPoint, array('metafield' => $metafield));
                    }
                    logger('============== END:: AddMetafields ===========');
                }
            }

            //   Register WebHooks
            $this->makeWebhooksFromConfig($user);

            // Register Webhooks


            // $webhookks = [
            //     [
            //         'topic' => 'app/uninstalled',
            //         'address' =>  env('AWS_ARN_WEBHOOK_ADDRESS')
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


            logger('============== END:: AfterAuthenticationJob ===========');
        } catch (\Exception $e) {
            logger('============== ERROR:: AfterAuthenticationJob ===========');
            logger($e);
            Bugsnag::notifyException($e);
        }
    }
}
