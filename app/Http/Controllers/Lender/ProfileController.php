<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\PaxosService;

class ProfileController extends Controller
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
        $profiles = auth()->user()->profiles()->with('account.identity')->latest()->get();

        return view('lender.profiles.index', compact('profiles'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        // Ensure the profile belongs to the authenticated user
        if ($profile->user_id !== auth()->id()) {
            abort(403);
        }

        $profile->load('account.identity', 'fiatDepositInstructions', 'depositAddresses');

        // Fetch transfers for this profile from Paxos API
        $transfers = [];
        $transfersError = null;

        if ($profile->paxos_profile_id) {
            try {
                $transfers = $this->paxosService->getTransfers([$profile->paxos_profile_id]);
                if (! is_array($transfers)) {
                    $transfers = [];
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to fetch transfers for profile', [
                    'error' => $e->getMessage(),
                    'profile_id' => $profile->id,
                    'paxos_profile_id' => $profile->paxos_profile_id,
                ]);
                $transfersError = 'Failed to fetch transfers: '.$e->getMessage();
            }
        }

        return view('lender.profiles.show', compact('profile', 'transfers', 'transfersError'));
    }
}
