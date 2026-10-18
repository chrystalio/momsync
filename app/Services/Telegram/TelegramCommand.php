<?php

namespace App\Services\Telegram;

use App\Enums\ReadingContextEnum;

final readonly class TelegramCommand
{
    public function __construct(
        public string $name,
        public ?int $value = null,
        public ?ReadingContextEnum $context = null,
        public ?string $notes = null,
        public ?string $error = null,
    ) {}

    public static function error(string $message): self
    {
        return new self(name: '', error: $message);
    }

    public static function help(): self
    {
        return new self(name: 'help');
    }
}
