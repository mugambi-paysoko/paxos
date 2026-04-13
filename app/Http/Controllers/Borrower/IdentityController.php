<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Identity;
use App\Models\Profile;
use App\Services\PaxosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IdentityController extends Controller
{
    protected PaxosService $paxosService;

    public function __construct(PaxosService $paxosService)
    {
        $this->middleware('auth');
        $this->paxosService = $paxosService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $identities = auth()->user()->identities()->latest()->get();
        return view('borrower.identities.index', compact('identities'));
    }

    /**
     * Show the form for creating a personal identity.
     */
    public function create()
    {
        return view('borrower.identities.create-person');
    }

    /**
     * Store a newly created personal identity in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'nationality' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:255',
            'cip_id' => 'nullable|string|max:255',
            'cip_id_type' => 'nullable|string|max:255',
            'cip_id_country' => 'nullable|string|max:255',
            'address_country' => 'required|string|max:255',
            'address1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:255',
            // Account and Profile creation fields
            'create_account' => 'nullable|boolean',
            'account_type' => 'nullable|in:BROKERAGE,CUSTODY,OTHER',
            'account_description' => 'nullable|string|max:500',
            'create_profile' => 'nullable|boolean',
            // Document uploads (optional)
            'proof_of_identity' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
            'proof_of_residency' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'proof_of_ssn' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Generate unique ref_id
        $refId = Str::uuid()->toString();

        // Prepare data for Paxos API
        $paxosData = [
            'person_details' => [
                'verifier_type' => 'PAXOS',
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'address' => [
                    'country' => $validated['address_country'],
                    'address1' => $validated['address1'],
                    'city' => $validated['city'],
                    'province' => $validated['province'] ?? null,
                    'zip_code' => $validated['zip_code'] ?? null,
                ],
                'nationality' => $validated['nationality'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
            ],
            'ref_id' => $refId,
        ];

        if (!empty($validated['cip_id'])) {
            $paxosData['person_details']['cip_id'] = $validated['cip_id'];
            $paxosData['person_details']['cip_id_type'] = $validated['cip_id_type'] ?? 'SSN';
            $paxosData['person_details']['cip_id_country'] = $validated['cip_id_country'] ?? 'USA';
        }

        try {
            // Call Paxos API - this will throw an exception if it fails
            $paxosResponse = $this->paxosService->createIdentity($paxosData);

            // Only save to database after successful Paxos API call
            $identity = Identity::create([
                'user_id' => auth()->id(),
                'paxos_identity_id' => $paxosResponse['id'],
                'ref_id' => $refId,
                'identity_type' => 'PERSON',
                'verifier_type' => 'PAXOS',
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'nationality' => $validated['nationality'],
                'cip_id' => $validated['cip_id'] ?? null,
                'cip_id_type' => $validated['cip_id_type'] ?? null,
                'cip_id_country' => $validated['cip_id_country'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'email' => $validated['email'],
                'address_country' => $validated['address_country'],
                'address1' => $validated['address1'],
                'city' => $validated['city'],
                'province' => $validated['province'] ?? null,
                'zip_code' => $validated['zip_code'] ?? null,
                'id_verification_status' => $paxosResponse['id_verification_status'] ?? 'PENDING',
                'sanctions_verification_status' => $paxosResponse['sanctions_verification_status'] ?? 'PENDING',
            ]);

            // Handle document uploads
            $this->storeDocuments($identity, $request);

            // Create account and profile if requested
            if ($request->has('create_account')) {
                $accountType = $validated['account_type'] ?? 'BROKERAGE';
                $accountRefId = Str::uuid()->toString();
                // Checkbox: if present in request, it's checked (true), otherwise default to true
                $createProfile = $request->has('create_profile') ? true : true;

                $accountData = [
                    'create_profile' => $createProfile,
                    'account' => [
                        'identity_id' => $identity->paxos_identity_id,
                        'ref_id' => $accountRefId,
                        'type' => $accountType,
                        'description' => $validated['account_description'] ?? 'Primary account for ' . auth()->user()->name,
                    ],
                ];

                try {
                    $accountResponse = $this->paxosService->createAccount($accountData);

                    // Save account
                    $account = Account::create([
                        'user_id' => auth()->id(),
                        'identity_id' => $identity->id,
                        'paxos_account_id' => $accountResponse['account']['id'],
                        'ref_id' => $accountRefId,
                        'type' => $accountType,
                        'description' => $validated['account_description'] ?? 'Primary account for ' . auth()->user()->name,
                    ]);

                    // Create profile if requested and returned
                    if ($createProfile && isset($accountResponse['profile']['id'])) {
                        Profile::create([
                            'user_id' => auth()->id(),
                            'account_id' => $account->id,
                            'paxos_profile_id' => $accountResponse['profile']['id'],
                        ]);
                    }

                    return redirect()->route('borrower.identities.index')
                        ->with('success', 'Identity, account, and profile created successfully!');
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to create account after identity creation', [
                        'identity_id' => $identity->id,
                        'error' => $e->getMessage(),
                    ]);

                    return redirect()->route('borrower.identities.index')
                        ->with('success', 'Identity created successfully, but account creation failed. You can create an account later.')
                        ->with('warning', 'Account creation error: ' . $e->getMessage());
                }
            }

            return redirect()->route('borrower.identities.index')
                ->with('success', 'Personal identity created successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create personal identity', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create identity in Paxos. Please try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Store documents for an identity
     */
    protected function storeDocuments(Identity $identity, Request $request): void
    {
        $documentTypes = [
            'proof_of_identity' => ['PROOF_OF_IDENTITY'],
            'proof_of_residency' => ['PROOF_OF_RESIDENCY'],
            'proof_of_ssn' => ['PROOF_OF_SSN'],
        ];

        foreach ($documentTypes as $fieldName => $paxosTypes) {
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $fileName = $file->getClientOriginalName();
                $filePath = $file->store("documents/{$identity->id}", 'private');

                try {
                    // Upload to Paxos
                    $uploadResponse = $this->paxosService->uploadDocument(
                        $identity->paxos_identity_id,
                        storage_path("app/private/{$filePath}"),
                        $fileName,
                        $paxosTypes
                    );

                    // Store document record
                    \App\Models\IdentityDocument::create([
                        'identity_id' => $identity->id,
                        'document_type' => $fieldName,
                        'paxos_document_type' => $paxosTypes[0],
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'upload_status' => 'uploaded',
                        'paxos_document_id' => $uploadResponse['file_id'] ?? null,
                        'uploaded_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to upload document', [
                        'identity_id' => $identity->id,
                        'document_type' => $fieldName,
                        'error' => $e->getMessage(),
                    ]);

                    // Store document record with failed status
                    \App\Models\IdentityDocument::create([
                        'identity_id' => $identity->id,
                        'document_type' => $fieldName,
                        'paxos_document_type' => $paxosTypes[0],
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'upload_status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Identity $identity)
    {
        // Ensure the identity belongs to the authenticated user
        if ($identity->user_id !== auth()->id()) {
            abort(403);
        }

        // Fetch latest status from Paxos API and update local database
        if ($identity->paxos_identity_id) {
            try {
                $paxosResponse = $this->paxosService->getIdentity($identity->paxos_identity_id);

                // Update local database with latest status from Paxos
                $updateData = [];

                // For person identities, use summary_status if available
                if ($identity->identity_type === 'PERSON') {
                    if (isset($paxosResponse['summary_status'])) {
                        $updateData['id_verification_status'] = $paxosResponse['summary_status'];
                    } elseif (isset($paxosResponse['id_verification_status'])) {
                        $updateData['id_verification_status'] = $paxosResponse['id_verification_status'];
                    }

                    if (isset($paxosResponse['sanctions_verification_status'])) {
                        $updateData['sanctions_verification_status'] = $paxosResponse['sanctions_verification_status'];
                    } elseif (isset($paxosResponse['summary_status'])) {
                        $updateData['sanctions_verification_status'] = $paxosResponse['summary_status'];
                    }

                    // Update person details if changed
                    if (isset($paxosResponse['person_details'])) {
                        $personDetails = $paxosResponse['person_details'];
                        if (isset($personDetails['first_name']) && $identity->first_name !== $personDetails['first_name']) {
                            $updateData['first_name'] = $personDetails['first_name'];
                        }
                        if (isset($personDetails['last_name']) && $identity->last_name !== $personDetails['last_name']) {
                            $updateData['last_name'] = $personDetails['last_name'];
                        }
                        if (isset($personDetails['email']) && $identity->email !== $personDetails['email']) {
                            $updateData['email'] = $personDetails['email'];
                        }
                    }
                }

                // Update the identity if there are changes
                if (!empty($updateData)) {
                    $identity->update($updateData);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to fetch identity from Paxos', [
                    'identity_id' => $identity->id,
                    'paxos_identity_id' => $identity->paxos_identity_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Load relationships
        $identity->load('documents', 'accounts.profile');

        return view('borrower.identities.show', compact('identity'));
    }

    /**
     * Approve identity (sandbox)
     */
    public function approve(Identity $identity)
    {
        // Ensure the identity belongs to the authenticated user
        if ($identity->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$identity->paxos_identity_id) {
            return redirect()->back()
                ->withErrors(['error' => 'Identity does not have a Paxos ID. Cannot approve.']);
        }

        try {
            // Prepare approval data
            $approvalData = [
                'id_verification_status' => 'APPROVED',
                'sanctions_verification_status' => 'APPROVED',
                'additional_screening_status' => 'APPROVED',
                'document_verification_status' => 'APPROVED',
            ];

            // Call Paxos API to approve the identity
            $paxosResponse = $this->paxosService->approveIdentity($identity->paxos_identity_id, $approvalData);

            // Wait a bit and then fetch the updated identity to verify approval
            // The sandbox-status endpoint may take a moment to process
            $maxRetries = 3;
            $retryDelay = 2; // seconds
            $updatedIdentity = null;

            for ($i = 0; $i < $maxRetries; $i++) {
                sleep($retryDelay);
                $updatedIdentity = $this->paxosService->getIdentity($identity->paxos_identity_id);

                $currentStatus = $updatedIdentity['summary_status'] ?? $updatedIdentity['status'] ?? null;
                if ($currentStatus === 'APPROVED') {
                    break; // Status is approved, no need to retry
                }
            }

            // Log the updated identity status
            $currentStatus = $updatedIdentity['summary_status'] ?? $updatedIdentity['status'] ?? null;
            \Illuminate\Support\Facades\Log::info('Paxos identity status after approval', [
                'local_identity_id' => $identity->id,
                'paxos_identity_id' => $identity->paxos_identity_id,
                'summary_status' => $updatedIdentity['summary_status'] ?? null,
                'status' => $updatedIdentity['status'] ?? null,
                'id_verification_status' => $updatedIdentity['id_verification_status'] ?? null,
                'sanctions_verification_status' => $updatedIdentity['sanctions_verification_status'] ?? null,
                'current_status' => $currentStatus,
                'was_approved' => $currentStatus === 'APPROVED',
            ]);

            // Only update local database if status is actually APPROVED
            // Don't update if still PENDING (the sandbox-status endpoint may not work immediately)
            if ($currentStatus === 'APPROVED') {
                $updateData = [];

                if ($identity->identity_type === 'PERSON') {
                    // For person identities, use summary_status
                    $updateData['id_verification_status'] = 'APPROVED';
                    $updateData['sanctions_verification_status'] = 'APPROVED';
                } else {
                    // For institution identities, use status
                    $updateData['id_verification_status'] = 'APPROVED';
                    if (isset($updatedIdentity['sanctions_verification_status'])) {
                        $updateData['sanctions_verification_status'] = $updatedIdentity['sanctions_verification_status'];
                    } else {
                        $updateData['sanctions_verification_status'] = 'APPROVED';
                    }
                }

                $identity->update($updateData);
                \Illuminate\Support\Facades\Log::info('Updated local identity after approval - Status confirmed APPROVED', [
                    'local_identity_id' => $identity->id,
                    'updates' => $updateData,
                ]);
            } else {
                \Illuminate\Support\Facades\Log::warning('Approval request sent but status still PENDING - not updating local DB', [
                    'local_identity_id' => $identity->id,
                    'paxos_identity_id' => $identity->paxos_identity_id,
                    'current_status' => $currentStatus,
                    'paxos_response' => $updatedIdentity,
                    'note' => 'The sandbox-status endpoint may take time to process or may not be working as expected',
                ]);

                return redirect()->back()
                    ->with('warning', 'Approval request sent to Paxos, but status is still pending. Please check back in a moment or contact support if the issue persists.');
            }

            return redirect()->back()->with('success', 'Identity approved successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to approve identity', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'identity_id' => $identity->id,
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to approve identity in Paxos. Please try again. Error: ' . $e->getMessage()]);
        }
    }
}
