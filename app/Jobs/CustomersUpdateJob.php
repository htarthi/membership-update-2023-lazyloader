<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\SsCustomer;
use App\Models\SsDeletedProduct;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Response;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;
use stdClass;

class CustomersUpdateJob implements ShouldQueue
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
            $domain = $this->shopDomain->toNative();
            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();
            $data = $this->data;
            $this->webhook('customers-update', $user->id, json_encode($this->data));
            $is_existcustomer = SsCustomer::where('shop_id', $shop->id)->where('shopify_customer_id', $data->id)->first();
            if ($is_existcustomer) {
                $customer = $is_existcustomer;
                $customer->first_name = $data->first_name;
                $customer->last_name = $data->last_name;
                $customer->email = $data->email;
                $customer->phone = $data->phone;
                $customer->save();
            } else {
                // $customer = new SsCustomer;
                // $customer->shop_id = $shop->id;
                // $customer->shopify_customer_id = $data->id;
                // $customer->active = 1;
                // $customer->first_name = $data->first_name;
                // $customer->last_name = $data->last_name;
                // $customer->email = $data->email;
                // $customer->phone = $data->phone;
                // $customer->notes = $data->note;
                // $customer->total_orders = $data->orders_count;
                // $customer->total_spend = $data->total_spent;
                // $customer->total_spend_currency = $data->currency;
                // $customer->currency_symbol = currencyH($data->currency);
                // $customer->avg_order_value = ( $data->orders_count > 0 ) ? ($data->total_spent /  $data->orders_count) : 0;
                // $customer->save();
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }
        return Response::make('', 200);
    }
}
