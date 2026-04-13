@extends('layouts.app')

@section('title', 'Choose Institution Type')

@section('content')
<div class="py-6">
    <div class="mb-4">
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">1</span>
            <span>Choose Identity Type</span>
            <span class="mx-2">→</span>
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">2</span>
            <span class="font-semibold text-gray-900">Choose Institution Type</span>
            <span class="mx-2">→</span>
            <span class="bg-gray-300 text-gray-600 rounded-full w-6 h-6 flex items-center justify-center mr-2">3</span>
            <span>Add Members</span>
            <span class="mx-2">→</span>
            <span class="bg-gray-300 text-gray-600 rounded-full w-6 h-6 flex items-center justify-center mr-2">4</span>
            <span>Institution Details</span>
        </div>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Choose Institution Type</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <p class="text-gray-600 mb-6">Select the legal structure of your institution. This will determine which member roles are available.</p>

        <form action="{{ route('lender.identities.store-institution-type') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 cursor-pointer" onclick="selectType('CORPORATION')">
                    <input type="radio" name="institution_type" id="type_corporation" value="CORPORATION" class="hidden" required>
                    <label for="type_corporation" class="cursor-pointer">
                        <div class="text-center">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Corporation</h3>
                            <p class="text-sm text-gray-600">A legal entity separate from its owners</p>
                            <p class="text-xs text-gray-500 mt-2">Roles: Beneficial Owner, Authorized User, Management Control Person</p>
                        </div>
                    </label>
                </div>

                <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 cursor-pointer" onclick="selectType('LLC')">
                    <input type="radio" name="institution_type" id="type_llc" value="LLC" class="hidden" required>
                    <label for="type_llc" class="cursor-pointer">
                        <div class="text-center">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">LLC</h3>
                            <p class="text-sm text-gray-600">Limited Liability Company</p>
                            <p class="text-xs text-gray-500 mt-2">Roles: Beneficial Owner, Authorized User, Management Control Person</p>
                        </div>
                    </label>
                </div>

                <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 cursor-pointer" onclick="selectType('PARTNERSHIP')">
                    <input type="radio" name="institution_type" id="type_partnership" value="PARTNERSHIP" class="hidden" required>
                    <label for="type_partnership" class="cursor-pointer">
                        <div class="text-center">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Partnership</h3>
                            <p class="text-sm text-gray-600">Business relationship between individuals</p>
                            <p class="text-xs text-gray-500 mt-2">Roles: Beneficial Owner, Authorized User, Management Control Person</p>
                        </div>
                    </label>
                </div>

                <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-indigo-500 cursor-pointer" onclick="selectType('TRUST')">
                    <input type="radio" name="institution_type" id="type_trust" value="TRUST" class="hidden" required>
                    <label for="type_trust" class="cursor-pointer">
                        <div class="text-center">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Trust</h3>
                            <p class="text-sm text-gray-600">Legal entity to hold and manage assets</p>
                            <p class="text-xs text-gray-500 mt-2">Roles: Trustee, Grantor, Beneficiary</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('lender.identities.create') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-4">
                    Back
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
