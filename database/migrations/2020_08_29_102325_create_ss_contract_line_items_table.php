<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsContractLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_contract_line_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shopify_contract_id')->nullable();
            $table->unsignedBigInteger('ss_contract_id')->nullable()->comment('internal ss_contracts id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('internal users id');
            $table->bigInteger('shopify_product_id')->nullable();
            $table->bigInteger('shopify_variant_id')->nullable();
            $table->decimal('price')->nullable();
            $table->string('currency')->nullable()->comment('USD, CAD, INR, etc…');
            $table->string('discount_type')->nullable()->comment('FIXED,PERCENTAGE');
            $table->decimal('discount_amount')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->on('users')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('ss_contract_id')->on('ss_contracts')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_contract_line_items');
    }
}
