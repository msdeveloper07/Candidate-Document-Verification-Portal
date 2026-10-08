<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateReference extends Model
{
    /** Every candidate is asked for exactly this many. */
    public const REQUIRED = 3;

    protected $fillable = [
        'candidate_id', 'slot', 'name', 'relationship',
        'organisation', 'email', 'dial_code', 'phone',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /** A reference only counts once we can actually contact the person. */
    public function isComplete(): bool
    {
        return filled($this->name)
            && (filled($this->email) || filled($this->phone));
    }

    public function fullPhone(): ?string
    {
        return filled($this->phone)
            ? trim(($this->dial_code ?? '').' '.$this->phone)
            : null;
    }
}
