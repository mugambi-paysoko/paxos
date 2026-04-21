<?php

namespace App\Support;

use App\Models\CryptoWithdrawal;
use App\Models\FiatWithdrawal;
use App\Models\User;

final class TransferStatusDigest
{
    public static function forUserCryptoWithdrawals(User $user): string
    {
        $payload = CryptoWithdrawal::query()
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get(['id', 'status', 'updated_at'])
            ->map(fn (CryptoWithdrawal $w): string => $w->id.':'.strtoupper((string) ($w->status ?? 'PENDING')).':'.$w->updated_at?->getTimestamp())
            ->implode('|');

        return hash('xxh128', $payload);
    }

    public static function forUserFiatWithdrawals(User $user): string
    {
        $payload = FiatWithdrawal::query()
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get(['id', 'status', 'updated_at'])
            ->map(fn (FiatWithdrawal $w): string => $w->id.':'.strtoupper((string) ($w->status ?? 'PENDING')).':'.$w->updated_at?->getTimestamp())
            ->implode('|');

        return hash('xxh128', $payload);
    }
}
