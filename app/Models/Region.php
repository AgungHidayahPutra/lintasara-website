<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'type',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Wilayah induk.
     *
     * Contoh:
     * Palembang -> Sumatera Selatan
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    /**
     * Wilayah yang berada di bawah wilayah ini.
     *
     * Contoh:
     * Sumatera Selatan -> Palembang, Banyuasin, Ogan Ilir, dst.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    /**
     * Artikel yang terkait dengan wilayah ini.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
