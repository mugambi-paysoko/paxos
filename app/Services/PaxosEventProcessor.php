<?php

namespace App\Services;

use App\Jobs\ProcessDocumentsRequiredEvent;
use App\Models\Identity;
use App\Models\ProcessedEvent;
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
                default => $this->processUnknownEvent($fullEvent),
            };

            if ($processed) {
                // Mark as processed
                $identityId = $this->extractIdentityId($fullEvent);
                ProcessedEvent::markAsProcessed($eventId, $eventType, $identityId, $fullEvent);

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
        $object = $event['object'] ?? [];
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
        $object = $event['object'] ?? [];
        $identityId = $object['identity_id'] ?? null;

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
        $object = $event['object'] ?? [];
        $identityId = $object['identity_id'] ?? null;

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
        $object = $event['object'] ?? [];
        $identityId = $object['identity_id'] ?? null;

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

    /**
     * Extract identity_id from event object
     */
    protected function extractIdentityId(array $event): ?string
    {
        $object = $event['object'] ?? [];

        return $object['identity_id'] ?? null;
    }
}
