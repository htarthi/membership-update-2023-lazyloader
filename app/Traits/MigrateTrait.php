<?php

namespace App\Traits;

use App\Jobs\MigrateMembershipsJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Shop;
use App\Models\SsContract;
use App\Models\SsCustomer;
use App\Models\SsContractLineItem;
use App\Models\SsPlanGroup;
use App\Models\SsPlan;
use App\Models\SsPlanGroupVariant;
use App\Models\SsSetting;
use App\Models\SsEmail;
use Illuminate\Support\Facades\Session;
use App\Models\SsAnswer;

/**
 * Trait MigrateTrait
 * @package App\Traits
 */
trait MigrateTrait
{
  use ShopifyTrait;

  public function migrateSubscriptions($user)
  {
    try {
      logger("====================== START:: migrateSubscriptions ======================");
      $file_path = Storage::disk('public')->path('migrate_csv/TEMPLATE - Simplee Import - Sheet.csv');
      MigrateMembershipsJob::dispatch($file_path, $user->id)->onQueue('UpdateServer');

      $customer['email'] = 'ruchita12.crawlapps@gmail.com';
      $customer['first_name'] = 'Ruchi';
      $customer['last_name'] = 'Gelani';
      $customerID = $this->getCustomer($customer, $user);
      $subscription = [
        'next_billing_date' => '2020-06-01',
        'currencyCode' => 'USD',
        'paymentMethodId' => "869e7a39",
        'billingPolicy' => [
          'interval' => 'MONTH',
          'intervalCount' => 1,
          'minCycles' => 3
        ],
        'deliveryPolicy' => [
          'interval' => 'MONTH',
          'intervalCount' => 1
        ],
        'deliveryMethodShippingAddress' => [
          'firstName' => "John",
          'lastName' => "McDonald",
          'address1' => "33 New Montgomery St",
          'address2' => "#750",
          'city' => "San Francisco",
          'provinceCode' => "CA",
          'countryCode' => 'US',
          'zip' => "94105"
        ],
        'deliveryPrice' => 14.99
      ];
      // $file = ImageTrait::makeImage($request->file('csv_file'), 'uploads/');
      // $this->createSubscriptionContract($subscription, $user, $customerID);
    } catch (\Exception $e) {
      logger("============= ERROR ::  migrateSubscriptions =============");
      logger($e);
      return response()->json(['data' => $e->getMessage()], 422);
    }
  }

  public function migrateMember($data, $user, $sessionKey)
  {
    try {
      $shop = Shop::where('user_id', $user->id)->first();
      $db_plan_group = SsPlanGroup::find($data['ss_plan_group_id']);
      $filter['email'] = $data['email'];
      $sh_customer = [];
      $sh_customer = $this->filterShopifyData($user, 'customers', 'id', $filter);
      $row['customer_email'] = $data['email'];
      $row['customer_firstname'] = $data['firstname'];
      $row['customer_lastname'] = $data['lastname'];
      $row['customer_tag'] = $db_plan_group->tag_customer;
      $sh_customer[0]['id'] = $this->createUpdateCustomerByMarchant($row, $user->id);
      if (!empty($sh_customer) && $sh_customer[0]['id'] != '') {
        $sh_customer = $sh_customer[0];
        $db_customer_id = $this->createDbCustomer($user, $shop, $sh_customer, $data);
        $data['db_customer_id'] = $db_customer_id;
        $db_contract_id = $this->createDbContract($user, $shop, $sh_customer, $data, $db_plan_group);
        $data['db_contract_id'] = $db_contract_id;
        $db_lineitem_id = $this->createDbLineItem($user, $shop, $sh_customer, $data);
        $this->saveActivity($user->id, $db_customer_id, $db_contract_id, 'System', 'Membership created manually by merchant');
        // $this->createDbAnswer($user, $row, $db_contract_id);
        $shop->member_number = $shop->member_number + 1;
        $shop->save();
        // send customer account invitation if checkbox is checked
        $setting = SsSetting::select('email_from_email', 'email_from_name')->where('shop_id', $shop->id)->first();
        if ($data['is_sendinvitation']) {
          $this->sendAccountInvites($user, $sh_customer['id'], $setting->email_from_email, $data['email']);
        } // end of if($data['is_sendinvitation']){
        // send customer new membership mail if checkbox is checked
        if ($data['is_sendnewmembershipmail']) {
          $email = SsEmail::where('shop_id', $shop->id)->where('category', 'new_membership_to_customer')->first();
          $res = sendMailH($email->subject, $email->html_body, $setting->email_from_email, $data['email'], $setting->email_from_name, $shop->id, $db_customer_id);
        } // end of if($data['is_sendnewmembershipmail']){
      } // end of if(!empty($sh_customer))
      else {
        ($sessionKey != '') ? $this->setSession($data['email'], $sessionKey) : '';
        logger($data['email'] . ' Customer Not created');
      }
    } catch (\Exception $e) {
      logger("============= ERROR ::  migrateMember =============");
      logger($e);
      return response()->json(['data' => $e->getMessage()], 422);
    }
  }

