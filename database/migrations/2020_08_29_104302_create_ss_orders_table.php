<?php

// use Doctrine\DBAL\Types\StringType;
// use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // if (!Type::hasType('char')) {
        //     Type::addType('char', StringType::class);
        // }

        Schema::create('ss_orders', function (Blueprint $table) {
            $table->id();
            $table->timestamp('ts_created')->nullable();
            $table->timestamp('ts_updated')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID from users table');
            $table->unsignedBigInteger('ss_contract_id')->nullable();
            $table->unsignedBigInteger('ss_customer_id')->nullable()->comment('internal customer id');
            $table->bigInteger('shopify_order_id')->nullable();
            $table->string('shopify_order_name')->nullable();
            $table->char('order_currency')->nullable()->comment('The currency of the order');
            $table->decimal('conversion_rate')->nullable()->comment('Rate used to get from order currency to USD');
            $table->decimal('order_amount')->nullable()->comment('USD');
            $table->decimal('tx_fee_percentage')->nullable()->comment('The fee that was active when the order was processed');
            $table->decimal('tx_fee_amount')->nullable()->comment('USD, the amount charged');
            $table->string('tx_fee_status')->nullable()->comment('Tracks whether the fee was successfully charged using usage based transactions.
(pending, success)');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('user_id')->on('users')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('ss_contract_id')->on('ss_contracts')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('ss_customer_id')->on('ss_customers')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_orders');
    }
}
