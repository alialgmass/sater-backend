<?php

namespace App\Enums;

enum FabricTypeEnum: string
{
    case COTTON = 'cotton';
    case SILK = 'silk';
    case LINEN = 'linen';
    case WOOL = 'wool';
    case POLYESTER = 'polyester';
    case BLEND = 'blend';
    case CHIFFON = 'chiffon';
    case GEORGETTE = 'georgette';
    case SATIN = 'satin';
    case VELVET = 'velvet';
    case DENIM = 'denim';
    case JERSEY = 'jersey';

    public function label(): string
    {
        return match($this) {
            self::COTTON => 'Cotton',
            self::SILK => 'Silk',
            self::LINEN => 'Linen',
            self::WOOL => 'Wool',
            self::POLYESTER => 'Polyester',
            self::BLEND => 'Blend',
            self::CHIFFON => 'Chiffon',
            self::GEORGETTE => 'Georgette',
            self::SATIN => 'Satin',
            self::VELVET => 'Velvet',
            self::DENIM => 'Denim',
            self::JERSEY => 'Jersey',
        };
    }

    /**
     * Get all cases as array for validation
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Get all cases with labels
     */
    public static function labels(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}
