<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductTitleToSsPlanGroupVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_plan_group_variants', function (Blueprint $table) {
            $table->string('product_title')->after('shopify_product_id')->nullable();
        });
        Schema::table('ss_rules', function (Blueprint $table) {
            $table->string('rule_child_type')->after('rule_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_plan_group_variants', function (Blueprint $table) {
            //
        });
    }
}
