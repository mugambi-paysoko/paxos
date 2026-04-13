<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\PaxosService;
use Illuminate\Http\Request;

class FiatDepositController extends Controller
{
    protected PaxosService $paxosService;

    public function __construct(PaxosService $paxosService)
    {
        $this->middleware('auth');
        $this->paxosService = $paxosService;
    }

    /**
     * Display a listing of transfers from Paxos API.
     */
    public function index()
    {
        try {
            // Get all profiles for the user
            $profiles = auth()->user()->profiles()->whereNotNull('paxos_profile_id')->get();
            
            if ($profiles->isEmpty()) {
                return view('lender.fiat-deposits.index', [
                    'transfers' => [],
                    'error' => 'No profiles found. Please create a profile first.'
                ]);
            }

            // Get profile IDs
            $profileIds = $profiles->pluck('paxos_profile_id')->toArray();
            
            // Fetch transfers from Paxos API
            $transfers = $this->paxosService->getTransfers($profileIds);

            // Log the transfers for debugging
            \Illuminate\Support\Facades\Log::info('Fetched transfers for user', [
                'user_id' => auth()->id(),
                'profile_ids' => $profileIds,
                'transfers_count' => is_array($transfers) ? count($transfers) : 0,
            ]);

            return view('lender.fiat-deposits.index', [
                'transfers' => is_array($transfers) ? $transfers : [],
                'profiles' => $profiles,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch transfers', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return view('lender.fiat-deposits.index', [
                'transfers' => [],
                'error' => 'Failed to fetch transfers from Paxos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified transfer.
     * Note: This now shows a transfer from Paxos API, not a local deposit
     */
    public function show($transferId)
    {
        try {
            // Get all profiles for the user
            $profiles = auth()->user()->profiles()->whereNotNull('paxos_profile_id')->get();
            
            if ($profiles->isEmpty()) {
                return redirect()->route('lender.fiat-deposits.index')
                    ->withErrors(['error' => 'No profiles found.']);
            }

            // Get profile IDs
            $profileIds = $profiles->pluck('paxos_profile_id')->toArray();
            
            // Fetch transfers from Paxos API
            $transfers = $this->paxosService->getTransfers($profileIds);
            
            // Find the specific transfer
            $transfer = null;
            if (is_array($transfers)) {
                foreach ($transfers as $t) {
                    if (isset($t['id']) && $t['id'] === $transferId) {
                        $transfer = $t;
                        break;
                    }
                }
            }

            if (!$transfer) {
                return redirect()->route('lender.fiat-deposits.index')
                    ->withErrors(['error' => 'Transfer not found.']);
            }

            return view('lender.fiat-deposits.show', compact('transfer'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch transfer', [
                'error' => $e->getMessage(),
                'transfer_id' => $transferId,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('lender.fiat-deposits.index')
                ->withErrors(['error' => 'Failed to fetch transfer: ' . $e->getMessage()]);
        }
    }
}
