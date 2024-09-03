<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnToSsShippingZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_shipping_zones', function (Blueprint $table) {
            $table->dropColumn('shopify_method_id');
            $table->dropColumn('shopify_location_id');
            $table->json('shopify_zone_id')->comment('{location_id: shopify_zone_id}')->change();
        });

        Schema::table('ss_shipping_profiles', function (Blueprint $table) {
            $table->json('shopify_location_group_id')->comment('{location_id: shopify_location_group_id}	')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_shipping_zones', function (Blueprint $table) {
            //
        });
    }
}
