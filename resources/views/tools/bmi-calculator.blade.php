@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('BMI Calculator'))
@section('meta_description', __('Calculate your Body Mass Index instantly'))

@section('content')
<div x-data="bmiCalc()" x-cloak>

    {{-- Hero --}}
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-teal-500 to-emerald-600"></div>
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 text-center text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-sm font-medium mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ __('Daily Tools') }}
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('BMI Calculator') }}</h1>
            <p class="mt-3 text-teal-100 text-base sm:text-lg max-w-xl mx-auto">{{ __('Calculate your Body Mass Index instantly') }}</p>
        </div>
    </div>

    {{-- Tool --}}
    <div class="max-w-xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">

            {{-- Unit toggle --}}
            <div class="flex bg-gray-100 dark:bg-gray-800 rounded-xl p-1 mb-6">
                <button @click="unit='metric'; reset()"
                        :class="unit==='metric' ? 'bg-white dark:bg-gray-700 text-teal-600 dark:text-teal-400 shadow-sm font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="flex-1 py-2 text-sm rounded-lg transition-all">{{ __('Metric (kg / cm)') }}</button>
                <button @click="unit='imperial'; reset()"
                        :class="unit==='imperial' ? 'bg-white dark:bg-gray-700 text-teal-600 dark:text-teal-400 shadow-sm font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="flex-1 py-2 text-sm rounded-lg transition-all">{{ __('Imperial (lbs / ft)') }}</button>
            </div>

            {{-- Metric inputs --}}
            <template x-if="unit==='metric'">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Height (cm)') }}</label>
                        <input type="number" x-model.number="metric.height" @input="calculate()" placeholder="175" min="1" max="300"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Weight (kg)') }}</label>
                        <input type="number" x-model.number="metric.weight" @input="calculate()" placeholder="70" min="1" max="600"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                    </div>
                </div>
            </template>

            {{-- Imperial inputs --}}
            <template x-if="unit==='imperial'">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Height (ft)') }}</label>
                        <input type="number" x-model.number="imperial.ft" @input="calculate()" placeholder="5" min="1" max="9"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Height (in)') }}</label>
                        <input type="number" x-model.number="imperial.inch" @input="calculate()" placeholder="9" min="0" max="11"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Weight (lbs)') }}</label>
                        <input type="number" x-model.number="imperial.lbs" @input="calculate()" placeholder="154" min="1" max="1300"
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                    </div>
                </div>
            </template>

            {{-- Result --}}
            <template x-if="result">
                <div class="mt-6">
                    <div class="rounded-2xl p-5 text-center border-2 transition-all"
                         :class="{
                             'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800/40': result.category === 'underweight',
                             'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800/40': result.category === 'normal',
                             'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800/40': result.category === 'overweight',
                             'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40': result.category === 'obese',
                         }">
                        <div class="text-5xl font-extrabold"
                             :class="{
                                 'text-blue-600 dark:text-blue-400': result.category === 'underweight',
                                 'text-green-600 dark:text-green-400': result.category === 'normal',
                                 'text-amber-600 dark:text-amber-400': result.category === 'overweight',
                                 'text-red-600 dark:text-red-400': result.category === 'obese',
                             }"
                             x-text="result.bmi"></div>
                        <div class="text-sm font-semibold mt-1"
                             :class="{
                                 'text-blue-600 dark:text-blue-400': result.category === 'underweight',
                                 'text-green-600 dark:text-green-400': result.category === 'normal',
                                 'text-amber-600 dark:text-amber-400': result.category === 'overweight',
                                 'text-red-600 dark:text-red-400': result.category === 'obese',
                             }"
                             x-text="result.label"></div>
                    </div>

                    {{-- BMI scale --}}
                    <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden divide-y divide-gray-100 dark:divide-gray-800">
                        <div class="flex justify-between items-center px-4 py-2.5 bg-blue-50 dark:bg-blue-900/10">
                            <span class="text-sm text-blue-600 dark:text-blue-400 font-medium">{{ __('Underweight') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Below') }} 18.5</span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-2.5 bg-green-50 dark:bg-green-900/10">
                            <span class="text-sm text-green-600 dark:text-green-400 font-medium">{{ __('Normal weight') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">18.5 – 24.9</span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-2.5 bg-amber-50 dark:bg-amber-900/10">
                            <span class="text-sm text-amber-600 dark:text-amber-400 font-medium">{{ __('Overweight') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">25.0 – 29.9</span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-2.5 bg-red-50 dark:bg-red-900/10">
                            <span class="text-sm text-red-600 dark:text-red-400 font-medium">{{ __('Obese') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('30 and above') }}</span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <p class="mt-6 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('BMI is a screening tool. Consult a healthcare professional for medical advice.') }}
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bmiCalc() {
    return {
        unit: 'metric',
        metric: { height: null, weight: null },
        imperial: { ft: null, inch: null, lbs: null },
        result: null,
        reset() { this.metric = { height: null, weight: null }; this.imperial = { ft: null, inch: null, lbs: null }; this.result = null; },
        calculate() {
            this.result = null;
            let heightM, weightKg;
            if (this.unit === 'metric') {
                if (!this.metric.height || !this.metric.weight) return;
                heightM = this.metric.height / 100;
                weightKg = this.metric.weight;
            } else {
                if (!this.imperial.lbs || (!this.imperial.ft && !this.imperial.inch)) return;
                const totalInches = (this.imperial.ft || 0) * 12 + (this.imperial.inch || 0);
                if (!totalInches) return;
                heightM = totalInches * 0.0254;
                weightKg = this.imperial.lbs * 0.453592;
            }
            if (heightM <= 0 || weightKg <= 0) return;
            const bmi = weightKg / (heightM * heightM);
            let category, label;
            if (bmi < 18.5) { category = 'underweight'; label = '{{ __('Underweight') }}'; }
            else if (bmi < 25) { category = 'normal'; label = '{{ __('Normal weight') }}'; }
            else if (bmi < 30) { category = 'overweight'; label = '{{ __('Overweight') }}'; }
            else { category = 'obese'; label = '{{ __('Obese') }}'; }
            this.result = { bmi: bmi.toFixed(1), category, label };
        }
    };
}
</script>
@endpush
