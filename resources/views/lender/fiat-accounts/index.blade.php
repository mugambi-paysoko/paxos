@extends('layouts.app')

@section('title', 'Fiat Accounts')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Fiat Accounts</h1>
        <a href="{{ route('lender.fiat-accounts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
            Create Fiat Account
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($fiatAccounts as $fiatAccount)
            <li>
                <a href="{{ route('lender.fiat-accounts.show', $fiatAccount) }}" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    Fiat Account
                                </p>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $fiatAccount->status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $fiatAccount->status }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    {{ $fiatAccount->identity->first_name }} {{ $fiatAccount->identity->last_name }}
                                </p>
                                @if($fiatAccount->fiat_account_owner && isset($fiatAccount->fiat_account_owner['person_details']))
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    {{ $fiatAccount->fiat_account_owner['person_details']['first_name'] ?? '' }} 
                                    {{ $fiatAccount->fiat_account_owner['person_details']['last_name'] ?? '' }}
                                </p>
                                @endif
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    Created {{ $fiatAccount->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            @empty
            <li class="px-4 py-8 text-center text-gray-500">
                No fiat accounts found. <a href="{{ route('lender.fiat-accounts.create') }}" class="text-indigo-600 hover:text-indigo-500">Create one</a>
            </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
