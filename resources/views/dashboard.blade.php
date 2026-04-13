@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Identities</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['identities_count'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Accounts</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['accounts_count'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Profiles</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['profiles_count'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @can('lender')
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('lender.identities.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded text-center">
                    Create Identity
                </a>
                <a href="{{ route('lender.accounts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded text-center">
                    Create Account
                </a>
                <a href="{{ route('lender.fiat-accounts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded text-center">
                    Create Fiat Account
                </a>
                <a href="{{ route('lender.fiat-deposits.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded text-center">
                    View Deposits
                </a>
                <a href="{{ route('lender.identities.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded text-center">
                    View All Identities
                </a>
            </div>
        </div>
        @endcan

        @can('borrower')
        @if(!auth()->user()->hasPaxosIdentity())
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Get Started</h2>
                    <p class="text-gray-600">Create your personal identity to begin using the platform.</p>
                </div>
                <a href="{{ route('borrower.identities.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded">
                    Create Identity
                </a>
            </div>
        </div>
        @else
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('borrower.identities.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded text-center">
                    View Identities
                </a>
                <a href="{{ route('borrower.accounts.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded text-center">
                    View Accounts
                </a>
                <a href="{{ route('borrower.profiles.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded text-center">
                    View Profiles
                </a>
                <a href="{{ route('borrower.identities.create') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded text-center">
                    Create Identity
                </a>
            </div>
        </div>
        @endif
        @endcan
    </div>
</div>
@endsection
