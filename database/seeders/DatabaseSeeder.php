<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Kristoff',
            'email' => 'kristoff@momsync.krisdev.my.id',
            'password' => bcrypt('admin123'),
        ]);

        $this->call(GlucoseReadingSeeder::class);
    }
}
