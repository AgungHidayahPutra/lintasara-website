<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegionController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->toString());
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();

        $regions = Region::query()
            ->with([
                'parent:id,name,type',
            ])
            ->withCount([
                'children',
                'articles',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('parent', function ($parent) use ($search) {
                            $parent->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                in_array($type, ['province', 'city', 'regency'], true),
                fn($query) => $query->where('type', $type)
            )
            ->when(
                $status === 'active',
                fn($query) => $query->where('is_active', true)
            )
            ->when(
                $status === 'inactive',
                fn($query) => $query->where('is_active', false)
            )
            ->orderByRaw(
                "CASE type
                    WHEN 'province' THEN 0
                    WHEN 'city' THEN 1
                    WHEN 'regency' THEN 2
                    ELSE 3 END"
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/regions/Index', [
            'regions' => $regions,

            'stats' => [
                'total' => Region::count(),
                'provinces' => Region::where('type', 'province')->count(),
                'cities' => Region::where('type', 'city')->count(),
                'regencies' => Region::where('type', 'regency')->count(),
                'active' => Region::where('is_active', true)->count(),
                'inactive' => Region::where('is_active', false)->count(),
            ],

            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/regions/Create', [
            'provinces' => Region::query()
                ->where('type', 'province')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                Rule::in(['province', 'city', 'regency']),
            ],

            'parent_id' => [
                'nullable',
                'integer',
                'exists:regions,id',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $parentId = $this->validateParent(
            $validated['type'],
            $validated['parent_id'] ?? null
        );

        $baseSlug = Str::slug(
            $validated['slug'] ?: $validated['name']
        );

        if ($baseSlug === '') {
            $baseSlug = 'wilayah';
        }

        Region::create([
            'parent_id' => $parentId,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($baseSlug),
            'type' => $validated['type'],
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return to_route('admin.regions.index')
            ->with('success', 'Wilayah berhasil ditambahkan.');
    }

    public function edit(Region $region): Response
    {
        return Inertia::render('admin/regions/Edit', [
            'region' => [
                'id' => $region->id,
                'parent_id' => $region->parent_id,
                'name' => $region->name,
                'slug' => $region->slug,
                'type' => $region->type,
                'is_active' => $region->is_active,
                'sort_order' => $region->sort_order,
            ],

            'provinces' => Region::query()
                ->where('type', 'province')
                ->where(function ($query) use ($region) {
                    $query->where('is_active', true);

                    if ($region->parent_id) {
                        $query->orWhere('id', $region->parent_id);
                    }
                })
                ->orderBy('name')
                ->get(['id', 'name']),

            'childrenCount' => $region->children()->count(),
            'articlesCount' => $region->articles()->count(),
        ]);
    }

    public function update(
        Request $request,
        Region $region
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                Rule::in(['province', 'city', 'regency']),
            ],

            'parent_id' => [
                'nullable',
                'integer',
                'exists:regions,id',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        if (
            $region->type === 'province' &&
            $validated['type'] !== 'province' &&
            $region->children()->exists()
        ) {
            throw ValidationException::withMessages([
                'type' => 'Provinsi yang memiliki wilayah turunan tidak dapat diubah jenisnya.',
            ]);
        }

        $parentId = $this->validateParent(
            $validated['type'],
            $validated['parent_id'] ?? null,
            $region->id
        );

        $baseSlug = Str::slug(
            $validated['slug'] ?: $validated['name']
        );

        if ($baseSlug === '') {
            $baseSlug = 'wilayah';
        }

        $region->update([
            'parent_id' => $parentId,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($baseSlug, $region->id),
            'type' => $validated['type'],
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return to_route('admin.regions.index')
            ->with('success', 'Wilayah berhasil diperbarui.');
    }

    public function destroy(Region $region): RedirectResponse
    {
        if ($region->children()->exists()) {
            return back()->with(
                'error',
                'Wilayah tidak dapat dihapus karena masih memiliki wilayah turunan.'
            );
        }

        if ($region->articles()->exists()) {
            return back()->with(
                'error',
                'Wilayah tidak dapat dihapus karena masih digunakan oleh artikel.'
            );
        }

        $region->delete();

        return to_route('admin.regions.index')
            ->with('success', 'Wilayah berhasil dihapus.');
    }

    private function validateParent(
        string $type,
        ?int $parentId,
        ?int $currentRegionId = null
    ): ?int {
        if ($type === 'province') {
            if ($parentId !== null) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Provinsi tidak boleh memiliki wilayah induk.',
                ]);
            }

            return null;
        }

        if ($parentId === null) {
            throw ValidationException::withMessages([
                'parent_id' => 'Kabupaten atau kota wajib memilih provinsi.',
            ]);
        }

        if (
            $currentRegionId !== null &&
            $parentId === $currentRegionId
        ) {
            throw ValidationException::withMessages([
                'parent_id' => 'Wilayah tidak dapat menjadi induk dirinya sendiri.',
            ]);
        }

        $parent = Region::find($parentId);

        if (!$parent || $parent->type !== 'province') {
            throw ValidationException::withMessages([
                'parent_id' => 'Wilayah induk harus berupa provinsi.',
            ]);
        }

        if (!$parent->is_active && $parentId !== Region::find($currentRegionId)?->parent_id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Provinsi induk harus aktif.',
            ]);
        }

        return $parentId;
    }

    private function uniqueSlug(
        string $baseSlug,
        ?int $ignoreId = null
    ): string {
        $slug = $baseSlug;
        $counter = 2;

        while (
            Region::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where('id', '!=', $ignoreId)
            )
            ->exists()
        ) {
            $suffix = '-' . $counter;
            $slug = Str::limit($baseSlug, 255 - strlen($suffix), '') . $suffix;
            $counter++;
        }

        return $slug;
    }
}
