# Paxos v2 OpenAPI Specification Overview

## API Structure

The Paxos v2 API is organized into the following main tag groups:

### 1. **Identity & Onboarding**
- **Identity** - Person or institution management
- **Institution Members** - Associate persons with institutions
- **Accounts** - Connect identities to profiles
- **Account Members** - Joint account support
- **Identity Documents** - KYC document uploads

### 2. **Profiles**
- **Profiles** - Asset balances and transaction containers
- Two types: `DEFAULT` (system-generated) and `NORMAL` (user-created)

### 3. **Deposits and Withdrawals**
- **Transfers** - General transfer operations
- **Fiat Transfers** - Fiat deposit/withdrawal operations
- **Deposit Addresses** - Crypto deposit addresses
- **Crypto Deposits** - Crypto deposit management
- **Crypto Withdrawals** - Crypto withdrawal operations
- **Fees** - Fee management
- **Internal Transfers** - Transfer between profiles
- **Paxos Transfers** - Platform-specific transfers
- **Limits** - Transaction limits

### 4. **Trading**
- **Market Data** - Order book and market information
- **Orders** - Market, limit, and post-only orders
- **Quotes** - Held rate quotes for buying/selling assets
- **Quote Executions** - Execute quotes
- **Pricing** - Historical price data for charting
- **Issuer Quotes** - Mint/redeem Paxos-issued assets

### 5. **Events**
- **Events** - Webhook events and polling
- **Event Types** - Available event types
- **Event Objects** - Event payload structures

### 6. **Other Features**
- **Settlements** - Settlement transactions
- **Stablecoin Conversion** - Stablecoin operations
- **Taxes** - Tax forms and tax lots
- **Rewards** - Monitoring addresses, statements, payments
- **Sandbox** - Sandbox-specific operations

## Authentication

The API uses **OAuth 2.0** with **client credentials** grant flow:

- **Production**: `https://oauth.paxos.com/oauth2/token`
- **Sandbox**: `https://oauth.sandbox.paxos.com/oauth2/token`

### Required Scopes

Scopes are required for different operations. Common scopes include:
- `identity:write_identity` - Create/update identities
- `identity:read_identity` - Read identity details
- `identity:write_account` - Create/update accounts
- `identity:read_account` - Read account details
- `transfer:write_fiat_account` - Create fiat accounts
- `transfer:read_fiat_account` - Read fiat accounts
- `transfer:write_fiat_deposit_instructions` - Create deposit instructions
- `transfer:read_fiat_deposit_instructions` - Read deposit instructions
- `events:read_event` - Read events
- And many more...

## Current Implementation Status

### ✅ Fully Implemented

#### Identity Management
- ✅ `POST /v2/identity/identities` - Create identity
- ✅ `GET /v2/identity/identities` - List identities
- ✅ `GET /v2/identity/identities/{id}` - Get identity
- ✅ `PUT /v2/identity/identities/{id}/sandbox-status` - Approve identity (sandbox)

#### Account Management
- ✅ `POST /v2/identity/accounts` - Create account (with optional profile)
- ✅ `GET /v2/identity/accounts` - List accounts

#### Fiat Operations
- ✅ `POST /v2/transfer/fiat-accounts` - Create fiat account
- ✅ `GET /v2/transfer/fiat-accounts/{id}` - Get fiat account
- ✅ `POST /v2/transfer/fiat-deposit-instructions` - Create deposit instruction
- ✅ `POST /v2/sandbox/fiat-deposits` - Create sandbox fiat deposit

#### Transfers
- ✅ `GET /v2/transfer/transfers` - Get transfers (by profile IDs)

#### Events
- ✅ `GET /v2/events` - List events (with filtering)
- ✅ `GET /v2/events/{id}` - Get event details

#### Identity Documents
- ✅ `PUT /v2/identity/identities/{id}/documents` - Request document upload URL
- ✅ `PUT {upload_url}` - Upload document
- ✅ `GET /v2/identity/identities/{id}/documents` - List identity documents

### ⚠️ Partially Implemented

#### Profiles
- ⚠️ Profiles are created automatically when creating accounts
- ❌ `GET /v2/funding/profiles` - List profiles (not implemented)
- ❌ `GET /v2/funding/profiles/{id}` - Get profile details (not implemented)
- ❌ `POST /v2/funding/profiles` - Create profile (not implemented)

### ❌ Not Yet Implemented

#### Account Management
- ❌ `GET /v2/identity/accounts/{id}` - Get account details
- ❌ `PUT /v2/identity/accounts/{id}` - Update account
- ❌ Account Members endpoints (joint accounts)

#### Institution Management
- ❌ Institution Members endpoints
- ❌ Institution identity creation

#### Trading Operations
- ❌ `GET /v2/exchange/quotes` - List quotes
- ❌ `POST /v2/exchange/quote-executions` - Create quote execution
- ❌ `GET /v2/exchange/quote-executions` - List quote executions
- ❌ `GET /v2/exchange/quote-executions/{id}` - Get quote execution
- ❌ `POST /v2/exchange/orders` - Create order
- ❌ `DELETE /v2/exchange/orders/{id}` - Cancel order
- ❌ `GET /v2/exchange/orders` - List orders
- ❌ `GET /v2/exchange/orders/{id}` - Get order
- ❌ Market Data endpoints
- ❌ Pricing endpoints
- ❌ Issuer Quotes endpoints

