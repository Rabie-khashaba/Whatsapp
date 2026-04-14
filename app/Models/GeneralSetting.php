<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_url',
        'support_email',
        'support_phone',
        'site_description',
    ];
}
