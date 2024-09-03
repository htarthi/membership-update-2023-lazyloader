<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSsContractsChangePricingAdjustmentValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            \DB::statement('alter table ss_contracts modify pricing_adjustment_value DOUBLE(10,2) DEFAULT 0');
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
            \DB::statement('alter table ss_contracts modify pricing_adjustment_value DOUBLE(8,2) DEFAULT 0');
        });
    }
}
