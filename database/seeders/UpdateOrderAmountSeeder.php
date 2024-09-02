<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateOrderAmountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::update('UPDATE ss_orders SET `usd_order_amount` = `order_amount` WHERE `usd_order_amount` IS NULL');
    }
}
