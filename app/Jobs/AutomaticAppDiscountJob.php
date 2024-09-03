<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AutomaticDiscount;
use App\Models\Shop;
use App\Models\User;
use App\Traits\ShopifyTrait;

use function Laravel\Prompts\form;

class AutomaticAppDiscountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ShopifyTrait;
    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain
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
        logger("================================= START :: AutomaticAppDiscountJob ================================== ");
        $shop = Shop::where('user_id',$this->userId)->first();
        $productIds = AutomaticDiscount::where(['shop_id' => $shop->id , 'user_id' =>  $shop->user_id])->pluck('product_id');
        $exist = AutomaticDiscount::where(['shop_id' => $shop->id ,'user_id' =>  $shop->user_id])->get();
        $disIdfrom = AutomaticDiscount::where(['shop_id' => $shop->id , 'user_id' =>  $shop->user_id])->whereNotNull('discount_id')->withTrashed()->first();

        $collectionArray = AutomaticDiscount::where(['shop_id' => $shop->id , 'user_id' =>  $shop->user_id])
        ->pluck('collection_id')
        ->flatMap(function ($collection) {
            if($collection){
                return array_map(function ($id) {
                    return 'gid://shopify/Collection/' . $id;
                }, explode(',', $collection));
            }
        })
        ->unique()
        ->toArray();

        $jsonArray = [
            'product' => [],
            'discount_type' => 'product_discounts',
            'selectedCollectionIds' => $collectionArray ? $collectionArray : [],
        ];
        $dicId = '';
        if($productIds->isNotEmpty()){
            if($disIdfrom){
                $dicId = $disIdfrom->discount_id ? "gid://shopify/DiscountAutomaticNode/". $disIdfrom->discount_id : null;
                if($disIdfrom->discount_id){
                    AutomaticDiscount::where('discount_id', null)
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
                        $savecollection = [];
                        if ($discount->collection_id) {
                            $savecollection = array_map(function($val) {
                                return ["gid://shopify/Collection/" . $val];
                            }, explode(',', $discount->collection_id));
                        }
                        $autoDisArray[] = [
                            'collectionId' => $savecollection ? $savecollection[0] : null,
                            'in_any_collection' => $discount->collection_id ? true : false,
                            'discount_type' => 'product_discounts',
                            'discount_method' =>  $discount->collection_discount_type == '$' ? 'fixedamount' : 'percentage',
                            'discount_value' =>  $discount->collection_discount ?? null,
                            'discount_message' =>  $discount->collection_message ? $discount->collection_message : "Product Automatic Discount",
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
                                        productDiscounts : true
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
                    $result = $this->graphQLRequest($this->userId, $query);
                    logger($result);
                }
            }else{
                if($escapedJsonString){
                    $query = 'mutation {
                        discountAutomaticAppCreate(automaticAppDiscount: {
                            title: "Product Automatic Discount",
                            functionId: "e51f847d-b51e-44c7-8eee-c78065bd8dc5",
                            combinesWith: {
                                productDiscounts : true
                                shippingDiscounts : true,
                            },
                            metafields: [
                                {
                                    namespace: "product-discount",
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
                    logger($automaticAppDiscount);
                    $getURL = isset($automaticAppDiscount['body']->container['data']['discountAutomaticAppCreate']['automaticAppDiscount']['discountId']) ? $automaticAppDiscount['body']->container['data']['discountAutomaticAppCreate']['automaticAppDiscount']['discountId'] : null;
                    if($getURL){
                        $lastPart = strrchr($getURL, '/');
                        $discountID =  (int) ltrim($lastPart, '/');
                        AutomaticDiscount::where('discount_id', null)
                        ->where('shop_id', $shop->id)
                        ->where('user_id',  $shop->user_id)
                        ->update(['discount_id' => $discountID]);
                    }
                }
            }
        }else {
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
                $this->graphQLRequest($this->userId, $mutation);
                AutomaticDiscount::where([
                    'user_id' => $shop->user_id,
                    'shop_id' => $shop->id
                ])->withTrashed()->update(['discount_id' => null]);
            }
        }
        logger("================================= END :: AutomaticAppDiscountJob ================================== ");
    }
}

