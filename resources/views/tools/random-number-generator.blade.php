@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('Random Number Generator'))
@section('meta_description', __('Generate random numbers in any range'))

@section('content')
<div x-data="randNumGen()" x-cloak>

    {{-- Hero --}}
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-violet-600"></div>
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 text-center text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-sm font-medium mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('Daily Tools') }}
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('Random Number Generator') }}</h1>
            <p class="mt-3 text-purple-100 text-base sm:text-lg max-w-xl mx-auto">{{ __('Generate random numbers in any range') }}</p>
        </div>
    </div>

    {{-- Tool --}}
    <div class="max-w-xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">

            {{-- Options --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Minimum') }}</label>
                    <input type="number" x-model.number="min" placeholder="1"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Maximum') }}</label>
                    <input type="number" x-model.number="max" placeholder="100"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm">
                </div>
            </div>

            <div class="flex items-center gap-4 mb-5">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Count') }} <span class="text-gray-400 font-normal" x-text="'(' + count + ')'"></span>
                    </label>
                    <input type="range" x-model.number="count" min="1" max="100" step="1"
                           class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-purple-500">
                </div>
                <div class="flex items-center gap-2 mt-5">
                    <input type="checkbox" x-model="unique" id="unique"
                           class="w-4 h-4 text-purple-600 rounded border-gray-300 dark:border-gray-700 focus:ring-purple-500">
                    <label for="unique" class="text-sm text-gray-700 dark:text-gray-300 select-none cursor-pointer">{{ __('Unique') }}</label>
                </div>
            </div>

            {{-- Error --}}
            <template x-if="error">
                <p class="text-sm text-red-500 mb-4" x-text="error"></p>
            </template>

            {{-- Generate button --}}
            <button @click="generate()"
                    class="w-full py-3 bg-gradient-to-r from-purple-500 to-violet-600 hover:from-purple-600 hover:to-violet-700 text-white font-semibold rounded-xl shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all active:scale-[.98]">
                {{ __('Generate') }}
            </button>

            {{-- Results --}}
            <template x-if="numbers.length > 0">
                <div class="mt-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Generated Numbers') }}</span>
                        <button @click="copyAll()"
                                class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-medium transition-colors">
                            <span x-show="!allCopied">{{ __('Copy All') }}</span>
                            <span x-show="allCopied" class="text-green-600 dark:text-green-400">{{ __('Copied!') }}</span>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(n, i) in numbers" :key="i">
                            <button @click="copyOne(i)"
                                    :class="copied[i] ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-300 dark:border-green-700' : 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/40 hover:bg-purple-100 dark:hover:bg-purple-900/30'"
                                    class="px-3 py-1.5 rounded-lg border font-mono text-sm font-medium transition-colors"
                                    x-text="n"></button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <p class="mt-6 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('Uses cryptographically secure random values from the browser.') }}
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function randNumGen() {
    return {
        min: 1,
        max: 100,
        count: 10,
        unique: false,
        numbers: [],
        copied: {},
        allCopied: false,
        error: '',
        generate() {
            this.error = '';
            this.numbers = [];
            this.copied = {};
            this.allCopied = false;
            const lo = Math.ceil(Math.min(this.min, this.max));
            const hi = Math.floor(Math.max(this.min, this.max));
            const range = hi - lo + 1;
            const cnt = Math.max(1, Math.min(100, this.count));
            if (this.unique && cnt > range) {
                this.error = '{{ __('Cannot generate more unique numbers than the range allows.') }}';
                return;
            }
            const arr = [];
            const used = new Set();
            let attempts = 0;
            while (arr.length < cnt && attempts < cnt * 200) {
                attempts++;
                const buf = new Uint32Array(1);
                crypto.getRandomValues(buf);
                const n = lo + (buf[0] % range);
                if (this.unique) {
                    if (!used.has(n)) { used.add(n); arr.push(n); }
                } else {
                    arr.push(n);
                }
            }
            this.numbers = arr;
        },
        copyOne(i) {
            navigator.clipboard.writeText(String(this.numbers[i])).then(() => {
                this.copied = { ...this.copied, [i]: true };
                setTimeout(() => { this.copied = { ...this.copied, [i]: false }; }, 1500);
            });
        },
        copyAll() {
            navigator.clipboard.writeText(this.numbers.join(', ')).then(() => {
                this.allCopied = true;
                setTimeout(() => { this.allCopied = false; }, 2000);
            });
        }
    };
}
</script>
@endpush
