<?php

namespace App\Console\Commands;

use App\Services\PaxosService;
use Illuminate\Console\Command;

class PollPaxosEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paxos:events
                            {--type= : Filter by event type (e.g., identity.documents_required)}
                            {--created-after= : Filter events created after this date (RFC3339 format, e.g., 2025-01-01T00:00:00Z)}
                            {--limit=100 : Maximum number of events to retrieve}
                            {--details : Fetch full details for each event (slower but more complete)}
                            {--json : Output results as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll and display Paxos events. Useful for testing event polling functionality.';

    /**
     * Execute the console command.
     */
    public function handle(PaxosService $paxosService): int
    {
        $this->info('Polling Paxos events...');
        $this->newLine();

        // Build filters
        $filters = [];

        if ($this->option('type')) {
            $filters['type'] = $this->option('type');
            $this->info("Filtering by type: {$filters['type']}");
        }

        if ($this->option('created-after')) {
            $filters['created_at.gt'] = $this->option('created-after');
            $this->info("Filtering events created after: {$filters['created_at.gt']}");
        }

        if ($this->option('limit')) {
            $filters['limit'] = (int) $this->option('limit');
        }

        try {
            $events = $paxosService->listEvents($filters);

            if (empty($events)) {
                $this->warn('No events found matching the criteria.');
                return Command::SUCCESS;
            }

            $this->info("Found " . count($events) . " event(s)");
            $this->newLine();

            // Output as JSON if requested
            if ($this->option('json')) {
                $this->line(json_encode($events, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            }

            // Display events in a table
            $tableData = [];
            foreach ($events as $event) {
                $eventId = $event['id'] ?? 'N/A';
                $eventType = $event['type'] ?? 'N/A';
                $eventTime = $event['created_at'] ?? $event['time'] ?? 'N/A';
                $objectType = $event['object_type'] ?? 'N/A';
                $undeliveredWebhooks = $event['undelivered_webhooks'] ?? 0;

                // If --details option is set, fetch full event details
                if ($this->option('details') && isset($event['id'])) {
                    try {
                        $fullEvent = $paxosService->getEvent($event['id']);
                        $event = $fullEvent; // Use full event data
                    } catch (\Exception $e) {
                        $this->warn("Failed to fetch full details for event {$eventId}: ".$e->getMessage());
                    }
                }

                $tableData[] = [
                    'ID' => substr($eventId, 0, 20).'...',
                    'Type' => $eventType,
                    'Object Type' => $objectType,
                    'Created At' => $eventTime,
                    'Undelivered' => $undeliveredWebhooks,
                ];
            }

            $this->table(['ID', 'Type', 'Object Type', 'Created At', 'Undelivered'], $tableData);

            // Show summary
            $this->newLine();
            $this->info('Summary:');
            $this->line("  Total events: " . count($events));
            
            // Count by type
            $typeCounts = [];
            foreach ($events as $event) {
                $type = $event['type'] ?? 'unknown';
                $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
            }
            
            if (count($typeCounts) > 0) {
                $this->line("  Events by type:");
                foreach ($typeCounts as $type => $count) {
                    $this->line("    - {$type}: {$count}");
                }
            }

            // Show example event details if --details was used
            if ($this->option('details') && !empty($events)) {
                $this->newLine();
                $this->info('Example event (first event full details):');
                $this->line(json_encode($events[0], JSON_PRETTY_PRINT));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to poll events: ' . $e->getMessage());
            $this->newLine();
            $this->line('Error details:');
            $this->line($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
