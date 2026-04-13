<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Identity;
use App\Models\Profile;
use App\Services\PaxosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        return view('lender.identities.index', compact('identities'));
    }

    /**
     * Show the form for creating a new resource.
     * For lenders, only institution identities are allowed
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $step = $request->get('step', 1);
        $identityType = $request->get('identity_type');

        // Lenders should only create institution identities
        // If no type specified and user is a lender, default to institution
        if (!$identityType && $user->isLender()) {
            $identityType = 'INSTITUTION';
            $step = 2; // Skip the type selection step
        }

        // If step 2 and type is person, show person form
        if ($step == 2 && $identityType == 'PERSON') {
            return view('lender.identities.create-person');
        }

        // If step 2 and type is institution, ask for institution type first
        if ($step == 2 && $identityType == 'INSTITUTION') {
            $institutionType = session('institution_type');
            if (!$institutionType) {
                return view('lender.identities.choose-institution-type');
            }
            $memberIndex = $request->get('member_index', 0);
            $members = $this->resolveInstitutionWizardMembers();
            return view('lender.identities.create-institution-member', compact('memberIndex', 'members', 'institutionType'));
        }

        // If step 3 and type is institution, show institution details form
        if ($step == 3 && $identityType == 'INSTITUTION') {
            $members = $this->resolveInstitutionWizardMembers();
            $institutionType = session('institution_type');
            if (empty($members)) {
                return redirect()->route('lender.identities.create', ['step' => 2, 'identity_type' => 'INSTITUTION'])
                    ->withErrors(['error' => 'Please add at least one institution member first.']);
            }
            return view('lender.identities.create-institution', compact('members', 'institutionType'));
        }

        // Step 1: Choose identity type (only shown if not a lender)
        return view('lender.identities.create');
    }

    /**
     * Store institution type selection
     */
    public function storeInstitutionType(Request $request)
    {
        $validated = $request->validate([
            'institution_type' => 'required|in:CORPORATION,PARTNERSHIP,LLC,TRUST,OTHER',
        ]);

        session()->forget('institution_members');
        Cache::forget($this->institutionWizardMembersCacheKey());
        session(['institution_type' => $validated['institution_type']]);

        return redirect()->route('lender.identities.create', ['step' => 2, 'identity_type' => 'INSTITUTION']);
    }

    /**
     * Store institution member (person identity for institution)
     */
    public function storeMember(Request $request)
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
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:BENEFICIAL_OWNER,AUTHORIZED_USER,MANAGEMENT_CONTROL_PERSON,ACCOUNT_OPENER,TRUSTEE,GRANTOR,BENEFICIARY',
        ]);

        try {
            $refId = Str::uuid()->toString();
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

            $paxosResponse = $this->paxosService->createIdentity($paxosData);

            // Save member identity to database
            $memberIdentity = Identity::create([
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

            // Store member in session
            $members = session('institution_members', []);
            $members[] = [
                'identity_id' => $paxosResponse['id'],
                'local_identity_id' => $memberIdentity->id,
                'roles' => $validated['roles'],
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            ];
            session(['institution_members' => $members]);
            Cache::put($this->institutionWizardMembersCacheKey(), $members, now()->addHours(24));

            $action = $request->get('action', 'add_another');
            if ($action === 'continue') {
                return redirect()->route('lender.identities.create', ['step' => 3, 'identity_type' => 'INSTITUTION']);
            }

            return redirect()->route('lender.identities.create', ['step' => 2, 'identity_type' => 'INSTITUTION', 'member_index' => count($members)])
                ->with('success', 'Institution member added successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create institution member', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create institution member. Please try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Store institution identity
     */
    public function storeInstitution(Request $request)
    {
        $members = $this->resolveInstitutionWizardMembers();

        if ($members === []) {
            $members = $this->validatedWizardMembersFromRequest($request);
        }

        if ($members === []) {
            return redirect()->route('lender.identities.create', ['step' => 2, 'identity_type' => 'INSTITUTION'])
                ->withErrors(['error' => 'Please add at least one institution member first.']);
        }

        session(['institution_members' => $members]);

        // Get institution type from session
        $institutionType = session('institution_type');
        if (!$institutionType) {
            return redirect()->route('lender.identities.create', ['step' => 2, 'identity_type' => 'INSTITUTION'])
                ->withErrors(['error' => 'Please select institution type first.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'institution_sub_type' => 'nullable|string',
            'cip_id' => 'required|string|max:255',
            'cip_id_type' => 'required|string|in:EIN,SSN,OTHER',
            'cip_id_country' => 'required|string|max:255',
            'business_address_country' => 'required|string|max:255',
            'business_address1' => 'required|string|max:255',
            'business_city' => 'required|string|max:255',
            'business_province' => 'nullable|string|max:255',
            'business_zip_code' => 'nullable|string|max:255',
            'govt_registration_date' => 'nullable|date',
            'incorporation_address_country' => 'nullable|string|max:255',
            'incorporation_address1' => 'nullable|string|max:255',
            'incorporation_city' => 'nullable|string|max:255',
            'incorporation_province' => 'nullable|string|max:255',
            'incorporation_zip_code' => 'nullable|string|max:255',
            'regulation_status' => 'nullable|in:US_REGULATED,INTL_REGULATED,NON_REGULATED',
            'trading_type' => 'nullable|in:PRIVATE,PUBLIC,PUBLICLY_TRADED_SUBSIDIARY',
            'listed_exchange' => 'nullable|string',
            'ticker_symbol' => 'nullable|string',
            'regulator_name' => 'nullable|string',
            'regulator_jurisdiction' => 'nullable|string',
            'regulator_register_number' => 'nullable|string',
            'parent_institution_name' => 'nullable|string',
            'business_description' => 'nullable|string',
            'create_account' => 'nullable|boolean',
            'account_type' => 'nullable|in:BROKERAGE,CUSTODY,OTHER',
        ]);

        // Add conditional validation based on regulation_status and trading_type
        $regulationStatus = $validated['regulation_status'] ?? null;
        $tradingType = $validated['trading_type'] ?? null;

        if ($regulationStatus && $tradingType) {
            // US_REGULATED or INTL_REGULATED require regulator fields
            if (in_array($regulationStatus, ['US_REGULATED', 'INTL_REGULATED'])) {
                $request->validate([
                    'regulator_name' => 'required|string',
                    'regulator_jurisdiction' => 'required|string',
                    'regulator_register_number' => 'required|string',
                ]);
            }

            // PUBLIC or PUBLICLY_TRADED_SUBSIDIARY require exchange fields
            if (in_array($tradingType, ['PUBLIC', 'PUBLICLY_TRADED_SUBSIDIARY'])) {
                $request->validate([
                    'listed_exchange' => 'required|string',
                    'ticker_symbol' => 'required|string',
                ]);
            }

            // PUBLICLY_TRADED_SUBSIDIARY requires parent_institution_name
            if ($tradingType === 'PUBLICLY_TRADED_SUBSIDIARY') {
                $request->validate([
                    'parent_institution_name' => 'required|string',
                ]);
            }
        }

        // Use institution_type from session
        $validated['institution_type'] = $institutionType;

        try {
            $refId = Str::uuid()->toString();

            // Prepare institution members for Paxos
            $institutionMembers = [];
            foreach ($members as $member) {
                $institutionMembers[] = [
                    'identity_id' => $member['identity_id'],
                    'roles' => $member['roles'],
                ];
            }

            // Map country abbreviations to full names
            $countryMap = [
                'USA' => 'United States',
                'US' => 'United States',
            ];
            $businessCountry = $countryMap[$validated['business_address_country']] ?? $validated['business_address_country'];
            $incorporationCountry = !empty($validated['incorporation_address_country'])
                ? ($countryMap[$validated['incorporation_address_country']] ?? $validated['incorporation_address_country'])
                : $businessCountry;

            // Prepare institution details
            $institutionDetails = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'institution_type' => $validated['institution_type'],
                'business_address' => [
                    'country' => $businessCountry,
                    'address1' => $validated['business_address1'],
                    'city' => $validated['business_city'],
                    'province' => $validated['business_province'] ?? null,
                    'zip_code' => $validated['business_zip_code'] ?? null,
                ],
                'cip_id' => $validated['cip_id'],
                'cip_id_type' => $validated['cip_id_type'],
                'cip_id_country' => $validated['cip_id_country'],
            ];

            if (!empty($validated['institution_sub_type'])) {
                $institutionDetails['institution_sub_type'] = $validated['institution_sub_type'];
            }

            // Convert date to ISO 8601 format with time
            if (!empty($validated['govt_registration_date'])) {
                $date = new \DateTime($validated['govt_registration_date']);
                $institutionDetails['govt_registration_date'] = $date->format('Y-m-d\TH:i:s\Z');
            }

            // Always include incorporation_address (use business_address if not provided)
            if (!empty($validated['incorporation_address1'])) {
                $institutionDetails['incorporation_address'] = [
                    'country' => $incorporationCountry,
                    'address1' => $validated['incorporation_address1'],
                    'city' => $validated['incorporation_city'],
                    'province' => $validated['incorporation_province'] ?? null,
                    'zip_code' => $validated['incorporation_zip_code'] ?? null,
                ];
            } else {
                // Use business_address as incorporation_address if not provided
                $institutionDetails['incorporation_address'] = [
                    'country' => $businessCountry,
                    'address1' => $validated['business_address1'],
                    'city' => $validated['business_city'],
                    'province' => $validated['business_province'] ?? null,
                    'zip_code' => $validated['business_zip_code'] ?? null,
                ];
            }

            if (!empty($validated['regulation_status'])) {
                $institutionDetails['regulation_status'] = $validated['regulation_status'];
            }

            if (!empty($validated['trading_type'])) {
                $institutionDetails['trading_type'] = $validated['trading_type'];
            }

            if (!empty($validated['listed_exchange'])) {
                $institutionDetails['listed_exchange'] = $validated['listed_exchange'];
            }

            if (!empty($validated['ticker_symbol'])) {
                $institutionDetails['ticker_symbol'] = $validated['ticker_symbol'];
            }

            if (!empty($validated['regulator_name'])) {
                $institutionDetails['regulator_name'] = $validated['regulator_name'];
            }

            if (!empty($validated['regulator_jurisdiction'])) {
                // Map jurisdiction to country format (e.g., "US-NY" -> "USA")
                $jurisdiction = $validated['regulator_jurisdiction'];
                if (strpos($jurisdiction, '-') !== false) {
                    // Extract country part (before the dash)
                    $parts = explode('-', $jurisdiction);
                    $institutionDetails['regulator_jurisdiction'] = $parts[0] === 'US' ? 'USA' : $parts[0];
                } else {
                    $institutionDetails['regulator_jurisdiction'] = $jurisdiction === 'US' ? 'USA' : $jurisdiction;
                }
            }

            if (!empty($validated['regulator_register_number'])) {
                $institutionDetails['regulator_register_number'] = $validated['regulator_register_number'];
            }

            if (!empty($validated['parent_institution_name'])) {
                $institutionDetails['parent_institution_name'] = $validated['parent_institution_name'];
            }

            if (!empty($validated['business_description'])) {
                $institutionDetails['business_description'] = $validated['business_description'];
            }

            $paxosData = [
                'ref_id' => $refId,
                'institution_members' => $institutionMembers,
                'institution_details' => $institutionDetails,
            ];

            // Log the request data before sending to Paxos
            \Illuminate\Support\Facades\Log::info('Creating institution identity - Request Data', [
                'paxos_data' => $paxosData,
                'paxos_data_json' => json_encode($paxosData, JSON_PRETTY_PRINT),
                'members_count' => count($institutionMembers),
                'user_id' => auth()->id(),
            ]);

            $paxosResponse = $this->paxosService->createIdentity($paxosData);

            // Save institution identity to database
            // For institution identities, person-specific fields are null
            $identity = Identity::create([
                'user_id' => auth()->id(),
                'paxos_identity_id' => $paxosResponse['id'],
                'ref_id' => $refId,
                'identity_type' => 'INSTITUTION',
                'verifier_type' => 'PAXOS',
                'first_name' => null,
                'last_name' => null,
                'date_of_birth' => null,
                'nationality' => null,
                'email' => $validated['email'],
                'address_country' => null,
                'address1' => null,
                'city' => null,
                'province' => null,
                'zip_code' => null,
                'institution_details' => $institutionDetails,
                'institution_members' => $members,
                'id_verification_status' => $paxosResponse['status'] ?? 'PENDING',
                'sanctions_verification_status' => 'PENDING',
            ]);

            session()->forget(['institution_members', 'institution_type']);
            Cache::forget($this->institutionWizardMembersCacheKey());

            // Create account and profile if requested
            if ($request->get('create_account', false)) {
                $accountType = $validated['account_type'] ?? 'BROKERAGE';
                $accountRefId = Str::uuid()->toString();

                $accountData = [
                    'create_profile' => true,
                    'account' => [
                        'identity_id' => $paxosResponse['id'],
                        'ref_id' => $accountRefId,
                        'type' => $accountType,
                        'description' => $validated['name'] . ' Account',
                    ],
                ];

                $accountResponse = $this->paxosService->createAccount($accountData);

                // Save account
                $account = Account::create([
                    'user_id' => auth()->id(),
                    'identity_id' => $identity->id,
                    'paxos_account_id' => $accountResponse['account']['id'],
                    'ref_id' => $accountRefId,
                    'type' => $accountType,
                    'description' => $validated['name'] . ' Account',
                ]);

                // Create profile if returned
                if (isset($accountResponse['profile']['id'])) {
                    Profile::create([
                        'user_id' => auth()->id(),
                        'account_id' => $account->id,
                        'paxos_profile_id' => $accountResponse['profile']['id'],
                    ]);
                }

                return redirect()->route('lender.identities.show', $identity)
                    ->with('success', 'Institution identity, account, and profile created successfully!');
            }

            return redirect()->route('lender.identities.show', $identity)
                ->with('success', 'Institution identity created successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create institution identity', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create institution identity. Please try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     * For person identities (existing flow)
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

            return redirect()->route('lender.identities.show', $identity)
                ->with('success', 'Identity created successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create identity', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create identity in Paxos. Please try again. Error: ' . $e->getMessage()]);
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

                // Log the full response from Paxos API
                \Illuminate\Support\Facades\Log::info('Paxos get identity response', [
                    'local_identity_id' => $identity->id,
                    'paxos_identity_id' => $identity->paxos_identity_id,
                    'identity_type' => $identity->identity_type,
                    'response' => $paxosResponse,
                    'response_json' => json_encode($paxosResponse, JSON_PRETTY_PRINT),
                    'status' => $paxosResponse['status'] ?? null,
                    'summary_status' => $paxosResponse['summary_status'] ?? null,
                    'id_verification_status' => $paxosResponse['id_verification_status'] ?? null,
                    'sanctions_verification_status' => $paxosResponse['sanctions_verification_status'] ?? null,
                ]);

                // Update local database with latest status from Paxos
                $updateData = [];

                // Update verification statuses
                // For person identities, use summary_status if available
                if ($identity->identity_type === 'PERSON') {
                    if (isset($paxosResponse['summary_status'])) {
                        // Map summary_status to id_verification_status
                        $updateData['id_verification_status'] = $paxosResponse['summary_status'];
                    } elseif (isset($paxosResponse['id_verification_status'])) {
                        $updateData['id_verification_status'] = $paxosResponse['id_verification_status'];
                    }

                    if (isset($paxosResponse['sanctions_verification_status'])) {
                        $updateData['sanctions_verification_status'] = $paxosResponse['sanctions_verification_status'];
                    } elseif (isset($paxosResponse['summary_status'])) {
                        // If sanctions_verification_status is not separate, use summary_status
                        $updateData['sanctions_verification_status'] = $paxosResponse['summary_status'];
                    }
                } else {
                    // For institution identities, use status field
                    if (isset($paxosResponse['status'])) {
                        $updateData['id_verification_status'] = $paxosResponse['status'];
                    } elseif (isset($paxosResponse['summary_status'])) {
                        $updateData['id_verification_status'] = $paxosResponse['summary_status'];
                    }

                    if (isset($paxosResponse['sanctions_verification_status'])) {
                        $updateData['sanctions_verification_status'] = $paxosResponse['sanctions_verification_status'];
                    }
                }

                // For person identities, update person details if changed
                if ($identity->identity_type === 'PERSON' && isset($paxosResponse['person_details'])) {
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

                // For institution identities, update institution details if changed
                if ($identity->identity_type === 'INSTITUTION') {
                    // Update status - institution identities use 'status' field
                    if (isset($paxosResponse['status'])) {
                        $updateData['id_verification_status'] = $paxosResponse['status'];
                    }

                    // Update institution details if changed
                    if (isset($paxosResponse['institution_details'])) {
                        $currentDetails = $identity->institution_details ?? [];
                        $newDetails = $paxosResponse['institution_details'];

                        // Merge and update institution details
                        $mergedDetails = array_merge($currentDetails, $newDetails);
                        if ($mergedDetails !== $currentDetails) {
                            $updateData['institution_details'] = $mergedDetails;
                        }
                    }

                    // Update institution members if changed
                    if (isset($paxosResponse['institution_members'])) {
                        // Note: We store local member data, but we can update if needed
                        // For now, we'll keep our local member data structure
                    }
                }

                // Update the identity if there are changes
                if (!empty($updateData)) {
                    $identity->update($updateData);
                    \Illuminate\Support\Facades\Log::info('Updated identity from Paxos API', [
                        'identity_id' => $identity->id,
                        'paxos_identity_id' => $identity->paxos_identity_id,
                        'updates' => $updateData,
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't prevent the page from loading
                \Illuminate\Support\Facades\Log::warning('Failed to fetch identity status from Paxos', [
                    'identity_id' => $identity->id,
                    'paxos_identity_id' => $identity->paxos_identity_id,
                    'error' => $e->getMessage(),
                ]);
                // Continue to show the page with existing data
            }
        }

        // Refresh the identity from database to get updated values
        $identity->refresh();

        return view('lender.identities.show', compact('identity'));
    }

    /**
     * Approve identity (sandbox)
     */
    public function approve(Identity $identity)
    {
        if ($identity->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            // Prepare approval data - use standard fields for all identity types
            $approvalData = [
                'id_verification_status' => 'APPROVED',
                'sanctions_verification_status' => 'APPROVED',
                'additional_screening_status' => 'APPROVED',
                'document_verification_status' => 'APPROVED',
            ];

            // Call Paxos API to approve the identity
            $paxosResponse = $this->paxosService->approveIdentity($identity->paxos_identity_id, $approvalData);

            // Log the approval response (usually empty array)
            \Illuminate\Support\Facades\Log::info('Paxos approve identity response (sandbox-status endpoint)', [
                'local_identity_id' => $identity->id,
                'paxos_identity_id' => $identity->paxos_identity_id,
                'identity_type' => $identity->identity_type,
                'approval_data_sent' => $approvalData,
                'response' => $paxosResponse,
                'response_json' => json_encode($paxosResponse, JSON_PRETTY_PRINT),
                'response_is_empty' => empty($paxosResponse),
            ]);

            // Wait for Paxos to process the approval (response is usually empty, so we need to query)
            sleep(2);

            // Query the identity to see if status was updated
            $updatedIdentity = $this->paxosService->getIdentity($identity->paxos_identity_id);

            // If still pending, wait a bit more and try again (up to 3 retries)
            $maxRetries = 3;
            $retryCount = 0;
            while ($retryCount < $maxRetries) {
                $currentStatus = $updatedIdentity['summary_status'] ?? $updatedIdentity['status'] ?? null;
                if ($currentStatus === 'APPROVED') {
                    break; // Status is approved, no need to retry
                }

                $retryCount++;
                if ($retryCount < $maxRetries) {
                    \Illuminate\Support\Facades\Log::info('Status still pending, retrying...', [
                        'retry_count' => $retryCount,
                        'current_status' => $currentStatus,
                    ]);
                    sleep(1);
                    $updatedIdentity = $this->paxosService->getIdentity($identity->paxos_identity_id);
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
                'local_identity_id' => $identity->id,
                'paxos_identity_id' => $identity->paxos_identity_id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to approve identity in Paxos. Please try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Cache key for institution wizard member list (survives empty session on a later request).
     */
    protected function institutionWizardMembersCacheKey(): string
    {
        return 'institution_wizard_members:'.auth()->id();
    }

    /**
     * @return array<int, array{identity_id: string, local_identity_id: int, roles: array<int, string>, name: string}>
     */
    protected function resolveInstitutionWizardMembers(): array
    {
        $members = session('institution_members', []);
        if ($members !== []) {
            return $members;
        }

        $cached = Cache::get($this->institutionWizardMembersCacheKey(), []);
        if (is_array($cached) && $cached !== []) {
            session(['institution_members' => $cached]);
        }

        return is_array($cached) ? $cached : [];
    }

    /**
     * Rebuild members from the institution form POST when session/cache were lost mid-wizard.
     *
     * @return array<int, array{identity_id: string, local_identity_id: int, roles: array<int, string>, name: string}>
     */
    protected function validatedWizardMembersFromRequest(Request $request): array
    {
        $raw = $request->input('wizard_members');
        if (! is_array($raw) || $raw === []) {
            return [];
        }

        $allowedRoles = [
            'BENEFICIAL_OWNER',
            'AUTHORIZED_USER',
            'MANAGEMENT_CONTROL_PERSON',
            'ACCOUNT_OPENER',
            'TRUSTEE',
            'GRANTOR',
            'BENEFICIARY',
        ];

        $out = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $localId = isset($row['local_identity_id']) ? (int) $row['local_identity_id'] : 0;
            $paxosId = isset($row['identity_id']) ? (string) $row['identity_id'] : '';
            $roles = $row['roles'] ?? [];

            if ($localId < 1 || $paxosId === '') {
                continue;
            }

            if (! is_array($roles)) {
                $roles = [$roles];
            }

            $roles = array_values(array_unique(array_filter(
                array_map(static fn ($v) => (string) $v, $roles),
                fn (string $r) => in_array($r, $allowedRoles, true)
            )));

            if ($roles === []) {
                continue;
            }

            $identity = Identity::query()
                ->where('user_id', auth()->id())
                ->where('id', $localId)
                ->where('identity_type', 'PERSON')
                ->first();

            if ($identity === null || $identity->paxos_identity_id !== $paxosId) {
                continue;
            }

            $out[] = [
                'identity_id' => $paxosId,
                'local_identity_id' => $identity->id,
                'roles' => $roles,
                'name' => trim(($identity->first_name ?? '').' '.($identity->last_name ?? '')),
            ];
        }

        return $out;
    }
}
