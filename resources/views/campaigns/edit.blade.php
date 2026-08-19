@extends('layouts.app')

@section('title', 'Compose Message - ' . $campaign->name)

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Compose Campaign Message</h1>
        <p class="mt-1 text-sm text-gray-700">
            Campaign: <span class="font-semibold text-indigo-600">{{ $campaign->name }}</span> &middot; 
            Target: <span class="font-semibold">{{ $campaign->target_name }}</span> ({{ number_format($campaign->total_recipients) }} recipients)
        </p>
    </div>
    
    <div class="mt-4 sm:mt-0 flex gap-2">
        @if($campaign->message_template)
            <a href="{{ route('campaigns.preview', $campaign) }}" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                Preview & Send &rarr;
            </a>
        @endif
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6" x-data="{ composerMode: 'custom' }">
    <!-- Left Column: Composer (60%) -->
    <div class="w-full lg:w-3/5 flex flex-col gap-6">
        <!-- Mode Switcher -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Choose Message Drafting Method:</span>
            <div class="flex items-center gap-2">
                <button type="button" @click="composerMode = 'custom'" :class="composerMode === 'custom' ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-600' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-300'" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
                    <span>✍️ Type Custom Message</span>
                </button>
                <button type="button" @click="composerMode = 'template'" :class="composerMode === 'template' ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-600' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-300'" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
                    <span>📋 Select From Saved Templates</span>
                </button>
            </div>
        </div>

        <!-- Template Picker Drawer (Visible when Mode === 'template') -->
        <div x-show="composerMode === 'template'" style="display: none;" class="bg-indigo-50/80 border border-indigo-200 rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-indigo-900">Select a Template to Load & Edit</h3>
                <span class="text-xs text-indigo-600 font-medium">Click any template below to populate the message editor</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-1">
                @foreach($templateCategories as $cat)
                    @foreach($cat->templates as $tpl)
                        <div onclick="loadAndEditTemplate({{ json_encode($tpl->body) }})" class="bg-white p-3.5 rounded-lg border border-indigo-100 hover:border-indigo-500 hover:shadow-md transition-all cursor-pointer group">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-gray-900 group-hover:text-indigo-600">{{ $tpl->title }}</span>
                                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-semibold">{{ $cat->name }}</span>
                            </div>
                            <p class="text-xs text-gray-600 font-mono line-clamp-3 bg-gray-50 p-2 rounded border border-gray-100">{{ $tpl->body }}</p>
                        </div>
                    @endforeach
                @endforeach
                @if(!empty($uncategorizedTemplates))
                    @foreach($uncategorizedTemplates as $tpl)
                        <div onclick="loadAndEditTemplate({{ json_encode($tpl->body) }})" class="bg-white p-3.5 rounded-lg border border-indigo-100 hover:border-indigo-500 hover:shadow-md transition-all cursor-pointer group">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-gray-900 group-hover:text-indigo-600">{{ $tpl->title }}</span>
                                <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-semibold">General</span>
                            </div>
                            <p class="text-xs text-gray-600 font-mono line-clamp-3 bg-gray-50 p-2 rounded border border-gray-100">{{ $tpl->body }}</p>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Main Form & Textarea -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden flex-1">
            <form action="{{ route('campaigns.update', $campaign) }}" method="POST" class="flex flex-col h-full">
                @csrf
                @method('PUT')
                
                <div class="p-5 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Available Merge Fields (Placeholders)</h3>
                    <p class="text-xs text-gray-500 mb-3">Click any button below to insert dynamic student data fields into your message.</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($columns as $column)
                            <button type="button" onclick="insertField('{{ $column }}')" class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 hover:bg-indigo-100 transition-colors cursor-pointer group">
                                <svg class="mr-1 h-3 w-3 text-indigo-500 group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                {{ $column }}
                            </button>
                        @endforeach
                    </div>
                </div>
                
                <div class="p-5 flex-1 flex flex-col">
                    <label for="message_template" class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wider">Message Content (Editable)</label>
                    <textarea name="message_template" id="message_template" rows="12" class="block w-full flex-1 rounded-md border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 resize-y font-sans" placeholder="Type your message here or click 'Select From Saved Templates' above." required>{{ old('message_template', $campaign->message_template) }}</textarea>
                    
                    @error('message_template')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-xs text-gray-500">
                            <span id="char_count" class="font-bold text-gray-700">0</span> characters
                        </p>
                        <button type="submit" class="inline-flex justify-center rounded-lg bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition-colors">
                            Save Template & Preview Message &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Live Preview (40%) -->
    <div class="w-full lg:w-2/5">
        <div class="bg-gray-100 shadow-inner rounded-xl border border-gray-200 h-full min-h-[500px] flex flex-col sticky top-6 overflow-hidden relative" style="background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23d1d5db\' fill-opacity=\'0.2\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E');">
            <!-- WhatsApp Header -->
            <div class="bg-emerald-600 text-white px-4 py-3 flex items-center shadow-md z-10">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-gray-300 overflow-hidden flex-shrink-0">
                        <svg class="h-full w-full text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-sm leading-tight">{{ $sampleData['Name'] ?? $sampleData['Student Name'] ?? 'Sample Student' }}</div>
                        <div class="text-xs text-emerald-100 mt-0.5">online</div>
                    </div>
                </div>
            </div>
            
            <!-- WhatsApp Chat Area -->
            <div class="p-4 flex-1 flex flex-col items-end overflow-y-auto">
                <div class="mb-4 text-center w-full">
                    <span class="bg-blue-100 text-blue-800 text-[10px] font-medium px-2.5 py-1 rounded-md inline-block uppercase tracking-wider shadow-sm">Today</span>
                </div>
                
                <div class="bg-[#dcf8c6] rounded-lg p-3 max-w-[85%] relative shadow-sm text-sm text-gray-800">
                    <div id="preview_text" class="whitespace-pre-wrap break-words leading-snug font-normal"><em>Start typing to preview...</em></div>
                    <div class="text-right mt-1.5 flex items-center justify-end space-x-1">
                        <span class="text-[10px] text-gray-500">10:42 AM</span>
                        <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none">
                            <path d="M11.603 16.635L7.04259 12.0747L8.45681 10.6605L11.603 13.8066L19.3813 6.02844L20.7956 7.44265L11.603 16.635Z" fill="currentColor"/>
                            <path d="M3 12.0747L4.41421 10.6605L7.20235 13.4486L5.78813 14.8628L3 12.0747Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="absolute right-0 top-0 w-3 h-3 bg-[#dcf8c6]" style="transform: translate(30%, 30%) rotate(45deg); z-index: -1;"></div>
                </div>
            </div>
            
            <div class="bg-gray-100 px-4 py-3 flex items-center space-x-2 z-10 mt-auto">
                <div class="bg-white rounded-full flex-1 h-10 px-4 flex items-center text-gray-400 text-sm">
                    Message Preview
                </div>
                <div class="h-10 w-10 bg-emerald-600 rounded-full flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="currentColor"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const sampleData = @json($sampleData ?? []);
    
    function insertField(fieldName) {
        const textarea = document.getElementById('message_template');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const before = text.substring(0, start);
        const after = text.substring(end, text.length);
        
        textarea.value = before + '{{' + fieldName + '}}' + after;
        
        textarea.selectionStart = textarea.selectionEnd = start + fieldName.length + 4;
        textarea.focus();
        
        updatePreview();
    }

    function loadAndEditTemplate(bodyText) {
        const textarea = document.getElementById('message_template');
        textarea.value = bodyText;
        textarea.focus();
        updatePreview();
    }

    function findSampleValue(placeholderName) {
        if (!placeholderName) return null;
        const lowerP = placeholderName.toLowerCase();
        const cleanP = lowerP.replace(/[^a-z0-9]/g, '');
        
        if (sampleData[placeholderName] !== undefined) return sampleData[placeholderName];
        
        for (const [k, v] of Object.entries(sampleData)) {
            if (k.toLowerCase() === lowerP) return v;
        }
        
        for (const [k, v] of Object.entries(sampleData)) {
            const cleanK = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (cleanK === cleanP) return v;
        }
        
        const nameAliases = ['name', 'studentname', 'fullname', 'firstname', 'lastname', 'fieldname', 'student', 'recipientname'];
        if (nameAliases.includes(cleanP) || lowerP.includes('name') || lowerP.includes('student')) {
            for (const [k, v] of Object.entries(sampleData)) {
                const cleanK = k.toLowerCase().replace(/[^a-z0-9]/g, '');
                if (['name', 'studentname', 'fullname', 'student', 'firstname', 'nameofstudent'].includes(cleanK)) return v;
            }
            for (const [k, v] of Object.entries(sampleData)) {
                const cleanK = k.toLowerCase().replace(/[^a-z0-9]/g, '');
                if (!['whatsappnumber', 'phone', 'mobile', 'email', 'indexnumber', 'id'].includes(cleanK)) return v;
            }
        }
        
        return null;
    }

    function updatePreview() {
        const textarea = document.getElementById('message_template');
        let text = textarea.value;
        
        document.getElementById('char_count').textContent = text.length;
        
        if (!text.trim()) {
            document.getElementById('preview_text').innerHTML = '<em>Start typing or select a template to preview...</em>';
            return;
        }

        const placeholderRegex = /(\{\{\s*([^}]+?)\s*\}\}|\[\s*([^\]]+?)\s*\]|\{\s*([^}]+?)\s*\}|<\s*([^>]+?)\s*>|%\s*([^%]+?)\s*%|\+\s*([^+]+?)\s*\+)/gi;
        
        text = text.replace(placeholderRegex, (match, p1, p2, p3, p4, p5, p6, p7) => {
            const fieldName = (p2 || p3 || p4 || p5 || p6 || p7 || '').trim();
            const value = findSampleValue(fieldName);
            return value !== null ? `<b>${value}</b>` : match;
        });

        text = text.replace(/\n/g, '<br>');
        document.getElementById('preview_text').innerHTML = text;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const textarea = document.getElementById('message_template');
        textarea.addEventListener('input', updatePreview);
        updatePreview();
    });
</script>
@endsection
