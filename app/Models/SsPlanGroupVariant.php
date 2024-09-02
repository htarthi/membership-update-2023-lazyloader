<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsPlanGroupVariant extends Model
{
    use SoftDeletes;

    public function planGroups() {
        return $this->hasMany(SsPlanGroup::class, 'id','ss_plan_group_id');
    }
}
