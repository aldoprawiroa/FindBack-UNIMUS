<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CategorySchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('categories'));

        $this->assertTrue(Schema::hasColumns('categories', [
            'id',
            'name',
            'slug',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_duplicate_category_slug_is_rejected(): void
    {
        DB::table('categories')->insert([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('categories')->insert([
            'name' => 'Elektronik Kedua',
            'slug' => 'elektronik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
