<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\ReadingContextEnum;
use App\Http\Controllers\Controller;
use App\Models\GlucoseReading;
use App\Services\Telegram\TelegramClient;
use App\Services\Telegram\TelegramCommand;
use App\Services\Telegram\TelegramCommandParser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function handle(
        Request $request,
        TelegramCommandParser $parser,
        TelegramClient $client,
    ): JsonResponse {
        $secret = config('services.telegram.webhook_secret');
        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            abort(403);
        }

        $data = json_decode($request->getContent(), true);
        $chatId = $data['message']['chat']['id'] ?? null;
        $text = $data['message']['text'] ?? null;

        $trusted = (int) config('services.telegram.chat_id');

        if (! is_int($chatId) && ! is_string($chatId) || (int) $chatId !== $trusted) {
            return response()->json(['ok' => true]);
        }

        if (! is_string($text)) {
            return response()->json(['ok' => true]);
        }

        $text = $text ?: '';

        $command = $parser->parse($text);
        $reply = $this->buildReply($command);

        if ($reply !== null) {
            $client->sendMessage($trusted, $reply);
        }

        return response()->json(['ok' => true]);
    }

    private function buildReply(TelegramCommand $command): ?string
    {
        if ($command->error !== null) {
            return $command->error;
        }

        if ($command->name === 'help') {
            return "/reading <value> [context] [\"notes\"]\n"
                .'context: fasting | before_meal | after_meal | bedtime | random'
                ."\nvalue: whole number in mg/dL (20–600)";
        }

        if ($command->name === 'reading' && $command->value !== null) {
            $context = $command->context ?? ReadingContextEnum::Random;

            GlucoseReading::create([
                'glucose_level' => $command->value,
                'measured_at' => Carbon::now(),
                'reading_context' => $context,
                'notes' => $command->notes,
                'source' => 'telegram',
            ]);

            $time = Carbon::now()->format('H:i');

            return sprintf(
                'Logged %d mg/dL (%s) at %s.',
                $command->value,
                $context->readableText(),
                $time,
            );
        }

        return null;
    }
}
