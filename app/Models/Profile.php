<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'paxos_profile_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function fiatDepositInstructions()
    {
        return $this->hasMany(FiatDepositInstruction::class);
    }

    public function depositAddresses()
    {
        return $this->hasMany(DepositAddress::class);
    }

    public function fiatWithdrawals()
    {
        return $this->hasMany(FiatWithdrawal::class);
    }

    public function cryptoWithdrawals()
    {
        return $this->hasMany(CryptoWithdrawal::class);
    }
}
