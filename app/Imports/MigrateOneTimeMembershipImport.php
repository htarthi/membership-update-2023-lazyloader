<?php

namespace App\Imports;

use App\Traits\ShopifyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\User;
use App\Models\Shop;
use App\Models\SsCustomer;
use App\Models\SsAnswer;
use App\Models\SsContractLineItem;
use App\Models\SsContract;
use App\Models\SsPlan;

class MigrateOneTimeMembershipImport implements ToModel, WithHeadingRow
{
    use ShopifyTrait;

    private $user_id;

    public function __construct($user_id)
    {
        \Log::info('metafieldImport');
        $this->user_id = $user_id;
    }

    public function model(array $row)
    {

      try{
        // logger($row);
        if($row['email'] != ''){
           $user = User::find($this->user_id);
           $shop = Shop::where('user_id', $user->id)->first();

           $filter['email'] = $row['email'];
           $sh_customer = $this->filterShopifyData($user, 'customers', 'id', $filter);

           if(empty($sh_customer)){
                // logger('Empty customer');
                $row['customer_email'] = $row['email'];
                $row['customer_firstname'] = $row['first_name'];
                $row['customer_lastname'] = $row['last_name'];
                $row['customer_tag'] = 'VIP-L';
                $this->createUpdateCustomer($row, $this->user_id);

                $filter['email'] = $row['email'];
                $sh_customer = $this->filterShopifyData($user, 'customers', 'id', $filter);
           } //end of if(empty($sh_customer))


            if(!empty($sh_customer)){
                logger('============ Customer Exist =============');
              $sh_customer = $sh_customer[0];
              $db_customer_id = $this->createDbCustomer($user, $shop, $sh_customer, $row);

              $group_data['ss_plan_group_id'] = '908';
              $group_data['ss_plan_id'] = '1392';
              $group_data['product_id'] = '6612970504266';
              $group_data['variant_id'] = '39688740634698';
              $group_data['customer_tag'] = 'VIP-L';
              $group_data['next_billing_date'] = '2032-01-01 00:00:00';
              $group_data['db_customer_id'] = $db_customer_id;

              $db_contract_id = $this->createDbContract($user, $shop, $sh_customer, $group_data);


              $group_data['db_contract_id'] = $db_contract_id;

              $db_lineitem_id = $this->createDbLineItem($user, $shop, $sh_customer, $group_data);

              $this->createDbAnswer($user, $row, $db_contract_id);

              $shop->member_number = $shop->member_number + 1;
              $shop->save();
           } // end of if(!empty($sh_customer))
        } // end of if($row['email'] != '')
      }catch(\Exception $e){
        logger("=============== ERROR:: MigrateOneTimeMembershipImport ===============");
        logger(json_encode($e));
      } // end of catch

        // if($row['gateway_customer_id'] != '' || $row['gateway_customer_id'] != null){

        //    $shopify_customer_id = '';
        //     // create customer if missing
        //     if($row['customer_shopify_id'] == '' || $row['customer_shopify_id'] == null){
        //         $shopify_customer_id = $this->createUpdateCustomer($row, $this->user_id);
        //     }

        //     if($shopify_customer_id != ''){

        //       $row['customer_shopify_id'] = $shopify_customer_id;

        //     // create customer payment method
        //       $paymentMethodIdResult = $this->createCustomerPaymentMethod($row, $this->user_id);

        //       logger(json_encode($paymentMethodIdResult));
        //        if(!$paymentMethodIdResult['success']){
        //           logger('paymentMethodIdResult :: ' . json_encode($paymentMethodIdResult));
        //           return $paymentMethodIdResult;
        //        }

        //        $row['paymentMethodId'] = $paymentMethodIdResult['message'];
        //        // $row['paymentMethodId'] = 'gid://shopify/CustomerPaymentMethod/d890f0d7275aa527c84af88884ed44e';

        //        $ContractDraftIdResult = $this->createSubscriptionContractInShopify($row, $this->user_id);

        //        if(!$ContractDraftIdResult['success'] || $ContractDraftIdResult['message'] == ''){
        //         // return false;
        //           // return response()->json(['data' => $ContractDraftIdResult], 200);
        //        }else{
        //          $lineItem['price'] = $row['line_item_price'];
        //          $lineItem['discount_type'] = '';
        //          $lineItem['discount_amount'] = 0;
        //          $lineItem['final_amount'] = $row['line_item_price'];
        //          $lineItem['shopify_variant_id'] = $row['line_item_variant_id'];
        //          $lineItem['quantity'] = $row['line_item_qty'];

        //          $contractLineAddResult = $this->subscriptionDraftLineAdd($this->user_id, $lineItem, $ContractDraftIdResult['message']);

        //          logger("========================= contractLineAddResult ========================");
        //          logger($contractLineAddResult);

        //          $shopify_contract_id = str_replace('gid://shopify/SubscriptionContract/', '', $contractLineAddResult['contractID']);
        //          // $mBillingAttempt = $this->createBillingAttemptAfterMigration($shopify_contract_id, $this->user_id);
        //          //    if($mBillingAttempt['isSuccess']){
        //          //        // $data->origin_order_id = $mBillingAttempt['order_id'];
        //          //    }
        //          // $res = $this->commitDraft($user->id, $ContractDraftIdResult['message']);

        //          // dd($contractLineAddResult);
        //       // $this->createSubscriptionContractInShopify($row, $this->user_id);
        //       // TODO: Implement model() method.

        //        }
        //    }
        // }
    } //end of model function


