<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ss_languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable()->comment('ID from shops table');
            $table->string('portal_title_details')->nullable()->default('Membership Details');
            $table->string('portal_title_subscriptions')->nullable()->default('My memberships');
            $table->string('portal_products_title')->nullable()->default('Membership');
            $table->string('portal_products_qty')->nullable()->default('Qty');
            $table->string('protal_products_price')->nullable()->default('Price');
            $table->string('portal_products_variant')->nullable()->default('Variant');
            $table->string('portal_products_update')->nullable()->default('Update');
            $table->string('portal_products_cancel')->nullable()->default('Cancel');
            $table->string('portal_products_subtotal')->nullable()->default('Subtotal');
            $table->string('portal_order_title')->nullable()->default('Renewal Information');
            $table->string('portal_order_to')->nullable()->default('Shipping to');
            $table->string('portal_order_address')->nullable()->default('Member information');
            $table->string('portal_order_method')->nullable()->default('Shipping method');
            $table->string('portal_order_billing')->nullable()->default('Next billing date');
            $table->string('portal_order_frequency')->nullable()->default('Membership length');
            $table->string('portal_order_delivery')->nullable()->default('Next renewal date');
            $table->string('portal_order_fname')->nullable()->default('First name');
            $table->string('portal_order_lname')->nullable()->default('Last name');
            $table->string('portal_order_company')->nullable()->default('Company');
            $table->string('portal_order_address1')->nullable()->default('Address');
            $table->string('portal_order_address2')->nullable()->default('Suite');
            $table->string('portal_order_city')->nullable()->default('City');
            $table->string('portal_order_state')->nullable()->default('State');
            $table->string('portal_order_zip')->nullable()->default('Zip');
            $table->string('portal_order_country')->nullable()->default('Country');
            $table->string('portal_order_update')->nullable()->default('Update');
            $table->string('portal_order_cancel')->nullable()->default('Cancel');
            $table->string('portal_billing_title')->nullable()->default('Billing Information');
            $table->string('portal_billing_card')->nullable()->default('Card on file');
            $table->string('portal_billing_ending')->nullable()->default('ending in');
            $table->string('portal_billing_summary')->nullable()->default('Summary');
            $table->string('portal_billing_subtotal')->nullable()->default('Subtotal');
            $table->string('portal_billing_discount')->nullable()->default('Discount');
            $table->string('portal_billing_total')->nullable()->default('Total');
            $table->string('portal_billing_instructions')->nullable()->default('To update your billing information, we will email you with further instructions');
            $table->string('portal_billing_send')->nullable()->default('Send instructions');
            $table->string('portal_billing_cancel')->nullable()->default('Cancel');
            $table->string('portal_popup_cancel_title')->nullable()->default('Cancel your membership?');
            $table->string('portal_popup_cancel_text')->nullable()->default('Are you sure you would like to cancel this membership?');
            $table->string('portal_popup_cancel_yes')->nullable()->default('Cancel membership');
            $table->string('portal_popup_cancel_no')->nullable()->default('Go back');
            $table->string('portal_general_status')->nullable()->default('Status');
            $table->string('portal_general_active')->nullable()->default('Active');
            $table->string('portal_general_paused')->nullable()->default('Paused');
            $table->string('portal_general_cancelled')->nullable()->default('Cancelled');
            $table->string('portal_general_expired')->nullable()->default('Expired');
            $table->string('portal_action_pause')->nullable()->default('Pause');
            $table->string('portal_action_cancel')->nullable()->default('Cancel membership');
            $table->string('portal_action_resume')->nullable()->default('Resume membership');
            $table->string('portal_action_skip')->nullable()->default('Skip this order');
            $table->string('portal_warning_title')->nullable()->default('IMPORTANT');
            $table->string('portal_warning_text')->nullable()->default('Your payment method recently failed. To prevent a disruption, please update it now');
            $table->string('portal_error_required')->nullable()->default('This is a required field');
            $table->timestamps();

            $table->foreign('shop_id')->on('shops')->references('id')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ss_languages');
    }
}
