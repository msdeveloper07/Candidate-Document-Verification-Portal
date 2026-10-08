@extends('layouts.admin')

@section('title', 'Team')
@section('crumb', 'Configuration')

@section('actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-ink btn-sm">Add team member</a>
@endsection

@section('content')
<div class="content-narrow">
    <form method="GET" class="filter-bar">
        <div class="field grow">
            <label class="form-label" for="search">Search</label>
            <input id="search" name="search" type="search" class="form-control"
                   value="{{ $filters['search'] ?? '' }}" placeholder="Name or email">
        </div>
        <div class="field">
            <label class="form-label" for="role">Role</label>
            <select id="role" name="role" class="form-select">
                <option value="">Any role</option>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-ink">Apply</button>
    </form>

    <div class="table-shell">
        <div class="table-responsive">
            <table class="table-clean">
                <thead>
                    <tr><th>Name</th><th>Role</th><th>Last signed in</th><th>Status</th><th style="width:1%;"></th></tr>
                </thead>
                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar" style="width:32px;height:32px;font-size:.7rem;">{{ $user->initials }}</span>
                                <div>
                                    <div class="cell-name">{{ $user->name }}</div>
                                    <div class="cell-sub">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-stamp :tone="$user->isSuperAdmin() ? 'info' : 'neutral'" :label="$user->role->label()" />
                            <div class="cell-sub mt-1">{{ $user->designation }}</div>
                        </td>
                        <td class="mono" style="font-size:.78rem;">
                            {{ $user->last_login_at?->format('d M Y, H:i') ?? 'Never' }}
                        </td>
                        <td>
                            <x-stamp :tone="$user->is_active ? 'success' : 'danger'"
                                     :label="$user->is_active ? 'Active' : 'Disabled'" />
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-quiet btn-sm">Edit</a>
                                @if ($user->id !== auth('admin')->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          data-confirm="Remove {{ $user->name }} from the team?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger-quiet btn-sm" type="submit">Remove</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
