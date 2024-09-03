<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTestToSsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_orders', function (Blueprint $table) {
            $table->boolean('is_test')->after('tx_fee_status')->default(0);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->decimal('balance', 8, 4)->after('zip')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_orders', function (Blueprint $table) {
            //
        });
    }
}
