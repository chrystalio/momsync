<?php

namespace App\Http\Controllers;

use App\Enums\ReadingContextEnum;
use App\Http\Requests\GlucoseReading\StoreGlucoseReadingRequest;
use App\Models\GlucoseReading;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GlucoseReadingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('GlucoseReadings/Index', [
            'readings' => GlucoseReading::orderBy('measured_at', 'desc')->get(),
            'contexts' => collect(ReadingContextEnum::cases())
                ->map(fn (ReadingContextEnum $c) => ['value' => $c->value, 'label' => $c->readableText()])
                ->all(),
        ]);
    }

    public function store(StoreGlucoseReadingRequest $request): RedirectResponse
    {
        GlucoseReading::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reading logged.')]);

        return redirect()->route('glucose-readings.index');
    }

    public function update(StoreGlucoseReadingRequest $request, GlucoseReading $reading): RedirectResponse
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
