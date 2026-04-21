<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CryptoWithdrawal;
use App\Models\Identity;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\PaxosTransferUpdatedNotification;
use App\Services\PaxosEventProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaxosTransferWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_webhook_updates_crypto_withdrawal_and_sends_database_notification(): void
    {
        $eventId = (string) Str::uuid();
        $tid = (string) Str::uuid();

        Http::fake([
            'https://oauth.sandbox.paxos.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
            'https://api.sandbox.paxos.com/v2/events/*' => Http::response([
                'id' => $eventId,
                'type' => 'transfer.crypto_withdrawal.completed',
                'object' => [
                    'id' => $tid,
                    'type' => 'CRYPTO_WITHDRAWAL',
                    'status' => 'COMPLETED',
                    'profile_id' => (string) Str::uuid(),
                ],
            ], 200),
        ]);

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
            'paxos_transfer_id' => $tid,
            'ref_id' => (string) Str::uuid(),
            'amount' => '0.1',
            'asset' => 'BTC',
            'destination_address' => 'addr',
            'crypto_network' => 'BITCOIN',
            'status' => 'PENDING',
        ]);

        $envelope = [
            'id' => $eventId,
            'type' => 'transfer.crypto_withdrawal.completed',
            'source' => 'com.paxos',
            'time' => now()->toIso8601String(),
            'object' => 'event',
            'is_test' => false,
        ];

        $processor = $this->app->make(PaxosEventProcessor::class);
        $this->assertTrue($processor->processEvent($envelope));

        $this->assertDatabaseHas('crypto_withdrawals', [
            'paxos_transfer_id' => $tid,
            'status' => 'COMPLETED',
        ]);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(1, $user->fresh()->unreadNotifications()->count());

        $eventId2 = (string) Str::uuid();
        Http::fake([
            'https://oauth.sandbox.paxos.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
            'https://api.sandbox.paxos.com/v2/events/*' => Http::response([
                'id' => $eventId2,
                'type' => 'transfer.crypto_withdrawal.completed',
                'object' => [
                    'id' => $tid,
                    'type' => 'CRYPTO_WITHDRAWAL',
                    'status' => 'COMPLETED',
                    'profile_id' => (string) Str::uuid(),
                ],
            ], 200),
        ]);

        $envelope2 = [
            'id' => $eventId2,
            'type' => 'transfer.crypto_withdrawal.completed',
            'source' => 'com.paxos',
            'time' => now()->toIso8601String(),
            'object' => 'event',
            'is_test' => false,
        ];

        $this->assertTrue($processor->processEvent($envelope2));
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_transfer_webhook_sends_notification_when_status_unchanged_but_paxos_event_is_new(): void
    {
        $eventId = (string) Str::uuid();
        $tid = (string) Str::uuid();

        Http::fake([
            'https://oauth.sandbox.paxos.com/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
            'https://api.sandbox.paxos.com/v2/events/*' => Http::response([
                'id' => $eventId,
                'type' => 'transfer.crypto_withdrawal.pending',
                'object' => [
                    'id' => $tid,
                    'type' => 'CRYPTO_WITHDRAWAL',
                    'status' => 'PENDING',
                    'profile_id' => (string) Str::uuid(),
                ],
            ], 200),
        ]);

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
            'paxos_transfer_id' => $tid,
            'ref_id' => (string) Str::uuid(),
            'amount' => '0.1',
            'asset' => 'BTC',
            'destination_address' => 'addr',
            'crypto_network' => 'BITCOIN',
            'status' => 'PENDING',
        ]);

        $envelope = [
            'id' => $eventId,
            'type' => 'transfer.crypto_withdrawal.pending',
            'source' => 'com.paxos',
            'time' => now()->toIso8601String(),
            'object' => 'event',
            'is_test' => false,
        ];

        $processor = $this->app->make(PaxosEventProcessor::class);
        $this->assertTrue($processor->processEvent($envelope));

        $this->assertDatabaseHas('crypto_withdrawals', [
            'paxos_transfer_id' => $tid,
            'status' => 'PENDING',
        ]);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(1, $user->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_dismiss_notification(): void
    {
        $user = User::factory()->create(['role' => 'individual']);
        $user->notify(new PaxosTransferUpdatedNotification(
            title: 'Test',
            message: 'Hello',
            actionUrl: null,
        ));

        $notification = $user->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($user->notifications()->whereKey($notification->id)->first()?->read_at);
    }

    public function test_user_cannot_dismiss_another_users_notification(): void
    {
        $owner = User::factory()->create(['role' => 'individual']);
        $other = User::factory()->create(['role' => 'individual']);
        $owner->notify(new PaxosTransferUpdatedNotification(
            title: 'Secret',
            message: 'Nope',
            actionUrl: null,
        ));

        $notification = $owner->notifications()->first();
        $this->assertNotNull($notification);

        $this->actingAs($other)
            ->post(route('notifications.read', $notification->id))
            ->assertForbidden();
    }
}