  public function setSession($msg, $sessionKey)
  {
    $data = Session::get($sessionKey);
    array_push($data, $msg);
    session([$sessionKey =>  $data]);
    \Log::info(Session::get($sessionKey));
  }

  public function createDbCustomer($user, $shop, $sh_customer, $row)
  {
    try {
      $isExistCustomer = SsCustomer::where('shop_id', $shop->id)->where('shopify_customer_id', $sh_customer['id'])->first();
      $db_customer = ($isExistCustomer) ? $isExistCustomer : new SsCustomer;
      $db_customer->shop_id = $shop->id;
      $db_customer->shopify_customer_id = $sh_customer['id'];
      $db_customer->active = 1;
      $db_customer->first_name = $row['firstname'];
      $db_customer->last_name = $row['lastname'];
      $db_customer->email = $row['email'];
      $db_customer->save();
      return $db_customer->id;
    } catch (\Exception $e) {
      logger("============= ERROR ::  createDbCustomer =============");
      logger($e->getMessage());
      return response()->json(['data' => $e->getMessage()], 422);
    }
  }

  public function createDbContract($user, $shop, $sh_customer, $data, $db_plan_group)
  {
    try {

      $db_plan = SsPlan::where('ss_plan_group_id', $data['ss_plan_group_id'])->first();
      $data['next_billing_date'] = date('Y-m-d H:i:s', strtotime('+10 years'));

      $memberCount = SsContract::select('member_number')->where('shop_id', $shop->id)->orderBy('created_at', 'desc')->first();

      $db_contract = new SsContract;
      $db_contract->shop_id = $shop->id;
      $db_contract->user_id = $user->id;
      $db_contract->shopify_customer_id = $sh_customer['id'];
      $db_contract->ss_customer_id = $data['db_customer_id'];
      $db_contract->ss_plan_groups_id = $data['ss_plan_group_id'];
      $db_contract->ss_plan_id = $db_plan->id;
      $db_contract->status = 'active';
      $db_contract->status_display = 'Active';
      $db_contract->next_order_date = $data['next_billing_date'];
      $db_contract->next_processing_date = $data['next_billing_date'];
      $db_contract->tag_customer = $db_plan_group->tag_customer;
      $db_contract->is_onetime_payment = 1;
      $db_contract->order_count = 0;
      $db_contract->member_number = ($memberCount) ? $memberCount->member_number + 1 : 1;

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

      $db_contract->is_migrated = 0;
      $db_contract->save();

      return $db_contract->id;
    } catch (\Exception $e) {
      logger("============= ERROR ::  createDbContract =============");
      logger($e->getMessage());
      return response()->json(['data' => $e->getMessage()], 422);
    }
  }

  public function createDbLineItem($user, $shop, $sh_customer, $group_data)
  {
    try {
      $lineitem = SsPlanGroupVariant::where('user_id', $user->id)->where('ss_plan_group_id', $group_data['ss_plan_group_id'])->first();
      $sh_product = $this->getShopifyData($user, $lineitem->shopify_product_id, 'product', $fields = 'id,variants');
      $db_lineitem = new SsContractLineItem;
      $db_lineitem->user_id = $user->id;
      $db_lineitem->ss_contract_id = $group_data['db_contract_id'];
      $db_lineitem->shopify_product_id = $lineitem->shopify_product_id;
      $db_lineitem->title = $lineitem->product_title;
      if (!empty($sh_product)) {
        $sh_variants = $sh_product['variants'];
        $variant = $sh_variants[0];
        $db_lineitem->price = $variant['price'];
        $db_lineitem->discount_amount = $variant['price'];
        $db_lineitem->shopify_variant_id = $variant['id'];
        $db_lineitem->currency = $shop->currency;
        $db_lineitem->currency_symbol = $shop->currency_symbol;
        if (count($sh_variants) > 1) {
          $db_lineitem->shopify_variant_title = $variant['title'];
        }
      }
      $db_lineitem->save();
      return $db_lineitem->id;
    } catch (\Exception $e) {
      logger("============= ERROR ::  createDbLineItem =============");
      logger($e->getMessage());
      return response()->json(['data' => $e->getMessage()], 422);
    }
  }

  public function createDbAnswer($user, $row, $db_contract_id)
  {
    try {
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
    } catch (\Exception $e) {
      logger("============= ERROR ::  createDbAnswer =============");
      logger($e->getMessage());
      return response()->json(['data' => $e->getMessage()], 422);
    }
  }
}
