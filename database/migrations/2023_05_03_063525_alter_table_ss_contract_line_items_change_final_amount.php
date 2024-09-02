<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSsContractLineItemsChangeFinalAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contract_line_items', function (Blueprint $table) {

            \DB::statement('alter table ss_contract_line_items modify final_amount decimal(10,2) DEFAULT NULL');

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
            \DB::statement('alter table ss_contract_line_items modify final_amount decimal(8,2) DEFAULT NULL');
        });
    }
}
