@php
    $isEdit = $candidate->exists;
@endphp

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-flat mb-3">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Step one</span>
                    <h2 style="font-size:1.02rem;">Who are we collecting from?</h2>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label" for="first_name">First name</label>
                        <input id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                               value="{{ old('first_name', $candidate->first_name) }}" required>
                        @error('first_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-4">
                        <label class="form-label" for="middle_name">Middle name</label>
                        <input id="middle_name" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror"
                               value="{{ old('middle_name', $candidate->middle_name) }}">
                        <div class="form-hint">Leave blank if none.</div>
                        @error('middle_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-4">
                        <label class="form-label" for="last_name">Last name</label>
                        <input id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                               value="{{ old('last_name', $candidate->last_name) }}" required>
                        @error('last_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="date_of_birth">Date of birth</label>
                        <input id="date_of_birth" name="date_of_birth" type="date"
                               class="form-control @error('date_of_birth') is-invalid @enderror"
                               style="font-family:var(--font-mono);"
                               value="{{ old('date_of_birth', $candidate->date_of_birth?->format('Y-m-d')) }}">
                        <div class="form-hint">Must match the photo ID exactly.</div>
                        @error('date_of_birth')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="email">Email address</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $candidate->email) }}" required>
                        <div class="form-hint">The upload link goes here.</div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="country">Country of residence</label>
                        <select id="country" name="country_code" class="form-select" data-country-select>
                            <option value="">Select a country</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country['code'] }}"
                                        data-dial="{{ $country['dial'] }}"
                                        data-name="{{ $country['name'] }}"
                                        @selected(old('country_code', $candidate->country_code) === $country['code'])>
                                    {{ $country['name'] }} ({{ $country['dial'] }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="country_name" value="{{ old('country_name', $candidate->country_name) }}" data-country-name>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="phone">Mobile number</label>
                        <div class="phone-group">
                            <input name="dial_code" class="form-control @error('dial_code') is-invalid @enderror"
                                   style="max-width:110px;font-family:var(--font-mono);"
                                   value="{{ old('dial_code', $candidate->dial_code ?: '+') }}" data-dial-code required>
                            <input id="phone" name="phone" inputmode="numeric"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   style="font-family:var(--font-mono);"
                                   value="{{ old('phone', $candidate->phone) }}" required>
                        </div>
                        <div class="form-hint">The candidate must enter this exact number to receive their code, so check it carefully.</div>
                        @error('dial_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12"><hr style="border-color:var(--line);margin:.25rem 0 0;"></div>

                    <div class="col-12">
                        <label class="form-label" for="address_line1">Street address</label>
                        <input id="address_line1" name="address_line1" class="form-control"
                               value="{{ old('address_line1', $candidate->address_line1) }}"
                               placeholder="Building, street">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="address_line2">Address line 2</label>
                        <input id="address_line2" name="address_line2" class="form-control"
                               value="{{ old('address_line2', $candidate->address_line2) }}"
                               placeholder="Apartment, unit, floor — optional">
                    </div>

                    <div class="col-sm-5">
                        <label class="form-label" for="city">City</label>
                        <input id="city" name="city" class="form-control"
                               value="{{ old('city', $candidate->city) }}">
                    </div>

                    <div class="col-sm-4">
                        <label class="form-label" for="state">State / province</label>
                        <input id="state" name="state" class="form-control"
                               value="{{ old('state', $candidate->state) }}">
                    </div>

                    <div class="col-sm-3">
                        <label class="form-label" for="postal_code">ZIP / postcode</label>
                        <input id="postal_code" name="postal_code" class="form-control"
                               style="font-family:var(--font-mono);"
                               value="{{ old('postal_code', $candidate->postal_code) }}">
                    </div>

                    <div class="col-12"><hr style="border-color:var(--line);margin:.25rem 0 0;"></div>

                    <div class="col-sm-6">
                        <label class="form-label" for="availability">Availability</label>
                        <select id="availability" name="availability" class="form-select">
                            <option value="">Not stated</option>
                            @foreach (\App\Enums\Availability::options() as $opt)
                                <option value="{{ $opt['value'] }}"
                                        @selected(old('availability', $candidate->availability?->value) === $opt['value'])>
                                    {{ $opt['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="preferred_shift">Preferred shift</label>
                        <select id="preferred_shift" name="preferred_shift" class="form-select">
                            <option value="">Not stated</option>
                            @foreach (\App\Enums\PreferredShift::options() as $opt)
                                <option value="{{ $opt['value'] }}"
                                        @selected(old('preferred_shift', $candidate->preferred_shift?->value) === $opt['value'])>
                                    {{ $opt['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="available_from">Available from</label>
                        <input id="available_from" name="available_from" type="date"
                               class="form-control @error('available_from') is-invalid @enderror"
                               style="font-family:var(--font-mono);"
                               value="{{ old('available_from', $candidate->available_from?->format('Y-m-d')) }}">
                        @error('available_from')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label" for="position_applied">Position</label>
                        <input id="position_applied" name="position_applied" class="form-control"
                               value="{{ old('position_applied', $candidate->position_applied) }}"
                               placeholder="e.g. Site Supervisor — Doha">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="internal_notes">Internal notes</label>
                        <textarea id="internal_notes" name="internal_notes" rows="3" class="form-control"
                                  placeholder="Only your team sees this.">{{ old('internal_notes', $candidate->internal_notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-flat mb-3">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Step two</span>
                    <h2 style="font-size:1.02rem;">What should they send?</h2>
                </div>
                <button type="button" class="btn btn-quiet btn-sm" data-toggle-all-requirements>Toggle all</button>
            </div>
            <div class="card-body">
                @error('requirements')<div class="notice notice-danger mb-3">{{ $message }}</div>@enderror

                <div class="d-flex flex-column gap-2">
                    @foreach ($documentTypes as $type)
                        @php($checked = in_array($type->id, old('requirements', $preselected), false))
                        <label class="d-flex gap-2 p-2 rounded" style="border:1px solid var(--line);cursor:pointer;">
                            <input class="form-check-input mt-1 flex-shrink-0" type="checkbox"
                                   name="requirements[]" value="{{ $type->id }}" @checked($checked)>
                            <span>
                                <span class="d-block fw-semibold" style="font-size:.88rem;">{{ $type->name }}</span>
                                <span class="d-block text-muted-2" style="font-size:.78rem;">{{ $type->description }}</span>
                                <span class="mono text-faint" style="font-size:.7rem;">{{ $type->extension_list }} · max {{ $type->max_size_label }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <p class="form-hint mt-3 mb-0">
                    Manage this list under <a href="{{ route('admin.document-types.index') }}">Document checklist</a>.
                </p>
            </div>
        </div>

        @unless ($isEdit)
            <div class="card-flat">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_invite" id="send_invite" value="1"
                               @checked(old('send_invite', true))>
                        <label class="form-check-label fw-semibold" for="send_invite">Email the upload link now</label>
                    </div>
                    <p class="form-hint mb-0">
                        The link stays live for {{ config('portal.invite.valid_days') }} days. You can re-send it any time.
                    </p>
                </div>
            </div>
        @endunless
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-ink">{{ $isEdit ? 'Save changes' : 'Add candidate' }}</button>
    <a href="{{ $isEdit ? route('admin.candidates.show', $candidate) : route('admin.candidates.index') }}" class="btn btn-quiet">Cancel</a>
</div>
