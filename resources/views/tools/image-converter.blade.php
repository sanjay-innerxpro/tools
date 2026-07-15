@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('Image Converter'))
@section('meta_description', __('Convert images between JPG, PNG, WebP, GIF, and more formats with a fast free image converter.'))

@section('content')
<div x-data="imageConverter()" x-cloak class="max-w-4xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl shadow-lg shadow-orange-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Image Converter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Convert images between PNG, JPG, WebP, BMP, GIF, and more') }}</p>
    </div>

    {{-- Upload + Options --}}
    <div x-show="!result" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8 mb-6">
        <div @dragover.prevent="dragover = true"
             @dragleave.prevent="dragover = false"
             @drop.prevent="dragover = false; handleDrop($event)"
             :class="dragover ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-700'"
             class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer"
             @click="$refs.fileInput.click()">

            <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept="image/*" class="hidden">

            <div x-show="!file && !preview" class="space-y-3">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-medium">{{ __('Drop your image here or click to browse') }}</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('JPG, PNG, WebP, BMP, GIF, TIFF — Max 50 MB') }}</p>
            </div>

            <div x-show="file" class="flex items-center justify-center gap-4">
                <img x-show="preview" :src="preview" class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="text-left">
                    <p class="font-medium text-gray-900 dark:text-white" x-text="file?.name"></p>
                    <p class="text-sm text-gray-500" x-text="formatBytes(file?.size)"></p>
                </div>
                <button type="button" @click.stop="clearFile()" class="ml-2 p-1 text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        {{-- Format Selection --}}
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('Convert to:') }}</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="fmt in formats" :key="fmt">
                    <button @click="targetFormat = fmt"
                            :class="targetFormat === fmt ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-400'"
                            class="px-4 py-2 text-sm font-medium border rounded-lg transition-all uppercase"
                            x-text="fmt"></button>
                </template>
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="process()"
                    :disabled="!file || processing"
                    class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 disabled:from-gray-400 disabled:to-gray-400 text-white font-semibold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <template x-if="!processing">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        {{ __('Convert Image') }}
                    </span>
                </template>
                <template x-if="processing">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                        {{ __('Converting...') }}
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
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Conversion Complete') }}</h2>
                <a :href="result.downloadUrl" :download="result.downloadName"
                   class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    {{ __('Download') }}
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Original') }}</p>
                    <p class="font-medium text-gray-900 dark:text-white" x-text="formatBytes(result.originalSize)"></p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Converted') }}</p>
                    <p class="font-medium text-gray-900 dark:text-white" x-text="formatBytes(result.convertedSize)"></p>
                </div>
            </div>
            <div class="mt-4 text-center">
                <button @click="reset()" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('Convert another image') }}</button>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function imageConverter() {
    return {
        file: null, preview: null, processing: false, error: null, result: null, dragover: false,
        targetFormat: 'png',
        formats: ['png', 'jpg', 'webp', 'bmp', 'gif', 'ico', 'tiff', 'pdf'],

        handleDrop(e) {
            const f = e.dataTransfer.files[0];
            if (f && f.type.startsWith('image/')) { this.file = f; this.showPreview(f); }
            else this.error = '{{ __('Please drop an image file.') }}';
        },
        handleFileSelect(e) { const f = e.target.files[0]; if (f) { this.file = f; this.showPreview(f); this.error = null; } },
        showPreview(f) { const r = new FileReader(); r.onload = (e) => this.preview = e.target.result; r.readAsDataURL(f); },
        clearFile() { this.file = null; this.preview = null; },

        async process() {
            if (!this.file) return;
            this.processing = true; this.error = null;
            const fd = new FormData();
            fd.append('file', this.file);
            fd.append('format', this.targetFormat);
            try {
                const res = await fetch('/api/tools/image-convert', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await res.json();
                if (!res.ok) { this.error = data.error || data.message || '{{ __('Conversion failed.') }}'; return; }
                this.result = data;
            } catch (e) { this.error = '{{ __('Network error. Please try again.') }}'; }
            finally { this.processing = false; }
        },

        reset() { this.file = null; this.preview = null; this.result = null; this.error = null; },
        formatBytes(b) { if (!b) return '0 B'; const u = ['B','KB','MB','GB']; let s = b; for (const unit of u) { if (s < 1024) return Math.round(s*10)/10+' '+unit; s /= 1024; } return Math.round(s*10)/10+' TB'; },
    };
}
</script>
@endpush
