@extends('layouts.app')
@section('title', config('app.name') . ' — ' . __('Markdown Preview'))

@section('content')
<div x-data="mdPreview()" x-cloak class="max-w-7xl mx-auto px-4 py-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl shadow-lg shadow-sky-500/20 mb-5">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ __('Markdown Preview') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">{{ __('Write Markdown and see the live HTML preview') }}</p>
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">**bold**</span>
            <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">*italic*</span>
            <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded"># Heading</span>
            <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">- list</span>
            <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">`code`</span>
        </div>
        <div class="flex items-center gap-2">
            <button @click="md=''"
                    class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all">
                {{ __('Clear') }}
            </button>
            <button @click="copyHtml()"
                    :class="htmlCopied?'bg-sky-700':'bg-sky-600 hover:bg-sky-700'"
                    class="px-4 py-2 text-sm text-white font-medium rounded-lg transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span x-show="!htmlCopied">{{ __('Copy HTML') }}</span>
                <span x-show="htmlCopied">{{ __('HTML Copied!') }}</span>
            </button>
        </div>
    </div>

    {{-- Split pane --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Editor --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 flex flex-col overflow-hidden">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <span class="ml-2 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Editor') }}</span>
            </div>
            <textarea x-model="md"
                      placeholder="{{ __('Write Markdown here...') }}"
                      spellcheck="false"
                      class="flex-1 min-h-[32rem] lg:min-h-[38rem] font-mono text-sm p-5 text-gray-900 dark:text-white placeholder-gray-400 bg-transparent resize-none focus:outline-none leading-relaxed"></textarea>
        </div>

        {{-- Preview --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 flex flex-col overflow-hidden">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <div class="w-3 h-3 rounded-full bg-sky-400"></div>
                <div class="w-3 h-3 rounded-full bg-sky-300"></div>
                <div class="w-3 h-3 rounded-full bg-sky-200"></div>
                <span class="ml-2 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Preview') }}</span>
            </div>
            <div x-html="parsed"
                 class="flex-1 min-h-[32rem] lg:min-h-[38rem] p-5 overflow-auto prose-md text-sm leading-relaxed [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:mt-5 [&_h1]:mb-3 [&_h1]:pb-2 [&_h1]:border-b [&_h1]:border-gray-200 [&_h1]:dark:border-gray-700 [&_h1]:text-gray-900 [&_h1]:dark:text-white [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mt-4 [&_h2]:mb-2 [&_h2]:pb-1 [&_h2]:border-b [&_h2]:border-gray-200 [&_h2]:dark:border-gray-700 [&_h2]:text-gray-900 [&_h2]:dark:text-white [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:mt-4 [&_h3]:mb-2 [&_h3]:text-gray-900 [&_h3]:dark:text-white [&_h4]:text-base [&_h4]:font-semibold [&_h4]:mt-3 [&_h4]:mb-1 [&_h4]:text-gray-900 [&_h4]:dark:text-white [&_p]:my-2 [&_p]:text-gray-700 [&_p]:dark:text-gray-300 [&_strong]:font-bold [&_strong]:text-gray-900 [&_strong]:dark:text-white [&_em]:italic [&_del]:line-through [&_del]:text-gray-400 [&_blockquote]:border-l-4 [&_blockquote]:border-sky-400 [&_blockquote]:pl-4 [&_blockquote]:py-1 [&_blockquote]:italic [&_blockquote]:text-gray-600 [&_blockquote]:dark:text-gray-400 [&_blockquote]:my-3 [&_hr]:my-5 [&_hr]:border-gray-300 [&_hr]:dark:border-gray-600 [&_ul]:list-disc [&_ul]:ml-5 [&_ul]:my-2 [&_ul]:space-y-1 [&_ol]:list-decimal [&_ol]:ml-5 [&_ol]:my-2 [&_ol]:space-y-1 [&_li]:text-gray-700 [&_li]:dark:text-gray-300 [&_pre]:bg-gray-100 [&_pre]:dark:bg-gray-800 [&_pre]:rounded-lg [&_pre]:p-4 [&_pre]:my-3 [&_pre]:overflow-x-auto [&_code]:font-mono [&_pre_code]:text-sm [&_pre_code]:text-gray-800 [&_pre_code]:dark:text-gray-200 [&_:not(pre)_code]:bg-gray-100 [&_:not(pre)_code]:dark:bg-gray-800 [&_:not(pre)_code]:px-1.5 [&_:not(pre)_code]:py-0.5 [&_:not(pre)_code]:rounded [&_:not(pre)_code]:text-pink-600 [&_:not(pre)_code]:dark:text-pink-400 [&_:not(pre)_code]:text-sm [&_a]:text-sky-600 [&_a]:dark:text-sky-400 [&_a]:underline"></div>
        </div>
    </div>
</div>

<script>
function mdPreview() {
    function parse(md) {
        if (!md.trim()) return '<p class="text-gray-400 italic">{{ __('Write Markdown here...') }}</p>';
        // Escape HTML entities
        let s = md.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        // Protect fenced code blocks
        const blocks = [];
        s = s.replace(/```(\w*)\n?([\s\S]*?)```/g, (_, lang, code) => {
            blocks.push(`<pre><code class="language-${lang}">${code.trim()}</code></pre>`);
            return `\x00B${blocks.length-1}\x00`;
        });
        // Headings
        s = s.replace(/^#{6} (.+)$/gm,'<h6>$1</h6>').replace(/^#{5} (.+)$/gm,'<h5>$1</h5>')
             .replace(/^#{4} (.+)$/gm,'<h4>$1</h4>').replace(/^#{3} (.+)$/gm,'<h3>$1</h3>')
             .replace(/^#{2} (.+)$/gm,'<h2>$1</h2>').replace(/^# (.+)$/gm,'<h1>$1</h1>');
        // HR
        s = s.replace(/^---$/gm,'<hr>');
        // Blockquotes
        s = s.replace(/^> (.+)$/gm,'<blockquote>$1</blockquote>');
        // Inline: bold+italic, bold, italic, strikethrough, inline code, links
        s = s.replace(/\*{3}(.+?)\*{3}/g,'<strong><em>$1</em></strong>')
             .replace(/\*{2}(.+?)\*{2}/g,'<strong>$1</strong>')
             .replace(/\*(.+?)\*/g,'<em>$1</em>')
             .replace(/~~(.+?)~~/g,'<del>$1</del>')
             .replace(/`([^`\n]+)`/g,'<code>$1</code>')
             .replace(/\[([^\]]+)\]\(([^)\s]+)\)/g,(_,t,u)=>{
                 const safe=/^(https?:|mailto:|\/|#)/.test(u)?u:'#';
                 return `<a href="${safe}" target="_blank" rel="noopener">${t}</a>`;
             });
        // Lists - line by line
        const lines = s.split('\n');
        const out = []; let inUl=false, inOl=false;
        for (const line of lines) {
            if (/^[*\-] /.test(line)) {
                if (!inUl) { if(inOl){out.push('</ol>');inOl=false;} out.push('<ul>'); inUl=true; }
                out.push(`<li>${line.slice(2)}</li>`);
            } else if (/^\d+\. /.test(line)) {
                if (!inOl) { if(inUl){out.push('</ul>');inUl=false;} out.push('<ol>'); inOl=true; }
                out.push(`<li>${line.replace(/^\d+\. /,'')}</li>`);
            } else {
                if(inUl){out.push('</ul>');inUl=false;} if(inOl){out.push('</ol>');inOl=false;}
                out.push(line);
            }
        }
        if(inUl)out.push('</ul>'); if(inOl)out.push('</ol>');
        s = out.join('\n');
        // Paragraphs
        s = s.split(/\n{2,}/).map(block => {
            block = block.trim();
            if (!block) return '';
            if (/^<(h[1-6]|ul|ol|pre|blockquote|hr|\x00)/.test(block)) return block;
            return `<p>${block.replace(/\n/g,'<br>')}</p>`;
        }).join('\n');
        // Restore code blocks
        s = s.replace(/\x00B(\d+)\x00/g, (_, i) => blocks[parseInt(i)]);
        return s;
    }

    const DEFAULT = `# Welcome to Markdown Preview

## What is Markdown?

**Markdown** is a lightweight markup language that converts plain text to *formatted HTML*.

## Features

- **Bold** and *italic* text
- \`inline code\` snippets
- Ordered and unordered lists
- [Hyperlinks](https://example.com)
- Blockquotes and horizontal rules

> This is a blockquote. Perfect for callouts and quotes.

## Code Example

\`\`\`js
const greet = name => \`Hello, \${name}!\`;
console.log(greet('World'));
\`\`\`

---

1. First item
2. Second item
3. Third item`;

    return {
        md: DEFAULT,
        htmlCopied: false,
        get parsed() { return parse(this.md); },
        get rawHtml() { return parse(this.md); },
        copyHtml() {
            navigator.clipboard.writeText(this.rawHtml).then(() => {
                this.htmlCopied=true;
                setTimeout(()=>this.htmlCopied=false, 2000);
            });
        }
    }
}
</script>
@endsection
