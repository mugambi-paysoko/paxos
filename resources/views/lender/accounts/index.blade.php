@extends('layouts.app')

@section('title', 'Accounts')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Accounts</h1>
        <a href="{{ route('lender.accounts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
            Create Account
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($accounts as $account)
            <li>
                <a href="{{ route('lender.accounts.show', $account) }}" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    {{ $account->type }} Account
                                </p>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                @if($account->profile)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Has Profile
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    {{ $account->identity->first_name }} {{ $account->identity->last_name }}
                                </p>
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    {{ $account->description ?? 'No description' }}
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    Created {{ $account->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            @empty
            <li class="px-4 py-8 text-center text-gray-500">
                No accounts found. <a href="{{ route('lender.accounts.create') }}" class="text-indigo-600 hover:text-indigo-500">Create one</a>
            </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
