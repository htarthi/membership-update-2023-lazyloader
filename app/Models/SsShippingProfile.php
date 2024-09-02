<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsShippingProfile extends Model
{
	use SoftDeletes;

    public function ShippingZones(){
        return $this->hasMany(SsShippingZone::class, 'ss_shipping_profile_id', 'id' );
    }
}
