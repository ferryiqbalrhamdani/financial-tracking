<?php

namespace App\Enums\Settings;

enum Currency: string
{
    case IDR = 'IDR';
    case USD = 'USD';

    public function label(): string
    {
        return match ($this) {
            self::IDR => 'Rupiah (IDR)',
            self::USD => 'Dollar (USD)',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::IDR => 'Rp',
            self::USD => '$',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
