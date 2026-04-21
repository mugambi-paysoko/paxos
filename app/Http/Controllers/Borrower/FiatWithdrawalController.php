<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\FiatWithdrawal;
use App\Models\Profile;
use App\Services\PaxosService;
use App\Support\TransferStatusDigest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FiatWithdrawalController extends Controller
{
    protected PaxosService $paxosService;

    public function __construct(PaxosService $paxosService)
    {
        $this->middleware('auth');
        $this->paxosService = $paxosService;
    }

    public function index()
    {
        $user = auth()->user();
        $fiatWithdrawals = $user->fiatWithdrawals()
            ->with('profile', 'fiatAccount', 'identity')
            ->latest()
            ->get();

        $fiatWithdrawalsIndexDigest = TransferStatusDigest::forUserFiatWithdrawals($user);

        return view('borrower.fiat-withdrawals.index', compact('fiatWithdrawals', 'fiatWithdrawalsIndexDigest'));
    }

    public function create()
    {
        $profiles = auth()->user()->profiles()
            ->whereNotNull('paxos_profile_id')
            ->latest()
            ->get();

        $fiatAccounts = auth()->user()->fiatAccounts()
            ->whereNotNull('paxos_fiat_account_id')
            ->where('status', 'APPROVED')
            ->with('identity')
            ->latest()
            ->get();

        return view('borrower.fiat-withdrawals.create', compact('profiles', 'fiatAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'fiat_account_id' => 'required|exists:fiat_accounts,id',
            'amount' => 'nullable|numeric|min:0.01|required_without:total',
            'total' => 'nullable|numeric|min:0.01|required_without:amount',
            'asset' => 'required|in:USD',
            'ref_id' => 'nullable|string|max:255',
            'memo' => 'nullable|string|max:255',
            'metadata_label' => 'nullable|string|max:100',
        ]);

        $profile = Profile::where('id', $validated['profile_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $fiatAccount = FiatAccount::where('id', $validated['fiat_account_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $identity = $fiatAccount->identity;

        try {
            $refId = $validated['ref_id'] ?? Str::uuid()->toString();

            $paxosData = [
                'asset' => $validated['asset'],
                'fiat_account_id' => $fiatAccount->paxos_fiat_account_id,
                'profile_id' => $profile->paxos_profile_id,
                'ref_id' => $refId,
            ];

            if (! empty($validated['amount'])) {
                $paxosData['amount'] = number_format($validated['amount'], 2, '.', '');
            }

            if (! empty($validated['total'])) {
                $paxosData['total'] = number_format($validated['total'], 2, '.', '');
            }

            if (! empty($validated['memo'])) {
                $paxosData['memo'] = $validated['memo'];
            }

            if (! empty($validated['metadata_label'])) {
                $paxosData['metadata'] = [
                    'label' => $validated['metadata_label'],
                ];
            }

            if (! config('services.paxos.first_party', true) && $identity) {
                $paxosData['identity_id'] = $identity->paxos_identity_id;
                $linkedAccount = $identity->accounts()->whereNotNull('paxos_account_id')->latest()->first();
                if ($linkedAccount) {
                    $paxosData['account_id'] = $linkedAccount->paxos_account_id;
                }
            }

            $paxosResponse = $this->paxosService->createFiatWithdrawal($paxosData);

            $fiatWithdrawal = FiatWithdrawal::create([
                'user_id' => auth()->id(),
                'profile_id' => $profile->id,
                'fiat_account_id' => $fiatAccount->id,
                'identity_id' => $identity?->id,
                'paxos_transfer_id' => $paxosResponse['id'],
                'ref_id' => $paxosResponse['ref_id'] ?? $refId,
                'amount' => $paxosResponse['amount'] ?? ($paxosData['amount'] ?? $paxosData['total']),
                'asset' => $paxosResponse['asset'] ?? 'USD',
                'status' => $paxosResponse['status'] ?? 'PENDING',
                'transfer_type' => $paxosResponse['type'] ?? null,
                'memo' => $paxosResponse['memo'] ?? ($validated['memo'] ?? null),
                'metadata' => $paxosResponse['metadata'] ?? ($paxosData['metadata'] ?? null),
                'paxos_response' => $paxosResponse,
                'paxos_created_at' => $paxosResponse['created_at'] ?? null,
            ]);

            return redirect()->route('borrower.fiat-withdrawals.show', $fiatWithdrawal)
                ->with('success', 'Fiat withdrawal created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create fiat withdrawal', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'profile_id' => $profile->id,
                'fiat_account_id' => $fiatAccount->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create fiat withdrawal in Paxos. '.$e->getMessage()]);
        }
    }

    public function show(FiatWithdrawal $fiatWithdrawal)
    {
        if ($fiatWithdrawal->user_id !== auth()->id()) {
            abort(403);
        }

        $fiatWithdrawal->load('profile.account.identity', 'fiatAccount.identity', 'identity');

        return view('borrower.fiat-withdrawals.show', compact('fiatWithdrawal'));
    }
}
