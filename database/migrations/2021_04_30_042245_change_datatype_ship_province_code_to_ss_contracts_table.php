<?php

// use Doctrine\DBAL\Types\StringType;
// use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDatatypeShipProvinceCodeToSsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // if (!Type::hasType('char')) {
        //     Type::addType('char', StringType::class);
        // }
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->char('ship_provinceCode', 5)->nullable()->change();
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
