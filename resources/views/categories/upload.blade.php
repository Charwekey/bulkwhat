@extends('layouts.app')

@section('title', 'Upload Data for Category - ' . $category->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('categories.show', $category) }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Category
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Upload Excel Data for Category</h1>
        <p class="text-sm text-gray-600 mt-1">Category: <span class="font-bold text-indigo-600">{{ $category->name }}</span></p>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <form action="{{ route('categories.process-upload', $category) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Excel / CSV File</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md p-2">
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: .xlsx, .xls, .csv (Max size: 10MB)</p>
                </div>

                <div>
                    <label for="country_code" class="block text-sm font-medium text-gray-700">Country Code for Phone Formatting</label>
                    <input type="text" name="country_code" id="country_code" value="233" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="text-xs text-gray-500 mt-1">Default is 233 (Ghana). Phone numbers without country code will automatically be formatted.</p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-md p-4 text-xs text-amber-800">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-amber-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>Category Data Update:</strong> Uploading this Excel file will append/update student contact records directly inside the <strong>{{ $category->name }}</strong> category without affecting other categories in the database.
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('categories.show', $category) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-500 shadow-sm">
                        Process & Save to {{ $category->name }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
