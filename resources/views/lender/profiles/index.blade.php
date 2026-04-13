@extends('layouts.app')

@section('title', 'Profiles')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Profiles</h1>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($profiles as $profile)
            <li>
                <a href="{{ route('lender.profiles.show', $profile) }}" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    Profile for {{ $profile->account->type }} Account
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    {{ $profile->account->identity->first_name }} {{ $profile->account->identity->last_name }}
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    Created {{ $profile->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            @empty
            <li class="px-4 py-8 text-center text-gray-500">
                No profiles found. Profiles are created automatically when creating accounts with the "Create profile" option enabled.
            </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
