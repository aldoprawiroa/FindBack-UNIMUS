<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAdminIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_categories(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        Category::create([
            'name' => 'Dokumen',
            'slug' => 'dokumen',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.categories.index'));

        $response->assertOk();

        $response->assertViewIs('admin.categories.index');

        $response->assertSeeInOrder([
            'Dokumen',
            'Elektronik',
        ]);
    }

    public function test_non_admin_cannot_access_categories(): void
    {
        foreach (['mahasiswa', 'dosen', 'security'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
            ]);

            $this->actingAs($user)
                ->get(route('admin.categories.index'))
                ->assertForbidden();
        }
    }

    public function test_admin_sees_empty_category_message(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Belum ada kategori yang tersedia.');
    }
}
