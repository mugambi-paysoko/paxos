<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        $stats = [
            'identities_count' => $user->identities()->count(),
            'accounts_count' => $user->accounts()->count(),
            'profiles_count' => $user->profiles()->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
