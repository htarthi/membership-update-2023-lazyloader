<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSsContractIdToSsBillingAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_billing_attempts', function (Blueprint $table) {
            $table->unsignedBigInteger('ss_contract_id')->after('shopify_id')->nullable()->comment('internal ss_contracts id');

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
        Schema::table('ss_billing_attempts', function (Blueprint $table) {
            //
        });
    }
}
