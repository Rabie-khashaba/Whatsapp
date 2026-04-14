<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'vodafone_cash_enabled',
        'vodafone_cash_number',
        'bank_transfer_enabled',
        'bank_name',
        'bank_account_number',
        'bank_iban',
        'credit_card_enabled',
    ];

    protected $casts = [
        'vodafone_cash_enabled' => 'boolean',
        'bank_transfer_enabled' => 'boolean',
        'credit_card_enabled' => 'boolean',
    ];
}
