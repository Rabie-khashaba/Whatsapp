<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_id',
        'payment_id',
        'invoice_number',
        'amount',
        'currency',
        'status',
        'issued_at',
        'due_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_at' => 'date',
        'due_at' => 'date',
        'paid_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
