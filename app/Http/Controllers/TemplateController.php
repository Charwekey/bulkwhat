<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateCategory;
use App\Services\MessageTemplateService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function __construct(
        private MessageTemplateService $templateService
    ) {}

    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        $search = $request->query('search');

        $categories = TemplateCategory::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        })->withCount('templates')->get();

        $templates = Template::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        })
        ->when($categoryId, function ($q) use ($categoryId) {
            $q->where('template_category_id', $categoryId);
        })
        ->when($search, function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('body', 'like', "%{$search}%");
        })
        ->with('category')
        ->orderBy('created_at', 'desc')
        ->paginate(12);

        return view('templates.index', compact('templates', 'categories', 'categoryId', 'search'));
    }

    public function create(Request $request)
    {
        $categories = TemplateCategory::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        })->get();

        return view('templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:5',
            'template_category_id' => 'nullable|exists:template_categories,id',
        ]);

        $placeholders = $this->templateService->extractPlaceholders($request->input('body'));

        $template = Template::create([
            'user_id' => auth()->id(),
            'template_category_id' => $request->input('template_category_id'),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'placeholders' => $placeholders,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template saved successfully!',
                'template' => $template,
            ]);
        }

        return redirect()->route('templates.index')
            ->with('success', 'Message template created successfully.');
    }

    public function edit(Request $request, Template $template)
    {
        $categories = TemplateCategory::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        })->get();

        return view('templates.edit', compact('template', 'categories'));
    }

    public function update(Request $request, Template $template)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:5',
            'template_category_id' => 'nullable|exists:template_categories,id',
        ]);

        $placeholders = $this->templateService->extractPlaceholders($request->input('body'));

        $template->update([
            'template_category_id' => $request->input('template_category_id'),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'placeholders' => $placeholders,
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Message template updated successfully.');
    }

    public function destroy(Request $request, Template $template)
    {
        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * API endpoint to fetch all templates for dropdown insertion in campaign composer.
     */
    public function getTemplatesJson(Request $request)
    {
        $templates = Template::where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhereNull('user_id');
        })->with('category')->get();

        return response()->json($templates);
    }
}