    public function createDbCustomer($user, $shop, $sh_customer, $row){
      try{
         logger('========== createDbCustomer ===========');
         $isExistCustomer = SsCustomer::where('shop_id', $shop->id)->where('shopify_customer_id', $sh_customer['id'])->first();

         $db_customer = ($isExistCustomer) ? $isExistCustomer : new SsCustomer;
         $db_customer->shop_id = $shop->id;
         $db_customer->shopify_customer_id = $sh_customer['id'];
         $db_customer->active = 1;
         $db_customer->first_name = $row['first_name'];
         $db_customer->last_name = $row['last_name'];
         $db_customer->email = $row['email'];
         $db_customer->date_first_order = date('Y-m-d H:i:s', strtotime('2021-08-03'));
         $db_customer->save();

         return $db_customer->id;
      }catch(\Exception $e){
        logger("=============== ERROR:: createDbCustomer ===============");
        logger(json_encode($e));
      }
    }

    public function createDbContract($user, $shop, $sh_customer, $group_data){
      try{
        logger('========== createDbContract ===========');
         $db_plan = SsPlan::find($group_data['ss_plan_id']);

         $memberCount = SsContract::select('member_number')->where('shop_id', $shop->id)->orderBy('created_at', 'desc')->first();
         $db_contract = new SsContract;
         $db_contract->shop_id = $shop->id;
         $db_contract->user_id = $user->id;
         $db_contract->shopify_customer_id = $sh_customer['id'];
         $db_contract->ss_customer_id = $group_data['db_customer_id'];
         $db_contract->ss_plan_groups_id = $group_data['ss_plan_group_id'];
         $db_contract->ss_plan_id = $group_data['ss_plan_id'];
         $db_contract->status = 'active';
         $db_contract->next_order_date = $group_data['next_billing_date'];
         $db_contract->next_processing_date = $group_data['next_billing_date'];
         $db_contract->tag_customer = $group_data['customer_tag'];
         $db_contract->is_onetime_payment = 1;
         $db_contract->order_count = 0;
         $db_contract->member_number = $memberCount->member_number + 1;

        $db_contract->is_set_min = $db_plan->is_set_min;
        $db_contract->is_set_max = $db_plan->is_set_max;
        $db_contract->trial_available = $db_plan->trial_available;
        $db_contract->pricing2_after_cycle = $db_plan->pricing2_after_cycle;
        $db_contract->pricing_adjustment_value = $db_plan->pricing_adjustment_value;
        $db_contract->pricing2_adjustment_value = $db_plan->pricing2_adjustment_value;

        $db_contract->billing_interval = $db_plan->billing_interval;
        $db_contract->billing_interval_count = $db_plan->billing_interval_count;
        $db_contract->billing_min_cycles = $db_plan->billing_min_cycles;
        $db_contract->billing_max_cycles = $db_plan->billing_max_cycles;
        $db_contract->billing_anchor_day = $db_plan->billing_anchor_day;
        $db_contract->billing_anchor_type = $db_plan->billing_anchor_type;
        $db_contract->billing_anchor_month = $db_plan->billing_anchor_month;

        $db_contract->is_migrated = 1;
         $db_contract->save();

         return $db_contract->id;
      }catch(\Exception $e){
        logger("=============== ERROR:: createDbContract ===============");
        logger(json_encode($e));
      }
    }

    public function createDbLineItem($user, $shop, $sh_customer, $group_data){
      try{
         logger('========== createDbLineItem ===========');
         $group_data['price'] = 79.99;
         $group_data['discount_amount'] = $group_data['price'];
         $group_data['currency'] = '';
         $group_data['title'] = 'RockRooster Lifelong Membership VIP System Get $200+ Gift';
         $group_data['shopify_variant_image'] = 'https://cdn.shopify.com/s/files/1/0048/9845/5626/products/banner1_76202e08-becc-4568-bc01-bc169743a597.jpg?v=1625865769';
         $group_data['shopify_variant_title'] = '';

         $db_lineitem = new SsContractLineItem;
         $db_lineitem->user_id = $user->id;
         $db_lineitem->ss_contract_id = $group_data['db_contract_id'];
         $db_lineitem->shopify_product_id = $group_data['product_id'];
         $db_lineitem->shopify_variant_id = $group_data['variant_id'];
         $db_lineitem->title = $group_data['title'];
         $db_lineitem->price = $group_data['price'];
         $db_lineitem->discount_amount = $group_data['discount_amount'];

         $db_lineitem->shopify_variant_title = $group_data['shopify_variant_title'];
         $db_lineitem->shopify_variant_image = $group_data['shopify_variant_image'];

         $db_lineitem->selling_plan_id = 469106762;
         $db_lineitem->selling_plan_name = 'Lifelong';
         $db_lineitem->sku = 'VK6253 US 04';
         $db_lineitem->save();

         return $db_lineitem->id;
      }catch(\Exception $e){
        logger("=============== ERROR:: createDbCustomer ===============");
        logger(json_encode($e));
      }
    }

    public function createDbAnswer($user, $row, $db_contract_id){
      try{
         logger('========== createDbAnswer ===========');
         $ansFields = [];
         $ansFields['Shoe Size'] = $row['shoe_size'];
         $ansFields['Gift Card Number'] = $row['gift_card_no'];
         $ansFields['Social Media'] = $row['social_media'];
         $ansFields['Email Sending'] = $row['email_sending'];

         foreach ($ansFields as $key => $value) {
             $db_answer = new SsAnswer;
             $db_answer->ss_contract_id = $db_contract_id;
             $db_answer->question = $key;
             $db_answer->answer = $value;
             $db_answer->save();
         }
      }catch(\Exception $e){
        logger("=============== ERROR:: createDbCustomer ===============");
        logger(json_encode($e));
      }
    }
} // end of class
