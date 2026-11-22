<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureRole;
use App\Models\FoundItem;
use App\Models\ReturnRecord;
use App\Models\SecurityPost;
use App\Models\User;
use App\Policies\FoundItemPolicy;
use App\Policies\ReturnRecordPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UserAuthorizationAndSecurityPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_to_security_post(): void
    {
        $post = SecurityPost::create([
            'name' => 'Pos Gerbang Utama',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'security',
            'security_post_id' => $post->id,
        ]);

        $this->assertNotNull($user->securityPost);
        $this->assertSame($post->id, $user->securityPost->id);
        $this->assertSame('Pos Gerbang Utama', $user->securityPost->name);
    }

    public function test_user_can_have_no_security_post_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->security_post_id);
        $this->assertNull($user->securityPost);
    }

    public function test_security_post_has_assigned_users_relation(): void
    {
        $post = SecurityPost::create([
            'name' => 'Pos Gedung NRC',
            'is_active' => true,
        ]);

        $user1 = User::factory()->create([
            'name' => 'Petugas 1',
            'role' => 'security',
            'security_post_id' => $post->id,
        ]);

        $user2 = User::factory()->create([
            'name' => 'Petugas 2',
            'role' => 'security',
            'security_post_id' => $post->id,
        ]);

        $this->assertCount(2, $post->assignedUsers);
        $this->assertTrue($post->assignedUsers->contains($user1));
        $this->assertTrue($post->assignedUsers->contains($user2));
    }

    public function test_deleting_security_post_sets_user_security_post_id_to_null(): void
    {
        $post = SecurityPost::create([
            'name' => 'Pos Sementara',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'security_post_id' => $post->id,
        ]);

        $post->delete();
        $user->refresh();

        $this->assertNull($user->security_post_id);
    }

    public function test_user_role_helpers_identify_role_properly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $security = User::factory()->create(['role' => 'security']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isSecurity());
        $this->assertFalse($admin->isMahasiswa());
        $this->assertFalse($admin->isDosen());

        $this->assertTrue($security->isSecurity());
        $this->assertFalse($security->isAdmin());

        $this->assertTrue($mahasiswa->isMahasiswa());
        $this->assertFalse($mahasiswa->isAdmin());

        $this->assertTrue($dosen->isDosen());
        $this->assertFalse($dosen->isAdmin());
    }

    public function test_user_has_role_and_can_manage_returns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $security = User::factory()->create(['role' => 'security']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasRole('admin', 'security'));
        $this->assertFalse($admin->hasRole('mahasiswa', 'dosen'));
        $this->assertTrue($admin->canManageReturns());

        $this->assertTrue($security->canManageReturns());
        $this->assertFalse($mahasiswa->canManageReturns());
    }

    public function test_ensure_role_middleware_allows_authorized_user(): void
    {
        $middleware = new EnsureRole();
        $user = User::factory()->create(['role' => 'admin']);

        $request = Request::create('/test');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, fn () => response('OK'), 'admin', 'security');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }

    public function test_ensure_role_middleware_aborts_forbidden_for_unauthorized_user(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Anda tidak memiliki akses.');

        $middleware = new EnsureRole();
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $request = Request::create('/test');
        $request->setUserResolver(fn () => $user);

        $middleware->handle($request, fn () => response('OK'), 'admin', 'security');
    }

    public function test_found_item_policy_authorization(): void
    {
        $policy = new FoundItemPolicy();
        $admin = User::factory()->create(['role' => 'admin']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $item = new FoundItem();

        $this->assertTrue($policy->create($mahasiswa));
        $this->assertTrue($policy->publish($admin, $item));
        $this->assertFalse($policy->publish($mahasiswa, $item));
        $this->assertTrue($policy->archive($admin, $item));
        $this->assertFalse($policy->archive($mahasiswa, $item));
    }

    public function test_return_record_policy_authorization(): void
    {
        $policy = new ReturnRecordPolicy();
        $admin = User::factory()->create(['role' => 'admin']);
        $security = User::factory()->create(['role' => 'security']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->viewAny($security));
        $this->assertFalse($policy->viewAny($mahasiswa));

        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->create($security));
        $this->assertFalse($policy->create($mahasiswa));
    }
}

