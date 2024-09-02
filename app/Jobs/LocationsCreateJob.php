<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\SsShippingProfile;
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
class LocationsCreateJob implements ShouldQueue
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
     * @return void
     */
    public function handle()
    {
        try {
            $domain = $this->shopDomain->toNative();
            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();
            $data = $this->data;
            $this->webhook('locations-create', $user->id, json_encode($this->data));
            if ($this->data->active) {
                $profiles = SsShippingProfile::where('shop_id', $shop->id)->get();
                foreach ($profiles as $pkey => $pval) {
                    $result = $this->createDeliveryProfile($user->id, $pval->id,  'gid://shopify/Location/' . $this->data->id);
                }
            }
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }
        return Response::make('', 200);
    }
}
