@extends('layouts.app')

@section('title', 'Preview & Send - ' . $campaign->name)

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Preview & Send Campaign</h1>
    <p class="mt-2 text-sm text-gray-700">Review your message and send a test before executing the bulk campaign.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Previews -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-base font-semibold text-gray-900">Sample Messages</h3>
                <a href="{{ route('campaigns.edit', $campaign) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit Template</a>
            </div>
            
            <div class="p-6 bg-gray-100 space-y-6 overflow-y-auto max-h-[600px]" style="background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23d1d5db\' fill-opacity=\'0.2\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E');">
                @foreach($previews as $index => $preview)
                    <div class="flex flex-col mb-4">
                        <div class="text-xs text-gray-500 text-center mb-2 font-medium bg-white/50 py-1 px-3 rounded-full self-center">
                            Sample #{{ $index + 1 }} &middot; {{ $preview['recipient']->phone_number }}
                        </div>
                        <div class="bg-[#dcf8c6] rounded-lg p-3 w-max max-w-full sm:max-w-[85%] self-end relative shadow-sm text-sm text-gray-800">
                            <div class="whitespace-pre-wrap break-words leading-snug">{!! nl2br(e($preview['message'])) !!}</div>
                            <!-- Tail -->
                            <div class="absolute right-0 top-0 w-3 h-3 bg-[#dcf8c6]" style="transform: translate(30%, 30%) rotate(45deg); z-index: -1;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Actions -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Summary Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-900">Campaign Summary</h3>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Campaign Name</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $campaign->name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Recipients</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-600">{{ number_format($campaign->import->valid_records) }}</p>
                </div>
            </div>
        </div>

        <!-- Test Send Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-900">Test Campaign</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('campaigns.test', $campaign) }}" method="POST">
                    @csrf
                    <div>
                        <label for="test_phone" class="block text-sm font-medium text-gray-900">Test Phone Number</label>
                        <div class="mt-2 flex rounded-md shadow-sm">
                            <input type="text" name="test_phone" id="test_phone" class="block w-full min-w-0 flex-1 rounded-none rounded-l-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="e.g. 233555000000" required>
                            <button type="submit" class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Send Test
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">We'll use Sample #1's data for this test message.</p>
                    </div>
                </form>
                
                @if(session('test_success'))
                    <div class="mt-4 rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('test_success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                @if(session('test_error'))
                    <div class="mt-4 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ session('test_error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Execute Campaign Card -->
        <div class="bg-white shadow-sm rounded-xl border border-red-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-red-100 bg-red-50">
                <h3 class="text-base font-semibold text-red-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Execute Campaign
                </h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700 mb-6">
                    You are about to send <strong>{{ number_format($campaign->import->valid_records) }}</strong> messages. This action cannot be undone and will begin immediately.
                </p>
                
                <form action="{{ route('campaigns.send', $campaign) }}" method="POST" id="execute_form">
                    @csrf
                    
                    <div class="relative flex items-start mb-6">
                        <div class="flex h-6 items-center">
                            <input id="confirm_send" name="confirm_send" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 cursor-pointer">
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="confirm_send" class="font-medium text-gray-900 cursor-pointer">I confirm I want to send this campaign</label>
                        </div>
                    </div>

                    <button type="submit" id="btn_send" class="w-full justify-center rounded-md bg-indigo-600 px-3 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
                        Start Campaign
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('confirm_send');
        const submitBtn = document.getElementById('btn_send');
        const form = document.getElementById('execute_form');

        checkbox.addEventListener('change', function() {
            submitBtn.disabled = !this.checked;
        });

        form.addEventListener('submit', function(e) {
            if(!checkbox.checked) {
                e.preventDefault();
                alert('Please confirm you want to send the campaign.');
                return;
            }
            
            // Show double confirmation
            if(!confirm('Are you absolutely sure you want to broadcast this message to {{ number_format($campaign->import->valid_records) }} recipients?')) {
                e.preventDefault();
                checkbox.checked = false;
                submitBtn.disabled = true;
            } else {
                // Change button state to prevent double submission
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Starting...
                `;
                submitBtn.disabled = true;
            }
        });
    });
</script>
@endsection
