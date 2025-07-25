<?php

namespace App\Enums\Settings;

enum Locale: string
{
    case Indonesia = 'id';
    case English = 'en';

    public const DEFAULT = self::Indonesia->value;

    public function label(): string
    {
        return match ($this) {
            self::Indonesia => 'Indonesia',
            self::English => 'English',
        };
    }
}
