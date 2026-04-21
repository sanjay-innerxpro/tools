@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Timestamp Converter'))

@section('content')
<div x-data="timestampTool()" x-cloak class="max-w-3xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-sky-400 to-cyan-500 rounded-2xl shadow-lg shadow-sky-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Timestamp Converter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Convert between Unix timestamps and human-readable dates') }}</p>
    </div>

    {{-- Live clock --}}
    <div class="bg-gradient-to-r from-sky-50 to-cyan-50 dark:from-sky-950/40 dark:to-cyan-950/40 border border-sky-200 dark:border-sky-800 rounded-2xl p-5 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-xs font-bold text-sky-500 uppercase tracking-widest mb-1">{{ __('Current Unix Timestamp') }}</p>
                <span class="font-mono text-3xl font-bold text-sky-700 dark:text-sky-300 tabular-nums" x-text="currentTs"></span>
            </div>
            <button @click="copy(String(currentTs), 'now')"
                    class="px-4 py-2 text-sm font-medium rounded-xl border transition-all"
                    :class="copiedKey==='now' ? 'bg-sky-500 text-white border-sky-500' : 'bg-white dark:bg-gray-800 text-sky-600 dark:text-sky-400 border-sky-200 dark:border-sky-700 hover:bg-sky-50 dark:hover:bg-sky-900/30'">
                <span x-show="copiedKey!=='now'">{{ __('Copy') }}</span>
                <span x-show="copiedKey==='now'">{{ __('Copied!') }}</span>
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex bg-gray-100 dark:bg-gray-800 rounded-xl p-1 mb-6">
        <button @click="tab='to-date'"
                :class="tab==='to-date' ? 'bg-white dark:bg-gray-900 shadow text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                class="flex-1 py-2.5 text-sm font-medium rounded-lg transition-all">{{ __('Unix → Date') }}</button>
        <button @click="tab='to-unix'"
                :class="tab==='to-unix' ? 'bg-white dark:bg-gray-900 shadow text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                class="flex-1 py-2.5 text-sm font-medium rounded-lg transition-all">{{ __('Date → Unix') }}</button>
    </div>

    {{-- Unix → Date --}}
    <div x-show="tab==='to-date'" class="space-y-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-3">{{ __('Unix Timestamp') }}</label>
            <div class="flex gap-3">
                <input type="number" x-model="unixInput" @input="convertUnix()"
                       placeholder="{{ __('Enter Unix timestamp...') }}"
                       class="flex-1 font-mono text-base bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-sky-400 transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"/>
                <button @click="setNow()"
                        class="px-5 py-3 bg-sky-500 hover:bg-sky-600 text-white text-sm font-semibold rounded-xl transition-colors flex-shrink-0">
                    {{ __('Now') }}
                </button>
            </div>
        </div>
        <div x-show="tsError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-5 py-3 text-sm text-red-600 dark:text-red-400" x-text="tsError"></div>
        <div x-show="unixResult && !tsError" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="row in (unixResult||[])" :key="row.label">
                    <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                        <div class="w-28 flex-shrink-0">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider" x-text="row.label"></span>
                        </div>
                        <p class="flex-1 font-mono text-sm text-gray-900 dark:text-white break-all" x-text="row.value"></p>
                        <button @click="copy(row.value, row.label)"
                                class="flex-shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                                :class="copiedKey===row.label ? 'bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 opacity-100' : 'text-gray-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/20'">
                            <span x-show="copiedKey!==row.label">{{ __('Copy') }}</span>
                            <span x-show="copiedKey===row.label">{{ __('Copied!') }}</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Date → Unix --}}
    <div x-show="tab==='to-unix'" class="space-y-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-3">{{ __('Date & Time') }}</label>
            <input type="datetime-local" x-model="dateInput" @input="convertDate()"
                   class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-400 transition"/>
        </div>
        <div x-show="dateResult" class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="row in (dateResult||[])" :key="row.label">
                    <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                        <div class="w-36 flex-shrink-0">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider" x-text="row.label"></span>
                        </div>
                        <p class="flex-1 font-mono text-sm text-gray-900 dark:text-white break-all" x-text="row.value"></p>
                        <button @click="copy(row.value, row.label)"
                                class="flex-shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                                :class="copiedKey===row.label ? 'bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 opacity-100' : 'text-gray-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/20'">
                            <span x-show="copiedKey!==row.label">{{ __('Copy') }}</span>
                            <span x-show="copiedKey===row.label">{{ __('Copied!') }}</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>

<script>
function timestampTool() {
    return {
        tab: 'to-date',
        unixInput: '',
        dateInput: '',
        unixResult: null,
        dateResult: null,
        tsError: '',
        currentTs: Math.floor(Date.now() / 1000),
        copiedKey: null,

        init() {
            this.setNow();
            const d = new Date();
            const p = n => String(n).padStart(2, '0');
            this.dateInput = `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
            this.convertDate();
            setInterval(() => { this.currentTs = Math.floor(Date.now() / 1000); }, 1000);
        },

        setNow() {
            this.unixInput = String(Math.floor(Date.now() / 1000));
            this.convertUnix();
        },

        convertUnix() {
            const v = this.unixInput.trim();
            if (!v) { this.unixResult = null; this.tsError = ''; return; }
            const ts = parseInt(v, 10);
            if (isNaN(ts)) { this.tsError = '{{ __("Invalid timestamp.") }}'; this.unixResult = null; return; }
            this.tsError = '';
            const d = new Date(ts * 1000);
            const diff = this.currentTs - ts;
            const abs = Math.abs(diff);
            const suf = diff >= 0 ? 'ago' : 'from now';
            let rel;
            if (abs < 60)       rel = `${abs}s ${suf}`;
            else if (abs < 3600)  rel = `${Math.floor(abs/60)}m ${suf}`;
            else if (abs < 86400) rel = `${Math.floor(abs/3600)}h ${suf}`;
            else                  rel = `${Math.floor(abs/86400)}d ${suf}`;
            this.unixResult = [
                { label: 'UTC',                          value: d.toUTCString() },
                { label: 'ISO 8601',                     value: d.toISOString() },
                { label: '{{ __("Local") }}',            value: d.toLocaleString() },
                { label: '{{ __("Relative") }}',         value: rel },
            ];
        },

        convertDate() {
            if (!this.dateInput) { this.dateResult = null; return; }
            const d = new Date(this.dateInput);
            if (isNaN(d.getTime())) { this.dateResult = null; return; }
            this.dateResult = [
                { label: '{{ __("Unix (seconds)") }}',      value: String(Math.floor(d.getTime() / 1000)) },
                { label: '{{ __("Unix (milliseconds)") }}', value: String(d.getTime()) },
                { label: 'ISO 8601',                         value: d.toISOString() },
                { label: 'UTC',                              value: d.toUTCString() },
            ];
        },

        copy(value, key) {
            navigator.clipboard.writeText(String(value)).then(() => {
                this.copiedKey = key;
                setTimeout(() => this.copiedKey = null, 2000);
            });
        }
    };
}
</script>
@endsection
