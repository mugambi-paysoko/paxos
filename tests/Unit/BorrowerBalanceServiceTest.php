<?php

namespace Tests\Unit;

use App\Services\BorrowerBalanceService;
use Tests\TestCase;

class BorrowerBalanceServiceTest extends TestCase
{
    public function test_dashboard_card_sums_usdp_when_usd_row_is_zero(): void
    {
        $service = $this->app->make(BorrowerBalanceService::class);

        $card = $service->dashboardCardFromSummary([
            'has_profiles' => true,
            'aggregated' => [
                ['asset' => 'USD', 'available' => '0', 'trading' => '0', 'total' => '0'],
                ['asset' => 'USDP', 'available' => '396.7', 'trading' => '0', 'total' => '396.7'],
            ],
            'aggregated_non_zero' => [
                ['asset' => 'USDP', 'available' => '396.7', 'trading' => '0', 'total' => '396.7'],
            ],
            'by_profile' => [],
            'error' => null,
        ]);

        $this->assertSame('396.7', $card['value']);
        $this->assertSame('USD', $card['unit']);
        $this->assertStringContainsString('USD-stable', $card['caption']);
    }

    public function test_dashboard_card_shows_single_non_stable_asset_with_its_code(): void
    {
        $service = $this->app->make(BorrowerBalanceService::class);

        $card = $service->dashboardCardFromSummary([
            'has_profiles' => true,
            'aggregated' => [
                ['asset' => 'BTC', 'available' => '0.25', 'trading' => '0', 'total' => '0.25'],
            ],
            'aggregated_non_zero' => [
                ['asset' => 'BTC', 'available' => '0.25', 'trading' => '0', 'total' => '0.25'],
            ],
            'by_profile' => [],
            'error' => null,
        ]);

        $this->assertSame('0.25', $card['value']);
        $this->assertSame('BTC', $card['unit']);
    }
}
