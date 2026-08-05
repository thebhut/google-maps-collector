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
        Schema::create('collection_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('collection_session_id')->nullable()->constrained('collection_sessions')->nullOnDelete();
            $table->string('error_type');
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('error_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_errors');
    }
};
