<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    @php
        $appName = config('app.name', 'ToolBox');
        $defaultTitle = $appName . ' — ' . __('Free Online Media & File Tools');
        $defaultDescription = __('Use :app free online tools to scan media URLs, convert files, and handle daily tasks instantly. No signup required.', ['app' => $appName]);
        $seoTitle = trim($__env->yieldContent('title', $defaultTitle));
        $seoDescription = trim($__env->yieldContent('meta_description', $defaultDescription));
        $seoRobots = trim($__env->yieldContent('meta_robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'));
        $defaultSeoImage = \App\Support\SeoMeta::defaultImageForPath(request()->path());

        $seoImage = trim($__env->yieldContent('meta_image', $defaultSeoImage));
        $baseCurrentUrl = url()->current();
        $currentQuery = request()->query();
        $canonicalQuery = $currentQuery;
        $canonicalUrl = $baseCurrentUrl;
        if (!empty($canonicalQuery)) {
            ksort($canonicalQuery);
            $canonicalUrl .= '?' . http_build_query($canonicalQuery);
        }

        $supportedLocales = ['en','hi','es','fr','zh','ar','pt','de','ja','ru'];
        $currentLocale = app()->getLocale();

        $siteLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $appName,
            'url' => url('/'),
            'inLanguage' => $currentLocale,
        ];

        $webPageLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $seoTitle,
            'description' => $seoDescription,
            'url' => $canonicalUrl,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $appName,
                'url' => url('/'),
            ],
            'inLanguage' => $currentLocale,
        ];
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $seoRobots }}">

    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', $currentLocale) }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $seoImage }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    @foreach($supportedLocales as $locale)
        @php
            $altQuery = array_merge($currentQuery, ['lang' => $locale]);
            ksort($altQuery);
            $altUrl = $baseCurrentUrl . '?' . http_build_query($altQuery);
        @endphp
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ $altUrl }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <script type="application/ld+json">{!! json_encode($siteLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($webPageLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        [x-cloak] { display: none !important; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-950 min-h-screen transition-colors duration-300 font-[Inter,ui-sans-serif,system-ui,sans-serif]">

    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25 group-hover:shadow-blue-500/40 transition-shadow">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ config('app.name', 'ToolBox') }}
                    </span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="/"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->is('/') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        {{ __('All Tools') }}
                    </a>
                    <a href="/tools/media-scanner"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->is('tools/media-scanner') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        {{ __('Media Scanner') }}
                    </a>

                    {{-- PDF Tools Dropdown --}}
                    <div class="relative" x-data="{open:false}" @mouseenter="open=true" @mouseleave="open=false">
                        <button class="px-3 py-2 text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1 {{ request()->is('tools/pdf-to-text') || request()->is('tools/merge-pdf') || request()->is('tools/split-pdf') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                            {{ __('PDF Tools') }}
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute top-full left-0 mt-1 w-48 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-xl py-1 z-50">
                            <a href="/tools/pdf-to-text" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('PDF to Text') }}</a>
                            <a href="/tools/merge-pdf" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Merge PDF') }}</a>
                            <a href="/tools/split-pdf" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Split PDF') }}</a>
                        </div>
                    </div>

                    {{-- Image & Media Dropdown --}}
                    <div class="relative" x-data="{open:false}" @mouseenter="open=true" @mouseleave="open=false">
                        <button class="px-3 py-2 text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1 {{ request()->is('tools/image-converter') || request()->is('tools/compress-image') || request()->is('tools/image-resizer') || request()->is('tools/video-converter') || request()->is('tools/audio-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                            {{ __('Image & Media') }}
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute top-full left-0 mt-1 w-52 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-xl py-1 z-50">
                            <a href="/tools/image-converter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Image Converter') }}</a>
                            <a href="/tools/compress-image" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Compress Image') }}</a>
                            <a href="/tools/image-resizer" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Image Resizer') }}</a>
                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                            <a href="/tools/video-converter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Video Converter') }}</a>
                            <a href="/tools/audio-converter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Audio Converter') }}</a>
                        </div>
                    </div>

                    {{-- Utilities Dropdown --}}
                    <div class="relative" x-data="{open:false}" @mouseenter="open=true" @mouseleave="open=false">
                        <button class="px-3 py-2 text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1 {{ request()->is('tools/password-generator') || request()->is('tools/otp-generator') || request()->is('tools/json-formatter') || request()->is('tools/base64-encoder') || request()->is('tools/unit-converter') || request()->is('tools/color-converter') || request()->is('tools/qr-code-generator') || request()->is('tools/word-counter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                            {{ __('Utilities') }}
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute top-full left-0 mt-1 w-52 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-xl py-1 z-50">
                            <a href="/tools/password-generator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Password Generator') }}</a>
                            <a href="/tools/otp-generator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('OTP Generator') }}</a>
                            <a href="/tools/json-formatter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('JSON Formatter') }}</a>
                            <a href="/tools/base64-encoder" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Base64 Encoder / Decoder') }}</a>
                            <a href="/tools/unit-converter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Unit Converter') }}</a>
                            <a href="/tools/color-converter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Color Converter') }}</a>
                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                            <a href="/tools/qr-code-generator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('QR Code Generator') }}</a>
                            <a href="/tools/word-counter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Word Counter') }}</a>
                        </div>
                    </div>

                    {{-- Dev Tools Dropdown --}}
                    <div class="relative" x-data="{open:false}" @mouseenter="open=true" @mouseleave="open=false">
                        <button class="px-3 py-2 text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1 {{ request()->is('tools/lorem-ipsum') || request()->is('tools/url-encoder') || request()->is('tools/hash-generator') || request()->is('tools/text-case-converter') || request()->is('tools/markdown-preview') || request()->is('tools/timestamp-converter') || request()->is('tools/uuid-generator') || request()->is('tools/diff-checker') || request()->is('tools/regex-tester') || request()->is('tools/number-base-converter') || request()->is('tools/age-calculator') || request()->is('tools/percentage-calculator') || request()->is('tools/bmi-calculator') || request()->is('tools/stopwatch') || request()->is('tools/random-number-generator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                            {{ __('More Tools') }}
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute top-full right-0 mt-1 w-60 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-xl py-1 z-50 max-h-96 overflow-y-auto">
                            <p class="px-4 pt-2 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Developer & Text') }}</p>
                            <a href="/tools/lorem-ipsum" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Lorem Ipsum Generator') }}</a>
                            <a href="/tools/url-encoder" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('URL Encoder / Decoder') }}</a>
                            <a href="/tools/hash-generator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Hash Generator') }}</a>
                            <a href="/tools/text-case-converter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Text Case Converter') }}</a>
                            <a href="/tools/markdown-preview" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Markdown Preview') }}</a>
                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                            <p class="px-4 pt-2 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Web & Code') }}</p>
                            <a href="/tools/timestamp-converter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Timestamp Converter') }}</a>
                            <a href="/tools/uuid-generator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('UUID Generator') }}</a>
                            <a href="/tools/diff-checker" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Diff Checker') }}</a>
                            <a href="/tools/regex-tester" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Regex Tester') }}</a>
                            <a href="/tools/number-base-converter" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Number Base Converter') }}</a>
                            <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>
                            <p class="px-4 pt-2 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Daily Tools') }}</p>
                            <a href="/tools/age-calculator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Age Calculator') }}</a>
                            <a href="/tools/percentage-calculator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Percentage Calculator') }}</a>
                            <a href="/tools/bmi-calculator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('BMI Calculator') }}</a>
                            <a href="/tools/stopwatch" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Stopwatch') }}</a>
                            <a href="/tools/random-number-generator" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Random Number Generator') }}</a>
                        </div>
                    </div>
                </div>

                {{-- Right side --}}
                <div class="flex items-center gap-2">
                    {{-- Language Switcher --}}
                    @php
                        $locales = [
                            'en' => 'English',
                            'hi' => 'हिन्दी',
                            'es' => 'Español',
                            'fr' => 'Français',
                            'zh' => '中文',
                            'ar' => 'العربية',
                            'pt' => 'Português',
                            'de' => 'Deutsch',
                            'ja' => '日本語',
                            'ru' => 'Русский',
                        ];
                        $currentLocale = app()->getLocale();
                    @endphp
                    <div class="relative" x-data="{open:false}" @click.outside="open=false">
                        <button @click="open=!open"
                                class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                title="{{ __('Language') }}">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="hidden sm:inline">{{ $locales[$currentLocale] ?? 'English' }}</span>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute right-0 top-full mt-1 w-44 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-xl py-1 z-50 max-h-80 overflow-y-auto" x-cloak>
                            @foreach($locales as $code => $name)
                                <a href="/set-locale/{{ $code }}"
                                   class="block px-4 py-2 text-sm transition-colors {{ $currentLocale === $code ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                    {{ $name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            title="{{ __('Toggle dark mode') }}">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    {{-- Mobile menu button --}}
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg x-show="!mobileMenu" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenu" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="md:hidden border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900" x-cloak>
            <div class="px-4 py-3 space-y-1">
                <a href="/" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('/') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('All Tools') }}</a>
                <a href="/tools/media-scanner" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/media-scanner') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Media Scanner') }}</a>
                <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('PDF') }}</p>
                <a href="/tools/pdf-to-text" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/pdf-to-text') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('PDF to Text') }}</a>
                <a href="/tools/merge-pdf" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/merge-pdf') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Merge PDF') }}</a>
                <a href="/tools/split-pdf" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/split-pdf') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Split PDF') }}</a>
                <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Image') }}</p>
                <a href="/tools/image-converter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/image-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Image Converter') }}</a>
                <a href="/tools/compress-image" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/compress-image') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Compress Image') }}</a>
                <a href="/tools/image-resizer" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/image-resizer') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Image Resizer') }}</a>
                <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Utilities') }}</p>
                <a href="/tools/qr-code-generator" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/qr-code-generator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('QR Code Generator') }}</a>
                <a href="/tools/word-counter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/word-counter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Word Counter') }}</a>
                <a href="/tools/password-generator" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/password-generator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Password Generator') }}</a>
                <a href="/tools/json-formatter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/json-formatter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('JSON Formatter') }}</a>
                <a href="/tools/base64-encoder" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/base64-encoder') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Base64 Encoder / Decoder') }}</a>
                <a href="/tools/unit-converter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/unit-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Unit Converter') }}</a>
                <a href="/tools/color-converter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/color-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Color Converter') }}</a>
                <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Media') }}</p>
                <a href="/tools/video-converter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/video-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Video Converter') }}</a>
                <a href="/tools/audio-converter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/audio-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Audio Converter') }}</a>
                <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Developer & Text') }}</p>
                <a href="/tools/lorem-ipsum" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/lorem-ipsum') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Lorem Ipsum Generator') }}</a>
                <a href="/tools/url-encoder" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/url-encoder') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('URL Encoder / Decoder') }}</a>
                <a href="/tools/hash-generator" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/hash-generator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Hash Generator') }}</a>
                <a href="/tools/text-case-converter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/text-case-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Text Case Converter') }}</a>
                <a href="/tools/markdown-preview" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/markdown-preview') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Markdown Preview') }}</a>
                <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Web & Code') }}</p>
                <a href="/tools/timestamp-converter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/timestamp-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Timestamp Converter') }}</a>
                <a href="/tools/uuid-generator" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/uuid-generator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('UUID Generator') }}</a>
                <a href="/tools/diff-checker" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/diff-checker') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Diff Checker') }}</a>
                <a href="/tools/regex-tester" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/regex-tester') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Regex Tester') }}</a>
                <a href="/tools/number-base-converter" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/number-base-converter') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Number Base Converter') }}</a>
                <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Daily Tools') }}</p>
                <a href="/tools/age-calculator" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/age-calculator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Age Calculator') }}</a>
                <a href="/tools/percentage-calculator" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/percentage-calculator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Percentage Calculator') }}</a>
                <a href="/tools/bmi-calculator" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/bmi-calculator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('BMI Calculator') }}</a>
                <a href="/tools/stopwatch" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/stopwatch') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Stopwatch') }}</a>
                <a href="/tools/random-number-generator" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->is('tools/random-number-generator') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ __('Random Number Generator') }}</a>
                <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Language') }}</p>
                <div class="px-3 flex flex-wrap gap-2">
                    @foreach(['en' => 'EN', 'hi' => 'हि', 'es' => 'ES', 'fr' => 'FR', 'zh' => '中', 'ar' => 'عر', 'pt' => 'PT', 'de' => 'DE', 'ja' => '日', 'ru' => 'RU'] as $code => $label)
                        <a href="/set-locale/{{ $code }}"
                           class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors {{ app()->getLocale() === $code ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- Brand --}}
                <div class="md:col-span-1">
                    <a href="/" class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ config('app.name', 'ToolBox') }}</span>
                    </a>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        {{ __('Free online tools for media downloading, file conversion, and more. No signup required.') }}
                    </p>
                </div>

                {{-- Media Tools --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">{{ __('Media Tools') }}</h3>
                    <ul class="space-y-2.5">
                        <li><a href="/tools/media-scanner" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('Media Scanner') }}</a></li>
                    </ul>
                </div>

                {{-- Converter Tools --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">{{ __('Converters & Tools') }}</h3>
                    <ul class="space-y-2.5">
                        <li><a href="/tools/pdf-to-text" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('PDF to Text') }}</a></li>
                        <li><a href="/tools/merge-pdf" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('Merge PDF') }}</a></li>
                        <li><a href="/tools/split-pdf" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('Split PDF') }}</a></li>
                        <li><a href="/tools/image-converter" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('Image Converter') }}</a></li>
                        <li><a href="/tools/compress-image" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('Compress Image') }}</a></li>
                        <li><a href="/tools/image-resizer" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('Image Resizer') }}</a></li>
                        <li><a href="/tools/qr-code-generator" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('QR Code Generator') }}</a></li>
                        <li><a href="/tools/word-counter" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('Word Counter') }}</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">{{ __('Legal') }}</h3>
                    <ul class="space-y-2.5">
                        <li><span class="text-sm text-gray-400 dark:text-gray-500">{{ __('Privacy Policy') }}</span></li>
                        <li><span class="text-sm text-gray-400 dark:text-gray-500">{{ __('Terms of Service') }}</span></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-800 mt-8 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-400 dark:text-gray-500">&copy; {{ date('Y') }} {{ config('app.name', 'ToolBox') }}. {{ __('All rights reserved.') }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('Only download content you have the right to access. Respect copyright laws.') }}</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
