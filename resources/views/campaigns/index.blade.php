@extends('layouts.app')

@section('title', 'Campaigns')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Campaigns</h1>
        <p class="mt-2 text-sm text-gray-700">Create and manage your WhatsApp messaging campaigns.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="{{ route('campaigns.create') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Campaign
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    @if($campaigns->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipients</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Progress</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($campaigns as $campaign)
                        @php
                            $total = $campaign->import ? $campaign->import->valid_records : 0;
                            $processed = $campaign->messages_sent + $campaign->messages_failed;
                            $percentage = $total > 0 ? min(100, round(($processed / $total) * 100)) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $campaign->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $campaign->import ? $campaign->import->file_name : 'No import attached' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="font-medium text-gray-900">{{ number_format($total) }}</span> total
                                @if($processed > 0)
                                    <div class="text-xs mt-1">
                                        <span class="text-green-600">{{ number_format($campaign->messages_sent) }} sent</span> &middot;
                                        <span class="text-red-600">{{ number_format($campaign->messages_failed) }} failed</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-xs font-medium text-gray-700 w-8">{{ $percentage }}%</span>
                                    <div class="w-full bg-gray-200 rounded-full h-2 ml-2">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <x-status-badge :status="$campaign->status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $campaign->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <a href="{{ route('campaigns.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    
                                    @if($campaign->status === 'draft')
                                        <a href="{{ route('campaigns.edit', $campaign) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this draft campaign?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $campaigns->links() }}
        </div>
    @else
        <x-empty-state>
            <x-slot name="title">No campaigns found</x-slot>
            <x-slot name="description">Start reaching out to your students by creating your first campaign.</x-slot>
            <x-slot name="action">
                <a href="{{ route('campaigns.create') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    Create Campaign
                </a>
            </x-slot>
        </x-empty-state>
    @endif
</div>
@endsection
