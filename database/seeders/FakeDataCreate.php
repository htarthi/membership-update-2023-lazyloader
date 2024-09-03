<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class FakeDataCreate extends Seeder
{
    /**

 * Run the database seeds.
 *
 * @return void
 */
public function run()
{
    $faker = Faker::create();

    foreach(range(0,5000) as $i){
        DB::table('fake_customers')->insert([
            'id' => $i + 1,
            'firstname' => $faker->firstName(),
            'lastname' => $faker->lastName(),
            'email' => $faker->email(),
        ]);
    }
}
}
