<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }

        $status = $request->query('status');

        $messages = $campaign->messages()
            ->with('recipient')
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate(25);

        return view('messages.index', compact('campaign', 'messages', 'status'));
    }
}
