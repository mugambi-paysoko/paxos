@extends('layouts.app')

@section('title', 'Transfer Details')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Transfer Details</h1>
        <a href="{{ route('lender.fiat-deposits.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            Back to Transfers
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                @if(isset($transfer['id']))
                <div>
                    <dt class="text-sm font-medium text-gray-500">Transfer ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer['id'] }}</dd>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ ($transfer['status'] ?? 'PENDING') === 'COMPLETED' ? 'bg-green-100 text-green-800' : (($transfer['status'] ?? 'PENDING') === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ $transfer['status'] ?? 'PENDING' }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $transfer['type'] ?? 'N/A' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-bold">{{ number_format($transfer['amount'] ?? 0, 2) }} {{ $transfer['asset'] ?? 'USD' }}</dd>
                </div>

                @if(isset($transfer['memo_id']))
                <div>
                    <dt class="text-sm font-medium text-gray-500">Memo ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer['memo_id'] }}</dd>
                </div>
                @endif

                @if(isset($transfer['profile_id']))
                <div>
                    <dt class="text-sm font-medium text-gray-500">Profile ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer['profile_id'] }}</dd>
                </div>
                @endif

                @if(isset($transfer['created_at']))
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($transfer['created_at'])->format('Y-m-d H:i:s') }}</dd>
                </div>
                @endif

                @if(isset($transfer['updated_at']))
                <div>
                    <dt class="text-sm font-medium text-gray-500">Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($transfer['updated_at'])->format('Y-m-d H:i:s') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        @if(isset($transfer) && count($transfer) > 0)
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Full Transfer Data</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-sm text-gray-800 overflow-x-auto">{{ json_encode($transfer, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
