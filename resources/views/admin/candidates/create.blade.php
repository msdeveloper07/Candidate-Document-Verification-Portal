@extends('layouts.admin')

@section('title', 'Add candidate')
@section('crumb', 'Intake / Candidates')

@section('content')
<div class="content-narrow">
    <form method="POST" action="{{ route('admin.candidates.store') }}" novalidate>
        @csrf
        @include('admin.candidates._form', ['candidate' => new \App\Models\Candidate])
    </form>
</div>
@endsection
