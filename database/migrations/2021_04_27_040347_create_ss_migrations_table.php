<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_migrations', function (Blueprint $table) {
            $table->id();
            $table->integer('import_id')->nullable();
            $table->string('import_status', 50)->nullable();
            $table->unsignedBigInteger('shop_id');
            $table->string('status', 50)->nullable();
            $table->integer('subscription_number')->nullable();
            $table->string('subscription_status', 20)->nullable();
            $table->date('subscription_next_billing_date')->nullable();
            $table->char('subscription_currency', 3)->nullable();
            $table->integer('customer_shopify_id')->nullable();
            $table->string('customer_gateway_id')->nullable()->comment('token from payment gateway');
            $table->string('customer_email')->nullable();
            $table->string('customer_firstname')->nullable();
            $table->string('customer_lastname')->nullable();
            $table->integer('billing_interval_count')->nullable();
            $table->string('billing_interval_type', 5)->nullable();
            $table->integer('delivery_interval_count')->nullable();
            $table->string('delivery_interval_type', 5)->nullable();
            $table->integer('delivery_anchor_days')->nullable();
            $table->integer('delivery_anchor_month')->nullable();
            $table->string('delivery_anchor_type', 8)->nullable();
            $table->integer('is_prepaid')->nullable();
            $table->integer('prepaid_orders_completed')->nullable();
            $table->boolean('prepaid_renew')->default(0);
            $table->integer('min_cycles')->nullable();
            $table->integer('max_cycles')->nullable();
            $table->integer('order_count')->nullable();
            $table->string('shipping_firstname')->nullable();
            $table->string('shipping_lastname')->nullable();
            $table->string('shipping_address1')->nullable();
            $table->string('shipping_address2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state',3)->nullable();
            $table->char('shipping_country_code',2)->nullable();
            $table->string('shipping_zip',20)->nullable();
            $table->string('shipping_phone',20)->nullable();
            $table->decimal('shipping_price',8,2)->nullable();
            $table->integer('line_item_qty')->nullable();
            $table->integer('line_item_product_id')->nullable();
            $table->integer('line_item_variant_id')->nullable();
            $table->decimal('line_item_price',8,2)->nullable();
            $table->integer('payment_method_id')->nullable();
            $table->integer('contract_id')->nullable();
            $table->longText('errors')->nullable()->comment('a blob or something for lots of text');
            $table->timestamps();

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
        Schema::dropIfExists('ss_migrations');
    }
}
