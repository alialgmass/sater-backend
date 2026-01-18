<?php

namespace Modules\Core\Enums;

enum OpacityLevelEnum: string
{
    case TRANSPARENT = 'transparent';
    case SEMI_TRANSPARENT = 'semi_transparent';
    case OPAQUE = 'opaque';
    case FULLY_OPAQUE = 'fully_opaque';

    public function label(): string
    {
        return match($this) {
            self::TRANSPARENT => 'Transparent',
            self::SEMI_TRANSPARENT => 'Semi-Transparent',
            self::OPAQUE => 'Opaque',
            self::FULLY_OPAQUE => 'Fully Opaque',
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
