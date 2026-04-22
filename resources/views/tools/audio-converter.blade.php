@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Audio Converter'))
@section('meta_description', __('Convert audio between MP3, WAV, AAC, FLAC, and other formats quickly with this free tool.'))

@section('content')
<div x-data="audioConverter()" x-cloak class="max-w-4xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-2xl shadow-lg shadow-violet-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Audio Converter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Convert audio files between popular formats') }}</p>
    </div>

    <div x-show="!result" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8 mb-6">
        <div @dragover.prevent="drag=true" @dragleave.prevent="drag=false" @drop.prevent="drag=false;dropFile($event)"
             :class="drag?'border-blue-500 bg-blue-50 dark:bg-blue-900/20':'border-gray-300 dark:border-gray-700'"
             class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer" @click="$refs.fi.click()">
            <input type="file" x-ref="fi" @change="pickFile($event)" accept="audio/*,.ogg,.flac,.m4a,.opus,.wma" class="hidden">
            <div x-show="!file" class="space-y-3">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-medium">{{ __('Drop your audio file here or click to browse') }}</p>
                <p class="text-sm text-gray-400">{{ __('MP3, WAV, OGG, AAC, FLAC, M4A — Max 100 MB') }}</p>
            </div>
            <div x-show="file" class="flex items-center justify-center gap-4">
                <div class="w-12 h-12 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                </div>
                <div class="text-left"><p class="font-medium text-gray-900 dark:text-white" x-text="file?.name"></p><p class="text-sm text-gray-500" x-text="fmt(file?.size)"></p></div>
                <button @click.stop="file=null" class="ml-2 p-1 text-gray-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Output format') }}</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="f in formats" :key="f">
                    <button @click="format=f" :class="format===f?'bg-violet-600 text-white border-violet-600':'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-violet-400'" class="px-4 py-2 text-sm font-medium border rounded-lg transition-all uppercase" x-text="f"></button>
                </template>
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="run()" :disabled="!file||busy" class="px-8 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 disabled:from-gray-400 disabled:to-gray-400 text-white font-semibold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <template x-if="!busy"><span class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>{{ __('Convert Audio') }}</span></template>
                <template x-if="busy"><span class="flex items-center gap-2"><svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>{{ __('Converting...') }}</span></template>
            </button>
        </div>
    </div>

    <template x-if="error"><div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6"><p class="text-red-700 dark:text-red-300" x-text="error"></p></div></template>

    <template x-if="result">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 fade-in">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Conversion Complete') }}</h2>
                <a :href="result.downloadUrl" :download="result.downloadName" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>{{ __('Download') }}
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"><p class="text-gray-500 dark:text-gray-400">{{ __('Original') }}</p><p class="font-medium text-gray-900 dark:text-white" x-text="fmt(result.originalSize)"></p></div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"><p class="text-gray-500 dark:text-gray-400">{{ __('Converted') }}</p><p class="font-medium text-gray-900 dark:text-white" x-text="fmt(result.convertedSize)"></p></div>
            </div>
            <div class="mt-4 text-center"><button @click="reset()" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('Convert another audio file') }}</button></div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function audioConverter(){return{file:null,format:'mp3',formats:['mp3','wav','ogg','aac','flac','m4a'],busy:false,error:null,result:null,drag:false,
dropFile(e){const f=e.dataTransfer.files[0];if(f&&(f.type.startsWith('audio/')||/\.(ogg|flac|m4a|opus|wma|aac)$/i.test(f.name))){this.file=f;this.error=null}else this.error='{{ __("Please drop an audio file.") }}'},
pickFile(e){const f=e.target.files[0];if(f){this.file=f;this.error=null}},
async run(){if(!this.file)return;this.busy=true;this.error=null;const fd=new FormData();fd.append('file',this.file);fd.append('format',this.format);
try{const r=await fetch('/api/tools/audio-convert',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:fd});const d=await r.json();if(!r.ok){this.error=d.error||d.message||'{{ __("Conversion failed.") }}';return}this.result=d}catch(e){this.error='{{ __("Network error. Please try again.") }}'}finally{this.busy=false}},
reset(){this.file=null;this.result=null;this.error=null},
fmt(b){if(!b)return'0 B';const u=['B','KB','MB','GB'];let s=b;for(const unit of u){if(s<1024)return Math.round(s*10)/10+' '+unit;s/=1024}return Math.round(s*10)/10+' TB'}}}
</script>
@endpush
