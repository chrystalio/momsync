<?php

namespace App\Enums;

use App\Traits\EnhancedEnumTrait;

enum ReadingContextEnum: string
{
    use EnhancedEnumTrait;

    case Fasting = 'fasting';
    case BeforeMeal = 'before_meal';
    case AfterMeal = 'after_meal';
    case Bedtime = 'bedtime';
    case Random = 'random';

    public function readableText(): string
    {
        return match ($this) {
            self::Fasting => 'Fasting',
            self::BeforeMeal => 'Before meal',
            self::AfterMeal => 'After meal',
            self::Bedtime => 'Bedtime',
            self::Random => 'Random',
        };
    }
}
