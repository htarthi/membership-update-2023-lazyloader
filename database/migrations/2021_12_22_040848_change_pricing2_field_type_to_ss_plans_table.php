<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePricing2FieldTypeToSsPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_plans', function (Blueprint $table) {
            $table->decimal('pricing2_adjustment_value',8, 2)->change();
            $table->integer('pricing2_after_cycle')->change();
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
            //
        });
    }
}
