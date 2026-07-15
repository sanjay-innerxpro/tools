@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Number Base Converter'))
@section('meta_description', __('Convert numbers between binary, octal, decimal, and hexadecimal formats instantly.'))

@section('content')
<div x-data="baseConverter()" x-cloak class="max-w-2xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl shadow-lg shadow-indigo-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Number Base Converter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Convert numbers between binary, octal, decimal and hex') }}</p>
    </div>

    {{-- Input + Base selector --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 mb-6">
        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-3">{{ __('Input Base') }}</label>
        <div class="flex gap-2 mb-4 flex-wrap">
            <template x-for="b in baseMeta" :key="b.val">
                <button @click="fromBase=b.val; convert()"
                        class="flex-1 min-w-16 py-2.5 text-sm font-bold rounded-xl border-2 transition-all"
                        :class="fromBase===b.val
                            ? 'bg-indigo-500 border-indigo-500 text-white shadow-md shadow-indigo-500/20'
                            : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-700'">
                    <span x-text="b.short"></span>
                    <span class="block text-[10px] font-normal opacity-70" x-text="b.label"></span>
                </button>
            </template>
        </div>
        <div>
            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">{{ __('Enter a number...') }}</label>
            <input x-model="input" @input="convert()"
                   type="text"
                   spellcheck="false"
                   placeholder="e.g. 255"
                   class="w-full font-mono text-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition tracking-wider"/>
        </div>
        <div x-show="error" class="mt-3 text-sm text-red-600 dark:text-red-400" x-text="error"></div>
    </div>

    {{-- Output rows --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Conversions') }}</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="b in baseMeta" :key="b.val">
                <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group"
                     :class="fromBase===b.val ? 'bg-indigo-50/50 dark:bg-indigo-950/20' : ''">
                    <div class="w-36 flex-shrink-0">
                        <span class="text-xs font-bold uppercase tracking-wider"
                              :class="fromBase===b.val ? 'text-indigo-500' : 'text-gray-400'"
                              x-text="b.long"></span>
                        <span class="ml-1.5 text-xs text-gray-400 font-mono" x-text="'('+b.prefix+')'"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p x-show="!results[b.key]" class="text-sm text-gray-300 dark:text-gray-600 font-mono">—</p>
                        <p x-show="results[b.key]" class="font-mono text-sm text-gray-900 dark:text-white break-all tracking-wider" x-text="results[b.key]"></p>
                    </div>
                    <button @click="copyKey(b.key)" x-show="results[b.key]"
                            class="flex-shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                            :class="copied[b.key] ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 opacity-100' : 'text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20'">
                        <span x-show="!copied[b.key]">{{ __('Copy') }}</span>
                        <span x-show="copied[b.key]">{{ __('Copied!') }}</span>
                    </button>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
function baseConverter() {
    return {
        input: '',
        fromBase: 10,
        error: '',
        results: { bin: '', oct: '', dec: '', hex: '' },
        copied: { bin: false, oct: false, dec: false, hex: false },

        baseMeta: [
            { val: 2,  key: 'bin', short: 'BIN', label: 'Base 2',  long: 'Binary',       prefix: '0b' },
            { val: 8,  key: 'oct', short: 'OCT', label: 'Base 8',  long: 'Octal',        prefix: '0o' },
            { val: 10, key: 'dec', short: 'DEC', label: 'Base 10', long: 'Decimal',      prefix: ''   },
            { val: 16, key: 'hex', short: 'HEX', label: 'Base 16', long: 'Hexadecimal',  prefix: '0x' },
        ],

        convert() {
            const v = this.input.trim();
            if (!v) { this.results = { bin:'', oct:'', dec:'', hex:'' }; this.error = ''; return; }
            try {
                const n = parseInt(v, this.fromBase);
                if (isNaN(n)) throw new Error();
                // Validate all chars are valid for the base
                const valid = '0123456789abcdefABCDEF'.slice(0, this.fromBase <= 10 ? this.fromBase : 16);
                if (this.fromBase <= 16 && v.split('').some(c => !valid.includes(c))) throw new Error();
                this.error = '';
                this.results = {
                    bin: n.toString(2),
                    oct: n.toString(8),
                    dec: n.toString(10),
                    hex: n.toString(16).toUpperCase(),
                };
            } catch {
                this.error = '{{ __("Invalid number for the selected base.") }}';
                this.results = { bin: '', oct: '', dec: '', hex: '' };
            }
        },

        copyKey(key) {
            const v = this.results[key];
            if (!v) return;
            navigator.clipboard.writeText(v).then(() => {
                this.copied[key] = true;
                setTimeout(() => this.copied[key] = false, 2000);
            });
        }
    };
}
</script>
@endsection
