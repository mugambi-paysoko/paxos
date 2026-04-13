<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiatDepositInstruction extends Model
{
    protected $fillable = [
        'user_id',
        'profile_id',
        'fiat_account_id',
        'paxos_deposit_instruction_id',
        'paxos_profile_id',
        'paxos_identity_id',
        'paxos_account_id',
        'fiat_network',
        'ref_id',
        'routing_number_type',
        'memo_id',
        'status',
        'fiat_network_instructions',
        'fiat_account_owner',
        'metadata',
        'paxos_created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'fiat_network_instructions' => 'array',
        'fiat_account_owner' => 'array',
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

    public function fiatDeposits()
    {
        return $this->hasMany(FiatDeposit::class);
    }
}
