@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('QR Code Generator'))

@section('content')
<div x-data="qrGen()" x-cloak class="max-w-4xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-gray-800 to-gray-900 dark:from-gray-200 dark:to-white rounded-2xl shadow-lg mb-5">
            <svg class="w-8 h-8 text-white dark:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('QR Code Generator') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Generate QR codes instantly — runs entirely in your browser') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Input --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Content') }}</label>
            <textarea x-model="text" @input="generate()" rows="4" placeholder="{{ __('Enter URL, text, email, phone...') }}" class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-gray-500 focus:border-transparent resize-none"></textarea>
            <p class="text-xs text-gray-400 mt-1" x-text="text.length + ' {{ __('characters') }}'"></p>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">{{ __('Size (px)') }}</label>
                    <select x-model.number="size" @change="generate()" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg">
                        <option value="128">128×128</option>
                        <option value="256" selected>256×256</option>
                        <option value="512">512×512</option>
                        <option value="1024">1024×1024</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">{{ __('Error correction') }}</label>
                    <select x-model="ecl" @change="generate()" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg">
                        <option value="L">Low (7%)</option>
                        <option value="M" selected>Medium (15%)</option>
                        <option value="Q">Quartile (25%)</option>
                        <option value="H">High (30%)</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">{{ __('Foreground') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" x-model="fg" @input="generate()" class="w-8 h-8 rounded border-0 cursor-pointer">
                        <input type="text" x-model="fg" @input="generate()" class="flex-1 px-2 py-1.5 text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg font-mono">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">{{ __('Background') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" x-model="bg" @input="generate()" class="w-8 h-8 rounded border-0 cursor-pointer">
                        <input type="text" x-model="bg" @input="generate()" class="flex-1 px-2 py-1.5 text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg font-mono">
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 flex flex-col items-center justify-center">
            <div x-show="!text" class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <p class="text-sm text-gray-400">{{ __('Type something to generate a QR code') }}</p>
            </div>
            <div x-show="text" class="text-center">
                <canvas x-ref="canvas" class="mx-auto rounded-lg border border-gray-200 dark:border-gray-700" style="max-width:280px;max-height:280px;width:100%;height:auto"></canvas>
                <div class="mt-4 flex gap-2 justify-center">
                    <button @click="downloadPNG()" class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:opacity-90 transition-opacity">{{ __('Download PNG') }}</button>
                    <button @click="downloadSVG()" class="px-4 py-2 text-sm font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">{{ __('Download SVG') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
function qrGen(){return{text:'',size:256,ecl:'M',fg:'#000000',bg:'#ffffff',
generate(){if(!this.text)return;const ecMap={L:1,M:0,Q:3,H:2};const qr=qrcode(0,this.ecl);qr.addData(this.text);qr.make();
const c=this.$refs.canvas;if(!c)return;const ctx=c.getContext('2d');const mc=qr.getModuleCount();c.width=this.size;c.height=this.size;const cs=this.size/mc;
ctx.fillStyle=this.bg;ctx.fillRect(0,0,this.size,this.size);ctx.fillStyle=this.fg;
for(let r=0;r<mc;r++)for(let col=0;col<mc;col++)if(qr.isDark(r,col))ctx.fillRect(col*cs,r*cs,cs,cs)},
downloadPNG(){const c=this.$refs.canvas;const a=document.createElement('a');a.download='qrcode.png';a.href=c.toDataURL('image/png');a.click()},
downloadSVG(){const qr=qrcode(0,this.ecl);qr.addData(this.text);qr.make();const mc=qr.getModuleCount();const cs=this.size/mc;
let svg=`<svg xmlns="http://www.w3.org/2000/svg" width="${this.size}" height="${this.size}"><rect width="100%" height="100%" fill="${this.bg}"/>`;
for(let r=0;r<mc;r++)for(let c=0;c<mc;c++)if(qr.isDark(r,c))svg+=`<rect x="${c*cs}" y="${r*cs}" width="${cs}" height="${cs}" fill="${this.fg}"/>`;
svg+=`</svg>`;const b=new Blob([svg],{type:'image/svg+xml'});const a=document.createElement('a');a.download='qrcode.svg';a.href=URL.createObjectURL(b);a.click();URL.revokeObjectURL(a.href)}}}
</script>
@endpush
