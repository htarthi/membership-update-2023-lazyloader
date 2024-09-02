<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsAnswer extends Model
{
	use SoftDeletes;

    protected $fillable = [
        'id', 'question', 'answer', 'field_type'
    ];
}
