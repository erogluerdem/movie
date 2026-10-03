<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('items_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'slug']);
            $table->index('is_public');
        });

        Schema::create('custom_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_list_id')->constrained('custom_lists')->onDelete('cascade');
            $table->string('media_type'); // 'movie' or 'tv'
            $table->unsignedBigInteger('media_id');
            $table->text('notes')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['custom_list_id', 'media_type', 'media_id']);
            $table->index(['media_type', 'media_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_list_items');
        Schema::dropIfExists('custom_lists');
    }
};
