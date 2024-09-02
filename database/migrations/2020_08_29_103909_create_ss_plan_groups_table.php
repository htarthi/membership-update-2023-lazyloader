<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsPlanGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_plan_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shopify_plan_group_id')->nullable()->comment('ID from shopify once the plan group has been create');
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID from users table');
            $table->timestamp('ts_created')->nullable();
            $table->boolean('active')->nullable();
            $table->string('name')->nullable()->comment('Customer-facing name');
            $table->string('merchantCode')->nullable()->comment('Should be the “name” field, all lowercase, replace spaces with -');
            $table->string('description')->nullable()->comment('Merchant description of this selling plan group');
            $table->string('label')->nullable()->comment('Additional information to display for this group');
            $table->integer('position')->nullable()->comment('Order in which to display these on the storefront');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('user_id')->on('users')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_plan_groups');
    }
}
