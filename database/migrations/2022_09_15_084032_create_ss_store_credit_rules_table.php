<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsStoreCreditRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_store_credit_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('ss_plan_group_id')->nullable();
            $table->string('trigger')->nullable()->comment('The name of the rule trigger');
            $table->string('value_type')->nullable()->comment('The type of value');
            $table->decimal('value_amount', 8, 2)->nullable()->comment('amount of the credit');
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops');
            $table->foreign('ss_plan_group_id')->references('id')->on('ss_plan_groups')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_store_credit_rules');
    }
}
