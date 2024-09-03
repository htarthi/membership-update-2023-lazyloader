<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencySymbolToSsContractLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contract_line_items', function (Blueprint $table) {
            $table->string('currency_symbol')->after('currency')->nullable();
            $table->decimal('final_amount', 8, 2)->after('discount_amount')->nullable();
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
