@extends('layouts.admin')

@section('title', $type->exists ? 'Edit '.$type->name : 'Add document')
@section('crumb', 'Configuration / Checklist')

@section('content')
<div class="content-narrow" style="max-width:680px;">
    <form method="POST" action="{{ $type->exists ? route('admin.document-types.update', $type) : route('admin.document-types.store') }}" novalidate>
        @csrf
        @if ($type->exists) @method('PUT') @endif

        <div class="card-flat mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="name">Document name</label>
                        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $type->name) }}" placeholder="e.g. Proof of address" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="description">One-line description</label>
                        <input id="description" name="description" class="form-control"
                               value="{{ old('description', $type->description) }}"
                               placeholder="Shown under the title on the candidate's page">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="instructions">Instructions for the candidate</label>
                        <textarea id="instructions" name="instructions" rows="4" class="form-control"
                                  placeholder="Be specific: what must be visible, how recent, which language.">{{ old('instructions', $type->instructions) }}</textarea>
                        <div class="form-hint">Clear instructions here mean fewer re-uploads later.</div>
                    </div>

                    <div class="col-sm-7">
                        <label class="form-label">Accepted file types</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach (['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'] as $ext)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allowed_extensions[]"
                                           value="{{ $ext }}" id="ext-{{ $ext }}"
                                           @checked(in_array($ext, old('allowed_extensions', $type->allowed_extensions ?? []), true))>
                                    <label class="form-check-label mono" for="ext-{{ $ext }}" style="font-size:.8rem;">{{ mb_strtoupper($ext) }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('allowed_extensions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-5">
                        <label class="form-label" for="max_size_kb">Maximum size (KB)</label>
                        <input id="max_size_kb" name="max_size_kb" type="number" min="100" max="20480" step="256"
                               class="form-control mono @error('max_size_kb') is-invalid @enderror"
                               value="{{ old('max_size_kb', $type->max_size_kb ?: 5120) }}" required>
                        <div class="form-hint">5120 KB = 5 MB</div>
                        @error('max_size_kb')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="sort_order">Position in the list</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" max="9999"
                               class="form-control mono" value="{{ old('sort_order', $type->sort_order) }}">
                        <div class="form-hint">Lower numbers appear first.</div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_required_by_default" id="is_required_by_default"
                                   value="1" @checked(old('is_required_by_default', $type->is_required_by_default))>
                            <label class="form-check-label" for="is_required_by_default">Pre-tick this for new candidates</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" @checked(old('is_active', $type->is_active))>
                            <label class="form-check-label" for="is_active">Available to request</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-ink">{{ $type->exists ? 'Save changes' : 'Add to checklist' }}</button>
            <a href="{{ route('admin.document-types.index') }}" class="btn btn-quiet">Cancel</a>
        </div>
    </form>
</div>
@endsection
