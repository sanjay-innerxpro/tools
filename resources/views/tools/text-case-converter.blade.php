@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Text Case Converter'))
@section('meta_description', __('Change text to uppercase, lowercase, title case, or sentence case instantly with one click.'))

@section('content')
<div x-data="caseConverter()" x-cloak class="max-w-3xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-lime-400 to-green-500 rounded-2xl shadow-lg shadow-lime-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Text Case Converter') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Convert text between different letter cases instantly') }}</p>
    </div>

    {{-- Input --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Input') }}</h2>
            <button @click="input=''" class="text-xs text-gray-400 hover:text-red-500 transition-colors">{{ __('Clear') }}</button>
        </div>
        <textarea x-model="input"
                  placeholder="{{ __('Type or paste your text here...') }}"
                  class="w-full min-h-[7rem] text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-gray-900 dark:text-white placeholder-gray-400 resize-none focus:outline-none focus:ring-2 focus:ring-lime-400 transition"></textarea>
        <p class="mt-2 text-xs text-gray-400" x-show="input"
           x-text="input.trim().split(/\s+/).filter(Boolean).length + ' {{ __('Words') }}, ' + input.length + ' {{ __('characters') }}'"></p>
    </div>

    {{-- Conversions --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Conversions') }}</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="c in cases" :key="c.key">
                <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                    <div class="w-32 flex-shrink-0">
                        <span class="text-xs font-bold text-gray-400 dark:text-gray-500 font-mono" x-text="c.label"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 dark:text-gray-300 truncate" x-text="result(c.key)||'—'"></p>
                    </div>
                    <button @click="copy(c.key)" x-show="result(c.key)"
                            class="flex-shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                            :class="copied[c.key]?'bg-lime-100 dark:bg-lime-900/30 text-lime-700 dark:text-lime-400 opacity-100':'text-gray-400 hover:text-lime-600 dark:hover:text-lime-400 hover:bg-lime-50 dark:hover:bg-lime-900/20'">
                        <span x-show="!copied[c.key]">{{ __('Copy') }}</span>
                        <span x-show="copied[c.key]">{{ __('Copied!') }}</span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function caseConverter() {
    const transforms = {
        upper:    s => s.toUpperCase(),
        lower:    s => s.toLowerCase(),
        title:    s => s.replace(/\b\w/g, c=>c.toUpperCase()),
        sentence: s => { const t=s.toLowerCase(); return t.charAt(0).toUpperCase()+t.slice(1); },
        camel:    s => s.trim().replace(/[\s_\-]+(\w)/g,(_,c)=>c.toUpperCase()).replace(/^[A-Z]/,c=>c.toLowerCase()),
        pascal:   s => { const c=s.trim().replace(/[\s_\-]+(\w)/g,(_,c)=>c.toUpperCase()); return c.charAt(0).toUpperCase()+c.slice(1); },
        snake:    s => s.trim().replace(/\s+/g,'_').replace(/([A-Z])/g,c=>'_'+c.toLowerCase()).replace(/^_+|_+$/g,'').replace(/_+/g,'_').toLowerCase(),
        kebab:    s => s.trim().replace(/\s+/g,'-').replace(/([A-Z])/g,c=>'-'+c.toLowerCase()).replace(/^-+|-+$/g,'').replace(/-+/g,'-').toLowerCase(),
        alt:      s => s.split('').map((c,i)=>i%2===0?c.toLowerCase():c.toUpperCase()).join(''),
    };
    return {
        input: '',
        copied: { upper:false, lower:false, title:false, sentence:false, camel:false, pascal:false, snake:false, kebab:false, alt:false },
        cases: [
            { key:'upper',    label:'UPPERCASE'    },
            { key:'lower',    label:'lowercase'    },
            { key:'title',    label:'Title Case'   },
            { key:'sentence', label:'Sentence case'},
            { key:'camel',    label:'camelCase'    },
            { key:'pascal',   label:'PascalCase'   },
            { key:'snake',    label:'snake_case'   },
            { key:'kebab',    label:'kebab-case'   },
            { key:'alt',      label:'aLtErNaTiNg'  },
        ],
        result(key) { return this.input ? (transforms[key]?.(this.input) ?? '') : ''; },
        copy(key) {
            const r = this.result(key);
            if (!r) return;
            navigator.clipboard.writeText(r).then(() => {
                this.copied[key]=true;
                setTimeout(() => this.copied[key]=false, 2000);
            });
        }
    }
}
</script>
@endsection
