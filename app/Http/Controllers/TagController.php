<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    /**
     * Daftar tag.
     */
    public function index(Request $request): Response
    {
        $tags = Tag::query()
            ->withCount('articles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Tag::count(),
            'used' => Tag::whereHas('articles')->count(),
            'unused' => Tag::whereDoesntHave('articles')->count(),
        ];

        return Inertia::render('admin/tags/Index', [
            'tags' => $tags,
            'stats' => $stats,
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    /**
     * Form tambah tag.
     */
    public function create(): Response
    {
        return Inertia::render('admin/tags/Create');
    }

    /**
     * Simpan tag.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:tags,name',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $baseSlug = Str::slug(
            $validated['slug'] ?: $validated['name']
        );

        if ($baseSlug === '') {
            $baseSlug = 'tag';
        }

        $slug = $this->uniqueSlug($baseSlug);

        Tag::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return to_route('admin.tags.index')
            ->with('success', 'Tag berhasil ditambahkan.');
    }

    /**
     * Form edit tag.
     */
    public function edit(Tag $tag): Response
    {
        return Inertia::render('admin/tags/Edit', [
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ],
        ]);
    }

    /**
     * Perbarui tag.
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->ignore($tag->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $baseSlug = Str::slug(
            $validated['slug'] ?: $validated['name']
        );

        if ($baseSlug === '') {
            $baseSlug = 'tag';
        }

        $slug = $this->uniqueSlug($baseSlug, $tag->id);

        $tag->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return to_route('admin.tags.index')
            ->with('success', 'Tag berhasil diperbarui.');
    }

    /**
     * Hapus tag yang belum digunakan artikel.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        if ($tag->articles()->exists()) {
            return back()->with(
                'error',
                'Tag tidak dapat dihapus karena masih digunakan oleh artikel.'
            );
        }

        $tag->delete();

        return to_route('admin.tags.index')
            ->with('success', 'Tag berhasil dihapus.');
    }

    /**
     * Buat slug unik.
     */
    private function uniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $counter = 2;

        while (
            Tag::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
