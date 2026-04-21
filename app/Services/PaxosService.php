<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaxosService
{
    private string $baseUrl;

    private ?string $apiToken = null;

    public function __construct()
    {
        $this->baseUrl = config('services.paxos.base_url', 'https://api.sandbox.paxos.com');
        // Don't initialize apiToken from config - we'll authenticate fresh each time
    }

    /**
     * Get OAuth URL based on environment
     */
    private function getOAuthUrl(): string
    {
        $baseUrl = $this->baseUrl;
        if (str_contains($baseUrl, 'sandbox')) {
            return 'https://oauth.sandbox.paxos.com/oauth2/token';
        }

        return 'https://oauth.paxos.com/oauth2/token';
    }

    /**
     * Authenticate and get API token using credentials from .env
     *
     * @return string The access token
     *
     * @throws \Exception If authentication fails
     */
    public function authenticate(): string
    {
        $clientId = config('services.paxos.client_id');
        $clientSecret = config('services.paxos.client_secret');
        $scope = config('services.paxos.scope');

        // Validate that credentials are set
        if (empty($clientId) || empty($clientSecret)) {
            throw new \Exception('Paxos credentials not configured. Please set PAXOS_CLIENT_ID and PAXOS_CLIENT_SECRET in your .env file.');
        }

        try {
            $oauthUrl = $this->getOAuthUrl();

            Log::info('Authenticating with Paxos', [
                'url' => $oauthUrl,
                'client_id' => $clientId,
                'has_scope' => ! empty($scope),
            ]);

            $response = Http::asForm()->post($oauthUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => $scope,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if (! isset($responseData['access_token'])) {
                    Log::error('Paxos authentication response missing access_token', [
                        'response' => $responseData,
                    ]);
                    throw new \Exception('Paxos authentication failed: access_token not found in response');
                }

                $this->apiToken = $responseData['access_token'];
                Log::info('Paxos authentication successful', [
                    'token_length' => strlen($this->apiToken),
                    'expires_in' => $responseData['expires_in'] ?? 'unknown',
                ]);

                return $this->apiToken;
            }

            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos authentication failed', [
                'url' => $oauthUrl,
                'status' => $response->status(),
                'response' => $errorBody,
            ]);

            throw new \Exception('Paxos authentication failed. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        } catch (\Exception $e) {
            // If it's already our custom exception, re-throw it
            if (str_contains($e->getMessage(), 'Paxos authentication failed') || str_contains($e->getMessage(), 'Paxos credentials not configured')) {
                throw $e;
            }

            Log::error('Paxos authentication error', ['error' => $e->getMessage()]);
            throw new \Exception('Paxos authentication error: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Ensure we have a valid API token by authenticating with Paxos
     * This will always authenticate fresh using credentials from .env
     *
     * @throws \Exception If authentication fails
     */
    private function ensureAuthenticated(): void
    {
        // Always authenticate fresh to ensure we have a valid token
        // Tokens expire after ~1 hour, so this ensures we always have a valid one
        $this->authenticate();
    }

    /**
     * Create identity
     * This will first authenticate with Paxos using credentials from .env
     */
    public function createIdentity(array $data): array
    {
        // Authenticate first using credentials from .env
        $this->ensureAuthenticated();

        // Log the request body before sending
        Log::info('Creating identity in Paxos - Request Body', [
            'url' => "{$this->baseUrl}/v2/identity/identities",
            'request_body' => $data,
            'request_body_json' => json_encode($data, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post("{$this->baseUrl}/v2/identity/identities", $data);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos create identity failed', [
                'status' => $response->status(),
                'request_body' => $data,
                'request_body_json' => json_encode($data, JSON_PRETTY_PRINT),
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to create identity in Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Validate that we got a successful response with an ID
        if (! isset($responseData['id'])) {
            Log::error('Paxos create identity returned invalid response', [
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing identity ID. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Get identities
     */
    public function getIdentities(): array
    {
        $this->ensureAuthenticated();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get("{$this->baseUrl}/v2/identity/identities");

        return $response->json();
    }

    /**
     * Get a single identity by ID
     */
    public function getIdentity(string $identityId): array
    {
        $this->ensureAuthenticated();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get("{$this->baseUrl}/v2/identity/identities/{$identityId}");

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos get identity failed', [
                'status' => $response->status(),
                'identity_id' => $identityId,
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to get identity from Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        if (! isset($responseData['id'])) {
            Log::error('Paxos get identity returned invalid response', [
                'identity_id' => $identityId,
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing identity ID. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Approve identity (sandbox)
     */
    public function approveIdentity(string $identityId, array $data): array
    {
        $this->ensureAuthenticated();
        Log::info('Approving identity in Paxos - Request Body', [
            'url' => "{$this->baseUrl}/v2/identity/identities/{$identityId}/sandbox-status",
            'request_body' => $data,
            'request_body_json' => json_encode($data, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->put("{$this->baseUrl}/v2/identity/identities/{$identityId}/sandbox-status", $data);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos approve identity failed', [
                'status' => $response->status(),
                'identity_id' => $identityId,
                'request_body' => $data,
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to approve identity in Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Log the full response
        Log::info('Paxos approve identity response', [
            'identity_id' => $identityId,
            'status_code' => $response->status(),
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        return $responseData;
    }

    /**
     * Create account
     */
    public function createAccount(array $data): array
    {
        $this->ensureAuthenticated();

        // Ensure the request body matches the expected format:
        // {
        //     "create_profile": true,
        //     "account": {
        //         "identity_id": "...",
        //         "ref_id": "...",
        //         "type": "BROKERAGE",
        //         "description": "..."
        //     }
        // }
        $requestBody = [
            'create_profile' => $data['create_profile'] ?? false,
            'account' => [
                'identity_id' => $data['account']['identity_id'] ?? null,
                'ref_id' => $data['account']['ref_id'] ?? null,
                'type' => $data['account']['type'] ?? 'BROKERAGE',
                'description' => $data['account']['description'] ?? null,
            ],
        ];

        // Validate required fields
        if (empty($requestBody['account']['identity_id'])) {
            throw new \Exception('identity_id is required in account data');
        }
        if (empty($requestBody['account']['ref_id'])) {
            throw new \Exception('ref_id is required in account data');
        }

        // Log the request body before sending
        Log::info('Creating account in Paxos - Request Body', [
            'url' => "{$this->baseUrl}/v2/identity/accounts",
            'request_body' => $requestBody,
            'request_body_json' => json_encode($requestBody, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post("{$this->baseUrl}/v2/identity/accounts", $requestBody);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos create account failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to create account in Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Log the response for debugging
        Log::info('Paxos create account response', [
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        // The response structure has account data at the top level, not nested under 'account'
        // Response format: {"id": "...", "identity_id": "...", "type": "...", ...}
        if (! isset($responseData['id'])) {
            Log::error('Paxos create account returned invalid response', [
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing account ID. Response: '.json_encode($responseData));
        }

        // Transform response to match expected format with nested 'account' key for consistency
        // This allows callers to access responseData['account']['id'] if needed
        return [
            'account' => [
                'id' => $responseData['id'],
                'identity_id' => $responseData['identity_id'] ?? null,
                'ref_id' => $responseData['ref_id'] ?? null,
                'type' => $responseData['type'] ?? null,
                'description' => $responseData['description'] ?? null,
                'summary_status' => $responseData['summary_status'] ?? null,
                'created_at' => $responseData['created_at'] ?? null,
                'updated_at' => $responseData['updated_at'] ?? null,
            ],
            'profile' => isset($responseData['profile_id']) ? [
                'id' => $responseData['profile_id'],
            ] : null,
            // Include full response for reference
            '_raw' => $responseData,
        ];
    }

    /**
     * Get accounts
     */
    public function getAccounts(): array
    {
        $this->ensureAuthenticated();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get("{$this->baseUrl}/v2/identity/accounts");

        return $response->json();
    }

    /**
     * Create fiat account
     * Request body format:
     * {
     *   "fiat_network_instructions": {
     *     "wire": {
     *       "account_number": "...",
     *       "routing_details": {...},
     *       "fiat_account_owner_address": {...}
     *     }
     *   },
     *   "fiat_account_owner": {
     *     "person_details": {...}
     *   }
     * }
     */
    public function createFiatAccount(array $data): array
    {
        $this->ensureAuthenticated();

        // Log the request body before sending
        Log::info('Creating fiat account in Paxos - Request Body', [
            'url' => "{$this->baseUrl}/v2/transfer/fiat-accounts",
            'request_body' => $data,
            'request_body_json' => json_encode($data, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post("{$this->baseUrl}/v2/transfer/fiat-accounts", $data);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos create fiat account failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to create fiat account in Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Log the response for debugging
        Log::info('Paxos create fiat account response', [
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        // Validate that we got a successful response with an ID
        if (! isset($responseData['id'])) {
            Log::error('Paxos create fiat account returned invalid response', [
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing fiat account ID. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Create fiat withdrawal.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function createFiatWithdrawal(array $data): array
    {
        $this->ensureAuthenticated();

        $requestBody = [
            'asset' => $data['asset'] ?? 'USD',
            'fiat_account_id' => $data['fiat_account_id'] ?? null,
            'profile_id' => $data['profile_id'] ?? null,
        ];

        if (! empty($data['ref_id'])) {
            $requestBody['ref_id'] = $data['ref_id'];
        }
        if (! empty($data['amount'])) {
            $requestBody['amount'] = $data['amount'];
        }
        if (! empty($data['total'])) {
            $requestBody['total'] = $data['total'];
        }
        if (! empty($data['memo'])) {
            $requestBody['memo'] = $data['memo'];
        }
        if (! empty($data['metadata']) && is_array($data['metadata'])) {
            $requestBody['metadata'] = $data['metadata'];
        }
        if (! empty($data['identity_id'])) {
            $requestBody['identity_id'] = $data['identity_id'];
        }
        if (! empty($data['account_id'])) {
            $requestBody['account_id'] = $data['account_id'];
        }

        if (empty($requestBody['fiat_account_id']) || empty($requestBody['profile_id'])) {
            throw new \Exception('fiat_account_id and profile_id are required for fiat withdrawal');
        }
        if ((empty($requestBody['amount']) && empty($requestBody['total'])) || (! empty($requestBody['amount']) && ! empty($requestBody['total']))) {
            throw new \Exception('Specify exactly one of amount or total for fiat withdrawal');
        }

        $url = "{$this->baseUrl}/v2/transfer/fiat-withdrawals";

        Log::info('Creating fiat withdrawal in Paxos - Request Body', [
            'url' => $url,
            'request_body' => $requestBody,
            'request_body_json' => json_encode($requestBody, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post($url, $requestBody);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos create fiat withdrawal failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to create fiat withdrawal in Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        Log::info('Paxos create fiat withdrawal response', [
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        if (! isset($responseData['id'])) {
            throw new \Exception('Paxos API returned invalid response: missing transfer id for fiat withdrawal. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Create crypto withdrawal.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function createCryptoWithdrawal(array $data): array
    {
        $this->ensureAuthenticated();

        $requestBody = [
            'profile_id' => $data['profile_id'] ?? null,
            'asset' => $data['asset'] ?? null,
            'destination_address' => $data['destination_address'] ?? null,
            'crypto_network' => $data['crypto_network'] ?? null,
        ];

        if (! empty($data['ref_id'])) {
            $requestBody['ref_id'] = $data['ref_id'];
        }
        if (! empty($data['amount'])) {
            $requestBody['amount'] = $data['amount'];
        }
        if (! empty($data['total'])) {
            $requestBody['total'] = $data['total'];
        }
        if (! empty($data['balance_asset'])) {
            $requestBody['balance_asset'] = $data['balance_asset'];
        }
        if (! empty($data['metadata']) && is_array($data['metadata'])) {
            $requestBody['metadata'] = $data['metadata'];
        }
        if (! empty($data['beneficiary']) && is_array($data['beneficiary'])) {
            $requestBody['beneficiary'] = $data['beneficiary'];
        }
        if (! empty($data['memo'])) {
            $requestBody['memo'] = $data['memo'];
        }
        if (! empty($data['fee_id'])) {
            $requestBody['fee_id'] = $data['fee_id'];
        }
        if (! empty($data['identity_id'])) {
            $requestBody['identity_id'] = $data['identity_id'];
        }
        if (! empty($data['account_id'])) {
            $requestBody['account_id'] = $data['account_id'];
        }

        if (
            empty($requestBody['profile_id']) ||
            empty($requestBody['asset']) ||
            empty($requestBody['destination_address']) ||
            empty($requestBody['crypto_network'])
        ) {
            throw new \Exception('profile_id, asset, destination_address and crypto_network are required for crypto withdrawal');
        }

        if (
            (empty($requestBody['amount']) && empty($requestBody['total'])) ||
            (! empty($requestBody['amount']) && ! empty($requestBody['total']))
        ) {
            throw new \Exception('Specify exactly one of amount or total for crypto withdrawal');
        }

        $url = "{$this->baseUrl}/v2/transfer/crypto-withdrawals";

        Log::info('Creating crypto withdrawal in Paxos - Request Body', [
            'url' => $url,
            'request_body' => $requestBody,
            'request_body_json' => json_encode($requestBody, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post($url, $requestBody);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos create crypto withdrawal failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to create crypto withdrawal in Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        Log::info('Paxos create crypto withdrawal response', [
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        if (! isset($responseData['id'])) {
            throw new \Exception('Paxos API returned invalid response: missing transfer id for crypto withdrawal. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Create a crypto deposit address for a profile (POST /v2/transfer/deposit-addresses).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function createDepositAddress(array $data): array
    {
        $this->ensureAuthenticated();

        $requestBody = [
            'profile_id' => $data['profile_id'] ?? null,
            'crypto_network' => $data['crypto_network'] ?? null,
        ];

        if (! empty($data['identity_id'])) {
            $requestBody['identity_id'] = $data['identity_id'];
        }
        if (! empty($data['ref_id'])) {
            $requestBody['ref_id'] = $data['ref_id'];
        }
        if (! empty($data['account_id'])) {
            $requestBody['account_id'] = $data['account_id'];
        }
        if (! empty($data['conversion_target_asset'])) {
            $requestBody['conversion_target_asset'] = $data['conversion_target_asset'];
        }
        if (! empty($data['metadata']) && is_array($data['metadata'])) {
            $requestBody['metadata'] = $data['metadata'];
        }

        if (empty($requestBody['profile_id']) || empty($requestBody['crypto_network'])) {
            throw new \Exception('profile_id and crypto_network are required to create a deposit address');
        }

        $url = "{$this->baseUrl}/v2/transfer/deposit-addresses";

        Log::info('Creating deposit address in Paxos - Request Body', [
            'url' => $url,
            'request_body' => $requestBody,
            'request_body_json' => json_encode($requestBody, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post($url, $requestBody);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos create deposit address failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to create deposit address in Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        Log::info('Paxos create deposit address response', [
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        if (! isset($responseData['id'], $responseData['address'])) {
            Log::error('Paxos create deposit address returned invalid response', [
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing deposit address id or address. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Get fiat account details by ID
     */
    public function getFiatAccount(string $fiatAccountId): array
    {
        $this->ensureAuthenticated();

        Log::info('Getting fiat account from Paxos', [
            'url' => "{$this->baseUrl}/v2/transfer/fiat-accounts/{$fiatAccountId}",
            'fiat_account_id' => $fiatAccountId,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get("{$this->baseUrl}/v2/transfer/fiat-accounts/{$fiatAccountId}");

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos get fiat account failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to get fiat account from Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Log the response for debugging
        Log::info('Paxos get fiat account response', [
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        // Validate that we got a successful response with an ID
        if (! isset($responseData['id'])) {
            Log::error('Paxos get fiat account returned invalid response', [
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing fiat account ID. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Create fiat deposit instruction
     * Request body format:
     * {
     *   "profile_id": "...",
     *   "fiat_network": "CUBIX" | "WIRE",
     *   "account_id": "...",
     *   "ref_id": "...",
     *   "metadata": {...}
     * }
     */
    public function createFiatDepositInstruction(array $data): array
    {
        $this->ensureAuthenticated();

        // Log the request body before sending
        Log::info('Creating fiat deposit instruction in Paxos - Request Body', [
            'url' => "{$this->baseUrl}/v2/transfer/fiat-deposit-instructions",
            'request_body' => $data,
            'request_body_json' => json_encode($data, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post("{$this->baseUrl}/v2/transfer/fiat-deposit-instructions", $data);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos create fiat deposit instruction failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to create fiat deposit instruction in Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Log the response for debugging
        Log::info('Paxos create fiat deposit instruction response', [
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        // Validate that we got a successful response with an ID
        if (! isset($responseData['id'])) {
            Log::error('Paxos create fiat deposit instruction returned invalid response', [
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing deposit instruction ID. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Create sandbox fiat deposit
     * Request body format:
     * {
     *   "amount": "1000.00",
     *   "asset": "USD",
     *   "memo_id": "...",
     *   "fiat_network_instructions": {...},
     *   "fiat_account_owner": {...}
     * }
     */
    public function createSandboxFiatDeposit(array $data): array
    {
        $this->ensureAuthenticated();

        // Log the request body before sending
        Log::info('Creating sandbox fiat deposit in Paxos - Request Body', [
            'url' => "{$this->baseUrl}/v2/sandbox/fiat-deposits",
            'request_body' => $data,
            'request_body_json' => json_encode($data, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->post("{$this->baseUrl}/v2/sandbox/fiat-deposits", $data);

        $statusCode = $response->status();
        $responseBody = $response->body();
        $responseData = $response->json();

        // Log the full response for debugging
        Log::info('Paxos create sandbox fiat deposit response', [
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'response_body_length' => strlen($responseBody),
            'response_data' => $responseData,
            'response_data_type' => gettype($responseData),
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        if ($response->failed()) {
            Log::error('Paxos create sandbox fiat deposit failed', [
                'status' => $statusCode,
                'response_body' => $responseBody,
                'response_data' => $responseData,
            ]);
            throw new \Exception('Failed to create sandbox fiat deposit in Paxos API. Status: '.$statusCode.'. Response: '.$responseBody);
        }

        // Handle successful responses (200-299)
        // Some APIs return empty arrays or null for successful operations
        // If we get a 2xx status but empty response, consider it successful
        if ($statusCode >= 200 && $statusCode < 300) {
            // If response is empty array or null, return a success response with the request data
            if (empty($responseData) || $responseData === null || (is_array($responseData) && count($responseData) === 0)) {
                Log::warning('Paxos create sandbox fiat deposit returned empty response but status is success', [
                    'status_code' => $statusCode,
                    'response_body' => $responseBody,
                ]);

                // Return a success response with the request data
                // Don't set 'id' to 'pending' - use null instead to avoid unique constraint violations
                return [
                    'id' => null, // API might not return ID immediately - set to null to avoid unique constraint issues
                    'status' => 'PENDING',
                    'amount' => $data['amount'] ?? null,
                    'asset' => $data['asset'] ?? null,
                    'memo_id' => $data['memo_id'] ?? null,
                    '_note' => 'API returned empty response but status code indicates success',
                ];
            }

            // If we have data but no ID, check if it's a different structure
            if (! isset($responseData['id']) && is_array($responseData) && ! empty($responseData)) {
                // Maybe the response structure is different - return what we got
                Log::info('Paxos create sandbox fiat deposit response has data but no ID field', [
                    'response_data' => $responseData,
                ]);

                return $responseData;
            }

            // Normal case: we have an ID
            if (isset($responseData['id'])) {
                return $responseData;
            }
        }

        // If we get here, something unexpected happened
        Log::error('Paxos create sandbox fiat deposit returned unexpected response', [
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'response_data' => $responseData,
        ]);
        throw new \Exception('Paxos API returned unexpected response. Status: '.$statusCode.'. Response: '.$responseBody);
    }

    /**
     * List balances for a Paxos profile.
     * GET /v2/profiles/{profile_id}/balances
     *
     * @param  list<string>|null  $assets  When set, only these assets are returned; omit for all balances.
     * @return list<array{asset: string, available: string, trading: string}>
     *
     * @throws \Exception
     */
    public function listProfileBalances(string $profileId, ?array $assets = null): array
    {
        $this->ensureAuthenticated();

        $url = "{$this->baseUrl}/v2/profiles/{$profileId}/balances";
        if ($assets !== null && $assets !== []) {
            $url .= '?'.collect($assets)->map(fn (string $a) => 'assets='.rawurlencode($a))->implode('&');
        }

        Log::info('Listing profile balances from Paxos', [
            'url' => $url,
            'profile_id' => $profileId,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get($url);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos list profile balances failed', [
                'status' => $response->status(),
                'profile_id' => $profileId,
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to list profile balances from Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();
        if (! is_array($responseData)) {
            return [];
        }

        $items = $responseData['items'] ?? $responseData['balances'] ?? [];

        return is_array($items) ? $items : [];
    }

    /**
     * Get transfers for a profile
     * GET /v2/transfer/transfers?profile_ids={profile_id}
     */
    public function getTransfers(array $profileIds): array
    {
        $this->ensureAuthenticated();

        // Build query string with profile IDs
        $profileIdsString = is_array($profileIds) ? implode(',', $profileIds) : $profileIds;

        $url = "{$this->baseUrl}/v2/transfer/transfers?profile_ids={$profileIdsString}";

        Log::info('Getting transfers from Paxos', [
            'url' => $url,
            'profile_ids' => $profileIds,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get($url);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos get transfers failed', [
                'status' => $response->status(),
                'profile_ids' => $profileIds,
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to get transfers from Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Log the response for debugging
        Log::info('Paxos get transfers response', [
            'profile_ids' => $profileIds,
            'response_structure' => is_array($responseData) ? array_keys($responseData) : 'not_array',
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        // The API returns an object with 'items' array, not a direct array
        if (is_array($responseData) && isset($responseData['items'])) {
            return $responseData['items'];
        }

        // Fallback: if it's already an array, return it
        if (is_array($responseData)) {
            return $responseData;
        }

        // If it's not an array, return empty array
        return [];
    }

    /**
     * List events from Paxos
     * Supports filtering by type, created_at, etc.
     *
     * @param  array<string, mixed>  $filters  Optional filters like:
     *                                         - 'type' => 'identity.documents_required'
     *                                         - 'created_at.gt' => '2025-01-01T00:00:00Z' (strictly greater than)
     *                                         - 'created_at.gte' => '2025-01-01T00:00:00Z' (greater than or equal)
     *                                         - 'created_at.lt' => '2025-01-01T00:00:00Z' (strictly less than)
     *                                         - 'created_at.lte' => '2025-01-01T00:00:00Z' (less than or equal)
     *                                         - 'created_at.eq' => '2025-01-01T00:00:00Z' (exactly equal)
     *                                         - 'limit' => 100 (default: 100, max: 100)
     *                                         - 'is_test' => true/false (filter test events)
     * @return array<string, mixed> Array of events
     */
    public function listEvents(array $filters = []): array
    {
        $this->ensureAuthenticated();

        // Build query parameters
        $queryParams = [];

        if (isset($filters['type'])) {
            $queryParams['type'] = $filters['type'];
        }

        // Support all created_at operators
        $createdAtOperators = ['gt', 'gte', 'lt', 'lte', 'eq'];
        foreach ($createdAtOperators as $operator) {
            $key = "created_at.{$operator}";
            if (isset($filters[$key])) {
                $queryParams[$key] = $filters[$key];
            }
        }

        if (isset($filters['limit'])) {
            $queryParams['limit'] = $filters['limit'];
        }

        if (isset($filters['is_test'])) {
            $queryParams['is_test'] = $filters['is_test'] ? 'true' : 'false';
        }

        $url = "{$this->baseUrl}/v2/events";
        if (! empty($queryParams)) {
            $url .= '?'.http_build_query($queryParams);
        }

        Log::info('Listing events from Paxos', [
            'url' => $url,
            'filters' => $filters,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get($url);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos list events failed', [
                'status' => $response->status(),
                'filters' => $filters,
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to list events from Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Log the response for debugging
        Log::info('Paxos list events response', [
            'filters' => $filters,
            'response_structure' => is_array($responseData) ? array_keys($responseData) : 'not_array',
            'event_count' => is_array($responseData) && isset($responseData['events']) ? count($responseData['events']) : 0,
            'response' => $responseData,
        ]);

        // The API returns an object with 'events' array according to the documentation
        if (is_array($responseData) && isset($responseData['events'])) {
            return $responseData['events'];
        }

        // Fallback: if it's already an array, return it (for backwards compatibility)
        if (is_array($responseData)) {
            return $responseData;
        }

        // If it's not an array, return empty array
        return [];
    }

    /**
     * Get a single event by ID
     * This fetches the full event details (webhook payloads only contain minimal info)
     *
     * @param  string  $eventId  The event ID
     * @return array<string, mixed> Full event details
     */
    public function getEvent(string $eventId): array
    {
        $this->ensureAuthenticated();

        $url = "{$this->baseUrl}/v2/events/{$eventId}";

        Log::info('Getting event from Paxos', [
            'url' => $url,
            'event_id' => $eventId,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get($url);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos get event failed', [
                'status' => $response->status(),
                'event_id' => $eventId,
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to get event from Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        // Log the response for debugging
        Log::info('Paxos get event response', [
            'event_id' => $eventId,
            'response' => $responseData,
            'response_json' => json_encode($responseData, JSON_PRETTY_PRINT),
        ]);

        if (! isset($responseData['id'])) {
            Log::error('Paxos get event returned invalid response', [
                'event_id' => $eventId,
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing event ID. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Request document upload URL
     * PUT /v2/identity/identities/{identity_id}/documents
     *
     * @param  string  $identityId  The Paxos identity ID
     * @param  string  $fileName  The file name (e.g., "Proof_of_residence.jpg")
     * @param  array<string>  $documentTypes  Array of document types (e.g., ['PROOF_OF_RESIDENCY'])
     * @return array<string, mixed> Response with file_id, name, and upload_url
     */
    public function requestDocumentUploadUrl(string $identityId, string $fileName, array $documentTypes): array
    {
        $this->ensureAuthenticated();

        $requestBody = [
            'name' => $fileName,
            'document_types' => $documentTypes,
        ];

        Log::info('Requesting document upload URL from Paxos', [
            'url' => "{$this->baseUrl}/v2/identity/identities/{$identityId}/documents",
            'identity_id' => $identityId,
            'file_name' => $fileName,
            'document_types' => $documentTypes,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->put("{$this->baseUrl}/v2/identity/identities/{$identityId}/documents", $requestBody);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos request document upload URL failed', [
                'status' => $response->status(),
                'identity_id' => $identityId,
                'file_name' => $fileName,
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to request document upload URL from Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        Log::info('Paxos document upload URL response', [
            'identity_id' => $identityId,
            'response' => $responseData,
        ]);

        if (! isset($responseData['upload_url'])) {
            Log::error('Paxos request document upload URL returned invalid response', [
                'identity_id' => $identityId,
                'response' => $responseData,
            ]);
            throw new \Exception('Paxos API returned invalid response: missing upload_url. Response: '.json_encode($responseData));
        }

        return $responseData;
    }

    /**
     * Upload document to the provided upload URL
     *
     * @param  string  $uploadUrl  The upload URL from requestDocumentUploadUrl
     * @param  string  $filePath  Path to the file to upload
     * @return bool True if successful
     */
    public function uploadDocumentToUrl(string $uploadUrl, string $filePath): bool
    {
        if (! file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $fileSize = filesize($filePath);
        if ($fileSize > 100 * 1024 * 1024) { // 100 MB
            throw new \Exception("File size exceeds 100 MB limit: {$fileSize} bytes");
        }

        Log::info('Uploading document to Paxos', [
            'upload_url' => $uploadUrl,
            'file_path' => $filePath,
            'file_size' => $fileSize,
        ]);

        $fileContent = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $response = Http::withHeaders([
            'Content-Type' => $mimeType,
        ])->put($uploadUrl, $fileContent);

        if ($response->failed()) {
            Log::error('Paxos document upload failed', [
                'status' => $response->status(),
                'upload_url' => $uploadUrl,
                'file_path' => $filePath,
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to upload document to Paxos. Status: '.$response->status().'. Response: '.$response->body());
        }

        Log::info('Paxos document upload successful', [
            'upload_url' => $uploadUrl,
            'file_path' => $filePath,
            'status' => $response->status(),
        ]);

        return true;
    }

    /**
     * Upload a document for an identity
     * This is a convenience method that combines requestDocumentUploadUrl and uploadDocumentToUrl
     *
     * @param  string  $identityId  The Paxos identity ID
     * @param  string  $filePath  Path to the file to upload
     * @param  string  $fileName  The file name (e.g., "Proof_of_residence.jpg")
     * @param  array<string>  $documentTypes  Array of document types (e.g., ['PROOF_OF_RESIDENCY'])
     * @return array<string, mixed> Response from requestDocumentUploadUrl
     */
    public function uploadDocument(string $identityId, string $filePath, string $fileName, array $documentTypes): array
    {
        // Step 1: Request upload URL
        $uploadResponse = $this->requestDocumentUploadUrl($identityId, $fileName, $documentTypes);

        // Step 2: Upload the file
        $this->uploadDocumentToUrl($uploadResponse['upload_url'], $filePath);

        return $uploadResponse;
    }

    /**
     * List documents for an identity
     * GET /v2/identity/identities/{identity_id}/documents
     *
     * @param  string  $identityId  The Paxos identity ID
     * @return array<string, mixed> Array of document metadata
     */
    public function listIdentityDocuments(string $identityId): array
    {
        $this->ensureAuthenticated();

        $url = "{$this->baseUrl}/v2/identity/identities/{$identityId}/documents";

        Log::info('Listing identity documents from Paxos', [
            'url' => $url,
            'identity_id' => $identityId,
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiToken,
        ])->get($url);

        if ($response->failed()) {
            $errorBody = $response->json() ?? $response->body();
            Log::error('Paxos list identity documents failed', [
                'status' => $response->status(),
                'identity_id' => $identityId,
                'response' => $errorBody,
            ]);
            throw new \Exception('Failed to list identity documents from Paxos API. Status: '.$response->status().'. Response: '.json_encode($errorBody));
        }

        $responseData = $response->json();

        Log::info('Paxos list identity documents response', [
            'identity_id' => $identityId,
            'response' => $responseData,
        ]);

        // The API might return an array directly or wrapped in an object
        if (is_array($responseData) && isset($responseData['items'])) {
            return $responseData['items'];
        }

        if (is_array($responseData)) {
            return $responseData;
        }

        return [];
    }
}
