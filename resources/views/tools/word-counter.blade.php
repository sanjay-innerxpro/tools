@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Word & Character Counter'))

@section('content')
<div x-data="wordCounter()" x-cloak class="max-w-4xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg shadow-emerald-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Word & Character Counter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Count words, characters, sentences, and reading time — instantly') }}</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.words"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-1">{{ __('Words') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.chars"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-1">{{ __('Characters') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.sentences"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-1">{{ __('Sentences') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.readTime"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-1">{{ __('Read time') }}</p>
        </div>
    </div>

    {{-- Text Area --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between mb-3">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Your text') }}</label>
            <div class="flex gap-2">
                <button @click="text='';analyze()" class="text-xs text-gray-400 hover:text-red-500 transition-colors" x-show="text.length>0">{{ __('Clear') }}</button>
                <button @click="copyText()" class="text-xs text-gray-400 hover:text-blue-500 transition-colors" x-show="text.length>0" x-text="copied?'{{ __('Copied!') }}':'{{ __('Copy') }}'"></button>
            </div>
        </div>
        <textarea x-model="text" @input="analyze()" rows="14" placeholder="{{ __('Start typing or paste your text here...') }}" class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-y font-[inherit] leading-relaxed"></textarea>
    </div>

    {{-- Extra Stats --}}
    <div x-show="text.length > 0" class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3" x-transition>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="stats.charsNoSpace"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Chars (no spaces)') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="stats.paragraphs"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Paragraphs') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="stats.lines"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Lines') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="stats.avgWordLen"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Avg word length') }}</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function wordCounter(){return{text:'',copied:false,stats:{words:0,chars:0,charsNoSpace:0,sentences:0,paragraphs:0,lines:0,readTime:'0 sec',avgWordLen:0},
analyze(){const t=this.text;this.stats.chars=t.length;this.stats.charsNoSpace=t.replace(/\s/g,'').length;
const words=t.trim()?t.trim().split(/\s+/):[];this.stats.words=words.length;
this.stats.sentences=t.trim()?(t.match(/[.!?]+(?=\s|$)/g)||[]).length||( t.trim().length>0?1:0):0;
if(this.stats.sentences===0&&words.length>0)this.stats.sentences=1;
this.stats.paragraphs=t.trim()?t.split(/\n\s*\n/).filter(p=>p.trim()).length:0;
this.stats.lines=t.trim()?t.split('\n').length:0;
const wpm=238;const mins=words.length/wpm;this.stats.readTime=mins<1?(Math.ceil(mins*60)+' {{ __('sec') }}'):(Math.round(mins)+' {{ __('min') }}');
this.stats.avgWordLen=words.length?Math.round(words.reduce((a,w)=>a+w.length,0)/words.length*10)/10:0},
copyText(){navigator.clipboard.writeText(this.text);this.copied=true;setTimeout(()=>this.copied=false,2000)}}}
</script>
@endpush
