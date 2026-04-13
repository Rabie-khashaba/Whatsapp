<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_url',
        'support_email',
        'support_phone',
        'site_description',
        'vodafone_cash_enabled',
        'vodafone_cash_number',
        'bank_transfer_enabled',
        'bank_name',
        'bank_account_number',
        'bank_iban',
        'credit_card_enabled',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'from_email',
        'from_name',
        'notify_new_customer',
        'notify_new_payment',
        'notify_expiring',
        'notify_expired',
    ];

    protected $casts = [
        'vodafone_cash_enabled' => 'boolean',
        'bank_transfer_enabled' => 'boolean',
        'credit_card_enabled' => 'boolean',
        'smtp_port' => 'integer',
        'notify_new_customer' => 'boolean',
        'notify_new_payment' => 'boolean',
        'notify_expiring' => 'boolean',
        'notify_expired' => 'boolean',
    ];
}
