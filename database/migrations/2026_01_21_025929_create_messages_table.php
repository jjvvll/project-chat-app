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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Users
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();

            // Message content
            $table->text('message')->nullable();

            // File-related fields (stored as JSON arrays)
            $table->json('file_name')->nullable();
            $table->json('file_original_name')->nullable();
            $table->json('folder_path')->nullable();
            $table->json('file_type')->nullable();
            $table->json('thumbnail_path')->nullable();

            // Message state
            $table->boolean('is_read')->default(false);
            $table->string('reaction')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_forwarded')->default(false);

            // Threading / replies
            $table->foreignId('parent_id')->nullable()
                ->constrained('messages')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
