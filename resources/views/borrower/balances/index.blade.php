@extends('layouts.borrower')

@section('title', 'Balances')

@section('page_actions')
    <a href="{{ route('borrower.balances.json') }}" class="btn btn-outline-secondary btn-sm rounded-3" target="_blank" rel="noopener">
        <i class="ti ti-code me-1"></i> JSON
    </a>
@endsection

@section('content')
    @if (! empty($balanceData['error']))
        <div class="alert alert-warning border-0 rounded-3 mb-4" role="alert">
            {{ $balanceData['error'] }}
        </div>
    @endif

    @if (! ($balanceData['has_profiles'] ?? false))
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5 text-center text-muted">
                <p class="mb-0">You need at least one profile with a Paxos profile ID before balances can load.</p>
                <a href="{{ route('borrower.profiles.index') }}" class="btn btn-primary rounded-3 mt-4">View profiles</a>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <h4 class="card-title fw-semibold mb-1">By asset</h4>
                <p class="card-subtitle text-muted fs-3 mb-4">All Paxos profiles linked to your account, rolled up by ticker.</p>
                @if (count($balanceData['aggregated'] ?? []) > 0)
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase fs-2 text-muted fw-semibold">Asset</th>
                                    <th class="text-uppercase fs-2 text-muted fw-semibold text-end">Available</th>
                                    <th class="text-uppercase fs-2 text-muted fw-semibold text-end">In orders</th>
                                    <th class="text-uppercase fs-2 text-muted fw-semibold text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($balanceData['aggregated'] as $row)
                                    @php
                                        $assetSym = strtoupper((string) ($row['asset'] ?? ''));
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">
                                            @if (in_array($assetSym, ['BTC', 'ETH', 'LTC', 'XRP', 'BCH', 'DOGE', 'USDT'], true))
                                                <i class="cc {{ $assetSym }} me-2 align-middle" title="{{ $assetSym }}"></i>
                                            @elseif (in_array($assetSym, ['USD', 'USDC', 'USDP'], true))
                                                <i class="cc USDT me-2 align-middle" title="{{ $assetSym }}"></i>
                                            @endif
                                            {{ $row['asset'] }}
                                        </td>
                                        <td class="text-end font-monospace borrower-crypto-mono">{{ $row['available'] }}</td>
                                        <td class="text-end font-monospace borrower-crypto-mono">{{ $row['trading'] }}</td>
                                        <td class="text-end font-monospace fw-semibold borrower-crypto-mono">{{ $row['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted fs-3 mt-3 mb-0">Amounts are summed across every Paxos profile linked to your account.</p>
                @else
                    <p class="text-muted mb-0">No balance rows were returned.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-4">Per Paxos profile</h5>
                <div class="vstack gap-4">
                    @foreach ($balanceData['by_profile'] as $profileRow)
                        <div class="border rounded-4 p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <p class="fs-3 text-muted mb-0">Paxos profile</p>
                                    <p class="mb-0 font-monospace fw-semibold">{{ $profileRow['paxos_profile_id'] }}</p>
                                </div>
                                <a href="{{ route('borrower.profiles.show', $profileRow['local_profile_id']) }}" class="btn btn-sm btn-outline-primary rounded-3">Open profile</a>
                            </div>
                            @if (! empty($profileRow['error']))
                                <div class="alert alert-danger border-0 rounded-3 mb-0">{{ $profileRow['error'] }}</div>
                            @elseif (count($profileRow['items'] ?? []) > 0)
                                <div class="table-responsive rounded-3 border">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-uppercase fs-2 text-muted fw-semibold">Asset</th>
                                                <th class="text-uppercase fs-2 text-muted fw-semibold text-end">Available</th>
                                                <th class="text-uppercase fs-2 text-muted fw-semibold text-end">In orders</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($profileRow['items'] as $item)
                                                <tr>
                                                    <td class="fw-semibold">{{ $item['asset'] ?? '—' }}</td>
                                                    <td class="text-end font-monospace">{{ $item['available'] ?? '—' }}</td>
                                                    <td class="text-end font-monospace">{{ $item['trading'] ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0 fs-3">No rows for this profile.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
