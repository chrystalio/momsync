<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Throwable;

class TelegramClient
{
    public function sendMessage(int $chatId, string $text): bool
    {
        try {
            $response = Http::post(
                sprintf('https://api.telegram.org/bot%s/sendMessage', config('services.telegram.bot_token')),
                ['chat_id' => $chatId, 'text' => $text],
            );

            return $response->successful();
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
