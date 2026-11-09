<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_filter_must_reference_an_existing_category(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('catalog.index', ['category' => 999]));

        $response->assertSessionHasErrors('category');
    }

    public function test_category_filter_only_shows_items_from_the_selected_category(): void
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);
        $otherCategory = Category::create([
            'name' => 'Aksesoris',
            'slug' => 'aksesoris',
        ]);

        FoundItem::create([
            'title' => 'Laptop Terpilih',
            'category_id' => $category->id,
            'found_at' => now()->subDay(),
            'status' => 'Published',
            'published_at' => now()->subDay(),
        ]);
        FoundItem::create([
            'title' => 'Tas Tidak Relevan',
            'category_id' => $otherCategory->id,
            'found_at' => now()->subDay(),
            'status' => 'Published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('catalog.index', ['category' => $category->id]));

        $response->assertOk()
            ->assertSee('Laptop Terpilih')
            ->assertDontSee('Tas Tidak Relevan');
    }
}
