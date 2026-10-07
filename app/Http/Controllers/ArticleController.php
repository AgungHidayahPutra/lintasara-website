<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Region;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    /**
     * Menampilkan daftar artikel.
     */
    public function index(Request $request): Response
    {
        $articles = Article::query()
            ->with([
                'category:id,name',
                'region:id,name',
                'author:id,name',
            ])
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search')->toString();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('status'),
                fn($query) => $query->where(
                    'status',
                    $request->string('status')->toString()
                )
            )
            ->when(
                $request->filled('category'),
                fn($query) => $query->where(
                    'category_id',
                    $request->integer('category')
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $stats = [
            'total' => Article::query()->count(),

            'published' => Article::query()
                ->where('status', 'published')
                ->count(),

            'draft' => Article::query()
                ->where('status', 'draft')
                ->count(),

            'archived' => Article::query()
                ->where('status', 'archived')
                ->count(),
        ];

        return Inertia::render('admin/articles/Index', [
            'articles' => $articles,

            'categories' => $categories,

            'stats' => $stats,

            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'category' => $request->integer('category') ?: null,
            ],
        ]);
    }

    /**
     * Menampilkan form artikel baru.
     */
    public function create(): Response
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $regions = Region::query()
            ->where('is_active', true)
            ->with('parent:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'name',
                'type',
            ]);

        $tags = Tag::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return Inertia::render('admin/articles/Create', [
            'categories' => $categories,
            'regions' => $regions,
            'tags' => $tags,
        ]);
    }

    /**
     * Menyimpan artikel baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('is_active', true),
            ],

            'region_id' => [
                'nullable',
                'integer',
                Rule::exists('regions', 'id')
                    ->where('is_active', true),
            ],

            'excerpt' => [
                'nullable',
                'string',
            ],

            'content' => [
                'required',
                'string',
            ],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'thumbnail_alt' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image_caption' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image_credit' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                ]),
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'is_featured' => [
                'boolean',
            ],

            'is_breaking' => [
                'boolean',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
            ],

            'tags' => [
                'nullable',
                'array',
            ],

            'tags.*' => [
                'integer',
                'distinct',
                'exists:tags,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Slug
        |--------------------------------------------------------------------------
        */

        $baseSlug = Str::slug(
            $validated['slug'] ?: $validated['title']
        );

        if ($baseSlug === '') {
            $baseSlug = 'artikel';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Article::query()
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        /*
        |--------------------------------------------------------------------------
        | Thumbnail
        |--------------------------------------------------------------------------
        */

        $thumbnailPath = null;

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request
                ->file('thumbnail')
                ->store('articles', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Artikel
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $validated,
            $slug,
            $thumbnailPath
        ) {
            $article = Article::create([
                'category_id' => $validated['category_id'],

                'region_id' => $validated['region_id'] ?? null,

                'user_id' => $request->user()->id,

                'title' => $validated['title'],

                'slug' => $slug,

                'excerpt' => $validated['excerpt'] ?? null,

                'content' => $validated['content'],

                'thumbnail' => $thumbnailPath,

                'thumbnail_alt' =>
                $validated['thumbnail_alt'] ?? null,

                'image_caption' =>
                $validated['image_caption'] ?? null,

                'image_credit' =>
                $validated['image_credit'] ?? null,

                'status' => $validated['status'],

                'is_featured' =>
                $validated['is_featured'] ?? false,

                'is_breaking' =>
                $validated['is_breaking'] ?? false,

                'published_at' =>
                $validated['status'] === 'published'
                    ? ($validated['published_at'] ?? now())
                    : null,

                'meta_title' =>
                $validated['meta_title']
                    ?: $validated['title'],

                'meta_description' =>
                $validated['meta_description']
                    ?: ($validated['excerpt'] ?? null),
            ]);

            $article->tags()->sync(
                $validated['tags'] ?? []
            );
        });

        return to_route('admin.articles.index')
            ->with(
                'success',
                $validated['status'] === 'published'
                    ? 'Artikel berhasil diterbitkan.'
                    : 'Artikel berhasil disimpan sebagai draft.'
            );
    }

    /**
     * Menampilkan preview artikel.
     */
    public function show(Article $article): Response
    {
        $article->load([
            'category:id,name',
            'region:id,parent_id,name,type',
            'region.parent:id,name',
            'author:id,name,email',
            'tags:id,name',
        ]);

        return Inertia::render('admin/articles/Show', [
            'article' => [
                'id' => $article->id,

                'title' => $article->title,
                'slug' => $article->slug,

                'excerpt' => $article->excerpt,
                'content' => $article->content,

                'thumbnail' => $article->thumbnail,

                'thumbnail_url' => $article->thumbnail
                    ? asset('storage/' . $article->thumbnail)
                    : null,

                'thumbnail_alt' => $article->thumbnail_alt,
                'image_caption' => $article->image_caption,
                'image_credit' => $article->image_credit,

                'status' => $article->status,

                'is_featured' => (bool) $article->is_featured,
                'is_breaking' => (bool) $article->is_breaking,

                'published_at' => $article->published_at?->toISOString(),
                'created_at' => $article->created_at?->toISOString(),
                'updated_at' => $article->updated_at?->toISOString(),

                'views' => $article->views,

                'meta_title' => $article->meta_title,
                'meta_description' => $article->meta_description,

                'category' => $article->category
                    ? [
                        'id' => $article->category->id,
                        'name' => $article->category->name,
                    ]
                    : null,

                'region' => $article->region
                    ? [
                        'id' => $article->region->id,
                        'name' => $article->region->name,
                        'type' => $article->region->type,

                        'parent' => $article->region->parent
                            ? [
                                'id' => $article->region->parent->id,
                                'name' => $article->region->parent->name,
                            ]
                            : null,
                    ]
                    : null,

                'author' => $article->author
                    ? [
                        'id' => $article->author->id,
                        'name' => $article->author->name,
                        'email' => $article->author->email,
                    ]
                    : null,

                'tags' => $article->tags
                    ->map(fn($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ])
                    ->values(),
            ],
        ]);
    }

    /**
     * Menampilkan form edit artikel.
     */
    public function edit(Article $article): Response
    {
        $article->load([
            'tags:id,name',
        ]);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $regions = Region::query()
            ->where('is_active', true)
            ->with('parent:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'name',
                'type',
            ]);

        $tags = Tag::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return Inertia::render('admin/articles/Edit', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'category_id' => $article->category_id,
                'region_id' => $article->region_id,
                'excerpt' => $article->excerpt,
                'content' => $article->content,

                'thumbnail' => $article->thumbnail,
                'thumbnail_url' => $article->thumbnail
                    ? Storage::url($article->thumbnail)
                    : null,

                'thumbnail_alt' => $article->thumbnail_alt,
                'image_caption' => $article->image_caption,
                'image_credit' => $article->image_credit,

                'status' => $article->status,

                'published_at' => $article->published_at
                    ? $article->published_at->format('Y-m-d\TH:i')
                    : null,

                'is_featured' => $article->is_featured,
                'is_breaking' => $article->is_breaking,

                'meta_title' => $article->meta_title,
                'meta_description' => $article->meta_description,

                'tags' => $article->tags
                    ->pluck('id')
                    ->values(),
            ],

            'categories' => $categories,
            'regions' => $regions,
            'tags' => $tags,
        ]);
    }

    /**
     * Memperbarui artikel.
     */
    public function update(
        Request $request,
        Article $article
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('is_active', true),
            ],

            'region_id' => [
                'nullable',
                'integer',
                Rule::exists('regions', 'id')
                    ->where('is_active', true),
            ],

            'excerpt' => [
                'nullable',
                'string',
            ],

            'content' => [
                'required',
                'string',
            ],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'remove_thumbnail' => [
                'nullable',
                'boolean',
            ],

            'thumbnail_alt' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image_caption' => [
                'nullable',
                'string',
                'max:255',
            ],

            'image_credit' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                    'archived',
                ]),
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'is_featured' => [
                'boolean',
            ],

            'is_breaking' => [
                'boolean',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
            ],

            'tags' => [
                'nullable',
                'array',
            ],

            'tags.*' => [
                'integer',
                'distinct',
                'exists:tags,id',
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Slug
    |--------------------------------------------------------------------------
    */

        $baseSlug = Str::slug(
            $validated['slug'] ?: $validated['title']
        );

        if ($baseSlug === '') {
            $baseSlug = 'artikel';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Article::query()
            ->where('slug', $slug)
            ->whereKeyNot($article->id)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        /*
    |--------------------------------------------------------------------------
    | Thumbnail
    |--------------------------------------------------------------------------
    */

        $thumbnailPath = $article->thumbnail;

        if ($request->boolean('remove_thumbnail')) {
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            $thumbnailPath = null;
        }

        if ($request->hasFile('thumbnail')) {
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            $thumbnailPath = $request
                ->file('thumbnail')
                ->store('articles', 'public');
        }

        /*
    |--------------------------------------------------------------------------
    | Update Artikel
    |--------------------------------------------------------------------------
    */

        DB::transaction(function () use (
            $article,
            $validated,
            $slug,
            $thumbnailPath
        ) {
            $publishedAt = null;

            if ($validated['status'] === 'published') {
                $publishedAt =
                    $validated['published_at']
                    ?? $article->published_at
                    ?? now();
            }

            $article->update([
                'category_id' => $validated['category_id'],

                'region_id' =>
                $validated['region_id'] ?? null,

                'title' => $validated['title'],

                'slug' => $slug,

                'excerpt' =>
                $validated['excerpt'] ?? null,

                'content' => $validated['content'],

                'thumbnail' => $thumbnailPath,

                'thumbnail_alt' =>
                $validated['thumbnail_alt'] ?? null,

                'image_caption' =>
                $validated['image_caption'] ?? null,

                'image_credit' =>
                $validated['image_credit'] ?? null,

                'status' => $validated['status'],

                'is_featured' =>
                $validated['is_featured'] ?? false,

                'is_breaking' =>
                $validated['is_breaking'] ?? false,

                'published_at' => $publishedAt,

                'meta_title' =>
                $validated['meta_title']
                    ?: $validated['title'],

                'meta_description' =>
                $validated['meta_description']
                    ?: ($validated['excerpt'] ?? null),
            ]);

            $article->tags()->sync(
                $validated['tags'] ?? []
            );
        });

        return to_route('admin.articles.index')
            ->with(
                'success',
                $validated['status'] === 'published'
                    ? 'Artikel berhasil diperbarui dan diterbitkan.'
                    : 'Artikel berhasil diperbarui.'
            );
    }

    /**
     * Menghapus artikel.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $thumbnailPath = $article->thumbnail;

        DB::transaction(function () use ($article) {
            /*
        |--------------------------------------------------------------------------
        | Hapus Relasi Tag
        |--------------------------------------------------------------------------
        |
        | Putuskan relasi artikel dengan tag terlebih dahulu.
        |
        */

            $article->tags()->detach();

            /*
        |--------------------------------------------------------------------------
        | Hapus Artikel
        |--------------------------------------------------------------------------
        */

            $article->delete();
        });

        /*
    |--------------------------------------------------------------------------
    | Hapus Thumbnail
    |--------------------------------------------------------------------------
    |
    | File thumbnail baru dihapus setelah transaksi database berhasil.
    | Dengan begitu file tidak hilang apabila proses penghapusan database gagal.
    |
    */

        if (
            $thumbnailPath &&
            Storage::disk('public')->exists($thumbnailPath)
        ) {
            Storage::disk('public')->delete($thumbnailPath);
        }

        return to_route('admin.articles.index')
            ->with(
                'success',
                'Artikel berhasil dihapus.'
            );
    }
}
