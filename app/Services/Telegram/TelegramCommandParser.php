<?php

namespace App\Services\Telegram;

use App\Enums\ReadingContextEnum;

class TelegramCommandParser
{
    private const MIN_VALUE = 20;

    private const MAX_VALUE = 600;

    public function parse(string $text): TelegramCommand
    {
        $text = trim($text);

        if ($text === '') {
            return TelegramCommand::error('Send a command. /help for usage.');
        }

        if (! preg_match('#^/(\S+)#', $text, $m)) {
            return TelegramCommand::error('Unknown command. /help for usage.');
        }

        $name = strtolower($m[1]);

        if (! ctype_lower($name)) {
            return TelegramCommand::error('Unknown command. /help for usage.');
        }

        return match ($name) {
            'help' => TelegramCommand::help(),
            'reading' => $this->parseReading(substr($text, strlen($m[0]))),
            default => TelegramCommand::error('Unknown command. /help for usage.'),
        };
    }

    private function parseReading(string $rest): TelegramCommand
    {
        $tokens = $this->tokenize($rest);

        if ($tokens === []) {
            return TelegramCommand::error('Value must be a whole number between 20 and 600 mg/dL.');
        }

        $valueToken = array_shift($tokens);

        if (! ctype_digit($valueToken)) {
            return TelegramCommand::error('Value must be a whole number between 20 and 600 mg/dL.');
        }

        $value = (int) $valueToken;

        if ($value < self::MIN_VALUE || $value > self::MAX_VALUE) {
            return TelegramCommand::error('Value must be a whole number between 20 and 600 mg/dL.');
        }

        $context = null;
        $notes = null;

        if ($tokens !== []) {
            $next = array_shift($tokens);

            if ($this->isContextToken($next)) {
                $context = ReadingContextEnum::from($next);
            } elseif ($this->isQuoted($next)) {
                $notes = $this->unquote($next);
            } else {
                return TelegramCommand::error('Unknown context. Use one of: fasting, before_meal, after_meal, bedtime, random');
            }
        }

        if ($tokens !== []) {
            $next = array_shift($tokens);

            if ($this->isQuoted($next)) {
                $notes = $this->unquote($next);
            }
        }

        return new TelegramCommand(name: 'reading', value: $value, context: $context, notes: $notes);
    }

    /** @return list<string> */
    private function tokenize(string $text): array
    {
        if ($text === '') {
            return [];
        }

        preg_match_all('/"[^"]+"|\S+/', $text, $matches);

        return $matches[0];
    }

    private function isContextToken(string $token): bool
    {
        return ReadingContextEnum::tryFrom($token) !== null;
    }

    private function isQuoted(string $token): bool
    {
        return strlen($token) >= 2
            && $token[0] === '"'
            && $token[strlen($token) - 1] === '"';
    }

    private function unquote(string $token): string
    {
        return substr($token, 1, -1);
    }
}
