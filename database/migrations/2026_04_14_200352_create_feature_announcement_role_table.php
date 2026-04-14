<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_announcement_role', function (Blueprint $table) {
            $table->foreignId('feature_announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['feature_announcement_id', 'role_id'], 'fa_role_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_announcement_role');
    }
};
