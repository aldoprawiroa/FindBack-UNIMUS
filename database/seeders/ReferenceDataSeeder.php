<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SecurityPost;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Elektronik', 'slug' => 'elektronik'],
            ['name' => 'Dokumen', 'slug' => 'dokumen'],
            ['name' => 'Kunci', 'slug' => 'kunci'],
            ['name' => 'Aksesori', 'slug' => 'aksesori'],
            ['name' => 'Lainnya', 'slug' => 'lainnya'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }

        $securityPosts = [
            'Pos Gerbang Utama',
            'Pos Gedung A',
        ];

        foreach ($securityPosts as $name) {
            SecurityPost::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
