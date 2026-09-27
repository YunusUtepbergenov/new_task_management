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
        Schema::create('mail_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_item_id')->constrained()->cascadeOnDelete();
            $table->date('deadline')->index();
            $table->string('status', 16)->default('pending')->index();
            $table->text('note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_deadlines');
    }
};
