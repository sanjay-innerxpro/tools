@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('Merge PDF'))

@section('content')
<div x-data="mergePdf()" x-cloak class="max-w-4xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 rounded-2xl shadow-lg shadow-pink-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Merge PDF') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Combine multiple PDF files into a single document') }}</p>
    </div>

    {{-- Upload Area --}}
    <div x-show="!result" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8 mb-6">
        <div @dragover.prevent="dragover = true"
             @dragleave.prevent="dragover = false"
             @drop.prevent="dragover = false; handleDrop($event)"
             :class="dragover ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-700'"
             class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer"
             @click="$refs.fileInput.click()">

            <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept=".pdf" multiple class="hidden">

            <div class="space-y-3">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-medium">{{ __('Drop PDF files here or click to browse') }}</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('Select 2-20 PDF files — Max 50 MB each') }}</p>
            </div>
        </div>

        {{-- File List --}}
        <div x-show="files.length > 0" class="mt-4 space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="files.length + ' ' + (files.length !== 1 ? '{{ __('files selected') }}' : '{{ __('file selected') }}')"></h3>
                <button @click="files = []" class="text-sm text-red-500 hover:text-red-600">{{ __('Clear all') }}</button>
            </div>
            <template x-for="(f, i) in files" :key="i">
                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 rounded-lg px-4 py-2.5">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-6 text-center" x-text="i + 1"></span>
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-sm text-gray-900 dark:text-white truncate" x-text="f.name"></span>
                        <span class="text-xs text-gray-400" x-text="formatBytes(f.size)"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="moveFile(i, -1)" :disabled="i === 0" class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                        </button>
                        <button @click="moveFile(i, 1)" :disabled="i === files.length - 1" class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <button @click="files.splice(i, 1)" class="p-1 text-gray-400 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="process()"
                    :disabled="files.length < 2 || processing"
                    class="px-8 py-3 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 disabled:from-gray-400 disabled:to-gray-400 text-white font-semibold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <template x-if="!processing">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" /></svg>
                        {{ __('Merge PDFs') }}
                    </span>
                </template>
                <template x-if="processing">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                        {{ __('Merging...') }}
                    </span>
                </template>
            </button>
        </div>
    </div>

    {{-- Error --}}
    <template x-if="error">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
            <p class="text-red-700 dark:text-red-300" x-text="error"></p>
        </div>
    </template>

    {{-- Result --}}
    <template x-if="result">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 fade-in">
            <div class="text-center space-y-4">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('PDFs Merged Successfully') }}</h2>
                <p class="text-gray-500 dark:text-gray-400" x-text="result.fileCount + ' {{ __('files merged') }} · ' + formatBytes(result.fileSize)"></p>
                <a :href="result.downloadUrl" :download="result.downloadName"
                   class="inline-flex items-center gap-2 px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow-lg transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    {{ __('Download Merged PDF') }}
                </a>
                <div>
                    <button @click="reset()" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('Merge more files') }}</button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function mergePdf() {
    return {
        files: [], processing: false, error: null, result: null, dragover: false,

        handleDrop(e) {
            const newFiles = [...e.dataTransfer.files].filter(f => f.type === 'application/pdf');
            if (newFiles.length === 0) { this.error = '{{ __('Please drop PDF files.') }}'; return; }
            this.files = [...this.files, ...newFiles].slice(0, 20);
            this.error = null;
        },
        handleFileSelect(e) {
            const newFiles = [...e.target.files].filter(f => f.type === 'application/pdf');
            this.files = [...this.files, ...newFiles].slice(0, 20);
            this.error = null;
        },
        moveFile(i, dir) {
            const j = i + dir;
            if (j < 0 || j >= this.files.length) return;
            const temp = this.files[i];
            this.files[i] = this.files[j];
            this.files[j] = temp;
            this.files = [...this.files]; // trigger reactivity
        },

        async process() {
            if (this.files.length < 2) return;
            this.processing = true; this.error = null;
            const fd = new FormData();
            this.files.forEach(f => fd.append('files[]', f));
            try {
                const res = await fetch('/api/tools/merge-pdf', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await res.json();
                if (!res.ok) { this.error = data.error || data.message || '{{ __('Merge failed.') }}'; return; }
                this.result = data;
            } catch (e) { this.error = '{{ __('Network error. Please try again.') }}'; }
            finally { this.processing = false; }
        },

        reset() { this.files = []; this.result = null; this.error = null; },
        formatBytes(b) { if (!b) return '0 B'; const u = ['B','KB','MB','GB']; let s = b; for (const unit of u) { if (s < 1024) return Math.round(s*10)/10+' '+unit; s /= 1024; } return Math.round(s*10)/10+' TB'; },
    };
}
</script>
@endpush
