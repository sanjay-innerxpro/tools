@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('Free Online Media & File Tools'))
@section('meta_description', __('Free online media and file tools: scan media URLs, convert PDFs and images, optimize files, and use fast utility tools with no signup required.'))
@section('meta_image', asset('images/og-default.svg'))

@section('content')
    {{-- Hero Section --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950"></div>
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-400/10 dark:bg-indigo-400/5 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 lg:py-20">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium mb-6">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    {{ __('100% Free — No Signup Required') }}
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-[1.1]">
                    {{ __('Every tool you need,') }}
                    <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">{{ __('in one place') }}</span>
                </h1>

                <p class="mt-6 text-lg sm:text-xl text-gray-600 dark:text-gray-400 leading-relaxed max-w-2xl mx-auto">
                    {{ __('Download media from any URL, convert files, and more. All tools are free, fast, and work right in your browser.') }}
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/tools/media-scanner"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all text-base">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('Scan URL for Media') }}
                    </a>
                    <a href="#tools"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-semibold rounded-xl border border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600 shadow-sm hover:shadow transition-all text-base">
                        {{ __('Browse All Tools') }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Tools Grid --}}
    <section id="tools" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        {{-- Section Header --}}
        <div class="text-center mb-6">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('All Tools') }}</h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400 text-lg">{{ __('Choose a tool to get started') }}</p>
        </div>

        {{-- Category: Media --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Media Tools') }}</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Media Scanner (active) --}}
                <a href="/tools/media-scanner"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-lg hover:shadow-blue-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/20 group-hover:shadow-blue-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ __('Media Scanner') }}
                            </h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Paste any URL to detect and download videos, audio, and other media files. Supports YouTube, Vimeo, and 1800+ sites.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>

        {{-- Category: Converters --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Converters') }}</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- PDF to Text (active) --}}
                <a href="/tools/pdf-to-text"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-red-300 dark:hover:border-red-700 hover:shadow-lg hover:shadow-red-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-400 to-rose-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-red-500/20 group-hover:shadow-red-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">{{ __('PDF to Text') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Extract all text content from PDF documents quickly and accurately.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-red-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                {{-- Image Converter (active) --}}
                <a href="/tools/image-converter"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-lg hover:shadow-amber-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-orange-500/20 group-hover:shadow-orange-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ __('Image Converter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Convert between PNG, JPG, WebP, BMP, GIF, and more image formats.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-amber-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                {{-- Merge PDF (active) --}}
                <a href="/tools/merge-pdf"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-pink-300 dark:hover:border-pink-700 hover:shadow-lg hover:shadow-pink-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-rose-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-pink-500/20 group-hover:shadow-pink-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">{{ __('Merge PDF') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Combine multiple PDF files into a single document effortlessly.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-pink-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                {{-- Compress Image (active) --}}
                <a href="/tools/compress-image"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-green-300 dark:hover:border-green-700 hover:shadow-lg hover:shadow-green-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-lime-400 to-green-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-green-500/20 group-hover:shadow-green-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">{{ __('Compress Image') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Reduce image file size while maintaining quality. Supports batch compression.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-green-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                {{-- Split PDF (active) --}}
                <a href="/tools/split-pdf"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-lg hover:shadow-indigo-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-500/20 group-hover:shadow-indigo-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('Split PDF') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Split PDF into individual pages or extract a specific page range.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-indigo-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                {{-- Image Resizer (active) --}}
                <a href="/tools/image-resizer"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-cyan-300 dark:hover:border-cyan-700 hover:shadow-lg hover:shadow-cyan-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/20 group-hover:shadow-cyan-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">{{ __('Image Resizer') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Resize images by exact dimensions or percentage with high-quality output.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-cyan-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                {{-- QR Code Generator (active) --}}
                <a href="/tools/qr-code-generator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-gray-400 dark:hover:border-gray-600 hover:shadow-lg hover:shadow-gray-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-gray-500/20 group-hover:shadow-gray-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">{{ __('QR Code Generator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Generate QR codes instantly with custom size, colors, and error correction.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-gray-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                {{-- Word Counter (active) --}}
                <a href="/tools/word-counter"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-lg hover:shadow-emerald-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/20 group-hover:shadow-emerald-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ __('Word Counter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('Count words, characters, sentences, and estimate reading time instantly.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </a>

                {{-- Video Converter --}}
                <a href="/tools/video-converter" class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 hover:border-red-300 dark:hover:border-red-700 hover:shadow-lg hover:shadow-red-500/5 p-6 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-red-500/20">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Video Converter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Convert videos between MP4, WebM, AVI, MOV, and other formats.') }}</p>
                        </div>
                    </div>
                    <span class="absolute top-4 right-4 text-gray-300 dark:text-gray-600 group-hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>

                {{-- Audio Converter --}}
                <a href="/tools/audio-converter" class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 hover:border-violet-300 dark:hover:border-violet-700 hover:shadow-lg hover:shadow-violet-500/5 p-6 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-violet-500/20">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Audio Converter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Convert audio files between MP3, WAV, AAC, FLAC, and more.') }}</p>
                        </div>
                    </div>
                    <span class="absolute top-4 right-4 text-gray-300 dark:text-gray-600 group-hover:text-violet-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>
            </div>
        </div>

        {{-- Category: Utilities --}}
        <div class="mb-0">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Utilities') }}</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Password Generator --}}
                <a href="/tools/password-generator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-green-300 dark:hover:border-green-700 hover:shadow-lg hover:shadow-green-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-green-500/20 group-hover:shadow-green-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">{{ __('Password Generator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Create strong, random passwords instantly') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-green-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                {{-- OTP Generator --}}
                <a href="/tools/otp-generator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-sky-300 dark:hover:border-sky-700 hover:shadow-lg hover:shadow-sky-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20 group-hover:shadow-sky-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 11h6m-6 4h10m2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ __('OTP Generator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Generate one-time passwords instantly and copy them to your clipboard.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-sky-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                {{-- JSON Formatter --}}
                <a href="/tools/json-formatter"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-lg hover:shadow-amber-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/20 group-hover:shadow-amber-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ __('JSON Formatter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Format & validate JSON instantly') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-amber-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                {{-- Base64 Encoder --}}
                <a href="/tools/base64-encoder"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-violet-300 dark:hover:border-violet-700 hover:shadow-lg hover:shadow-violet-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-violet-500/20 group-hover:shadow-violet-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">{{ __('Base64 Encoder / Decoder') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Encode text to Base64 or decode Base64 to plain text') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-violet-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                {{-- Unit Converter --}}
                <a href="/tools/unit-converter"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-sky-300 dark:hover:border-sky-700 hover:shadow-lg hover:shadow-sky-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20 group-hover:shadow-sky-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ __('Unit Converter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Convert between units of length, weight, temperature, and more') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-sky-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                {{-- Color Converter --}}
                <a href="/tools/color-converter"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-pink-300 dark:hover:border-pink-700 hover:shadow-lg hover:shadow-pink-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-pink-500/20 group-hover:shadow-pink-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">{{ __('Color Converter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Convert colors between HEX, RGB, and HSL formats') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-pink-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Category: Developer & Text Tools --}}
    <section class="bg-gray-50 dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Developer & Text Tools') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ __('Powerful tools for coders and content creators') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Lorem Ipsum --}}
                <a href="/tools/lorem-ipsum"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-teal-300 dark:hover:border-teal-700 hover:shadow-lg hover:shadow-teal-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-teal-400 to-cyan-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-teal-500/20 group-hover:shadow-teal-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">{{ __('Lorem Ipsum Generator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Generate realistic placeholder text for designs and prototypes.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-teal-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- URL Encoder --}}
                <a href="/tools/url-encoder"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-orange-300 dark:hover:border-orange-700 hover:shadow-lg hover:shadow-orange-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-orange-500/20 group-hover:shadow-orange-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">{{ __('URL Encoder / Decoder') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Encode and decode URL-safe percent-encoded strings.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-orange-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Hash Generator --}}
                <a href="/tools/hash-generator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-gray-400 dark:hover:border-gray-600 hover:shadow-lg hover:shadow-gray-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-slate-600 to-gray-800 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-slate-900/20 group-hover:shadow-slate-900/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">{{ __('Hash Generator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Compute SHA-1, SHA-256, SHA-384 and SHA-512 checksums.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Text Case Converter --}}
                <a href="/tools/text-case-converter"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-lime-300 dark:hover:border-lime-700 hover:shadow-lg hover:shadow-lime-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-lime-400 to-green-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-lime-500/20 group-hover:shadow-lime-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-lime-600 dark:group-hover:text-lime-400 transition-colors">{{ __('Text Case Converter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Convert text between UPPERCASE, lowercase, camelCase, and more.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-lime-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Markdown Preview --}}
                <a href="/tools/markdown-preview"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-sky-300 dark:hover:border-sky-700 hover:shadow-lg hover:shadow-sky-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20 group-hover:shadow-sky-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ __('Markdown Preview') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Write Markdown and preview the rendered HTML output.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-sky-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Category: Web & Code Tools --}}
    <section class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Web & Code Tools') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ __('Essential tools for web developers and programmers') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Timestamp Converter --}}
                <a href="/tools/timestamp-converter"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-sky-300 dark:hover:border-sky-700 hover:shadow-lg hover:shadow-sky-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-sky-400 to-cyan-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20 group-hover:shadow-sky-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ __('Timestamp Converter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Convert Unix timestamps to readable dates and vice versa.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-sky-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- UUID Generator --}}
                <a href="/tools/uuid-generator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-violet-300 dark:hover:border-violet-700 hover:shadow-lg hover:shadow-violet-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-violet-500/20 group-hover:shadow-violet-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">{{ __('UUID Generator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Generate cryptographically random UUID v4 identifiers.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-violet-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Diff Checker --}}
                <a href="/tools/diff-checker"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-lg hover:shadow-amber-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/20 group-hover:shadow-amber-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ __('Diff Checker') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Compare two text blocks and highlight line-by-line changes.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-amber-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Regex Tester --}}
                <a href="/tools/regex-tester"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-rose-300 dark:hover:border-rose-700 hover:shadow-lg hover:shadow-rose-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-red-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-rose-500/20 group-hover:shadow-rose-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">{{ __('Regex Tester') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Test regular expressions and see live highlighted matches.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-rose-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Number Base Converter --}}
                <a href="/tools/number-base-converter"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-lg hover:shadow-indigo-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-500/20 group-hover:shadow-indigo-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('Number Base Converter') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Convert numbers between Binary, Octal, Decimal and Hex.') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-indigo-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Category: Daily Tools --}}
    <section class="bg-gray-50 dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg shadow-rose-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Daily Tools') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ __('Handy calculators and everyday utilities') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Age Calculator --}}
                <a href="/tools/age-calculator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-rose-300 dark:hover:border-rose-700 hover:shadow-lg hover:shadow-rose-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-rose-500/20 group-hover:shadow-rose-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">{{ __('Age Calculator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Calculate your exact age from your date of birth') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-rose-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Percentage Calculator --}}
                <a href="/tools/percentage-calculator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-lg hover:shadow-amber-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/20 group-hover:shadow-amber-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ __('Percentage Calculator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Quick percentage calculations for everyday use') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-amber-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- BMI Calculator --}}
                <a href="/tools/bmi-calculator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-teal-300 dark:hover:border-teal-700 hover:shadow-lg hover:shadow-teal-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-teal-500/20 group-hover:shadow-teal-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">{{ __('BMI Calculator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Calculate your Body Mass Index instantly') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-teal-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Stopwatch --}}
                <a href="/tools/stopwatch"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-lg hover:shadow-blue-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/20 group-hover:shadow-blue-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ __('Stopwatch') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Precise stopwatch with lap time tracking') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                {{-- Random Number Generator --}}
                <a href="/tools/random-number-generator"
                   class="group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 hover:border-violet-300 dark:hover:border-violet-700 hover:shadow-lg hover:shadow-violet-500/5 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-purple-500/20 group-hover:shadow-purple-500/30 transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">{{ __('Random Number Generator') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Generate random numbers in any range') }}</p>
                        </div>
                    </div>
                    <div class="absolute top-4 right-4">
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-700 group-hover:text-violet-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <div class="text-center mb-8">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Why use :app?', ['app' => config('app.name')]) }}</h2>
                <p class="mt-3 text-gray-500 dark:text-gray-400 text-lg">{{ __('Built for simplicity, speed, and privacy') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="text-center px-4">
                    <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Lightning Fast') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('All processing happens instantly. No waiting in queues or uploading to slow servers.') }}</p>
                </div>

                <div class="text-center px-4">
                    <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Privacy First') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('No accounts, no tracking, no data stored. Your files and URLs stay private.') }}</p>
                </div>

                <div class="text-center px-4">
                    <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('100% Free') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('Every tool is completely free with no hidden costs, no premium tiers, no limits.') }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection
