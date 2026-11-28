<?php

namespace Tests\Feature;

use App\Models\SecurityPost;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyAndAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_assign_and_unassign_security_post(): void
    {
        $post = SecurityPost::create([
            'name' => 'Pos Gerbang Utama',
            'is_active' => true,
        ]);

        $user = User::factory()->security()->create();

        $this->assertFalse($user->hasAssignedSecurityPost());

        $user->assignSecurityPost($post);
        $user->refresh();

        $this->assertTrue($user->hasAssignedSecurityPost());
        $this->assertSame($post->id, $user->security_post_id);
        $this->assertTrue($user->isAssignedTo($post));
        $this->assertTrue($user->isAssignedTo($post->id));

        $user->unassignSecurityPost();
        $user->refresh();

        $this->assertFalse($user->hasAssignedSecurityPost());
        $this->assertNull($user->security_post_id);
    }

    public function test_user_is_assigned_to_returns_false_for_different_post(): void
    {
        $post1 = SecurityPost::create(['name' => 'Pos A', 'is_active' => true]);
        $post2 = SecurityPost::create(['name' => 'Pos B', 'is_active' => true]);

        $user = User::factory()->security($post1)->create();

        $this->assertTrue($user->isAssignedTo($post1));
        $this->assertFalse($user->isAssignedTo($post2));
    }

    public function test_user_policy_allows_admin_to_assign_active_security_post_to_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $security = User::factory()->security()->create();
        $post = SecurityPost::create(['name' => 'Pos Gerbang', 'is_active' => true]);

        $policy = new UserPolicy();

        $this->assertTrue($policy->assignSecurityPost($admin, $security, $post));
        $this->assertTrue($policy->assignSecurityPost($admin, $admin, $post));
    }

    public function test_user_policy_denies_non_admin_from_assigning_security_post(): void
    {
        $securityActor = User::factory()->security()->create();
        $mahasiswaActor = User::factory()->mahasiswa()->create();
        $securityTarget = User::factory()->security()->create();
        $post = SecurityPost::create(['name' => 'Pos Gerbang', 'is_active' => true]);

        $policy = new UserPolicy();

        $this->assertFalse($policy->assignSecurityPost($securityActor, $securityTarget, $post));
        $this->assertFalse($policy->assignSecurityPost($mahasiswaActor, $securityTarget, $post));
    }

    public function test_user_policy_denies_assigning_security_post_to_regular_users(): void
    {
        $admin = User::factory()->admin()->create();
        $mahasiswa = User::factory()->mahasiswa()->create();
        $dosen = User::factory()->dosen()->create();
        $post = SecurityPost::create(['name' => 'Pos Gerbang', 'is_active' => true]);

        $policy = new UserPolicy();

        $this->assertFalse($policy->assignSecurityPost($admin, $mahasiswa, $post));
        $this->assertFalse($policy->assignSecurityPost($admin, $dosen, $post));
    }

    public function test_user_policy_denies_assigning_inactive_security_post(): void
    {
        $admin = User::factory()->admin()->create();
        $security = User::factory()->security()->create();
        $inactivePost = SecurityPost::create(['name' => 'Pos Renovasi', 'is_active' => false]);

        $policy = new UserPolicy();

        $this->assertFalse($policy->assignSecurityPost($admin, $security, $inactivePost));
    }

    public function test_navigation_renders_assigned_security_post_for_staff(): void
    {
        $post = SecurityPost::create(['name' => 'Pos Gerbang Utama', 'is_active' => true]);
        $user = User::factory()->security($post)->create([
            'name' => 'Bambang Satpam',
        ]);

        $response = $this->actingAs($user)->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('Bambang Satpam');
        $response->assertSee('[Pos Gerbang Utama]');
        $response->assertSee(route('returns.create'));
        $response->assertSee(route('returns.index'));
    }

    public function test_navigation_does_not_render_security_post_badge_when_unassigned(): void
    {
        $user = User::factory()->mahasiswa()->create([
            'name' => 'Citra Mahasiswa',
        ]);

        $response = $this->actingAs($user)->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('Citra Mahasiswa');
        $response->assertDontSee('user-security-post');
        $response->assertDontSee(route('returns.create'));
        $response->assertDontSee(route('returns.index'));
    }

    public function test_user_staff_scope_and_active_security_post_helpers(): void
    {
        $activePost = SecurityPost::create(['name' => 'Pos Utama', 'is_active' => true]);
        $inactivePost = SecurityPost::create(['name' => 'Pos Lama', 'is_active' => false]);

        $admin = User::factory()->admin()->create(['security_post_id' => $activePost->id]);
        $security = User::factory()->security($inactivePost)->create();
        $mahasiswa = User::factory()->mahasiswa()->create();

        $this->assertTrue($admin->isStaff());
        $this->assertTrue($security->isStaff());
        $this->assertFalse($mahasiswa->isStaff());

        $this->assertTrue($admin->hasActiveSecurityPost());
        $this->assertFalse($security->hasActiveSecurityPost());
        $this->assertFalse($mahasiswa->hasActiveSecurityPost());

        $staffIds = User::query()->staff()->pluck('id')->all();
        $this->assertContains($admin->id, $staffIds);
        $this->assertContains($security->id, $staffIds);
        $this->assertNotContains($mahasiswa->id, $staffIds);
    }

    public function test_user_policy_view_and_view_any_rules(): void
    {
        $admin = User::factory()->admin()->create();
        $security = User::factory()->security()->create();
        $mahasiswa = User::factory()->mahasiswa()->create();

        $policy = new UserPolicy();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($security));
        $this->assertFalse($policy->viewAny($mahasiswa));

        $this->assertTrue($policy->view($admin, $security));
        $this->assertTrue($policy->view($security, $security));
        $this->assertFalse($policy->view($security, $mahasiswa));
    }

    public function test_navigation_marks_inactive_assigned_security_post(): void
    {
        $inactivePost = SecurityPost::create(['name' => 'Pos Lama', 'is_active' => false]);
        $user = User::factory()->security($inactivePost)->create([
            'name' => 'Satpam Lama',
        ]);

        $response = $this->actingAs($user)->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('[Pos Lama (Nonaktif)]');
    }
}
