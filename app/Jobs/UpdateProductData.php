<?php

namespace App\Jobs;

use App\Events\CheckProductUpdate;
use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProductData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user, $data;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $data)
    {
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Update product data
        $shop = Shop::where('user_id', $this->user->id)->first();


        $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/products/'. $this->data['shopify_product_id'] . '.json';
        $result = $this->user->api()->rest('GET', $endPoint);


        if(!$result['errors']) {
            $data = $result['body']['product'];
            logger("user is an ===============>". $this->user->id );
            event(new CheckProductUpdate($this->user->id, $shop->id, $data));
        } else  {
            logger('=======> ERROR:: While fetching product from shopify');
        }
    }
}
