<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'country_code',
        'plan',
        'status',
        'expiry_date',
        'max_instances',
        'billing_cycle',
        'trial_ends_at',
        'used_trial',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'trial_ends_at' => 'datetime',
        'max_instances' => 'integer',
        'user_id' => 'integer',
        'used_trial' => 'boolean',
    ];

    /**
     * Get the user that owns the customer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if customer has active trial period
     */
    public function hasActiveTrial(): bool
    {
        return $this->plan === 'Trial' &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }

    public function updateTrialStatusIfExpired(): bool
    {
        if ($this->plan === 'Trial' && $this->trial_ends_at && $this->trial_ends_at->isPast() && $this->status === 'active') {
            $this->status = 'pending';
            $this->save();
            return true;
        }

        return false;
    }
}

