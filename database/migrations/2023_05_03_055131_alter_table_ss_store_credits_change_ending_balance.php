<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSsStoreCreditsChangeEndingBalance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_store_credits', function (Blueprint $table) {
            \DB::statement('alter table ss_store_credits modify ending_balance decimal(10,2) DEFAULT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_store_credits', function (Blueprint $table) {
            \DB::statement('alter table ss_store_credits modify ending_balance decimal(8,2) DEFAULT NULL');
        });
    }
}
