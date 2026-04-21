<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Models\DepositAddress;
use App\Models\Profile;
use App\Services\PaxosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DepositAddressController extends Controller
{
    public function __construct(protected PaxosService $paxosService)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $depositAddresses = auth()->user()->depositAddresses()
            ->with('profile')
            ->latest()
            ->get();

        return view('lender.deposit-addresses.index', compact('depositAddresses'));
    }

    public function create(Profile $profile)
    {
        if ($profile->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $profile->paxos_profile_id) {
            return redirect()
                ->route('lender.profiles.show', $profile)
                ->withErrors(['error' => 'This profile does not have a Paxos profile ID. Create an account with a profile first.']);
        }

        return view('lender.deposit-addresses.create', compact('profile'));
    }

    public function store(Request $request, Profile $profile)
    {
        if ($profile->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $profile->paxos_profile_id) {
            return redirect()
                ->route('lender.profiles.show', $profile)
                ->withErrors(['error' => 'This profile does not have a Paxos profile ID.']);
        }

        $validated = $request->validate([
            'crypto_network' => ['required', Rule::in(DepositAddress::allowedCryptoNetworks())],
            'conversion_target_asset' => 'nullable|in:NO_CONVERSION,USD',
            'ref_id' => 'nullable|string|max:255',
            'metadata_label' => 'nullable|string|max:100',
        ]);

        $profile->load('account.identity');

        $refId = $validated['ref_id'] !== null && $validated['ref_id'] !== ''
            ? $validated['ref_id']
            : (string) Str::uuid();

        $metadata = null;
        if (! empty($validated['metadata_label'])) {
            $metadata = ['label' => $validated['metadata_label']];
        }

        $paxosData = [
            'profile_id' => $profile->paxos_profile_id,
            'crypto_network' => $validated['crypto_network'],
            'ref_id' => $refId,
        ];

        if (! config('services.paxos.first_party', true)) {
            $identity = $profile->account?->identity;
            if ($identity && $identity->paxos_identity_id) {
                $paxosData['identity_id'] = $identity->paxos_identity_id;
            }

            if ($profile->account && $profile->account->paxos_account_id) {
                $paxosData['account_id'] = $profile->account->paxos_account_id;
            }
        }

        if (! empty($validated['conversion_target_asset'])) {
            $paxosData['conversion_target_asset'] = $validated['conversion_target_asset'];
        }

        if ($metadata !== null) {
            $paxosData['metadata'] = $metadata;
        }

        try {
            $paxosResponse = $this->paxosService->createDepositAddress($paxosData);

            DepositAddress::create([
                'user_id' => auth()->id(),
                'profile_id' => $profile->id,
                'paxos_deposit_address_id' => $paxosResponse['id'],
                'ref_id' => $paxosResponse['ref_id'] ?? $refId,
                'crypto_network' => $paxosResponse['crypto_network'] ?? $validated['crypto_network'],
                'address' => $paxosResponse['address'],
                'paxos_profile_id' => $paxosResponse['profile_id'] ?? $profile->paxos_profile_id,
                'paxos_identity_id' => $paxosResponse['identity_id'] ?? null,
                'paxos_account_id' => $paxosResponse['account_id'] ?? null,
                'conversion_target_asset' => $paxosResponse['conversion_target_asset'] ?? null,
                'metadata' => isset($paxosResponse['metadata']) && is_array($paxosResponse['metadata'])
                    ? $paxosResponse['metadata']
                    : $metadata,
            ]);

            return redirect()->route('lender.deposit-addresses.index')
                ->with('success', 'Crypto deposit address created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create deposit address', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'profile_id' => $profile->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create deposit address in Paxos. '.$e->getMessage()]);
        }
    }
}
