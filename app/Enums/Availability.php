<?php

namespace App\Enums;

enum Availability: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case PerDiem  = 'per_diem';
    case Travel   = 'travel';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Full-time',
            self::PartTime => 'Part-time',
            self::PerDiem  => 'Per diem',
            self::Travel   => 'Travel / contract',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}
