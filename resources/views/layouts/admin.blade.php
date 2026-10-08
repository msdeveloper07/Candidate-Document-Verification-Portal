<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
    <link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
</head>
<body>
<div class="app-shell">

    @include('admin.partials.sidebar')
    <div class="sidebar-scrim" data-scrim></div>

    <div class="app-main">
        <header class="topbar">
            <button class="btn btn-quiet btn-sm sidebar-toggle" type="button" data-sidebar-toggle aria-label="Open menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>

            <div>
                <span class="crumb">@yield('crumb', 'Console')</span>
                <div class="page-title">@yield('title', 'Dashboard')</div>
            </div>

            <div class="ms-auto d-flex align-items-center gap-2">
                @yield('actions')

                <div class="dropdown">
                    <button class="btn btn-quiet btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar" style="width:26px;height:26px;font-size:.66rem;">{{ auth('admin')->user()->initials }}</span>
                        <span class="d-none d-sm-inline">{{ auth('admin')->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="px-3 py-2">
                            <div class="fw-semibold small">{{ auth('admin')->user()->name }}</div>
                            <div class="mono text-faint">{{ auth('admin')->user()->role->label() }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}">Your profile</a></li>
                        @if (auth('admin')->user()->isSuperAdmin())
                            <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Team</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">Sign out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="app-content">
            @include('layouts.partials.flash')
            @yield('content')
        </main>
    </div>
</div>

@include('layouts.partials.scripts')
<script src="{{ asset('assets/js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
