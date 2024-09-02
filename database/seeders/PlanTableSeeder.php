<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanTableSeeder extends Seeder
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
                'id' => 1,
                'type' => env('IS_RECURRING', 'RECURRING'),
                'name' => env('PLAN_NAME_1', "Starter Plan"),
                'price' => env('PLAN_PRICE_1', 0.00),
                'interval' => 'EVERY_30_DAYS',
                'capped_amount' => 1000,
                'terms' => env('PLAN_TERM_1', "Includes all features. Additional charge of $0.25 per member per month will be added to your monthly charge"),
                'trial_days' => env('TRIAL_DAY', 0),
                'test' => env('TEST_MODE', 1),
                'on_install' => env('ON_INSTALL', 1),
                'transaction_fee' => 0.0025,
                'is_free_trial_plans' => NULL
            ],
            [
                'id' => 2,
                'type' => env('IS_RECURRING', 'RECURRING'),
                'name' => env('PLAN_NAME_1', "Growth Plan"),
                'price' => env('PLAN_PRICE_1', 19.99),
                'interval' => 'EVERY_30_DAYS',
                'capped_amount' => 500.00,
                'terms' => env('PLAN_TERM_1', "Includes all features. Additional charge of $0.15 per member per month will be added to your monthly charge."),
                'trial_days' => env('TRIAL_DAY', 0),
                'test' => env('TEST_MODE', 1),
                'on_install' => env('ON_INSTALL', 1),
                'transaction_fee' => 0.0015,
                'is_free_trial_plans' => NULL
            ],
            [
                'id' => 3,
                'type' => env('IS_RECURRING', 'RECURRING'),
                'name' => env('PLAN_NAME_1', "Enterprise Plan"),
                'price' => env('PLAN_PRICE_1', 299.99),
                'interval' => 'EVERY_30_DAYS',
                'capped_amount' => 1000.00,
                'terms' => env('PLAN_TERM_1', "Includes all features. Additional charge of $0.05 per member per month will be added to your monthly charge."),
                'trial_days' => env('TRIAL_DAY', 0),
                'test' => env('TEST_MODE', 1),
                'on_install' => env('ON_INSTALL', 1),
                'transaction_fee' => 0.0005,
                'is_free_trial_plans' => NULL
            ],
            [
                'id' => 4,
                'type' => 'ONETIME',
                'name' => 'FREE',
                'price' => 0.50,
                'interval' => '',
                'capped_amount' => 0.00,
                'terms' => "FREE",
                'trial_days' => 0,
                'test' => 1,
                'on_install' => 0,
                'transaction_fee' => NULL,
                'is_free_trial_plans' => 1
            ],
        ]);
    }
}
