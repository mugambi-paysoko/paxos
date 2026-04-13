@extends('layouts.app')

@section('title', 'Fiat Deposit Instructions')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Fiat Deposit Instructions</h1>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($instructions as $instruction)
            <li>
                <a href="{{ route('lender.fiat-deposit-instructions.show', $instruction) }}" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    {{ $instruction->fiat_network }} Deposit Instruction
                                </p>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $instruction->status === 'VALID' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $instruction->status ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    Profile: {{ $instruction->paxos_profile_id }}
                                </p>
                                @if($instruction->memo_id)
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                    Memo ID: <span class="font-mono ml-1">{{ $instruction->memo_id }}</span>
                                </p>
                                @endif
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    Created {{ $instruction->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            @empty
            <li class="px-4 py-8 text-center text-gray-500">
                No fiat deposit instructions found. Create one from a profile page.
            </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
