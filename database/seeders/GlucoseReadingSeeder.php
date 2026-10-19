<?php

namespace Database\Seeders;

use App\Enums\ReadingContextEnum;
use App\Models\GlucoseReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlucoseReadingSeeder extends Seeder
{
    /**
     * Real glucose readings dated October 2026. measured_at is randomized within
     * a 15-minute morning window (05:45–06:00). All entries are fasting readings.
     *
     * Run with: php artisan db:seed --class=GlucoseReadingSeeder
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            GlucoseReading::query()->delete();

            $readings = [
                '1/10' => 210,
                '2/10' => 299,
                '3/10' => 210,
                '4/10' => 111,
                '5/10' => 237,
                '6/10' => 179,
                '7/10' => 163,
                '8/10' => 192,
                '9/10' => 152,
                '10/10' => 78,
                '11/10' => 204,
                '12/10' => 297,
                '13/10' => 331,
                '14/10' => 311,
                '15/10' => 218,
                '16/10' => 305,
                '17/10' => 287,
                '18/10' => 344,
                '19/10' => 335,
            ];

            foreach ($readings as $date => $level) {
                [$day, $month] = explode('/', $date);

                $measuredAt = Carbon::create(2026, (int) $month, (int) $day, 5, 45, 0)
                    ->addSeconds(random_int(0, 900));

                GlucoseReading::create([
                    'glucose_level' => $level,
                    'measured_at' => $measuredAt,
                    'reading_context' => ReadingContextEnum::Fasting,
                    'source' => 'web',
                ]);
            }
        });
    }
}
