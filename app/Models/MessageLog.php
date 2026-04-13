<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'instance_id',
        'phone',
        'message',
        'status',
        'response',
        'message_id',
        'type',
        'is_delivered',
        'is_read',
        'retry_count',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'response' => 'array',
        'is_delivered' => 'boolean',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    // ==================== Relationships ====================

    public function instance(): BelongsTo
    {
        return $this->belongsTo(Instance::class);
    }

    // ==================== Scopes ====================

    public function scopeSuccessful($query)
    {
        return $query->where('status', 200);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', '!=', 200)->orWhere('status', 0);
    }

    public function scopeDelivered($query)
    {
        return $query->where('is_delivered', true);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sent_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('sent_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('sent_at', now()->month)
                     ->whereYear('sent_at', now()->year);
    }

    public function scopeByInstance($query, $instanceId)
    {
        return $query->where('instance_id', $instanceId);
    }

    public function scopeByPhone($query, $phone)
    {
        return $query->where('phone', $phone);
    }

    // ==================== Accessors ====================

    public function getIsSuccessfulAttribute(): bool
    {
        return $this->status === 200;
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            200 => 'Success',
            0 => 'Failed',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            429 => 'Rate Limited',
            500 => 'Server Error',
            default => 'Unknown'
        };
    }

    public function getDeliveryTimeAttribute(): ?int
    {
        if (!$this->delivered_at || !$this->sent_at) {
            return null;
        }
        
        return $this->sent_at->diffInSeconds($this->delivered_at);
    }

    // ==================== Helper Methods ====================

    public function markAsDelivered(): void
    {
        $this->update([
            'is_delivered' => true,
            'delivered_at' => now()
        ]);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    public function incrementRetry(): void
    {
        $this->increment('retry_count');
    }

    // ==================== Static Methods ====================

    public static function getStatsForInstance(int $instanceId, int $days = 7): array
    {
        $query = self::byInstance($instanceId)
            ->where('sent_at', '>=', now()->subDays($days));

        $total = $query->count();
        $successful = (clone $query)->successful()->count();
        $delivered = (clone $query)->delivered()->count();
        $read = (clone $query)->read()->count();

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $total - $successful,
            'delivered' => $delivered,
            'read' => $read,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100, 2) : 0,
            'read_rate' => $delivered > 0 ? round(($read / $delivered) * 100, 2) : 0,
        ];
    }

    public static function getHourlyStats(int $instanceId): array
    {
        return self::byInstance($instanceId)
            ->where('sent_at', '>=', now()->subDay())
            ->selectRaw('HOUR(sent_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
    }

    public static function getDailyStats(int $instanceId, int $days = 30): array
    {
        return self::byInstance($instanceId)
            ->where('sent_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(sent_at) as date, COUNT(*) as total, 
                        SUM(CASE WHEN status = 200 THEN 1 ELSE 0 END) as successful')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }

    public static function getTopRecipients(int $instanceId, int $limit = 10): array
    {
        return self::byInstance($instanceId)
            ->selectRaw('phone, COUNT(*) as count')
            ->groupBy('phone')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}