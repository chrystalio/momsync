<?php

use App\Services\Telegram\TelegramClient;
use Illuminate\Support\Facades\Http;

it('returns true when telegram returns 200', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true]),
    ]);

    config()->set('services.telegram.bot_token', 'test-token');

    expect((new TelegramClient)->sendMessage(123, 'hello'))->toBeTrue();
});

it('returns false when telegram returns 500', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response('', 500),
    ]);

    config()->set('services.telegram.bot_token', 'test-token');

    expect((new TelegramClient)->sendMessage(123, 'hello'))->toBeFalse();
});

it('posts to the bot API with the configured token and payload', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true]),
    ]);

    config()->set('services.telegram.bot_token', 'TOKEN123');

    (new TelegramClient)->sendMessage(456, 'world');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.telegram.org/botTOKEN123/sendMessage'
            && $request['chat_id'] === 456
            && $request['text'] === 'world';
    });
});
