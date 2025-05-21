<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTaskStatusToNeProchitano extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tasks')->where('status', 'Новое')->update(['status' => 'Не прочитано']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tasks')->where('status', 'Не прочитано')->update(['status' => 'Новое']);
    }
}
