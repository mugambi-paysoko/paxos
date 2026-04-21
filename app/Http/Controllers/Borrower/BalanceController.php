<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Services\BorrowerBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BalanceController extends Controller
{
    /**
     * Balances page: per-asset table (aggregated across your Paxos profiles).
     */
    public function index(BorrowerBalanceService $borrowerBalanceService): View
    {
        $balanceData = $borrowerBalanceService->summarizeForUser(auth()->user());

        return view('borrower.balances.index', compact('balanceData'));
    }

    /**
     * JSON summary (same payload as used server-side for the dashboard card and balances page).
     */
    public function json(BorrowerBalanceService $borrowerBalanceService): JsonResponse
    {
        $data = $borrowerBalanceService->summarizeForUser(auth()->user());

        return response()->json($data);
    }
}
