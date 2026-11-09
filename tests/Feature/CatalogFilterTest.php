<?php

namespace Tests\Feature;

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
}
