<?php

namespace App\Http\Controllers;

use App\Services\SMessService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private SMessService $smessService
    ) {}

    public function index()
    {
        $apiKey = config('smess.api_key') ?? env('SMESS_API_KEYS', '');
        $maskedApiKey = $apiKey ? substr($apiKey, 0, 5) . str_repeat('*', max(0, strlen($apiKey) - 9)) . substr($apiKey, -4) : '';

        $settings = [
            'api_key' => $apiKey,
            'masked_api_key' => $maskedApiKey,
            'base_url' => config('smess.base_url', 'https://smess.io/api'),
            'default_country_code' => config('smess.default_country_code', '233'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        return back()->with('info', 'Your SMess API keys are loaded from your .env file.');
    }

    public function testConnection()
    {
        try {
            $result = $this->smessService->validateCredentials();
            
            if ($result['valid']) {
                return response()->json([
                    'success' => true,
                    'message' => 'SMess API Connection successful!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Connection failed. Please check your SMESS_API_KEYS.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ]);
        }
    }
}
