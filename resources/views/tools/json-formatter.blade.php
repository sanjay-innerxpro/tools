@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('JSON Formatter'))
@section('meta_description', __('Format, validate, and beautify JSON data online to improve readability and catch syntax issues.'))

@section('content')
<div x-data="jsonFormatter()" x-cloak class="max-w-6xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-2xl shadow-lg shadow-amber-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('JSON Formatter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Format & validate JSON instantly') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Input panel --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Input') }}</h2>
                <button @click="clear()" class="text-xs text-gray-400 hover:text-red-500 transition-colors">{{ __('Clear') }}</button>
            </div>
            <textarea
                x-model="input"
                @input="autoProcess()"
                placeholder="{{ __('Paste JSON here...') }}"
                spellcheck="false"
                class="flex-1 min-h-[22rem] font-mono text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
            ></textarea>
        </div>

        {{-- Output panel --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Output') }}</h2>
                <button @click="copyOutput()" x-show="output"
                        class="text-xs font-medium transition-colors"
                        :class="copied ? 'text-amber-600' : 'text-gray-400 hover:text-amber-500'">
                    <span x-show="!copied">{{ __('Copy') }}</span>
                    <span x-show="copied">{{ __('Copied!') }}</span>
                </button>
            </div>
            <pre x-text="output || placeholder"
                 :class="output ? 'text-gray-900 dark:text-white' : 'text-gray-400'"
                 class="flex-1 min-h-[22rem] font-mono text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 overflow-auto whitespace-pre-wrap break-all"
            ></pre>
        </div>
    </div>

    {{-- Status bar --}}
    <div x-show="status" x-transition class="mt-4 flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium"
         :class="status === 'valid' ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400'">
        <svg x-show="status === 'valid'" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <svg x-show="status === 'error'" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        <span x-text="statusMsg"></span>
    </div>

    {{-- Actions --}}
    <div class="mt-5 flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 mr-auto">
            <label class="text-sm text-gray-600 dark:text-gray-400">{{ __('Indent') }}</label>
            <select x-model="indent" @change="if(input.trim()) format()"
                    class="text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                <option value="2">2 {{ __('spaces') }}</option>
                <option value="4">4 {{ __('spaces') }}</option>
                <option value="1">1 {{ __('space') }}</option>
            </select>
        </div>
        <button @click="format()"
                class="px-6 py-2.5 bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 text-white font-semibold rounded-xl shadow-md transition-all">
            {{ __('Format') }}
        </button>
        <button @click="minify()"
                class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:border-amber-400 dark:hover:border-amber-500 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all">
            {{ __('Minify') }}
        </button>
    </div>
</div>

<script>
function jsonFormatter() {
    return {
        input: '',
        output: '',
        indent: 2,
        status: null,
        statusMsg: '',
        copied: false,
        get placeholder() { return '{{ __('Output will appear here...') }}'; },
        autoProcess() {
            if (this.input.trim()) this.format();
            else { this.output = ''; this.status = null; }
        },
        format() {
            if (!this.input.trim()) return;
            try {
                const parsed = JSON.parse(this.input);
                this.output = JSON.stringify(parsed, null, parseInt(this.indent));
                this.status = 'valid';
                this.statusMsg = '{{ __('Valid JSON') }}';
            } catch(e) {
                this.status = 'error';
                this.statusMsg = e.message;
                this.output = '';
            }
        },
        minify() {
            if (!this.input.trim()) return;
            try {
                const parsed = JSON.parse(this.input);
                this.output = JSON.stringify(parsed);
                this.status = 'valid';
                this.statusMsg = '{{ __('Valid JSON') }}';
            } catch(e) {
                this.status = 'error';
                this.statusMsg = e.message;
                this.output = '';
            }
        },
        clear() { this.input = ''; this.output = ''; this.status = null; this.copied = false; },
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
