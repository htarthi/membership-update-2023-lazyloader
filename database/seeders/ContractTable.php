<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ss_contracts')->insert([
            [
                'id' => 1,
                'shop_id' => env('SHOP_ID'),
                'user_id' => env('USER_ID'),
                'shopify_contract_id' => env('SHOPIFY_CONTRACT_ID_1'),
                'shopify_customer_id' => env('SHOPIFY_CUSTOMER_ID_1'),
                'ss_customer_id' => 1,
                'status' => 'FAILED',
                'error_state' => 'Billing Failed',
                'next_order_date' => date('Y-m-d H:i:s'),
                'is_prepaid' => '1',
                'last_billing_order_number' => '123456789',
                'prepaid_renew' => '1',
                'billing_interval' => 'DAY',
                'billing_interval_count' => '10',
                'billing_min_cycles' => '10',
                'billing_max_cycles' => '20',
                'billing_anchors' => 'dummy_billing_anchors',
                'delivery_intent' => 'dummy_delivery_intent',
                'delivery_interval' => 'DAY',
                'delivery_interval_count' => '10',
                'delivery_cutoff' => '5',
                'delivery_pre_cutoff_behaviour' => 'dummy_delivery_pre_cutoff_behaviour',
                'delivery_anchors' => 'dummy_delivery_anchors',
                'pricing_adjustment_type' => 'dummy',
                'pricing_adjustment_value' => 'dummy',
                'pricing_after_cycle' => 'dummy',
                'ship_name' => 'dummy',
                'ship_address1' => 'dummy',
                'ship_address2' => 'dummy',
                'ship_city' => 'surat',
                'ship_province' => 'gujarat',
                'ship_zip' => '965963',
                'ship_country' => 'india',
                'ship_phone' => '91 9632698563',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'shop_id' => env('SHOP_ID'),
                'user_id' => env('USER_ID'),
                'shopify_contract_id' => env('SHOPIFY_CONTRACT_ID_1'),
                'shopify_customer_id' => env('SHOPIFY_CUSTOMER_ID_2'),
                'ss_customer_id' => 1,
                'status' => 'CANCELED',
                'error_state' => 'Renewal Canceled',
                'next_order_date' => date('Y-m-d H:i:s'),
                'is_prepaid' => '1',
                'last_billing_order_number' => '1234526',
                'prepaid_renew' => '1',
                'billing_interval' => 'MONTH',
                'billing_interval_count' => '2',
                'billing_min_cycles' => '2',
                'billing_max_cycles' => '5',
                'billing_anchors' => 'dummy_billing_anchors',
                'delivery_intent' => 'dummy_delivery_intent',
                'delivery_interval' => 'DAY',
                'delivery_interval_count' => '10',
                'delivery_cutoff' => '5',
                'delivery_pre_cutoff_behaviour' => 'dummy_delivery_pre_cutoff_behaviour',
                'delivery_anchors' => 'dummy_delivery_anchors',
                'pricing_adjustment_type' => 'dummy',
                'pricing_adjustment_value' => 'dummy',
                'pricing_after_cycle' => 'dummy',
                'ship_name' => 'dummy',
                'ship_address1' => 'dummy',
                'ship_address2' => 'dummy',
                'ship_city' => 'surat',
                'ship_province' => 'gujarat',
                'ship_zip' => '965963',
                'ship_country' => 'india',
                'ship_phone' => '91 9632698563',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 3,
                'shop_id' => env('SHOP_ID'),
                'user_id' => env('USER_ID'),
                'shopify_contract_id' => env('SHOPIFY_CONTRACT_ID_1'),
                'shopify_customer_id' => env('SHOPIFY_CUSTOMER_ID_3'),
                'ss_customer_id' => 1,
                'status' => 'PAUSED',
                'error_state' => 'Renewal Paused',
                'next_order_date' => date('Y-m-d H:i:s'),
                'is_prepaid' => '1',
                'last_billing_order_number' => '12345678',
                'prepaid_renew' => '1',
                'billing_interval' => 'MONTH',
                'billing_interval_count' => '2',
                'billing_min_cycles' => '2',
                'billing_max_cycles' => '5',
                'billing_anchors' => 'dummy_billing_anchors',
                'delivery_intent' => 'dummy_delivery_intent',
                'delivery_interval' => 'DAY',
                'delivery_interval_count' => '10',
                'delivery_cutoff' => '5',
                'delivery_pre_cutoff_behaviour' => 'dummy_delivery_pre_cutoff_behaviour',
                'delivery_anchors' => 'dummy_delivery_anchors',
                'pricing_adjustment_type' => 'dummy',
                'pricing_adjustment_value' => 'dummy',
                'pricing_after_cycle' => 'dummy',
                'ship_name' => 'dummy',
                'ship_address1' => 'dummy',
                'ship_address2' => 'dummy',
                'ship_city' => 'surat',
                'ship_province' => 'gujarat',
                'ship_zip' => '965963',
                'ship_country' => 'india',
                'ship_phone' => '91 9632698563',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
