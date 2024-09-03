<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvanceOptionsFieldsToSsPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_plans', function (Blueprint $table) {
            $table->boolean('is_advance_option')->after('is_set_max')->default(0);
            $table->boolean('is_onetime_payment')->after('trial_available')->default(0);
            // $table->renameColumn('trial_days', 'trial_orders')->change();
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
