<?php

namespace App\Models;

use App\Enums\ReadingContextEnum;
use Database\Factories\GlucoseReadingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlucoseReading extends Model
{
    /** @use HasFactory<GlucoseReadingFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'glucose_level',
        'measured_at',
        'reading_context',
        'notes',
        'source',
    ];

    protected $casts = [
        'measured_at' => 'datetime',
        'glucose_level' => 'integer',
        'reading_context' => ReadingContextEnum::class,
    ];
}
