<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori.
     */
    public function index(Request $request): Response
    {
        $categories = Category::query()
            ->withCount('articles')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search')->toString();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    if ($request->string('status')->toString() === 'active') {
                        $query->where('is_active', true);
                    }

                    if ($request->string('status')->toString() === 'inactive') {
                        $query->where('is_active', false);
                    }
                }
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Category::query()->count(),

            'active' => Category::query()
                ->where('is_active', true)
                ->count(),

            'inactive' => Category::query()
                ->where('is_active', false)
                ->count(),

            'used' => Category::query()
                ->whereHas('articles')
                ->count(),
        ];

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,

            'stats' => $stats,

            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    /**
     * Menampilkan form tambah kategori.
     */
    public function create(): Response
    {
        return Inertia::render('admin/categories/Create');
    }

    /**
     * Menyimpan kategori baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $baseSlug = Str::slug(
            $validated['slug'] ?: $validated['name']
        );

        if ($baseSlug === '') {
            $baseSlug = 'kategori';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Category::query()
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'],
        ]);

        return to_route('admin.categories.index')
            ->with(
                'success',
                'Kategori berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan form edit kategori.
     */
    public function edit(Category $category): Response
    {
        return Inertia::render('admin/categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'is_active' => (bool) $category->is_active,
                'sort_order' => $category->sort_order,
            ],
        ]);
    }

    /**
     * Memperbarui kategori.
     */
    public function update(
        Request $request,
        Category $category
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->ignore($category->id),
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $baseSlug = Str::slug(
            $validated['slug'] ?: $validated['name']
        );

        if ($baseSlug === '') {
            $baseSlug = 'kategori';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Category::query()
            ->where('slug', $slug)
            ->whereKeyNot($category->id)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? false,
            'sort_order' => $validated['sort_order'],
        ]);

        return to_route('admin.categories.index')
            ->with(
                'success',
                'Kategori berhasil diperbarui.'
            );
    }

    /**
     * Menghapus kategori.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->articles()->exists()) {
            return back()->with(
                'error',
                'Kategori tidak dapat dihapus karena masih digunakan oleh artikel.'
            );
        }

        $category->delete();

        return to_route('admin.categories.index')
            ->with(
                'success',
                'Kategori berhasil dihapus.'
            );
    }
}
