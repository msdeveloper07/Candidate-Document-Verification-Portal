@extends('layouts.admin')

@section('title', 'Your profile')
@section('crumb', 'Account')

@section('content')
<div class="content-narrow" style="max-width:620px;">

    <div class="card-flat mb-3">
        <div class="card-head">
            <div><span class="eyebrow">Details</span><h2 style="font-size:1.02rem;">Your information</h2></div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="name">Full name</label>
                        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $admin->name) }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="email">Email address</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $admin->email) }}" required>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="designation">Job title</label>
                        <input id="designation" name="designation" class="form-control" value="{{ old('designation', $admin->designation) }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="phone">Phone</label>
                        <input id="phone" name="phone" class="form-control mono" value="{{ old('phone', $admin->phone) }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-ink mt-3">Save changes</button>
            </form>
        </div>
    </div>

    <div class="card-flat">
        <div class="card-head">
            <div><span class="eyebrow">Security</span><h2 style="font-size:1.02rem;">Change password</h2></div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.profile.password') }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="current_password">Current password</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                               class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="password">New password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="password_confirmation">Confirm new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               autocomplete="new-password" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-ink mt-3">Change password</button>
            </form>
        </div>
    </div>
</div>
@endsection
