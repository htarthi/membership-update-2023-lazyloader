<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSsPlanGroupIdToSsPosDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_pos_discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('ss_plan_groups_id')->after('ss_plan_id')->nullable()->comment('ID from ss_plan_groups table');  
            $table->foreign('ss_plan_groups_id')->on('ss_plan_groups')->references('id')->onUpdate('NO ACTION')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_pos_discounts', function (Blueprint $table) {
            //
        });
    }
}
