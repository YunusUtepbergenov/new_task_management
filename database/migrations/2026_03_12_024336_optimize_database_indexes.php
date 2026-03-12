<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isMySQL = DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb';

        if ($isMySQL) {
            // Remove duplicate indexes on tasks table
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex('tasks_creator_id_index');
                $table->dropIndex('tasks_status_index');
                $table->dropIndex('tasks_sector_id_foreign');
                $table->dropIndex('tasks_user_id_foreign');
                $table->dropIndex('tasks_creator_status_project_idx');
                $table->dropIndex('tasks_user_status_project_idx');
            });
        }

        // Add missing indexes
        Schema::table('journals', function (Blueprint $table) {
            $table->index(['lang', 'year'], 'journals_lang_year_index');
        });

        Schema::table('vacations', function (Blueprint $table) {
            $table->index(['year', 'month'], 'vacations_year_month_index');
        });

        if ($isMySQL) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->index(['sector_id', 'effective_deadline'], 'tasks_sector_id_eff_deadline_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isMySQL = DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb';

        if ($isMySQL) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex('tasks_sector_id_eff_deadline_index');
            });
        }

        Schema::table('vacations', function (Blueprint $table) {
            $table->dropIndex('vacations_year_month_index');
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->dropIndex('journals_lang_year_index');
        });

        if ($isMySQL) {
            // Restore removed duplicate indexes
            Schema::table('tasks', function (Blueprint $table) {
                $table->index('creator_id', 'tasks_creator_id_index');
                $table->index('status', 'tasks_status_index');
                $table->index('sector_id', 'tasks_sector_id_foreign');
                $table->index('user_id', 'tasks_user_id_foreign');
                $table->index(['creator_id', 'status', 'project_id'], 'tasks_creator_status_project_idx');
                $table->index(['user_id', 'status', 'project_id'], 'tasks_user_status_project_idx');
            });
        }
    }
};
