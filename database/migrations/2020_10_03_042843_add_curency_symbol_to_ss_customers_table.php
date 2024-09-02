<?php

// use Doctrine\DBAL\Types\StringType;
// use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurencySymbolToSsCustomersTable extends Migration
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

        Schema::table('ss_customers', function (Blueprint $table) {
            $table->char('currency_symbol')->after('total_spend_currency')->nullable();
        });

        Schema::table('ss_orders', function (Blueprint $table) {
            $table->char('currency_symbol')->after('order_currency')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_customers', function (Blueprint $table) {
            //
        });
    }
}
