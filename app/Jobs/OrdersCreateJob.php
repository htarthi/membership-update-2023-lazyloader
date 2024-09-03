<?php

namespace App\Jobs;

use App\Models\ExchangeRate;
use App\Models\Shop;
use App\Models\SsCustomer;
use App\Models\SsOrder;
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
use Osiset\ShopifyApp\Storage\Models\Plan;
use stdClass;

class OrdersCreateJob implements ShouldQueue
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
            logger('======================= Order Create webhook =====================');
            $domain = $this->shopDomain->toNative();
            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();
            $data = $this->data;

            // $is_exist = SsOrder::where('shopify_order_id', $data->id)->where('shop_id', $shop->id)->where('user_id', $user->id)->first();
            $topic = 'orders/create';
            $this->webhook($topic, $user->id, ($this->data));
            return Response::make('', 200);
            // $is_exist_order = SsOrder::where('shop_id', $shop->id)->where('user_id', $user->id)->where('shopify_order_id', $data->id)->first();
            // $order = ($is_exist_order) ? $is_exist_order : new SsOrder;

            // $sh_customer = $this->data->customer;
            // $db_customer = SsCustomer::where('shopify_customer_id', $sh_customer->id)->where('shop_id', $shop->id)->first();
            // if( $db_customer ){
            //     $db_customer_id = $db_customer->id;
            // }else{
            //     $customer = new SsCustomer;
            //     $customer->shop_id = $shop->id;
            //     $customer->shopify_customer_id = $sh_customer->id;
            //     $customer->first_name = $sh_customer->first_name;
            //     $customer->last_name = $sh_customer->last_name;
            //     $customer->email = $sh_customer->email;
            //     $customer->phone = $sh_customer->phone;
            //     $customer->notes = $sh_customer->note;
            //     $customer->total_orders = $sh_customer->orders_count;
            //     $customer->total_spend = $sh_customer->total_spent;
            //     $customer->total_spend_currency = $sh_customer->currency;
            //     $customer->currency_symbol = currencyH($sh_customer->currency);
            //     $customer->save();

            //     $db_customer_id = $customer->id;
            // }

        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }
        return response()->json(['data' => 'success'], 200);
    }
}
