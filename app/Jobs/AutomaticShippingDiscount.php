<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Shop;
use App\Models\SsPlanGroup;
use App\Models\User;
use App\Traits\ShopifyTrait;
use App\Models\ShippingDiscount;

class AutomaticShippingDiscount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ShopifyTrait;
    /**
     * Create a new job instance.
     */
    public $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger("================================= START :: AutomaticShippingDiscount ================================== ");
        $shop = Shop::where('user_id',$this->userId)->first();
        $productIds = ShippingDiscount::where(['shop_id' => $shop->id , 'user_id' =>  $shop->user_id])->pluck('product_id');
        $exist = ShippingDiscount::where(['shop_id' => $shop->id ,'user_id' =>  $shop->user_id])->get();
        $disIdfrom = ShippingDiscount::where(['shop_id' => $shop->id , 'user_id' =>  $shop->user_id])->whereNotNull('discount_id')->withTrashed()->first();
        $jsonArray = [
            'product' => [],
            'discount_type' => 'shipping_discounts',
        ];
        $dicId = '';
        if($productIds->isNotEmpty()){
            if($disIdfrom){
                $dicId = $disIdfrom->discount_id ? "gid://shopify/DiscountAutomaticNode/". $disIdfrom->discount_id : null;
                if($disIdfrom->discount_id){
                    ShippingDiscount::where('discount_id', null)
                    ->where('shop_id', $shop->id)
                    ->where('user_id',  $shop->user_id)
                    ->update(['discount_id' => $disIdfrom->discount_id]);
                }
            }
            foreach ($productIds as $productId) {
                $productDiscounts = $exist->where('product_id', $productId);
                if ($productDiscounts->isNotEmpty()) {
                    $autoDisArray = [];
                    foreach ($productDiscounts as $discount) {
                        $autoDisArray[] = [
                            'discount_type' => 'product_discounts',
                            'discount_method' =>  $discount->shipping_discount_type == '$' ? 'fixedamount' : 'percentage',
                            'discount_value' =>  $discount->shipping_discount ?? null,
                            'discount_message' => $discount->shipping_discount_message ? $discount->shipping_discount_message : "Shipping Automatic Discount",
                            'tags' => $discount->customer_tag ?? null,

                        ];
                    }
                    $jsonArray['product']["gid://shopify/Product/{$productId}"] = $autoDisArray;
                }
            }

            $jsonString = json_encode($jsonArray, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $escapedJsonString = addslashes($jsonString);
            if ($dicId) {
                $getExistDisCountsquery = '
                    query {
                        automaticDiscountNode(id: "' . $dicId . '") {
                        metafields(first: 1) {
                            edges {
                            node {
                                id
                                namespace
                                key
                                value
                                type
                            }
                            }
                        }
                        }
                    }
                ';
                $resultMeatfileds = $this->graphQLRequest($this->userId, $getExistDisCountsquery);
                $getMetafieldsId = isset($resultMeatfileds['body']->container['data']['automaticDiscountNode']['metafields']['edges'][0]['node']['id']) ? $resultMeatfileds['body']->container['data']['automaticDiscountNode']['metafields']['edges'][0]['node']['id'] : '';
                if ($getMetafieldsId) {
                    $query = '
                        mutation {
                            discountAutomaticAppUpdate(
                                automaticAppDiscount: {
                                    combinesWith: {
                                        productDiscounts : true,
                                        shippingDiscounts : true,
                                    },
                                    metafields: [
                                        {
                                            id: "' . $getMetafieldsId . '",
                                            value: "' . $escapedJsonString . '",
                                            type: "json"
                                        }
                                    ]
                                },
                                id: "' .$dicId.  '"
                            ) {
                                automaticAppDiscount {
                                    discountId
                                    title
                                    startsAt
                                    endsAt
                                    status
                                    appDiscountType {
                                      appKey
                                      functionId
                                    }
                                    combinesWith {
                                      orderDiscounts
                                      productDiscounts
                                      shippingDiscounts
                                    }
                                }
                                userErrors {
                                    field
                                    message
                                }
                            }
                        }
                    ';
                    $this->graphQLRequest($this->userId, $query);
                }
            }else{
                if($escapedJsonString){
                    $query = 'mutation {
                        discountAutomaticAppCreate(automaticAppDiscount: {
                            title: "Shipping Automatic Discount",
                            functionId: "9c53cf37-c9cf-4508-b631-c8273a275eba",
                            combinesWith: {
                                productDiscounts : true,
                                shippingDiscounts : true,
                            },
                            metafields: [
                                {
                                    namespace: "shipping-discount",
                                    key: "simplee-configuration",
                                    value: "' . $escapedJsonString . '",
                                    type: "json"
                                }
                            ],
                            startsAt: "2021-06-22T00:00:00"
                        }) {
                            automaticAppDiscount {
                                discountId
                                title
                                startsAt
                                endsAt
                                status
                                appDiscountType {
                                  appKey
                                  functionId
                                }
                                combinesWith {
                                  orderDiscounts
                                  productDiscounts
                                  shippingDiscounts
                                }
                            }
                            userErrors {
                                field
                                message
                            }
                        }
                    }';
                    $automaticAppDiscount =  $this->graphQLRequest($this->userId, $query);
                    $getURL = isset($automaticAppDiscount['body']->container['data']['discountAutomaticAppCreate']['automaticAppDiscount']['discountId']) ? $automaticAppDiscount['body']->container['data']['discountAutomaticAppCreate']['automaticAppDiscount']['discountId'] : null;
                    if($getURL){
                        $lastPart = strrchr($getURL, '/');
                        $discountID =  (int) ltrim($lastPart, '/');
                        ShippingDiscount::where('discount_id', null)
                        ->where('shop_id', $shop->id)
                        ->where('user_id',  $shop->user_id)
                        ->update(['discount_id' => $discountID]);
                    }
                }
            }
        }else{
            $deleId = $disIdfrom->discount_id ?? null;
            $deldiscount = $deleId ? "gid://shopify/DiscountAutomaticNode/". $deleId : null;
            if($deldiscount){
                $mutation = '
                    mutation discountAutomaticDelete{
                    discountAutomaticDelete(id: "' . $deldiscount . '") {
                        deletedAutomaticDiscountId
                        userErrors {
                        field
                        code
                        message
                        }
                    }
                    }
                ';
                $response = $this->graphQLRequest($this->userId, $mutation);
                ShippingDiscount::where([
                    'user_id' => $shop->user_id,
                    'shop_id' => $shop->id
                ])->withTrashed()->update(['discount_id' => null]);
            }
        }
        logger("================================= END :: AutomaticShippingDiscount ================================== ");
    }
}
