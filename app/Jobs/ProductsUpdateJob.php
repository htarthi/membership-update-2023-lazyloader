<?php

namespace App\Jobs;

use App\Events\CheckProductUpdate;
use App\Models\Shop;
use App\Models\SsContractLineItem;
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
class ProductsUpdateJob implements ShouldQueue
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
            $webhookId = $this->webhook('products/update', $user->id, json_encode($this->data));
            event(new CheckProductUpdate($webhookId, $user->id, $shop->id));
            return Response::make('', 200);
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            return Response::make('', 200);
        }
    }
}
