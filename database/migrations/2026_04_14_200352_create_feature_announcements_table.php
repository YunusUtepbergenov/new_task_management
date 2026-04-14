<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title_ru');
            $table->string('title_uz')->nullable();
            $table->text('body_ru');
            $table->text('body_uz')->nullable();
            $table->string('image_path')->nullable();
            $table->string('link_url')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('target_all')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_announcements');
    }
};
