@extends('layouts.app')

@section('title', 'Create Identity')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Identity</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Step 1: Choose Identity Type</h2>
            <p class="text-gray-600 mb-6">Select whether you want to create a person identity or an institution identity.</p>
        </div>

        <form action="{{ route('lender.identities.create') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 cursor-pointer" onclick="selectType('PERSON')">
                    <input type="radio" name="identity_type" id="type_person" value="PERSON" class="hidden" required>
                    <label for="type_person" class="cursor-pointer">
                        <div class="text-center">
                            <div class="text-4xl mb-4">👤</div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Person Identity</h3>
                            <p class="text-gray-600">Create an identity for an individual person</p>
                        </div>
                    </label>
                </div>

                <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 cursor-pointer" onclick="selectType('INSTITUTION')">
                    <input type="radio" name="identity_type" id="type_institution" value="INSTITUTION" class="hidden" required>
                    <label for="type_institution" class="cursor-pointer">
                        <div class="text-center">
                            <div class="text-4xl mb-4">🏢</div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Institution Identity</h3>
                            <p class="text-gray-600">Create an identity for a business or organization</p>
                        </div>
                    </label>
                </div>
            </div>

            <input type="hidden" name="step" value="2">

            <div class="mt-6 flex justify-end">
                <a href="{{ route('lender.identities.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-4">
                    Cancel
                </a>
                <button type="submit" id="continueBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed" disabled>
                    Continue
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectType(type) {
    document.getElementById('type_' + type.toLowerCase()).checked = true;
    document.getElementById('continueBtn').disabled = false;
    document.getElementById('continueBtn').classList.remove('opacity-50', 'cursor-not-allowed');
    
    // Update visual selection
    document.querySelectorAll('.border-2').forEach(el => {
        el.classList.remove('border-indigo-500', 'bg-indigo-50');
        el.classList.add('border-gray-200');
    });
    event.currentTarget.classList.remove('border-gray-200');
    event.currentTarget.classList.add('border-indigo-500', 'bg-indigo-50');
}
</script>
@endsection
