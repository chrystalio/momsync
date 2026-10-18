<?php

namespace App\Traits;

use BackedEnum;
use UnitEnum;

/**
 * @mixin UnitEnum
 */
trait EnhancedEnumTrait
{
    /**
     * Find a case by its declared name and return its backing value.
     *
     * @param  string  $name  case name as declared (e.g. 'Fasting'), not the backing value
     * @return string|int|null backing value, or null if no case with that name exists
     */
    public static function fromName(string $name): string|int|null
    {
        foreach (static::cases() as $case) {
            if ($case->name === $name) {
                return $case->value;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public static function toNames(): array
    {
        return array_map(fn (BackedEnum $case) => $case->name, static::cases());
    }

    /**
     * @return array<int, string|int>
     */
    public static function toValues(): array
    {
        return array_map(fn (BackedEnum $case) => $case->value, static::cases());
    }

    /**
     * @param  bool  $reverse  flip keys and values
     * @return array<string, string|int>
     */
    public static function toArray(bool $reverse = false): array
    {
        $result = [];

        foreach (static::cases() as $case) {
            $result[$case->name] = $case->value;
        }

        return $reverse ? array_flip($result) : $result;
    }

    /**
     * Build a value => readable text map. Enums using this trait must
     * define a `readableText(): string` method on each case.
     *
     * @param  bool  $reverse  flip keys and values
     * @return array<string, string>
     */
    public static function toArrayWithReadableText(bool $reverse = false): array
    {
        $result = [];

        foreach (static::cases() as $case) {
            $result[$case->value] = $case->readableText();
        }

        return $reverse ? array_flip($result) : $result;
    }
}
