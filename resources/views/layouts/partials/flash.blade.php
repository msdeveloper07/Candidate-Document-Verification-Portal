@if (session('status'))
    <div class="notice notice-success mb-3" role="status">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-1">
            <path d="M20 6 9 17l-5-5"/>
        </svg>
        <div>{{ session('status') }}</div>
    </div>
@endif

@if (session('warning'))
    <div class="notice notice-warn mb-3" role="alert">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-1">
            <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>
        </svg>
        <div>{{ session('warning') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="notice notice-danger mb-3" role="alert">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-1">
            <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
        </svg>
        <div>{{ session('error') }}</div>
    </div>
@endif
