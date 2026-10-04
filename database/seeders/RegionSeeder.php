<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $province = Region::updateOrCreate(
            [
                'slug' => 'sumatera-selatan',
            ],
            [
                'parent_id' => null,
                'name' => 'Sumatera Selatan',
                'type' => 'province',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $regions = [
            ['name' => 'Palembang', 'slug' => 'palembang', 'type' => 'city'],
            ['name' => 'Prabumulih', 'slug' => 'prabumulih', 'type' => 'city'],
            ['name' => 'Pagar Alam', 'slug' => 'pagar-alam', 'type' => 'city'],
            ['name' => 'Lubuklinggau', 'slug' => 'lubuklinggau', 'type' => 'city'],

            ['name' => 'Banyuasin', 'slug' => 'banyuasin', 'type' => 'regency'],
            ['name' => 'Empat Lawang', 'slug' => 'empat-lawang', 'type' => 'regency'],
            ['name' => 'Lahat', 'slug' => 'lahat', 'type' => 'regency'],
            ['name' => 'Muara Enim', 'slug' => 'muara-enim', 'type' => 'regency'],
            ['name' => 'Musi Banyuasin', 'slug' => 'musi-banyuasin', 'type' => 'regency'],
            ['name' => 'Musi Rawas', 'slug' => 'musi-rawas', 'type' => 'regency'],
            ['name' => 'Musi Rawas Utara', 'slug' => 'musi-rawas-utara', 'type' => 'regency'],
            ['name' => 'Ogan Ilir', 'slug' => 'ogan-ilir', 'type' => 'regency'],
            ['name' => 'Ogan Komering Ilir', 'slug' => 'ogan-komering-ilir', 'type' => 'regency'],
            ['name' => 'Ogan Komering Ulu', 'slug' => 'ogan-komering-ulu', 'type' => 'regency'],
            ['name' => 'Ogan Komering Ulu Selatan', 'slug' => 'ogan-komering-ulu-selatan', 'type' => 'regency'],
            ['name' => 'Ogan Komering Ulu Timur', 'slug' => 'ogan-komering-ulu-timur', 'type' => 'regency'],
            ['name' => 'Penukal Abab Lematang Ilir', 'slug' => 'penukal-abab-lematang-ilir', 'type' => 'regency'],
        ];

        foreach ($regions as $index => $region) {
            Region::updateOrCreate(
                [
                    'slug' => $region['slug'],
                ],
                [
                    'parent_id' => $province->id,
                    'name' => $region['name'],
                    'type' => $region['type'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
