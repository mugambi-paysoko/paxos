<?php

namespace App\Jobs;

use App\Models\Identity;
use App\Models\IdentityDocument;
use App\Services\PaxosService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentsRequiredEvent implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $event,
        protected ?int $localIdentityId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PaxosService $paxosService): void
    {
        $object = $this->event['object'] ?? [];
        $identityId = $object['identity_id'] ?? null;
        $requiredDocuments = $object['required_documents'] ?? [];

        if (! $identityId) {
            Log::error('Documents required event missing identity_id', ['event' => $this->event]);

            return;
        }

        // Find local identity
        $identity = $this->localIdentityId
            ? Identity::find($this->localIdentityId)
            : Identity::where('paxos_identity_id', $identityId)->first();

        if (! $identity) {
            Log::warning('Identity not found for documents required event', [
                'paxos_identity_id' => $identityId,
                'local_identity_id' => $this->localIdentityId,
            ]);

            // TODO: Notify user or admin that documents are required
            return;
        }

        Log::info('Processing documents required event in job', [
            'identity_id' => $identity->id,
            'paxos_identity_id' => $identityId,
            'required_documents' => $requiredDocuments,
        ]);

        // Process each required document
        $uploadedCount = 0;
        $failedCount = 0;
        $missingDocuments = [];

        foreach ($requiredDocuments as $requiredDoc) {
            $paxosDocumentType = $requiredDoc['document_type'] ?? null;

            if (! $paxosDocumentType) {
                Log::warning('Required document missing document_type', [
                    'required_doc' => $requiredDoc,
                ]);
                continue;
            }

            // Map Paxos document type to local document type
            $localDocumentType = IdentityDocument::mapFromPaxosType($paxosDocumentType);

            // For PROOF_OF_BUSINESS, we need to check multiple possible document types
            // since multiple institution documents map to PROOF_OF_BUSINESS
            if ($paxosDocumentType === 'PROOF_OF_BUSINESS' && $identity->identity_type === 'INSTITUTION') {
                // Try to find any business-related document that hasn't been uploaded
                $document = $identity->documents()
                    ->whereIn('document_type', ['proof_of_business', 'proof_of_funds', 'tax_id_document', 'certificate_of_good_standing'])
                    ->where('upload_status', 'pending')
                    ->first();
            } else {
                // Find matching document for this identity
                $document = $identity->documents()
                    ->where('document_type', $localDocumentType)
                    ->where('upload_status', 'pending')
                    ->first();
            }

            if (! $document) {
                Log::info('Document not found locally', [
                    'identity_id' => $identity->id,
                    'paxos_document_type' => $paxosDocumentType,
                    'local_document_type' => $localDocumentType,
                ]);
                $missingDocuments[] = $paxosDocumentType;
                continue;
            }

            // Check if file exists
            if (! Storage::disk('private')->exists($document->file_path)) {
                Log::error('Document file not found in storage', [
                    'identity_id' => $identity->id,
                    'document_id' => $document->id,
                    'file_path' => $document->file_path,
                ]);
                $document->markAsFailed('File not found in storage');
                $failedCount++;
                continue;
            }

            // Get full file path
            $fullFilePath = Storage::disk('private')->path($document->file_path);

            try {
                // Upload document to Paxos
                $paxosService->uploadDocument(
                    identityId: $identityId,
                    filePath: $fullFilePath,
                    fileName: $document->file_name,
                    documentTypes: [$paxosDocumentType]
                );

                // Mark as uploaded (we don't get a document ID back, so we'll mark it as uploaded)
                $document->markAsUploaded('uploaded');

                Log::info('Document uploaded successfully to Paxos', [
                    'identity_id' => $identity->id,
                    'document_id' => $document->id,
                    'document_type' => $paxosDocumentType,
                ]);

                $uploadedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to upload document to Paxos', [
                    'identity_id' => $identity->id,
                    'document_id' => $document->id,
                    'document_type' => $paxosDocumentType,
                    'error' => $e->getMessage(),
                ]);

                $document->markAsFailed($e->getMessage());
                $failedCount++;
            }
        }

        // Log summary
        Log::info('Documents required event processed', [
            'identity_id' => $identity->id,
            'paxos_identity_id' => $identityId,
            'uploaded_count' => $uploadedCount,
            'failed_count' => $failedCount,
            'missing_count' => count($missingDocuments),
            'missing_documents' => $missingDocuments,
        ]);

        // TODO: Notify user if documents are missing or failed
        if (count($missingDocuments) > 0) {
            Log::info('Missing documents - user notification needed', [
                'identity_id' => $identity->id,
                'missing_documents' => $missingDocuments,
            ]);
            // TODO: Send email/notification to user
        }

        if ($failedCount > 0) {
            Log::warning('Some documents failed to upload - user notification needed', [
                'identity_id' => $identity->id,
                'failed_count' => $failedCount,
            ]);
            // TODO: Send email/notification to user
        }
    }
}
