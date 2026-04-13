@extends('layouts.app')

@section('title', 'Transfers')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Transfers</h1>

    @if(isset($error))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
        {{ $error }}
    </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($transfers ?? [] as $transfer)
            <li>
                <a href="{{ route('lender.fiat-deposits.show', $transfer['id'] ?? '') }}" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    {{ $transfer['asset'] ?? 'USD' }} {{ number_format($transfer['amount'] ?? 0, 2) }} 
                                    {{ isset($transfer['type']) && (str_contains($transfer['type'], 'DEPOSIT') || $transfer['type'] === 'DEPOSIT') ? 'Deposit' : 'Transfer' }}
                                </p>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ ($transfer['status'] ?? 'PENDING') === 'COMPLETED' ? 'bg-green-100 text-green-800' : (($transfer['status'] ?? 'PENDING') === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $transfer['status'] ?? 'PENDING' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    Type: {{ $transfer['type'] ?? 'N/A' }}
                                </p>
                                @if(isset($transfer['memo']) || isset($transfer['memo_id']))
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    Memo: <span class="font-mono ml-1">{{ $transfer['memo'] ?? $transfer['memo_id'] ?? 'N/A' }}</span>
                                </p>
                                @endif
                                @if(isset($transfer['id']))
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    ID: <span class="font-mono ml-1 text-xs">{{ substr($transfer['id'], 0, 8) }}...</span>
                                </p>
                                @endif
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                @if(isset($transfer['created_at']))
                                <p>
                                    Created {{ \Carbon\Carbon::parse($transfer['created_at'])->diffForHumans() }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            @empty
            <li class="px-4 py-8 text-center text-gray-500">
                No transfers found. Initiate a deposit from a deposit instruction page.
            </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
