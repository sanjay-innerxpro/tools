@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('Percentage Calculator'))
@section('meta_description', __('Quick percentage calculations for everyday use'))

@section('content')
<div x-data="percentCalc()" x-cloak>

    {{-- Hero --}}
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-orange-500"></div>
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 text-center text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-sm font-medium mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                {{ __('Daily Tools') }}
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('Percentage Calculator') }}</h1>
            <p class="mt-3 text-amber-100 text-base sm:text-lg max-w-xl mx-auto">{{ __('Quick percentage calculations for everyday use') }}</p>
        </div>
    </div>

    {{-- Tool --}}
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 sm:py-10 space-y-5">

        {{-- Tab 1: X% of Y --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-4">{{ __('What is X% of Y?') }}</h2>
            <div class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Percentage (%)') }}</label>
                    <input type="number" x-model.number="p1.pct" @input="calcP1()" placeholder="e.g. 15"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm">
                </div>
                <span class="text-gray-400 font-semibold mt-4 sm:mt-5">{{ __('of') }}</span>
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Number') }}</label>
                    <input type="number" x-model.number="p1.num" @input="calcP1()" placeholder="e.g. 200"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm">
                </div>
                <span class="text-gray-400 font-semibold mt-4 sm:mt-5">=</span>
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Result') }}</label>
                    <div class="w-full px-3 py-2.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 text-amber-700 dark:text-amber-300 font-bold text-sm" x-text="p1.result !== null ? p1.result : '—'"></div>
                </div>
            </div>
        </div>

        {{-- Tab 2: X is what % of Y --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-orange-600 dark:text-orange-400 uppercase tracking-wide mb-4">{{ __('X is what % of Y?') }}</h2>
            <div class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Value (X)') }}</label>
                    <input type="number" x-model.number="p2.x" @input="calcP2()" placeholder="e.g. 30"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm">
                </div>
                <span class="text-gray-400 font-semibold mt-4 sm:mt-5">{{ __('of') }}</span>
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Total (Y)') }}</label>
                    <input type="number" x-model.number="p2.y" @input="calcP2()" placeholder="e.g. 200"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm">
                </div>
                <span class="text-gray-400 font-semibold mt-4 sm:mt-5">=</span>
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Percentage') }}</label>
                    <div class="w-full px-3 py-2.5 rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800/40 text-orange-700 dark:text-orange-300 font-bold text-sm" x-text="p2.result !== null ? p2.result + '%' : '—'"></div>
                </div>
            </div>
        </div>

        {{-- Tab 3: Percentage change --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide mb-4">{{ __('Percentage Change') }}</h2>
            <div class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('From') }}</label>
                    <input type="number" x-model.number="p3.from" @input="calcP3()" placeholder="e.g. 100"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 text-sm">
                </div>
                <span class="text-gray-400 font-semibold mt-4 sm:mt-5">→</span>
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('To') }}</label>
                    <input type="number" x-model.number="p3.to" @input="calcP3()" placeholder="e.g. 125"
                           class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 text-sm">
                </div>
                <span class="text-gray-400 font-semibold mt-4 sm:mt-5">=</span>
                <div class="flex-1 w-full">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Change') }}</label>
                    <div class="w-full px-3 py-2.5 rounded-xl border font-bold text-sm"
                         :class="p3.positive ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800/40 text-green-700 dark:text-green-300' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40 text-red-700 dark:text-red-300'"
                         x-text="p3.result !== null ? p3.result : '—'"></div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function percentCalc() {
    return {
        p1: { pct: null, num: null, result: null },
        p2: { x: null, y: null, result: null },
        p3: { from: null, to: null, result: null, positive: true },
        calcP1() {
            if (this.p1.pct !== null && this.p1.num !== null && this.p1.num !== '' && this.p1.pct !== '') {
                this.p1.result = +((this.p1.pct / 100) * this.p1.num).toFixed(6).replace(/\.?0+$/, '');
            } else { this.p1.result = null; }
        },
        calcP2() {
            if (this.p2.x !== null && this.p2.y !== null && this.p2.y !== 0 && this.p2.y !== '') {
                this.p2.result = +((this.p2.x / this.p2.y) * 100).toFixed(4).replace(/\.?0+$/, '');
            } else { this.p2.result = null; }
        },
        calcP3() {
            if (this.p3.from !== null && this.p3.to !== null && this.p3.from !== 0 && this.p3.from !== '') {
                const change = ((this.p3.to - this.p3.from) / Math.abs(this.p3.from)) * 100;
                this.p3.positive = change >= 0;
                this.p3.result = (change >= 0 ? '+' : '') + change.toFixed(2).replace(/\.?0+$/, '') + '%';
            } else { this.p3.result = null; }
        }
    };
}
</script>
@endpush
