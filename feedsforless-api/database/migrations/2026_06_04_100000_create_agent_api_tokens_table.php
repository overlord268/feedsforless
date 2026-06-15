<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('token_prefix', 16);
            $table->string('token_hash', 64)->unique();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('rotated_from_id')->nullable()->constrained('agent_api_tokens')->nullOnDelete();
            $table->timestamps();

            $table->index(['revoked_at', 'token_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_api_tokens');
    }
};
