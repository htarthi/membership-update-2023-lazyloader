<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingDiscount extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'shipping_discount';

    protected $fillable = ['discount_id','shop_id','user_id','product_id','tier_id','customer_tag','shipping_discount','shipping_discount_type','shipping_discount_message'];


}
