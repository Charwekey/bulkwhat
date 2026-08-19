@extends('layouts.app')

@section('title', 'Import Preview - ' . $import->original_filename)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Map Columns</h1>
            <p class="mt-2 text-sm text-gray-700">Select the column containing the WhatsApp numbers.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="md:col-span-1">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-base font-semibold text-gray-900">Import Summary</h3>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">File Name</p>
                        <p class="mt-1 text-sm text-gray-900 truncate">{{ $import->original_filename }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Rows Detected</p>
                        <p class="mt-1 text-sm text-gray-900">{{ number_format($import->total_records) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <div class="mt-1">
                            <x-status-badge :status="$import->status" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <form action="{{ route('imports.process', $import) }}" method="POST" class="px-6 py-5">
                    @csrf
                    
                    <div class="space-y-6">
                        <div>
                            <label for="phone_column" class="block text-sm font-medium text-gray-900">Select the column containing WhatsApp numbers</label>
                            <select id="phone_column" name="phone_column" class="mt-2 block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="">-- Select a column --</option>
                                @foreach($import->columns as $column)
                                    <option value="{{ $column }}" {{ old('phone_column') == $column || stripos($column, 'phone') !== false || stripos($column, 'number') !== false ? 'selected' : '' }}>
                                        {{ $column }}
                                    </option>
                                @endforeach
                            </select>
                            @error('phone_column')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="country_code" class="block text-sm font-medium text-gray-900">Default Country Code (for numbers without one)</label>
                            <div class="mt-2 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 px-3 text-gray-500 sm:text-sm bg-gray-50">+</span>
                                <input type="text" name="country_code" id="country_code" value="{{ old('country_code', '233') }}" class="block w-full min-w-0 flex-1 rounded-none rounded-r-md border-0 py-2 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            @error('country_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('imports.index') }}" class="inline-flex justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                            Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Data Preview -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">Data Preview (First 10 Rows)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($import->columns as $column)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ $column }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($previewRows as $row)
                        <tr class="hover:bg-gray-50">
                            @foreach($import->columns as $column)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $row[$column] ?? '' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($import->columns) }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                No preview data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneSelect = document.getElementById('phone_column');
        
        function highlightColumn() {
            const selectedCol = phoneSelect.value;
            // Get all headers
            const headers = document.querySelectorAll('th');
            let colIndex = -1;
            
            headers.forEach((th, index) => {
                th.classList.remove('bg-indigo-50', 'text-indigo-700');
                if (th.textContent.trim() === selectedCol) {
                    colIndex = index;
                    th.classList.add('bg-indigo-50', 'text-indigo-700');
                }
            });

            // Highlight corresponding cells
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((td, index) => {
                    td.classList.remove('bg-indigo-50', 'font-medium', 'text-indigo-900');
                    if (index === colIndex) {
                        td.classList.add('bg-indigo-50', 'font-medium', 'text-indigo-900');
                    }
                });
            });
        }

        phoneSelect.addEventListener('change', highlightColumn);
        if(phoneSelect.value) {
            highlightColumn();
        }
    });
</script>
@endsection
