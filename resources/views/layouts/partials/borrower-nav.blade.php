<ul class="navbar-nav flex-row flex-wrap align-items-center gap-1 gap-lg-2">
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('dashboard') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('dashboard') }}">
            <span class="d-none d-xl-inline">Dashboard</span>
            <i class="ti ti-layout-dashboard d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('borrower.balances.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('borrower.balances.index') }}">
            <span class="d-none d-xl-inline">Balances</span>
            <i class="ti ti-coins d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('borrower.identities.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('borrower.identities.index') }}">
            <span class="d-none d-xl-inline">Identities</span>
            <i class="ti ti-id d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('borrower.accounts.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('borrower.accounts.index') }}">
            <span class="d-none d-xl-inline">Accounts</span>
            <i class="ti ti-wallet d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('borrower.profiles.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('borrower.profiles.index') }}">
            <span class="d-none d-xl-inline">Profiles</span>
            <i class="ti ti-user-circle d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('borrower.fiat-accounts.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('borrower.fiat-accounts.index') }}">
            <span class="d-none d-xl-inline">Fiat accounts</span>
            <i class="ti ti-building-bank d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('borrower.fiat-withdrawals.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('borrower.fiat-withdrawals.index') }}">
            <span class="d-none d-xl-inline">Fiat withdrawals</span>
            <i class="ti ti-arrow-up-right d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('borrower.crypto-withdrawals.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('borrower.crypto-withdrawals.index') }}">
            <span class="d-none d-xl-inline">Crypto withdrawals</span>
            <i class="ti ti-currency-bitcoin d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('borrower.deposit-addresses.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('borrower.deposit-addresses.index') }}">
            <span class="d-none d-xl-inline">Crypto addresses</span>
            <i class="ti ti-qrcode d-xl-none fs-6"></i>
        </a>
    </li>
</ul>
