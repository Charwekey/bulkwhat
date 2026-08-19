@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'indigo',
    'subtitle' => null,
])

@php
    $colorClasses = [
        'indigo' => 'text-indigo-600 bg-indigo-50 border-indigo-200',
        'green' => 'text-green-600 bg-green-50 border-green-200',
        'blue' => 'text-blue-600 bg-blue-50 border-blue-200',
        'amber' => 'text-amber-600 bg-amber-50 border-amber-200',
        'red' => 'text-red-600 bg-red-50 border-red-200',
        'violet' => 'text-violet-600 bg-violet-50 border-violet-200',
    ];
    
    $textColorClasses = [
        'indigo' => 'text-indigo-600',
        'green' => 'text-green-600',
        'blue' => 'text-blue-600',
        'amber' => 'text-amber-600',
        'red' => 'text-red-600',
        'violet' => 'text-violet-600',
    ];

    $selectedColor = $colorClasses[$color] ?? $colorClasses['indigo'];
    $selectedTextColor = $textColorClasses[$color] ?? $textColorClasses['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300 relative']) }}>
    <!-- Decorative top border -->
    <div class="absolute top-0 left-0 right-0 h-1 {{ explode(' ', $selectedColor)[1] ?? 'bg-indigo-50' }}"></div>
    
    <div class="p-6">
        <div class="flex items-center">
            @if($icon)
                <div class="flex-shrink-0 p-3 rounded-lg {{ $selectedColor }} mr-4 border">
                    {{ $icon }}
                </div>
            @endif
            
            <div class="flex-1 w-0">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ $title }}
                    </dt>
                    <dd class="flex items-baseline mt-1">
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $value }}
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
        
        @if($subtitle)
            <div class="mt-4 text-sm text-gray-500">
                {{ $subtitle }}
            </div>
        @endif
    </div>
</div>
