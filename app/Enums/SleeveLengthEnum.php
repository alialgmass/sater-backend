<?php

namespace App\Enums;

enum SleeveLengthEnum: string
{
    case SLEEVELESS = 'sleeveless';
    case HALF_SLEEVE = 'half_sleeve';
    case THREE_QUARTER_SLEEVE = 'three_quarter_sleeve';
    case FULL_SLEEVE = 'full_sleeve';
    case EXTENDED_SLEEVE = 'extended_sleeve';

    public function label(): string
    {
        return match($this) {
            self::SLEEVELESS => 'Sleeveless',
            self::HALF_SLEEVE => 'Half Sleeve',
            self::THREE_QUARTER_SLEEVE => '3/4 Sleeve',
            self::FULL_SLEEVE => 'Full Sleeve',
            self::EXTENDED_SLEEVE => 'Extended Sleeve',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function labels(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}
