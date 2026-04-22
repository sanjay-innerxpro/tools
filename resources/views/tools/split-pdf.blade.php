@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Split PDF'))
@section('meta_description', __('Split PDF files into separate pages or custom page ranges quickly and securely online.'))

@section('content')
<div x-data="splitPdf()" x-cloak class="max-w-4xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg shadow-indigo-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Split PDF') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Extract pages or split into individual files') }}</p>
    </div>

    <div x-show="!result" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8 mb-6">
        <div @dragover.prevent="drag=true" @dragleave.prevent="drag=false" @drop.prevent="drag=false;dropFile($event)"
             :class="drag?'border-blue-500 bg-blue-50 dark:bg-blue-900/20':'border-gray-300 dark:border-gray-700'"
             class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer" @click="$refs.fi.click()">
            <input type="file" x-ref="fi" @change="file=$event.target.files[0];error=null" accept=".pdf" class="hidden">
            <div x-show="!file" class="space-y-3">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-medium">{{ __('Drop your PDF here or click to browse') }}</p>
                <p class="text-sm text-gray-400">{{ __('Max 50 MB') }}</p>
            </div>
            <div x-show="file" class="flex items-center justify-center gap-3">
                <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div class="text-left"><p class="font-medium text-gray-900 dark:text-white" x-text="file?.name"></p><p class="text-sm text-gray-500" x-text="fmt(file?.size)"></p></div>
                <button @click.stop="file=null" class="ml-4 p-1 text-gray-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Split mode') }}</label>
            <div class="flex gap-3">
                <button @click="mode='all'" :class="mode==='all'?'bg-indigo-600 text-white border-indigo-600':'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-indigo-400'" class="px-4 py-2 text-sm font-medium border rounded-lg transition-all">{{ __('Every page') }}</button>
                <button @click="mode='range'" :class="mode==='range'?'bg-indigo-600 text-white border-indigo-600':'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-indigo-400'" class="px-4 py-2 text-sm font-medium border rounded-lg transition-all">{{ __('Page range') }}</button>
            </div>
            <div x-show="mode==='range'" class="flex gap-3 items-center" x-transition>
                <input type="number" x-model.number="from" min="1" placeholder="{{ __('From') }}" class="w-24 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <span class="text-gray-400">{{ __('to') }}</span>
                <input type="number" x-model.number="to" min="1" placeholder="{{ __('End') }}" class="w-24 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="run()" :disabled="!file||busy" class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 text-white font-semibold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <template x-if="!busy"><span class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2"/></svg>{{ __('Split PDF') }}</span></template>
                <template x-if="busy"><span class="flex items-center gap-2"><svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>{{ __('Splitting...') }}</span></template>
            </button>
        </div>
    </div>

    <template x-if="error"><div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6"><p class="text-red-700 dark:text-red-300" x-text="error"></p></div></template>

    <template x-if="result">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 fade-in">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Split Complete') }}</h2>
                <span class="text-sm text-gray-500" x-text="result.totalPages + ' {{ __('total pages') }}'"></span>
            </div>
            <div class="space-y-2 max-h-80 overflow-y-auto">
                <template x-for="(f,i) in result.files" :key="i">
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 rounded-lg px-4 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="f.name"></span>
                            <span class="text-xs text-gray-400" x-text="fmt(f.size)"></span>
                        </div>
                        <a :href="f.downloadUrl" :download="f.name" class="px-3 py-1.5 text-xs font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">{{ __('Download') }}</a>
                    </div>
                </template>
            </div>
            <div class="mt-4 text-center"><button @click="reset()" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('Split another PDF') }}</button></div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function splitPdf(){return{file:null,mode:'all',from:1,to:null,busy:false,error:null,result:null,drag:false,
dropFile(e){const f=e.dataTransfer.files[0];if(f&&f.type==='application/pdf'){this.file=f;this.error=null}else this.error='{{ __('Please drop a PDF file.') }}'},
async run(){if(!this.file)return;this.busy=true;this.error=null;const fd=new FormData();fd.append('file',this.file);fd.append('mode',this.mode);if(this.mode==='range'){fd.append('from',this.from);if(this.to)fd.append('to',this.to)}
try{const r=await fetch('/api/tools/split-pdf',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:fd});const d=await r.json();if(!r.ok){this.error=d.error||d.message||'{{ __('Split failed.') }}';return}this.result=d}catch(e){this.error='{{ __('Network error.') }}'}finally{this.busy=false}},
reset(){this.file=null;this.result=null;this.error=null;this.mode='all';this.from=1;this.to=null},
fmt(b){if(!b)return'0 B';const u=['B','KB','MB','GB'];let s=b;for(const unit of u){if(s<1024)return Math.round(s*10)/10+' '+unit;s/=1024}return Math.round(s*10)/10+' TB'}}}
</script>
@endpush
