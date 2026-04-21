@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Lorem Ipsum Generator'))

@section('content')
<div x-data="loremIpsum()" x-cloak class="max-w-3xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-teal-400 to-cyan-500 rounded-2xl shadow-lg shadow-teal-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Lorem Ipsum Generator') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Generate placeholder text for your designs and mockups') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8 mb-6">

        {{-- Type tabs --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('Type') }}</label>
            <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-full sm:w-auto">
                <button @click="type='paragraphs';generate()"
                        :class="type==='paragraphs'?'bg-white dark:bg-gray-700 text-teal-600 dark:text-teal-400 shadow-sm':'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        class="flex-1 sm:flex-none px-5 py-2 rounded-lg text-sm font-semibold transition-all">{{ __('Paragraphs') }}</button>
                <button @click="type='sentences';generate()"
                        :class="type==='sentences'?'bg-white dark:bg-gray-700 text-teal-600 dark:text-teal-400 shadow-sm':'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        class="flex-1 sm:flex-none px-5 py-2 rounded-lg text-sm font-semibold transition-all">{{ __('Sentences') }}</button>
                <button @click="type='words';generate()"
                        :class="type==='words'?'bg-white dark:bg-gray-700 text-teal-600 dark:text-teal-400 shadow-sm':'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        class="flex-1 sm:flex-none px-5 py-2 rounded-lg text-sm font-semibold transition-all">{{ __('Words') }}</button>
            </div>
        </div>

        {{-- Count slider --}}
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Count') }}</label>
                <span class="text-sm font-bold text-teal-600 dark:text-teal-400 tabular-nums w-8 text-right" x-text="count"></span>
            </div>
            <input type="range" min="1" :max="type==='words'?200:10" step="1"
                   x-model.number="count" @input="generate()"
                   class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full appearance-none cursor-pointer accent-teal-500">
            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span>1</span><span x-text="type==='words'?200:10"></span>
            </div>
        </div>

        {{-- Start with Lorem toggle --}}
        <div class="flex items-center gap-3 mb-8">
            <button @click="startWithLorem=!startWithLorem;generate()"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors flex-shrink-0"
                    :class="startWithLorem?'bg-teal-500':'bg-gray-300 dark:bg-gray-600'">
                <span :class="startWithLorem?'translate-x-6':'translate-x-1'"
                      class="inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
            </button>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start with "Lorem ipsum..."') }}</span>
        </div>

        <button @click="generate()"
                class="w-full py-3.5 bg-gradient-to-r from-teal-400 to-cyan-500 hover:from-teal-500 hover:to-cyan-600 text-white font-semibold rounded-xl shadow-lg shadow-teal-500/20 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            {{ __('Generate') }}
        </button>
    </div>

    {{-- Output --}}
    <div x-show="output" x-transition class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Output') }}</p>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400" x-text="wordCount + ' {{ __('Words') }}'"></span>
                <button @click="copy()"
                        :class="copied?'bg-teal-700':'bg-teal-600 hover:bg-teal-700'"
                        class="px-4 py-1.5 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-1.5">
                    <span x-show="!copied">{{ __('Copy') }}</span>
                    <span x-show="copied">{{ __('Copied!') }}</span>
                </button>
            </div>
        </div>
        <div x-text="output" class="text-gray-700 dark:text-gray-300 text-sm leading-7 whitespace-pre-wrap max-h-80 overflow-y-auto"></div>
    </div>
</div>

<script>
function loremIpsum() {
    const W = 'lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua enim ad minim veniam quis nostrud exercitation ullamco laboris nisi aliquip ex ea commodo consequat duis aute irure reprehenderit voluptate velit esse cillum fugiat nulla pariatur excepteur sint occaecat cupidatat non proident sunt culpa qui officia deserunt mollit anim id est laborum pellentesque habitant morbi tristique senectus netus malesuada fames turpis egestas pretium aenean pharetra magna placerat vestibulum ornare quam viverra orci sagittis volutpat odio facilisis mauris rhoncus ultrices vitae auctor augue lectus arcu bibendum varius nunc aliquet gravida neque convallis suspendisse lacus tellus cras felis vivamus dui arcu'.split(' ');
    const r = (a,b) => a + Math.floor(Math.random()*(b-a+1));
    const cap = s => s.charAt(0).toUpperCase()+s.slice(1);
    const sentence = () => { const ws=[]; for(let i=0;i<r(8,18);i++) ws.push(W[r(0,W.length-1)]); return cap(ws.join(' '))+'.'; };
    const paragraph = () => { const ss=[]; for(let i=0;i<r(3,5);i++) ss.push(sentence()); return ss.join(' '); };
    const LOREM = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    return {
        type: 'paragraphs', count: 3, startWithLorem: true, output: '', copied: false,
        get wordCount() { return this.output ? this.output.trim().split(/\s+/).filter(Boolean).length : 0; },
        generate() {
            let res = '';
            if (this.type === 'paragraphs') {
                const parts = [];
                for (let i=0; i<this.count; i++) {
                    let p = paragraph();
                    if (i===0 && this.startWithLorem) p = LOREM + ' ' + p;
                    parts.push(p);
                }
                res = parts.join('\n\n');
            } else if (this.type === 'sentences') {
                const parts = [];
                for (let i=0; i<this.count; i++) parts.push(i===0 && this.startWithLorem ? LOREM : sentence());
                res = parts.join(' ');
            } else {
                const ws = [];
                for (let i=0; i<this.count; i++) ws.push(W[r(0,W.length-1)]);
                if (this.startWithLorem) { ws[0]='Lorem'; if(ws.length>1) ws[1]='ipsum'; if(ws.length>2) ws[2]='dolor'; }
                res = ws.join(' ');
            }
            this.output = res;
            this.copied = false;
        },
        copy() { navigator.clipboard.writeText(this.output).then(() => { this.copied=true; setTimeout(()=>this.copied=false,2000); }); },
        init() { this.generate(); }
    }
}
</script>
@endsection
