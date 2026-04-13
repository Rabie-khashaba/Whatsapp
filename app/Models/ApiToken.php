<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $fillable = ['user_id', 'token', 'active'];



    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
