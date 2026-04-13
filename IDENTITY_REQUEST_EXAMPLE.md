# Identity Creation Request Body for Testing Documents Required Event

## Overview

This document provides example request bodies for creating identities in the Paxos sandbox that will trigger the `identity.documents_required` event. This is useful for testing your event processing system.

## Request Body Format

### For PERSON Identity (Most Common for Document Requirements)

```json
{
  "ref_id": "550e8400-e29b-41d4-a716-446655440000",
  "person_details": {
    "verifier_type": "PAXOS",
    "first_name": "John",
    "last_name": "Doe",
    "date_of_birth": "1990-05-15",
    "nationality": "United States",
    "email": "john.doe@example.com",
    "phone_number": "+1-555-123-4567",
    "address": {
      "country": "United States",
      "address1": "123 Main Street",
      "city": "New York",
      "province": "NY",
      "zip_code": "10001"
    },
    "cip_id": "123-45-6789",
    "cip_id_type": "SSN",
    "cip_id_country": "USA"
  }
}
```

### Minimal PERSON Identity (More Likely to Trigger Document Requirements)

```json
{
  "ref_id": "550e8400-e29b-41d4-a716-446655440001",
  "person_details": {
    "verifier_type": "PAXOS",
    "first_name": "Jane",
    "last_name": "Smith",
    "date_of_birth": "1985-03-20",
    "nationality": "United States",
    "email": "jane.smith@example.com",
    "phone_number": "+1-555-987-6543",
    "address": {
      "country": "United States",
      "address1": "456 Oak Avenue",
      "city": "Los Angeles",
      "province": "CA",
      "zip_code": "90001"
    }
  }
}
```

**Note:** Omitting `cip_id` (SSN) often triggers document requirements as additional verification is needed.

### International PERSON Identity (Higher Likelihood of Document Requirements)

```json
{
  "ref_id": "550e8400-e29b-41d4-a716-446655440002",
  "person_details": {
    "verifier_type": "PAXOS",
    "first_name": "Maria",
    "last_name": "Garcia",
    "date_of_birth": "1992-08-10",
    "nationality": "Mexico",
    "email": "maria.garcia@example.com",
    "phone_number": "+52-55-1234-5678",
    "address": {
      "country": "Mexico",
      "address1": "Calle Reforma 123",
      "city": "Mexico City",
      "province": "CDMX",
      "zip_code": "06000"
    }
  }
}
```

**Note:** Non-US identities typically require more documents for KYC verification.

## Using with cURL

### Example 1: Create Identity via API

```bash
curl -X POST "https://api.sandbox.paxos.com/v2/identity/identities" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "ref_id": "550e8400-e29b-41d4-a716-446655440000",
    "person_details": {
      "verifier_type": "PAXOS",
      "first_name": "John",
      "last_name": "Doe",
      "date_of_birth": "1990-05-15",
      "nationality": "United States",
      "email": "john.doe@example.com",
      "phone_number": "+1-555-123-4567",
      "address": {
        "country": "United States",
        "address1": "123 Main Street",
        "city": "New York",
        "province": "NY",
        "zip_code": "10001"
      }
    }
  }'
```

### Example 2: Using Laravel's PaxosService

