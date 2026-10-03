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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('role');
            $table->string('banned_reason')->nullable()->after('is_banned');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // e.g. create_movie, delete_review, ban_user, clear_cache
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('stream_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('media_type'); // movie, tv, sport
            $table->unsignedBigInteger('media_id');
            $table->string('server_name')->nullable();
            $table->string('issue_type')->default('broken_link'); // broken_link, audio_sync, buffering, wrong_content
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, resolved, dismissed
            $table->timestamps();
        });

        Schema::create('curated_collections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->boolean('is_featured')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('curated_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curated_collection_id')->constrained('curated_collections')->cascadeOnDelete();
            $table->string('collectible_type'); // Movie or TvShow class
            $table->unsignedBigInteger('collectible_id');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curated_collection_items');
        Schema::dropIfExists('curated_collections');
        Schema::dropIfExists('stream_reports');
        Schema::dropIfExists('audit_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_banned', 'banned_reason']);
        });
    }
};
