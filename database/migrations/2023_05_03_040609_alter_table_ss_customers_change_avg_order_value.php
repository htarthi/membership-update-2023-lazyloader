<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSsCustomersChangeAvgOrderValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_customers', function (Blueprint $table) {
            \DB::statement('alter table ss_customers modify avg_order_value decimal(10,2) DEFAULT 0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_customers', function (Blueprint $table) {
            \DB::statement('alter table ss_customers modify avg_order_value decimal(8,2) DEFAULT 0');
        });
    }
}
