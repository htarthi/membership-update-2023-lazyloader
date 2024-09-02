<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSsContractsChangeDeliveryPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            \DB::statement('alter table ss_contracts modify delivery_price decimal(10,2) DEFAULT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            \DB::statement('alter table ss_contracts modify delivery_price decimal(8,2) DEFAULT NULL');
        });
    }
}
