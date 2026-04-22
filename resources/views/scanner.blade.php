@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('URL Media Scanner'))
@section('meta_description', __('Scan any public URL to detect downloadable videos, audio, and media assets. Fast browser-based media discovery with no signup required.'))
@section('meta_image', asset('images/og-default.svg'))

@section('content')
<div x-data="mediaScanner()" x-cloak class="max-w-4xl mx-auto px-4 py-10">

    {{-- Page Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl shadow-lg shadow-blue-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
            {{ __('Media Scanner') }}
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Paste any public URL to detect downloadable media assets') }}</p>
    </div>

    {{-- URL Input --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 mb-6">
        <form @submit.prevent="startScan()">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">&#128279;</span>
                    <input type="url"
                           x-model="url"
                           placeholder="https://example.com/page-with-media"
                           required
                           :disabled="scanning"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none disabled:opacity-50 transition-colors">
                </div>
                <button type="submit"
                        :disabled="scanning || !url"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <span x-show="!scanning">&#128270; {{ __('Scan') }}</span>
                    <span x-show="scanning" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ __('Scanning...') }}
                    </span>
                </button>
            </div>

            {{-- Options toggle --}}
            <div class="mt-3">
                <button type="button" @click="showOptions = !showOptions" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    &#9881; {{ __('Advanced options') }}
                </button>
                <div x-show="showOptions" x-transition class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" x-model="options.includeImages" class="rounded border-gray-300 dark:border-gray-600">
                        {{ __('Include images') }}
                    </label>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('Min file size (KB)') }}</label>
                        <input type="number" x-model.number="options.minFileSize" min="0" class="mt-1 w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('Max results') }}</label>
                        <input type="number" x-model.number="options.maxResults" min="1" max="500" class="mt-1 w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Progress Bar --}}
    <template x-if="scanning">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 mb-6 fade-in">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="progressMessage"></span>
                <span class="text-sm text-gray-500" x-text="progress + '%'"></span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" :style="'width: ' + progress + '%'"></div>
            </div>
        </div>
    </template>

    {{-- Error Message --}}
    <template x-if="error">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6 fade-in">
            <div class="flex items-start gap-3">
                <span class="text-red-500 text-xl mt-0.5">&#9888;&#65039;</span>
                <div>
                    <p class="text-red-800 dark:text-red-300 font-medium" x-text="error"></p>
                </div>
            </div>
        </div>
    </template>

    {{-- Results --}}
    <template x-if="results">
        <div class="fade-in">
            {{-- Warnings --}}
            <template x-if="results.warnings && results.warnings.length > 0">
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <span class="text-amber-500 text-xl mt-0.5">&#9888;&#65039;</span>
                        <div>
                            <template x-for="warning in results.warnings" :key="warning">
                                <p class="text-amber-800 dark:text-amber-300 text-sm" x-text="warning"></p>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Stats Bar --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-4 mb-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <span x-text="results.stats.totalAssets > 0 ? '&#9989;' : '&#128269;'"></span>
                            <span x-text="results.stats.totalAssets"></span> <span x-text="results.stats.totalAssets !== 1 ? '{{ __('assets found') }}' : '{{ __('asset found') }}'"></span>
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="results.pageTitle || results.url"></p>
                    </div>
                    <div x-show="results.stats.totalSize > 0" class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Total:') }} <span x-text="formatBytes(results.stats.totalSize)"></span>
                    </div>
                </div>
            </div>

            {{-- No assets found message --}}
            <template x-if="results.stats.totalAssets === 0">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8 text-center">
                    <div class="text-5xl mb-4">&#128566;</div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('No downloadable media detected') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        {{ __('The page may load media dynamically via JavaScript, require authentication, or not contain any publicly accessible media files.') }}
                    </p>
                    <div class="mt-4 space-y-2 text-sm text-gray-400 dark:text-gray-500">
                        <p>{{ __('Try a page that contains direct <video>, <audio>, or download links.') }}</p>
                    </div>
                </div>
            </template>

            {{-- Filter Tabs (only show when there are assets) --}}
            <div x-show="results.stats.totalAssets > 0" class="flex flex-wrap gap-2 mb-4">
                <button @click="filter = 'all'"
                        :class="filter === 'all' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    {{ __('All') }} (<span x-text="results.stats.totalAssets"></span>)
                </button>
                <template x-for="(count, type) in results.stats.byType" :key="type">
                    <button @click="filter = type"
                            :class="filter === type ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm capitalize">
                        <span x-text="getTypeIcon(type)"></span> <span x-text="type"></span> (<span x-text="count"></span>)
                    </button>
                </template>
            </div>

            {{-- Sort --}}
            <div x-show="results.stats.totalAssets > 0" class="flex justify-end mb-3">
                <select x-model="sortBy" class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <option value="name">{{ __('Sort by name') }}</option>
                    <option value="size">{{ __('Sort by size') }}</option>
                    <option value="type">{{ __('Sort by type') }}</option>
                </select>
            </div>

            {{-- Asset Cards --}}
            <div class="space-y-3">
                <template x-for="asset in filteredAssets()" :key="asset.id">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-4 hover:shadow-md hover:border-gray-300 dark:hover:border-gray-700 transition-all fade-in">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <span class="text-2xl flex-shrink-0 mt-0.5" x-text="getTypeIcon(asset.type)"></span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-medium text-gray-900 dark:text-white truncate" x-text="asset.filename || '{{ __('Unknown file') }}'"></h3>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize"
                                              :class="getTypeBadgeClass(asset.type)"
                                              x-text="asset.type"></span>
                                        <span x-show="asset.extension" class="text-xs text-gray-500 dark:text-gray-400 uppercase" x-text="asset.extension"></span>
                                        <span x-show="asset.sizeFormatted" class="text-xs text-gray-500 dark:text-gray-400" x-text="asset.sizeFormatted"></span>
                                        <span x-show="!asset.sizeFormatted && (asset.extension === 'm3u8' || asset.extension === 'mpd')"
                                              class="text-xs text-purple-600 dark:text-purple-400 font-medium"
                                              x-text="asset.extension === 'm3u8' ? '{{ __('HLS Stream') }}' : '{{ __('DASH Stream') }}'"></span>
                                        <span x-show="asset.quality" class="text-xs text-blue-600 dark:text-blue-400 font-medium" x-text="asset.quality"></span>
                                    </div>

                                    {{-- Quality Variants --}}
                                    <div x-show="asset.qualityVariants && asset.qualityVariants.length > 0" class="mt-2 flex flex-wrap gap-1">
                                        <template x-for="variant in (asset.qualityVariants || [])" :key="variant.label">
                                            <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-xs rounded cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-colors"
                                                  x-text="variant.label || variant.resolution"></span>
                                        </template>
                                    </div>

                                    {{-- DRM Warning --}}
                                    <div x-show="asset.drm" class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                                        &#128274; {{ __('This content is DRM-protected and cannot be downloaded.') }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex-shrink-0 flex flex-col gap-2 items-end min-w-[180px]">
                                {{-- HLS/DASH stream download --}}
                                <template x-if="asset.hlsDownloadUrl && !asset.drm">
                                    <div class="flex flex-col items-end gap-1.5 w-full">
                                        {{-- Download button --}}
                                        <button type="button"
                                                @click="downloadHls(asset)"
                                                :class="{
                                                    'bg-blue-600 hover:bg-blue-700 cursor-pointer': !hlsDownloading[asset.id] && !hlsReady[asset.id],
                                                    'bg-blue-400 cursor-wait pointer-events-none': hlsDownloading[asset.id] && !hlsReady[asset.id],
                                                    'bg-green-600 hover:bg-green-700 cursor-pointer': hlsReady[asset.id]
                                                }"
                                                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-white text-sm font-medium rounded-lg transition-all whitespace-nowrap">

                                            {{-- Idle state --}}
                                            <span x-show="!hlsDownloading[asset.id] && !hlsReady[asset.id]" class="flex items-center gap-1.5">&#11015;&#65039; {{ __('Download as MP4') }}</span>

                                            {{-- Processing state --}}
                                            <span x-show="hlsDownloading[asset.id] && !hlsReady[asset.id]" class="flex items-center gap-1.5">
                                                <svg class="animate-spin h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path>
                                                </svg>
                                                <span x-text="hlsProgress[asset.id] || '{{ __('Starting...') }}'"></span>
                                            </span>

                                            {{-- Ready state --}}
                                            <span x-show="hlsReady[asset.id]" class="flex items-center gap-1.5">&#9989; {{ __('Save MP4') }}
                                                <span x-show="hlsFileSize[asset.id]"
                                                      class="text-[11px] opacity-80"
                                                      x-text="hlsFileSize[asset.id] ? '(' + formatBytes(hlsFileSize[asset.id]) + ')' : ''"></span>
                                            </span>
                                        </button>

                                        {{-- Progress bar --}}
                                        <div x-show="hlsDownloading[asset.id]" x-transition class="w-full">
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                <div class="bg-blue-500 h-2 rounded-full transition-all duration-500 ease-out"
                                                     :style="'width: ' + (hlsProgressPct[asset.id] || 2) + '%'"></div>
                                            </div>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 text-right"
                                               x-text="hlsProgressPct[asset.id] ? hlsProgressPct[asset.id] + '%' : ''"></p>
                                        </div>

                                        {{-- Error message --}}
                                        <p x-show="hlsError[asset.id]" x-text="hlsError[asset.id]"
                                           class="text-xs text-red-500 dark:text-red-400 text-right leading-tight"></p>
                                    </div>
                                </template>
                                {{-- Regular downloadable asset --}}
                                <template x-if="asset.downloadable && !asset.drm && !asset.hlsDownloadUrl">
                                    <a :href="asset.downloadUrl"
                                       class="inline-flex items-center gap-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        &#11015;&#65039; {{ __('Download') }}
                                    </a>
                                </template>
                                <template x-if="asset.drm">
                                    <span class="inline-flex items-center gap-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed">
                                        {{ __('Unavailable') }}
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- No results for filter --}}
            <div x-show="filteredAssets().length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                {{ __('No assets match the selected filter.') }}
            </div>
        </div>
    </template>

    {{-- Disclaimer --}}
    <div class="mt-12 text-center text-xs text-gray-400 dark:text-gray-500">
        <p>{{ __('Only download content you have the right to access. Respect copyright laws and terms of service.') }}</p>
        <p class="mt-1">{{ __('DRM-protected content is flagged and cannot be downloaded. This tool does not bypass any access restrictions.') }}</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
