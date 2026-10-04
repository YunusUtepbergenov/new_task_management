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
        Schema::create('mail_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_deadline_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name')->unique();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_files');
    }
};
