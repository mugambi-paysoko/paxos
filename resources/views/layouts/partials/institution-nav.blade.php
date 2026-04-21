<ul class="navbar-nav flex-row flex-wrap align-items-center gap-1 gap-lg-2">
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('dashboard') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('dashboard') }}">
            <span class="d-none d-xl-inline">Dashboard</span>
            <i class="ti ti-layout-dashboard d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.identities.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.identities.index') }}">
            <span class="d-none d-xl-inline">Identities</span>
            <i class="ti ti-id d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.accounts.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.accounts.index') }}">
            <span class="d-none d-xl-inline">Accounts</span>
            <i class="ti ti-wallet d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.profiles.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.profiles.index') }}">
            <span class="d-none d-xl-inline">Profiles</span>
            <i class="ti ti-user-circle d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.fiat-accounts.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.fiat-accounts.index') }}">
            <span class="d-none d-xl-inline">Fiat accounts</span>
            <i class="ti ti-building-bank d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.fiat-withdrawals.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.fiat-withdrawals.index') }}">
            <span class="d-none d-xl-inline">Fiat withdrawals</span>
            <i class="ti ti-arrow-up-right d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.crypto-withdrawals.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.crypto-withdrawals.index') }}">
            <span class="d-none d-xl-inline">Crypto withdrawals</span>
            <i class="ti ti-currency-bitcoin d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.deposit-addresses.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.deposit-addresses.index') }}">
            <span class="d-none d-xl-inline">Crypto addresses</span>
            <i class="ti ti-qrcode d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.fiat-deposit-instructions.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.fiat-deposit-instructions.index') }}">
            <span class="d-none d-xl-inline">Deposit instructions</span>
            <i class="ti ti-building-bank d-xl-none fs-6"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-2 px-lg-3 rounded-3 {{ request()->routeIs('lender.fiat-deposits.*') ? 'bg-primary-subtle text-primary fw-semibold borrower-nav-link--active' : 'text-body' }}" href="{{ route('lender.fiat-deposits.index') }}">
            <span class="d-none d-xl-inline">Fiat deposits</span>
            <i class="ti ti-receipt d-xl-none fs-6"></i>
        </a>
    </li>
</ul>
