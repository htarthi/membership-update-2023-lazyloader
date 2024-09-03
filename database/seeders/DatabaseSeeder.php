<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PlanTableSeeder::class);
//         $this->call(AppTableSeeder::class);
         $this->call(CountryTableSeeder::class);
         $this->call(StateTableSeeder::class);
//        $this->call(CustomerTable::class);
//        $this->call(ContractTable::class);
//        $this->call(OrderTable::class);
    }
}
