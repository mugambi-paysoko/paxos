<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Identity;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaxosOnboardingService
{
    protected PaxosService $paxosService;

    public function __construct(PaxosService $paxosService)
    {
        $this->paxosService = $paxosService;
    }

    /**
     * Create complete Paxos onboarding (Identity, Account, Profile) for a user
     * 
     * @param User $user
     * @param array $identityData
     * @param array $accountData
     * @return array Returns ['identity' => Identity, 'account' => Account, 'profile' => Profile|null]
     * @throws \Exception
     */
    public function createCompleteOnboarding(User $user, array $identityData, array $accountData = []): array
    {
        try {
            // Step 1: Create Identity
            $identity = $this->createIdentity($user, $identityData);

            // Step 2: Approve identity in sandbox (if in sandbox mode)
            if (config('services.paxos.base_url') === 'https://api.sandbox.paxos.com') {
                $this->approveIdentityInSandbox($identity);
            }

            // Step 3: Create Account with Profile
            $accountData['identity_id'] = $identity->id;
            $accountData['create_profile'] = $accountData['create_profile'] ?? true;
            
            $account = $this->createAccount($user, $accountData);
            
            // Step 4: Get Profile if created
            $profile = $account->profile;

            return [
                'identity' => $identity,
                'account' => $account,
                'profile' => $profile,
            ];
        } catch (\Exception $e) {
            Log::error('Paxos onboarding failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create identity for user
     */
    protected function createIdentity(User $user, array $data): Identity
    {
        $refId = Str::uuid()->toString();

        // Prepare data for Paxos API
        $paxosData = [
            'person_details' => [
                'verifier_type' => 'PAXOS',
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'address' => [
                    'country' => $data['address_country'],
                    'address1' => $data['address1'],
                    'city' => $data['city'],
                    'province' => $data['province'] ?? null,
                    'zip_code' => $data['zip_code'] ?? null,
                ],
                'nationality' => $data['nationality'],
                'email' => $data['email'] ?? $user->email,
                'phone_number' => $data['phone_number'] ?? null,
            ],
            'ref_id' => $refId,
        ];

        if (!empty($data['cip_id'])) {
            $paxosData['person_details']['cip_id'] = $data['cip_id'];
            $paxosData['person_details']['cip_id_type'] = $data['cip_id_type'] ?? 'SSN';
            $paxosData['person_details']['cip_id_country'] = $data['cip_id_country'] ?? 'USA';
        }

        // Call Paxos API - this will throw an exception if it fails
        $paxosResponse = $this->paxosService->createIdentity($paxosData);

        // Only save to database after successful Paxos API call
        // The PaxosService already validates that 'id' exists, so we can safely use it here
        $identity = Identity::create([
            'user_id' => $user->id,
            'paxos_identity_id' => $paxosResponse['id'],
            'ref_id' => $refId,
            'verifier_type' => 'PAXOS',
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'],
            'nationality' => $data['nationality'],
            'cip_id' => $data['cip_id'] ?? null,
            'cip_id_type' => $data['cip_id_type'] ?? null,
            'cip_id_country' => $data['cip_id_country'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'email' => $data['email'] ?? $user->email,
            'address_country' => $data['address_country'],
            'address1' => $data['address1'],
            'city' => $data['city'],
            'province' => $data['province'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'id_verification_status' => $paxosResponse['id_verification_status'] ?? 'PENDING',
            'sanctions_verification_status' => $paxosResponse['sanctions_verification_status'] ?? 'PENDING',
        ]);

        return $identity;
    }

    /**
     * Approve identity in sandbox
     */
    protected function approveIdentityInSandbox(Identity $identity): void
    {
        try {
            $paxosResponse = $this->paxosService->approveIdentity($identity->paxos_identity_id, [
                'id_verification_status' => 'APPROVED',
                'sanctions_verification_status' => 'APPROVED',
            ]);

            $identity->update([
                'id_verification_status' => 'APPROVED',
                'sanctions_verification_status' => 'APPROVED',
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to approve identity in sandbox', [
                'identity_id' => $identity->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw - this is optional for sandbox
        }
    }

    /**
     * Create account for user
     */
    protected function createAccount(User $user, array $data): Account
    {
        $identity = Identity::where('id', $data['identity_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $refId = Str::uuid()->toString();
        $createProfile = $data['create_profile'] ?? true;

        // Prepare data for Paxos API
        $paxosData = [
            'create_profile' => $createProfile,
            'account' => [
                'identity_id' => $identity->paxos_identity_id,
                'ref_id' => $refId,
                'type' => $data['type'] ?? 'BROKERAGE',
                'description' => $data['description'] ?? 'Primary account for ' . $user->name,
            ],
        ];

        // Call Paxos API - this will throw an exception if it fails
        $paxosResponse = $this->paxosService->createAccount($paxosData);

        // Only save to database after successful Paxos API call
        // The PaxosService already validates that 'account.id' exists, so we can safely use it here
        $account = Account::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'paxos_account_id' => $paxosResponse['account']['id'],
            'ref_id' => $refId,
            'type' => $data['type'] ?? 'BROKERAGE',
            'description' => $data['description'] ?? 'Primary account for ' . $user->name,
        ]);

        // Create profile if requested and returned
        if ($createProfile && isset($paxosResponse['profile']['id'])) {
            Profile::create([
                'user_id' => $user->id,
                'account_id' => $account->id,
                'paxos_profile_id' => $paxosResponse['profile']['id'],
            ]);
        }

        return $account;
    }
}
