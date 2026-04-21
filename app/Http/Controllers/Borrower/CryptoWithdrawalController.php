<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\CryptoWithdrawal;
use App\Models\Profile;
use App\Services\PaxosService;
use App\Support\TransferStatusDigest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CryptoWithdrawalController extends Controller
{
    public function __construct(protected PaxosService $paxosService)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $cryptoWithdrawals = $user->cryptoWithdrawals()
            ->with('profile', 'identity')
            ->latest()
            ->get();

        $cryptoWithdrawalsIndexDigest = TransferStatusDigest::forUserCryptoWithdrawals($user);

        return view('borrower.crypto-withdrawals.index', compact('cryptoWithdrawals', 'cryptoWithdrawalsIndexDigest'));
    }

    public function create(Profile $profile)
    {
        if ($profile->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $profile->paxos_profile_id) {
            return redirect()
                ->route('borrower.profiles.show', $profile)
                ->withErrors(['error' => 'This profile does not have a Paxos profile ID.']);
        }

        return view('borrower.crypto-withdrawals.create', compact('profile'));
    }

    public function store(Request $request, Profile $profile)
    {
        if ($profile->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $profile->paxos_profile_id) {
            return redirect()
                ->route('borrower.profiles.show', $profile)
                ->withErrors(['error' => 'This profile does not have a Paxos profile ID.']);
        }

        $validated = $request->validate([
            'asset' => 'required|string|max:20',
            'crypto_network' => ['required', Rule::in(CryptoWithdrawal::allowedNetworks())],
            'destination_address' => 'required|string|max:255',
            'amount' => 'nullable|numeric|min:0.00000001|required_without:total',
            'total' => 'nullable|numeric|min:0.00000001|required_without:amount',
            'balance_asset' => 'nullable|string|max:20',
            'ref_id' => 'nullable|string|max:255',
            'memo' => 'nullable|string|max:255',
            'metadata_label' => 'nullable|string|max:100',
            'beneficiary_first_name' => 'nullable|string|max:255',
            'beneficiary_last_name' => 'nullable|string|max:255',
            'beneficiary_institution_name' => 'nullable|string|max:255',
        ]);

        $profile->load('account.identity');

        try {
            $refId = $validated['ref_id'] ?: (string) Str::uuid();

            $paxosData = [
                'profile_id' => $profile->paxos_profile_id,
                'asset' => strtoupper($validated['asset']),
                'destination_address' => $validated['destination_address'],
                'crypto_network' => $validated['crypto_network'],
                'ref_id' => $refId,
            ];

            if (! empty($validated['amount'])) {
                $paxosData['amount'] = (string) $validated['amount'];
            }

            if (! empty($validated['total'])) {
                $paxosData['total'] = (string) $validated['total'];
            }

            if (! empty($validated['balance_asset'])) {
                $paxosData['balance_asset'] = strtoupper($validated['balance_asset']);
            }

            if (! empty($validated['memo'])) {
                $paxosData['memo'] = $validated['memo'];
            }

            if (! empty($validated['metadata_label'])) {
                $paxosData['metadata'] = ['label' => $validated['metadata_label']];
            }

            $beneficiary = [];
            if (! empty($validated['beneficiary_first_name']) || ! empty($validated['beneficiary_last_name'])) {
                $beneficiary['person_details'] = [
                    'first_name' => $validated['beneficiary_first_name'] ?? '',
                    'last_name' => $validated['beneficiary_last_name'] ?? '',
                ];
            }

            if (! empty($validated['beneficiary_institution_name'])) {
                $beneficiary['institution_details'] = [
                    'name' => $validated['beneficiary_institution_name'],
                ];
            }

            if ($beneficiary !== []) {
                $paxosData['beneficiary'] = $beneficiary;
            }

            if (! config('services.paxos.first_party', true)) {
                $identity = $profile->account?->identity;
                if ($identity && $identity->paxos_identity_id) {
                    $paxosData['identity_id'] = $identity->paxos_identity_id;
                }

                if ($profile->account && $profile->account->paxos_account_id) {
                    $paxosData['account_id'] = $profile->account->paxos_account_id;
                }
            }

            $paxosResponse = $this->paxosService->createCryptoWithdrawal($paxosData);

            $cryptoWithdrawal = CryptoWithdrawal::create([
                'user_id' => auth()->id(),
                'profile_id' => $profile->id,
                'identity_id' => $profile->account?->identity?->id,
                'paxos_transfer_id' => $paxosResponse['id'],
                'ref_id' => $paxosResponse['ref_id'] ?? $refId,
                'amount' => $paxosResponse['amount'] ?? ($paxosData['amount'] ?? $paxosData['total']),
                'asset' => $paxosResponse['asset'] ?? $paxosData['asset'],
                'balance_asset' => $paxosResponse['balance_asset'] ?? ($paxosData['balance_asset'] ?? null),
                'destination_address' => $paxosResponse['destination_address'] ?? $paxosData['destination_address'],
                'crypto_network' => $paxosResponse['crypto_network'] ?? $paxosData['crypto_network'],
                'status' => $paxosResponse['status'] ?? 'PENDING',
                'transfer_type' => $paxosResponse['type'] ?? 'CRYPTO_WITHDRAWAL',
                'memo' => $paxosResponse['memo'] ?? ($paxosData['memo'] ?? null),
                'metadata' => $paxosResponse['metadata'] ?? ($paxosData['metadata'] ?? null),
                'beneficiary' => $paxosData['beneficiary'] ?? null,
                'paxos_response' => $paxosResponse,
                'paxos_created_at' => $paxosResponse['created_at'] ?? null,
            ]);

            return redirect()->route('borrower.crypto-withdrawals.show', $cryptoWithdrawal)
                ->with('success', 'Crypto withdrawal created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create crypto withdrawal', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'profile_id' => $profile->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create crypto withdrawal in Paxos. '.$e->getMessage()]);
        }
    }

    public function show(CryptoWithdrawal $cryptoWithdrawal)
    {
        if ($cryptoWithdrawal->user_id !== auth()->id()) {
            abort(403);
        }

        $cryptoWithdrawal->load('profile.account.identity', 'identity');

        return view('borrower.crypto-withdrawals.show', compact('cryptoWithdrawal'));
    }
}
