<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiatDeposit extends Model
{
    protected $fillable = [
        'user_id',
        'fiat_deposit_instruction_id',
        'paxos_deposit_id',
        'amount',
        'asset',
        'memo_id',
        'status',
        'fiat_network_instructions',
        'fiat_account_owner',
        'paxos_response',
    ];

    protected $casts = [
        'fiat_network_instructions' => 'array',
        'fiat_account_owner' => 'array',
        'paxos_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fiatDepositInstruction(): BelongsTo
    {
        return $this->belongsTo(FiatDepositInstruction::class);
    }
}
