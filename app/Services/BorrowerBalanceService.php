<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class BorrowerBalanceService
{
    /**
     * Asset codes treated as ~USD for the dashboard headline (summed at par).
     * Paxos may return Paxos Dollar as USDP while USD is zero or absent.
     *
     * @var list<string>
     */
    private const USD_STABLE_ASSET_CODES = [
        'USD',
        'USDP',
        'USDC',
        'USDT',
        'BUSD',
        'TUSD',
        'PYUSD',
        'GUSD',
        'USDS',
        'PAX',
    ];

    public function __construct(private PaxosService $paxos) {}

    /**
     * Fetch balances for all borrower profiles with a Paxos profile id, aggregate by asset,
     * and return per-profile rows for the dashboard.
     *
     * @return array{
     *     aggregated: list<array{asset: string, available: string, trading: string, total: string}>,
     *     aggregated_non_zero: list<array{asset: string, available: string, trading: string, total: string}>,
     *     by_profile: list<array{local_profile_id: int, paxos_profile_id: string, items: list<array<string, mixed>>, error?: string}>,
     *     error: ?string,
     *     has_profiles: bool
     * }
     */
    public function summarizeForUser(User $user): array
    {
        $profiles = $user->profiles()->whereNotNull('paxos_profile_id')->orderBy('id')->get();

        if ($profiles->isEmpty()) {
            return [
                'aggregated' => [],
                'aggregated_non_zero' => [],
                'by_profile' => [],
                'error' => null,
                'has_profiles' => false,
            ];
        }

        $byProfile = [];
        $aggregate = [];

        foreach ($profiles as $profile) {
            $paxosProfileId = (string) $profile->paxos_profile_id;

            try {
                $items = $this->paxos->listProfileBalances($paxosProfileId);
            } catch (\Throwable $e) {
                $byProfile[] = [
                    'local_profile_id' => $profile->id,
                    'paxos_profile_id' => $paxosProfileId,
                    'items' => [],
                    'error' => $e->getMessage(),
                ];

                continue;
            }

            $byProfile[] = [
                'local_profile_id' => $profile->id,
                'paxos_profile_id' => $paxosProfileId,
                'items' => $items,
            ];

            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $asset = (string) ($row['asset'] ?? '');
                if ($asset === '') {
                    continue;
                }
                $available = (string) ($row['available'] ?? '0');
                $trading = (string) ($row['trading'] ?? '0');

                if (! isset($aggregate[$asset])) {
                    $aggregate[$asset] = ['available' => '0', 'trading' => '0'];
                }
                $aggregate[$asset]['available'] = $this->addDecimalStrings($aggregate[$asset]['available'], $available);
                $aggregate[$asset]['trading'] = $this->addDecimalStrings($aggregate[$asset]['trading'], $trading);
            }
        }

        $aggregated = [];
        foreach ($aggregate as $asset => $amounts) {
            $aggregated[] = [
                'asset' => $asset,
                'available' => $this->trimDecimal($amounts['available']),
                'trading' => $this->trimDecimal($amounts['trading']),
                'total' => $this->trimDecimal($this->addDecimalStrings($amounts['available'], $amounts['trading'])),
            ];
        }

        usort($aggregated, function (array $a, array $b): int {
            if ($a['asset'] === 'USD' && $b['asset'] !== 'USD') {
                return -1;
            }
            if ($b['asset'] === 'USD' && $a['asset'] !== 'USD') {
                return 1;
            }

            return strcmp($a['asset'], $b['asset']);
        });

        $globalError = null;
        foreach ($byProfile as $row) {
            if (! empty($row['error'])) {
                $globalError = 'Some profiles could not load balances. Check each profile below or your Paxos credentials and scopes (funding:read_profile).';
                break;
            }
        }

        $aggregatedNonZero = array_values(array_filter(
            $aggregated,
            fn (array $row): bool => $this->isPositiveAmount($row['total'])
        ));

        return [
            'aggregated' => $aggregated,
            'aggregated_non_zero' => $aggregatedNonZero,
            'by_profile' => $byProfile,
            'error' => $globalError,
            'has_profiles' => true,
        ];
    }

    /**
     * Copy for the dashboard stat card (single headline + caption).
     *
     * @param  array{
     *     aggregated: list<array{asset: string, available: string, trading: string, total: string}>,
     *     aggregated_non_zero: list<array{asset: string, available: string, trading: string, total: string}>,
     *     by_profile: list<array<string, mixed>>,
     *     error: ?string,
     *     has_profiles: bool
     * }  $summary
     * @return array{value: string, unit: ?string, caption: string, has_error: bool}
     */
    public function dashboardCardFromSummary(array $summary): array
    {
        if (! ($summary['has_profiles'] ?? false)) {
            return [
                'value' => '—',
                'unit' => null,
                'caption' => 'Add a profile with a Paxos ID to see balances',
                'has_error' => false,
            ];
        }

        $aggregated = $summary['aggregated'] ?? [];
        $nonZero = $summary['aggregated_non_zero'] ?? [];

        if ($aggregated === []) {
            $firstError = collect($summary['by_profile'] ?? [])
                ->pluck('error')
                ->filter()
                ->first();

            return [
                'value' => '—',
                'unit' => null,
                'caption' => $firstError
                    ? Str::limit((string) $firstError, 96)
                    : 'No balance rows returned from Paxos',
                'has_error' => true,
            ];
        }

        $usdStableTotal = $this->sumTotalsForAssetCodes($aggregated, self::USD_STABLE_ASSET_CODES);
        if ($this->isPositiveAmount($usdStableTotal)) {
            return [
                'value' => $this->trimDecimal($usdStableTotal),
                'unit' => 'USD',
                'caption' => 'Cash & USD-stable (available + in orders, summed at par)',
                'has_error' => ! empty($summary['error']),
            ];
        }

        if ($nonZero !== []) {
            if (count($nonZero) === 1) {
                $only = $nonZero[0];

                return [
                    'value' => $only['total'],
                    'unit' => $only['asset'],
                    'caption' => 'Total balance (available + in orders)',
                    'has_error' => ! empty($summary['error']),
                ];
            }

            return [
                'value' => (string) count($nonZero),
                'unit' => null,
                'caption' => 'Assets with a non-zero balance · see Balances for amounts',
                'has_error' => ! empty($summary['error']),
            ];
        }

        return [
            'value' => '0',
            'unit' => null,
            'caption' => 'All balances are zero',
            'has_error' => ! empty($summary['error']),
        ];
    }

    /**
     * @param  list<array{asset: string, available: string, trading: string, total: string}>  $aggregated
     * @param  list<string>  $assetCodesUpper
     */
    private function sumTotalsForAssetCodes(array $aggregated, array $assetCodesUpper): string
    {
        $allowed = [];
        foreach ($assetCodesUpper as $code) {
            $allowed[strtoupper($code)] = true;
        }

        $sum = '0';
        foreach ($aggregated as $row) {
            $asset = strtoupper((string) ($row['asset'] ?? ''));
            if ($asset === '' || ! isset($allowed[$asset])) {
                continue;
            }
            $sum = $this->addDecimalStrings($sum, (string) ($row['total'] ?? '0'));
        }

        return $sum;
    }

    private function trimDecimal(string $value): string
    {
        $value = trim($value);
        if ($value === '' || str_contains($value, 'e') || str_contains($value, 'E')) {
            return $value === '' ? '0' : $value;
        }
        if (! str_contains($value, '.')) {
            return $value;
        }

        return rtrim(rtrim($value, '0'), '.') ?: '0';
    }

    private function isPositiveAmount(string $amount): bool
    {
        $amount = $this->normalizeDecimalString($amount);
        if (function_exists('bccomp')) {
            return bccomp($amount, '0', 18) === 1;
        }

        return (float) $amount > 0;
    }

    private function addDecimalStrings(string $a, string $b): string
    {
        $a = $this->normalizeDecimalString($a);
        $b = $this->normalizeDecimalString($b);

        if (function_exists('bcadd')) {
            return bcadd($a, $b, 18);
        }

        return rtrim(rtrim(sprintf('%.18F', (float) $a + (float) $b), '0'), '.') ?: '0';
    }

    private function normalizeDecimalString(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '.') {
            return '0';
        }

        return $value;
    }
}
