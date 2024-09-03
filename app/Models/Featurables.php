<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Featurables extends Model
{
    use HasFactory;
    protected $table = 'featurables';
    protected $fillable =['feature_id','featurable_id','featurable_type'];
    public $timestamps = false;

}
