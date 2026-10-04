<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When the executor first sent the result to edo.ijro.uz (status "Юборилган"). A task sent
     * by its deadline counts as on time even if it is closed later. Deadlines imported from
     * Excel, or sent before this column existed, keep null: their timing is unknown.
     */
    public function up(): void
    {
        Schema::table('mail_deadlines', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('mail_deadlines', function (Blueprint $table) {
            $table->dropColumn('sent_at');
        });
    }
};
