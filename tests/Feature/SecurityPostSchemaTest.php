<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoundItem;
use App\Models\ReturnRecord;
use App\Models\SecurityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function test_security_post_has_many_return_records(): void
    {
        $post = SecurityPost::create([
            'name' => 'Pos Gerbang Utama',
        ]);

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $item = FoundItem::create([
            'title' => 'Laptop Hitam',
            'category_id' => $category->id,
            'found_at' => now(),
        ]);

        $user = User::factory()->create();

        $returnRecord = ReturnRecord::create([
            'found_item_id' => $item->id,
            'recipient_name' => 'Pemilik Laptop',
            'security_post_id' => $post->id,
            'processed_by' => $user->id,
            'returned_at' => now(),
        ]);

        $this->assertInstanceOf(
            HasMany::class,
            $post->returnRecords()
        );

        $this->assertTrue(
            $post->returnRecords()
                ->whereKey($returnRecord->id)
                ->exists()
        );
    }
}
