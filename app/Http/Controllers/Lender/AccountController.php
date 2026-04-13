<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Identity;
use App\Models\Profile;
use App\Services\PaxosService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
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
        $accounts = auth()->user()->accounts()->with('identity', 'profile')->latest()->get();
        return view('lender.accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $identities = auth()->user()->identities()
            ->where('id_verification_status', 'APPROVED')
            ->where('sanctions_verification_status', 'APPROVED')
            ->get();
        
        return view('lender.accounts.create', compact('identities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'identity_id' => 'required|exists:identities,id',
            'type' => 'required|in:BROKERAGE,CUSTODY,OTHER',
            'description' => 'nullable|string|max:500',
            'create_profile' => 'boolean',
        ]);

        // Ensure identity belongs to user
        $identity = Identity::where('id', $validated['identity_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Generate unique ref_id
        $refId = Str::uuid()->toString();

        // Prepare data for Paxos API
        $paxosData = [
            'create_profile' => $request->has('create_profile'),
            'account' => [
                'identity_id' => $identity->paxos_identity_id,
                'ref_id' => $refId,
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
            ],
        ];

        try {
            // Call Paxos API - this will throw an exception if it fails
            $paxosResponse = $this->paxosService->createAccount($paxosData);

            // Only save to database after successful Paxos API call
            $account = Account::create([
                'user_id' => auth()->id(),
                'identity_id' => $identity->id,
                'paxos_account_id' => $paxosResponse['account']['id'],
                'ref_id' => $refId,
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
            ]);

            // Create profile if requested and returned
            if ($request->has('create_profile') && isset($paxosResponse['profile']['id'])) {
                Profile::create([
                    'user_id' => auth()->id(),
                    'account_id' => $account->id,
                    'paxos_profile_id' => $paxosResponse['profile']['id'],
                ]);
            }

            return redirect()->route('lender.accounts.show', $account)
                ->with('success', 'Account created successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create account', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'identity_id' => $identity->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create account in Paxos. Please try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        // Ensure the account belongs to the authenticated user
        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        $account->load('identity', 'profile');
        return view('lender.accounts.show', compact('account'));
    }
}
