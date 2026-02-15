<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status');
            $table->index('deadline');
            $table->index('overdue');
            $table->index('creator_id');
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'deadline']);
            $table->index(['sector_id', 'deadline']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['deadline']);
            $table->dropIndex(['overdue']);
            $table->dropIndex(['creator_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'deadline']);
            $table->dropIndex(['sector_id', 'deadline']);
        });
    }
};
