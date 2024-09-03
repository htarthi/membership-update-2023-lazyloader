<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\SsContractLineItem;
use App\Traits\ShopifyTrait;
use App\Models\User;
use App\Models\SsDeletedProduct;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Response;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;
use stdClass;

class ProductsDeleteJob implements ShouldQueue
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
            $this->webhook('products/delete', $user->id, json_encode($this->data));
            $productCnt = SsContractLineItem::select('ss_contract_id')->distinct()->where('user_id',  $user->id)->where('shopify_product_id', $data->id)->count();
            if ($productCnt > 0) {
                $deleted_product = new SsDeletedProduct;
                $deleted_product->shop_id = $shop->id;
                $deleted_product->user_id = $user->id;
                $deleted_product->shopify_product_id =  $data->id;
                $deleted_product->subscriptions_impacted = $productCnt;
                $deleted_product->active = 1;
                $deleted_product->save();
            }
            return Response::make('', 200);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            return Response::make('', 200);
        }
    }
}
