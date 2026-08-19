@extends('layouts.app')

@section('title', 'Category - ' . $category->name)

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('categories.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Categories
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2 flex items-center gap-3">
            {{ $category->name }}
            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                {{ number_format($totalCount) }} Total Contacts
            </span>
        </h1>
        @if($category->description)
            <p class="mt-1 text-sm text-gray-500">{{ $category->description }}</p>
        @endif
    </div>
    
    <div class="mt-4 sm:mt-0 flex gap-3">
        <a href="{{ route('categories.upload', $category) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
            </svg>
            Upload / Update Excel Data
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="mb-6 flex justify-between items-center">
    <form method="GET" action="{{ route('categories.show', $category) }}" class="w-full max-w-md">
        <div class="relative">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search students..." class="w-full rounded-md border-0 py-2 pl-9 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </div>
    </form>
</div>

@php
    $columns = [];
    if ($recipients->count() > 0) {
        $firstRec = $recipients->first();
        if (!empty($firstRec->data) && is_array($firstRec->data)) {
            $columns = array_keys($firstRec->data);
        }
    }
@endphp

<!-- Dynamic Excel Data Table -->
<div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
    @if($recipients->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-indigo-600 uppercase tracking-wider">Formatted WhatsApp Phone</th>
                        @foreach($columns as $col)
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($recipients as $index => $recipient)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                {{ $recipients->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-indigo-600 font-mono">
                                {{ $recipient->phone_number }}
                            </td>
                            @foreach($columns as $col)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $recipient->data[$col] ?? '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $recipients->links() }}
        </div>
    @else
        <div class="p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">No student contacts in this category</h3>
            <p class="mt-1 text-sm text-gray-500">Upload an Excel or CSV file to add student contacts to {{ $category->name }}.</p>
            <div class="mt-6">
                <a href="{{ route('categories.upload', $category) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Upload Excel File
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
