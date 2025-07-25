<?php

namespace App\Enums\Settings;

use Carbon\Carbon;
use Filament\Support\Contracts\HasLabel;

enum MonthStart: int implements HasLabel
{
    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case June = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    public const DEFAULT = self::January->value;

    public function getLabel(): ?string
    {
        return Carbon::create()->month($this->value)->translatedFormat('F');
    }
}
