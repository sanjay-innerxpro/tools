@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Diff Checker'))
@section('meta_description', __('Compare two text blocks and highlight differences instantly to review edits and spot changes.'))

@section('content')
<div x-data="diffChecker()" x-cloak class="max-w-7xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl shadow-lg shadow-amber-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Diff Checker') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Compare two texts and highlight the differences') }}</p>
    </div>

    {{-- Input panes --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Original') }}</span>
                <button @click="original=''; compute()" class="text-xs text-gray-400 hover:text-red-500 transition-colors">{{ __('Clear') }}</button>
            </div>
            <textarea x-model="original" @input="compute()"
                      placeholder="{{ __('Enter original text...') }}"
                      spellcheck="false"
                      class="flex-1 min-h-[18rem] font-mono text-sm p-5 bg-transparent text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none leading-relaxed"></textarea>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Modified') }}</span>
                <button @click="modified=''; compute()" class="text-xs text-gray-400 hover:text-red-500 transition-colors">{{ __('Clear') }}</button>
            </div>
            <textarea x-model="modified" @input="compute()"
                      placeholder="{{ __('Enter modified text...') }}"
                      spellcheck="false"
                      class="flex-1 min-h-[18rem] font-mono text-sm p-5 bg-transparent text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none leading-relaxed"></textarea>
        </div>
    </div>

    {{-- Too large warning --}}
    <div x-show="tooLarge" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl px-5 py-3 mb-5 text-sm text-amber-700 dark:text-amber-400">
        {{ __('Input too large to diff. Please limit each side to 500 lines.') }}
    </div>

    {{-- Stats bar --}}
    <div x-show="diff.length && !tooLarge" class="flex flex-wrap items-center gap-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-6 py-4 mb-5 shadow">
        <div class="flex items-center gap-2">
            <span class="inline-block w-3 h-3 rounded-sm bg-green-400"></span>
            <span class="text-sm font-semibold text-green-600 dark:text-green-400">+<span x-text="addedCount"></span> {{ __('added') }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-3 h-3 rounded-sm bg-red-400"></span>
            <span class="text-sm font-semibold text-red-600 dark:text-red-400">−<span x-text="removedCount"></span> {{ __('removed') }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-3 h-3 rounded-sm bg-gray-300 dark:bg-gray-600"></span>
            <span class="text-sm text-gray-500 dark:text-gray-400"><span x-text="unchangedCount"></span> {{ __('unchanged') }}</span>
        </div>
        <div class="ml-auto">
            <span x-show="addedCount===0 && removedCount===0" class="text-sm text-gray-400 italic">{{ __('No differences found.') }}</span>
        </div>
    </div>

    {{-- Diff output --}}
    <div x-show="diff.length && !tooLarge" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Differences') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <div class="font-mono text-sm min-w-0">
                <template x-for="(line, idx) in diff" :key="idx">
                    <div class="flex items-stretch px-4 py-0.5 transition-colors"
                         :class="{
                             'bg-green-50 dark:bg-green-950/30': line.type==='added',
                             'bg-red-50 dark:bg-red-950/30': line.type==='removed',
                             'hover:bg-gray-50 dark:hover:bg-gray-800/30': line.type==='equal'
                         }">
                        <span class="w-5 flex-shrink-0 text-xs leading-6 font-bold select-none"
                              :class="{
                                  'text-green-500': line.type==='added',
                                  'text-red-500': line.type==='removed',
                                  'text-gray-300 dark:text-gray-600': line.type==='equal'
                              }"
                              x-text="line.type==='added' ? '+' : line.type==='removed' ? '−' : ' '"></span>
                        <span class="flex-1 leading-6 whitespace-pre-wrap break-all pl-3"
                              :class="{
                                  'text-green-700 dark:text-green-300': line.type==='added',
                                  'text-red-700 dark:text-red-300': line.type==='removed',
                                  'text-gray-600 dark:text-gray-400': line.type==='equal'
                              }"
                              x-text="line.value || '\u00a0'"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>

<script>
function diffChecker() {
    function lcsDp(a, b) {
        const m = a.length, n = b.length;
        const dp = Array.from({ length: m + 1 }, () => new Int32Array(n + 1));
        for (let i = 1; i <= m; i++)
            for (let j = 1; j <= n; j++)
                dp[i][j] = a[i-1] === b[j-1] ? dp[i-1][j-1] + 1 : Math.max(dp[i-1][j], dp[i][j-1]);
        return dp;
    }

    function buildDiff(a, b) {
        const dp = lcsDp(a, b);
        const result = [];
        let i = a.length, j = b.length;
        while (i > 0 || j > 0) {
            if (i > 0 && j > 0 && a[i-1] === b[j-1]) {
                result.unshift({ type: 'equal',   value: a[i-1] }); i--; j--;
            } else if (j > 0 && (i === 0 || dp[i][j-1] >= dp[i-1][j])) {
                result.unshift({ type: 'added',   value: b[j-1] }); j--;
            } else {
                result.unshift({ type: 'removed', value: a[i-1] }); i--;
            }
        }
        return result;
    }

    return {
        original: '',
        modified: '',
        diff: [],
        tooLarge: false,

        get addedCount()    { return this.diff.filter(l => l.type === 'added').length; },
        get removedCount()  { return this.diff.filter(l => l.type === 'removed').length; },
        get unchangedCount(){ return this.diff.filter(l => l.type === 'equal').length; },

        compute() {
            if (!this.original && !this.modified) { this.diff = []; this.tooLarge = false; return; }
            const a = this.original.split('\n');
            const b = this.modified.split('\n');
            if (a.length > 500 || b.length > 500) { this.tooLarge = true; this.diff = []; return; }
            this.tooLarge = false;
            this.diff = buildDiff(a, b);
        }
    };
}
</script>
@endsection
