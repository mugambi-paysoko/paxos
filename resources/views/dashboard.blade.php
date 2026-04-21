@extends(auth()->user()->can('borrower') ? 'layouts.borrower' : 'layouts.app')

@section('title', 'Dashboard')

@section('content')
    @if (auth()->user()->can('borrower'))
        @php
            $balanceUnit = strtoupper((string) ($borrowerBalanceCard['unit'] ?? ''));
            $balanceCc = match ($balanceUnit) {
                'BTC', 'ETH', 'LTC', 'XRP', 'BCH', 'DOGE' => $balanceUnit,
                'USD', 'USDC', 'USDP' => 'USDT',
                'USDT' => 'USDT',
                default => null,
            };
        @endphp
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card bg-primary-subtle shadow-none rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="round rounded text-bg-primary d-flex align-items-center justify-content-center">
                                <i class="ti ti-fingerprint text-white fs-7" aria-hidden="true"></i>
                            </div>
                            <h6 class="mb-0 ms-3 fw-semibold text-body">Identities</h6>
                            <div class="ms-auto text-primary d-flex align-items-center">
                                <i class="ti ti-shield-check fs-6" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h3 class="mb-0 fw-semibold fs-7 borrower-crypto-mono">{{ $stats['identities_count'] }}</h3>
                            <span class="fs-3 text-muted fw-semibold">KYC</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card bg-danger-subtle shadow-none rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="round rounded text-bg-danger d-flex align-items-center justify-content-center">
                                <i class="ti ti-wallet text-white fs-7" aria-hidden="true"></i>
                            </div>
                            <h6 class="mb-0 ms-3 fw-semibold text-body">Accounts</h6>
                            <div class="ms-auto text-danger d-flex align-items-center">
                                <i class="ti ti-building-bank fs-6" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h3 class="mb-0 fw-semibold fs-7 borrower-crypto-mono">{{ $stats['accounts_count'] }}</h3>
                            <span class="fs-3 text-muted fw-semibold">Custody</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card bg-success-subtle shadow-none rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="round rounded text-bg-success d-flex align-items-center justify-content-center">
                                <i class="ti ti-user-check text-white fs-7" aria-hidden="true"></i>
                            </div>
                            <h6 class="mb-0 ms-3 fw-semibold text-body">Profiles</h6>
                            <div class="ms-auto text-success d-flex align-items-center">
                                <i class="ti ti-api fs-6" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h3 class="mb-0 fw-semibold fs-7 borrower-crypto-mono">{{ $stats['profiles_count'] }}</h3>
                            <span class="fs-3 text-muted fw-semibold">Paxos</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card bg-warning-subtle shadow-none rounded-4 h-100 position-relative {{ ($borrowerBalanceCard['has_error'] ?? false) ? 'border border-warning border-opacity-75' : '' }}">
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex align-items-center">
                            <div class="round rounded text-bg-warning d-flex align-items-center justify-content-center">
                                @if ($balanceCc !== null)
                                    <i class="cc {{ $balanceCc }} text-white fs-7" title="{{ $balanceUnit }}"></i>
                                @else
                                    <i class="ti ti-coins text-white fs-7" aria-hidden="true"></i>
                                @endif
                            </div>
                            <h6 class="mb-0 ms-3 fw-semibold text-body">Total balance</h6>
                            <div class="ms-auto text-warning d-flex align-items-center gap-1">
                                <i class="ti ti-bolt fs-6" aria-hidden="true"></i>
                                <span class="fs-2 fw-bold">Live</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4 gap-2">
                            <h3 class="mb-0 fw-semibold fs-7 borrower-crypto-mono text-truncate">
                                {{ $borrowerBalanceCard['value'] }}
                                @if (! empty($borrowerBalanceCard['unit']))
                                    <span class="fs-5 text-muted fw-medium">{{ $borrowerBalanceCard['unit'] }}</span>
                                @endif
                            </h3>
                        </div>
                        <p class="fs-2 text-muted mb-0 mt-2">{{ $borrowerBalanceCard['caption'] }}</p>
                        <a href="{{ route('borrower.balances.index') }}" class="stretched-link" aria-label="Open balances"></a>
                    </div>
                </div>
            </div>
        </div>

        @if (! auth()->user()->hasPaxosIdentity())
            <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
                        <div>
                            <h4 class="fw-semibold mb-2">Onboard to the network</h4>
                            <p class="text-muted mb-0 fs-3">Create your personal identity to link Paxos custody and start moving assets.</p>
                        </div>
                        <a href="{{ route('borrower.identities.create') }}" class="btn btn-primary btn-lg rounded-3 px-4">
                            <i class="ti ti-plus me-2"></i>Create identity
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body p-4 p-md-5">
                    <h4 class="card-title fw-semibold mb-1">Quick actions</h4>
                    <p class="card-subtitle text-muted fs-3 mb-4">Jump to the flows you use most.</p>
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('borrower.identities.index') }}" class="btn btn-primary w-100 py-3 rounded-3">
                                <i class="ti ti-id me-2"></i>Identities
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('borrower.accounts.index') }}" class="btn bg-danger-subtle text-danger w-100 py-3 rounded-3 fw-semibold border-0">
                                <i class="ti ti-wallet me-2"></i>Accounts
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('borrower.profiles.index') }}" class="btn bg-success-subtle text-success w-100 py-3 rounded-3 fw-semibold border-0">
                                <i class="ti ti-user-check me-2"></i>Profiles
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('borrower.balances.index') }}" class="btn bg-warning-subtle text-warning w-100 py-3 rounded-3 fw-semibold border-0">
                                <i class="ti ti-chart-line me-2"></i>Balances
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card bg-primary-subtle shadow-none rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="round rounded text-bg-primary d-flex align-items-center justify-content-center">
                                <i class="ti ti-fingerprint text-white fs-7" aria-hidden="true"></i>
                            </div>
                            <h6 class="mb-0 ms-3 fw-semibold text-body">Identities</h6>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h3 class="mb-0 fw-semibold fs-7 borrower-crypto-mono">{{ $stats['identities_count'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card bg-danger-subtle shadow-none rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="round rounded text-bg-danger d-flex align-items-center justify-content-center">
                                <i class="ti ti-wallet text-white fs-7" aria-hidden="true"></i>
                            </div>
                            <h6 class="mb-0 ms-3 fw-semibold text-body">Accounts</h6>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h3 class="mb-0 fw-semibold fs-7 borrower-crypto-mono">{{ $stats['accounts_count'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card bg-success-subtle shadow-none rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="round rounded text-bg-success d-flex align-items-center justify-content-center">
                                <i class="ti ti-user-check text-white fs-7" aria-hidden="true"></i>
                            </div>
                            <h6 class="mb-0 ms-3 fw-semibold text-body">Profiles</h6>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h3 class="mb-0 fw-semibold fs-7 borrower-crypto-mono">{{ $stats['profiles_count'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @can('lender')
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body p-4 p-md-5">
                    <h4 class="card-title fw-semibold mb-1">Institution quick actions</h4>
                    <p class="card-subtitle text-muted fs-3 mb-4">Jump to the workflows your team uses most.</p>
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('lender.identities.create') }}" class="btn btn-primary w-100 py-3 rounded-3">
                                <i class="ti ti-id me-2"></i>Create identity
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('lender.accounts.create') }}" class="btn bg-danger-subtle text-danger w-100 py-3 rounded-3 fw-semibold border-0">
                                <i class="ti ti-wallet me-2"></i>Create account
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('lender.fiat-accounts.create') }}" class="btn bg-success-subtle text-success w-100 py-3 rounded-3 fw-semibold border-0">
                                <i class="ti ti-building-bank me-2"></i>Create fiat account
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('lender.fiat-deposits.index') }}" class="btn bg-warning-subtle text-warning w-100 py-3 rounded-3 fw-semibold border-0">
                                <i class="ti ti-chart-line me-2"></i>View deposits
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    @endif
@endsection
