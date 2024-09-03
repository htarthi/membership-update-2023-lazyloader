<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSsPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_plans', function (Blueprint $table) {
            $table->string('options', 255)->after('description')->nullable();
            $table->boolean('is_prepaid')->after('position')->default(0);
            $table->boolean('prepaid_renew')->after('is_prepaid')->nullable();
            $table->boolean('pricing2_after_cycle')->default(0)->change();
            $table->boolean('pricing2_adjustment_value')->default(0)->change();
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
