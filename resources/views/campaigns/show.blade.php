@extends('layouts.app')

@section('title', 'Campaign: ' . $campaign->name)

@section('content')
<div class="mb-8 sm:flex sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $campaign->name }}</h1>
        <p class="mt-2 text-sm text-gray-700">Started: {{ $campaign->started_at ? $campaign->started_at->format('M d, Y H:i') : 'Not started' }}</p>
    </div>
    <div class="mt-4 sm:mt-0 space-x-3 flex">
        <a href="{{ route('campaigns.index') }}" class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
            Back to Campaigns
        </a>
        
        @if($campaign->status === 'draft')
            <a href="{{ route('campaigns.edit', $campaign) }}" class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                Edit Message
            </a>
            <a href="{{ route('campaigns.preview', $campaign) }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                Preview & Send
            </a>
        @endif
    </div>
</div>

@php
    $total = $campaign->total_recipients;
    $sent = $campaign->sent_count;
    $failed = $campaign->failed_count;
    $pending = max(0, $total - $sent - $failed);
    $processed = $sent + $failed;
    $successRate = $processed > 0 ? round(($sent / $processed) * 100, 1) : 0;
    $progressRate = $total > 0 ? min(100, round(($processed / $total) * 100, 1)) : 0;
    $sentPct = $total > 0 ? ($sent / $total) * 100 : 0;
    $failedPct = $total > 0 ? ($failed / $total) * 100 : 0;
@endphp

<!-- Stats Overview -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <x-stats-card title="Total Recipients" :value="$total" icon="users" />
    <x-stats-card title="Successfully Sent" :value="$sent" icon="check-circle" valueClass="text-green-600" />
    <x-stats-card title="Failed" :value="$failed" icon="x-circle" valueClass="text-red-600" />
    <x-stats-card title="Success Rate" :value="$successRate . '%'" icon="chart-bar" />
</div>

<!-- Progress Bar Component -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-900">Campaign Progress</h3>
        <span class="text-sm font-medium text-gray-700">{{ $progressRate }}% Complete</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden flex">
        @if($sentPct > 0)<div class="bg-green-500 h-4" style="width: {{ $sentPct }}%"></div>@endif
        @if($failedPct > 0)<div class="bg-red-500 h-4" style="width: {{ $failedPct }}%"></div>@endif
    </div>
    <div class="mt-3 flex items-center space-x-6 text-xs text-gray-500">
        <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-1.5"></span> Delivered</div>
        <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-1.5"></span> Failed</div>
        <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-gray-200 border border-gray-300 mr-1.5"></span> Pending ({{ $pending }})</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Campaign Details -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-900">Details</h3>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <div class="mt-1"><x-status-badge :status="$campaign->status" /></div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Target Audience</p>
                    <p class="mt-1 text-sm text-gray-900">
                        @if($campaign->studentCategory)
                            <a href="{{ route('categories.show', $campaign->studentCategory) }}" class="text-indigo-600 font-semibold hover:underline">
                                Category: {{ $campaign->studentCategory->name }}
                            </a>
                        @elseif($campaign->import)
                            <a href="{{ route('imports.show', $campaign->import) }}" class="text-indigo-600 font-semibold hover:underline">
                                Import: {{ $campaign->import->original_filename }}
                            </a>
                        @else
                            <span class="text-gray-500">No target specified</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Completed At</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $campaign->completed_at ? $campaign->completed_at->format('M d, Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-900">Message Template</h3>
            </div>
            <div class="px-6 py-5">
                <div class="bg-gray-50 rounded p-4 text-sm text-gray-700 whitespace-pre-wrap font-mono">{{ $campaign->message_template ?: 'No template saved yet.' }}</div>
            </div>
        </div>
    </div>

    <!-- Message Logs Table -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 sm:flex sm:items-center sm:justify-between">
                <h3 class="text-base font-semibold text-gray-900">Message Log</h3>
                
                <div class="mt-3 sm:ml-4 sm:mt-0 flex">
                    <form action="{{ route('campaigns.show', $campaign) }}" method="GET" class="flex items-center space-x-2">
                        <select name="status" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </form>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($messages as $msg)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $msg->phone_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-status-badge :status="$msg->status" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $msg->sent_at ? $msg->sent_at->format('M d, H:i:s') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">
                                    @if($msg->error_message)
                                        <span class="text-red-600" title="{{ $msg->error_message }}">{{ Str::limit($msg->error_message, 40) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No messages found in the log.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($messages) && $messages->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $messages->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@if($campaign->status === 'sending')
<script>
    setTimeout(function() {
        window.location.reload();
    }, 10000);
</script>
@endif
@endsection
