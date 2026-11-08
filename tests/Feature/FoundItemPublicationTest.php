<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FoundItemPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_create_found_item_page(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->actingAs($user)->get(route('found-items.create'));

        $response->assertOk();
        $response->assertViewIs('found-items.create');
        $response->assertViewHas('categories');
        $response->assertSee('Input Barang Temuan');
        $response->assertSee('Elektronik');
    }

    public function test_guest_is_redirected_to_login_when_accessing_found_items_create(): void
    {
        $response = $this->get(route('found-items.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_storing_found_item(): void
    {
        $response = $this->post(route('found-items.store'), [
            'title' => 'HP Hitam',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('found_items', 0);
    }

    public function test_guest_accessing_found_items_create_is_redirected_to_intended_page_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'pelapor@unimus.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $guestResponse = $this->get(route('found-items.create'));
        $guestResponse->assertRedirect(route('login'));

        $loginResponse = $this->post(route('login'), [
            'email' => 'pelapor@unimus.ac.id',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('found-items.create'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_found_item_can_be_saved_as_draft(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->actingAs($user)->post(route('found-items.store'), [
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
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->actingAs($user)->post(route('found-items.store'), [
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
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('found-items.store'), [
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
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->actingAs($user)->post(route('found-items.store'), [
            'title' => 'HP Hitam',
            'description' => 'HP ditemukan di area kampus',
            'category_id' => $category->id,
            'found_at' => '',
        ]);

        $response->assertSessionHasErrors('found_at');

        $this->assertDatabaseCount('found_items', 0);
    }
}
