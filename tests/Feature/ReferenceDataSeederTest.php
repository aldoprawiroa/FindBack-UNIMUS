<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SecurityPost;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_data_is_seeded(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('security_posts', 2);

        $this->assertDatabaseHas('categories', [
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $this->assertDatabaseHas('security_posts', [
            'name' => 'Pos Gerbang Utama',
            'is_active' => true,
        ]);
    }

    public function test_repeated_seeding_does_not_duplicate_data(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame(5, Category::count());
        $this->assertSame(2, SecurityPost::count());
    }

    public function test_repeated_seeding_preserves_inactive_post(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        SecurityPost::where('name', 'Pos Gerbang Utama')
            ->update(['is_active' => false]);

        $this->seed(ReferenceDataSeeder::class);

        $post = SecurityPost::where('name', 'Pos Gerbang Utama')
            ->firstOrFail();

        $this->assertFalse($post->is_active);
    }

    public function test_seeder_does_not_create_users(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame(0, User::count());
    }
}
