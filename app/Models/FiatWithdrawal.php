<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiatWithdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'profile_id',
        'fiat_account_id',
        'identity_id',
        'paxos_transfer_id',
        'ref_id',
        'amount',
        'asset',
        'status',
        'transfer_type',
        'memo',
        'metadata',
        'paxos_response',
        'paxos_created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
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

    public function fiatAccount(): BelongsTo
    {
        return $this->belongsTo(FiatAccount::class);
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }
}
