<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumntypeToSsContractLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contract_line_items', function (Blueprint $table) {
            $table->string('currency_symbol', 3)->change();
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->string('currency_symbol', 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_contract_line_items', function (Blueprint $table) {
            //
        });
    }
}
