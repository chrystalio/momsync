<?php

use App\Models\GlucoseReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
    config()->set('services.telegram.bot_token', 'test-token');
    config()->set('services.telegram.chat_id', 500202371);
});

function webhookPayload(string $text, ?int $chatId = 500202371): array
{
    return [
        'message' => [
            'chat' => ['id' => $chatId],
            'text' => $text,
        ],
    ];
}

it('creates a reading on valid /reading with context', function () {
    Carbon::setTestNow('2026-10-18 14:32:00');

    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading 120 fasting'))
        ->assertOk();

    expect(GlucoseReading::count())->toBe(1);
    $row = GlucoseReading::first();
    expect($row->glucose_level)->toBe(120)
        ->and($row->reading_context->value)->toBe('fasting')
        ->and($row->source)->toBe('telegram');

    Http::assertSent(fn ($r) => str_contains((string) $r['text'], 'Logged 120 mg/dL'));
});

it('defaults context to random when omitted', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading 95'))
        ->assertOk();

    expect(GlucoseReading::first()->reading_context->value)->toBe('random');
});

it('persists notes from quoted arg', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading 95 after_meal "felt good"'))
        ->assertOk();

    expect(GlucoseReading::first()->notes)->toBe('felt good');
});

it('replies with usage text on /help', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload('/help'))
        ->assertOk();

    Http::assertSent(fn ($r) => str_contains((string) $r['text'], '/reading')
        && str_contains((string) $r['text'], 'fasting'));
});

it('ignores untrusted chat_id silently', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading 120', chatId: 999))
        ->assertOk();

    expect(GlucoseReading::count())->toBe(0);
    Http::assertNothingSent();
});

it('returns 403 when secret token is configured but header missing', function () {
    config()->set('services.telegram.webhook_secret', 'shh');

    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading 120'))
        ->assertForbidden();

    expect(GlucoseReading::count())->toBe(0);
});

it('returns 200 when secret token header matches', function () {
    config()->set('services.telegram.webhook_secret', 'shh');

    $this->withHeaders(['X-Telegram-Bot-Api-Secret-Token' => 'shh'])
        ->postJson(route('webhooks.telegram'), webhookPayload('/reading 120'))
        ->assertOk();

    expect(GlucoseReading::count())->toBe(1);
});

it('replies with error on invalid value', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading abc'))
        ->assertOk();

    expect(GlucoseReading::count())->toBe(0);
    Http::assertSent(fn ($r) => str_contains((string) $r['text'], 'whole number'));
});

it('replies with error on out-of-range value', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading 10'))
        ->assertOk();

    expect(GlucoseReading::count())->toBe(0);
    Http::assertSent(fn ($r) => str_contains((string) $r['text'], 'whole number'));
});

it('replies with error on invalid context', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading 120 mystery'))
        ->assertOk();

    expect(GlucoseReading::count())->toBe(0);
    Http::assertSent(fn ($r) => str_contains((string) $r['text'], 'Unknown context'));
});

it('replies with error on unknown command', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload('/wave hello'))
        ->assertOk();

    expect(GlucoseReading::count())->toBe(0);
    Http::assertSent(fn ($r) => str_contains((string) $r['text'], 'Unknown command'));
});

it('replies with error on empty text', function () {
    $this->postJson(route('webhooks.telegram'), webhookPayload(''))
        ->assertOk();

    Http::assertSent(fn ($r) => str_contains((string) $r['text'], 'Send a command'));
});

it('returns 200 silently when payload lacks message.text', function () {
    $this->postJson(route('webhooks.telegram'), ['message' => ['chat' => ['id' => 500202371]]])
        ->assertOk();

    expect(GlucoseReading::count())->toBe(0);
    Http::assertNothingSent();
});

it('still returns 200 when telegram sendMessage fails', function () {
    Http::fake(['api.telegram.org/*' => Http::response('', 500)]);

    $this->postJson(route('webhooks.telegram'), webhookPayload('/reading 120'))
        ->assertOk();

    expect(GlucoseReading::count())->toBe(1);
});
