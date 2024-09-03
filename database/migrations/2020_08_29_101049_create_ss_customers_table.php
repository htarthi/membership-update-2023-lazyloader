<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->string('shopify_customer_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('ts_created')->nullable()->comment('when the customer was created');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('notes')->nullable();
            $table->integer('total_orders')->nullable();
            $table->decimal('total_spend')->nullable();
            $table->string('total_spend_currency')->nullable()->comment('USD, INR, etc…');
            $table->decimal('avg_order_value')->default(0)->comment('total_spend / total_orders');
            $table->timestamp('date_first_order')->nullable()->comment('UTC timestamp');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_customers');
    }
}
