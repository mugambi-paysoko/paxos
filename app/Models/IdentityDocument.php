<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class IdentityDocument extends Model
{
    protected $fillable = [
        'identity_id',
        'document_type',
        'paxos_document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'upload_status',
        'paxos_document_id',
        'uploaded_at',
        'error_message',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }

    /**
     * Scope to get pending documents
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('upload_status', 'pending');
    }

    /**
     * Scope to get uploaded documents
     */
    public function scopeUploaded(Builder $query): Builder
    {
        return $query->where('upload_status', 'uploaded');
    }

    /**
     * Scope to get failed documents
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('upload_status', 'failed');
    }

    /**
     * Scope to filter by document type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('document_type', $type);
    }

    /**
     * Check if document is uploaded
     */
    public function isUploaded(): bool
    {
        return $this->upload_status === 'uploaded';
    }

    /**
     * Mark document as uploaded
     */
    public function markAsUploaded(string $paxosDocumentId): void
    {
        $this->update([
            'upload_status' => 'uploaded',
            'paxos_document_id' => $paxosDocumentId,
            'uploaded_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark document as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'upload_status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Map local document type to Paxos document type
     */
    public static function mapToPaxosType(string $localType): string
    {
        return match ($localType) {
            'proof_of_identity' => 'PROOF_OF_IDENTITY',
            'proof_of_residency' => 'PROOF_OF_RESIDENCY',
            'proof_of_residence' => 'PROOF_OF_RESIDENCY', // Alias
            'proof_of_ssn' => 'PROOF_OF_SSN',
            'proof_of_business' => 'PROOF_OF_BUSINESS',
            'proof_of_funds' => 'PROOF_OF_BUSINESS', // Maps to PROOF_OF_BUSINESS
            'tax_id_document' => 'PROOF_OF_BUSINESS', // Maps to PROOF_OF_BUSINESS
            'certificate_of_good_standing' => 'PROOF_OF_BUSINESS', // Maps to PROOF_OF_BUSINESS
            default => 'PROOF_OF_IDENTITY', // Fallback
        };
    }

    /**
     * Map Paxos document type to local document type
     */
    public static function mapFromPaxosType(string $paxosType): string
    {
        return match ($paxosType) {
            'PROOF_OF_IDENTITY' => 'proof_of_identity',
            'PROOF_OF_RESIDENCY' => 'proof_of_residency',
            'PROOF_OF_RESIDENCE' => 'proof_of_residency', // Alias
            'PROOF_OF_SSN' => 'proof_of_ssn',
            'PROOF_OF_BUSINESS' => 'proof_of_business', // Could be any business-related document
            default => 'proof_of_identity', // Fallback
        };
    }
}
