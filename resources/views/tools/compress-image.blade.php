@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('Compress Image'))
@section('meta_description', __('Reduce image file size without major quality loss using this quick online image compressor.'))

@section('content')
<div x-data="compressImage()" x-cloak class="max-w-4xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-lime-400 to-green-500 rounded-2xl shadow-lg shadow-green-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Compress Image') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Reduce image file size while keeping quality') }}</p>
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
                <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('JPG, PNG, WebP, BMP, TIFF — Max 50 MB') }}</p>
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

        {{-- Quality Slider --}}
        <div class="mt-6">
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Quality') }}</label>
                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400" x-text="quality + '%'"></span>
            </div>
            <input type="range" x-model="quality" min="1" max="100" step="1"
                   class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-green-500">
            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span>{{ __('Smallest file') }}</span>
                <span>{{ __('Best quality') }}</span>
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="process()"
                    :disabled="!file || processing"
                    class="px-8 py-3 bg-gradient-to-r from-lime-500 to-green-500 hover:from-lime-600 hover:to-green-600 disabled:from-gray-400 disabled:to-gray-400 text-white font-semibold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <template x-if="!processing">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        {{ __('Compress Image') }}
                    </span>
                </template>
                <template x-if="processing">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                        {{ __('Compressing...') }}
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
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Compression Complete') }}</h2>
                <a :href="result.downloadUrl" :download="result.downloadName"
                   class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    {{ __('Download') }}
                </a>
            </div>

            {{-- Size comparison --}}
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ __('Original') }}</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="formatBytes(result.originalSize)"></p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ __('Compressed') }}</p>
                    <p class="text-lg font-bold text-green-600 dark:text-green-400" x-text="formatBytes(result.compressedSize)"></p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ __('Saved') }}</p>
                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400" x-text="result.savedPercent + '%'"></p>
                </div>
            </div>

            <div class="mt-4 text-center">
                <button @click="reset()" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('Compress another image') }}</button>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function compressImage() {
    return {
        file: null, preview: null, processing: false, error: null, result: null, dragover: false,
        quality: 75,

        handleDrop(e) {
            const f = e.dataTransfer.files[0];
            if (f && f.type.startsWith('image/')) { this.file = f; this.showPreview(f); }
            else this.error = '{{ __('Please drop an image.') }}';
        },
        handleFileSelect(e) { const f = e.target.files[0]; if (f) { this.file = f; this.showPreview(f); this.error = null; } },
        showPreview(f) { const r = new FileReader(); r.onload = (e) => this.preview = e.target.result; r.readAsDataURL(f); },
        clearFile() { this.file = null; this.preview = null; },

        async process() {
            if (!this.file) return;
            this.processing = true; this.error = null;
            const fd = new FormData();
            fd.append('file', this.file);
            fd.append('quality', this.quality);
            try {
                const res = await fetch('/api/tools/compress-image', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await res.json();
                if (!res.ok) { this.error = data.error || data.message || '{{ __('Compression failed.') }}'; return; }
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
