<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Lender\AccountController;
use App\Http\Controllers\Lender\FiatAccountController;
use App\Http\Controllers\Lender\FiatDepositController;
use App\Http\Controllers\Lender\FiatDepositInstructionController;
use App\Http\Controllers\Lender\IdentityController;
use App\Http\Controllers\Lender\OnboardingController;
use App\Http\Controllers\Lender\ProfileController;
use App\Http\Controllers\Borrower\IdentityController as BorrowerIdentityController;
use App\Http\Controllers\Borrower\ProfileController as BorrowerProfileController;
use App\Http\Controllers\Borrower\AccountController as BorrowerAccountController;
use App\Http\Controllers\Borrower\FiatDepositInstructionController as BorrowerFiatDepositInstructionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Lender routes
    Route::middleware('can:lender')->prefix('lender')->name('lender.')->group(function () {
        // Onboarding (must be first, before other routes)
        Route::get('onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
        Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

        // Identities
        Route::resource('identities', IdentityController::class);
        Route::post('identities/{identity}/approve', [IdentityController::class, 'approve'])->name('identities.approve');
        Route::post('identities/store-institution-type', [IdentityController::class, 'storeInstitutionType'])->name('identities.store-institution-type');
        Route::post('identities/store-member', [IdentityController::class, 'storeMember'])->name('identities.store-member');
        Route::post('identities/store-institution', [IdentityController::class, 'storeInstitution'])->name('identities.store-institution');

        // Accounts
        Route::resource('accounts', AccountController::class)->except(['edit', 'update', 'destroy']);

        // Profiles
        Route::resource('profiles', ProfileController::class)->only(['index', 'show']);

        // Fiat Accounts
        Route::resource('fiat-accounts', FiatAccountController::class)->except(['edit', 'update', 'destroy']);
        Route::post('fiat-accounts/{fiat_account}/refresh', [FiatAccountController::class, 'refresh'])->name('fiat-accounts.refresh');

        // Fiat Deposit Instructions
        Route::get('fiat-deposit-instructions', [FiatDepositInstructionController::class, 'index'])->name('fiat-deposit-instructions.index');
        Route::get('fiat-deposit-instructions/{fiat_deposit_instruction}', [FiatDepositInstructionController::class, 'show'])->name('fiat-deposit-instructions.show');
        Route::post('fiat-deposit-instructions/{fiat_deposit_instruction}/initiate-deposit', [FiatDepositInstructionController::class, 'initiateDeposit'])->name('fiat-deposit-instructions.initiate-deposit');
        Route::get('profiles/{profile}/fiat-deposit-instructions/create', [FiatDepositInstructionController::class, 'create'])->name('profiles.fiat-deposit-instructions.create');
        Route::post('profiles/{profile}/fiat-deposit-instructions', [FiatDepositInstructionController::class, 'store'])->name('profiles.fiat-deposit-instructions.store');

        // Fiat Deposits (transfers from Paxos API)
        Route::get('fiat-deposits', [FiatDepositController::class, 'index'])->name('fiat-deposits.index');
        Route::get('fiat-deposits/{transferId}', [FiatDepositController::class, 'show'])->name('fiat-deposits.show');
    });

    // Borrower routes
    Route::middleware('can:borrower')->prefix('borrower')->name('borrower.')->group(function () {
        // Identities (personal only)
        Route::get('identities', [BorrowerIdentityController::class, 'index'])->name('identities.index');
        Route::get('identities/create', [BorrowerIdentityController::class, 'create'])->name('identities.create');
        Route::post('identities', [BorrowerIdentityController::class, 'store'])->name('identities.store');
        Route::get('identities/{identity}', [BorrowerIdentityController::class, 'show'])->name('identities.show');
        Route::post('identities/{identity}/approve', [BorrowerIdentityController::class, 'approve'])->name('identities.approve');

        // Accounts
        Route::get('accounts', [BorrowerAccountController::class, 'index'])->name('accounts.index');
        Route::get('accounts/{account}', [BorrowerAccountController::class, 'show'])->name('accounts.show');

        // Profiles
        Route::get('profiles', [BorrowerProfileController::class, 'index'])->name('profiles.index');
        Route::get('profiles/{profile}', [BorrowerProfileController::class, 'show'])->name('profiles.show');
        
        // Fiat Deposit Instructions
        Route::get('profiles/{profile}/fiat-deposit-instructions/create', [BorrowerFiatDepositInstructionController::class, 'create'])->name('profiles.fiat-deposit-instructions.create');
        Route::post('profiles/{profile}/fiat-deposit-instructions', [BorrowerFiatDepositInstructionController::class, 'store'])->name('profiles.fiat-deposit-instructions.store');
        Route::get('fiat-deposit-instructions/{fiat_deposit_instruction}', [BorrowerFiatDepositInstructionController::class, 'show'])->name('fiat-deposit-instructions.show');
        Route::post('fiat-deposit-instructions/{fiat_deposit_instruction}/initiate-deposit', [BorrowerFiatDepositInstructionController::class, 'initiateDeposit'])->name('fiat-deposit-instructions.initiate-deposit');
    });
});
