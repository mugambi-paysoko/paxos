# Document Upload Implementation Plan - Approach A

## Overview
Users upload documents during identity creation. Documents are stored locally. When `identity.documents_required` event arrives, documents are automatically uploaded to Paxos.

## Implementation Steps

### Step 1: Database Schema - Create Documents Table

**Migration**: `create_identity_documents_table.php`

**Fields needed**:
- `id` (primary key)
- `identity_id` (foreign key to identities)
- `document_type` (string) - Your local type (e.g., 'proof_of_identity', 'proof_of_residency')
- `paxos_document_type` (string) - Paxos type (e.g., 'PROOF_OF_IDENTITY', 'PROOF_OF_RESIDENCY')
- `file_path` (string) - Storage path to the file
- `file_name` (string) - Original file name
- `file_size` (integer) - File size in bytes
- `mime_type` (string) - File MIME type
- `upload_status` (enum) - 'pending', 'uploaded', 'failed'
- `paxos_document_id` (string, nullable) - ID returned from Paxos after upload
- `uploaded_at` (timestamp, nullable) - When uploaded to Paxos
- `error_message` (text, nullable) - Error if upload failed
- `created_at`, `updated_at` (timestamps)

**Indexes**:
- `identity_id` + `document_type` (composite, unique) - One document per type per identity
- `upload_status` - For querying pending uploads

### Step 2: Create IdentityDocument Model

**Model**: `app/Models/IdentityDocument.php`

**Relationships**:
- `belongsTo(Identity::class)`

**Scopes**:
- `pending()` - Documents not yet uploaded
- `uploaded()` - Successfully uploaded documents
- `failed()` - Failed uploads
- `byType($type)` - Filter by document type

**Methods**:
- `markAsUploaded($paxosDocumentId)` - Update status after successful upload
- `markAsFailed($errorMessage)` - Update status after failed upload
- `isUploaded()` - Check if already uploaded

### Step 3: Update Identity Creation Form

**File**: `resources/views/lender/identities/create-person.blade.php`

