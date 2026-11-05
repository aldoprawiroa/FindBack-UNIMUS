<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundItemPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_found_item_can_be_saved_as_draft(): void
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->post(route('found-items.store'), [
            'title' => 'HP Hitam',
            'description' => 'HP ditemukan di area kampus',
            'category_id' => $category->id,
            'found_at' => '2026-11-05 08:00:00',
        ]);

        $response->assertRedirect(route('found-items.create'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('found_items', [
            'title' => 'HP Hitam',
            'category_id' => $category->id,
            'status' => 'Draft',
            'published_at' => null,
        ]);
    }

    public function test_found_item_requires_title(): void
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->post(route('found-items.store'), [
            'title' => '',
            'description' => 'HP ditemukan',
            'category_id' => $category->id,
            'found_at' => '2026-11-05 08:00:00',
        ]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseCount('found_items', 0);
    }

    public function test_found_item_requires_existing_category(): void
    {
        $response = $this->post(route('found-items.store'), [
            'title' => 'HP Hitam',
            'description' => 'HP ditemukan di area kampus',
            'category_id' => 999,
            'found_at' => '2026-11-05 08:00:00',
        ]);

        $response->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('found_items', 0);
    }

    public function test_found_item_requires_found_at(): void
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->post(route('found-items.store'), [
            'title' => 'HP Hitam',
            'description' => 'HP ditemukan di area kampus',
            'category_id' => $category->id,
            'found_at' => '',
        ]);

        $response->assertSessionHasErrors('found_at');

        $this->assertDatabaseCount('found_items', 0);
    }
}
