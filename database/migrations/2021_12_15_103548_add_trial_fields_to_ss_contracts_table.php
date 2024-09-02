<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrialFieldsToSsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->boolean('is_set_min')->after('on_trial')->default(0);
            $table->boolean('is_set_max')->after('is_set_min')->default(0);
            $table->boolean('trial_available')->after('is_set_max')->default(0);
            $table->boolean('is_onetime_payment')->after('trial_available')->default(0);
            $table->decimal('pricing_adjustment_value', 8,2)->after('is_onetime_payment')->nullable();
            $table->decimal('pricing2_adjustment_value', 8,2)->after('pricing_adjustment_value')->nullable();
            $table->integer('pricing2_after_cycle')->after('is_onetime_payment')->nullable();
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
            //
        });
    }
}
