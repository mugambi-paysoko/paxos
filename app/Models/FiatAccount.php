<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiatAccount extends Model
{
    protected $fillable = [
        'user_id',
        'identity_id',
        'paxos_fiat_account_id',
        'paxos_identity_id',
        'paxos_account_id',
        'status',
        'fiat_account_owner',
        'fiat_network_instructions',
        'paxos_created_at',
    ];

    protected $casts = [
        'fiat_account_owner' => 'array',
        'fiat_network_instructions' => 'array',
        'paxos_created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }
}
