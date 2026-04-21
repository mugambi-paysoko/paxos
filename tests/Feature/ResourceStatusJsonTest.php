<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CryptoWithdrawal;
use App\Models\FiatAccount;
use App\Models\FiatWithdrawal;
use App\Models\Identity;
use App\Models\Profile;
use App\Models\User;
use App\Support\TransferStatusDigest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResourceStatusJsonTest extends TestCase
{
    use RefreshDatabase;

    public function test_borrower_can_poll_own_identity_status(): void
    {
        $user = User::factory()->create(['role' => 'individual']);
        $identity = Identity::create([
            'user_id' => $user->id,
            'paxos_identity_id' => (string) Str::uuid(),
            'ref_id' => (string) Str::uuid(),
            'first_name' => 'A',
            'last_name' => 'B',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'USA',
            'email' => 'a@example.com',
            'address_country' => 'USA',
            'address1' => '1 Main St',
            'city' => 'NYC',
            'id_verification_status' => 'PENDING',
            'sanctions_verification_status' => 'PENDING',
        ]);

        $this->actingAs($user)
            ->getJson(route('borrower.status.identity', $identity))
            ->assertOk()
            ->assertJsonPath('resource', 'identity')
            ->assertJsonPath('id', $identity->id)
            ->assertJsonPath('id_verification_status', 'PENDING');
    }

    public function test_borrower_cannot_poll_another_users_identity_status(): void
    {
        $owner = User::factory()->create(['role' => 'individual']);
        $other = User::factory()->create(['role' => 'individual']);
        $identity = Identity::create([
            'user_id' => $owner->id,
            'paxos_identity_id' => (string) Str::uuid(),
            'ref_id' => (string) Str::uuid(),
            'first_name' => 'A',
            'last_name' => 'B',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'USA',
            'email' => 'owner@example.com',
            'address_country' => 'USA',
            'address1' => '1 Main St',
            'city' => 'NYC',
        ]);

        $this->actingAs($other)
            ->getJson(route('borrower.status.identity', $identity))
            ->assertForbidden();
    }

    public function test_borrower_crypto_withdrawals_digest_matches_transfer_status_digest(): void
    {
        $user = User::factory()->create(['role' => 'individual']);
        $identity = Identity::create([
            'user_id' => $user->id,
            'paxos_identity_id' => (string) Str::uuid(),
            'ref_id' => (string) Str::uuid(),
            'first_name' => 'A',
            'last_name' => 'B',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'USA',
            'email' => 'a@example.com',
            'address_country' => 'USA',
            'address1' => '1 Main St',
            'city' => 'NYC',
        ]);
        $account = Account::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'ref_id' => (string) Str::uuid(),
            'type' => 'BROKERAGE',
        ]);
        $profile = Profile::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'paxos_profile_id' => (string) Str::uuid(),
        ]);
        CryptoWithdrawal::create([
            'user_id' => $user->id,
            'profile_id' => $profile->id,
            'identity_id' => $identity->id,
            'paxos_transfer_id' => (string) Str::uuid(),
            'ref_id' => (string) Str::uuid(),
            'amount' => '1',
            'asset' => 'USDP',
            'destination_address' => '0xabc',
            'crypto_network' => 'ETHEREUM',
            'status' => 'PENDING',
        ]);

        $expected = TransferStatusDigest::forUserCryptoWithdrawals($user->fresh());

        $this->actingAs($user)
            ->getJson(route('borrower.status.crypto-withdrawals.digest'))
            ->assertOk()
            ->assertJsonPath('resource', 'crypto_withdrawals_index')
            ->assertJsonPath('digest', $expected);
    }

    public function test_borrower_fiat_withdrawals_digest_matches_transfer_status_digest(): void
    {
        $user = User::factory()->create(['role' => 'individual']);
        $identity = Identity::create([
            'user_id' => $user->id,
            'paxos_identity_id' => (string) Str::uuid(),
            'ref_id' => (string) Str::uuid(),
            'first_name' => 'A',
            'last_name' => 'B',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'USA',
            'email' => 'a@example.com',
            'address_country' => 'USA',
            'address1' => '1 Main St',
            'city' => 'NYC',
        ]);
        $account = Account::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'ref_id' => (string) Str::uuid(),
            'type' => 'BROKERAGE',
        ]);
        $profile = Profile::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'paxos_profile_id' => (string) Str::uuid(),
        ]);
        $fiatAccount = FiatAccount::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'paxos_fiat_account_id' => (string) Str::uuid(),
            'status' => 'ACTIVE',
        ]);
        FiatWithdrawal::create([
            'user_id' => $user->id,
            'profile_id' => $profile->id,
            'identity_id' => $identity->id,
            'fiat_account_id' => $fiatAccount->id,
            'paxos_transfer_id' => (string) Str::uuid(),
            'ref_id' => (string) Str::uuid(),
            'amount' => '10',
            'asset' => 'USD',
            'status' => 'PENDING',
            'transfer_type' => 'FIAT_WITHDRAWAL',
        ]);

        $expected = TransferStatusDigest::forUserFiatWithdrawals($user->fresh());

        $this->actingAs($user)
            ->getJson(route('borrower.status.fiat-withdrawals.digest'))
            ->assertOk()
            ->assertJsonPath('resource', 'fiat_withdrawals_index')
            ->assertJsonPath('digest', $expected);
    }
}
