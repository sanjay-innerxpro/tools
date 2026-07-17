@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('OTP Generator'))
@section('meta_description', __('Generate one-time passwords instantly and copy them to your clipboard with a single click.'))

@section('content')
<div x-data="otpGen()" x-cloak class="max-w-2xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-600 rounded-2xl shadow-lg shadow-blue-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 1.657-1.343 3-3 3S6 12.657 6 11s1.343-3 3-3 3 1.343 3 3zm8-3h-3.5a1.5 1.5 0 01-1.5-1.5V4m-7 0v2.5A1.5 1.5 0 016.5 8H3m0 10h3.5a1.5 1.5 0 001.5 1.5V20m7 0v-2.5a1.5 1.5 0 011.5-1.5H21"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('OTP Generator') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Generate secure one-time passwords and copy them instantly.') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 mb-4 flex items-center gap-3 min-h-[3.5rem]">
            <span class="flex-1 font-mono text-2xl tracking-wider text-gray-900 dark:text-white break-all select-all" x-text="code"></span>
            <button @click="copy()"
                    :class="copied ? 'bg-sky-700' : 'bg-sky-600 hover:bg-sky-700'"
                    class="flex-shrink-0 px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors">
                <span x-show="!copied">{{ __('Copy') }}</span>
                <span x-show="copied">{{ __('Copied!') }}</span>
            </button>
        </div>

        <div class="grid gap-4 mb-6 sm:grid-cols-2">
            <div>
                <div class="flex justify-between mb-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>{{ __('Length') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white" x-text="length"></span>
                </div>
                <input type="range" min="4" max="12" step="1" x-model="length" @input="generate()"
                       class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full appearance-none cursor-pointer accent-sky-500">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>4</span><span>12</span>
                </div>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('Type') }}</p>
                <div class="grid gap-2">
                    <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl cursor-pointer hover:bg-sky-50 dark:hover:bg-gray-700 border border-transparent hover:border-sky-200 dark:hover:border-sky-800 transition-all">
                        <input type="radio" name="otpType" value="numeric" x-model="type" @change="generate()" class="w-4 h-4 accent-sky-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Numeric only') }}</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl cursor-pointer hover:bg-sky-50 dark:hover:bg-gray-700 border border-transparent hover:border-sky-200 dark:hover:border-sky-800 transition-all">
                        <input type="radio" name="otpType" value="alphanumeric" x-model="type" @change="generate()" class="w-4 h-4 accent-sky-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Alphanumeric') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <button @click="generate()"
                class="w-full py-3.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-sky-500/20 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.581m15.356 2A8.001 8.001 0 004.581 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            {{ __('Generate OTP') }}
        </button>
    </div>
</div>

<script>
function otpGen() {
    return {
        length: 6,
        type: 'numeric',
        code: '',
        copied: false,
        get charset() {
            return this.type === 'alphanumeric'
                ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
                : '0123456789';
        },
        generate() {
            const charset = this.charset;
            const values = new Uint32Array(this.length);
            crypto.getRandomValues(values);
            this.code = Array.from(values, n => charset[n % charset.length]).join('');
            this.copied = false;
        },
        copy() {
            if (!this.code) return;
            navigator.clipboard.writeText(this.code).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        },
        init() {
            this.generate();
        }
    }
}
</script>
@endsection
