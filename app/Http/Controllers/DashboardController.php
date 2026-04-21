<?php

namespace App\Http\Controllers;

use App\Services\BorrowerBalanceService;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(BorrowerBalanceService $borrowerBalanceService)
    {
        $user = auth()->user();

        $stats = [
            'identities_count' => $user->identities()->count(),
            'accounts_count' => $user->accounts()->count(),
            'profiles_count' => $user->profiles()->count(),
        ];

        $borrowerBalanceData = null;
        $borrowerBalanceCard = null;
        if ($user->can('borrower')) {
            $borrowerBalanceData = $borrowerBalanceService->summarizeForUser($user);
            $borrowerBalanceCard = $borrowerBalanceService->dashboardCardFromSummary($borrowerBalanceData);
        }

        return view('dashboard', compact('stats', 'borrowerBalanceData', 'borrowerBalanceCard'));
    }
}
