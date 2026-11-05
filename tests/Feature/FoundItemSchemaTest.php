<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FoundItemSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_found_items_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('found_items'));

        $this->assertTrue(Schema::hasColumns('found_items', [
            'id',
            'title',
            'description',
            'category_id',
            'found_at',
            'status',
            'published_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_found_items_category_id_has_foreign_key(): void
    {
        $foreignKeys = Schema::getForeignKeys('found_items');

        $this->assertTrue(
            collect($foreignKeys)->contains(
                fn ($foreignKey) => $foreignKey['columns'] === ['category_id']
                    && $foreignKey['foreign_table'] === 'categories'
            )
        );
    }

    public function test_found_item_has_draft_status_by_default(): void
    {
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $foundItemId = DB::table('found_items')->insertGetId([
            'title' => 'Dompet hitam',
            'category_id' => $categoryId,
            'found_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $foundItem = DB::table('found_items')->find($foundItemId);

        $this->assertSame('Draft', $foundItem->status);
        $this->assertNull($foundItem->published_at);
    }
}
