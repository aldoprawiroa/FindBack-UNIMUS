<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('found_item_id')
                ->unique()
                ->constrained('found_items')
                ->restrictOnDelete();

            $table->string('recipient_name');

            $table->foreignId('security_post_id')
                ->constrained('security_posts')
                ->restrictOnDelete();

            $table->foreignId('processed_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('returned_at');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_records');
    }
};