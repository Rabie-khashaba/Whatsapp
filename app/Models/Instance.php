<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'label',
        'phone_number',
        'access_token',
        'status',
        'qrcode',
        // Green API Credentials
        'green_instance_id',
        'green_api_token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
