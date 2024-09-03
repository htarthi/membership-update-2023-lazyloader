<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelledSubscriptionsToSsMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_metrics', function (Blueprint $table) {
            $table->integer('cancelled_subscriptions')->comment('Number of cancellations for each shop.')->nullable()->after('amount_processed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_metrics', function (Blueprint $table) {
            $table->dropColumn('cancelled_subscriptions');
        });
    }
}
