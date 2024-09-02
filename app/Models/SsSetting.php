<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsSetting extends Model
{
    use SoftDeletes;

    protected $casts = [
        'notify_new' => 'boolean',
        'notify_cancel' => 'boolean',
        'notify_revoke' => 'boolean',
        'notify_paymentfailed' => 'boolean',
        'send_account_invites' => 'boolean',
    ];
}
