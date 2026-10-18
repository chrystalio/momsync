<?php

namespace Database\Seeders;

use App\Enums\ReadingContextEnum;
use App\Models\GlucoseReading;
use Illuminate\Database\Seeder;

class GlucoseReadingSeeder extends Seeder
{
    /**
     * Seed 30 days of glucose readings — roughly three per day, with glucose
     * levels and times-of-day chosen to be plausible for each context.
     *
     * Run with: php artisan db:seed --class=GlucoseReadingSeeder
     */
    public function run(): void
    {
        GlucoseReading::query()->delete();

        $now = now();
        $profiles = [
            'fasting' => ['hour' => 7, 'min' => 60, 'max' => 105],
            'before_meal' => ['hour' => 12, 'min' => 80, 'max' => 130],
            'after_meal' => ['hour' => 13, 'min' => 110, 'max' => 190],
            'bedtime' => ['hour' => 22, 'min' => 100, 'max' => 150],
            'random' => ['hour' => 16, 'min' => 85, 'max' => 170],
        ];

        for ($day = 29; $day >= 0; $day--) {
            $date = $now->copy()->subDays($day);
            $picks = (array) array_rand($profiles, random_int(2, 3));

            foreach ($picks as $value) {
                $context = ReadingContextEnum::from($value);
                $profile = $profiles[$value];

                GlucoseReading::create([
                    'glucose_level' => random_int($profile['min'], $profile['max']),
                    'measured_at' => $date->copy()->setTime(
                        $profile['hour'],
                        random_int(0, 59),
                    ),
                    'reading_context' => $context,
                    'notes' => null,
                    'source' => 'web',
                ]);
            }
        }
    }
}
