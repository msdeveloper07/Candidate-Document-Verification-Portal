<?php

namespace App\Enums;

enum DocumentStatus: string
{
    /** No file has been received for this requirement yet. */
    case Pending = 'pending';

    /** A file is in, but the candidate has not sent the dossier for review. */
    case Uploaded = 'uploaded';

    /** Sitting in the recruiter's review queue. */
    case UnderReview = 'under_review';

    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending     => 'Pending',
            self::Uploaded    => 'Uploaded',
            self::UnderReview => 'Under review',
            self::Approved    => 'Approved',
            self::Rejected    => 'Rejected',
        };
    }

    /** What the candidate is told to do next. */
    public function candidateHint(): string
    {
        return match ($this) {
            self::Pending     => 'Not uploaded yet',
            self::Uploaded    => 'Received — you can still replace it',
            self::UnderReview => 'With the recruiter',
            self::Approved    => 'Accepted, nothing more to do',
            self::Rejected    => 'Please upload a new copy',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending     => 'neutral',
            self::Uploaded    => 'info',
            self::UnderReview => 'warn',
            self::Approved    => 'success',
            self::Rejected    => 'danger',
        };
    }

    /** Statuses that still need something to happen. */
    public function isOpen(): bool
    {
        return $this !== self::Approved;
    }

    /** The recruiter still has to look at it. */
    public function awaitsRecruiter(): bool
    {
        return in_array($this, [self::Uploaded, self::UnderReview], true);
    }

    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}
