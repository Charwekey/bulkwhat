@extends('layouts.app')

@section('title', 'Edit Template - ' . $template->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('templates.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Templates
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Message Template</h1>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <form action="{{ route('templates.update', $template) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Template Title</label>
                    <input type="text" name="title" id="title" required value="{{ old('title', $template->title) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="template_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="template_category_id" id="template_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('template_category_id', $template->template_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700">Message Body</label>
                    <p class="text-xs text-gray-500 mb-2">Supported Placeholders: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-indigo-600 font-mono">[Student Name]</code>, <code class="bg-gray-100 px-1.5 py-0.5 rounded text-indigo-600 font-mono">@{{Programme}}</code>, <code class="bg-gray-100 px-1.5 py-0.5 rounded text-indigo-600 font-mono">[Index Number]</code></p>
                    <textarea name="body" id="body" rows="8" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">{{ old('body', $template->body) }}</textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('templates.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-500">Update Template</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
