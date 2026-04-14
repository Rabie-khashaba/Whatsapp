<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'notify_new_customer',
        'notify_new_payment',
        'notify_expiring',
        'notify_expired',
    ];

    protected $casts = [
        'notify_new_customer' => 'boolean',
        'notify_new_payment' => 'boolean',
        'notify_expiring' => 'boolean',
        'notify_expired' => 'boolean',
    ];
}
