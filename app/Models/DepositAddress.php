<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositAddress extends Model
{
    /**
     * @return list<string>
     */
    public static function allowedCryptoNetworks(): array
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

    protected $fillable = [
        'user_id',
        'profile_id',
        'paxos_deposit_address_id',
        'ref_id',
        'crypto_network',
        'address',
        'paxos_profile_id',
        'paxos_identity_id',
        'paxos_account_id',
        'conversion_target_asset',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