function mediaScanner() {
    return {
        url: '',
        scanning: false,
        progress: 0,
        progressMessage: '{{ __('Initializing...') }}',
        error: null,
        results: null,
        scanId: null,
        filter: 'all',
        sortBy: 'name',
        showOptions: false,
        hlsDownloading: {},
        hlsError: {},
        hlsProgress: {},
        hlsProgressPct: {},
        hlsReady: {},
        hlsFileSize: {},
        options: {
            includeImages: false,
            minFileSize: 5,
            maxResults: 100,
        },
        pollInterval: null,

        async startScan() {
            this.scanning = true;
            this.error = null;
            this.results = null;
            this.progress = 5;
            this.progressMessage = '{{ __('Submitting scan request...') }}';

            try {
                const res = await fetch('/api/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        url: this.url,
                        options: {
                            includeImages: this.options.includeImages,
                            minFileSize: this.options.minFileSize * 1024,
                            maxResults: this.options.maxResults,
                        },
                    }),
                });

                const data = await res.json();

                if (!res.ok) {
                    this.error = data.error?.message || '{{ __('An unknown error occurred.') }}';
                    this.scanning = false;
                    return;
                }

                // If cached result, load directly
                if (data.cached && data.status === 'completed') {
                    this.scanId = data.scanId;
                    await this.loadResults();
                    this.scanning = false;
                    return;
                }

                this.scanId = data.scanId;
                this.pollProgress();

            } catch (e) {
                this.error = '{{ __('Network error. Please check your connection and try again.') }}';
                this.scanning = false;
            }
        },

        pollProgress() {
            this.pollInterval = setInterval(async () => {
                try {
                    const res = await fetch(`/api/scan/${this.scanId}/poll`);
                    const data = await res.json();

                    if (data.progress) {
                        this.progress = data.progress.progress || 0;
                        this.progressMessage = data.progress.message || '{{ __('Processing...') }}';
                    }

                    if (data.status === 'completed') {
                        clearInterval(this.pollInterval);
                        await this.loadResults();
                        this.scanning = false;
                    } else if (data.status === 'failed') {
                        clearInterval(this.pollInterval);
                        this.error = data.progress?.message || '{{ __('Scan failed.') }}';
                        this.scanning = false;
                    }
                } catch (e) {
                    // Silently retry
                }
            }, 1500);
        },

        async loadResults() {
            try {
                const res = await fetch(`/api/scan/${this.scanId}/results`);
                const data = await res.json();

                if (data.assets) {
                    this.results = data;
                    this.filter = 'all';
                } else if (data.status === 'failed') {
                    this.error = data.error?.message || '{{ __('Scan failed.') }}';
                } else {
                    this.error = '{{ __('No results available yet.') }}';
                }
            } catch (e) {
                this.error = '{{ __('Failed to load results.') }}';
            }
        },

        filteredAssets() {
            if (!this.results?.assets) return [];

            let assets = this.filter === 'all'
                ? [...this.results.assets]
                : this.results.assets.filter(a => a.type === this.filter);

            // Sort
            assets.sort((a, b) => {
                if (this.sortBy === 'name') return (a.filename || '').localeCompare(b.filename || '');
                if (this.sortBy === 'size') return (b.size || 0) - (a.size || 0);
                if (this.sortBy === 'type') return (a.type || '').localeCompare(b.type || '');
                return 0;
            });

            return assets;
        },

        getTypeIcon(type) {
            const icons = { video: '🎬', audio: '🎵', document: '📄', image: '🖼️', other: '📎' };
            return icons[type] || '📎';
        },

        getTypeBadgeClass(type) {
            const classes = {
                video: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                audio: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                document: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                image: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                other: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            };
            return classes[type] || classes.other;
        },

        formatBytes(bytes) {
            if (!bytes || bytes === 0) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            let size = bytes;
            for (const unit of units) {
                if (size < 1024) return Math.round(size * 10) / 10 + ' ' + unit;
                size /= 1024;
            }
            return Math.round(size * 10) / 10 + ' TB';
        },

        async downloadHls(asset) {
            // If file is ready, trigger browser save
            if (this.hlsReady[asset.id]) {
                const taskId = this.hlsReady[asset.id];
                const fileUrl = `/api/download-hls/file/${taskId}`;
                const a = document.createElement('a');
                a.href = fileUrl;
                a.download = (asset.filename ? asset.filename.replace(/\.(m3u8|mpd)$/i, '') : 'video') + '.mp4';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                // Reset state after delay so user sees the green button briefly
                setTimeout(() => {
                    this.hlsReady = { ...this.hlsReady, [asset.id]: null };
                    this.hlsProgress = { ...this.hlsProgress, [asset.id]: null };
                    this.hlsProgressPct = { ...this.hlsProgressPct, [asset.id]: 0 };
                    this.hlsFileSize = { ...this.hlsFileSize, [asset.id]: null };
                }, 5000);
                return;
            }

            if (this.hlsDownloading[asset.id]) return;
            this.hlsDownloading = { ...this.hlsDownloading, [asset.id]: true };
            this.hlsError = { ...this.hlsError, [asset.id]: null };
            this.hlsProgress = { ...this.hlsProgress, [asset.id]: '{{ __('Starting download...') }}' };
            this.hlsProgressPct = { ...this.hlsProgressPct, [asset.id]: 2 };

            try {
                // Step 1: Start background download
                const startRes = await fetch(asset.hlsDownloadUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                });

                if (!startRes.ok) {
                    let msg = '{{ __('Failed to start download.') }}';
                    try { const d = await startRes.json(); msg = d.error?.message || d.message || msg; } catch (_) {}
                    this.hlsError = { ...this.hlsError, [asset.id]: msg };
                    return;
                }

                const { taskId } = await startRes.json();

                // Step 2: Poll progress until done
                const startTime = Date.now();
                const pollResult = await new Promise((resolve) => {
                    const poll = setInterval(async () => {
                        try {
                            const statusRes = await fetch(`/api/download-hls/status/${taskId}`);
                            const data = await statusRes.json();

                            if (data.status === 'downloading' && data.total > 0) {
                                const pct = Math.round((data.done / data.total) * 90);
                                const elapsed = (Date.now() - startTime) / 1000;
                                const speed = data.done > 0 && elapsed > 2
                                    ? ` · ${Math.round(data.done / elapsed)}/s`
                                    : '';
                                this.hlsProgress = { ...this.hlsProgress, [asset.id]: `{{ __('Downloading') }} ${data.done}/${data.total} {{ __('segments') }}${speed}` };
                                this.hlsProgressPct = { ...this.hlsProgressPct, [asset.id]: Math.max(pct, 2) };
                            } else if (data.status === 'converting') {
                                this.hlsProgress = { ...this.hlsProgress, [asset.id]: '{{ __('Converting to MP4...') }}' };
                                this.hlsProgressPct = { ...this.hlsProgressPct, [asset.id]: 92 };
                            } else if (data.status === 'done') {
                                const sizeStr = data.fileSize ? ' (' + this.formatBytes(data.fileSize) + ')' : '';
                                this.hlsProgress = { ...this.hlsProgress, [asset.id]: '{{ __('Ready to save!') }}' + sizeStr };
                                this.hlsProgressPct = { ...this.hlsProgressPct, [asset.id]: 100 };
                                if (data.fileSize) this.hlsFileSize = { ...this.hlsFileSize, [asset.id]: data.fileSize };
                                clearInterval(poll);
                                resolve({ success: true, taskId });
                            } else if (data.status === 'error') {
                                clearInterval(poll);
                                resolve({ success: false, message: data.message || '{{ __('Download failed.') }}' });
                            } else if (data.message) {
                                this.hlsProgress = { ...this.hlsProgress, [asset.id]: data.message };
                            }
                        } catch (e) {
                            // Network glitch — keep polling
                        }
                    }, 2000);

                    // Safety timeout: 15 minutes
                    setTimeout(() => {
                        clearInterval(poll);
                        resolve({ success: false, message: '{{ __('Download timed out after 15 min.') }}' });
                    }, 900000);
                });

                if (!pollResult.success) {
                    this.hlsError = { ...this.hlsError, [asset.id]: pollResult.message };
                    return;
                }

                // Step 3: Mark file as ready — button turns green
                this.hlsReady = { ...this.hlsReady, [asset.id]: pollResult.taskId };

                // Auto-trigger browser save
                this.$nextTick(() => this.downloadHls(asset));

            } catch (e) {
                this.hlsError = { ...this.hlsError, [asset.id]: '{{ __('Download failed. Try again.') }}' };
            } finally {
                this.hlsDownloading = { ...this.hlsDownloading, [asset.id]: false };
            }
        },
    };
}
</script>
@endpush
