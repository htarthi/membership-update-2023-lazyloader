<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrepaidColumnsToSsLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_languages', function (Blueprint $table) {
            $table->string('portal_prepaid_title')->nullable()->after('portal_error_required')->default('Prepaid orders');
            $table->string('portal_prepaid_orderdate')->nullable()->after('portal_prepaid_title')->default('Order Date');
            $table->string('protal_prepaid_status')->nullable()->after('portal_prepaid_orderdate')->default('Status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_languages', function (Blueprint $table) {
            //
        });
    }
}
