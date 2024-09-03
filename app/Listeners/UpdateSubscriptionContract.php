<?php

namespace App\Listeners;

use App\Events\CheckSubscriptionContract;
use App\Models\Shop;
use App\Models\SsContract;
use App\Models\SsSetting;
use App\Models\SsWebhook;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
class UpdateSubscriptionContract
{
    use ShopifyTrait;

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
        // try {
        //     logger('========== Listener:: UpdateSubscriptionContract ==========');
        //     $ids = $event->ids;
        //     $user = User::find($ids['user_id']);
        //     $shop = Shop::find($ids['shop_id']);
        //     $webhookResonse = SsWebhook::find($ids['webhook_id']);

        //     if ($webhookResonse) {
        //         $data = json_decode($webhookResonse->body);

        //         $shopify_contract_id = str_replace('gid://shopify/SubscriptionContract/', '', $data->admin_graphql_api_id);

        //         $contract = SsContract::where('shopify_contract_id', $shopify_contract_id)->where('shop_id', $shop->id)->firstOrFail();

        //         $shopify_customer_id = $data->customer_id;
        //         $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/' . $shopify_customer_id . '.json';

        //         $settings = SsSetting::where('shop_id', $shop->id)->first();
        //         $customer_tags = $settings->tag_customer;

        //         logger('============== START:: AddCustomerTags ===========');
        //         // If subscription is resumed, add cutomers tags
        //         if ($data['type'] == 'resumed') {
        //             $user->api()->rest('PUT', $endPoint, $customer_tags);
        //         }

        //         // If subscription is cancelled, paused, or expired, remove customer tags
        //         if ($data['type'] == 'paused' ||
        //             $data['type'] == 'cancelled' ||
        //             $data['type'] == 'expired') {
        //             $user->api()->rest('PUT', $endPoint, '');
        //         }
        //         logger('============== END:: AddCustomerTags ===========');

        //         // update contract status
        //         $contract->status = $data->status;
        //         $contract->save();
        //     }
        // } catch (\Exception $e) {
        //     logger($e);
        //     Bugsnag::notifyException($e);
        // }
    }
}
