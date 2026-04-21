<?php

namespace Tests\Feature;

use App\Jobs\ProcessPaxosWebhookJob;
use App\Models\Identity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaxosWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_webhook_when_secret_configured_and_header_missing(): void
    {
        config([
            'services.paxos.webhook_secret' => 'expected-secret',
            'services.paxos.webhook_header' => 'X-Paxos-Webhook-Key',
        ]);

        $this->postJson('/webhooks/paxos', [
            'id' => (string) Str::uuid(),
            'type' => 'identity.approved',
            'source' => 'com.paxos',
            'time' => now()->toIso8601String(),
            'object' => 'event',
            'is_test' => false,
        ])->assertUnauthorized();
    }

    public function test_rejects_webhook_when_secret_does_not_match(): void
    {
        config([
            'services.paxos.webhook_secret' => 'expected-secret',
            'services.paxos.webhook_header' => 'X-Paxos-Webhook-Key',
        ]);

        $this->postJson(
            '/webhooks/paxos',
            [
                'id' => (string) Str::uuid(),
                'type' => 'identity.approved',
                'source' => 'com.paxos',
                'time' => now()->toIso8601String(),
                'object' => 'event',
                'is_test' => false,
            ],
            ['X-Paxos-Webhook-Key' => 'wrong']
        )->assertUnauthorized();
    }

    public function test_rejects_invalid_json_body(): void
    {
        config([
            'services.paxos.webhook_secret' => 's',
            'services.paxos.webhook_header' => 'X-Paxos-Webhook-Key',
        ]);

        $this->call(
            'POST',
            '/webhooks/paxos',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_PAXOS_WEBHOOK_KEY' => 's',
            ],
            '{not json'
        )->assertStatus(400);
    }

    public function test_accepts_valid_webhook_and_dispatches_job(): void
    {
        Bus::fake();

        config([
            'services.paxos.webhook_secret' => 'expected-secret',
            'services.paxos.webhook_header' => 'X-Paxos-Webhook-Key',
        ]);

        $payload = [
            'id' => (string) Str::uuid(),
            'type' => 'identity.approved',
            'source' => 'com.paxos',
            'time' => now()->toIso8601String(),
            'object' => 'event',
            'is_test' => false,
        ];

        $this->postJson('/webhooks/paxos', $payload, [
            'X-Paxos-Webhook-Key' => 'expected-secret',
        ])->assertOk();

        Bus::assertDispatched(ProcessPaxosWebhookJob::class, function (ProcessPaxosWebhookJob $job) use ($payload): bool {
            return $job->envelope['id'] === $payload['id']
                && $job->envelope['type'] === 'identity.approved';
        });
    }

    public function test_test_webhook_does_not_dispatch_job(): void
    {
        Bus::fake();

        config([
            'services.paxos.webhook_secret' => 'expected-secret',
            'services.paxos.webhook_header' => 'X-Paxos-Webhook-Key',
        ]);

        $this->postJson('/webhooks/paxos', [
            'id' => (string) Str::uuid(),
            'type' => 'identity.approved',
            'source' => 'com.paxos',
            'time' => now()->toIso8601String(),
            'object' => 'event',
            'is_test' => true,
        ], [
            'X-Paxos-Webhook-Key' => 'expected-secret',
        ])->assertOk();

        Bus::assertNothingDispatched();
    }

    public function test_job_updates_identity_on_identity_approved(): void
    {
        $user = User::factory()->create(['role' => 'individual']);
        $paxosIdentityId = (string) Str::uuid();
        Identity::create([
            'user_id' => $user->id,
            'paxos_identity_id' => $paxosIdentityId,
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

        $eventId = (string) Str::uuid();

        \Illuminate\Support\Facades\Http::fake([
            'https://oauth.sandbox.paxos.com/*' => \Illuminate\Support\Facades\Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], 200),
            'https://api.sandbox.paxos.com/v2/events/*' => \Illuminate\Support\Facades\Http::response([
                'id' => $eventId,
                'type' => 'identity.approved',
                'object' => [
                    'identity_id' => $paxosIdentityId,
                ],
            ], 200),
        ]);

        $envelope = [
            'id' => $eventId,
            'type' => 'identity.approved',
            'source' => 'com.paxos',
            'time' => now()->toIso8601String(),
            'object' => 'event',
            'is_test' => false,
        ];

        ProcessPaxosWebhookJob::dispatchSync($envelope);

        $this->assertDatabaseHas('identities', [
            'paxos_identity_id' => $paxosIdentityId,
            'id_verification_status' => 'APPROVED',
            'sanctions_verification_status' => 'APPROVED',
        ]);

        $this->assertDatabaseHas('processed_events', [
            'event_id' => $eventId,
            'event_type' => 'identity.approved',
        ]);
    }
}
