<?php

namespace App\Enums;

enum PreferredShift: string
{
    case Day      = 'day';
    case Evening  = 'evening';
    case Night    = 'night';
    case Rotating = 'rotating';
    case Flexible = 'flexible';

    public function label(): string
    {
        return match ($this) {
            self::Day      => 'Day (7a–7p)',
            self::Evening  => 'Evening (3p–11p)',
            self::Night    => 'Night (7p–7a)',
            self::Rotating => 'Rotating',
            self::Flexible => 'Flexible / any',
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
