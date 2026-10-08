@extends('layouts.admin')

@section('title', $user->exists ? 'Edit '.$user->name : 'Add team member')
@section('crumb', 'Configuration / Team')

@section('content')
<div class="content-narrow" style="max-width:620px;">
    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" novalidate>
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="card-flat mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="name">Full name</label>
                        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="email">Email address</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="designation">Job title</label>
                        <input id="designation" name="designation" class="form-control"
                               value="{{ old('designation', $user->designation) }}">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="phone">Phone</label>
                        <input id="phone" name="phone" class="form-control mono" value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="role">Role</label>
                        <select id="role" name="role" class="form-select">
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $user->role?->value) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Super admins can manage the team and the document checklist.</div>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="password">{{ $user->exists ? 'New password' : 'Password' }}</label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @if ($user->exists)<div class="form-hint">Leave blank to keep the current one.</div>@endif
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               autocomplete="new-password" class="form-control">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   @checked(old('is_active', $user->is_active))>
                            <label class="form-check-label" for="is_active">Can sign in</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-ink">{{ $user->exists ? 'Save changes' : 'Add team member' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-quiet">Cancel</a>
        </div>
    </form>
</div>
@endsection
