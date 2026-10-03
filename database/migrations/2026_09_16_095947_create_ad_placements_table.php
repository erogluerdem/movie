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
        Schema::create('ad_placements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->default('adsense'); // adsense, custom_code, banner_image
            $table->boolean('is_active')->default(false);
            $table->string('ad_client')->nullable();
            $table->string('ad_slot')->nullable();
            $table->text('code')->nullable();
            $table->string('image_url')->nullable();
            $table->string('target_url')->nullable();
            $table->string('device_target')->default('all'); // all, desktop, mobile
            $table->integer('frequency_minutes')->default(30);
            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_placements');
    }
};
