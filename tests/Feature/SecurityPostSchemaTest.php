<?php

namespace Tests\Feature;

use App\Models\SecurityPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityPostSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_posts_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('security_posts'));

        $this->assertTrue(Schema::hasColumns('security_posts', [
            'id',
            'name',
            'is_active',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_new_security_post_is_active_by_default(): void
    {
        $post = SecurityPost::create([
            'name' => 'Pos Gerbang Utama',
        ]);

        $post->refresh();

        $this->assertTrue($post->is_active);
    }

    public function test_security_post_can_be_inactive(): void
    {
        $post = SecurityPost::create([
            'name' => 'Pos Gedung A',
            'is_active' => false,
        ]);

        $post->refresh();

        $this->assertFalse($post->is_active);
    }
}
