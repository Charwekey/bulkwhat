<?php

namespace App\Http\Controllers;

use App\Models\TemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateCategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        TemplateCategory::create([
            'user_id' => auth()->id(),
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
        ]);

        return back()->with('success', 'Template Category created successfully.');
    }

    public function update(Request $request, TemplateCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $category->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
        ]);

        return back()->with('success', 'Template Category updated successfully.');
    }

    public function destroy(TemplateCategory $category)
    {
        $category->delete();

        return back()->with('success', 'Template Category deleted.');
    }
}
