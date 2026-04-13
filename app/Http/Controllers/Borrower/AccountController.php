<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\PaxosService;
use Illuminate\Http\Request;

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
        return view('borrower.accounts.index', compact('accounts'));
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

        return view('borrower.accounts.show', compact('account'));
    }
}
