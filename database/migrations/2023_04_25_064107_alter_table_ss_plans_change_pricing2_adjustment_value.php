<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSsPlansChangePricing2AdjustmentValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_plans', function (Blueprint $table) {
            \DB::statement('alter table ss_plans modify pricing2_adjustment_value DOUBLE(10,2) DEFAULT 0');
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_plans', function (Blueprint $table) {
            \DB::statement('alter table ss_plans modify pricing2_adjustment_value DOUBLE(8,2) DEFAULT 0');
        });
    }
}
