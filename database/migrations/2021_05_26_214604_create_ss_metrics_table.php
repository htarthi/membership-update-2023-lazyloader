<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->comment('ID from shops table');
            $table->char('shop_currency', 3)->nullable();
            $table->date('date')->comment('If the job is being run at 00:15:00UTC on Jan 15th, then the date would be Jan 14th');
            $table->integer('active_subscriptions')->comment('All contracts for this shop with a status of active');
            $table->integer('paused_subscriptions')->comment('All contracts for this shop with a status of paused');
            $table->integer('new_subscriptions')->comment('All contracts for this shop which were created between 00:00:00 and 23:59:59UTC the previous day');
            $table->integer('orders_processed')->comment('Count of orders in ss_orders for this shop which were created between 00:00:00 and 23:59:50UTC the previous day');
            $table->decimal('amount_processed', 10, 2)->comment('Sum of all orders in ss_orders for this shop which were created between 00:00:00 and 23:59:59 the previous day');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_metrics');
    }
}
