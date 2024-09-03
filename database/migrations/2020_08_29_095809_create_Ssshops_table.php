<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->bigInteger('shopify_store_id')->nullable();
            $table->boolean('active')->nullable();
            $table->timestamp('ts_created')->nullable();
            $table->timestamp('ts_lastupdated')->nullable();
            $table->timestamp('ts_last_deactivation')->nullable()->comment('The last time this shop was turned off because we didn’t have API access');
            $table->boolean('test_store')->nullable()->comment('If set to true, then the billing charge will be set to a test charge');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('myshopify_domain')->nullable();
            $table->string('domain')->nullable();
            $table->string('owner')->nullable();
            $table->string('shopify_plan')->nullable()->comment('plan_name from API');
            $table->string( 'timezone')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('checkout_api_supported')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_name')->nullable();
            $table->string('country_taxes')->nullable();
            $table->string('ss_created_at')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('currency')->nullable();
            $table->string('enabled_presentment_currencies')->nullable();
            $table->string('eligible_for_payments')->nullable();
            $table->string('has_discounts')->nullable();
            $table->string('has_gift_cards')->nullable();
            $table->string('has_storefront')->nullable();
            $table->string('iana_timezone')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('money_format')->nullable();
            $table->string('money_in_emails_format')->nullable();
            $table->string('money_with_currency_format')->nullable();
            $table->string('money_with_currency_in_emails_format')->nullable();
            $table->string('multi_location_enabled')->nullable();
            $table->string('password_enabled')->nullable();
            $table->string('phone')->nullable();
            $table->string('pre_launch_enabled')->nullable();
            $table->string('primary_locale')->nullable();
            $table->string('province')->nullable();
            $table->string('province_code')->nullable();
            $table->string('requires_extra_payments_agreement')->nullable();
            $table->string('setup_required')->nullable();
            $table->string('taxes_included')->nullable();
            $table->string('tax_shipping')->nullable();
            $table->string('tbl_updated_at')->nullable();
            $table->string('weight_unit')->nullable();
            $table->string('zip')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->on('users')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('shops');
    }
}