```php
use App\Services\PaxosService;

$paxosService = app(PaxosService::class);

$identityData = [
    'ref_id' => \Illuminate\Support\Str::uuid()->toString(),
    'person_details' => [
        'verifier_type' => 'PAXOS',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => '1990-05-15',
        'nationality' => 'United States',
        'email' => 'john.doe@example.com',
        'phone_number' => '+1-555-123-4567',
        'address' => [
            'country' => 'United States',
            'address1' => '123 Main Street',
            'city' => 'New York',
            'province' => 'NY',
            'zip_code' => '10001',
        ],
    ],
];

try {
    $response = $paxosService->createIdentity($identityData);
    $identityId = $response['id'];
    
    // The identity should trigger a documents_required event
    // Monitor events to see when it arrives
    echo "Identity created: {$identityId}\n";
    echo "Monitor events for identity.documents_required event\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Expected Event Structure

After creating the identity, you should receive an `identity.documents_required` event with this structure:

```json
{
  "id": "event-12345",
  "type": "identity.documents_required",
  "created_at": "2025-01-15T10:30:00Z",
  "object": {
    "identity_id": "identity-abc-123",
    "required_documents": [
      {
        "document_type": "PROOF_OF_IDENTITY",
        "description": "Government-issued photo ID"
      },
      {
        "document_type": "PROOF_OF_RESIDENCY",
        "description": "Proof of address"
      }
    ]
  }
}
```

## Testing the Event Processing

1. **Create the identity** using one of the request bodies above
2. **Poll for events** or wait for webhook:
   ```php
   $events = $paxosService->listEvents([
       'type' => 'identity.documents_required',
       'created_at.gte' => now()->subMinutes(5)->toIso8601String(),
   ]);
   ```
3. **Process the event**:
   ```php
   $eventProcessor = app(PaxosEventProcessor::class);
   foreach ($events as $event) {
       $eventProcessor->processEvent($event);
   }
   ```
4. **Check logs** for the `ProcessDocumentsRequiredEvent` job execution

## Tips for Triggering Document Requirements

1. **Omit CIP ID (SSN)**: Identities without SSN often require additional documents (but may be denied if other data is invalid)
2. **Use international addresses**: Non-US addresses typically require more verification
3. **Use non-US nationality**: Foreign nationals often need additional KYC documents
4. **Create identity without documents first**: Then wait for the event to request them
5. **Avoid invalid SSN formats**: Invalid SSNs can cause immediate denial instead of document requirements
6. **Use realistic but incomplete data**: Valid enough to pass initial checks, incomplete enough to need documents

## ⚠️ Important: Avoiding Denial vs. Document Requirements

If your identity is being **denied** instead of requiring documents, try:

1. **Remove SSN entirely** (don't include `cip_id` field):
   ```json
   {
     "ref_id": "550e8400-e29b-41d4-a716-446655440003",
     "person_details": {
       "verifier_type": "PAXOS",
       "first_name": "John",
       "last_name": "Doe",
       "date_of_birth": "1990-05-15",
       "nationality": "United States",
       "email": "john.doe@example.com",
       "phone_number": "+1-555-123-4567",
       "address": {
         "country": "United States",
         "address1": "123 Main Street",
         "city": "New York",
         "province": "NY",
         "zip_code": "10001"
       }
       // NO cip_id field - this often triggers document requirements
     }
   }
   ```

2. **Use a valid but non-US identity** (more likely to require documents):
   ```json
   {
     "ref_id": "550e8400-e29b-41d4-a716-446655440004",
     "person_details": {
       "verifier_type": "PAXOS",
       "first_name": "Jean",
       "last_name": "Dupont",
       "date_of_birth": "1988-07-22",
       "nationality": "France",
       "email": "jean.dupont@example.com",
       "phone_number": "+33-1-23-45-67-89",
       "address": {
         "country": "France",
         "address1": "15 Rue de la Paix",
         "city": "Paris",
         "province": null,
         "zip_code": "75001"
       }
     }
   }
   ```

3. **Check the identity status** after creation - if `additional_screening_status` is "ERROR", it may be denied. Look for `PENDING` status instead.

## Common Document Types

When the event is triggered, you may see these document types in `required_documents`:

- `PROOF_OF_IDENTITY` - Government-issued photo ID (passport, driver's license)
- `PROOF_OF_RESIDENCY` - Proof of address (utility bill, bank statement)
- `PROOF_OF_SSN` - Social Security Number verification
- `PROOF_OF_BUSINESS` - For institution identities (business license, articles of incorporation)

## Next Steps After Receiving the Event

1. **Extract required documents** from the event
2. **Upload documents** using `PaxosService::uploadDocument()`:
   ```php
   $paxosService->uploadDocument(
       identityId: $identityId,
       filePath: '/path/to/document.pdf',
       fileName: 'proof_of_identity.pdf',
       documentTypes: ['PROOF_OF_IDENTITY']
   );
   ```
3. **Monitor for approval** - Wait for `identity.approved` event
