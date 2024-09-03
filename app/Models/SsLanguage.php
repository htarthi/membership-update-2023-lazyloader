<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsLanguage extends Model
{
	use SoftDeletes;

	protected $fillable = ['portal_title_details', 'portal_title_subscriptions', 'portal_products_title', 'portal_products_qty', 'protal_products_price', 'portal_products_variant', 'portal_products_update', 'portal_products_cancel', 'portal_products_subtotal', 'portal_order_title', 'portal_order_to', 'portal_order_address', 'portal_order_method', 'portal_order_billing', 'portal_order_frequency', 'portal_order_delivery', 'portal_order_fname', 'portal_order_lname', 'portal_order_company', 'portal_order_address1', 'portal_order_address2', 'portal_order_city', 'portal_order_state', 'portal_order_zip', 'portal_order_country', 'portal_order_update', 'portal_order_cancel', 'portal_billing_title', 'portal_billing_card', 'portal_billing_ending', 'portal_billing_summary', 'portal_billing_subtotal', 'portal_billing_discount', 'portal_billing_total', 'portal_billing_instructions', 'portal_billing_send', 'portal_billing_cancel', 'portal_popup_cancel_title', 'portal_popup_cancel_text', 'portal_popup_cancel_yes', 'portal_popup_cancel_no', 'portal_general_status', 'portal_general_active', 'portal_general_paused', 'portal_general_cancelled', 'portal_general_expired', 'portal_action_pause', 'portal_action_cancel', 'portal_action_resume', 'portal_action_skip', 'portal_warning_title', 'portal_warning_text', 'portal_error_required', 'portal_no_membership', 'portal_is_waiting', 'portal_dropdown_label', 'portal_next_renewal', 'toaster_email_sent', 'toaster_membership_updated','portal_member_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $hidden = [
        'id', 'shop_id', 'created_at', 'updated_at', 'deleted_at'
    ];
}
