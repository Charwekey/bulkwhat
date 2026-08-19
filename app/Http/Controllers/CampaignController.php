<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Import;
use App\Models\StudentCategory;
use App\Models\Template;
use App\Models\TemplateCategory;
use App\Services\MessageTemplateService;
use App\Services\SMessService;
use App\Jobs\ProcessBulkCampaignJob;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct(
        private MessageTemplateService $messageTemplateService,
        private SMessService $smessService
    ) {}

    public function index(Request $request)
    {
        $campaigns = Campaign::where('user_id', $request->user()->id)
            ->with(['import', 'studentCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create(Request $request)
    {
        $imports = Import::where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = StudentCategory::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        })
        ->whereNull('parent_id')
        ->with('children')
        ->get();

        return view('campaigns.create', compact('imports', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_type' => 'required|in:import,category',
            'import_id' => 'required_if:target_type,import|nullable|exists:imports,id',
            'student_category_id' => 'required_if:target_type,category|nullable|exists:student_categories,id',
        ]);

        $importId = null;
        $categoryId = null;
        $totalRecipients = 0;

        if ($request->input('target_type') === 'category') {
            $categoryId = $request->input('student_category_id');
            $category = StudentCategory::where('id', $categoryId)
                ->where(function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
                })->firstOrFail();

            $totalRecipients = $category->getAllValidRecipientsQuery()->count();
        } else {
            $importId = $request->input('import_id');
            $import = Import::where('id', $importId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            $totalRecipients = $import->valid_records;
        }

        $campaign = Campaign::create([
            'user_id' => $request->user()->id,
            'import_id' => $importId,
            'student_category_id' => $categoryId,
            'name' => $request->input('name'),
            'message_template' => '',
            'status' => 'draft',
            'total_recipients' => $totalRecipients,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        return redirect()->route('campaigns.edit', $campaign)
            ->with('success', 'Campaign created. Please compose your message.');
    }

    public function edit(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($campaign->status !== 'draft') {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', 'Only draft campaigns can be edited.');
        }

        $columns = [];
        $sampleData = [];

        if ($campaign->import) {
            $columns = $campaign->import->columns ?? [];
            $sampleRecipient = $campaign->import->recipients()->where('is_valid', true)->first();
            $sampleData = $sampleRecipient ? $sampleRecipient->data : [];
        } elseif ($campaign->studentCategory) {
            $sampleRecipient = $campaign->studentCategory->getAllValidRecipientsQuery()->first();
            if ($sampleRecipient) {
                $columns = array_keys($sampleRecipient->data);
                $sampleData = $sampleRecipient->data;
            }
        }

        if (empty($columns)) {
            $columns = ['Name', 'Programme', 'Index Number'];
        }

        // Fetch saved templates grouped by category
        $templateCategories = TemplateCategory::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        })->with(['templates' => function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        }])->get();

        $uncategorizedTemplates = Template::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        })->whereNull('template_category_id')->get();

        return view('campaigns.edit', compact('campaign', 'columns', 'sampleData', 'templateCategories', 'uncategorizedTemplates'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($campaign->status !== 'draft') {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', 'Only draft campaigns can be edited.');
        }

        $request->validate([
            'message_template' => 'required|string|min:5',
        ]);

        $campaign->update([
            'message_template' => $request->input('message_template'),
        ]);

        return redirect()->route('campaigns.preview', $campaign)
            ->with('success', 'Message template saved.');
    }

    public function show(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }

        $messages = $campaign->messages()->with('recipient')->paginate(20);

        return view('campaigns.show', compact('campaign', 'messages'));
    }

    public function preview(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }

        $recipients = [];

        if ($campaign->import) {
            $recipients = $campaign->import->recipients()->where('is_valid', true)->limit(5)->get();
        } elseif ($campaign->studentCategory) {
            $recipients = $campaign->studentCategory->getAllValidRecipientsQuery()->limit(5)->get();
        }

        $previews = [];

        foreach ($recipients as $recipient) {
            $previews[] = [
                'recipient' => $recipient,
                'message' => $this->messageTemplateService->personalizeMessage($campaign->message_template, $recipient->data)
            ];
        }

        return view('campaigns.preview', compact('campaign', 'previews'));
    }

    public function testSend(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'test_phone_number' => 'required|string',
        ]);

        $recipient = null;
        if ($campaign->import) {
            $recipient = $campaign->import->recipients()->where('is_valid', true)->first();
        } elseif ($campaign->studentCategory) {
            $recipient = $campaign->studentCategory->getAllValidRecipientsQuery()->first();
        }

        $sampleData = $recipient ? $recipient->data : ['Name' => 'Test Student', 'Programme' => 'Sample Program'];
        $messageText = $this->messageTemplateService->personalizeMessage($campaign->message_template, $sampleData);

        try {
            $result = $this->smessService->sendMessage($request->input('test_phone_number'), $messageText);
            
            if (isset($result['success']) && $result['success']) {
                return back()->with('success', 'Test message sent via SMess successfully (Queue ID: ' . ($result['message_id'] ?? 'N/A') . ').');
            } else {
                return back()->with('error', 'Failed to send test message: ' . ($result['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error sending test message: ' . $e->getMessage());
        }
    }

    public function send(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($campaign->status !== 'draft') {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', 'Only draft campaigns can be sent.');
        }

        $campaign->update(['status' => 'sending']);

        ProcessBulkCampaignJob::dispatch($campaign->id);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign processing started.');
    }

    public function destroy(Request $request, Campaign $campaign)
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($campaign->status !== 'draft') {
            return back()->with('error', 'Only draft campaigns can be deleted.');
        }

        $campaign->delete();

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }
}
