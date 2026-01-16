<?php

namespace App\Enums;

enum HijabStyleEnum: string
{
    case COTTON_WRAP = 'cotton_wrap';
    case SILK_WRAP = 'silk_wrap';
    case JERSEY_WRAP = 'jersey_wrap';
    case AL_AMIRA = 'al_amira';
    case SHAYLA = 'shayla';
    case CHADOR = 'chador';
    case NIQAB = 'niqab';
    case TURBAN = 'turban';
    case UNDER_SCARF = 'under_scarf';
    case INSTANT_WRAP = 'instant_wrap';

    public function label(): string
    {
        return match($this) {
            self::COTTON_WRAP => 'Cotton Wrap',
            self::SILK_WRAP => 'Silk Wrap',
            self::JERSEY_WRAP => 'Jersey Wrap',
            self::AL_AMIRA => 'Al Amira',
            self::SHAYLA => 'Shayla',
            self::CHADOR => 'Chador',
            self::NIQAB => 'Niqab',
            self::TURBAN => 'Turban',
            self::UNDER_SCARF => 'Under Scarf',
            self::INSTANT_WRAP => 'Instant Wrap',
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
