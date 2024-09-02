<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsShippingZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ss_shipping_profile_id')->nullable()->comment('ID from shipping profile table');
            $table->string('shopify_zone_id')->nullable();
            $table->string('shopify_method_id')->nullable();
            $table->string('shopify_location_id')->nullable();
            $table->boolean('active')->nullable();
            $table->longText('countries')->nullable();
            $table->string('zone_name')->nullable();
            $table->string('rate_name')->nullable();
            $table->decimal('rate_value', 8,2)->nullable();
            $table->timestamps();

            $table->foreign('ss_shipping_profile_id')->on('ss_shipping_profiles')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_shipping_zones');
    }
}
