@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('Age Calculator'))
@section('meta_description', __('Calculate your exact age from your date of birth'))

@section('content')
<div x-data="ageCalc()" x-cloak>

    {{-- Hero --}}
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-500 to-pink-600"></div>
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 text-center text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-sm font-medium mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('Daily Tools') }}
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ __('Age Calculator') }}</h1>
            <p class="mt-3 text-rose-100 text-base sm:text-lg max-w-xl mx-auto">{{ __('Calculate your exact age from your date of birth') }}</p>
        </div>
    </div>

    {{-- Tool --}}
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Date of Birth') }}</label>
            <input type="date" x-model="dob" @change="calculate()"
                   :max="today"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors">

            <template x-if="error">
                <p class="mt-3 text-sm text-red-500" x-text="error"></p>
            </template>

            <template x-if="result">
                <div class="mt-6 space-y-4">
                    {{-- Main age blocks --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-rose-50 dark:bg-rose-900/20 rounded-2xl p-4 text-center border border-rose-100 dark:border-rose-800/40">
                            <div class="text-4xl font-extrabold text-rose-600 dark:text-rose-400" x-text="result.years"></div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide">{{ __('Years') }}</div>
                        </div>
                        <div class="bg-pink-50 dark:bg-pink-900/20 rounded-2xl p-4 text-center border border-pink-100 dark:border-pink-800/40">
                            <div class="text-4xl font-extrabold text-pink-600 dark:text-pink-400" x-text="result.months"></div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide">{{ __('Months') }}</div>
                        </div>
                        <div class="bg-fuchsia-50 dark:bg-fuchsia-900/20 rounded-2xl p-4 text-center border border-fuchsia-100 dark:border-fuchsia-800/40">
                            <div class="text-4xl font-extrabold text-fuchsia-600 dark:text-fuchsia-400" x-text="result.days"></div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide">{{ __('Days') }}</div>
                        </div>
                    </div>

                    {{-- Details table --}}
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden divide-y divide-gray-100 dark:divide-gray-800">
                        <div class="flex justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-800/50">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Days Lived') }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="result.totalDays.toLocaleString()"></span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-3">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Hours Lived') }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="result.totalHours.toLocaleString()"></span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-800/50">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Next Birthday') }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="result.nextBirthday"></span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-3">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Days Until Next Birthday') }}</span>
                            <span class="text-sm font-bold text-rose-600 dark:text-rose-400" x-text="result.daysUntilBirthday + ' {{ __('days') }}'"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Description --}}
        <p class="mt-6 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('Enter your date of birth to see your exact age and upcoming birthday.') }}
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ageCalc() {
    return {
        dob: '',
        today: new Date().toISOString().split('T')[0],
        result: null,
        error: '',
        calculate() {
            this.error = '';
            this.result = null;
            if (!this.dob) return;
            const birth = new Date(this.dob + 'T00:00:00');
            if (isNaN(birth.getTime())) { this.error = '{{ __('Invalid date.') }}'; return; }
            const now = new Date();
            if (birth > now) { this.error = '{{ __('Date of birth cannot be in the future.') }}'; return; }

            let years = now.getFullYear() - birth.getFullYear();
            let months = now.getMonth() - birth.getMonth();
            let days = now.getDate() - birth.getDate();

            if (days < 0) {
                months--;
                days += new Date(now.getFullYear(), now.getMonth(), 0).getDate();
            }
            if (months < 0) { years--; months += 12; }

            const totalDays = Math.floor((now - birth) / 86400000);
            const totalHours = Math.floor((now - birth) / 3600000);

            let nextBD = new Date(now.getFullYear(), birth.getMonth(), birth.getDate());
            if (nextBD <= now) nextBD.setFullYear(now.getFullYear() + 1);
            const daysUntilBirthday = Math.ceil((nextBD - now) / 86400000);
            const nextBirthday = nextBD.toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });

            this.result = { years, months, days, totalDays, totalHours, nextBirthday, daysUntilBirthday };
        }
    };
}
</script>
@endpush
