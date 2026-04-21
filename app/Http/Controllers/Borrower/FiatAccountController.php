<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\FiatAccount;
use App\Models\Identity;
use App\Services\PaxosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FiatAccountController extends Controller
{
    protected PaxosService $paxosService;

    public function __construct(PaxosService $paxosService)
    {
        $this->middleware('auth');
        $this->paxosService = $paxosService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fiatAccounts = auth()->user()->fiatAccounts()->with('identity')->latest()->get();

        return view('borrower.fiat-accounts.index', compact('fiatAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $identities = auth()->user()->identities()
            ->whereNotNull('paxos_identity_id')
            ->get();

        return view('borrower.fiat-accounts.create', compact('identities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'identity_id' => 'required|exists:identities,id',
            'fiat_network' => 'required|in:WIRE,CUBIX',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'ref_id' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255|required_if:fiat_network,WIRE',
            'routing_number' => 'nullable|string|max:255|required_if:fiat_network,WIRE',
            'routing_number_type' => 'nullable|in:ABA,SWIFT|required_if:fiat_network,WIRE',
            'bank_name' => 'nullable|string|max:255|required_if:fiat_network,WIRE',
            'bank_country' => 'nullable|string|max:255|required_if:fiat_network,WIRE',
            'bank_address1' => 'nullable|string|max:255',
            'bank_city' => 'nullable|string|max:255',
            'bank_province' => 'nullable|string|max:255',
            'bank_zip_code' => 'nullable|string|max:255',
            'owner_address1' => 'nullable|string|max:255|required_if:fiat_network,WIRE',
            'owner_city' => 'nullable|string|max:255|required_if:fiat_network,WIRE',
            'owner_country' => 'nullable|string|max:255|required_if:fiat_network,WIRE',
            'owner_province' => 'nullable|string|max:255',
            'owner_zip_code' => 'nullable|string|max:255',
            'cubix_account_id' => 'nullable|string|max:255|required_if:fiat_network,CUBIX',
        ]);

        // Ensure identity belongs to user
        $identity = Identity::where('id', $validated['identity_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            // Prepare data for Paxos API
            $paxosData = [
                'fiat_account_owner' => [
                    'person_details' => [
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                    ],
                ],
            ];

            if (! empty($validated['ref_id'])) {
                $paxosData['ref_id'] = $validated['ref_id'];
            }

            if ($validated['fiat_network'] === 'WIRE') {
                $paxosData['fiat_network_instructions'] = [
                    'wire' => [
                        'account_number' => $validated['account_number'],
                        'routing_details' => [
                            'routing_number' => $validated['routing_number'],
                            'routing_number_type' => $validated['routing_number_type'],
                            'bank_name' => $validated['bank_name'],
                            'bank_address' => [
                                'country' => $validated['bank_country'],
                                'address1' => $validated['bank_address1'] ?? null,
                                'city' => $validated['bank_city'] ?? null,
                                'province' => $validated['bank_province'] ?? null,
                                'zip_code' => $validated['bank_zip_code'] ?? null,
                            ],
                        ],
                        'fiat_account_owner_address' => [
                            'address1' => $validated['owner_address1'],
                            'city' => $validated['owner_city'],
                            'country' => $validated['owner_country'],
                            'province' => $validated['owner_province'] ?? null,
                            'zip_code' => $validated['owner_zip_code'] ?? null,
                        ],
                    ],
                ];
            } else {
                $paxosData['fiat_network_instructions'] = [
                    'cubix' => [
                        'account_id' => $validated['cubix_account_id'],
                    ],
                ];
            }

            // Third-party integrations must pass identity/account references.
            if (! config('services.paxos.first_party', true)) {
                $paxosData['identity_id'] = $identity->paxos_identity_id;
                $linkedAccount = $identity->accounts()
                    ->whereNotNull('paxos_account_id')
                    ->latest()
                    ->first();
                if ($linkedAccount) {
                    $paxosData['account_id'] = $linkedAccount->paxos_account_id;
                }
            }

            // Call Paxos API - this will throw an exception if it fails
            $paxosResponse = $this->paxosService->createFiatAccount($paxosData);

            // Only save to database after successful Paxos API call
            $fiatAccount = FiatAccount::create([
                'user_id' => auth()->id(),
                'identity_id' => $identity->id,
                'paxos_fiat_account_id' => $paxosResponse['id'],
                'paxos_identity_id' => $paxosResponse['identity_id'] ?? null,
                'paxos_account_id' => $paxosResponse['account_id'] ?? null,
                'status' => $paxosResponse['status'] ?? 'PENDING',
                'fiat_account_owner' => $paxosResponse['fiat_account_owner'] ?? null,
                'fiat_network_instructions' => $paxosResponse['fiat_network_instructions'] ?? null,
                'paxos_created_at' => isset($paxosResponse['created_at']) ? $paxosResponse['created_at'] : null,
            ]);

            return redirect()->route('borrower.fiat-accounts.show', $fiatAccount)
                ->with('success', 'Fiat account created successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to create fiat account', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'identity_id' => $identity->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create fiat account in Paxos. Please try again. Error: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FiatAccount $fiatAccount)
    {
        // Ensure the fiat account belongs to the authenticated user
        if ($fiatAccount->user_id !== auth()->id()) {
            abort(403);
        }

        $fiatAccount->load('identity');

        return view('borrower.fiat-accounts.show', compact('fiatAccount'));
    }

    /**
     * Refresh fiat account status from Paxos API
     */
    public function refresh(FiatAccount $fiatAccount)
    {
        // Ensure the fiat account belongs to the authenticated user
        if ($fiatAccount->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            // Get latest data from Paxos API
            $paxosResponse = $this->paxosService->getFiatAccount($fiatAccount->paxos_fiat_account_id);

            // Check if status has changed
            $oldStatus = $fiatAccount->status;
            $newStatus = $paxosResponse['status'] ?? $fiatAccount->status;

            // Update the fiat account with latest data from Paxos
            $fiatAccount->update([
                'paxos_identity_id' => $paxosResponse['identity_id'] ?? $fiatAccount->paxos_identity_id,
                'paxos_account_id' => $paxosResponse['account_id'] ?? $fiatAccount->paxos_account_id,
                'status' => $newStatus,
                'fiat_account_owner' => $paxosResponse['fiat_account_owner'] ?? $fiatAccount->fiat_account_owner,
                'fiat_network_instructions' => $paxosResponse['fiat_network_instructions'] ?? $fiatAccount->fiat_network_instructions,
                'paxos_created_at' => isset($paxosResponse['created_at']) ? $paxosResponse['created_at'] : $fiatAccount->paxos_created_at,
            ]);

            $message = 'Fiat account refreshed successfully.';
            if ($oldStatus !== $newStatus) {
                $message .= " Status changed from {$oldStatus} to {$newStatus}.";
            } else {
                $message .= " Status remains {$newStatus}.";
            }

            return redirect()->route('borrower.fiat-accounts.show', $fiatAccount)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to refresh fiat account', [
                'error' => $e->getMessage(),
                'fiat_account_id' => $fiatAccount->id,
            ]);

            return redirect()->route('borrower.fiat-accounts.show', $fiatAccount)
                ->with('error', 'Failed to refresh fiat account from Paxos. Error: '.$e->getMessage());
        }
    }
}
