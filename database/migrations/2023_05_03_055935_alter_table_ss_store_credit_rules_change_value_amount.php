<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSsStoreCreditRulesChangeValueAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_store_credit_rules', function (Blueprint $table) {
            \DB::statement('alter table ss_store_credit_rules modify value_amount decimal(10,2) DEFAULT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_store_credit_rules', function (Blueprint $table) {
            \DB::statement('alter table ss_store_credit_rules modify value_amount decimal(8,2) DEFAULT NULL');
        });
    }
}
