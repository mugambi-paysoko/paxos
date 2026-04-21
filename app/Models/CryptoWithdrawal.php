<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoWithdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'profile_id',
        'identity_id',
        'paxos_transfer_id',
        'ref_id',
        'amount',
        'asset',
        'balance_asset',
        'destination_address',
        'crypto_network',
        'status',
        'transfer_type',
        'memo',
        'metadata',
        'beneficiary',
        'paxos_response',
        'paxos_created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'beneficiary' => 'array',
        'paxos_response' => 'array',
        'paxos_created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }

    public static function allowedNetworks(): array
    {
        return [
            'BITCOIN',
            'ETHEREUM',
            'BITCOIN_CASH',
            'LITECOIN',
            'SOLANA',
            'POLYGON_POS',
            'BASE',
            'ARBITRUM_ONE',
            'STELLAR',
            'INK',
            'XLAYER',
        ];
    }
}
