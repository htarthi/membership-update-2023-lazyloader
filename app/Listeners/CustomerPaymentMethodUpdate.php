<?php

namespace App\Listeners;

use App\Events\CheckCustomerPaymentMethodUpdate;
use App\Models\SsContract;
use App\Models\SsWebhook;
use App\Traits\ShopifyTrait;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
class CustomerPaymentMethodUpdate
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
     * @param  CheckCustomerPaymentMethodUpdate  $event
     * @return void
     */
    public function handle(CheckCustomerPaymentMethodUpdate $event)
    {
        try {
            $ids = $event->ids;
            $this->statuswebhook_id = $ids['webhook_id'];
            $data = json_decode($ids['payload']);
            logger('========== Listener:: CustomerPaymentMethodUpdate :: Webhook :: ' . $ids['webhook_id'] . ' ==> shopify_id :: ' . $data->admin_graphql_api_id . '==========');

            $db_contracts = SsContract::where('shop_id', $ids['shop_id'])->where('user_id', $ids['user_id'])->where('cc_id', $data->token)->where('shopify_customer_id', $data->customer_id)->pluck('id');
            $payment_instrument = $data->payment_instrument;
            
            if ($data->instrument_type == 'CustomerCreditCard') {
                SsContract::whereIn('id', $db_contracts)->update(['payment_method' => 'credit_card', 'cc_brand' => $payment_instrument->brand, 'cc_expiryMonth' => $payment_instrument->month, 'cc_expiryYear' => $payment_instrument->year, 'cc_lastDigits' => $payment_instrument->last_digits, 'cc_maskedNumber' => "•••• •••• •••• $payment_instrument->last_digits", 'cc_name' => $payment_instrument->name]);
            } elseif ($data->instrument_type == 'CustomerPaypalBillingAgreement') {
                SsContract::whereIn('id', $db_contracts)->update(['payment_method' => 'paypal', 'paypal_account' => $payment_instrument->paypalAccountEmail, 'paypal_inactive' => $payment_instrument->inactive, 'paypal_isRevocable' => $payment_instrument->isRevocable]);
            } elseif ($data->instrument_type == 'CustomerRemoteCreditCard') {
                SsContract::whereIn('id', $db_contracts)->update(['payment_method' => 'credit_card', 'cc_brand' => $payment_instrument->brand, 'cc_expiryMonth' => $payment_instrument->month, 'cc_expiryYear' => $payment_instrument->year, 'cc_lastDigits' => $payment_instrument->last_digits, 'cc_maskedNumber' => "•••• •••• •••• $payment_instrument->last_digits", 'cc_name' => $payment_instrument->name]);
            } elseif ($data->instrument_type == 'CustomerShopPayAgreement') {
                SsContract::whereIn('id', $db_contracts)->update(['payment_method' => 'credit_card', 'cc_brand' => $payment_instrument->brand, 'cc_expiryMonth' => $payment_instrument->month, 'cc_expiryYear' => $payment_instrument->year, 'cc_lastDigits' => $payment_instrument->last_digits, 'cc_maskedNumber' => "•••• •••• •••• $payment_instrument->last_digits", 'cc_name' => $payment_instrument->name]);
            }
            $this->updateWebhookStatus($this->statuswebhook_id, 'processed', null);
        } catch (\Exception $e) {
            logger('========== ERROR:: CustomerPaymentMethodUpdate ==========');
            logger($e);
            $this->updateWebhookStatus($this->statuswebhook_id, 'error', $e);
            Bugsnag::notifyException($e);
        }
    }
}
