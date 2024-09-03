<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class OrderTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ss_orders')->insert([
            [
                'id' => 1,
                'shop_id' => env('SHOP_ID'),
                'user_id' => env('USER_ID'),
                'ss_contract_id' => 1,
                'ss_customer_id' => 1,
                'shopify_order_id' => '96356985696',
                'shopify_order_name' => '#1001',
                'order_currency' => '$',
                'conversion_rate' => 3,
                'order_amount' => 497.76,
                'tx_fee_percentage' => 10,
                'tx_fee_amount' => 56.23,
                'tx_fee_status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'shop_id' => env('SHOP_ID'),
                'user_id' => env('USER_ID'),
                'ss_contract_id' => 2,
                'ss_customer_id' => 2,
                'shopify_order_id' => '96356985696',
                'shopify_order_name' => '#1001',
                'order_currency' => '$',
                'conversion_rate' => 3,
                'order_amount' => 497.76,
                'tx_fee_percentage' => 10,
                'tx_fee_amount' => 56.23,
                'tx_fee_status' => 'success',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 3,
                'shop_id' => env('SHOP_ID'),
                'user_id' => env('USER_ID'),
                'ss_contract_id' => 3,
                'ss_customer_id' => 3,
                'shopify_order_id' => '96356985696',
                'shopify_order_name' => '#1001',
                'order_currency' => '$',
                'conversion_rate' => 3,
                'order_amount' => 497.76,
                'tx_fee_percentage' => 10,
                'tx_fee_amount' => 56.23,
                'tx_fee_status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 4,
                'shop_id' => env('SHOP_ID'),
                'user_id' => env('USER_ID'),
                'ss_contract_id' => 3,
                'ss_customer_id' => 3,
                'shopify_order_id' => '96356985696',
                'shopify_order_name' => '#1001',
                'order_currency' => '$',
                'conversion_rate' => 3,
                'order_amount' => 497.76,
                'tx_fee_percentage' => 10,
                'tx_fee_amount' => 56.23,
                'tx_fee_status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
