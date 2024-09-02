<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsOrder extends Model
{
    use SoftDeletes;

    protected $fillable = ['tx_fee_status','order_amount','is_updated'];
}
