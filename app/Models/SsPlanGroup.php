<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsPlanGroup extends Model
{
    use SoftDeletes;
    protected $fillable = ['position'];
    //protected $attributes = ['tier_id'];
    protected $appends = [
        'tier_id'
    ];

    public function getTierIdAttribute()
    {
        return ($this->id);
    }
    public function hasManyPlan()
    {
        return $this->hasMany(SsPlan::class, 'ss_plan_group_id', 'id')->orderBy('position');
    }
    public function hasManyPosDiscounts()
    {
        return $this->hasMany(SsPosDiscounts::class, 'ss_plan_groups_id', 'id');
    }
    public function hasManyVariants()
    {
        return $this->hasMany(SsPlanGroupVariant::class, 'ss_plan_group_id', 'id');
    }
    public function hasManyRules()
    {
        return $this->hasMany(SsRule::class, 'ss_plan_group_id', 'id');
    }
    public function hasManyForms()
    {
        return $this->hasMany(SsForm::class, 'ss_plan_group_id', 'id')->orderBy('field_order');
    }
    public function hasManyCreditRules()
    {
        return $this->hasMany(SsStoreCreditRules::class, 'ss_plan_group_id', 'id');
    }
    public function hasManualMembership()
    {
        return $this->hasMany(SsContract::class,'ss_plan_groups_id','id')->where('shopify_contract_id',null)->where('is_onetime_payment',1);
    }
}
