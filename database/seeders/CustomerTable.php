<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ss_customers')->insert([
            [
                'id' => 1,
                'shop_id' => env('SHOP_ID'),
                'shopify_customer_id' => env('SHOPIFY_CUSTOMER_ID_1'),
                'active' => 1,
                'first_name' => 'Qi Ling',
                'last_name' => 'Lin',
                'email' => 'quling@gmail.com',
                'phone' => '404-555-4495',
                'notes' => '',
                'total_orders' => 3,
                'total_spend' => 497.76,
                'total_spend_currency' => '$',
                'avg_order_value' => '',
                'date_first_order' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'shop_id' => env('SHOP_ID'),
                'shopify_customer_id' => env('SHOPIFY_CUSTOMER_ID_2'),
                'active' => 1,
                'first_name' => 'Johnathan',
                'last_name' => 'Smithers',
                'email' => 'quling@gmail.com',
                'phone' => '',
                'notes' => '',
                'total_orders' => 39,
                'total_spend' => 65289.78,
                'total_spend_currency' => '$',
                'avg_order_value' => '',
                'date_first_order' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 3,
                'shop_id' => env('SHOP_ID'),
                'shopify_customer_id' => env('SHOPIFY_CUSTOMER_ID_3'),
                'active' => 0,
                'first_name' => 'Jane',
                'last_name' => 'McDougal',
                'email' => '',
                'phone' => '754-557-9985',
                'notes' => '',
                'total_orders' => 3,
                'total_spend' => 497.76,
                'total_spend_currency' => '$',
                'avg_order_value' => '',
                'date_first_order' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 4,
                'shop_id' => env('SHOP_ID'),
                'shopify_customer_id' => env('SHOPIFY_CUSTOMER_ID_4'),
                'active' => 0,
                'first_name' => 'Fenil',
                'last_name' => 'Lathiya',
                'email' => 'fenil@hotmail.com',
                'phone' => '+91 74055 79402',
                'notes' => '',
                'total_orders' => 25,
                'total_spend' => 497.76,
                'total_spend_currency' => '$',
                'avg_order_value' => '',
                'date_first_order' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 5,
                'shop_id' => env('SHOP_ID'),
                'shopify_customer_id' => env('SHOPIFY_CUSTOMER_ID_5'),
                'active' => 1,
                'first_name' => 'Jim',
                'last_name' => 'Bob',
                'email' => 'jimbob@xyz.xyz',
                'phone' => '',
                'notes' => '',
                'total_orders' => 3,
                'total_spend' => 497.76,
                'total_spend_currency' => '$',
                'avg_order_value' => '',
                'date_first_order' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
