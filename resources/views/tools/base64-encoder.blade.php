@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Base64 Encoder / Decoder'))

@section('content')
<div x-data="base64Tool()" x-cloak class="max-w-5xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl shadow-lg shadow-violet-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Base64 Encoder / Decoder') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Encode text to Base64 or decode Base64 to plain text') }}</p>
    </div>

    {{-- Mode tabs --}}
    <div class="flex justify-center mb-6">
        <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-xl p-1">
            <button @click="setMode('encode')"
                    :class="mode === 'encode' ? 'bg-white dark:bg-gray-700 text-violet-600 dark:text-violet-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="px-6 py-2 rounded-lg text-sm font-semibold transition-all">
                {{ __('Encode') }}
            </button>
            <button @click="setMode('decode')"
                    :class="mode === 'decode' ? 'bg-white dark:bg-gray-700 text-violet-600 dark:text-violet-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="px-6 py-2 rounded-lg text-sm font-semibold transition-all">
                {{ __('Decode') }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Input --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    x-text="mode === 'encode' ? '{{ __('Plain Text') }}' : 'Base64'"></h2>
                <button @click="clearAll()" class="text-xs text-gray-400 hover:text-red-500 transition-colors">{{ __('Clear') }}</button>
            </div>
            <textarea
                x-model="input"
                @input="run()"
                :placeholder="mode === 'encode' ? '{{ __('Enter text to encode...') }}' : '{{ __('Enter Base64 to decode...') }}'"
                spellcheck="false"
                class="flex-1 min-h-[16rem] font-mono text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none focus:ring-2 focus:ring-violet-400 transition"
            ></textarea>
        </div>

        {{-- Output --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    x-text="mode === 'encode' ? 'Base64' : '{{ __('Plain Text') }}'"></h2>
                <button @click="copyOutput()" x-show="output"
                        class="text-xs font-medium transition-colors"
                        :class="copied ? 'text-violet-600' : 'text-gray-400 hover:text-violet-500'">
                    <span x-show="!copied">{{ __('Copy') }}</span>
                    <span x-show="copied">{{ __('Copied!') }}</span>
                </button>
            </div>
            <pre :class="error ? 'text-red-500' : (output ? 'text-gray-900 dark:text-white' : 'text-gray-400')"
                 x-text="error || output || '{{ __('Output will appear here...') }}'"
                 class="flex-1 min-h-[16rem] font-mono text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 overflow-auto whitespace-pre-wrap break-all"></pre>
        </div>
    </div>

    {{-- Swap --}}
    <div class="flex justify-center mt-5">
        <button @click="swap()"
                class="flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:border-violet-400 dark:hover:border-violet-500 text-gray-700 dark:text-gray-300 font-medium rounded-xl shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            {{ __('Swap') }}
        </button>
    </div>
</div>

<script>
function base64Tool() {
    return {
        mode: 'encode',
        input: '',
        output: '',
        error: '',
        copied: false,
        setMode(m) {
            this.mode = m;
            this.input = this.output = this.error = '';
            this.copied = false;
        },
        run() {
            this.error = '';
            if (!this.input) { this.output = ''; return; }
            try {
                if (this.mode === 'encode') {
                    this.output = btoa(unescape(encodeURIComponent(this.input)));
                } else {
                    this.output = decodeURIComponent(escape(atob(this.input.trim())));
                }
            } catch(e) {
                this.error = '{{ __('Invalid Base64 input.') }}';
                this.output = '';
            }
        },
        swap() {
            [this.input, this.output] = [this.output, this.input];
            this.mode = this.mode === 'encode' ? 'decode' : 'encode';
            this.error = '';
            this.copied = false;
        },
        clearAll() { this.input = this.output = this.error = ''; this.copied = false; },
        copyOutput() {
            navigator.clipboard.writeText(this.output).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        }
    }
}
</script>
@endsection
