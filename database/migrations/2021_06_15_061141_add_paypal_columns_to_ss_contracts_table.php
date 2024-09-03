<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaypalColumnsToSsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->comment('credit_card/paypal')->after('currency_code')->default('credit_card');
            $table->string('paypal_account')->nullable()->comment('store customer email for paypal payment method')->after('payment_method');
            $table->string('paypal_inactive')->nullable()->after('paypal_account');
            $table->boolean('paypal_isRevocable')->nullable()->after('paypal_inactive');
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
