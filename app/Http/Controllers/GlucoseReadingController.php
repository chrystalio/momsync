<?php

namespace App\Http\Controllers;

use App\Enums\ReadingContextEnum;
use App\Http\Requests\GlucoseReading\GlucoseReadingRequest;
use App\Models\GlucoseReading;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GlucoseReadingController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'from_date' => ['nullable', 'date'],
            'reading_context' => ['nullable', Rule::enum(ReadingContextEnum::class)],
        ]);

        $readings = GlucoseReading::query()
            ->when($filters['from_date'] ?? null, fn ($q, string $date) => $q->whereDate('measured_at', '>=', $date))
            ->when($filters['reading_context'] ?? null, fn ($q, string $context) => $q->where('reading_context', $context))
            ->orderBy('measured_at', 'desc')
            ->get();

        return Inertia::render('GlucoseReadings/Index', [
            'readings' => $readings,
            'contexts' => collect(ReadingContextEnum::cases())
                ->map(fn (ReadingContextEnum $c) => ['value' => $c->value, 'label' => $c->readableText()])
                ->all(),
            'filters' => $filters,
        ]);
    }

    public function store(GlucoseReadingRequest $request): RedirectResponse
    {
        GlucoseReading::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reading logged.')]);

        return redirect()->route('glucose-readings.index');
    }

    public function update(GlucoseReadingRequest $request, GlucoseReading $reading): RedirectResponse
    {
        $reading->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reading updated.')]);

        return redirect()->route('glucose-readings.index');
    }

    public function destroy(GlucoseReading $reading): RedirectResponse
    {
        $reading->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reading deleted.')]);

        return redirect()->route('glucose-readings.index');
    }
}
