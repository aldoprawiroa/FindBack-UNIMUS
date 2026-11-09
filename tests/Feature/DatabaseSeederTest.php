<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test running the default database seeder on an empty database does not create a demo user.
     */
    public function test_default_database_seeder_does_not_create_demo_user(): void
    {
        $this->assertDatabaseEmpty('users');

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertSame(0, User::count());
    }

    /**
     * Test running the artisan db:seed command without arguments does not create a demo user.
     */
    public function test_artisan_db_seed_command_does_not_create_demo_user(): void
    {
        $this->assertDatabaseEmpty('users');

        $this->artisan('db:seed')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertSame(0, User::count());
    }

    /**
     * Test user factory remains available for tests requiring user creation.
     */
    public function test_user_factory_remains_available_for_tests(): void
    {
        $user = User::factory()->create([
            'email' => 'custom@unimus.ac.id',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'custom@unimus.ac.id',
        ]);
        $this->assertSame(1, User::count());
    }
}
