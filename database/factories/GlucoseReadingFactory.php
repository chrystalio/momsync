<?php

namespace Database\Factories;

use App\Enums\ReadingContextEnum;
use App\Models\GlucoseReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GlucoseReading>
 */
class GlucoseReadingFactory extends Factory
{
    protected $model = GlucoseReading::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'glucose_level' => $this->faker->numberBetween(80, 180),
            'measured_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'reading_context' => $this->faker->randomElement(ReadingContextEnum::cases()),
            'notes' => null,
            'source' => 'web',
        ];
    }
}
