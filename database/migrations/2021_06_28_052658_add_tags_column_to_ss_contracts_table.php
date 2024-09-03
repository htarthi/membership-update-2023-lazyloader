<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTagsColumnToSsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->string('tag_customer')->after('order_count')->nullable();
            $table->string('tag_order', 40)->after('tag_customer')->nullable();
            $table->unsignedBigInteger('ss_plan_groups_id')->after('ss_customer_id')->nullable()->comment('ID from ss_plan_groups table');

            $table->foreign('ss_plan_groups_id')->on('ss_plan_groups')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
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
