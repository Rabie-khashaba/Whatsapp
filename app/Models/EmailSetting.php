<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $fillable = [
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'from_email',
        'from_name',
    ];

    protected $casts = [
        'smtp_port' => 'integer',
    ];
}
