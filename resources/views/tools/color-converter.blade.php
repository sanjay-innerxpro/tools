@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Color Converter'))

@section('content')
<div x-data="colorConverter()" x-cloak class="max-w-2xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 rounded-2xl shadow-lg shadow-pink-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Color Converter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Convert colors between HEX, RGB, and HSL formats') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8">

        {{-- Color picker + hex input --}}
        <div class="flex items-center gap-6 mb-8">
            <label class="relative cursor-pointer flex-shrink-0 group">
                <input type="color" x-model="pickerVal" @input="fromPicker()" class="sr-only">
                <div :style="'background:' + hex"
                     class="w-20 h-20 rounded-2xl shadow-lg border-4 border-white dark:border-gray-700 cursor-pointer transition-transform group-hover:scale-105"></div>
                <span class="absolute -bottom-1.5 -right-1.5 w-7 h-7 bg-pink-500 rounded-full flex items-center justify-center shadow-md">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z"/>
                    </svg>
                </span>
            </label>
            <div class="flex-1">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ __('Click the swatch to pick a color, or type below.') }}</p>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="hexInput"
                           @input="fromHexInput()"
                           @blur="normalizeHex()"
                           placeholder="#3b82f6"
                           maxlength="7"
                           spellcheck="false"
                           class="w-36 font-mono text-sm uppercase bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400 transition">
                    <span x-show="hexError" class="text-xs text-red-500 font-medium">{{ __('Invalid HEX') }}</span>
                </div>
            </div>
        </div>

        {{-- Format outputs --}}
        <div class="space-y-3 mb-8">
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3.5 border border-gray-200 dark:border-gray-700">
                <span class="w-8 text-xs font-bold text-gray-400 uppercase">HEX</span>
                <span class="flex-1 font-mono text-gray-900 dark:text-white" x-text="hex"></span>
                <button @click="copyVal(hex, 'hex')"
                        :class="copied === 'hex' ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-600' : 'text-gray-400 hover:text-pink-500 hover:bg-pink-50 dark:hover:bg-pink-900/20'"
                        class="text-xs px-3 py-1 rounded-lg font-medium transition-all">
                    <span x-show="copied !== 'hex'">{{ __('Copy') }}</span>
                    <span x-show="copied === 'hex'">{{ __('Copied!') }}</span>
                </button>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3.5 border border-gray-200 dark:border-gray-700">
                <span class="w-8 text-xs font-bold text-gray-400 uppercase">RGB</span>
                <span class="flex-1 font-mono text-gray-900 dark:text-white" x-text="rgbStr"></span>
                <button @click="copyVal(rgbStr, 'rgb')"
                        :class="copied === 'rgb' ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-600' : 'text-gray-400 hover:text-pink-500 hover:bg-pink-50 dark:hover:bg-pink-900/20'"
                        class="text-xs px-3 py-1 rounded-lg font-medium transition-all">
                    <span x-show="copied !== 'rgb'">{{ __('Copy') }}</span>
                    <span x-show="copied === 'rgb'">{{ __('Copied!') }}</span>
                </button>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3.5 border border-gray-200 dark:border-gray-700">
                <span class="w-8 text-xs font-bold text-gray-400 uppercase">HSL</span>
                <span class="flex-1 font-mono text-gray-900 dark:text-white" x-text="hslStr"></span>
                <button @click="copyVal(hslStr, 'hsl')"
                        :class="copied === 'hsl' ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-600' : 'text-gray-400 hover:text-pink-500 hover:bg-pink-50 dark:hover:bg-pink-900/20'"
                        class="text-xs px-3 py-1 rounded-lg font-medium transition-all">
                    <span x-show="copied !== 'hsl'">{{ __('Copy') }}</span>
                    <span x-show="copied === 'hsl'">{{ __('Copied!') }}</span>
                </button>
            </div>
        </div>

        {{-- Shades row --}}
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ __('Shades') }}</p>
            <div class="flex gap-1.5">
                <template x-for="shade in shades" :key="shade">
                    <div :style="'background:' + shade"
                         @click="setFromHex(shade)"
                         :title="shade"
                         class="flex-1 h-10 rounded-lg cursor-pointer hover:scale-110 transition-transform shadow-sm"></div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function colorConverter() {
    return {
        hex: '#3b82f6',
        hexInput: '#3b82f6',
        pickerVal: '#3b82f6',
        rgb: {r:59, g:130, b:246},
        hsl: {h:217, s:91, l:60},
        hexError: false,
        copied: null,
        get rgbStr() { return `rgb(${this.rgb.r}, ${this.rgb.g}, ${this.rgb.b})`; },
        get hslStr() { return `hsl(${this.hsl.h}, ${this.hsl.s}%, ${this.hsl.l}%)`; },
        get shades() {
            const {r, g, b} = this.rgb;
            return [0.85,0.65,0.45,0.25,0.1, 0, -0.1,-0.25,-0.45,-0.65].map(t => {
                const f = t >= 0 ? 255 : 0, m = Math.abs(t);
                const blend = x => Math.min(255, Math.max(0, Math.round(t >= 0 ? x + (f - x)*m : x*(1-m))));
                return `#${blend(r).toString(16).padStart(2,'0')}${blend(g).toString(16).padStart(2,'0')}${blend(b).toString(16).padStart(2,'0')}`;
            });
        },
        fromPicker() { this.setFromHex(this.pickerVal); },
        fromHexInput() {
            let v = this.hexInput.trim();
            if (!v.startsWith('#')) v = '#' + v;
            if (/^#[0-9a-fA-F]{6}$/.test(v)) { this.hexError = false; this.setFromHex(v); }
            else if (/^#[0-9a-fA-F]{3}$/.test(v)) { this.hexError = false; this.setFromHex('#'+v[1]+v[1]+v[2]+v[2]+v[3]+v[3]); }
            else { this.hexError = !!this.hexInput; }
        },
        normalizeHex() {
            let v = this.hexInput.trim();
            if (!v.startsWith('#')) v = '#' + v;
            if (/^#[0-9a-fA-F]{3}$/.test(v)) { this.hexInput = '#'+v[1]+v[1]+v[2]+v[2]+v[3]+v[3]; this.fromHexInput(); }
        },
        setFromHex(h) {
            h = h.toLowerCase();
            this.hex = h; this.hexInput = h; this.pickerVal = h; this.hexError = false;
            const r = parseInt(h.slice(1,3),16), g = parseInt(h.slice(3,5),16), b = parseInt(h.slice(5,7),16);
            this.rgb = {r,g,b};
            this.hsl = this.toHsl(r,g,b);
        },
        toHsl(r,g,b) {
            r/=255; g/=255; b/=255;
            const max=Math.max(r,g,b), min=Math.min(r,g,b);
            let h,s, l=(max+min)/2;
            if (max===min) { h=s=0; }
            else {
                const d=max-min;
                s = l>0.5 ? d/(2-max-min) : d/(max+min);
                switch(max) {
                    case r: h=((g-b)/d+(g<b?6:0))/6; break;
                    case g: h=((b-r)/d+2)/6; break;
                    default: h=((r-g)/d+4)/6;
                }
            }
            return {h:Math.round(h*360), s:Math.round(s*100), l:Math.round(l*100)};
        },
        copyVal(val, key) {
            navigator.clipboard.writeText(val).then(() => {
                this.copied = key;
                setTimeout(() => this.copied = null, 2000);
            });
        }
    }
}
</script>
@endsection
