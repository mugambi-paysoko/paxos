<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaxosWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaxosWebhookController extends Controller
{
    public function __invoke(Request $request): Response|JsonResponse
    {
        $secret = config('services.paxos.webhook_secret');
        $headerName = (string) config('services.paxos.webhook_header', 'X-Paxos-Webhook-Key');

        if (($secret === null || $secret === '') && ! app()->environment('local', 'testing')) {
            Log::error('Paxos webhook received but PAXOS_WEBHOOK_SECRET is not configured');

            return response('Webhook not configured', 503);
        }

        if (! $this->webhookKeyIsValid($request, $headerName, $secret)) {
            return response('Unauthorized', 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (! is_array($payload)) {
            return response('Bad Request', 400);
        }

        if (empty($payload['id']) || empty($payload['type'])) {
            return response('Bad Request', 400);
        }

        if (! empty($payload['is_test'])) {
            Log::info('Paxos test webhook ignored', [
                'id' => $payload['id'],
                'type' => $payload['type'],
            ]);

            return response()->noContent(200);
        }

        Log::info('Paxos webhook accepted, processing after HTTP response', [
            'event_id' => $payload['id'],
            'type' => $payload['type'],
        ]);

        // Use after-response dispatch so Paxos gets 200 immediately, then the job runs in-process
        // via the sync connection (no separate `queue:work` required). Regular `dispatch()` would
        // leave jobs stuck on the database/redis queue when no worker is running.
        ProcessPaxosWebhookJob::dispatchAfterResponse($payload);

        return response()->noContent(200);
    }

    private function webhookKeyIsValid(Request $request, string $headerName, mixed $secret): bool
    {
        if ($secret === null || $secret === '') {
            Log::warning('Paxos webhook accepted without secret (local/testing only)');

            return true;
        }

        $sent = $request->header($headerName);

        if (! is_string($sent) || $sent === '') {
            return false;
        }

        return hash_equals((string) $secret, $sent);
    }
}
