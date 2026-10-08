@php
    $admin = auth('admin')->user();
@endphp

<aside class="sidebar" data-sidebar>
    <div class="sidebar-brand">
        <span class="seal">{{ mb_substr(config('portal.agency_name'), 0, 1) }}</span>
        <div>
            <div class="name">{{ config('portal.agency_name') }}</div>
            <div class="role">Console</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="nav-link-side {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
            Overview
        </a>

        <div class="nav-section">Intake</div>

        <a href="{{ route('admin.candidates.index') }}" class="nav-link-side {{ request()->routeIs('admin.candidates.*') ? 'active' : '' }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
            Candidates
        </a>

        <a href="{{ route('admin.documents.index') }}" class="nav-link-side {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>
            Review queue
            @isset($pendingReviewCount)
                @if ($pendingReviewCount > 0)
                    <span class="count">{{ $pendingReviewCount }}</span>
                @endif
            @endisset
        </a>

        <div class="nav-section">Configuration</div>

        <a href="{{ route('admin.document-types.index') }}" class="nav-link-side {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M9 6h11M9 12h11M9 18h11"/><path d="m3 6 1.5 1.5L7 5M3 12l1.5 1.5L7 11M3 18l1.5 1.5L7 17"/></svg>
            Document checklist
        </a>

        <a href="{{ route('admin.settings.edit') }}" class="nav-link-side {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg>
            Agency settings
        </a>

        <a href="{{ route('admin.email-templates.index') }}" class="nav-link-side {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
            Email templates
        </a>

        @if ($admin->isSuperAdmin())
            <a href="{{ route('admin.users.index') }}" class="nav-link-side {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Team
            </a>
        @endif

        <a href="{{ route('admin.profile.edit') }}" class="nav-link-side {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Your profile
        </a>
    </nav>

    <div class="sidebar-foot">
        <div class="mono" style="font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;">Signed in as</div>
        <div style="color:#D5E2EE;">{{ $admin->name }}</div>
    </div>
</aside>
