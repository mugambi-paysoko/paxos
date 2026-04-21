<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PaxosTransferUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationUnreadSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_fetch_unread_notification_summary(): void
    {
        $this->getJson(route('notifications.unread-summary'))
            ->assertUnauthorized();
    }

    public function test_authenticated_user_receives_unread_notification_summary_json(): void
    {
        $user = User::factory()->create(['role' => 'individual']);
        $user->notify(new PaxosTransferUpdatedNotification(
            title: 'Transfer update',
            message: 'Crypto withdrawal is now COMPLETED.',
            actionUrl: 'https://example.test/view',
            actionLabel: 'View details',
        ));

        $this->actingAs($user)
            ->getJson(route('notifications.unread-summary'))
            ->assertOk()
            ->assertJsonPath('notifications.0.title', 'Transfer update')
            ->assertJsonPath('notifications.0.message', 'Crypto withdrawal is now COMPLETED.')
            ->assertJsonPath('notifications.0.action_url', 'https://example.test/view')
            ->assertJsonPath('notifications.0.action_label', 'View details');
    }
}
