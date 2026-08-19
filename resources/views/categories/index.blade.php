@extends('layouts.app')

@section('title', 'Student Contact Categories')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Student Contact Categories</h1>
        <p class="mt-1 text-sm text-gray-500">Organize students into levels, study modes, and faculties for targeted messaging.</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <button onclick="document.getElementById('newCategoryModal').classList.remove('hidden')" class="inline-flex items-center rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New Student Category
        </button>
    </div>
</div>

<!-- Category Tree Cards -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Summary Stat Card -->
    <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-xl p-6 text-white shadow-md flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-indigo-200 text-xs font-semibold uppercase tracking-wider">Total Database</span>
                <span class="bg-indigo-500/30 text-indigo-100 text-xs px-2.5 py-1 rounded-full border border-indigo-400/30">All Students</span>
            </div>
            <div class="text-4xl font-extrabold tracking-tight mt-2">{{ number_format($allStudentsCount) }}</div>
            <p class="text-indigo-100 text-sm mt-1">Valid WhatsApp Student Contacts across all categories.</p>
        </div>
        <div class="mt-6 border-t border-indigo-500/40 pt-4 flex justify-between items-center text-xs text-indigo-200">
            <span>Dynamic Category Filters Active</span>
            <svg class="h-5 w-5 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.97 5.97 0 0 0-.942 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
        </div>
    </div>
</div>

<!-- Main Categories List -->
<div class="space-y-6">
    @foreach($categories as $category)
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <!-- Parent Category Header -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr($category->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <a href="{{ route('categories.show', $category) }}" class="hover:text-indigo-600 transition-colors">
                                {{ $category->name }}
                            </a>
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                {{ number_format($category->getAllValidRecipientsQuery()->count()) }} Contacts
                            </span>
                        </h2>
                        @if($category->description)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $category->description }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('categories.upload', $category) }}" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="-ml-0.5 mr-1 h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        Upload Excel
                    </a>
                    <a href="{{ route('categories.show', $category) }}" class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                        View Students &rarr;
                    </a>
                </div>
            </div>

            <!-- Subcategories Grid -->
            @if($category->children->count() > 0)
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($category->children as $child)
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/50 hover:bg-white hover:shadow-sm hover:border-indigo-200 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-semibold text-gray-900 text-sm">
                                        <a href="{{ route('categories.show', $child) }}" class="hover:text-indigo-600">
                                            {{ $child->name }}
                                        </a>
                                    </h3>
                                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                                        {{ number_format($child->recipients_count) }}
                                    </span>
                                </div>
                                @if($child->description)
                                    <p class="text-xs text-gray-500 line-clamp-2 mt-1">{{ $child->description }}</p>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                                <a href="{{ route('categories.upload', $child) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                    </svg>
                                    Upload File
                                </a>
                                <a href="{{ route('categories.show', $child) }}" class="text-xs text-gray-500 hover:text-gray-700">
                                    View Data &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-xs text-gray-500">
                    No subcategories added under {{ $category->name }}. You can upload Excel files directly to this category or create subcategories.
                </div>
            @endif
        </div>
    @endforeach
</div>

<!-- Modal: Create Student Category -->
<div id="newCategoryModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-gray-200">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Create Student Category</h3>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" name="name" id="name" required placeholder="e.g. Weekend Students or Faculty of Science" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent Category (Optional)</label>
                <select name="parent_id" id="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">-- Main Top-Level Category --</option>
                    @foreach($categories as $pCat)
                        <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">E.g., select "Undergraduate Students" to make this a subcategory.</p>
            </div>

            <div class="mb-4">
                <label for="type" class="block text-sm font-medium text-gray-700">Category Type</label>
                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="study_mode">Study Mode (Regular, Weekend, Evening)</option>
                    <option value="level">Level of Study (Undergraduate, Postgraduate, Masters)</option>
                    <option value="faculty">Faculty / School</option>
                    <option value="custom">Custom Group</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                <textarea name="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Brief explanation of this student category..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('newCategoryModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-md">Create Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
