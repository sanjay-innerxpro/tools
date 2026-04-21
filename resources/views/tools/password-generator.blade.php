@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Password Generator'))

@section('content')
<div x-data="passwordGen()" x-cloak class="max-w-2xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Password Generator') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Create strong, random passwords instantly') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8">

        {{-- Password output --}}
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 mb-4 flex items-center gap-3 min-h-[3.5rem]">
            <span class="flex-1 font-mono text-lg tracking-wider text-gray-900 dark:text-white break-all select-all" x-text="password"></span>
            <button @click="generate()" title="{{ __('Regenerate') }}"
                    class="p-2 text-gray-400 hover:text-green-500 transition-colors flex-shrink-0 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            <button @click="copy()"
                    :class="copied ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'"
                    class="flex-shrink-0 px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors">
                <span x-show="!copied">{{ __('Copy') }}</span>
                <span x-show="copied">{{ __('Copied!') }}</span>
            </button>
        </div>

        {{-- Strength bar --}}
        <div class="mb-6">
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500 dark:text-gray-400">{{ __('Strength') }}</span>
                <span :class="strengthTextColor" class="font-semibold" x-text="strengthLabel"></span>
            </div>
            <div class="h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div :class="strengthBg" class="h-full rounded-full transition-all duration-300"
                     :style="'width:' + Math.round(strength / 6 * 100) + '%'"></div>
            </div>
        </div>

        {{-- Length slider --}}
        <div class="mb-6">
            <div class="flex justify-between mb-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Length') }}</label>
                <span class="text-sm font-bold text-green-600 dark:text-green-400 tabular-nums w-6 text-right" x-text="length"></span>
            </div>
            <input type="range" min="6" max="64" step="1"
                   x-model="length" @input="generate()"
                   class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full appearance-none cursor-pointer accent-green-500">
            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span>6</span><span>64</span>
            </div>
        </div>

        {{-- Character types --}}
        <div class="grid grid-cols-2 gap-3 mb-8">
            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl cursor-pointer hover:bg-green-50 dark:hover:bg-gray-700 border border-transparent hover:border-green-200 dark:hover:border-green-800 transition-all">
                <input type="checkbox" x-model="upper" @change="generate()" class="w-4 h-4 accent-green-500 cursor-pointer flex-shrink-0">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Uppercase') }} <span class="text-gray-400 font-normal">A–Z</span></span>
            </label>
            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl cursor-pointer hover:bg-green-50 dark:hover:bg-gray-700 border border-transparent hover:border-green-200 dark:hover:border-green-800 transition-all">
                <input type="checkbox" x-model="lower" @change="generate()" class="w-4 h-4 accent-green-500 cursor-pointer flex-shrink-0">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Lowercase') }} <span class="text-gray-400 font-normal">a–z</span></span>
            </label>
            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl cursor-pointer hover:bg-green-50 dark:hover:bg-gray-700 border border-transparent hover:border-green-200 dark:hover:border-green-800 transition-all">
                <input type="checkbox" x-model="numbers" @change="generate()" class="w-4 h-4 accent-green-500 cursor-pointer flex-shrink-0">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Numbers') }} <span class="text-gray-400 font-normal">0–9</span></span>
            </label>
            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl cursor-pointer hover:bg-green-50 dark:hover:bg-gray-700 border border-transparent hover:border-green-200 dark:hover:border-green-800 transition-all">
                <input type="checkbox" x-model="symbols" @change="generate()" class="w-4 h-4 accent-green-500 cursor-pointer flex-shrink-0">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Symbols') }} <span class="text-gray-400 font-normal">!@#$</span></span>
            </label>
        </div>

        <button @click="generate()"
                class="w-full py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg shadow-green-500/20 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            {{ __('Generate Password') }}
        </button>
    </div>
</div>

<script>
function passwordGen() {
    return {
        length: 16,
        upper: true,
        lower: true,
        numbers: true,
        symbols: false,
        password: '',
        copied: false,
        get strength() {
            let s = 0;
            if (this.upper) s++;
            if (this.lower) s++;
            if (this.numbers) s++;
            if (this.symbols) s++;
            if (this.length >= 12) s++;
            if (this.length >= 20) s++;
            return Math.min(s, 6);
        },
        get strengthLabel() {
            const s = this.strength;
            if (s <= 2) return '{{ __('Weak') }}';
            if (s <= 3) return '{{ __('Fair') }}';
            if (s <= 4) return '{{ __('Good') }}';
            return '{{ __('Strong') }}';
        },
        get strengthBg() {
            const s = this.strength;
            if (s <= 2) return 'bg-red-500';
            if (s <= 3) return 'bg-orange-400';
            if (s <= 4) return 'bg-yellow-400';
            return 'bg-green-500';
        },
        get strengthTextColor() {
            const s = this.strength;
            if (s <= 2) return 'text-red-500';
            if (s <= 3) return 'text-orange-500';
            if (s <= 4) return 'text-yellow-500';
            return 'text-green-500';
        },
        generate() {
            let charset = '';
            if (this.upper) charset += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            if (this.lower) charset += 'abcdefghijklmnopqrstuvwxyz';
            if (this.numbers) charset += '0123456789';
            if (this.symbols) charset += '!@#$%^&*()-_=+[]{}|;:,.<>?';
            if (!charset) { this.password = '—'; return; }
            const arr = new Uint32Array(this.length);
            crypto.getRandomValues(arr);
            this.password = Array.from(arr, n => charset[n % charset.length]).join('');
            this.copied = false;
        },
        copy() {
            if (!this.password || this.password === '—') return;
            navigator.clipboard.writeText(this.password).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        },
        init() { this.generate(); }
    }
}
</script>
@endsection
