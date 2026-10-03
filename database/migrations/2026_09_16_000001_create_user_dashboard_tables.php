<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('password');
            }
            if (! Schema::hasColumn('users', 'preferred_quality')) {
                $table->string('preferred_quality')->default('1080p')->after('role');
            }
            if (! Schema::hasColumn('users', 'preferred_language')) {
                $table->string('preferred_language')->default('en')->after('preferred_quality');
            }
            if (! Schema::hasColumn('users', 'autoplay_next')) {
                $table->boolean('autoplay_next')->default(true)->after('preferred_language');
            }
        });

        if (! Schema::hasTable('watch_histories')) {
            Schema::create('watch_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('media_type'); // 'movie' or 'tv'
                $table->unsignedBigInteger('media_id');
                $table->integer('season_number')->nullable();
                $table->integer('episode_number')->nullable();
                $table->integer('progress_percent')->default(0); // 0-100
                $table->boolean('completed')->default(false);
                $table->timestamp('last_watched_at')->useCurrent();
                $table->timestamps();

                $table->index(['user_id', 'media_type', 'media_id']);
            });
        }

        if (! Schema::hasTable('content_requests')) {
            Schema::create('content_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->string('type')->default('movie'); // 'movie' or 'tv'
                $table->string('release_year')->nullable();
                $table->string('tmdb_id')->nullable();
                $table->string('status')->default('pending'); // 'pending', 'in_review', 'available', 'rejected'
                $table->text('user_notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_requests');
        Schema::dropIfExists('watch_histories');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'role', 'preferred_quality', 'preferred_language', 'autoplay_next']);
        });
    }
};
