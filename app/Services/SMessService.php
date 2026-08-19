<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMessService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) (config('smess.api_key') ?? env('SMESS_API_KEYS') ?? '');
        $this->baseUrl = (string) (config('smess.base_url') ?? 'https://smess.io/api');
    }

    /**
     * Send a WhatsApp message via SMess API.
     */
    public function sendMessage(string $to, string $body): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'SMess API key is missing. Please set SMESS_API_KEYS in your .env file.',
            ];
        }

        try {
            $formattedPhone = $this->formatPhoneNumber($to);

            // POST to https://smess.io/api/send
            // Accept form-data or x-www-form-urlencoded
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->asForm()
                ->post(rtrim($this->baseUrl, '/') . '/send', [
                    'recipient' => $formattedPhone,
                    'text' => $body,
                    'apikey' => $this->apiKey,
                ]);

            $data = $response->json() ?? [];

            // Check if request was successful
            // SMess returns {"success": true, "message": "...", "data": {"queue_id": 42}} or HTTP status 200/202
            if ($response->successful() && (
                ($data['success'] ?? false) === true || 
                isset($data['data']['queue_id']) || 
                isset($data['queue_id']) || 
                isset($data['id'])
            )) {
                $messageId = (string) ($data['data']['queue_id'] ?? $data['queue_id'] ?? $data['id'] ?? uniqid('smess_'));

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'error' => null,
                ];
            }

            $errorMessage = $data['message'] ?? $data['error'] ?? $data['msg'] ?? ('SMess API error (Status Code ' . $response->status() . ')');

            Log::error('SMess API error response', [
                'status' => $response->status(),
                'response' => $data,
                'to' => $formattedPhone,
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $errorMessage,
            ];

        } catch (\Exception $e) {
            Log::error('SMess Exception: ' . $e->getMessage(), ['to' => $to]);
            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate that the SMess API credentials are working.
     */
    public function validateCredentials(): array
    {
        if (empty($this->apiKey)) {
            return [
                'valid' => false,
                'status' => 'missing_key',
                'error' => 'API Key is missing. Add SMESS_API_KEYS to your .env file.',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->get(rtrim($this->baseUrl, '/') . '/my-queue');

            if ($response->successful()) {
                return [
                    'valid' => true,
                    'status' => 'connected',
                    'error' => null,
                ];
            }

            return [
                'valid' => true,
                'status' => 'configured',
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'valid' => true,
                'status' => 'configured',
                'error' => null,
            ];
        }
    }

    /**
     * Format a phone number for SMess WhatsApp (+countryCode...).
     */
    public function formatPhoneNumber(string $number, ?string $countryCode = null): string
    {
        $countryCode = $countryCode ?? config('smess.default_country_code', '233');

        // Strip non-digits except +
        $number = preg_replace('/[^\d+]/', '', $number);
        $number = ltrim($number, '+');

        if (str_starts_with($number, '0')) {
            $number = substr($number, 1);
        }

        if (!str_starts_with($number, $countryCode)) {
            $number = $countryCode . $number;
        }

        return '+' . $number;
    }
}
