<?php

namespace App\Services;

use App\Jobs\ProcessDocumentsRequiredEvent;
use App\Models\CryptoWithdrawal;
use App\Models\FiatDeposit;
use App\Models\FiatWithdrawal;
use App\Models\Identity;
use App\Models\ProcessedEvent;
use App\Models\User;
use App\Notifications\PaxosTransferUpdatedNotification;
use Illuminate\Support\Facades\Log;

class PaxosEventProcessor
{
    public function __construct(
        protected PaxosService $paxosService
    ) {}

    /**
     * Process a single event
     *
     * @param  array<string, mixed>  $event  The event data from Paxos API
     * @return bool True if processed successfully
     */
    public function processEvent(array $event): bool
    {
        $eventId = $event['id'] ?? null;
        $eventType = $event['type'] ?? null;

        if (! $eventId || ! $eventType) {
            Log::warning('Invalid event data received', ['event' => $event]);

            return false;
        }

        // Check if already processed (idempotency)
        if (ProcessedEvent::isProcessed($eventId)) {
            Log::info('Event already processed, skipping', [
                'event_id' => $eventId,
                'event_type' => $eventType,
            ]);

            return true;
        }

        try {
            // Fetch full event details
            $fullEvent = $this->paxosService->getEvent($eventId);

            // Process based on event type
            $processed = match ($eventType) {
                'identity.documents_required' => $this->processDocumentsRequiredEvent($fullEvent),
                'identity.approved' => $this->processIdentityApprovedEvent($fullEvent),
                'identity.denied' => $this->processIdentityDeniedEvent($fullEvent),
                'identity.disabled' => $this->processIdentityDisabledEvent($fullEvent),
                'identity.kyc_refresh.started' => $this->processIdentityKycRefreshStartedEvent($fullEvent),
                'identity.kyc_refresh.completed' => $this->processIdentityKycRefreshCompletedEvent($fullEvent),
                'identity.kyc_refresh.expired' => $this->processIdentityKycRefreshExpiredEvent($fullEvent),
                default => str_starts_with((string) $eventType, 'transfer.')
                    ? $this->processTransferStatusChangeEvent($fullEvent, (string) $eventType)
                    : $this->processUnknownEvent($fullEvent),
            };

            if ($processed) {
                // Mark as processed (identity_id column stores Paxos identity UUID or transfer UUID when applicable)
                $relatedKey = $this->extractIdentityId($fullEvent) ?? $this->extractTransferReferenceId($fullEvent);
                ProcessedEvent::markAsProcessed($eventId, $eventType, $relatedKey, $fullEvent);

                Log::info('Event processed successfully', [
                    'event_id' => $eventId,
                    'event_type' => $eventType,
                ]);
            }

            return $processed;
        } catch (\Exception $e) {
            Log::error('Failed to process event', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process identity.documents_required event
     */
    protected function processDocumentsRequiredEvent(array $event): bool
    {
        $object = is_array($event['object'] ?? null) ? $event['object'] : [];
        $identityId = $object['identity_id'] ?? null;
        $requiredDocuments = $object['required_documents'] ?? [];

        if (! $identityId) {
            Log::error('Documents required event missing identity_id', ['event' => $event]);

            return false;
        }

        // Find local identity
        $identity = Identity::where('paxos_identity_id', $identityId)->first();

        if (! $identity) {
            Log::warning('Identity not found locally for documents required event', [
                'paxos_identity_id' => $identityId,
            ]);

            // Still mark as processed, but queue a job to handle it later
            ProcessDocumentsRequiredEvent::dispatch($event);

            return true;
        }

        Log::info('Processing documents required event', [
            'identity_id' => $identityId,
            'local_identity_id' => $identity->id,
            'required_documents' => $requiredDocuments,
        ]);

        // Queue job to handle document upload (asynchronous)
        // This allows you to check if documents are available locally or notify the user
        ProcessDocumentsRequiredEvent::dispatch($event, $identity->id);

        return true;
    }

    /**
     * Process identity.approved event
     */
    protected function processIdentityApprovedEvent(array $event): bool
    {
        $identityId = $this->extractIdentityId($event);

        if (! $identityId) {
            Log::error('Identity approved event missing identity_id', ['event' => $event]);

            return false;
        }

        $identity = Identity::where('paxos_identity_id', $identityId)->first();

        if ($identity) {
            $identity->update([
                'id_verification_status' => 'APPROVED',
                'sanctions_verification_status' => 'APPROVED',
            ]);

            Log::info('Updated identity status to APPROVED', [
                'identity_id' => $identity->id,
                'paxos_identity_id' => $identityId,
            ]);
        } else {
            Log::warning('Identity not found locally for approved event', [
                'paxos_identity_id' => $identityId,
            ]);
        }

        return true;
    }

    /**
     * Process identity.denied event
     */
    protected function processIdentityDeniedEvent(array $event): bool
    {
        $identityId = $this->extractIdentityId($event);

        if (! $identityId) {
            return false;
        }

        $identity = Identity::where('paxos_identity_id', $identityId)->first();

        if ($identity) {
            $identity->update([
                'id_verification_status' => 'REJECTED',
            ]);

            Log::info('Updated identity status to REJECTED', [
                'identity_id' => $identity->id,
                'paxos_identity_id' => $identityId,
            ]);
        }

        return true;
    }

    /**
     * Process identity.disabled event
     */
    protected function processIdentityDisabledEvent(array $event): bool
    {
        $identityId = $this->extractIdentityId($event);

        if (! $identityId) {
            return false;
        }

        $identity = Identity::where('paxos_identity_id', $identityId)->first();

        if ($identity) {
            // You might want to add a 'status' field to identities table for DISABLED
            // For now, we'll just log it
            Log::info('Identity disabled event received', [
                'identity_id' => $identity->id,
                'paxos_identity_id' => $identityId,
            ]);
        }

        return true;
    }

    /**
     * Process unknown event types
     */
    protected function processUnknownEvent(array $event): bool
    {
        $eventType = $event['type'] ?? 'unknown';

        Log::info('Unknown event type received, marking as processed', [
            'event_type' => $eventType,
            'event_id' => $event['id'] ?? null,
        ]);

        // Still mark as processed to avoid reprocessing
        return true;
    }

    protected function processIdentityKycRefreshStartedEvent(array $event): bool
    {
        return $this->logIdentityComplianceEvent($event, 'kyc_refresh.started');
    }

    protected function processIdentityKycRefreshCompletedEvent(array $event): bool
    {
        return $this->logIdentityComplianceEvent($event, 'kyc_refresh.completed');
    }

    protected function processIdentityKycRefreshExpiredEvent(array $event): bool
    {
        return $this->logIdentityComplianceEvent($event, 'kyc_refresh.expired');
    }

    protected function logIdentityComplianceEvent(array $event, string $label): bool
    {
        $identityId = $this->extractIdentityId($event);

        if (! $identityId) {
            Log::warning("Paxos {$label} event missing identity_id", ['event' => $event]);

            return true;
        }

        $identity = Identity::where('paxos_identity_id', $identityId)->first();

        if ($identity) {
            Log::info("Paxos {$label} for local identity", [
                'local_identity_id' => $identity->id,
                'paxos_identity_id' => $identityId,
                'paxos_event_id' => $event['id'] ?? null,
            ]);
        } else {
            Log::warning("Paxos {$label}: no local identity row", [
                'paxos_identity_id' => $identityId,
                'paxos_event_id' => $event['id'] ?? null,
            ]);
        }

        return true;
    }

    /**
     * Extract identity_id from event object
     */
    protected function extractIdentityId(array $event): ?string
    {
        $object = $event['object'] ?? [];
        if (! is_array($object)) {
            return null;
        }

        return $object['identity_id'] ?? null;
    }

    protected function extractTransferReferenceId(array $event): ?string
    {
        $payload = $this->extractTransferStatusChange($event);

        return $payload['transfer_id'] ?? null;
    }

    /**
     * @return array{transfer_id: string, status: string, transfer_type: ?string, profile_id: ?string, ref_id: ?string}|null
     */
    protected function extractTransferStatusChange(array $event): ?array
    {
        $object = $event['object'] ?? null;
        if (! is_array($object)) {
            return null;
        }

        if (isset($object['transfer_status_change']) && is_array($object['transfer_status_change'])) {
            $object = $object['transfer_status_change'];
        }

        if (! isset($object['id'], $object['status'])) {
            return null;
        }

        return [
            'transfer_id' => (string) $object['id'],
            'status' => (string) $object['status'],
            'transfer_type' => isset($object['type']) ? (string) $object['type'] : null,
            'profile_id' => isset($object['profile_id']) ? (string) $object['profile_id'] : null,
            'ref_id' => isset($object['ref_id']) ? (string) $object['ref_id'] : null,
        ];
    }

    protected function processTransferStatusChangeEvent(array $event, string $eventType): bool
    {
        $payload = $this->extractTransferStatusChange($event);
        if ($payload === null) {
            Log::warning('Paxos transfer event missing transfer_status_change fields', [
                'event_type' => $eventType,
                'event_id' => $event['id'] ?? null,
            ]);

            return true;
        }

        $transferId = $payload['transfer_id'];
        $status = $payload['status'];
        $transferKind = strtoupper((string) ($payload['transfer_type'] ?? ''));

        $crypto = CryptoWithdrawal::query()->where('paxos_transfer_id', $transferId)->first();
        if ($crypto) {
            $this->applyTransferUpdateAndNotify(
                $crypto->user,
                $crypto,
                'crypto_withdrawal',
                $eventType,
                $status,
                $transferKind,
                fn (): string => $crypto->user->isBorrower()
                    ? route('borrower.crypto-withdrawals.show', $crypto)
                    : route('lender.crypto-withdrawals.show', $crypto)
            );

            return true;
        }

        $fiat = FiatWithdrawal::query()->where('paxos_transfer_id', $transferId)->first();
        if ($fiat) {
            $this->applyTransferUpdateAndNotify(
                $fiat->user,
                $fiat,
                'fiat_withdrawal',
                $eventType,
                $status,
                $transferKind,
                fn (): string => $fiat->user->isBorrower()
                    ? route('borrower.fiat-withdrawals.show', $fiat)
                    : route('lender.fiat-withdrawals.show', $fiat)
            );

            return true;
        }

        $deposit = FiatDeposit::query()->where('paxos_deposit_id', $transferId)->first();
        if ($deposit) {
            $url = $deposit->user->isBorrower()
                ? null
                : route('lender.fiat-deposits.show', $deposit->paxos_deposit_id);

            $this->applyTransferUpdateAndNotify(
                $deposit->user,
                $deposit,
                'fiat_deposit',
                $eventType,
                $status,
                $transferKind,
                fn (): ?string => $url
            );

            return true;
        }

        Log::info('Paxos transfer event: no local row matched transfer id', [
            'event_type' => $eventType,
            'transfer_id' => $transferId,
            'transfer_kind' => $transferKind,
        ]);

        return true;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    protected function applyTransferUpdateAndNotify(
        User $user,
        $model,
        string $kind,
        string $eventType,
        string $status,
        string $transferKind,
        \Closure $resolveUrl
    ): void {
        $previous = (string) ($model->getAttribute('status') ?? '');
        $priorResponse = is_array($model->paxos_response) ? $model->paxos_response : [];
        $priorWebhookEvent = $priorResponse['last_webhook_event'] ?? null;

        $mergedResponse = array_merge($priorResponse, [
            'last_webhook_event' => $eventType,
            'last_webhook_at' => now()->toIso8601String(),
        ]);

        $model->forceFill([
            'status' => $status,
            'paxos_response' => $mergedResponse,
        ])->save();

        $label = match ($kind) {
            'crypto_withdrawal' => 'Crypto withdrawal',
            'fiat_withdrawal' => 'Fiat withdrawal',
            'fiat_deposit' => 'Fiat deposit',
            default => 'Transfer',
        };

        $actionUrl = $resolveUrl();

        if ($previous !== $status) {
            $message = "{$label} is now {$status}".($transferKind !== '' ? " ({$transferKind})." : '.');

            $user->notify(new PaxosTransferUpdatedNotification(
                title: 'Transfer update',
                message: $message,
                actionUrl: $actionUrl,
                actionLabel: 'View details',
            ));

            return;
        }

        if ($eventType !== $priorWebhookEvent) {
            $message = "{$label}: Paxos reported {$eventType}; status remains {$status}".($transferKind !== '' ? " ({$transferKind})." : '.');

            $user->notify(new PaxosTransferUpdatedNotification(
                title: 'Transfer update',
                message: $message,
                actionUrl: $actionUrl,
                actionLabel: 'View details',
            ));
        }
    }
}
