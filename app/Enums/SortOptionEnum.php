<?php

namespace App\Enums;

enum SortOptionEnum: string
{
    case RELEVANCE = 'relevance';
    case PRICE_ASC = 'price_asc';
    case PRICE_DESC = 'price_desc';
    case NEWEST = 'newest';
    case POPULARITY = 'popularity';
    case RATING = 'rating';

    public function label(): string
    {
        return match($this) {
            self::RELEVANCE => 'Most Relevant',
            self::PRICE_ASC => 'Price: Low to High',
            self::PRICE_DESC => 'Price: High to Low',
            self::NEWEST => 'Newest First',
            self::POPULARITY => 'Most Popular',
            self::RATING => 'Top Rated',
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
