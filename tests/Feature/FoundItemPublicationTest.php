<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function test_catalog_search_matches_item_descriptions(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        FoundItem::create([
            'title' => 'Tas hitam',
            'description' => 'Ditemukan di perpustakaan',
            'category_id' => $category->id,
            'found_at' => now(),
            'status' => 'Published',
            'published_at' => now(),
        ]);

        FoundItem::create([
            'title' => 'Kunci motor',
            'description' => 'Ditemukan di kantin',
            'category_id' => $category->id,
            'found_at' => now(),
            'status' => 'Published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('catalog.index', [
            'search' => 'perpustakaan',
        ]));

        $response->assertOk();
        $response->assertSee('Tas hitam');
        $response->assertDontSee('Kunci motor');
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
            'found_at' => '2026-11-05T08:00',
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

    public function test_found_item_can_be_saved_with_private_jpg_photo(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $photo = UploadedFile::fake()->image('handphone.jpg');

        $this->actingAs($user)->post(route('found-items.store'), [
            'title' => 'HP Hitam',
            'category_id' => $category->id,
            'found_at' => '2026-11-05 08:00:00',
            'photo' => $photo,
        ])->assertRedirect(route('found-items.create'));

        $foundItem = FoundItem::where('title', 'HP Hitam')->firstOrFail();

        $this->assertNotNull($foundItem->photo_path);
        Storage::disk('local')->assertExists($foundItem->photo_path);
        Storage::disk('public')->assertMissing($foundItem->photo_path);
    }

    public function test_found_item_can_be_saved_with_private_png_photo(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $photo = UploadedFile::fake()->image('handphone.png');

        $this->actingAs($user)->post(route('found-items.store'), [
            'title' => 'HP Putih',
            'category_id' => $category->id,
            'found_at' => '2026-11-05 08:00:00',
            'photo' => $photo,
        ])->assertRedirect(route('found-items.create'));

        $foundItem = FoundItem::where('title', 'HP Putih')->firstOrFail();

        $this->assertNotNull($foundItem->photo_path);
        Storage::disk('local')->assertExists($foundItem->photo_path);
        Storage::disk('public')->assertMissing($foundItem->photo_path);
    }

    public function test_found_item_rejects_non_image_photo(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        foreach (['dokumen.txt', 'dokumen.pdf'] as $filename) {
            $response = $this->actingAs($user)->post(route('found-items.store'), [
                'title' => 'Barang Uji',
                'category_id' => $category->id,
                'found_at' => '2026-11-05 08:00:00',
                'photo' => UploadedFile::fake()->create($filename, 100),
            ]);

            $response->assertSessionHasErrors('photo');
        }

        $this->assertDatabaseCount('found_items', 0);
        $this->assertEmpty(Storage::disk('local')->allFiles('found-items'));
    }

    public function test_found_item_rejects_photo_larger_than_2_mb(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $photo = UploadedFile::fake()->image('large.jpg')->size(2049);

        $response = $this->actingAs($user)->post(route('found-items.store'), [
            'title' => 'Barang Uji',
            'category_id' => $category->id,
            'found_at' => '2026-11-05 08:00:00',
            'photo' => $photo,
        ]);

        $response->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('found_items', 0);
        $this->assertEmpty(Storage::disk('local')->allFiles('found-items'));
    }

    public function test_found_item_rejects_category_id_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('found-items.store'), [
            'title' => 'HP Hitam',
            'description' => 'HP ditemukan di area kampus',
            'category_id' => ['1'],
            'found_at' => '2026-11-05T08:00',
        ]);

        $response->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('found_items', 0);
    }

    public function test_found_item_rejects_description_longer_than_2000_characters(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $response = $this->actingAs($user)->post(route('found-items.store'), [
            'title' => 'HP Hitam',
            'description' => str_repeat('A', 2001),
            'category_id' => $category->id,
            'found_at' => '2026-11-05T08:00',
        ]);

        $response->assertSessionHasErrors('description');

        $this->assertDatabaseCount('found_items', 0);
    }

    public function test_found_item_rejects_date_outside_mysql_timestamp_range(): void
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
            'found_at' => '2200-01-01T08:00',
        ]);

        $response->assertSessionHasErrors('found_at');

        $this->assertDatabaseCount('found_items', 0);
    }
}
