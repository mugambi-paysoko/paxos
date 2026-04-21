<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Identity;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class BorrowerBalancesEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_lender_cannot_access_borrower_balances_pages(): void
    {
        $user = User::factory()->create(['role' => 'institution']);

        $this->actingAs($user)
            ->get(route('borrower.balances.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson(route('borrower.balances.json'))
            ->assertForbidden();
    }

    public function test_borrower_balances_json_returns_aggregated_balances(): void
    {
        Http::fake([
            'https://oauth.sandbox.paxos.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
            'https://api.sandbox.paxos.com/v2/profiles/*/balances*' => Http::response([
                'items' => [
                    ['asset' => 'USD', 'available' => '99.10', 'trading' => '0.90'],
                    ['asset' => 'BTC', 'available' => '0', 'trading' => '0'],
                ],
            ], 200),
        ]);

        $user = User::factory()->create(['role' => 'individual']);
        $identity = Identity::create([
            'user_id' => $user->id,
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
        Profile::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'paxos_profile_id' => (string) Str::uuid(),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('borrower.balances.json'))
            ->assertOk()
            ->assertJsonPath('has_profiles', true);

        $response->assertJsonPath('aggregated.0.asset', 'USD');
        $response->assertJsonPath('aggregated.0.available', '99.1');
        $response->assertJsonPath('aggregated.0.trading', '0.9');
        $this->assertSame('100', $response->json('aggregated.0.total'));
    }

    public function test_borrower_can_view_balances_html_page(): void
    {
        Http::fake([
            'https://oauth.sandbox.paxos.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
            'https://api.sandbox.paxos.com/v2/profiles/*/balances*' => Http::response([
                'items' => [
                    ['asset' => 'USD', 'available' => '10', 'trading' => '0'],
                ],
            ], 200),
        ]);

        $user = User::factory()->create(['role' => 'individual']);
        $identity = Identity::create([
            'user_id' => $user->id,
            'ref_id' => (string) Str::uuid(),
            'first_name' => 'A',
            'last_name' => 'B',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'USA',
            'email' => 'b@example.com',
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
        Profile::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'paxos_profile_id' => (string) Str::uuid(),
        ]);

        $this->actingAs($user)
            ->get(route('borrower.balances.index'))
            ->assertOk()
            ->assertSee('By asset', false)
            ->assertSee('rolled up by ticker', false);
    }
}
