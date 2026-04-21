@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('Stopwatch'))
@section('meta_description', __('Precise stopwatch with lap time tracking'))

@section('content')
<div x-data="stopwatchTool()" x-init="init()" x-cloak @keydown.space.window.prevent="toggle()">

    {{-- Hero --}}
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600"></div>
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 text-center text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-sm font-medium mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('Daily Tools') }}
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('Stopwatch') }}</h1>
            <p class="mt-3 text-blue-100 text-base sm:text-lg max-w-xl mx-auto">{{ __('Precise stopwatch with lap time tracking') }}</p>
        </div>
    </div>

    {{-- Tool --}}
    <div class="max-w-xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

        {{-- Main display --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm text-center">
            <div class="font-mono text-6xl sm:text-7xl font-bold text-gray-900 dark:text-white tracking-tight tabular-nums"
                 x-text="display"></div>
            <div class="mt-1 font-mono text-2xl text-blue-500 dark:text-blue-400 tabular-nums" x-text="displayMs"></div>

            {{-- Controls --}}
            <div class="mt-8 flex items-center justify-center gap-4">
                {{-- Start / Stop --}}
                <button @click="toggle()"
                        :class="running ? 'bg-red-500 hover:bg-red-600 shadow-red-500/25' : 'bg-blue-500 hover:bg-blue-600 shadow-blue-500/25'"
                        class="px-8 py-3 rounded-xl text-white font-bold text-base shadow-lg transition-all active:scale-95">
                    <span x-text="running ? '{{ __('Stop') }}' : '{{ __('Start') }}'"></span>
                </button>
                {{-- Lap --}}
                <button @click="lap()" :disabled="!running"
                        class="px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-base hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all active:scale-95">
                    {{ __('Lap') }}
                </button>
                {{-- Reset --}}
                <button @click="reset()" :disabled="running"
                        class="px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-base hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all active:scale-95">
                    {{ __('Reset') }}
                </button>
            </div>
            <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">{{ __('Press Space to start / stop') }}</p>
        </div>

        {{-- Laps --}}
        <template x-if="laps.length > 0">
            <div class="mt-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Laps') }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500" x-text="laps.length + ' {{ __('laps') }}'"></span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800 max-h-64 overflow-y-auto">
                    <template x-for="(lap, i) in [...laps].reverse()" :key="i">
                        <div class="flex items-center justify-between px-4 py-2.5"
                             :class="i === 0 && running ? 'bg-blue-50 dark:bg-blue-900/10' : ''">
                            <span class="text-sm text-gray-500 dark:text-gray-400"
                                  x-text="'{{ __('Lap') }} ' + (laps.length - i)"></span>
                            <div class="text-right">
                                <div class="font-mono text-sm font-semibold text-gray-900 dark:text-white" x-text="lap.total"></div>
                                <div class="font-mono text-xs text-gray-400 dark:text-gray-500" x-text="'+' + lap.split"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

    </div>
</div>
@endsection

@push('scripts')
<script>
function stopwatchTool() {
    return {
        running: false,
        elapsed: 0,
        lapStart: 0,
        startTime: null,
        laps: [],
        raf: null,
        display: '00:00:00',
        displayMs: '.000',
        init() { this.updateDisplay(0); },
        format(ms) {
            const h = Math.floor(ms / 3600000);
            const m = Math.floor((ms % 3600000) / 60000);
            const s = Math.floor((ms % 60000) / 1000);
            const msec = ms % 1000;
            return {
                hms: String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0'),
                ms: '.' + String(msec).padStart(3,'0')
            };
        },
        updateDisplay(ms) {
            const f = this.format(ms);
            this.display = f.hms;
            this.displayMs = f.ms;
        },
        tick() {
            if (!this.running) return;
            this.elapsed = performance.now() - this.startTime;
            this.updateDisplay(Math.floor(this.elapsed));
            this.raf = requestAnimationFrame(() => this.tick());
        },
        toggle() {
            if (this.running) {
                this.running = false;
                cancelAnimationFrame(this.raf);
                this.elapsed = performance.now() - this.startTime;
                this.updateDisplay(Math.floor(this.elapsed));
            } else {
                this.startTime = performance.now() - this.elapsed;
                this.running = true;
                this.raf = requestAnimationFrame(() => this.tick());
            }
        },
        lap() {
            if (!this.running) return;
            const total = Math.floor(performance.now() - this.startTime);
            const split = total - this.lapStart;
            this.lapStart = total;
            const tf = this.format(total);
            const sf = this.format(split);
            this.laps.push({ total: tf.hms + sf.ms, split: sf.hms + sf.ms });
        },
        reset() {
            if (this.running) return;
            this.elapsed = 0;
            this.lapStart = 0;
            this.laps = [];
            this.updateDisplay(0);
        }
    };
}
</script>
@endpush
