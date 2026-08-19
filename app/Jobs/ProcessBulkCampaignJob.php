<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Message;
use App\Services\MessageTemplateService;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProcessBulkCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes to create all message records

    public function __construct(
        private int $campaignId
    ) {}

    public function handle(MessageTemplateService $templateService): void
    {
        $campaign = Campaign::with(['import', 'studentCategory'])->find($this->campaignId);
        if (!$campaign) return;

        // Set campaign to sending
        $campaign->update([
            'status' => 'sending',
            'started_at' => now(),
        ]);

        // Get all valid recipients for this campaign's target
        $recipients = collect();
        if ($campaign->import) {
            $recipients = $campaign->import->recipients()->where('is_valid', true)->get();
        } elseif ($campaign->studentCategory) {
            $recipients = $campaign->studentCategory->getAllValidRecipientsQuery()->get();
        }

        // Create Message records for each recipient and collect jobs
        $jobs = [];
        foreach ($recipients as $recipient) {
            $personalizedMessage = $templateService->personalizeMessage(
                $campaign->message_template,
                $recipient->data
            );

            $message = Message::create([
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'phone_number' => $recipient->phone_number,
                'personalized_message' => $personalizedMessage,
                'status' => 'pending',
            ]);

            $jobs[] = new SendWhatsAppMessageJob($message->id);
        }

        $campaignId = $this->campaignId;

        // Dispatch as a batch so we can track completion
        if (count($jobs) > 0) {
            Bus::batch($jobs)
                ->name('Campaign: ' . $campaign->name)
                ->allowFailures()
                ->then(function (Batch $batch) use ($campaignId) {
                    $campaign = Campaign::find($campaignId);
                    if ($campaign) {
                        $campaign->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }
                })
                ->catch(function (Batch $batch, \Throwable $e) use ($campaignId) {
                    Log::error('Campaign batch error', [
                        'campaign_id' => $campaignId,
                        'error' => $e->getMessage(),
                    ]);
                })
                ->finally(function (Batch $batch) use ($campaignId) {
                    $campaign = Campaign::find($campaignId);
                    if ($campaign && $campaign->status === 'sending') {
                        $campaign->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }
                })
                ->dispatch();
        } else {
            $campaign->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
        }
    }
}
