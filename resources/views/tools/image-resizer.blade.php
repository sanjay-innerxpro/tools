@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Image Resizer'))
@section('meta_description', __('Resize images to exact width and height or percentage while keeping quality and proportions.'))

@section('content')
<div x-data="imageResizer()" x-cloak class="max-w-4xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl shadow-lg shadow-cyan-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Image Resizer') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Resize images to exact dimensions or by percentage') }}</p>
    </div>

    <div x-show="!result" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8 mb-6">
        <div @dragover.prevent="drag=true" @dragleave.prevent="drag=false" @drop.prevent="drag=false;dropFile($event)"
             :class="drag?'border-blue-500 bg-blue-50 dark:bg-blue-900/20':'border-gray-300 dark:border-gray-700'"
             class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer" @click="$refs.fi.click()">
            <input type="file" x-ref="fi" @change="pickFile($event)" accept="image/*" class="hidden">
            <div x-show="!file" class="space-y-3">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-medium">{{ __('Drop your image here or click to browse') }}</p>
                <p class="text-sm text-gray-400">{{ __('JPG, PNG, WebP, BMP, GIF, TIFF — Max 50 MB') }}</p>
            </div>
            <div x-show="file" class="flex items-center justify-center gap-4">
                <img x-show="preview" :src="preview" class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="text-left"><p class="font-medium text-gray-900 dark:text-white" x-text="file?.name"></p><p class="text-sm text-gray-500" x-text="fmt(file?.size)"></p></div>
                <button @click.stop="clearFile()" class="ml-2 p-1 text-gray-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Resize mode') }}</label>
            <div class="flex gap-3">
                <button @click="mode='dimensions'" :class="mode==='dimensions'?'bg-cyan-600 text-white border-cyan-600':'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-cyan-400'" class="px-4 py-2 text-sm font-medium border rounded-lg transition-all">{{ __('Dimensions') }}</button>
                <button @click="mode='percentage'" :class="mode==='percentage'?'bg-cyan-600 text-white border-cyan-600':'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-cyan-400'" class="px-4 py-2 text-sm font-medium border rounded-lg transition-all">{{ __('Percentage') }}</button>
            </div>

            <div x-show="mode==='dimensions'" class="space-y-3" x-transition>
                <div class="flex gap-3 items-center">
                    <div><label class="text-xs text-gray-500 mb-1 block">{{ __('Width (px)') }}</label><input type="number" x-model.number="width" min="1" max="10000" placeholder="{{ __('Width') }}" class="w-28 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"></div>
                    <span class="text-gray-400 mt-5">×</span>
                    <div><label class="text-xs text-gray-500 mb-1 block">{{ __('Height (px)') }}</label><input type="number" x-model.number="height" min="1" max="10000" placeholder="{{ __('Height') }}" class="w-28 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"></div>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="keepAspect" class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-500">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Keep aspect ratio') }}</span>
                </label>
            </div>

            <div x-show="mode==='percentage'" class="space-y-2" x-transition>
                <div class="flex items-center justify-between">
                    <label class="text-sm text-gray-600 dark:text-gray-400">{{ __('Scale') }}</label>
                    <span class="text-sm font-semibold text-cyan-600" x-text="percent + '%'"></span>
                </div>
                <input type="range" x-model.number="percent" min="1" max="500" step="1" class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-cyan-500">
                <div class="flex justify-between text-xs text-gray-400"><span>1%</span><span>100%</span><span>500%</span></div>
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="run()" :disabled="!file||busy" class="px-8 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 disabled:from-gray-400 disabled:to-gray-400 text-white font-semibold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <template x-if="!busy"><span class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>{{ __('Resize') }}</span></template>
                <template x-if="busy"><span class="flex items-center gap-2"><svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>{{ __('Resizing...') }}</span></template>
            </button>
        </div>
    </div>

    <template x-if="error"><div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6"><p class="text-red-700 dark:text-red-300" x-text="error"></p></div></template>

    <template x-if="result">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 fade-in">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Resize Complete') }}</h2>
                <a :href="result.downloadUrl" :download="result.downloadName" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>{{ __('Download') }}
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"><p class="text-gray-500 dark:text-gray-400">{{ __('Original') }}</p><p class="font-medium text-gray-900 dark:text-white" x-text="fmt(result.originalSize)"></p></div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"><p class="text-gray-500 dark:text-gray-400">{{ __('Resized') }}</p><p class="font-medium text-gray-900 dark:text-white" x-text="fmt(result.resizedSize)"></p></div>
            </div>
            <div class="mt-4 text-center"><button @click="reset()" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('Resize another image') }}</button></div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function imageResizer(){return{file:null,preview:null,mode:'dimensions',width:null,height:null,percent:50,keepAspect:true,busy:false,error:null,result:null,drag:false,
dropFile(e){const f=e.dataTransfer.files[0];if(f&&f.type.startsWith('image/')){this.file=f;this.showPrev(f)}else this.error='{{ __('Please drop an image.') }}'},
pickFile(e){const f=e.target.files[0];if(f){this.file=f;this.showPrev(f);this.error=null}},
showPrev(f){const r=new FileReader();r.onload=e=>this.preview=e.target.result;r.readAsDataURL(f)},
clearFile(){this.file=null;this.preview=null},
async run(){if(!this.file)return;this.busy=true;this.error=null;const fd=new FormData();fd.append('file',this.file);fd.append('mode',this.mode);fd.append('keep_aspect',this.keepAspect?'1':'0');
if(this.mode==='dimensions'){if(this.width)fd.append('width',this.width);if(this.height)fd.append('height',this.height);if(!this.width&&!this.height){this.error='{{ __('Enter width or height.') }}';this.busy=false;return}}else{fd.append('percent',this.percent)}
try{const r=await fetch('/api/tools/image-resize',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:fd});const d=await r.json();if(!r.ok){this.error=d.error||d.message||'{{ __('Resize failed.') }}';return}this.result=d}catch(e){this.error='{{ __('Network error.') }}'}finally{this.busy=false}},
reset(){this.file=null;this.preview=null;this.result=null;this.error=null;this.width=null;this.height=null;this.percent=50},
fmt(b){if(!b)return'0 B';const u=['B','KB','MB','GB'];let s=b;for(const unit of u){if(s<1024)return Math.round(s*10)/10+' '+unit;s/=1024}return Math.round(s*10)/10+' TB'}}}
</script>
@endpush
