<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AppTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('apps')->insert([
            [
                'id' => 1,
                'name' => env('APP_NAME'),
                'api_key' => env('SHOPIFY_API_KEY'),
                'shared_secret' => env('SHOPIFY_API_SECRET'),
                'trial_days' => '30',
                'app_url' => env('APP_URL'),
                'api_version' => env('SHOPIFY_API_VERSION'),
            ],
        ]);
    }
}
