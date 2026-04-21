<?php

namespace App\Jobs;

use App\Services\PaxosEventProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessPaxosWebhookJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $envelope
     */
    public function __construct(
        public array $envelope
    ) {}

    public function uniqueId(): string
    {
        return (string) ($this->envelope['id'] ?? '');
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(PaxosEventProcessor $eventProcessor): void
    {
        $ok = $eventProcessor->processEvent($this->envelope);

        if (! $ok) {
            Log::error('Paxos webhook job: event processing failed', [
                'envelope' => $this->envelope,
            ]);

            throw new \RuntimeException('Paxos event processing returned false for event '.($this->envelope['id'] ?? ''));
        }
    }
}
