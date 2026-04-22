@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('UUID Generator'))
@section('meta_description', __('Generate secure UUIDs instantly for apps, APIs, databases, and development workflows.'))

@section('content')
<div x-data="uuidGen()" x-cloak x-init="generate()" class="max-w-3xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl shadow-lg shadow-violet-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('UUID Generator') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Generate random UUID v4 strings instantly') }}</p>
    </div>

    {{-- Controls --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 mb-6">
        <div class="flex flex-wrap items-center gap-8">
            <div class="flex-1 min-w-48">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-3">
                    {{ __('Count') }}: <span class="text-violet-500 font-bold" x-text="count"></span>
                </label>
                <input type="range" min="1" max="20" x-model.number="count"
                       class="w-full h-2 rounded-full accent-violet-500 cursor-pointer"/>
            </div>
            <label class="flex items-center gap-3 cursor-pointer select-none flex-shrink-0">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Uppercase') }}</span>
                <div class="relative">
                    <input type="checkbox" x-model="uppercase" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer-checked:bg-violet-500 transition-colors"></div>
                    <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                </div>
            </label>
        </div>
    </div>

    {{-- UUID list --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden mb-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">UUID v4</h2>
            <div class="flex items-center gap-2">
                <button @click="copyAll()"
                        class="px-4 py-1.5 text-xs font-semibold rounded-lg transition-colors"
                        :class="allCopied ? 'bg-violet-600 text-white' : 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 hover:bg-violet-200 dark:hover:bg-violet-800/40'">
                    <span x-show="!allCopied">{{ __('Copy All') }}</span>
                    <span x-show="allCopied">{{ __('All Copied!') }}</span>
                </button>
                <button @click="generate()"
                        class="px-4 py-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 rounded-lg transition-colors">
                    {{ __('Regenerate') }}
                </button>
            </div>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="(item, i) in items" :key="i">
                <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                    <span class="text-xs text-gray-400 w-5 flex-shrink-0 text-right tabular-nums" x-text="i+1"></span>
                    <code class="flex-1 font-mono text-sm text-gray-900 dark:text-white break-all"
                          x-text="uppercase ? item.value.toUpperCase() : item.value"></code>
                    <button @click="copyOne(i)"
                            class="flex-shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                            :class="item.copied ? 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 opacity-100' : 'text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20'">
                        <span x-show="!item.copied">{{ __('Copy') }}</span>
                        <span x-show="item.copied">{{ __('Copied!') }}</span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <button @click="generate()"
            class="w-full py-4 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 text-white font-semibold rounded-2xl shadow-lg shadow-violet-500/20 transition-all active:scale-[0.99]">
        {{ __('Generate UUIDs') }}
    </button>

</div>

<script>
function uuidGen() {
    return {
        count: 5,
        uppercase: false,
        items: [],
        allCopied: false,

        genOne() {
            return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
                (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
            );
        },

        generate() {
            this.items = Array.from({ length: this.count }, () => ({ value: this.genOne(), copied: false }));
            this.allCopied = false;
        },

        copyOne(i) {
            const val = this.uppercase ? this.items[i].value.toUpperCase() : this.items[i].value;
            navigator.clipboard.writeText(val).then(() => {
                this.items[i].copied = true;
                setTimeout(() => this.items[i].copied = false, 2000);
            });
        },

        copyAll() {
            const all = this.items.map(it => this.uppercase ? it.value.toUpperCase() : it.value).join('\n');
            navigator.clipboard.writeText(all).then(() => {
                this.allCopied = true;
                setTimeout(() => this.allCopied = false, 2000);
            });
        }
    };
}
</script>
@endsection
