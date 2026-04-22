<?php

namespace App\Support;

class SeoMeta
{
    /**
     * Resolve the default OG image URL for a given path.
     */
    public static function defaultImageForPath(string $path): string
    {
        $path = ltrim($path, '/');

        if ($path === 'tools/media-scanner') {
            return asset('images/og-media.svg');
        }

        if (in_array($path, [
            'tools/pdf-to-text',
            'tools/merge-pdf',
            'tools/split-pdf',
            'tools/image-converter',
            'tools/compress-image',
            'tools/image-resizer',
            'tools/video-converter',
            'tools/audio-converter',
        ], true)) {
            return asset('images/og-converters.svg');
        }

        if (in_array($path, [
            'tools/qr-code-generator',
            'tools/word-counter',
            'tools/password-generator',
            'tools/json-formatter',
            'tools/base64-encoder',
            'tools/unit-converter',
            'tools/color-converter',
        ], true)) {
            return asset('images/og-utilities.svg');
        }

        if (str_starts_with($path, 'tools/')) {
            return asset('images/og-devtools.svg');
        }

        return asset('images/og-default.svg');
    }
}
