<?php

namespace App\Http\Controllers;

use App\Models\CryptoWithdrawal;
use App\Models\FiatWithdrawal;
use App\Models\Identity;
use App\Support\TransferStatusDigest;
use Illuminate\Http\JsonResponse;

class ResourceStatusJsonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function identity(Identity $identity): JsonResponse
    {
        abort_unless($identity->user_id === auth()->id(), 403);

        $identity->refresh();

        return response()->json([
            'resource' => 'identity',
            'id' => $identity->id,
            'id_verification_status' => $identity->id_verification_status,
            'sanctions_verification_status' => $identity->sanctions_verification_status,
            'updated_at' => $identity->updated_at?->toIso8601String(),
        ]);
    }

    public function fiatWithdrawal(FiatWithdrawal $fiatWithdrawal): JsonResponse
    {
        abort_unless($fiatWithdrawal->user_id === auth()->id(), 403);

        $fiatWithdrawal->refresh();

        return response()->json([
            'resource' => 'fiat_withdrawal',
            'id' => $fiatWithdrawal->id,
            'status' => $fiatWithdrawal->status ?? 'PENDING',
            'paxos_transfer_id' => $fiatWithdrawal->paxos_transfer_id,
            'updated_at' => $fiatWithdrawal->updated_at?->toIso8601String(),
        ]);
    }

    public function cryptoWithdrawal(CryptoWithdrawal $cryptoWithdrawal): JsonResponse
    {
        abort_unless($cryptoWithdrawal->user_id === auth()->id(), 403);

        $cryptoWithdrawal->refresh();

        return response()->json([
            'resource' => 'crypto_withdrawal',
            'id' => $cryptoWithdrawal->id,
            'status' => $cryptoWithdrawal->status ?? 'PENDING',
            'paxos_transfer_id' => $cryptoWithdrawal->paxos_transfer_id,
            'updated_at' => $cryptoWithdrawal->updated_at?->toIso8601String(),
        ]);
    }

    public function cryptoWithdrawalsIndexDigest(): JsonResponse
    {
        return response()->json([
            'resource' => 'crypto_withdrawals_index',
            'digest' => TransferStatusDigest::forUserCryptoWithdrawals(auth()->user()),
        ]);
    }

    public function fiatWithdrawalsIndexDigest(): JsonResponse
    {
        return response()->json([
            'resource' => 'fiat_withdrawals_index',
            'digest' => TransferStatusDigest::forUserFiatWithdrawals(auth()->user()),
        ]);
    }
}
