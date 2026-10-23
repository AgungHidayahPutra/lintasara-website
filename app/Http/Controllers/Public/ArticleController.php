<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Ambil artikel yang sudah diterbitkan
        |--------------------------------------------------------------------------
        | Artikel draft, archived, atau yang tanggal terbitnya
        | masih di masa depan tidak dapat dibaca publik.
        */
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'category:id,name,slug',
                'region:id,name,slug',
                'author:id,name',
                'tags:id,name,slug',
            ])
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | 2. Hitung kunjungan artikel
        |--------------------------------------------------------------------------
        | Satu sesi browser dihitung maksimal satu kali
        | untuk artikel yang sama dalam 30 menit.
        |
        | Menggunakan session Laravel, bukan alamat IP.
        |--------------------------------------------------------------------------
        */
        $sessionKey = 'lintasara.article.viewed.' . $article->id;
        $lastViewed = $request->session()->get($sessionKey);

        if (
            !$lastViewed ||
            now()->timestamp - (int) $lastViewed >= 1800
        ) {
            DB::table('articles')
                ->where('id', $article->id)
                ->increment('views');

            $request->session()->put(
                $sessionKey,
                now()->timestamp
            );

            $article->refresh();
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Artikel terkait
        |--------------------------------------------------------------------------
        | Prioritas:
        | - Kategori yang sama
        | - Tanggal publikasi terbaru
        | - Tidak termasuk artikel yang sedang dibaca
        | - Maksimal 3 artikel
        |--------------------------------------------------------------------------
        */
        $relatedQuery = fn() => Article::query()
            ->published()
            ->where('id', '!=', $article->id)
            ->with([
                'category:id,name,slug',
                'region:id,name,slug',
                'author:id,name',
            ]);

        $relatedArticles = collect();

        if ($article->category_id) {
            $relatedArticles = $relatedQuery()
                ->where('category_id', $article->category_id)
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(3)
                ->get();
        }

        /*
        | Jika kategori yang sama memiliki kurang dari 3 artikel,
        | isi sisanya dengan artikel terbaru kategori lain.
        */
        if ($relatedArticles->count() < 3) {
            $remaining = 3 - $relatedArticles->count();

            $excludedIds = $relatedArticles
                ->pluck('id')
                ->push($article->id)
                ->all();

            $fallback = $relatedQuery()
                ->whereNotIn('id', $excludedIds)
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit($remaining)
                ->get();

            $relatedArticles = $relatedArticles
                ->concat($fallback);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Paling banyak dibaca
        |--------------------------------------------------------------------------
        */
        $popularArticles = Article::query()
            ->published()
            ->where('id', '!=', $article->id)
            ->with([
                'category:id,name,slug',
            ])
            ->orderByDesc('views')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 5. Format data artikel ringkas
        |--------------------------------------------------------------------------
        */
        $formatArticle = fn(Article $item) => [
            'id' => $item->id,
            'title' => $item->title,
            'slug' => $item->slug,
            'excerpt' => $item->excerpt,
            'thumbnail_url' => $item->thumbnail
                ? asset('storage/' . $item->thumbnail)
                : null,
            'thumbnail_alt' => $item->thumbnail_alt,
            'published_at' => $item->published_at?->toISOString(),
            'views' => (int) ($item->views ?? 0),
            'category' => $item->category
                ? [
                    'name' => $item->category->name,
                    'slug' => $item->category->slug,
                ]
                : null,
        ];

        /*
        |--------------------------------------------------------------------------
        | 6. Kirim data ke halaman detail Vue
        |--------------------------------------------------------------------------
        */
        return Inertia::render('public/Show', [
            'article' => [
                ...$formatArticle($article),

                'content' => $article->content,
                'image_caption' => $article->image_caption,
                'image_credit' => $article->image_credit,

                'meta_title' => $article->meta_title,
                'meta_description' => $article->meta_description,

                'region' => $article->region
                    ? [
                        'name' => $article->region->name,
                        'slug' => $article->region->slug,
                    ]
                    : null,

                'author' => $article->author
                    ? [
                        'name' => $article->author->name,
                    ]
                    : null,

                'tags' => $article->tags->map(
                    fn($tag) => [
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ]
                )->values(),
            ],

            'related' => $relatedArticles
                ->map($formatArticle)
                ->values(),

            'popular' => $popularArticles
                ->map($formatArticle)
                ->values(),
        ]);
    }
}
