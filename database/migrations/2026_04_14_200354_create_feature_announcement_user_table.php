<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_announcement_user', function (Blueprint $table) {
            $table->foreignId('feature_announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('seen_at')->nullable();
            $table->primary(['feature_announcement_id', 'user_id'], 'fa_user_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_announcement_user');
    }
};
