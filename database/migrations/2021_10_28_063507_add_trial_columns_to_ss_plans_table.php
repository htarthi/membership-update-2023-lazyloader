<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrialColumnsToSsPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_plans', function (Blueprint $table) {
            $table->boolean('is_set_min')->after('pricing2_after_cycle')->default(0);
            $table->boolean('is_set_max')->after('is_set_min')->default(0);
            $table->boolean('trial_available')->after('is_set_max')->default(0);
            $table->integer('trial_days')->after('trial_available')->nullable();
        });

       Schema::table('ss_contracts', function (Blueprint $table) {
           $table->boolean('on_trial')->after('tag_order')->default(0);
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
