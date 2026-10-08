@extends('layouts.admin')

@section('title', 'Edit '.$candidate->full_name)
@section('crumb', 'Intake / Candidates')

@section('content')
<div class="content-narrow">
    <form method="POST" action="{{ route('admin.candidates.update', $candidate) }}" novalidate>
        @csrf @method('PUT')
        @include('admin.candidates._form')
    </form>
</div>
@endsection
