@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Hash Generator'))

@section('content')
<div x-data="hashGen()" x-cloak class="max-w-3xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-slate-600 to-gray-800 rounded-2xl shadow-lg shadow-gray-900/30 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Hash Generator') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Generate SHA checksums for any text instantly') }}</p>
    </div>

    {{-- Input --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Input') }}</h2>
            <button @click="clearAll()" class="text-xs text-gray-400 hover:text-red-500 transition-colors">{{ __('Clear') }}</button>
        </div>
        <textarea x-model="input" @input="computeHashes()"
                  placeholder="{{ __('Enter text to hash...') }}"
                  spellcheck="false"
                  class="w-full min-h-[9rem] font-mono text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none focus:ring-2 focus:ring-gray-500 transition"></textarea>
    </div>

    {{-- Hash results --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Hashes') }}</h2>
            <div x-show="busy" class="flex items-center gap-1.5 text-xs text-gray-400">
                <svg class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
                {{ __('Computing...') }}
            </div>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="h in hashes" :key="h.algo">
                <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 pt-0.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-xs font-mono font-bold text-slate-600 dark:text-slate-300 min-w-[4.5rem] justify-center" x-text="h.label"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p x-show="!h.value && !busy" class="text-sm text-gray-400 italic">{{ __('—') }}</p>
                            <p x-show="busy && !h.value" class="text-sm text-gray-400 animate-pulse">{{ __('Computing...') }}</p>
                            <p x-show="h.value" class="font-mono text-xs sm:text-sm text-gray-900 dark:text-white break-all leading-relaxed" x-text="h.value"></p>
                        </div>
                        <button @click="copyHash(h)" x-show="h.value"
                                class="flex-shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg transition-all"
                                :class="h.copied?'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200':'text-gray-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                            <span x-show="!h.copied">{{ __('Copy') }}</span>
                            <span x-show="h.copied">{{ __('Copied!') }}</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function hashGen() {
    return {
        input: '',
        busy: false,
        _timer: null,
        hashes: [
            {algo:'SHA-1',   label:'SHA-1',   value:'', copied:false},
            {algo:'SHA-256', label:'SHA-256', value:'', copied:false},
            {algo:'SHA-384', label:'SHA-384', value:'', copied:false},
            {algo:'SHA-512', label:'SHA-512', value:'', copied:false},
        ],
        clearAll() {
            this.input='';
            this.hashes.forEach(h=>{h.value='';h.copied=false;});
            this.busy=false;
        },
        computeHashes() {
            clearTimeout(this._timer);
            if (!this.input) { this.hashes.forEach(h=>h.value=''); this.busy=false; return; }
            this.busy=true;
            this._timer = setTimeout(async () => {
                const enc = new TextEncoder();
                const data = enc.encode(this.input);
                for (const h of this.hashes) {
                    const buf = await crypto.subtle.digest(h.algo, data);
                    h.value = Array.from(new Uint8Array(buf)).map(b=>b.toString(16).padStart(2,'0')).join('');
                    h.copied = false;
                }
                this.busy=false;
            }, 120);
        },
        copyHash(h) {
            navigator.clipboard.writeText(h.value).then(() => {
                h.copied=true;
                setTimeout(()=>h.copied=false, 2000);
            });
        }
    }
}
</script>
@endsection
