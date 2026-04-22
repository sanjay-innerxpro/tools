@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Regex Tester'))
@section('meta_description', __('Test regular expressions with live matches, flags, and instant feedback for faster debugging.'))

@section('content')
<div x-data="regexTester()" x-cloak class="max-w-4xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl shadow-lg shadow-rose-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Regex Tester') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Test regular expressions with live match highlighting') }}</p>
    </div>

    {{-- Pattern + Flags --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 mb-5">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">{{ __('Pattern') }}</label>
                <div class="flex items-center bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 gap-2 focus-within:ring-2 focus-within:ring-rose-400 transition">
                    <span class="text-gray-400 font-mono text-lg select-none">/</span>
                    <input x-model="pattern" @input="compute()"
                           type="text"
                           placeholder="{{ __('Enter regex pattern...') }}"
                           spellcheck="false"
                           class="flex-1 font-mono text-sm text-gray-900 dark:text-white placeholder-gray-400 bg-transparent focus:outline-none"/>
                    <span class="text-gray-400 font-mono text-lg select-none">/</span>
                    <span class="font-mono text-sm text-rose-500 font-bold" x-text="activeFlags"></span>
                </div>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">{{ __('Flags') }}</label>
                <div class="flex gap-2">
                    <template x-for="(active, flag) in flags" :key="flag">
                        <button @click="flags[flag]=!flags[flag]; compute()"
                                class="px-3 py-3 font-mono text-sm font-bold rounded-xl border-2 transition-all"
                                :class="active ? 'bg-rose-500 border-rose-500 text-white' : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-rose-300 dark:hover:border-rose-700'"
                                x-text="flag"></button>
                    </template>
                </div>
            </div>
        </div>
        <div x-show="error" class="mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-2 text-sm text-red-600 dark:text-red-400 font-mono" x-text="error"></div>
    </div>

    {{-- Test string --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 mb-5">
        <div class="flex items-center justify-between mb-3">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Test String') }}</label>
            <button @click="testStr=''; compute()" class="text-xs text-gray-400 hover:text-red-500 transition-colors">{{ __('Clear') }}</button>
        </div>
        <textarea x-model="testStr" @input="compute()"
                  placeholder="{{ __('Enter test string...') }}"
                  spellcheck="false"
                  rows="5"
                  class="w-full font-mono text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none focus:ring-2 focus:ring-rose-400 transition leading-relaxed"></textarea>
    </div>

    {{-- Highlighted result --}}
    <div x-show="testStr" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden mb-5">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Result') }}</h2>
            <div x-show="!error">
                <span x-show="matchCount>0" class="text-sm font-semibold text-rose-600 dark:text-rose-400"
                      x-text="matchCount + ' {{ __('match') }}' + (matchCount===1?'':'{{ __('es') }}')"></span>
                <span x-show="matchCount===0 && pattern" class="text-sm text-gray-400 italic">{{ __('No matches.') }}</span>
            </div>
        </div>
        <div class="p-6 font-mono text-sm leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-all
                    [&_mark]:bg-yellow-300 [&_mark]:dark:bg-yellow-600 [&_mark]:dark:text-gray-900 [&_mark]:rounded [&_mark]:px-0.5"
             x-html="highlighted || '&nbsp;'"></div>
    </div>

    {{-- Match list --}}
    <div x-show="matchList.length" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Matches') }}</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800 max-h-72 overflow-y-auto">
            <template x-for="(m, i) in matchList" :key="i">
                <div class="flex items-start gap-4 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <span class="text-xs text-gray-400 w-6 tabular-nums flex-shrink-0 pt-0.5" x-text="i+1"></span>
                    <div class="flex-1 min-w-0">
                        <code class="text-sm text-gray-900 dark:text-white font-mono bg-yellow-50 dark:bg-yellow-900/20 px-2 py-0.5 rounded" x-text="m.value"></code>
                        <span class="text-xs text-gray-400 ml-2">@{{ __('index') }} <span x-text="m.index"></span></span>
                        <template x-if="m.groups.length">
                            <div class="mt-1 flex flex-wrap gap-1">
                                <template x-for="(g, gi) in m.groups" :key="gi">
                                    <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded font-mono" x-text="'$'+(gi+1)+': '+g"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
        <div x-show="matchCount > 50" class="px-6 py-3 bg-gray-50 dark:bg-gray-800/30 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-800">
            {{ __('Showing first 50 matches.') }}
        </div>
    </div>

</div>

<script>
function regexTester() {
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    return {
        pattern: '',
        flags: { g: true, i: false, m: false, s: false },
        testStr: '',
        highlighted: '',
        matchList: [],
        matchCount: 0,
        error: '',

        get activeFlags() {
            return Object.entries(this.flags).filter(([,v])=>v).map(([k])=>k).join('');
        },

        compute() {
            if (!this.pattern) {
                this.highlighted = esc(this.testStr);
                this.matchList = []; this.matchCount = 0; this.error = '';
                return;
            }
            try {
                const fl = this.activeFlags;
                const reAll = new RegExp(this.pattern, fl.includes('g') ? fl : fl + 'g');
                this.error = '';
                const ms = [...this.testStr.matchAll(reAll)].filter(m => m[0].length > 0);
                this.matchCount = ms.length;
                this.matchList = ms.slice(0, 50).map(m => ({
                    index: m.index,
                    value: m[0],
                    groups: m.slice(1).filter(g => g !== undefined),
                }));
                let out = '', last = 0;
                for (const m of ms) {
                    out += esc(this.testStr.slice(last, m.index));
                    out += `<mark>${esc(m[0])}</mark>`;
                    last = m.index + m[0].length;
                }
                out += esc(this.testStr.slice(last));
                this.highlighted = out;
            } catch(e) {
                this.error = e.message;
                this.highlighted = esc(this.testStr);
                this.matchList = []; this.matchCount = 0;
            }
        }
    };
}
</script>
@endsection
