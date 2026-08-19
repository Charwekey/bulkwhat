@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">SMess API Configuration</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Configure your SMess WhatsApp API credentials. These settings are loaded from your <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600 font-mono text-xs">.env</code> file.
                </p>
            </div>
        </div>
        
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="shadow sm:rounded-md sm:overflow-hidden bg-white border border-gray-200">
                <div class="px-4 py-5 space-y-6 sm:p-6" x-data="{ 
                        showToken: false, 
                        isTesting: false,
                        testResult: null,
                        testMessage: '',
                        
                        async testConnection() {
                            this.isTesting = true;
                            this.testResult = null;
                            
                            try {
                                const response = await fetch('{{ route('settings.test-connection') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                        'Accept': 'application/json'
                                    }
                                });
                                
                                const data = await response.json();
                                
                                if (response.ok && data.success) {
                                    this.testResult = 'success';
                                    this.testMessage = data.message || 'SMess Connection successful!';
                                } else {
                                    this.testResult = 'error';
                                    this.testMessage = data.message || 'Connection failed. Please check your SMESS_API_KEYS.';
                                }
                            } catch (error) {
                                this.testResult = 'error';
                                this.testMessage = 'An error occurred while testing the connection.';
                            } finally {
                                this.isTesting = false;
                            }
                        }
                    }">
                    
                    <!-- Alert for Test Connection -->
                    <div x-show="testResult !== null" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         :class="{'bg-green-50 border-green-200 text-green-800': testResult === 'success', 'bg-red-50 border-red-200 text-red-800': testResult === 'error'}"
                         class="rounded-md p-4 border mb-4 flex items-start" style="display: none;">
                        <div class="flex-shrink-0">
                            <svg x-show="testResult === 'success'" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                            <svg x-show="testResult === 'error'" class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium" x-text="testMessage"></p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button" @click="testResult = null" :class="{'text-green-500 hover:bg-green-100': testResult === 'success', 'text-red-500 hover:bg-red-100': testResult === 'error'}" class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                    <span class="sr-only">Dismiss</span>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-4">
                            <label for="api_key" class="block text-sm font-medium text-gray-700">SMess API Key</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21 7.5Z" />
                                    </svg>
                                </span>
                                <input :type="showToken ? 'text' : 'password'" name="api_key" id="api_key" value="{{ $settings['api_key'] ?? config('smess.api_key') }}" readonly class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-10 sm:text-sm border-gray-300 rounded-md bg-gray-50 text-gray-700 font-mono">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" @click="showToken = !showToken" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                        <svg x-show="!showToken" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <svg x-show="showToken" style="display:none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <label for="base_url" class="block text-sm font-medium text-gray-700">API Endpoint URL</label>
                            <input type="text" name="base_url" id="base_url" value="{{ $settings['base_url'] }}" readonly class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed">
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <label for="default_country_code" class="block text-sm font-medium text-gray-700">Default Country Code</label>
                            <input type="text" name="default_country_code" id="default_country_code" value="{{ $settings['default_country_code'] }}" readonly class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500">Default: 233 (Ghana)</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 border-t border-gray-100 pt-4 flex space-x-3">
                        <button type="button" @click="testConnection()" :disabled="isTesting" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="isTesting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isTesting ? 'Testing...' : 'Test Connection'"></span>
                        </button>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Edit SMESS_API_KEYS in your .env file to update settings</span>
                    <button type="button" disabled class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 opacity-50 cursor-not-allowed">
                        Saved
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
