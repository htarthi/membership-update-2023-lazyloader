<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CustomCharge extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('plans')->insert([
            [
                'id' => 4,
                'type' => env('IS_RECURRING', 'RECURRING'),
                'name' => env('PLAN_NAME_1', "Custom Plan"),
                'price' => env('PLAN_PRICE_1', 0.00),
                'interval' => 'EVERY_30_DAYS',
                'capped_amount' => 1000,
                'terms' => env('PLAN_TERM_1', "Includes all features. Additional charge of $0.25 per member per month will be added to your monthly charge"),
                'trial_days' => env('TRIAL_DAY', 0),
                'test' => env('TEST_MODE', 1),
                'on_install' => env('ON_INSTALL', 0),
                'transaction_fee' => 0.0025
            ],
        ]);
    }
}
