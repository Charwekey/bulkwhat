@extends('layouts.app')

@section('title', 'Message Templates')

@section('content')
<!-- Page Header -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Message Templates</h1>
        <p class="mt-1 text-sm text-gray-600">Create, edit, and organize reusable message templates for your WhatsApp campaigns.</p>
    </div>
    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
        <button onclick="document.getElementById('categoryModal').classList.remove('hidden')" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg shadow-sm whitespace-nowrap transition-colors">
            <svg class="-ml-0.5 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New Category
        </button>
        <a href="{{ route('templates.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-md whitespace-nowrap transition-colors">
            <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Create Template
        </a>
    </div>
</div>

<!-- Category Tabs & Search Bar -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('templates.index') }}" class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ !$categoryId ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
            All Categories
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('templates.index', ['category_id' => $cat->id]) }}" class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $categoryId == $cat->id ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                {{ $cat->name }} <span class="ml-1 opacity-75 font-normal">({{ $cat->templates_count }})</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('templates.index') }}" class="w-full md:w-72">
        @if($categoryId)
            <input type="hidden" name="category_id" value="{{ $categoryId }}">
        @endif
        <div class="relative">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search templates..." class="w-full rounded-lg border-0 py-2 pl-9 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </div>
    </form>
</div>

<!-- Template Grid -->
@if($templates->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($templates as $template)
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-5 flex flex-col justify-between hover:border-indigo-300 transition-all">
                <div>
                    <!-- Category Badge -->
                    <div class="mb-3">
                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                            {{ $template->category->name ?? 'General' }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="text-base font-bold text-gray-900 mb-2 leading-snug">{{ $template->title }}</h3>
                    
                    <!-- Template Body Preview -->
                    <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-700 font-mono whitespace-pre-wrap leading-relaxed border border-gray-100 mb-4 line-clamp-5">
                        {{ $template->body }}
                    </div>

                    <!-- Placeholders -->
                    @if(!empty($template->placeholders))
                        <div class="flex flex-wrap gap-1 mb-4">
                            @foreach($template->placeholders as $ph)
                                <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">[{{ $ph }}]</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Always Visible Action Bar (Edit & Delete) -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('templates.edit', $template) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-md border border-indigo-200/60 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Edit Template
                    </a>

                    <form action="{{ route('templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-md border border-red-200/60 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $templates->links() }}
    </div>
@else
    <x-empty-state 
        title="No templates found" 
        description="Create your first message template to quickly reuse common messages in campaigns."
        actionUrl="{{ route('templates.create') }}"
        actionText="Create Template"
    />
@endif

<!-- New Category Modal -->
<div id="categoryModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-gray-200">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Create Template Category</h3>
        <form action="{{ route('template-categories.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="cat_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" name="name" id="cat_name" required placeholder="e.g. Examination Results" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="mb-6">
                <label for="cat_desc" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                <textarea name="description" id="cat_desc" rows="2" placeholder="Short description..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('categoryModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-md">Save Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
