<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\FiatDepositInstruction;
use App\Models\Profile;
use App\Services\PaxosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FiatDepositInstructionController extends Controller
{
    protected PaxosService $paxosService;

    public function __construct(PaxosService $paxosService)
    {
        $this->middleware('auth');
        $this->paxosService = $paxosService;
    }

    /**
     * Display the specified resource.
     */
    public function show(FiatDepositInstruction $fiatDepositInstruction)
    {
        // Ensure the instruction belongs to the authenticated user
        if ($fiatDepositInstruction->user_id !== auth()->id()) {
            abort(403);
        }

        $fiatDepositInstruction->load('profile.account.identity', 'fiatDeposits');

        return view('borrower.fiat-deposit-instructions.show', compact('fiatDepositInstruction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Profile $profile)
    {
        // Ensure the profile belongs to the authenticated user
        if ($profile->user_id !== auth()->id()) {
            abort(403);
        }

        return view('borrower.fiat-deposit-instructions.create', compact('profile'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Profile $profile)
    {
        // Ensure the profile belongs to the authenticated user
        if ($profile->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'fiat_network' => 'required|in:WIRE,DBS_ACT,CUBIX,SCB',
            'routing_number_type' => 'nullable|in:ABA,SWIFT',
            'ref_id' => 'nullable|string|max:255',
        ]);

        try {
            // Generate unique ref_id if not provided
            $refId = $validated['ref_id'] ?? Str::uuid()->toString();

            // Prepare data for Paxos API - only send the 4 required fields
            $paxosData = [
                'profile_id' => $profile->paxos_profile_id,
                'fiat_network' => $validated['fiat_network'],
                'ref_id' => $refId,
            ];

            // Add routing_number_type only if provided
            if (!empty($validated['routing_number_type'])) {
                $paxosData['routing_number_type'] = $validated['routing_number_type'];
            }

            // Call Paxos API - this will throw an exception if it fails
            $paxosResponse = $this->paxosService->createFiatDepositInstruction($paxosData);

            // Only save to database after successful Paxos API call
            $fiatDepositInstruction = FiatDepositInstruction::create([
                'user_id' => auth()->id(),
                'profile_id' => $profile->id,
                'paxos_deposit_instruction_id' => $paxosResponse['id'],
                'paxos_profile_id' => $paxosResponse['profile_id'] ?? $profile->paxos_profile_id,
                'paxos_identity_id' => $paxosResponse['identity_id'] ?? null,
                'paxos_account_id' => $paxosResponse['account_id'] ?? null,
                'fiat_network' => $validated['fiat_network'],
                'ref_id' => $paxosResponse['ref_id'] ?? $refId,
                'routing_number_type' => $validated['routing_number_type'] ?? null,
                'memo_id' => $paxosResponse['memo_id'] ?? null, // Important for wire transfers
                'status' => $paxosResponse['status'] ?? null,
                'fiat_network_instructions' => $paxosResponse['fiat_network_instructions'] ?? null,
                'fiat_account_owner' => $paxosResponse['fiat_account_owner'] ?? null,
                'metadata' => $paxosResponse['metadata'] ?? null,
                'paxos_created_at' => isset($paxosResponse['created_at']) ? $paxosResponse['created_at'] : null,
            ]);

            return redirect()->route('borrower.profiles.show', $profile)
                ->with('success', 'Fiat deposit instruction created successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to create fiat deposit instruction', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'profile_id' => $profile->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create fiat deposit instruction in Paxos. Please try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Initiate a sandbox fiat deposit
     */
    public function initiateDeposit(Request $request, FiatDepositInstruction $fiatDepositInstruction)
    {
        // Ensure the instruction belongs to the authenticated user
        if ($fiatDepositInstruction->user_id !== auth()->id()) {
            abort(403);
        }

        // Load the profile relationship
        $fiatDepositInstruction->load('profile');

        // Validate that we have required data
        if (!$fiatDepositInstruction->memo_id) {
            return back()->withErrors(['error' => 'Cannot initiate deposit: memo_id is missing from this instruction.']);
        }

        if (!$fiatDepositInstruction->fiat_network_instructions) {
            return back()->withErrors(['error' => 'Cannot initiate deposit: fiat_network_instructions are missing from this instruction.']);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'asset' => 'nullable|string|in:USD',
        ]);

        try {
            // Prepare data for Paxos API
            $paxosData = [
                'amount' => number_format($validated['amount'], 2, '.', ''),
                'asset' => $validated['asset'] ?? 'USD',
                'memo_id' => $fiatDepositInstruction->memo_id,
                'fiat_network_instructions' => $fiatDepositInstruction->fiat_network_instructions,
            ];

            // Add fiat_account_owner if available
            if ($fiatDepositInstruction->fiat_account_owner) {
                $paxosData['fiat_account_owner'] = $fiatDepositInstruction->fiat_account_owner;
            }

            // Call Paxos API - this will throw an exception if it fails
            $paxosResponse = $this->paxosService->createSandboxFiatDeposit($paxosData);

            // Don't save deposits locally - fetch them from Paxos transfers endpoint instead
            // The deposit was created on Paxos, we'll query it from the transfers API
            Log::info('Paxos sandbox deposit created successfully', [
                'memo_id' => $fiatDepositInstruction->memo_id,
                'amount' => $validated['amount'],
                'profile_id' => $fiatDepositInstruction->profile->paxos_profile_id ?? null,
                'response' => $paxosResponse,
            ]);

            return redirect()->route('borrower.fiat-deposit-instructions.show', $fiatDepositInstruction)
                ->with('success', 'Sandbox fiat deposit initiated successfully! The deposit will appear in your transfers.');

        } catch (\Exception $e) {
            Log::error('Failed to initiate sandbox fiat deposit', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'instruction_id' => $fiatDepositInstruction->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to initiate sandbox fiat deposit. Please try again. Error: ' . $e->getMessage()]);
        }
    }
}