#### Crypto Operations
- ❌ `POST /v2/transfer/deposit-addresses` - Create deposit address
- ❌ `GET /v2/transfer/deposit-addresses` - List deposit addresses
- ❌ `GET /v2/transfer/deposit-addresses/{id}` - Get deposit address
- ❌ `POST /v2/transfer/crypto-withdrawals` - Create crypto withdrawal
- ❌ `GET /v2/transfer/crypto-withdrawals` - List crypto withdrawals
- ❌ `GET /v2/transfer/crypto-withdrawals/{id}` - Get crypto withdrawal
- ❌ `PUT /v2/transfer/crypto-deposits/{id}` - Update crypto deposit (travel rule)
- ❌ `POST /v2/transfer/crypto-deposits/{id}/reject` - Reject crypto deposit

#### Fiat Operations (Additional)
- ❌ `GET /v2/transfer/fiat-deposit-instructions` - List deposit instructions
- ❌ `GET /v2/transfer/fiat-deposit-instructions/{id}` - Get deposit instruction
- ❌ `PUT /v2/transfer/fiat-deposit-instructions/{id}` - Update deposit instruction
- ❌ `DELETE /v2/transfer/fiat-deposit-instructions/{id}` - Delete deposit instruction
- ❌ `POST /v2/transfer/fiat-withdrawals` - Create fiat withdrawal
- ❌ `GET /v2/transfer/fiat-withdrawals` - List fiat withdrawals
- ❌ `GET /v2/transfer/fiat-withdrawals/{id}` - Get fiat withdrawal

#### Internal Transfers
- ❌ `POST /v2/transfer/internal-transfers` - Create internal transfer

#### Limits
- ❌ `GET /v2/transfer/transfer-limits` - Get transfer limits

#### Settlements
- ❌ Settlement transaction endpoints

#### Stablecoin Conversion
- ❌ Stablecoin conversion endpoints

#### Taxes
- ❌ Tax forms endpoints
- ❌ Tax lots endpoints

## Key API Patterns

### 1. Quote Flow (Trading)
```
1. GET /v2/exchange/quotes - Get available quotes
2. Present prices to users with expiration timer
3. POST /v2/exchange/quote-executions - Execute quote when user accepts
4. GET /v2/exchange/quote-executions/{id} - Monitor completion
```

### 2. Order Flow (Trading)
```
1. POST /v2/exchange/orders - Create order (market/limit/post-only)
2. GET /v2/exchange/orders/{id} - Monitor order status
3. DELETE /v2/exchange/orders/{id} - Cancel if needed
```

### 3. Fiat Deposit Flow
```
1. POST /v2/transfer/fiat-accounts - Create fiat account
2. POST /v2/transfer/fiat-deposit-instructions - Create deposit instruction
3. User deposits funds using instruction details
4. Monitor via events or GET /v2/transfer/transfers
```

### 4. Crypto Deposit Flow
```
1. POST /v2/transfer/deposit-addresses - Create deposit address
2. User sends crypto to address
3. Monitor via events or GET /v2/transfer/crypto-deposits
```

### 5. Crypto Withdrawal Flow
```
1. POST /v2/transfer/crypto-withdrawals - Create withdrawal
2. GET /v2/transfer/crypto-withdrawals/{id} - Monitor status
```

## Error Handling

The API uses standard HTTP status codes and Problem Details (RFC 7807) format:

- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden (e.g., market conditions, limits exceeded)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error
- `503` - Service Unavailable
- `429` - Too Many Requests

Common problem types:
- `market-conditions-prevented-execution`
- `minimum-commission-exceeds-notional`
- `notional-value-too-large`
- `unavailable-market`
- `rejected`

## Rate Limiting

- API has rate limits (returns `429 Too Many Requests` when exceeded)
- Quote prices should be refreshed at least once per second if cached

## Sandbox vs Production

- **Sandbox**: `https://api.sandbox.paxos.com/v2`
- **Production**: `https://api.paxos.com/v2`

Sandbox-specific features:
- Identity approval (`PUT /v2/identity/identities/{id}/sandbox-status`)
- Sandbox fiat deposits (`POST /v2/sandbox/fiat-deposits`)
- Test events (`is_test` filter in events)

## Recommendations for Next Steps

### High Priority
1. **Profile Management** - Implement profile listing and details retrieval
2. **Account Details** - Implement GET account endpoint
3. **Fiat Deposit Instructions** - Implement list/get/update/delete endpoints
4. **Crypto Operations** - Implement deposit addresses and withdrawals

### Medium Priority
1. **Trading Operations** - If you need trading functionality:
   - Quotes and Quote Executions
   - Orders (market, limit, post-only)
   - Market Data
2. **Internal Transfers** - Transfer assets between profiles
3. **Transfer Limits** - Check limits before operations

### Low Priority
1. **Account Members** - Joint account support
2. **Institution Management** - If you need institution identities
3. **Settlements** - If you need settlement transactions
4. **Taxes** - Tax reporting features

## Notes

- All transactions are associated with a Profile
- Profiles hold asset balances
- Accounts connect Identities to Profiles
- Multiple Identities can share an Account (joint accounts)
- Events are the primary way to monitor asynchronous operations
- Document uploads use a two-step process (request URL, then PUT to URL)
