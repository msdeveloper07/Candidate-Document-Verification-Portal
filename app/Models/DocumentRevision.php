<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A snapshot of a superseded upload. Keeps an audit trail when a candidate
 * replaces a rejected file, so the agency can see what was sent originally.
 */
class DocumentRevision extends Model
{
    protected $fillable = [
        'candidate_document_id', 'original_name', 'file_path', 'disk',
        'mime_type', 'size_bytes', 'version', 'status_at_replacement',
        'remarks_at_replacement', 'replaced_at',
    ];

    protected function casts(): array
    {
        return ['replaced_at' => 'datetime', 'size_bytes' => 'integer'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(CandidateDocument::class, 'candidate_document_id');
    }
}
