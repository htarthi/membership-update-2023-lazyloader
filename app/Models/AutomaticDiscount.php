<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutomaticDiscount extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'automatic_discount';
    protected $fillable = ['shop_id','user_id','plan_id','tier_id','collection_id','collection_name','collection_discount','collection_discount_type','collection_message'];

}
