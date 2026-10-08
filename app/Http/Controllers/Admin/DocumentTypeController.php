<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentTypeRequest;
use App\Models\DocumentType;
use App\Repositories\Contracts\DocumentTypeRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    public function __construct(private readonly DocumentTypeRepositoryInterface $types)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.document-types.index', [
            'types'   => $this->types->paginate(50, $request->only('search')),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): View
    {
        return view('admin.document-types.form', ['type' => new DocumentType([
            'allowed_extensions'     => ['pdf', 'jpg', 'png'],
            'max_size_kb'            => 5120,
            'is_active'              => true,
            'is_required_by_default' => true,
        ])]);
    }

    public function store(StoreDocumentTypeRequest $request): RedirectResponse
    {
        $this->types->create($this->payload($request));

        return redirect()->route('admin.document-types.index')->with('status', 'Document added to the checklist.');
    }

    public function edit(DocumentType $documentType): View
    {
        return view('admin.document-types.form', ['type' => $documentType]);
    }

    public function update(StoreDocumentTypeRequest $request, DocumentType $documentType): RedirectResponse
    {
        $documentType->update($this->payload($request));

        return redirect()->route('admin.document-types.index')->with('status', 'Checklist item updated.');
    }

    public function destroy(DocumentType $documentType): RedirectResponse
    {
        if ($documentType->documents()->exists()) {
            $documentType->update(['is_active' => false]);

            return back()->with('status', 'Files already exist for this item, so it was deactivated instead of deleted.');
        }

        $documentType->delete();

        return back()->with('status', 'Checklist item deleted.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:document_types,id'],
        ]);

        $this->types->reorder($request->input('order'));

        return response()->json(['message' => 'Order saved.']);
    }

    private function payload(StoreDocumentTypeRequest $request): array
    {
        return [
            ...$request->safe()->only([
                'name', 'description', 'instructions', 'allowed_extensions', 'max_size_kb', 'sort_order', 'icon',
            ]),
            'is_active'              => $request->boolean('is_active'),
            'is_required_by_default' => $request->boolean('is_required_by_default'),
        ];
    }
}
