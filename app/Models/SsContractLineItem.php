<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsContractLineItem extends Model
{
    use SoftDeletes;

    public function SsContracts()
    {
        return $this->belongsTo(SsContract::class, 'id');
    }
}
