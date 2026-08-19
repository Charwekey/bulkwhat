<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Import;
use App\Models\Message;
use App\Models\Recipient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $totalCampaigns = Campaign::where('user_id', $userId)->count();
        
        $totalMessagesSent = Message::whereHas('campaign', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('status', 'sent')->count();

        $totalMessagesAttempted = Message::whereHas('campaign', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereIn('status', ['sent', 'failed'])->count();

        $successRate = $totalMessagesAttempted > 0 
            ? round(($totalMessagesSent / $totalMessagesAttempted) * 100, 2) 
            : 0;

        $recentCampaigns = Campaign::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalImports = Import::where('user_id', $userId)->count();

        $totalRecipients = Recipient::whereHas('import', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        return view('dashboard', compact(
            'totalCampaigns',
            'totalMessagesSent',
            'successRate',
            'recentCampaigns',
            'totalImports',
            'totalRecipients'
        ));
    }
}
