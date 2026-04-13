@extends('layouts.app')

@section('title', 'Create Fiat Deposit Instruction')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Fiat Deposit Instruction</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-4 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-blue-800">
                <strong>Profile:</strong> {{ $profile->paxos_profile_id }}
            </p>
        </div>

        <form action="{{ route('borrower.profiles.fiat-deposit-instructions.store', $profile) }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">There were some errors with your submission:</h3>
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

            <div class="space-y-6">
                <div>
                    <label for="fiat_network" class="block text-sm font-medium text-gray-700">Fiat Network *</label>
                    <select name="fiat_network" id="fiat_network" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select a network</option>
                        <option value="WIRE" {{ old('fiat_network') == 'WIRE' ? 'selected' : '' }}>WIRE</option>
                        <option value="CUBIX" {{ old('fiat_network') == 'CUBIX' ? 'selected' : '' }}>CUBIX</option>
                        <option value="DBS_ACT" {{ old('fiat_network') == 'DBS_ACT' ? 'selected' : '' }}>DBS ACT</option>
                        <option value="SCB" {{ old('fiat_network') == 'SCB' ? 'selected' : '' }}>SCB</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Select the fiat network for this deposit instruction</p>
                </div>

                <div id="routing_number_type_section" style="display: none;">
                    <label for="routing_number_type" class="block text-sm font-medium text-gray-700">Routing Number Type (Optional)</label>
                    <select name="routing_number_type" id="routing_number_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select type</option>
                        <option value="ABA" {{ old('routing_number_type') == 'ABA' ? 'selected' : '' }}>ABA</option>
                        <option value="SWIFT" {{ old('routing_number_type') == 'SWIFT' ? 'selected' : '' }}>SWIFT</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Optional for WIRE network</p>
                </div>

                <div>
                    <label for="ref_id" class="block text-sm font-medium text-gray-700">Reference ID (Optional)</label>
                    <input type="text" name="ref_id" id="ref_id" value="{{ old('ref_id') }}" placeholder="Leave empty to auto-generate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-sm text-gray-500">Client-specified ID for idempotence. Leave empty to auto-generate a UUID.</p>
                </div>
            </div>

            <script>
                // Show/hide routing_number_type based on fiat_network selection
                document.getElementById('fiat_network').addEventListener('change', function() {
                    const routingSection = document.getElementById('routing_number_type_section');
                    if (this.value === 'WIRE') {
                        routingSection.style.display = 'block';
                    } else {
                        routingSection.style.display = 'none';
                    }
                });
            </script>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('borrower.profiles.show', $profile) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-4">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Create Fiat Deposit Instruction
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
