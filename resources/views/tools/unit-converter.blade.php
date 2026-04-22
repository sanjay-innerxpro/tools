@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Unit Converter'))
@section('meta_description', __('Convert length, weight, temperature, and other units accurately with this fast free unit converter.'))

@section('content')
<div x-data="unitConverter()" x-cloak class="max-w-3xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl shadow-lg shadow-sky-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Unit Converter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Convert between units of length, weight, temperature, and more') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-8">

        {{-- Category chips --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-3">{{ __('Category') }}</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="(cat, key) in cats" :key="key">
                    <button @click="setCategory(key)"
                            :class="category === key
                                ? 'bg-sky-600 text-white border-sky-600 shadow-sm'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-sky-400 dark:hover:border-sky-500'"
                            class="px-3.5 py-1.5 text-sm font-medium border rounded-lg transition-all"
                            x-text="cat.label"></button>
                </template>
            </div>
        </div>

        {{-- Converter inputs --}}
        <div class="grid grid-cols-1 sm:grid-cols-[1fr_3rem_1fr] items-end gap-4">

            {{-- From --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('From') }}</label>
                <input type="number" x-model="fromVal" @input="convert()"
                       placeholder="0"
                       class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-3 text-gray-900 dark:text-white text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-sky-400 mb-3 transition">
                <select x-model="fromUnit" @change="convert()"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-3 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-sky-400 transition">
                    <template x-for="u in cats[category].units" :key="u">
                        <option :value="u" x-text="u"></option>
                    </template>
                </select>
            </div>

            {{-- Swap button --}}
            <div class="flex justify-center pb-3">
                <button @click="swapUnits()"
                        class="p-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-sky-100 dark:hover:bg-sky-900/30 border border-gray-200 dark:border-gray-700 hover:border-sky-300 rounded-xl text-gray-400 hover:text-sky-600 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </button>
            </div>

            {{-- To --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('To') }}</label>
                <div class="w-full bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 rounded-xl px-4 py-3 text-sky-700 dark:text-sky-300 text-lg font-bold mb-3 min-h-[3.25rem] flex items-center"
                     x-text="result || '—'"></div>
                <select x-model="toUnit" @change="convert()"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-3 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-sky-400 transition">
                    <template x-for="u in cats[category].units" :key="u">
                        <option :value="u" x-text="u"></option>
                    </template>
                </select>
            </div>
        </div>

        {{-- Formula display --}}
        <p x-show="formula" x-transition class="mt-5 text-center text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 rounded-xl py-2.5 px-4 italic" x-text="formula"></p>
    </div>
</div>

<script>
function unitConverter() {
    const cats = {
        length: {
            label: '{{ __('Length') }}',
            units: ['meter','kilometer','centimeter','millimeter','mile','yard','foot','inch'],
            factors: {meter:1,kilometer:1000,centimeter:0.01,millimeter:0.001,mile:1609.344,yard:0.9144,foot:0.3048,inch:0.0254}
        },
        weight: {
            label: '{{ __('Weight') }}',
            units: ['kilogram','gram','milligram','pound','ounce','ton','stone'],
            factors: {kilogram:1,gram:0.001,milligram:1e-6,pound:0.453592,ounce:0.0283495,ton:1000,stone:6.35029}
        },
        temperature: {
            label: '{{ __('Temperature') }}',
            units: ['celsius','fahrenheit','kelvin'],
            factors: null
        },
        area: {
            label: '{{ __('Area') }}',
            units: ['sq meter','sq kilometer','sq mile','sq foot','sq inch','hectare','acre'],
            factors: {'sq meter':1,'sq kilometer':1e6,'sq mile':2589988.11,'sq foot':0.092903,'sq inch':6.4516e-4,hectare:10000,acre:4046.86}
        },
        volume: {
            label: '{{ __('Volume') }}',
            units: ['liter','milliliter','cubic meter','gallon (US)','quart (US)','pint (US)','cup (US)'],
            factors: {liter:1,milliliter:0.001,'cubic meter':1000,'gallon (US)':3.78541,'quart (US)':0.946353,'pint (US)':0.473176,'cup (US)':0.236588}
        },
        speed: {
            label: '{{ __('Speed') }}',
            units: ['m/s','km/h','mph','knot','ft/s'],
            factors: {'m/s':1,'km/h':1/3.6,mph:0.44704,knot:0.514444,'ft/s':0.3048}
        },
        data: {
            label: '{{ __('Data') }}',
            units: ['byte','kilobyte','megabyte','gigabyte','terabyte','bit'],
            factors: {byte:1,kilobyte:1024,megabyte:1048576,gigabyte:1073741824,terabyte:1099511627776,bit:0.125}
        }
    };
    return {
        cats,
        category: 'length',
        fromVal: '',
        fromUnit: 'meter',
        toUnit: 'kilometer',
        result: '',
        formula: '',
        setCategory(key) {
            this.category = key;
            const units = cats[key].units;
            this.fromUnit = units[0];
            this.toUnit = units[1] ?? units[0];
            this.fromVal = this.result = this.formula = '';
        },
        convert() {
            const v = parseFloat(this.fromVal);
            if (isNaN(v)) { this.result = ''; this.formula = ''; return; }
            const cat = cats[this.category];
            let res;
            if (this.category === 'temperature') {
                let c;
                if (this.fromUnit === 'celsius') c = v;
                else if (this.fromUnit === 'fahrenheit') c = (v - 32) * 5/9;
                else c = v - 273.15;
                if (this.toUnit === 'celsius') res = c;
                else if (this.toUnit === 'fahrenheit') res = c * 9/5 + 32;
                else res = c + 273.15;
            } else {
                res = v * cat.factors[this.fromUnit] / cat.factors[this.toUnit];
            }
            const fmt = n => Math.abs(n) >= 1e12 || (Math.abs(n) < 1e-6 && n !== 0) ? n.toExponential(6) : parseFloat(n.toPrecision(10)).toString();
            this.result = fmt(res);
            this.formula = `${v} ${this.fromUnit} = ${this.result} ${this.toUnit}`;
        },
        swapUnits() {
            [this.fromUnit, this.toUnit] = [this.toUnit, this.fromUnit];
            this.convert();
        }
    }
}
</script>
@endsection
