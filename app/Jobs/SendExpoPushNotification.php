<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendExpoPushNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::query()
            ->with(['expoPushTokens' => fn ($query) => $query->orderBy('id')])
            ->find($this->userId);

        if (! $user || $user->expoPushTokens->isEmpty()) {
            return;
        }

        $messages = $user->expoPushTokens
            ->map(fn ($pushToken): array => [
                'to' => $pushToken->token,
                'sound' => 'default',
                'title' => $this->title,
                'body' => $this->body,
                'data' => $this->data,
            ])
            ->values()
            ->all();

        $response = Http::acceptJson()
            ->asJson()
            ->post(
                'https://exp.host/--/api/v2/push/send',
                $messages,
            );

        if (! $response->successful()) {
            return;
        }

        $tickets = $response->json('data');

        if (! is_array($tickets)) {
            return;
        }

        foreach ($tickets as $index => $ticket) {
            if (
                data_get($ticket, 'status') !== 'error' ||
                data_get($ticket, 'details.error') !== 'DeviceNotRegistered'
            ) {
                continue;
            }

            $user->expoPushTokens->get($index)?->delete();
        }
    }
}
