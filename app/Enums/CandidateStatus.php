<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case Invited     = 'invited';
    case InProgress  = 'in_progress';
    case Submitted   = 'submitted';
    case UnderReview = 'under_review';
    case Approved    = 'approved';
    case Rejected    = 'rejected';
    case Archived    = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Invited     => 'Invited',
            self::InProgress  => 'Uploading',
            self::Submitted   => 'Submitted',
            self::UnderReview => 'Under review',
            self::Approved    => 'Approved',
            self::Rejected    => 'Changes requested',
            self::Archived    => 'Archived',
        };
    }

    /** Bootstrap-ish token used by the stamp component. */
    public function tone(): string
    {
        return match ($this) {
            self::Invited     => 'neutral',
            self::InProgress  => 'info',
            self::Submitted   => 'info',
            self::UnderReview => 'warn',
            self::Approved    => 'success',
            self::Rejected    => 'danger',
            self::Archived    => 'neutral',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
