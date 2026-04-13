<?php

namespace App\Console\Commands;

use App\Services\PaxosEventProcessor;
use App\Services\PaxosService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PollPaxosEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paxos:poll-events
                            {--type= : Filter by specific event type (e.g., identity.documents_required)}
                            {--since= : Poll events since this date (RFC3339 format, defaults to last poll time)}
                            {--limit=100 : Maximum number of events per poll}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Paxos events and process them. Designed to run as a scheduled command.';

    /**
     * Execute the console command.
     */
    public function handle(PaxosService $paxosService, PaxosEventProcessor $eventProcessor): int
    {
        $this->info('Polling Paxos events...');

        // Get last processed timestamp
        $lastProcessedKey = 'paxos:last_event_processed_at';
        $lastProcessedAt = Cache::get($lastProcessedKey);

        // If --since option is provided, use it; otherwise use last processed time or default to 24 hours ago
        if ($this->option('since')) {
            $since = $this->option('since');
        } elseif ($lastProcessedAt) {
            $since = $lastProcessedAt;
        } else {
            // Default to 24 hours ago for first run
            $since = now()->subDay()->toIso8601String();
            $this->info("No previous poll time found, using 24 hours ago: {$since}");
        }

        // Build filters
        $filters = [
            'created_at.gt' => $since,
            'limit' => (int) $this->option('limit'),
        ];

        if ($this->option('type')) {
            $filters['type'] = $this->option('type');
            $this->info("Filtering by type: {$filters['type']}");
        }

        try {
            $events = $paxosService->listEvents($filters);

            if (empty($events)) {
                $this->info('No new events found.');
                $this->newLine();

                return Command::SUCCESS;
            }

            $this->info("Found ".count($events)." new event(s)");
            $this->newLine();

            $processedCount = 0;
            $failedCount = 0;
            $latestCreatedAt = $since;

            // Process each event
            foreach ($events as $event) {
                $eventId = $event['id'] ?? 'unknown';
                $eventType = $event['type'] ?? 'unknown';
                $createdAt = $event['created_at'] ?? null;

                $this->line("Processing: {$eventType} (ID: ".substr($eventId, 0, 20).'...)');

                try {
                    $processed = $eventProcessor->processEvent($event);

                    if ($processed) {
                        $processedCount++;
                        $this->info("  ✓ Processed successfully");

                        // Track latest created_at for next poll
                        if ($createdAt && $createdAt > $latestCreatedAt) {
                            $latestCreatedAt = $createdAt;
                        }
                    } else {
                        $failedCount++;
                        $this->warn("  ✗ Failed to process");
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $this->error("  ✗ Error: ".$e->getMessage());
                    Log::error('Error processing event in poll command', [
                        'event_id' => $eventId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update last processed timestamp
            // Use the latest created_at from the events we just processed
            if ($latestCreatedAt !== $since) {
                Cache::put($lastProcessedKey, $latestCreatedAt, now()->addDays(7));
                $this->info("Updated last processed timestamp: {$latestCreatedAt}");
            }

            // Summary
            $this->newLine();
            $this->info('Summary:');
            $this->line("  Total events: ".count($events));
            $this->line("  Processed: {$processedCount}");
            if ($failedCount > 0) {
                $this->warn("  Failed: {$failedCount}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to poll events: '.$e->getMessage());
            Log::error('Paxos event polling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
