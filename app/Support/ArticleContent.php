<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Str;

class ArticleContent
{
    public static function sanitize(string $content): string
    {
        // Artikel lama masih menggunakan teks biasa.
        // Ubah menjadi paragraf HTML yang aman.
        if (! preg_match('/<\s*[a-z][^>]*>/i', $content)) {
            $paragraphs = preg_split('/\R\s*\R/u', trim($content)) ?: [];

            $content = collect($paragraphs)
                ->filter(fn($paragraph) => trim($paragraph) !== '')
                ->map(
                    fn($paragraph) =>
                    '<p>' . nl2br(e(trim($paragraph))) . '</p>'
                )
                ->implode("\n");
        }

        $config = HTMLPurifier_Config::createDefault();

        $config->set(
            'HTML.Allowed',
            'p,br,strong,em,u,h2,h3,ul,ol,li,blockquote,a[href|title|target|rel]'
        );

        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
        ]);

        $config->set('AutoFormat.RemoveEmpty', false);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($content);
    }
}
