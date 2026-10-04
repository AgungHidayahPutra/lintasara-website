<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Nasional',
                'slug' => 'nasional',
                'description' => 'Berita dan informasi terkini dari seluruh Indonesia.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Daerah',
                'slug' => 'daerah',
                'description' => 'Berita dan informasi terkini dari berbagai daerah.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Politik',
                'slug' => 'politik',
                'description' => 'Berita politik, pemerintahan, dan kebijakan publik.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Ekonomi',
                'slug' => 'ekonomi',
                'description' => 'Berita ekonomi, bisnis, keuangan, dan dunia usaha.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Hukum',
                'slug' => 'hukum',
                'description' => 'Berita hukum, kriminal, dan penegakan hukum.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Pendidikan',
                'slug' => 'pendidikan',
                'description' => 'Berita pendidikan, sekolah, kampus, dan perkembangan akademik.',
                'sort_order' => 6,
            ],
            [
                'name' => 'Kesehatan',
                'slug' => 'kesehatan',
                'description' => 'Berita dan informasi seputar kesehatan.',
                'sort_order' => 7,
            ],
            [
                'name' => 'Teknologi',
                'slug' => 'teknologi',
                'description' => 'Berita teknologi, digital, internet, dan inovasi.',
                'sort_order' => 8,
            ],
            [
                'name' => 'Olahraga',
                'slug' => 'olahraga',
                'description' => 'Berita olahraga lokal, nasional, dan internasional.',
                'sort_order' => 9,
            ],
            [
                'name' => 'Lifestyle',
                'slug' => 'lifestyle',
                'description' => 'Berita gaya hidup, hiburan, budaya, dan tren.',
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }
    }
}
