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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'repeat_id')) {
                $table->unsignedBigInteger('repeat_id')->nullable()->after('total');
            }
            if (!Schema::hasColumn('tasks', 'effective_deadline')) {
                $table->dateTime('effective_deadline')->nullable()->after('extended_deadline');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['repeat_id', 'effective_deadline']);
        });
    }
};
