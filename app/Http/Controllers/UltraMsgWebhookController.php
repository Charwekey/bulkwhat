<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UltraMsgWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('UltraMsg Webhook Received', ['payload' => $payload]);

        if (isset($payload['event_type']) && $payload['event_type'] === 'message_ack') {
            $messageId = $payload['data']['id'] ?? null;
            $status = $payload['data']['status'] ?? null; // e.g., sent, delivered, viewed

            if ($messageId && $status) {
                $message = Message::where('ultramsg_message_id', $messageId)->first();

                if ($message) {
                    $message->update([
                        'status' => $status,
                        'error_message' => null
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
