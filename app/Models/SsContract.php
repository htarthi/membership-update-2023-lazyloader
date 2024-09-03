<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsContract extends Model
{
    use SoftDeletes;

    protected $fillable = ['status', 'status_display','next_order_date', 'next_processing_date', 'ship_firstName', 'ship_lastName', 'ship_address1', 'ship_address2', 'ship_phone', 'ship_company', 'ship_city', 'ship_province', 'ship_provinceCode', 'ship_zip', 'ship_country', 'error_state', 'failed_payment_count','is_physical_product','Active - Expiring' , 'store_credit_amount'];

    public function LineItems()
    {
        return $this->hasMany(SsContractLineItem::class, 'ss_contract_id', 'id');
    }

    public function LineItemsProductOtherMembership()
    {
        return $this->hasOne(SsContractLineItem::class, 'ss_contract_id', 'id')->select('title', 'ss_contract_id', 'id');
    }

    public function ActivityLog(){
        return $this->hasMany(SsActivityLog::class, 'ss_contract_id', 'id')->select('id', 'ss_customer_id', 'ss_contract_id', 'created_at', 'message', 'user_type', 'user_name')->orderBy('created_at', 'desc');
    }

    public function Customer(){
        return $this->belongsTo(SsCustomer::class, 'ss_customer_id', 'id')->select('id', 'first_name', 'last_name', 'notes', 'total_orders', 'total_spend', 'currency_symbol', 'date_first_order', 'email', 'phone');
    }

    public function BillingAttempt(){
        return $this->hasMany(SsBillingAttempt::class, 'ss_contract_id', 'id')->orderBy('id', 'desc');
    }

    public function CustomerAnswer(){
        return $this->hasMany(SsAnswer::class, 'ss_contract_id', 'id')->select(['id', 'ss_contract_id', 'question', 'answer', 'field_type'])->where('deleted_at', null);
    }

    public function PlanGroup(){
        return $this->hasOne(SsPlanGroup::class, 'id', 'ss_plan_groups_id')->withTrashed();
    }

    public function Plan(){
        return $this->hasOne(SsPlan::class,'id','billing_interval','billing_interval_count');
    }
}