**Changes needed**:
- Add file upload fields for common document types:
  - Proof of Identity (driver's license, passport)
  - Proof of Residency (utility bill, bank statement)
  - Optional: Additional documents

**Validation**:
- File types: PDF, JPG, PNG
- File size: Max 10MB (or whatever Paxos allows)
- Make uploads optional (user can skip and upload later)

### Step 4: Update IdentityController to Handle File Uploads

**File**: `app/Http/Controllers/Lender/IdentityController.php`

**Method**: `store()` - Add document handling

**Logic**:
1. After creating identity in Paxos
2. If files were uploaded:
   - Validate files (type, size)
   - Store files in `storage/app/documents/{identity_id}/`
   - Create `IdentityDocument` records with:
     - `upload_status = 'pending'`
     - `document_type` = your local type
     - `paxos_document_type` = mapped Paxos type (see mapping below)
     - `file_path` = storage path
     - `file_name` = original name
     - `file_size`, `mime_type` = from file

**File Storage**:
- Use Laravel's Storage facade
- Path: `documents/{identity_id}/{document_type}_{timestamp}.{ext}`
- Example: `documents/123/proof_of_identity_1705320000.pdf`

### Step 5: Document Type Mapping

**Create mapping configuration or helper method**

**Mapping** (Your Type → Paxos Type):
- `proof_of_identity` → `PROOF_OF_IDENTITY`
- `proof_of_residency` → `PROOF_OF_RESIDENCY`
- `proof_of_ssn` → `PROOF_OF_SSN`
- `proof_of_business` → `PROOF_OF_BUSINESS` (for institutions)

**Helper method** in `IdentityDocument` model:
```php
public static function mapToPaxosType(string $localType): string
{
    return match($localType) {
        'proof_of_identity' => 'PROOF_OF_IDENTITY',
        'proof_of_residency' => 'PROOF_OF_RESIDENCY',
        'proof_of_ssn' => 'PROOF_OF_SSN',
        'proof_of_business' => 'PROOF_OF_BUSINESS',
        default => 'PROOF_OF_IDENTITY', // fallback
    };
}
```

**Reverse mapping** (Paxos Type → Your Type):
- Used when event arrives with Paxos document types
- Map back to find matching local documents

### Step 6: Update ProcessDocumentsRequiredEvent Job

**File**: `app/Jobs/ProcessDocumentsRequiredEvent.php`

**Logic to implement**:

1. **Extract required documents from event**:
   - Get `required_documents` array from event
   - Each has `document_type` (Paxos type) and `description`

2. **For each required document**:
   - Map Paxos document type to your local type
   - Find `IdentityDocument` for this identity and type
   - Check if document exists and is pending upload

3. **If document found**:
   - Validate file exists and is readable
   - Call `PaxosService::uploadDocument()` with:
     - Identity ID (Paxos)
     - File path
     - File name
     - Document types array
   - On success: Mark document as uploaded, save Paxos document ID
   - On failure: Mark as failed, log error, optionally retry

4. **If document NOT found**:
   - Log which document type is missing
   - Notify user (email, in-app notification, etc.)
   - Could queue a reminder job

5. **Track upload results**:
   - Log success/failure for each document
   - Update document records with status
   - Handle partial success (some uploaded, some failed)

### Step 7: Error Handling & Retry Logic

**Retry Strategy**:
- If upload fails, mark as failed
- Could implement retry with exponential backoff
- Max retries: 3 attempts
- After max retries, notify user

**Error Scenarios**:
- File not found → Mark failed, notify user
- Invalid file format → Mark failed, log error
- Network error → Retry
- Paxos API error → Log, mark failed, notify

### Step 8: Notification System

**When documents are missing**:
- Send email to user: "Documents required for identity verification"
- In-app notification
- Admin dashboard alert

**When upload fails**:
- Notify user: "Failed to upload document, please try again"
- Provide link to re-upload

**When upload succeeds**:
- Optional: Notify user "Documents uploaded successfully"

### Step 9: Add Document Management UI

**New routes**:
- `GET /lender/identities/{identity}/documents` - View documents
- `POST /lender/identities/{identity}/documents` - Upload additional documents
- `DELETE /lender/identities/{identity}/documents/{document}` - Delete document

**Views**:
- Show list of documents for identity
- Show upload status (pending, uploaded, failed)
- Allow re-uploading failed documents
- Show upload date and Paxos document ID

### Step 10: Testing Strategy

**Test Cases**:
1. Create identity with documents → Verify stored locally
2. Receive documents_required event → Verify automatic upload
3. Missing document → Verify notification sent
4. Upload failure → Verify retry logic
5. Multiple documents → Verify all uploaded
6. Re-upload failed document → Verify works

## File Structure

```
app/
├── Models/
│   └── IdentityDocument.php (NEW)
├── Http/
│   └── Controllers/
│       └── Lender/
│           ├── IdentityController.php (UPDATE - add document handling)
│           └── IdentityDocumentController.php (NEW - optional, for management)
└── Jobs/
    └── ProcessDocumentsRequiredEvent.php (UPDATE - implement upload logic)

database/
└── migrations/
    └── YYYY_MM_DD_create_identity_documents_table.php (NEW)

resources/
└── views/
    └── lender/
        └── identities/
            ├── create-person.blade.php (UPDATE - add file uploads)
            └── documents/
                ├── index.blade.php (NEW - list documents)
                └── upload.blade.php (NEW - upload form)
```

## Document Type Mapping Reference

### Your Local Types → Paxos Types

| Local Type | Paxos Type | Description |
|------------|------------|-------------|
| `proof_of_identity` | `PROOF_OF_IDENTITY` | Government-issued photo ID |
| `proof_of_residency` | `PROOF_OF_RESIDENCY` | Proof of address |
| `proof_of_ssn` | `PROOF_OF_SSN` | SSN verification document |
| `proof_of_business` | `PROOF_OF_BUSINESS` | Business license, articles of incorporation |

### Paxos Types → Your Local Types (Reverse)

| Paxos Type | Local Type | Notes |
|------------|------------|-------|
| `PROOF_OF_IDENTITY` | `proof_of_identity` | |
| `PROOF_OF_RESIDENCY` | `proof_of_residency` | |
| `PROOF_OF_SSN` | `proof_of_ssn` | |
| `PROOF_OF_BUSINESS` | `proof_of_business` | For institutions |

## Implementation Order

1. ✅ Create migration for `identity_documents` table
2. ✅ Create `IdentityDocument` model
3. ✅ Update identity creation form (add file upload fields)
4. ✅ Update `IdentityController::store()` to handle file uploads
5. ✅ Implement document type mapping
6. ✅ Update `ProcessDocumentsRequiredEvent` job with upload logic
7. ✅ Add error handling and retry logic
8. ✅ Add notification system
9. ✅ Create document management UI (optional)
10. ✅ Test end-to-end flow

## Key Considerations

1. **File Validation**: Validate file type, size before storing
2. **Storage Security**: Store files in private storage, not public
3. **File Naming**: Use unique names to avoid conflicts
4. **Idempotency**: Don't upload same document twice (check status)
5. **Cleanup**: Consider deleting old/failed documents after some time
6. **Privacy**: Documents contain sensitive information - secure storage
7. **Backup**: Consider backing up documents before uploading to Paxos

## Success Criteria

- ✅ Users can upload documents during identity creation
- ✅ Documents are stored securely in local storage
- ✅ When `documents_required` event arrives, documents upload automatically
- ✅ Missing documents trigger user notifications
- ✅ Failed uploads are retried and logged
- ✅ Users can view upload status and re-upload if needed
