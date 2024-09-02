<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\Install;
use App\Models\Shop;
use App\Models\SsWebhook;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;
use stdClass;
class ShopUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ShopifyTrait;
    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string   $shopDomain The shop's myshopify domain
     * @param stdClass $data    The webhook data (JSON decoded)
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return \Illuminate\Http\Response
     */
    public function handle()
    {
        try {
            logger('============== START:: ShopUpdateJob ===========');
            $domain = $this->shopDomain->toNative();
            $user = User::where('name', $domain)->first();
            $is_exist_shop = Shop::where('user_id', $user->id)->first();
            $this->webhook('shop/update', $user->id, json_encode($this->data));
            $sh_shop = $this->data;
            $db_shop = ($is_exist_shop) ? $is_exist_shop : new Shop;
            $db_shop->user_id = $user['id'];
            $db_shop->shopify_store_id = $sh_shop->id;
            $db_shop->active = true;
            $db_shop->ts_last_deactivation = date('Y-m-d H:i:s');
            $db_shop->test_store = ($sh_shop->plan_name == 'partner_test');
            $db_shop->name = $sh_shop->name;
            $db_shop->email = $sh_shop->email;
            $db_shop->myshopify_domain = $sh_shop->myshopify_domain;
            $db_shop->domain = $sh_shop->domain;
            $db_shop->owner = $sh_shop->shop_owner;
            $db_shop->owner = $sh_shop->shop_owner;
            $db_shop->shopify_plan = $sh_shop->plan_name;
            $db_shop->timezone = $sh_shop->timezone;
            $db_shop->address1 = $sh_shop->address1;
            $db_shop->address2 = $sh_shop->address2;
            $db_shop->checkout_api_supported = $sh_shop->checkout_api_supported;
            $db_shop->city = $sh_shop->city;
            $db_shop->country = $sh_shop->country;
            $db_shop->country_code = $sh_shop->country_code;
            $db_shop->country_name = $sh_shop->country_name;
            $db_shop->country_taxes = $sh_shop->county_taxes;
            $db_shop->ss_created_at = ($is_exist_shop) ? $is_exist_shop->ss_created_at : date('Y-m-d H:i:s');
            $db_shop->customer_email = $sh_shop->customer_email;
            $db_shop->currency = $sh_shop->currency;
            $db_shop->currency_symbol = currencyH($sh_shop->currency);
            $db_shop->enabled_presentment_currencies = json_encode($sh_shop->enabled_presentment_currencies);
            $db_shop->eligible_for_payments = $sh_shop->eligible_for_payments;
            $db_shop->has_discounts = $sh_shop->has_discounts;
            $db_shop->has_gift_cards = $sh_shop->has_gift_cards;
            $db_shop->has_storefront = $sh_shop->has_storefront;
            $db_shop->iana_timezone = $sh_shop->iana_timezone;
            $db_shop->latitude = $sh_shop->latitude;
            $db_shop->longitude = $sh_shop->longitude;
            $db_shop->money_format = $sh_shop->money_format;
            $db_shop->money_in_emails_format = $sh_shop->money_in_emails_format;
            $db_shop->money_with_currency_format = $sh_shop->money_with_currency_format;
            $db_shop->money_with_currency_in_emails_format = $sh_shop->money_with_currency_in_emails_format;
            $db_shop->multi_location_enabled = $sh_shop->multi_location_enabled;
            $db_shop->password_enabled = $sh_shop->password_enabled;
            $db_shop->phone = $sh_shop->phone;
            $db_shop->pre_launch_enabled = $sh_shop->pre_launch_enabled;
            $db_shop->primary_locale = $sh_shop->primary_locale;
            $db_shop->province = $sh_shop->province;
            $db_shop->province_code = $sh_shop->province_code;
            $db_shop->requires_extra_payments_agreement = $sh_shop->requires_extra_payments_agreement;
            $db_shop->setup_required = $sh_shop->setup_required;
            $db_shop->taxes_included = $sh_shop->taxes_included;
            $db_shop->tax_shipping = $sh_shop->tax_shipping;
            $db_shop->tbl_updated_at = date('Y-m-d H:i:s');
            $db_shop->weight_unit = $sh_shop->weight_unit;
            $db_shop->zip = $sh_shop->zip;
            $db_shop->save();
            logger('============== END:: Shop Update Webhook ===========');
            return \Illuminate\Support\Facades\Response::make('', 200);
        } catch (\Exception $e) {
            logger('============== ERROR:: Shop Update Webhook ===========');
            logger(json_encode($e));
            Bugsnag::notifyException($e);
            return Response::make('', 200);
        }
    }
}
