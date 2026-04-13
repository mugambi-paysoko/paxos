@extends('layouts.app')

@section('title', 'Fiat Deposit Instruction Details')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Fiat Deposit Instruction Details</h1>
        <div class="flex gap-4">
            @if($fiatDepositInstruction->memo_id && $fiatDepositInstruction->fiat_network_instructions)
            <button onclick="document.getElementById('depositModal').classList.remove('hidden')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Initiate Deposit
            </button>
            @endif
            <a href="{{ route('lender.fiat-deposit-instructions.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Back to Instructions
            </a>
        </div>
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

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">There were some errors:</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Paxos Deposit Instruction ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatDepositInstruction->paxos_deposit_instruction_id }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $fiatDepositInstruction->status === 'VALID' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $fiatDepositInstruction->status ?? 'N/A' }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Fiat Network</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fiatDepositInstruction->fiat_network }}</dd>
                </div>

                @if($fiatDepositInstruction->routing_number_type)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Routing Number Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fiatDepositInstruction->routing_number_type }}</dd>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Profile ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatDepositInstruction->paxos_profile_id }}</dd>
                </div>

                @if($fiatDepositInstruction->memo_id)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Memo ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono font-bold text-indigo-600">{{ $fiatDepositInstruction->memo_id }}</dd>
                    <p class="mt-1 text-xs text-gray-500">Use this memo ID when initiating a wire transfer to Paxos</p>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Reference ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatDepositInstruction->ref_id }}</dd>
                </div>

                @if($fiatDepositInstruction->paxos_identity_id)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Paxos Identity ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatDepositInstruction->paxos_identity_id }}</dd>
                </div>
                @endif

                @if($fiatDepositInstruction->paxos_account_id)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Paxos Account ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatDepositInstruction->paxos_account_id }}</dd>
                </div>
                @endif

                @if($fiatDepositInstruction->paxos_created_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created at (Paxos)</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fiatDepositInstruction->paxos_created_at->format('Y-m-d H:i:s') }}</dd>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fiatDepositInstruction->created_at->format('Y-m-d H:i:s') }}</dd>
                </div>
            </dl>
        </div>

        @if($fiatDepositInstruction->profile)
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Profile ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatDepositInstruction->profile->paxos_profile_id }}</dd>
                </div>
                @if($fiatDepositInstruction->profile->account)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fiatDepositInstruction->profile->account->type }}</dd>
                </div>
                @if($fiatDepositInstruction->profile->account->identity)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Identity</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $fiatDepositInstruction->profile->account->identity->first_name }} 
                        {{ $fiatDepositInstruction->profile->account->identity->last_name }}
                    </dd>
                </div>
                @endif
                @endif
            </dl>
        </div>
        @endif

        @if($fiatDepositInstruction->fiat_account_owner)
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Owner</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                @if(isset($fiatDepositInstruction->fiat_account_owner['person_details']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">First Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fiatDepositInstruction->fiat_account_owner['person_details']['first_name'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fiatDepositInstruction->fiat_account_owner['person_details']['last_name'] ?? 'N/A' }}</dd>
                    </div>
                @endif
                @if(isset($fiatDepositInstruction->fiat_account_owner['institution_details']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Institution Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fiatDepositInstruction->fiat_account_owner['institution_details']['name'] ?? 'N/A' }}</dd>
                    </div>
                @endif
            </dl>
        </div>
        @endif

        @if($fiatDepositInstruction->fiat_network_instructions)
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Network Instructions</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-sm text-gray-800 overflow-x-auto">{{ json_encode($fiatDepositInstruction->fiat_network_instructions, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif

        @if($fiatDepositInstruction->metadata)
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Metadata</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-sm text-gray-800 overflow-x-auto">{{ json_encode($fiatDepositInstruction->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif

        @if($fiatDepositInstruction->fiatDeposits && $fiatDepositInstruction->fiatDeposits->count() > 0)
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Related Deposits</h3>
                <a href="{{ route('lender.fiat-deposits.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                    View All Deposits →
                </a>
            </div>
            <ul class="divide-y divide-gray-200">
                @foreach($fiatDepositInstruction->fiatDeposits as $deposit)
                <li class="py-3">
                    <a href="{{ route('lender.fiat-deposits.show', $deposit) }}" class="block hover:bg-gray-50 -mx-4 px-4 py-2 rounded">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-600">
                                    {{ $deposit->asset }} {{ number_format($deposit->amount, 2) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Created {{ $deposit->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $deposit->status === 'COMPLETED' ? 'bg-green-100 text-green-800' : ($deposit->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $deposit->status ?? 'PENDING' }}
                            </span>
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>

<!-- Deposit Modal -->
@if($fiatDepositInstruction->memo_id && $fiatDepositInstruction->fiat_network_instructions)
<div id="depositModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Initiate Sandbox Fiat Deposit</h3>
            <form action="{{ route('lender.fiat-deposit-instructions.initiate-deposit', $fiatDepositInstruction) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount *</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ old('amount', '1000.00') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Enter the deposit amount</p>
                </div>
                <div class="mb-4">
                    <label for="asset" class="block text-sm font-medium text-gray-700">Asset</label>
                    <select name="asset" id="asset" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="USD" selected>USD</option>
                    </select>
                </div>
                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs text-blue-800">
                        <strong>Memo ID:</strong> <span class="font-mono">{{ $fiatDepositInstruction->memo_id }}</span><br>
                        <strong>Network:</strong> {{ $fiatDepositInstruction->fiat_network }}
                    </p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('depositModal').classList.add('hidden')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Initiate Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
