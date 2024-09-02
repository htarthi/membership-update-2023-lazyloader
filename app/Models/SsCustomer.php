<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsCustomer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'notes', 'total_orders', 'total_spend', 'total_spend_currency', 'avg_order_value', 'date_first_order',
    ];
    public function Contracts()
    {
        return $this->hasMany(SsContract::class, 'ss_customer_id', 'id')->select('id', 'ss_customer_id', 'status');
    }
    public function ActivityLog()
    {
        return $this->hasMany(SsActivityLog::class, 'ss_customer_id', 'id')->select('id', 'ss_customer_id', 'created_at', 'message', 'user_type', 'user_name')->orderBy('created_at', 'desc');
    }
}
