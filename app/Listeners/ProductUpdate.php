<?php

namespace App\Listeners;

use App\Events\CheckProductUpdate;
use App\Models\Shop;
use App\Models\SsContractLineItem;
use App\Models\SsDeletedProduct;
use App\Models\SsPlanGroupVariant;
use App\Models\SsWebhook;
use App\Models\User;
use App\Traits\ShopifyTrait;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class ProductUpdate
{
    use ShopifyTrait;

    // private $statuswebhook_id = '';
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
     * @param  CheckProductUpdate  $event
     * @return void
     */
    public function handle(CheckProductUpdate $event)
    {
        try{


            $ids = $event->ids;


            $user = User::find($ids['user_id']);
            $shop = Shop::find($ids['shop_id']);
            // $this->statuswebhook_id = $ids['webhook_id'];
            // $webhookResonse = SsWebhook::find($ids['webhook_id']);


            $data = $ids['payload'];
            $variants = $data->variants;
            $sh_variantsId = [];
            if( !empty( $variants ) ){
                foreach ( $variants as $vkey=>$vval ){
                    // $sh_variantsId[] = $vval->id;

                    // $lineItems = SsContractLineItem::where('user_id',  $user->id)->where('shopify_product_id', $data->id)->where('shopify_variant_id', $vval->id)->get();

                    // foreach ($lineItems as $lkey => $lineitem) {
                    //     $lineitem->title = $data->title;
                    //     $lineitem->shopify_variant_title = $vval->title;
                    //     $lineitem->sku = $vval->sku;

                    //     $imageId = $vval->image_id;
                    //     $lineitem->shopify_variant_image = ($imageId) ? $this->fetchImageURL($data->images, $imageId) : null;

                    //     $lineitem->save();
                    // }

                    $imageId = $vval->image_id;
                    $image = ($imageId) ? $this->fetchImageURL($data->images, $imageId) : null;

                    $affected = \DB::table('ss_contract_line_items')
                      ->where('user_id', $user->id)
                      ->where('shopify_product_id', $data->id)
                      ->where('shopify_variant_id', $vval->id)
                      ->update(['title' => $data->title,
                                'shopify_variant_title' => $vval->title,
                                'sku' => $vval->sku,
                                'shopify_variant_image' => $image
                                ]);
                }

            }

            // $plan_Group_variant = SsPlanGroupVariant::where('user_id',  $user->id)->where('shopify_product_id', $data->id)->first();
            // if($plan_Group_variant){
            //     $plan_Group_variant->product_title = $data->title;
            //     $plan_Group_variant->save();
            // }

             $affected = \DB::table('ss_plan_group_variants')
                      ->where('user_id', $user->id)
                      ->where('shopify_product_id', $data->id)
                      ->update(['product_title' => $data->title]);

            // $products = SsContractLineItem::select('shopify_variant_id')->distinct()->where('user_id',  $user->id)->where('shopify_product_id', $data->id)->get();

            // foreach ($products as $key => $value) {
            //     if( !in_array($value->shopify_variant_id, $sh_variantsId) ){
            //         $cnt = SsContractLineItem::select('ss_contract_id')->distinct()->where('user_id',  $user->id)->where('shopify_product_id', $data->id)->where('shopify_variant_id', $value->shopify_variant_id)->count();
            //         $deleted_product = new SsDeletedProduct;
            //         $deleted_product->shop_id = $shop->id;
            //         $deleted_product->user_id = $user->id;
            //         $deleted_product->shopify_product_id =  $data->id;
            //         $deleted_product->shopify_variant_id = $value->shopify_variant_id;
            //         $deleted_product->subscriptions_impacted = $cnt;
            //         $deleted_product->active = 1;
            //         $deleted_product->save();
            //     }
            // }
            // \DB::table('ss_webhooks')->delete($this->statuswebhook_id);
            // $this->updateWebhookStatus($this->statuswebhook_id, 'processed', null);
            Cache::forget($ids['shop_id']);
            logger('=========>>>>>>>>  job done...');
        }catch ( \Exception $e ){
            logger('========== ERROR:: Listener:: ProductUpdate ==========');
            // $this->updateWebhookStatus($this->statuswebhook_id, 'error', $e);
            logger($e);
            Bugsnag::notifyException($e);
        }
    }

    public function fetchImageURL($images, $imageId){
        try{
            foreach ($images as $key => $image) {
                if($image->id == $imageId){
                    return $image->src;
                }
            }
            return null;
        }catch(\Exception $e){
            logger('========== ERROR:: fetchImageURL ==========');
            logger(json_encode($e));
        }
    }
}
