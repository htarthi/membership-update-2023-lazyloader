<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsPlan extends Model
{
	use SoftDeletes;
    protected $fillable = ['position'];
    protected $casts = [
        'is_set_min' => 'boolean',
        'is_set_max' => 'boolean',
        'trial_available' => 'boolean',
        'is_onetime_payment' => 'boolean',
        'is_advance_option' => 'boolean',
        'store_credit' => 'boolean'
    ];

    protected $appends = [
        'trial_type'
    ];

    public function getTrialTypeAttribute() {
        return ($this->pricing2_after_cycle && !$this->trial_days) ? 'orders' : 'days';
    }

    public function hasManyContracts(){
        return $this->hasMany(SsContract::class, 'ss_plan_id', 'id' )->select('id', 'ss_plan_id','status','shopify_contract_id')->whereNotNull('shopify_contract_id')->where('status', 'active');
    }

    public function hasManyPosDiscounts(){
        return $this->hasMany(SsPosDiscounts::class, 'ss_plan_id', 'id' );
    }
}
