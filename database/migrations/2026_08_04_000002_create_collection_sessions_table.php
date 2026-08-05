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
        Schema::create('collection_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_uuid')->unique();
            $table->string('source')->default('google_maps');
            $table->string('search_query')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->enum('status', ['running', 'completed', 'stopped', 'failed'])->default('running');
            $table->unsignedInteger('businesses_detected')->default(0);
            $table->unsignedInteger('businesses_uploaded')->default(0);
            $table->unsignedInteger('duplicates_count')->default(0);
            $table->unsignedInteger('errors_count')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_sessions');
    }
};
