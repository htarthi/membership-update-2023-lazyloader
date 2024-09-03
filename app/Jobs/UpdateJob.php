<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SsCustomer;
use App\Models\Shop;
use App\Models\User;

class UpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // $user = User::get();
        // foreach($user as $newUser){
        //     $result = $newUser->api()->rest('POST', '/admin/api/' . env('SHOPIFY_API_VERSION') . '/webhooks.json', ['webhook' => [
        //         'topic' => 'discounts/delete',
        //         'address' =>  env('AWS_ARN_WEBHOOK_ADDRESS')
        //     ]]);
        // }
        // return "DONE";

        //Command dispatch(new UpdateJob());

        $chunkSize = 100;
        SsCustomer::chunk($chunkSize, function ($customers) {
            foreach ($customers as $custm) {
                logger("============================= START :: UpdateJob ");
                // logger($custm->id);
                $shop = Shop::find($custm->shop_id);
                if ($shop) {
                    $user = User::find($shop->user_id);
                    if ($user) {
                        $getCustomer = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/' . $custm->shopify_customer_id . '.json');
                        $getTag  = isset($getCustomer['body']->container['customer']['tags']) ? $getCustomer['body']->container['customer']['tags'] : '';
                        if ($getTag) {
                            $formattedStr = preg_replace('/\s*,\s*/', ',', $getTag);
                            $keyVals['tags'] = $formattedStr;
                            $parameter = [
                                "metafield" => [
                                    'namespace' => 'simplee',
                                    'key' => 'customer-discount-tags',
                                    'value' => json_encode($keyVals),
                                    'type' => 'json'
                                ]
                            ];
                            $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/' . $custm->shopify_customer_id . '/metafields.json', $parameter);
                        }
                    }
                }
                logger("============================= END :: UpdateJob ");
            }
            logger("============================= DONE JOB :: UpdateJob ");
        });
    }
}
