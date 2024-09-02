<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToSsCustomPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_custom_plans', function (Blueprint $table) {
            $table->string('status')->after('plan_id')->nullable();
            $table->datetime('cancelled_at')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_custom_plans', function (Blueprint $table) {
            //
        });
    }
}
