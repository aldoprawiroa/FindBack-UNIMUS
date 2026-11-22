<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DemoUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_accounts_are_created_once_with_hashed_passwords(): void
    {
        config()->set('app.demo_user_password', 'demo-secret');

        $this->seed(DemoUserSeeder::class);
        $this->seed(DemoUserSeeder::class);

        $this->assertSame(3, User::count());

        foreach (['mahasiswa', 'dosen', 'admin'] as $role) {
            $user = User::where('email', "$role@unimus.ac.id")->firstOrFail();

            $this->assertSame($role, $user->role);
            $this->assertTrue(Hash::check('demo-secret', $user->password));
        }
    }

    public function test_blank_demo_password_is_rejected_without_creating_accounts(): void
    {
        config()->set('app.demo_user_password', '   ');

        try {
            $this->seed(DemoUserSeeder::class);
            $this->fail('Expected the demo seeder to reject a blank password.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Set DEMO_USER_PASSWORD before seeding demo accounts.', $exception->getMessage());
        }

        $this->assertSame(0, User::count());
    }

    public function test_short_demo_password_is_rejected_without_creating_accounts(): void
    {
        config()->set('app.demo_user_password', 'short');

        try {
            $this->seed(DemoUserSeeder::class);
            $this->fail('Expected the demo seeder to reject a short password.');
        } catch (RuntimeException $exception) {
            $this->assertSame('DEMO_USER_PASSWORD must be at least 8 characters.', $exception->getMessage());
        }

        $this->assertSame(0, User::count());
    }

    public function test_demo_password_shorter_than_eight_unicode_characters_is_rejected(): void
    {
        config()->set('app.demo_user_password', 'ééééééé');

        try {
            $this->seed(DemoUserSeeder::class);
            $this->fail('Expected the demo seeder to reject a password shorter than eight characters.');
        } catch (RuntimeException $exception) {
            $this->assertSame('DEMO_USER_PASSWORD must be at least 8 characters.', $exception->getMessage());
        }

        $this->assertSame(0, User::count());
    }

    public function test_eight_character_demo_password_is_accepted(): void
    {
        config()->set('app.demo_user_password', 'demo1234');

        $this->seed(DemoUserSeeder::class);

        $this->assertSame(3, User::count());
        $this->assertTrue(Hash::check('demo1234', User::where('email', 'admin@unimus.ac.id')->firstOrFail()->password));
    }

    public function test_reseeding_with_a_new_password_preserves_existing_passwords(): void
    {
        config()->set('app.demo_user_password', 'original-secret');
        $this->seed(DemoUserSeeder::class);

        config()->set('app.demo_user_password', 'replacement-secret');
        $this->seed(DemoUserSeeder::class);

        $this->assertSame(3, User::count());

        foreach (User::all() as $user) {
            $this->assertTrue(Hash::check('original-secret', $user->password));
            $this->assertFalse(Hash::check('replacement-secret', $user->password));
        }
    }
}
