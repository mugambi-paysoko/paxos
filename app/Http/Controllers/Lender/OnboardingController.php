<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Services\PaxosOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    protected PaxosOnboardingService $onboardingService;

    public function __construct(PaxosOnboardingService $onboardingService)
    {
        $this->middleware('auth');
        $this->onboardingService = $onboardingService;
    }

    /**
     * Show the onboarding form
     * For lenders, redirect directly to institution identity creation
     */
    public function create()
    {
        $user = Auth::user();
        
        // Only allow lenders who don't have a Paxos identity
        if (!$user->isLender()) {
            return redirect()->route('dashboard');
        }
        
        if ($user->hasPaxosIdentity()) {
            return redirect()->route('dashboard')
                ->with('info', 'You already have a Paxos identity set up.');
        }
        
        // Lenders should only create institution identities
        // Redirect to institution identity creation flow
        return redirect()->route('lender.identities.create', ['step' => 2, 'identity_type' => 'INSTITUTION'])
            ->with('info', 'Please set up your institution identity to get started.');
    }

    /**
     * Process the onboarding
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only allow lenders who don't have a Paxos identity
        if (!$user->isLender()) {
            return redirect()->route('dashboard');
        }
        
        if ($user->hasPaxosIdentity()) {
            return redirect()->route('dashboard')
                ->with('info', 'You already have a Paxos identity set up.');
        }

        $validated = $request->validate([
            // Identity fields
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'nationality' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:255',
            'cip_id' => 'nullable|string|max:255',
            'cip_id_type' => 'nullable|string|max:255',
            'cip_id_country' => 'nullable|string|max:255',
            'address_country' => 'required|string|max:255',
            'address1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:255',
            
            // Account fields
            'account_type' => 'nullable|in:BROKERAGE,CUSTODY,OTHER',
            'account_description' => 'nullable|string|max:500',
        ]);

        try {
            // Prepare identity data
            $identityData = [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'nationality' => $validated['nationality'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'cip_id' => $validated['cip_id'] ?? null,
                'cip_id_type' => $validated['cip_id_type'] ?? null,
                'cip_id_country' => $validated['cip_id_country'] ?? null,
                'address_country' => $validated['address_country'],
                'address1' => $validated['address1'],
                'city' => $validated['city'],
                'province' => $validated['province'] ?? null,
                'zip_code' => $validated['zip_code'] ?? null,
            ];

            // Prepare account data
            $accountData = [
                'type' => $validated['account_type'] ?? 'BROKERAGE',
                'description' => $validated['account_description'] ?? 'Primary account for ' . $user->name,
                'create_profile' => true,
            ];

            // Create complete onboarding
            $result = $this->onboardingService->createCompleteOnboarding($user, $identityData, $accountData);

            return redirect()->route('dashboard')
                ->with('success', 'Paxos identity, account, and profile created successfully!');
                
        } catch (\Exception $e) {
            Log::error('Onboarding failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create Paxos identity. Please try again. Error: ' . $e->getMessage()]);
        }
    }
}
