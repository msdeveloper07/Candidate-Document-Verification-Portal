<?php

namespace App\Models;

use App\Enums\Availability;
use App\Enums\CandidateStatus;
use App\Enums\DocumentStatus;
use App\Enums\PreferredShift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Candidate extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_no', 'first_name', 'middle_name', 'last_name',
        'date_of_birth', 'email',
        'dial_code', 'phone', 'country_code', 'country_name',
        'address_line1', 'address_line2', 'city', 'state', 'postal_code',
        'position_applied', 'availability', 'available_from', 'preferred_shift',
        'status', 'invited_by',
        'invite_token', 'invite_sent_at', 'invite_expires_at',
        'first_accessed_at', 'submitted_at', 'reviewed_at', 'reviewed_by',
        'internal_notes',
    ];

    protected $hidden = ['invite_token', 'remember_token'];

    protected function casts(): array
    {
        return [
            'status'            => CandidateStatus::class,
            'availability'      => Availability::class,
            'preferred_shift'   => PreferredShift::class,
            'date_of_birth'     => 'date',
            'available_from'    => 'date',
            'invite_sent_at'    => 'datetime',
            'invite_expires_at' => 'datetime',
            'first_accessed_at' => 'datetime',
            'submitted_at'      => 'datetime',
            'reviewed_at'       => 'datetime',
        ];
    }

    /** Candidates authenticate via OTP, never a password. */
    public function getAuthPassword(): string
    {
        return '';
    }

    protected static function booted(): void
    {
        static::creating(function (self $candidate) {
            $candidate->reference_no ??= self::generateReference();
        });
    }

    public static function generateReference(): string
    {
        do {
            $ref = 'CND-'.now()->format('y').'-'.Str::upper(Str::random(6));
        } while (self::withTrashed()->where('reference_no', $ref)->exists());

        return $ref;
    }

    // ---------- Relations ----------

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'invited_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CandidateDocument::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(CandidateReference::class)->orderBy('slot');
    }

    /**
     * Always hand back three rows, creating blanks for slots never filled in,
     * so the form can render a fixed three-row block without special cases.
     */
    public function referenceSlots(): \Illuminate\Support\Collection
    {
        $existing = $this->references->keyBy('slot');

        return collect(range(1, CandidateReference::REQUIRED))->map(
            fn (int $slot) => $existing->get($slot) ?? new CandidateReference([
                'candidate_id' => $this->id,
                'slot'         => $slot,
            ])
        );
    }

    public function completedReferenceCount(): int
    {
        return $this->references->filter->isComplete()->count();
    }

    public function hasAllReferences(): bool
    {
        return $this->completedReferenceCount() >= CandidateReference::REQUIRED;
    }

    /** The checklist of document types this candidate must provide. */
    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(DocumentType::class, 'candidate_requirements')
            ->withPivot(['is_required', 'instructions'])
            ->withTimestamps()
            ->orderBy('document_types.sort_order');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ---------- Accessors ----------

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name, $this->middle_name, $this->last_name,
        ])));
    }

    public function getFullPhoneAttribute(): string
    {
        return $this->dial_code.' '.$this->phone;
    }

    public function getMaskedPhoneAttribute(): string
    {
        $tail = mb_substr($this->phone, -3);

        return $this->dial_code.' '.str_repeat('•', max(mb_strlen($this->phone) - 3, 0)).$tail;
    }

    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->first_name, 0, 1).mb_substr($this->last_name, 0, 1));
    }

    public function getInviteUrlAttribute(): ?string
    {
        return $this->invite_token ? route('candidate.invite.open', $this->invite_token) : null;
    }

    // ---------- Domain helpers ----------

    public function inviteIsValid(): bool
    {
        return filled($this->invite_token)
            && $this->invite_expires_at instanceof Carbon
            && $this->invite_expires_at->isFuture();
    }

    /** Counts used by the segmented progress bar. */
    /**
     * One pass over the checklist, used by the progress meter, the collection
     * bar and the submit button. References count as one more line item so a
     * candidate cannot finish while the reference block is half empty.
     */
    public function collectionSummary(): array
    {
        $required = $this->requirements->where('pivot.is_required', true);
        $docs     = $this->documents->keyBy('document_type_id');

        $approved = $awaiting = $rejected = $uploaded = 0;

        foreach ($required as $type) {
            $doc = $docs->get($type->id);

            if (! $doc) {
                continue;
            }

            match ($doc->status) {
                DocumentStatus::Approved    => $approved++,
                DocumentStatus::Rejected    => $rejected++,
                DocumentStatus::UnderReview => $awaiting++,
                DocumentStatus::Uploaded    => $uploaded++,
                default                     => null,
            };
        }

        // References are optional, so they are reported but never counted
        // towards completion — only documents decide whether a file is done.
        $docTotal = $required->count();

        return [
            'total'      => $docTotal,
            'approved'   => $approved,
            'uploaded'   => $uploaded,
            'pending'    => $awaiting + $uploaded,          // anything the recruiter still owes
            'awaiting'   => $awaiting,
            'rejected'   => $rejected,
            'missing'    => max($docTotal - ($approved + $awaiting + $uploaded + $rejected), 0),
            'refs_done'  => $this->hasAllReferences(),
            'refs_count' => $this->completedReferenceCount(),
            'refs_total' => CandidateReference::REQUIRED,
            'line_items' => $docTotal,
            'percent'    => (int) round(($approved / max($docTotal, 1)) * 100),
        ];
    }

    /** Every document is in and nothing was sent back. References do not gate this. */
    public function hasSubmittedEverything(): bool
    {
        $summary = $this->collectionSummary();

        return $summary['total'] > 0
            && $summary['missing'] === 0
            && $summary['rejected'] === 0;
    }

    // ---------- Scopes ----------

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('reference_no', 'like', "%{$term}%");
        });
    }

    public function scopeStatus($query, ?string $status)
    {
        return blank($status) ? $query : $query->where('status', $status);
    }
}
