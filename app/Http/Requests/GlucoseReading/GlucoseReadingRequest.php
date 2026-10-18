<?php

namespace App\Http\Requests\GlucoseReading;

use App\Enums\ReadingContextEnum;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GlucoseReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'glucose_level' => ['required', 'integer', 'between:20,600'],
            'measured_at' => ['required', 'date', 'before_or_equal:now'],
            'reading_context' => ['required', Rule::enum(ReadingContextEnum::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Anchor the incoming datetime-local string to the app's configured timezone
     * so the stored timestamp reflects the user's local wall-clock time.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('measured_at') && is_string($this->measured_at)) {
            $this->merge([
                'measured_at' => Carbon::createFromFormat(
                    'Y-m-d\TH:i',
                    $this->measured_at,
                    config('app.timezone'),
                ),
            ]);
        }
    }
}
