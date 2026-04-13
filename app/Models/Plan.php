<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'currency',
        'billing_cycle',
        'monthly_price',
        'yearly_price',
        'max_instances',
        'max_messages',
        'max_campaigns',
        'color',
        'description',
        'features',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];
}
