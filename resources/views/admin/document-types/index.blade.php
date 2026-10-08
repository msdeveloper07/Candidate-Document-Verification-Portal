@extends('layouts.admin')

@section('title', 'Document checklist')
@section('crumb', 'Configuration')

@section('actions')
    <a href="{{ route('admin.document-types.create') }}" class="btn btn-ink btn-sm">Add document</a>
@endsection

@section('content')
<div class="content-narrow">
    <p class="text-muted-2 mb-3" style="font-size:.9rem;max-width:62ch;">
        These are the documents you can ask candidates for. Items marked <b>default</b> are pre-ticked
        when you add someone new — you can still change the selection per candidate.
    </p>

    <div class="table-shell">
        @if ($types->isEmpty())
            <div class="empty-state">
                <h3>Checklist is empty</h3>
                <p>Add the documents your agency needs before inviting candidates.</p>
                <a href="{{ route('admin.document-types.create') }}" class="btn btn-ink">Add document</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table-clean">
                    <thead>
                        <tr>
                            <th style="width:1%;">#</th>
                            <th>Document</th>
                            <th>Accepted</th>
                            <th>Default</th>
                            <th>Active</th>
                            <th style="width:1%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($types as $type)
                        <tr>
                            <td class="mono text-faint">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="cell-name">{{ $type->name }}</div>
                                <div class="cell-sub">{{ $type->description }}</div>
                            </td>
                            <td class="mono" style="font-size:.76rem;">
                                {{ $type->extension_list }}<br>
                                <span class="text-faint">max {{ $type->max_size_label }}</span>
                            </td>
                            <td>
                                @if ($type->is_required_by_default)
                                    <x-stamp tone="info" label="Default" />
                                @else
                                    <span class="text-faint" style="font-size:.82rem;">Optional</span>
                                @endif
                            </td>
                            <td>
                                <x-stamp :tone="$type->is_active ? 'success' : 'neutral'"
                                         :label="$type->is_active ? 'Active' : 'Hidden'" />
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.document-types.edit', $type) }}" class="btn btn-quiet btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.document-types.destroy', $type) }}"
                                          data-confirm="Remove “{{ $type->name }}” from the checklist?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger-quiet btn-sm" type="submit">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-3">{{ $types->links() }}</div>
</div>
@endsection
