@extends('layouts.app')

@section('title', 'Upload Student Data')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Upload Student Data</h1>
            <p class="mt-2 text-sm text-gray-700">Upload your student list in Excel or CSV format.</p>
        </div>
        <a href="{{ route('imports.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Back to Imports</a>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <form action="{{ route('imports.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
            @csrf
            
            <div id="dropzone" class="mt-2 flex justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-16 transition-colors duration-200 hover:border-indigo-500 hover:bg-indigo-50 cursor-pointer relative group">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-indigo-500 transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                    </svg>
                    <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                        <label for="file_upload" class="relative cursor-pointer rounded-md font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                            <span>Browse files</span>
                            <input id="file_upload" name="file" type="file" class="sr-only" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </label>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs leading-5 text-gray-500 mt-2">XLSX, XLS, or CSV up to 10MB</p>
                    <p id="file-name" class="mt-4 text-sm font-medium text-indigo-700 hidden px-3 py-1 bg-indigo-100 rounded-full inline-block"></p>
                </div>
            </div>
            
            @error('file')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-8 flex justify-end">
                <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    Upload & Detect Columns
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file_upload');
        const fileNameDisplay = document.getElementById('file-name');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight dropzone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropzone.classList.add('border-indigo-500', 'bg-indigo-50');
        }

        function unhighlight(e) {
            dropzone.classList.remove('border-indigo-500', 'bg-indigo-50');
        }

        // Handle dropped files
        dropzone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            let dt = e.dataTransfer;
            let files = dt.files;
            
            if(files.length > 0) {
                fileInput.files = files;
                updateFileName();
            }
        }
        
        // Handle clicked files
        fileInput.addEventListener('change', updateFileName);
        
        // Handle click on dropzone to trigger input
        dropzone.addEventListener('click', (e) => {
            if(e.target !== fileInput && e.target.tagName !== 'LABEL' && e.target.tagName !== 'SPAN') {
                fileInput.click();
            }
        });

        function updateFileName() {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = 'Selected: ' + fileInput.files[0].name;
                fileNameDisplay.classList.remove('hidden');
            }
        }
    });
</script>
@endsection
