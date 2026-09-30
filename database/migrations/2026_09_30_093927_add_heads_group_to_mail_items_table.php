<?php

use App\Models\MailItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remember when an item went to every sector head ("Барча шўъба мудирлари"), so the sector
     * report can count it under "Барча шўъбалар" like the Excel summary does.
     */
    public function up(): void
    {
        Schema::table('mail_items', function (Blueprint $table) {
            $table->string('heads_group', 32)->nullable()->after('position');
        });

        DB::table('mail_item_user')
            ->get(['mail_item_id', 'user_id', 'is_main'])
            ->groupBy('mail_item_id')
            ->filter(fn ($executors): bool => $executors->contains('is_main', false))
            ->each(function ($executors, $itemId): void {
                $group = MailItem::headsGroupFor($executors->pluck('user_id'));

                if ($group) {
                    DB::table('mail_items')->where('id', $itemId)->update(['heads_group' => $group]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('mail_items', function (Blueprint $table) {
            $table->dropColumn('heads_group');
        });
    }
};
