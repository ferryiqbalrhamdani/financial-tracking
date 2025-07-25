<?php

namespace App\Enums\Settings;

use Filament\Support\Contracts\HasLabel;

enum DayStart: int implements HasLabel
{
    case One = 1;
    case Two = 2;
    case Three = 3;
    case Four = 4;
    case Five = 5;
    case Six = 6;
    case Seven = 7;
    case Eight = 8;
    case Nine = 9;
    case Ten = 10;
    case Eleven = 11;
    case Twelve = 12;
    case Thirteen = 13;
    case Fourteen = 14;
    case Fifteen = 15;
    case Sixteen = 16;
    case Seventeen = 17;
    case Eighteen = 18;
    case Nineteen = 19;
    case Twenty = 20;
    case TwentyOne = 21;
    case TwentyTwo = 22;
    case TwentyThree = 23;
    case TwentyFour = 24;
    case TwentyFive = 25;
    case TwentySix = 26;
    case TwentySeven = 27;
    case TwentyEight = 28;

    public const DEFAULT = self::One->value;

    public function getLabel(): ?string
    {
        return 'Tanggal ' . $this->value;
    }
}
