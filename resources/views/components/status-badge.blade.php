@props(['status'])

@php
    $statusLower = strtolower($status);
    
    $classes = match($statusLower) {
        'draft' => 'bg-gray-100 text-gray-800 border-gray-200',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'processing', 'sending' => 'bg-blue-50 text-blue-700 border-blue-200 animate-pulse',
        'completed', 'sent', 'delivered' => 'bg-green-50 text-green-700 border-green-200',
        'failed' => 'bg-red-50 text-red-700 border-red-200',
        'read' => 'bg-blue-100 text-blue-800 border-blue-300',
        'queued' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        default => 'bg-gray-100 text-gray-800 border-gray-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border $classes"]) }}>
    @if(in_array($statusLower, ['processing', 'sending']))
        <svg class="animate-spin -ml-0.5 mr-1.5 h-3 w-3 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($statusLower === 'completed' || $statusLower === 'sent' || $statusLower === 'delivered')
        <svg class="-ml-0.5 mr-1.5 h-3 w-3 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
    @elseif($statusLower === 'failed')
        <svg class="-ml-0.5 mr-1.5 h-3 w-3 text-red-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    @endif
    {{ ucfirst($status) }}
</span>
