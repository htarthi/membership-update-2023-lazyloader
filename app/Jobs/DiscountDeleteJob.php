<?php

namespace App\Jobs;

use App\Models\AutomaticDiscount;
use App\Models\ShippingDiscount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SsPlanGroup;

class DiscountDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $shopId ;
    public $userId ;
    public $data ;
    /**
     * Create a new job instance.
     */
    public function __construct($shopId,$userId,$data)
    {
        $this->shopId = $shopId;
        $this->userId = $userId;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger("=======================START :: DiscountDeleteJob =====================");
        $adminGraphqlApiId = $this->data['payload']['admin_graphql_api_id'] ?? null;
        if ($adminGraphqlApiId) {
            $numericId = basename($adminGraphqlApiId);
            if($numericId){
                //Product Discount Delete
                $productdisUpdate = AutomaticDiscount::where([
                    'user_id' => $this->userId,
                    'shop_id' => $this->shopId,
                    'discount_id' => $numericId
                ])->update(['discount_id' => null]);
                if($productdisUpdate){
                    $delPdis = AutomaticDiscount::where([
                        'user_id' => $this->userId,
                        'shop_id' => $this->shopId,
                        'discount_id' => null
                    ])->delete();
                    if($delPdis){
                        SsPlanGroup::where([
                            'user_id' => $this->userId,
                            'shop_id' => $this->shopId,
                        ])->update(['activate_product_discount' => false]);
                    }
                }
                //Shipping Discount Delete
                $productdisUpdate = ShippingDiscount::where([
                    'user_id' => $this->userId,
                    'shop_id' => $this->shopId,
                    'discount_id' => $numericId
                ])->update(['discount_id' => null]);
                if($productdisUpdate){
                    $delsdis = ShippingDiscount::where([
                        'user_id' => $this->userId,
                        'shop_id' => $this->shopId,
                        'discount_id' => null
                    ])->delete();
                    if($delsdis){
                        SsPlanGroup::where([
                            'user_id' => $this->userId,
                            'shop_id' => $this->shopId,
                        ])->update([
                            'activate_shipping_discount' => false,
                            'shipping_discount_message' => null,
                            'shipping_discount_code' => null,
                            'active_shipping_dic' => '%'
                        ]);
                    }

                }
            }

        }
        logger("=======================END :: DiscountDeleteJob =====================");
    }
}
