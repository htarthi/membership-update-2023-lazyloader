<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsStoreCredit extends Model
{
    protected $fillable = ['shop_id', 'ss_customer_id','shopify_customer_id', 'shopify_storecreditaccount_id', 'shopify_contract_id', 'amount', 'balance', 'expiry_date', 'transaction_date', 'description', 'beginning_balance', 'ending_balance', 'gift_card_id', 'gift_card_code_ending'];
}
