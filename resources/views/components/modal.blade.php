@props(['id', 'title', 'confirmText' => 'Confirm', 'confirmColor' => 'indigo'])

@php
    $buttonColorClass = match($confirmColor) {
        'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'indigo' => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
        'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        default => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
    };
@endphp

<div x-data="{ open: false }" 
     @keydown.escape.window="open = false" 
     @open-modal-{{ $id }}.window="open = true" 
     class="relative z-50" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true" 
     x-show="open" 
     style="display: none;">
     
    <div class="fixed inset-0 bg-gray-900/75 transition-opacity" 
         x-show="open" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         @click="open = false"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                 x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.stop>
                
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">{{ $title }}</h3>
                            <div class="mt-2 text-sm text-gray-500">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" 
                            class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto {{ $buttonColorClass }} transition-colors"
                            @click="$dispatch('confirm-modal-{{ $id }}'); open = false;">
                        {{ $confirmText }}
                    </button>
                    <button type="button" 
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors"
                            @click="open = false">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
