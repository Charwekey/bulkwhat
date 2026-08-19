@extends('layouts.app')

@section('title', 'Create Campaign')

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ targetType: 'category' }">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Campaign</h1>
            <p class="mt-2 text-sm text-gray-700">Start a new bulk messaging campaign targeting a category or file import.</p>
        </div>
        <a href="{{ route('campaigns.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Back to Campaigns</a>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <form action="{{ route('campaigns.store') }}" method="POST" class="p-8">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-900">Campaign Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., First Semester Fee Reminders" class="mt-2 block w-full rounded-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Target Type Selection Tabs -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Select Campaign Target Group</label>
                    <input type="hidden" name="target_type" :value="targetType">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" @click="targetType = 'category'" :class="targetType === 'category' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 ring-2 ring-indigo-600' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'" class="p-4 border rounded-xl flex items-start space-x-3 text-left transition-all">
                            <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.97 5.97 0 0 0-.942 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-bold text-sm block">Student Category</span>
                                <span class="text-xs text-gray-500 block">Target Weekend, Regular, Evening, or Faculty groups</span>
                            </div>
                        </button>

                        <button type="button" @click="targetType = 'import'" :class="targetType === 'import' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 ring-2 ring-indigo-600' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'" class="p-4 border rounded-xl flex items-start space-x-3 text-left transition-all">
                            <div class="p-2 rounded-lg bg-gray-100 text-gray-600 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-bold text-sm block">Uploaded Excel File</span>
                                <span class="text-xs text-gray-500 block">Target a specific raw Excel file upload</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Category Target Selector -->
                <div x-show="targetType === 'category'">
                    <label for="student_category_id" class="block text-sm font-medium text-gray-900">Select Student Category</label>
                    <select id="student_category_id" name="student_category_id" class="mt-2 block w-full rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        <option value="">-- Choose Category --</option>
                        @foreach($categories as $pCat)
                            <optgroup label="{{ $pCat->name }}">
                                <option value="{{ $pCat->id }}">
                                    ★ {{ $pCat->name }} (All {{ number_format($pCat->getAllValidRecipientsQuery()->count()) }} Recipients)
                                </option>
                                @foreach($pCat->children as $cCat)
                                    <option value="{{ $cCat->id }}">
                                        └─ {{ $cCat->name }} ({{ number_format($cCat->getAllValidRecipientsQuery()->count()) }} Recipients)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Selecting a parent category (e.g. Undergraduate) automatically targets all students in its subcategories.</p>
                </div>

                <!-- Import File Selector -->
                <div x-show="targetType === 'import'" style="display: none;">
                    <label for="import_id" class="block text-sm font-medium text-gray-900">Select Contact List (Import)</label>
                    <select id="import_id" name="import_id" class="mt-2 block w-full rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        <option value="">-- Select an import --</option>
                        @foreach($imports as $import)
                            <option value="{{ $import->id }}" {{ (old('import_id') ?? request('import_id')) == $import->id ? 'selected' : '' }}>
                                {{ $import->file_name }} ({{ number_format($import->valid_records) }} valid recipients)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    Create & Compose Message
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
