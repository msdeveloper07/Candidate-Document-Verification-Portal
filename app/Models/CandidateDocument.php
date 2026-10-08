<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CandidateDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'document_type_id', 'original_name', 'file_path',
        'disk', 'mime_type', 'extension', 'size_bytes', 'checksum',
        'status', 'remarks', 'version', 'uploaded_ip',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'      => DocumentStatus::class,
            'reviewed_at' => 'datetime',
            'size_bytes'  => 'integer',
            'version'     => 'integer',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class);
    }

    public function getSizeLabelAttribute(): string
    {
        $bytes = $this->size_bytes;

        return match (true) {
            $bytes >= 1048576 => round($bytes / 1048576, 1).' MB',
            $bytes >= 1024    => round($bytes / 1024).' KB',
            default           => $bytes.' B',
        };
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function fileExists(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_path);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [DocumentStatus::Uploaded, DocumentStatus::UnderReview]);
    }
}
