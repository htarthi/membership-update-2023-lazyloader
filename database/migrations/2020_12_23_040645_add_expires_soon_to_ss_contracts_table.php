<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiresSoonToSsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->string('delivery_price_currency_symbol', 3)->after('delivery_price')->nullable();
            $table->boolean('cc_expires_soon')->after('cc_expiryYear')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            //
        });
    }
}
