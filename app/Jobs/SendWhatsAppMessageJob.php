<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Campaign;
use App\Services\UltraMsgService;
use App\Services\MessageTemplateService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 30;

    public function __construct(
        private int $messageId
    ) {}

    public function handle(UltraMsgService $ultraMsg): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $message = Message::with('recipient', 'campaign')->find($this->messageId);
        if (!$message || in_array($message->status, ['sent', 'delivered'])) {
            return;
        }

        $message->update(['status' => 'queued']);

        $result = $ultraMsg->sendMessage(
            $message->phone_number,
            $message->personalized_message
        );

        if ($result['success']) {
            $message->update([
                'status' => 'sent',
                'ultramsg_message_id' => $result['message_id'],
                'sent_at' => now(),
            ]);
            Campaign::where('id', $message->campaign_id)->increment('sent_count');
        } else {
            $message->update([
                'status' => 'failed',
                'error_message' => $result['error'] ?? 'Unknown error',
            ]);
            Campaign::where('id', $message->campaign_id)->increment('failed_count');
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsAppMessageJob permanently failed', [
            'message_id' => $this->messageId,
            'error' => $exception->getMessage(),
        ]);

        $message = Message::find($this->messageId);
        if ($message && !in_array($message->status, ['sent', 'delivered', 'failed'])) {
            $message->update([
                'status' => 'failed',
                'error_message' => 'Job failed: ' . $exception->getMessage(),
            ]);
            Campaign::where('id', $message->campaign_id)->increment('failed_count');
        }
    }
}
