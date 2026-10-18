<?php

use App\Enums\ReadingContextEnum;
use App\Services\Telegram\TelegramCommandParser;

function parser(): TelegramCommandParser
{
    return new TelegramCommandParser;
}

it('parses /help', function () {
    $cmd = parser()->parse('/help');

    expect($cmd->name)->toBe('help')
        ->and($cmd->error)->toBeNull();
});

it('parses /reading with just a value', function () {
    $cmd = parser()->parse('/reading 120');

    expect($cmd->name)->toBe('reading')
        ->and($cmd->value)->toBe(120)
        ->and($cmd->context)->toBeNull()
        ->and($cmd->notes)->toBeNull()
        ->and($cmd->error)->toBeNull();
});

it('parses /reading with value and context', function () {
    $cmd = parser()->parse('/reading 95 after_meal');

    expect($cmd->value)->toBe(95)
        ->and($cmd->context)->toBe(ReadingContextEnum::AfterMeal)
        ->and($cmd->notes)->toBeNull()
        ->and($cmd->error)->toBeNull();
});

it('parses /reading with value, context, and quoted notes', function () {
    $cmd = parser()->parse('/reading 110 bedtime "slept badly"');

    expect($cmd->value)->toBe(110)
        ->and($cmd->context)->toBe(ReadingContextEnum::Bedtime)
        ->and($cmd->notes)->toBe('slept badly')
        ->and($cmd->error)->toBeNull();
});

it('parses /reading with value and quoted notes only (no context)', function () {
    $cmd = parser()->parse('/reading 88 "morning check"');

    expect($cmd->value)->toBe(88)
        ->and($cmd->context)->toBeNull()
        ->and($cmd->notes)->toBe('morning check');
});

it('accepts all five context values', function (string $token, ReadingContextEnum $expected) {
    $cmd = parser()->parse("/reading 100 {$token}");

    expect($cmd->context)->toBe($expected);
})->with([
    'fasting' => ['fasting', ReadingContextEnum::Fasting],
    'before_meal' => ['before_meal', ReadingContextEnum::BeforeMeal],
    'after_meal' => ['after_meal', ReadingContextEnum::AfterMeal],
    'bedtime' => ['bedtime', ReadingContextEnum::Bedtime],
    'random' => ['random', ReadingContextEnum::Random],
]);

it('errors on empty text', function () {
    expect(parser()->parse('')->error)->toBe('Send a command. /help for usage.')
        ->and(parser()->parse('   ')->error)->toBe('Send a command. /help for usage.');
});

it('errors on missing leading slash', function () {
    expect(parser()->parse('reading 120')->error)->toBe('Unknown command. /help for usage.');
});

it('errors on unknown command name', function () {
    expect(parser()->parse('/wave hello')->error)->toBe('Unknown command. /help for usage.');
});

it('errors on non-integer value', function () {
    expect(parser()->parse('/reading abc')->error)
        ->toBe('Value must be a whole number between 20 and 600 mg/dL.');
});

it('errors on decimal value', function () {
    expect(parser()->parse('/reading 5.5')->error)
        ->toBe('Value must be a whole number between 20 and 600 mg/dL.');
});

it('errors on out-of-range value (too low)', function () {
    expect(parser()->parse('/reading 10')->error)
        ->toBe('Value must be a whole number between 20 and 600 mg/dL.');
});

it('errors on out-of-range value (too high)', function () {
    expect(parser()->parse('/reading 700')->error)
        ->toBe('Value must be a whole number between 20 and 600 mg/dL.');
});

it('errors on unknown context token', function () {
    expect(parser()->parse('/reading 120 mystery')->error)
        ->toBe('Unknown context. Use one of: fasting, before_meal, after_meal, bedtime, random');
});

it('ignores extra tokens after notes', function () {
    $cmd = parser()->parse('/reading 100 fasting "ok" trailing junk');

    expect($cmd->value)->toBe(100)
        ->and($cmd->context)->toBe(ReadingContextEnum::Fasting)
        ->and($cmd->notes)->toBe('ok');
});
