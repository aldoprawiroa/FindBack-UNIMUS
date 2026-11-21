<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundItemDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);
    }

    private function createFoundItem(
        string $status,
        $publishedAt
    ): FoundItem {
        return FoundItem::create([
            'title' => 'Laptop Lenovo',
            'description' => 'Laptop ditemukan di perpustakaan.',
            'category_id' => $this->createCategory()->id,
            'found_at' => now()->subDay(),
            'status' => $status,
            'published_at' => $publishedAt,
        ]);
    }

    public function test_authenticated_user_can_view_published_item_detail(): void
    {
        $item = $this->createFoundItem('Published', now()->subHour());

        $response = $this->actingAs(User::factory()->create())
            ->get(route('found-items.show', $item));

        $response->assertOk()
            ->assertSee('Laptop Lenovo')
            ->assertSee('Elektronik');
    }

    public function test_guest_is_redirected_to_login_from_item_detail(): void
    {
        $item = $this->createFoundItem('Published', now()->subHour());

        $this->get(route('found-items.show', $item))
            ->assertRedirect(route('login'));
    }

    public function test_draft_item_detail_returns_404(): void
    {
        $item = $this->createFoundItem('Draft', null);

        $this->actingAs(User::factory()->create())
            ->get(route('found-items.show', $item))
            ->assertNotFound();
    }

    public function test_archived_item_detail_returns_404(): void
    {
        $item = $this->createFoundItem('Archived', now()->subHour());

        $this->actingAs(User::factory()->create())
            ->get(route('found-items.show', $item))
            ->assertNotFound();
    }

    public function test_item_with_future_publication_date_returns_404(): void
    {
        $item = $this->createFoundItem('Published', now()->addDay());

        $this->actingAs(User::factory()->create())
            ->get(route('found-items.show', $item))
            ->assertNotFound();
    }

    public function test_returned_item_can_be_viewed_when_published(): void
    {
        $item = $this->createFoundItem('Returned', now()->subHour());

        $this->actingAs(User::factory()->create())
            ->get(route('found-items.show', $item))
            ->assertOk()
            ->assertSee('Laptop Lenovo');
    }

    public function test_item_without_publication_date_returns_404(): void
    {
        $item = $this->createFoundItem('Published', null);

        $this->actingAs(User::factory()->create())
            ->get(route('found-items.show', $item))
            ->assertNotFound();
    }
}
