<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSsOrdersChangeOrderAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_orders', function (Blueprint $table) {
            \DB::statement('alter table ss_orders modify order_amount decimal(10,2) DEFAULT 0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_orders', function (Blueprint $table) {
            \DB::statement('alter table ss_orders modify order_amount decimal(8,2) DEFAULT 0');
        });
    }
}
