<?php

use App\Models\GlucoseReading;
use App\Models\User;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('redirects guests from index to login', function () {
    $this->get(route('glucose-readings.index'))->assertRedirect(route('login'));
});

it('shows readings on index ordered by measured_at desc', function () {
    $user = User::factory()->create();
    $older = GlucoseReading::factory()->create(['measured_at' => now()->subDays(2)]);
    $newer = GlucoseReading::factory()->create(['measured_at' => now()->subDay()]);

    $this->actingAs($user)
        ->get(route('glucose-readings.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('GlucoseReadings/Index')
            ->where('readings.0.id', $newer->id)
            ->where('readings.1.id', $older->id)
        );
});

it('redirects guests from store to login', function () {
    $this->post(route('glucose-readings.store'), [])->assertRedirect(route('login'));
});

it('validates glucose_level range on store', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('glucose-readings.store'), validPayload(['glucose_level' => 10]))
        ->assertSessionHasErrors('glucose_level');
});

it('validates measured_at is not in the future on store', function () {
    Carbon::setTestNow('2026-10-18 12:00:00');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('glucose-readings.store'), validPayload(['measured_at' => '2026-10-18T13:00']))
        ->assertSessionHasErrors('measured_at');
});

it('validates reading_context is a known enum value on store', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('glucose-readings.store'), validPayload(['reading_context' => 'mystery']))
        ->assertSessionHasErrors('reading_context');
});

it('creates a reading and redirects on valid store', function () {
    Carbon::setTestNow('2026-10-18 12:00:00');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('glucose-readings.store'), validPayload())
        ->assertRedirect(route('glucose-readings.index'));

    expect(GlucoseReading::count())->toBe(1);
});

it('returns 405 on the removed create route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/glucose-readings/create')
        ->assertMethodNotAllowed();
});

it('returns 404 on the removed edit route', function () {
    $user = User::factory()->create();
    $reading = GlucoseReading::factory()->create();

    $this->actingAs($user)
        ->get("/glucose-readings/{$reading->id}/edit")
        ->assertNotFound();
});

it('passes all five contexts to the index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('glucose-readings.index'))
        ->assertInertia(fn ($page) => $page
            ->component('GlucoseReadings/Index')
            ->has('contexts', 5)
        );
});

it('updates a reading', function () {
    Carbon::setTestNow('2026-10-18 12:00:00');
    $user = User::factory()->create();
    $reading = GlucoseReading::factory()->create(['glucose_level' => 100]);

    $this->actingAs($user)
        ->patch(route('glucose-readings.update', $reading), validPayload(['glucose_level' => 150]))
        ->assertRedirect(route('glucose-readings.index'));

    expect($reading->fresh()->glucose_level)->toBe(150);
});

it('validates the same rules on update', function () {
    $user = User::factory()->create();
    $reading = GlucoseReading::factory()->create();

    $this->actingAs($user)
        ->patch(route('glucose-readings.update', $reading), validPayload(['glucose_level' => 5]))
        ->assertSessionHasErrors('glucose_level');
});

it('deletes a reading', function () {
    $user = User::factory()->create();
    $reading = GlucoseReading::factory()->create();

    $this->actingAs($user)
        ->delete(route('glucose-readings.destroy', $reading))
        ->assertRedirect(route('glucose-readings.index'));

    expect(GlucoseReading::find($reading->id))->toBeNull();
});

it('returns 404 when deleting a missing reading', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->delete(route('glucose-readings.destroy', '01HZZZZZZZZZZZZZZZZZZZZZZZ'))
        ->assertNotFound();
});

function validPayload(array $overrides = []): array
{
    return array_merge([
        'glucose_level' => 120,
        'measured_at' => '2026-10-18T11:00',
        'reading_context' => 'fasting',
        'notes' => 'feeling good',
    ], $overrides);
}
