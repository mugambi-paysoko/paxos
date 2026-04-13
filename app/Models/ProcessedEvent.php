<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedEvent extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'identity_id',
        'processed_at',
        'event_data',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'event_data' => 'array',
        ];
    }

    /**
     * Check if an event has already been processed
     */
    public static function isProcessed(string $eventId): bool
    {
        return self::where('event_id', $eventId)->exists();
    }

    /**
     * Mark an event as processed
     */
    public static function markAsProcessed(string $eventId, string $eventType, ?string $identityId = null, ?array $eventData = null): self
    {
        return self::create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'identity_id' => $identityId,
            'processed_at' => now(),
            'event_data' => $eventData,
        ]);
    }
}
