@extends('layouts.app')

@section('title', 'Identity Details')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Identity Details</h1>
        <div>
            @if($identity->id_verification_status !== 'APPROVED')
            <form action="{{ route('borrower.identities.approve', $identity) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Approve (Sandbox)
                </button>
            </form>
            @endif
            <a href="{{ route('borrower.identities.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2">
                Back
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <!-- Person Identity Display -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">First Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->first_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->last_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->date_of_birth ? $identity->date_of_birth->format('Y-m-d') : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->nationality }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->phone_number ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Address</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->address1 }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">City</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->city }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Province/State</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->province ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Zip Code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->zip_code ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Country</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $identity->address_country }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Verification Status</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID Verification Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $identity->id_verification_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $identity->id_verification_status }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Sanctions Verification Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $identity->sanctions_verification_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $identity->sanctions_verification_status }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Paxos Identity ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $identity->paxos_identity_id ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Reference ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $identity->ref_id }}</dd>
                </div>
            </dl>
        </div>

        @if($identity->accounts && $identity->accounts->count() > 0)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Accounts</h3>
            <div class="space-y-4">
                @foreach($identity->accounts as $account)
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $account->type }}</div>
                            @if($account->description)
                            <div class="text-sm text-gray-600 mt-1">{{ $account->description }}</div>
                            @endif
                            @if($account->profile)
                            <div class="text-sm text-indigo-600 mt-1">
                                Profile: {{ $account->profile->paxos_profile_id }}
                            </div>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500">
                            Created {{ $account->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($identity->documents && $identity->documents->count() > 0)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Uploaded Documents</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paxos Document ID</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($identity->documents as $document)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $document->file_name }}
                                <span class="text-xs text-gray-500 ml-2">({{ number_format($document->file_size / 1024, 2) }} KB)</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($document->upload_status === 'uploaded')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Uploaded
                                    </span>
                                @elseif($document->upload_status === 'failed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                    @if($document->error_message)
                                        <div class="text-xs text-red-600 mt-1">{{ Str::limit($document->error_message, 50) }}</div>
                                    @endif
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($document->uploaded_at)
                                    {{ $document->uploaded_at->format('Y-m-d H:i:s') }}
                                @else
                                    <span class="text-gray-400">Not uploaded</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $document->paxos_document_id ?? 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Uploaded Documents</h3>
            <p class="text-sm text-gray-500">No documents have been uploaded for this identity yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection
