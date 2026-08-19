@extends('layouts.app')

@section('title', 'Import: ' . $import->file_name)

@section('content')
<div class="mb-8 sm:flex sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Import Details</h1>
        <p class="mt-2 text-sm text-gray-700">File: <span class="font-medium">{{ $import->file_name }}</span> &middot; Uploaded on {{ $import->created_at->format('M d, Y') }}</p>
    </div>
    <div class="mt-4 sm:mt-0 space-x-3 flex">
        <a href="{{ route('imports.index') }}" class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
            Back to Imports
        </a>
        <a href="{{ route('campaigns.create', ['import_id' => $import->id]) }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            Create Campaign
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stats-card title="Total Records" :value="$import->total_records" icon="document-text" />
    <x-stats-card title="Valid Numbers" :value="$import->valid_records" icon="check-circle" valueClass="text-green-600" />
    <x-stats-card title="Invalid Numbers" :value="$import->invalid_records" icon="x-circle" valueClass="text-red-600" />
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 sm:flex sm:items-center sm:justify-between">
        <h3 class="text-base font-semibold text-gray-900">Imported Recipients</h3>
        <div class="mt-3 sm:ml-4 sm:mt-0">
            <form action="{{ route('imports.show', $import) }}" method="GET" class="relative rounded-md shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Search phone or data...">
            </form>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">#</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                    @php
                        // Show up to 4 columns from data as preview
                        $previewCols = is_array($import->columns) ? array_slice(array_diff($import->columns, [$import->phone_column]), 0, 4) : [];
                    @endphp
                    @foreach($previewCols as $col)
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $col }}</th>
                    @endforeach
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recipients as $index => $recipient)
                    <tr class="hover:bg-gray-50 transition-colors {{ !$recipient->is_valid ? 'border-l-4 border-l-red-500' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $recipients->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium {{ $recipient->is_valid ? 'text-gray-900' : 'text-red-600' }}">
                                {{ $recipient->phone_number ?? 'Missing' }}
                            </div>
                        </td>
                        @foreach($previewCols as $col)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">
                                {{ $recipient->data[$col] ?? '-' }}
                            </td>
                        @endforeach
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($recipient->is_valid)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Valid</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Invalid</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($previewCols) + 3 }}" class="px-6 py-8 text-center text-sm text-gray-500">
                            No recipients found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($recipients->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $recipients->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
