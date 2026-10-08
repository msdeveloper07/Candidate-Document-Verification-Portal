@extends('layouts.admin')
@section('title', $template->name)

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">
            <a href="{{ route('admin.email-templates.index') }}" style="color:inherit;">Email templates</a> / edit
        </span>
        <h1>{{ $template->name }}</h1>
        <p class="lede">{{ $template->description }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" rel="noopener"
           class="btn btn-quiet">Preview</a>
        <form method="POST" action="{{ route('admin.email-templates.test', $template) }}">
            @csrf
            <button type="submit" class="btn btn-quiet">Send me a test</button>
        </form>
    </div>
</div>

@include('layouts.partials.flash')

<form method="POST" action="{{ route('admin.email-templates.update', $template) }}">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card-flat">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="subject">Subject line</label>
                        <input id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror"
                               value="{{ old('subject', $template->subject) }}" data-tag-target required>
                        @error('subject')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="heading">Heading inside the email</label>
                        <input id="heading" name="heading" class="form-control @error('heading') is-invalid @enderror"
                               value="{{ old('heading', $template->heading) }}" data-tag-target required>
                        @error('heading')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="body">Message</label>
                        <textarea id="body" name="body" rows="10"
                                  class="form-control @error('body') is-invalid @enderror"
                                  data-tag-target required>{{ old('body', $template->body) }}</textarea>
                        <div class="form-hint">Leave a blank line between paragraphs. Plain text only — the styling is applied for you.</div>
                        @error('body')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="button_label">Button text</label>
                            <input id="button_label" name="button_label" class="form-control"
                                   value="{{ old('button_label', $template->button_label) }}" data-tag-target
                                   placeholder="Leave empty for no button">
                            <div class="form-hint">The button links to the candidate’s upload page.</div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="footer_note">Closing note</label>
                            <input id="footer_note" name="footer_note" class="form-control"
                                   value="{{ old('footer_note', $template->footer_note) }}" data-tag-target>
                            <div class="form-hint">Small print under the button.</div>
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                               @checked(old('is_active', $template->is_active))>
                        <label class="form-check-label" for="is_active">Use this wording</label>
                        <div class="form-hint">Switch off to fall back to the wording the app shipped with.</div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3 flex-wrap">
                <button type="submit" class="btn btn-ink">Save wording</button>
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-quiet">Cancel</a>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-flat mb-3">
                <div class="card-head">
                    <div>
                        <span class="eyebrow">Insert</span>
                        <h2 style="font-size:1rem;">Placeholders</h2>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted-2 mb-2" style="font-size:.84rem;">
                        Click a tag to drop it wherever your cursor is. Each one is replaced with the
                        real value when the email goes out.
                    </p>

                    <div class="d-flex flex-column gap-1">
                        @foreach ($placeholders as $placeholder)
                            <button type="button" class="tag-chip" data-tag="{{ $placeholder['token'] }}">
                                <span class="mono">{{ $placeholder['token'] }}</span>
                                <span class="tag-meaning">{{ $placeholder['meaning'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card-flat">
                <div class="card-body">
                    <h3 style="font-size:.92rem;">Start over</h3>
                    <p class="text-muted-2" style="font-size:.84rem;">
                        Puts back the original wording. Your current text is replaced.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Kept outside the edit form so one submit button cannot trigger the other. --}}
<form method="POST" action="{{ route('admin.email-templates.restore', $template) }}"
      data-confirm="Replace your wording with the original text?" class="mt-2">
    @csrf
    <button type="submit" class="btn btn-quiet btn-sm">Restore default wording</button>
</form>
@endsection
