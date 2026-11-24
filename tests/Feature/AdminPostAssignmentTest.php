<?php

namespace Tests\Feature;

use App\Models\SecurityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPostAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_post_assignments(): void
    {
        $staff = User::factory()->security()->create();

        $this->get(route('admin.post-assignments.index'))
            ->assertRedirect(route('login'));

        $this->patch(route('admin.post-assignments.update', $staff), [
            'security_post_id' => null,
        ])->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_or_update_post_assignments(): void
    {
        $post = SecurityPost::create([
            'name' => 'Pos Gerbang Utama',
            'is_active' => true,
        ]);

        $targetStaff = User::factory()->security()->create();

        foreach ([
            User::factory()->mahasiswa()->create(),
            User::factory()->dosen()->create(),
            User::factory()->security()->create(),
        ] as $nonAdmin) {
            $this->actingAs($nonAdmin)
                ->get(route('admin.post-assignments.index'))
                ->assertForbidden();

            $this->actingAs($nonAdmin)
                ->patch(route('admin.post-assignments.update', $targetStaff), [
                    'security_post_id' => $post->id,
                ])
                ->assertForbidden();
        }

        $this->assertNull($targetStaff->fresh()->security_post_id);
    }

    public function test_admin_can_view_post_assignment_page_with_staff_and_active_posts(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Utama']);
        $security = User::factory()->security()->create(['name' => 'Satpam Budi']);
        $mahasiswa = User::factory()->mahasiswa()->create(['name' => 'Mahasiswa Andi']);

        $activePost = SecurityPost::create(['name' => 'Pos Gerbang Utama', 'is_active' => true]);
        $inactivePost = SecurityPost::create(['name' => 'Pos Nonaktif', 'is_active' => false]);

        $response = $this->actingAs($admin)
            ->get(route('admin.post-assignments.index'));

        $response->assertOk();
        $response->assertViewIs('admin.post-assignments.index');
        $response->assertSee('Admin Utama');
        $response->assertSee('Satpam Budi');
        $response->assertDontSee('Mahasiswa Andi');
        $response->assertSee($activePost->name);
        $response->assertDontSee($inactivePost->name);
    }

    public function test_admin_can_assign_active_security_post_to_staff_and_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $security = User::factory()->security()->create(['name' => 'Petugas Pos']);
        $post = SecurityPost::create(['name' => 'Pos Gedung A', 'is_active' => true]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.post-assignments.update', $security), [
                'security_post_id' => $post->id,
            ]);

        $response->assertRedirect(route('admin.post-assignments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $security->id,
            'security_post_id' => $post->id,
        ]);
    }

    public function test_admin_can_unassign_security_post_from_staff(): void
    {
        $post = SecurityPost::create(['name' => 'Pos Gedung A', 'is_active' => true]);
        $admin = User::factory()->admin()->create();
        $security = User::factory()->security($post)->create();

        $response = $this->actingAs($admin)
            ->patch(route('admin.post-assignments.update', $security), [
                'security_post_id' => '',
            ]);

        $response->assertRedirect(route('admin.post-assignments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $security->id,
            'security_post_id' => null,
        ]);
    }

    public function test_admin_cannot_assign_inactive_security_post(): void
    {
        $admin = User::factory()->admin()->create();
        $security = User::factory()->security()->create();
        $inactivePost = SecurityPost::create(['name' => 'Pos Renovasi', 'is_active' => false]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.post-assignments.update', $security), [
                'security_post_id' => $inactivePost->id,
            ]);

        $response->assertSessionHasErrors('security_post_id');
        $this->assertNull($security->fresh()->security_post_id);
    }

    public function test_admin_cannot_assign_security_post_to_non_staff_user(): void
    {
        $admin = User::factory()->admin()->create();
        $mahasiswa = User::factory()->mahasiswa()->create();
        $post = SecurityPost::create(['name' => 'Pos Gerbang Utama', 'is_active' => true]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.post-assignments.update', $mahasiswa), [
                'security_post_id' => $post->id,
            ]);

        $response->assertForbidden();
        $this->assertNull($mahasiswa->fresh()->security_post_id);
    }

    public function test_navigation_shows_post_assignment_link_only_for_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $security = User::factory()->security()->create();
        $mahasiswa = User::factory()->mahasiswa()->create();

        $this->actingAs($admin)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertSee(route('admin.post-assignments.index'))
            ->assertSee('Penugasan Pos');

        $this->actingAs($security)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertDontSee(route('admin.post-assignments.index'));

        $this->actingAs($mahasiswa)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertDontSee(route('admin.post-assignments.index'));
    }
}
