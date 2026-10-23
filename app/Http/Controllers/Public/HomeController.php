<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Query dasar artikel yang sudah diterbitkan
        |--------------------------------------------------------------------------
        */
        $baseQuery = fn() => Article::query()
            ->published()
            ->with([
                'category:id,name,slug',
                'region:id,name,slug',
                'author:id,name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Format artikel untuk dikirim ke Vue
        |--------------------------------------------------------------------------
        */
        $formatArticle = fn(Article $article) => [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,

            'thumbnail_url' => $article->thumbnail
                ? asset('storage/' . $article->thumbnail)
                : null,

            'thumbnail_alt' => $article->thumbnail_alt,
            'published_at' => $article->published_at?->toISOString(),
            'views' => $article->views ?? 0,
            'is_featured' => (bool) $article->is_featured,
            'is_breaking' => (bool) $article->is_breaking,

            'category' => $article->category
                ? [
                    'name' => $article->category->name,
                    'slug' => $article->category->slug,
                ]
                : null,

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
        ];

        /*
        |--------------------------------------------------------------------------
        | 1. SOROTAN UTAMA
        | Maksimal 5 artikel pilihan terbaru
        |--------------------------------------------------------------------------
        */
        $featuredArticles = $baseQuery()
            ->featured()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        /*
        | Jika belum ada artikel pilihan, gunakan artikel terbaru
        | sebagai headline sementara.
        */
        if ($featuredArticles->isEmpty()) {
            $featuredArticles = $baseQuery()
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(1)
                ->get();
        }

        /*
        | Simpan ID artikel yang sudah tampil di Sorotan Utama
        | agar tidak ditampilkan ulang di Berita Terbaru.
        */
        $featuredIds = $featuredArticles
            ->pluck('id')
            ->all();

        $featured = $featuredArticles
            ->map($formatArticle)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 2. BERITA TERBARU
        | Maksimal 8 artikel selain Sorotan Utama
        |--------------------------------------------------------------------------
        */
        $latest = $baseQuery()
            ->when(
                !empty($featuredIds),
                fn($query) => $query->whereNotIn('id', $featuredIds)
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map($formatArticle)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 3. BREAKING NEWS
        | Maksimal 5 artikel breaking terbaru
        |--------------------------------------------------------------------------
        */
        $breaking = $baseQuery()
            ->breaking()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map($formatArticle)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 4. PALING BANYAK DIBACA
        | Maksimal 5 artikel berdasarkan jumlah views
        |--------------------------------------------------------------------------
        */
        $popular = $baseQuery()
            ->orderByDesc('views')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map($formatArticle)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 5. KATEGORI AKTIF
        |--------------------------------------------------------------------------
        */
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        /*
        |--------------------------------------------------------------------------
        | Kirim seluruh data ke homepage Lintasara
        |--------------------------------------------------------------------------
        */
        return Inertia::render('public/Home', [
            'featured' => $featured,
            'latest' => $latest,
            'breaking' => $breaking,
            'popular' => $popular,
            'categories' => $categories,
        ]);
    }
}
